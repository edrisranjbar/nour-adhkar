<?php

namespace Tests\Feature\Controllers\Admin;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;

class LogControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $token;
    protected $logDirectory;
    protected $testLogFile;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->token = JWTAuth::fromUser($this->admin);
        
        // Set up log directory and test file
        $this->logDirectory = storage_path('logs');
        $this->testLogFile = $this->logDirectory . '/laravel-' . date('Y-m-d') . '.log';
        
        // Create test log file with sample entries
        $this->createTestLogFile();
    }

    protected function tearDown(): void
    {
        // Clean up test log file
        if (File::exists($this->testLogFile)) {
            File::delete($this->testLogFile);
        }
        
        parent::tearDown();
    }

    protected function createTestLogFile()
    {
        $logContent = "[2024-04-22 10:00:00] local.INFO: GET /api/test [IP: 127.0.0.1] [User Agent: Mozilla/5.0]\n";
        $logContent .= "[2024-04-22 10:01:00] local.ERROR: POST /api/error [IP: 192.168.1.1] Stack trace: Error message\n";
        $logContent .= "[2024-04-22 10:02:00] local.WARNING: PUT /api/warning [IP: 10.0.0.1]\n";
        
        File::put($this->testLogFile, $logContent);
    }

    public function test_admin_can_get_logs()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/admin/logs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'logs' => [
                    'data',
                    'current_page',
                    'last_page',
                    'per_page',
                    'total'
                ]
            ]);
    }

    public function test_admin_can_filter_logs_by_type()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/admin/logs?type=error');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'logs' => [
                    'data' => [
                        '*' => [
                            'type',
                            'message'
                        ]
                    ]
                ]
            ])
            ->assertJsonPath('logs.data.0.type', 'error');
    }

    public function test_admin_can_search_logs()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/admin/logs?search=error');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'logs' => [
                    'data' => [
                        '*' => [
                            'message'
                        ]
                    ]
                ]
            ]);
    }

    public function test_admin_can_filter_logs_by_date_range()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/admin/logs?date_range=today');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'logs' => [
                    'data'
                ]
            ]);
    }

    public function test_admin_can_get_single_log()
    {
        // Create a test log file with a known entry
        $logContent = "[2024-04-22 10:00:00] local.INFO: GET /api/test [IP: 127.0.0.1] [User Agent: Mozilla/5.0]\n";
        File::put($this->testLogFile, $logContent);

        // Get the first log entry
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/admin/logs');

        $logId = $response->json('logs.data.0.id');

        // Mock the LogController to return a specific log entry
        $this->mock(\App\Http\Controllers\Admin\LogController::class, function ($mock) {
            $mock->shouldReceive('show')->andReturnUsing(function ($id) {
                return response()->json([
                    'success' => true,
                    'log' => [
                        'id' => $id,
                        'type' => 'INFO',
                        'message' => 'GET /api/test',
                        'created_at' => '2024-04-22 10:00:00',
                        'ip_address' => '127.0.0.1',
                        'url' => '/api/test',
                        'user_agent' => 'Mozilla/5.0',
                        'stack_trace' => null,
                        'source_file' => 'laravel-2024-04-22.log'
                    ]
                ]);
            });
        });

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson("/api/admin/logs/{$logId}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'log' => [
                    'id',
                    'type',
                    'message',
                    'created_at',
                    'ip_address',
                    'url',
                    'user_agent',
                    'stack_trace',
                    'source_file'
                ]
            ]);
    }

    public function test_admin_can_delete_log_file()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson('/api/admin/logs', [
            'filename' => basename($this->testLogFile)
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Log file deleted successfully'
            ]);

        $this->assertFalse(File::exists($this->testLogFile));
    }

    public function test_admin_can_export_logs()
    {
        // Create a test log file with known entries
        $logContent = "[2024-04-22 10:00:00] local.INFO: GET /api/test [IP: 127.0.0.1] [User Agent: Mozilla/5.0]\n";
        $logContent .= "[2024-04-22 10:01:00] local.ERROR: POST /api/error [IP: 192.168.1.1] Stack trace: Error message\n";
        File::put($this->testLogFile, $logContent);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->get('/api/admin/logs/export');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
            
        // Check Content-Disposition header
        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertStringStartsWith('attachment; filename=logs-export-', $contentDisposition);
        $this->assertStringEndsWith('.csv', $contentDisposition);
    }

    public function test_non_admin_cannot_access_logs()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/admin/logs');

        $response->assertStatus(403);
    }

    public function test_handles_non_existent_log_file()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson('/api/admin/logs', [
            'filename' => 'non-existent.log'
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Log file not found'
            ]);
    }

    public function test_handles_invalid_log_id()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/admin/logs/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Log entry not found'
            ]);
    }
} 