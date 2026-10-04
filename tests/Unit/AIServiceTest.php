<?php

namespace Tests\Unit;

use App\Services\AIService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Exception;

require_once __DIR__ . '/../TestCase.php';

class AIServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.ai.base_url', 'https://ai.example.com/v1');
        Config::set('services.ai.api_key', 'test-key');
        Config::set('services.ai.model', 'gemini-3.7-flash');
        Config::set('services.ai.timeout', 25);
        Config::set('services.ai.execution_time_limit', 120);
    }

    public function test_aiservice_prepares_execution_environment_and_bounds_timeout(): void
    {
        $service = new AIService();
        $effectiveTimeout = $service->prepareExecutionEnvironment();

        // Effective timeout should be positive and <= configured timeout
        $this->assertGreaterThan(0, $effectiveTimeout);
        $this->assertLessThanOrEqual(25, $effectiveTimeout);
    }

    public function test_aiservice_generates_structured_report_successfully(): void
    {
        $mockPayload = [
            'dasar_pelaksanaan' => 'Surat Tugas Nomor 123',
            'moda_transportasi' => 'Kendaraan Pribadi',
            'daerah_dikunjungi' => 'Kecamatan Mempawah Timur',
            'tabel_kegiatan' => [],
            'ringkasan' => 'Ringkasan kegiatan.',
            'kesimpulan' => 'Kesimpulan kegiatan.',
            'tindak_lanjut' => 'Tindak lanjut kegiatan.',
        ];

        Http::fake([
            'https://ai.example.com/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode($mockPayload),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $service = new AIService();
        $result = $service->generateStructuredReport([], [
            'kegiatan_nama' => 'Uji Coba Lapangan',
            'nomor_surat_tugas' => '123/ST/2026',
            'moda_transportasi' => 'Kendaraan Pribadi',
            'daerah_dikunjungi' => 'Mempawah Timur',
            'wilayah_tugas' => 'Kecamatan Mempawah Timur',
            'pelaksana_nama' => 'Test User',
            'periode_str' => '04 Oktober 2026',
            'preset_steps' => [],
        ]);

        $this->assertEquals($mockPayload['dasar_pelaksanaan'], $result['dasar_pelaksanaan']);
        $this->assertEquals($mockPayload['ringkasan'], $result['ringkasan']);
    }

    public function test_aiservice_catches_timeout_and_throws_user_friendly_exception(): void
    {
        Http::fake([
            'https://ai.example.com/v1/chat/completions' => function () {
                throw new ConnectionException('cURL error 28: Operation timed out after 25000 milliseconds with 0 bytes received');
            },
        ]);

        $service = new AIService();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Permintaan ke AI Service melebihi batas waktu (timeout). Silakan periksa jaringan atau coba beberapa saat lagi.');

        $service->generateStructuredReport([], [
            'kegiatan_nama' => 'Uji Coba Lapangan',
            'nomor_surat_tugas' => '123/ST/2026',
        ]);
    }
}
