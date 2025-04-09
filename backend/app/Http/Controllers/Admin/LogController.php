<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class LogController extends Controller
{
    /**
     * The log directory.
     */
    protected $logDirectory;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->logDirectory = storage_path('logs');
    }

    /**
     * Get paginated logs from Laravel log files.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Filters
            $type = $request->input('type');
            $search = $request->input('search');
            $dateRange = $request->input('date_range', 'all');
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 15);

            // Get log files
            $logFiles = $this->getLogFiles($dateRange);
            if (empty($logFiles)) {
                return response()->json([
                    'success' => true,
                    'logs' => [
                        'data' => [],
                        'current_page' => 1,
                        'last_page' => 1,
                        'per_page' => $perPage,
                        'total' => 0
                    ]
                ]);
            }

            // Parse logs
            $logs = $this->parseLogs($logFiles, $type, $search);

            // Paginate
            $total = count($logs);
            $lastPage = ceil($total / $perPage);
            $offset = ($page - 1) * $perPage;
            $logs = array_slice($logs, $offset, $perPage);

            return response()->json([
                'success' => true,
                'logs' => [
                    'data' => $logs,
                    'current_page' => (int) $page,
                    'last_page' => $lastPage,
                    'per_page' => (int) $perPage,
                    'total' => $total
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific log entry.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        try {
            // Get all log files
            $logFiles = $this->getLogFiles('all');
            
            // Parse logs to find the specific log entry
            foreach ($logFiles as $file) {
                $logs = $this->parseLogs([$file]);
                foreach ($logs as $log) {
                    if ($log['id'] === $id) {
                        return response()->json([
                            'success' => true,
                            'log' => $log
                        ]);
                    }
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Log entry not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve log: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a log file.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        try {
            $filename = $request->input('filename');
            $path = $this->logDirectory . '/' . $filename;

            if (!File::exists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Log file not found'
                ], 404);
            }

            File::delete($path);

            return response()->json([
                'success' => true,
                'message' => 'Log file deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete log file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export logs as CSV.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        try {
            // Filters
            $type = $request->input('type');
            $search = $request->input('search');
            $dateRange = $request->input('date_range', 'all');

            // Get log files
            $logFiles = $this->getLogFiles($dateRange);
            
            // Parse logs
            $logs = $this->parseLogs($logFiles, $type, $search);

            // Create CSV
            $filename = 'logs-export-' . date('Y-m-d') . '.csv';
            $path = storage_path('app/' . $filename);

            $handle = fopen($path, 'w');
            
            // Add headers
            fputcsv($handle, ['ID', 'Type', 'Message', 'Date', 'IP Address', 'User']);
            
            // Add data
            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log['id'],
                    $log['type'],
                    $log['message'],
                    $log['created_at'],
                    $log['ip_address'] ?? '',
                    isset($log['user']) ? $log['user']['name'] : 'System'
                ]);
            }
            
            fclose($handle);

            return response()->download($path, $filename, [
                'Content-Type' => 'text/csv',
            ])->deleteFileAfterSend();
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get log files based on date range.
     *
     * @param string $dateRange
     * @return array
     */
    protected function getLogFiles($dateRange = 'all')
    {
        $files = File::files($this->logDirectory);
        $logFiles = [];

        // Filter by date range if specified
        foreach ($files as $file) {
            $filename = $file->getFilename();
            if (strpos($filename, 'laravel') === 0) {
                // Laravel log file format: laravel-YYYY-MM-DD.log
                if ($dateRange !== 'all') {
                    $fileDateStr = str_replace(['laravel-', '.log'], '', $filename);
                    if (strtotime($fileDateStr)) {
                        $fileDate = Carbon::parse($fileDateStr);
                        $today = Carbon::now()->startOfDay();

                        if ($dateRange === 'today' && !$fileDate->isSameDay($today)) {
                            continue;
                        } elseif ($dateRange === 'yesterday' && !$fileDate->isSameDay($today->subDay())) {
                            continue;
                        } elseif ($dateRange === 'week' && $fileDate->lt($today->subDays(7))) {
                            continue;
                        } elseif ($dateRange === 'month' && $fileDate->lt($today->subDays(30))) {
                            continue;
                        }
                    }
                }
                $logFiles[] = $file->getPathname();
            } else {
                // Other log files
                $logFiles[] = $file->getPathname();
            }
        }

        return $logFiles;
    }

    /**
     * Parse log files.
     *
     * @param array $files
     * @param string|null $type
     * @param string|null $search
     * @return array
     */
    protected function parseLogs($files, $type = null, $search = null)
    {
        $logs = [];
        $logId = 1;

        foreach ($files as $file) {
            $content = file_get_contents($file);
            
            // Split by log entry pattern
            $pattern = '/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(\w+): (.*?)(?=\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]|$)/s';
            preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $timestamp = $match[1];
                $level = strtolower($match[3]); // info, error, warning, etc.
                $message = trim($match[4]);
                
                // Apply type filter
                if ($type && $level !== strtolower($type)) {
                    continue;
                }

                // Apply search filter
                if ($search && stripos($message, $search) === false) {
                    continue;
                }

                // Extract IP, URL, user agent if available in the message
                $ipAddress = $this->extractIpAddress($message);
                $url = $this->extractUrl($message);
                $userAgent = $this->extractUserAgent($message);

                // Extract stack trace if available
                $stackTrace = $this->extractStackTrace($message);
                if ($stackTrace) {
                    $message = str_replace($stackTrace, '', $message);
                }

                // Create log entry
                $log = [
                    'id' => (string) $logId++,
                    'type' => $level,
                    'message' => trim($message),
                    'created_at' => $timestamp,
                    'ip_address' => $ipAddress,
                    'url' => $url,
                    'user_agent' => $userAgent,
                    'stack_trace' => $stackTrace,
                    'source_file' => basename($file)
                ];

                $logs[] = $log;
            }
        }

        // Sort by timestamp desc
        usort($logs, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return $logs;
    }

    /**
     * Extract IP address from message.
     *
     * @param string $message
     * @return string|null
     */
    protected function extractIpAddress($message)
    {
        $pattern = '/\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b/';
        if (preg_match($pattern, $message, $matches)) {
            return $matches[0];
        }
        return null;
    }

    /**
     * Extract URL from message.
     *
     * @param string $message
     * @return string|null
     */
    protected function extractUrl($message)
    {
        if (preg_match('/(GET|POST|PUT|DELETE|PATCH) ([^ ]+)/', $message, $matches)) {
            return $matches[2];
        }
        return null;
    }

    /**
     * Extract user agent from message.
     *
     * @param string $message
     * @return string|null
     */
    protected function extractUserAgent($message)
    {
        if (preg_match('/User Agent: ([^\n]+)/', $message, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Extract stack trace from message.
     *
     * @param string $message
     * @return string|null
     */
    protected function extractStackTrace($message)
    {
        if (preg_match('/Stack trace:([\s\S]+)/', $message, $matches)) {
            return trim($matches[0]);
        }
        return null;
    }
} 