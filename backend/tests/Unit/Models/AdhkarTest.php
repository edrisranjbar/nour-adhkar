<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Adhkar;
use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdhkarTest extends TestCase
{
    use RefreshDatabase;

    public function test_adhkar_has_required_fields()
    {
        $collection = Collection::factory()->create();
        $adhkar = Adhkar::factory()->create([
            'title' => 'Test Dhikr',
            'arabic_text' => 'بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ',
            'translation' => 'In the name of Allah, the Most Gracious, the Most Merciful',
            'count' => 33,
            'prefix' => 'سبحان الله',
            'collection_id' => $collection->id
        ]);

        $this->assertEquals('Test Dhikr', $adhkar->title);
        $this->assertEquals('بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ', $adhkar->arabic_text);
        $this->assertEquals('In the name of Allah, the Most Gracious, the Most Merciful', $adhkar->translation);
        $this->assertEquals(33, $adhkar->count);
        $this->assertEquals('سبحان الله', $adhkar->prefix);
        $this->assertEquals($collection->id, $adhkar->collection_id);
    }

    public function test_adhkar_belongs_to_collection()
    {
        $collection = Collection::factory()->create();
        $adhkar = Adhkar::factory()->create([
            'title' => 'Test Dhikr',
            'arabic_text' => 'بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ',
            'translation' => 'In the name of Allah, the Most Gracious, the Most Merciful',
            'count' => 33,
            'prefix' => 'سبحان الله',
            'collection_id' => $collection->id
        ]);

        $this->assertInstanceOf(Collection::class, $adhkar->collection);
        $this->assertEquals($collection->id, $adhkar->collection->id);
    }

    public function test_adhkar_casts_attributes_correctly()
    {
        $adhkar = new Adhkar();
        
        $this->assertEquals([
            'count' => 'integer',
            'id' => 'int'
        ], $adhkar->getCasts());
    }
} 