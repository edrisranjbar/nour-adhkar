<?php

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/** A manually dispatched, additive content operation. Never overwrites an existing article. */
final class ScheduledArticleBatch
{
    public static function import(array $input): array
    {
        $batch = Validator::make($input, [
            'timezone' => 'required|in:Asia/Tehran',
            'reference_slug' => 'required|in:daily-adhkar-start',
            'articles' => 'required|array|size:2',
            'articles.*.slug' => ['required', 'string', 'max:150', 'distinct', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'articles.*.title' => 'required|string|max:200',
            'articles.*.excerpt' => 'required|string|max:500',
            'articles.*.content' => 'required|string|max:200000',
            'articles.*.publish_at' => 'required|date_format:Y-m-d\TH:i',
        ])->validate();

        $dates = array_map(fn ($a) => CarbonImmutable::createFromFormat('!Y-m-d\TH:i', $a['publish_at'], $batch['timezone'])->utc(), $batch['articles']);
        if ($dates[0]->isPast() || $dates[1]->isPast() || $dates[0]->diffInHours($dates[1], false) != 48) {
            throw new InvalidArgumentException('Both dates must be future dates exactly two days apart.');
        }

        return DB::transaction(function () use ($batch, $dates) {
            $author = DB::table('posts')->where('slug', $batch['reference_slug'])->value('user_id');
            if (!$author || !DB::table('users')->where('id', $author)->where('role', 'admin')->exists()) {
                throw new InvalidArgumentException('The reference article must belong to an existing administrator.');
            }
            $result = [];
            foreach ($batch['articles'] as $i => $article) {
                $row = [
                    'slug' => $article['slug'], 'title' => trim($article['title']),
                    'excerpt' => trim($article['excerpt']), 'content' => trim($article['content']),
                    'status' => 'published', 'published_at' => $dates[$i]->format('Y-m-d H:i:s'),
                    'user_id' => $author,
                ];
                // published + future published_at is the panel's scheduled state, not immediate publication.
                $existing = DB::table('posts')->where('slug', $row['slug'])->first();
                if ($existing) {
                    foreach ($row as $field => $expected) {
                        if ((string) $existing->$field !== (string) $expected) {
                            throw new InvalidArgumentException('Existing article differs; refusing to overwrite: '.$row['slug']);
                        }
                    }
                    $id = $existing->id;
                } else {
                    $id = DB::table('posts')->insertGetId($row + ['created_at' => now(), 'updated_at' => now()]);
                }
                $saved = DB::table('posts')->find($id);
                if ($saved->content !== $row['content'] || $saved->published_at !== $row['published_at'] || $saved->status !== 'published') {
                    throw new RuntimeException('Article read-back verification failed.');
                }
                $result[] = ['id' => $id, 'slug' => $saved->slug,
                    'published_at_utc' => $saved->published_at, 'publish_at_tehran' => $dates[$i]->setTimezone('Asia/Tehran')->format('Y-m-d H:i'),
                    'state' => 'scheduled', 'content_sha256' => hash('sha256', $saved->content)];
            }
            return $result;
        });
    }
}

if (isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    try {
        $backend = getenv('NOUR_BACKEND_ROOT');
        if (!$backend || empty($argv[1])) throw new InvalidArgumentException('Backend root and approved batch file are required.');
        require $backend.'/vendor/autoload.php';
        $app = require $backend.'/bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        $input = json_decode(file_get_contents($argv[1]), true, 512, JSON_THROW_ON_ERROR);
        echo json_encode(['scheduled' => ScheduledArticleBatch::import($input)], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT).PHP_EOL;
    } catch (Throwable $error) {
        $safeMessage = $error instanceof InvalidArgumentException || $error instanceof \Illuminate\Validation\ValidationException
            ? $error->getMessage() : 'Scheduling failed; inspect server logs. Exception: '.get_class($error);
        fwrite(STDERR, $safeMessage.PHP_EOL);
        exit(1);
    }
}
