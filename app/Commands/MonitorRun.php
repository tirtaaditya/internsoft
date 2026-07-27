<?php

namespace App\Commands;

use App\Services\MonitorService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class MonitorRun extends BaseCommand
{
    protected $group       = 'Monitoring';
    protected $name        = 'monitor:run';
    protected $description = 'Cek status semua domain aktif (UP/DOWN), catat riwayat, dan kirim notifikasi WA saat berubah.';
    protected $usage       = 'monitor:run [options]';
    protected $arguments   = [];
    protected $options     = [
        '--domain'      => 'Cek satu domain_id saja.',
        '--timeout'     => 'Timeout HTTP dalam detik (default: 10).',
        '--concurrency' => 'Jumlah domain dicek paralel (default: 15).',
    ];

    public function run(array $params)
    {
        $domainId    = CLI::getOption('domain');
        $timeout     = CLI::getOption('timeout');
        $concurrency = CLI::getOption('concurrency');

        $domainId    = $domainId !== null && $domainId !== false ? (int) $domainId : null;
        $timeout     = $timeout !== null && $timeout !== false ? (int) $timeout : 10;
        $concurrency = $concurrency !== null && $concurrency !== false ? (int) $concurrency : 15;

        CLI::write('Internsoft Monitor starting...', 'yellow');
        CLI::write('Timeout: ' . $timeout . 's | Paralel: ' . $concurrency, 'white');

        if ($domainId) {
            CLI::write('Filter domain_id: ' . $domainId, 'white');
        }

        $started = microtime(true);

        try {
            $service = new MonitorService($timeout, 7, $concurrency);
            $summary = $service->run($domainId);
        } catch (\Throwable $e) {
            CLI::error('Monitor gagal: ' . $e->getMessage());
            log_message('error', 'monitor:run failed: {message}', ['message' => $e->getMessage()]);

            return EXIT_ERROR;
        }

        foreach ($summary['results'] as $row) {
            $color = $row['status'] === 'UP' ? 'green' : 'red';
            $line  = sprintf(
                '[%s] %s | %s -> %s | HTTP %s | %s ms',
                $row['checked_at'],
                $row['domain_url'],
                $row['old_status'],
                $row['status'],
                $row['http_code'] ?? '-',
                $row['response_time_ms'] ?? '-'
            );

            CLI::write($line, $color);

            if (! empty($row['error_message'])) {
                CLI::write('  error: ' . $row['error_message'], 'light_gray');
            }

            if ($row['changed']) {
                CLI::write('  status berubah, notifikasi terkirim: ' . $row['notified'], 'yellow');
            }
        }

        $elapsed = round(microtime(true) - $started, 2);

        CLI::newLine();
        CLI::write('======= RINGKASAN =======', 'white');
        CLI::write('Dicek     : ' . $summary['checked']);
        CLI::write('UP        : ' . $summary['up'], 'green');
        CLI::write('DOWN      : ' . $summary['down'], 'red');
        CLI::write('Berubah   : ' . $summary['changed'], 'yellow');
        CLI::write('Notifikasi: ' . $summary['notified']);
        CLI::write('Hapus lama : checks ' . ($summary['pruned_checks'] ?? 0) . ', outages ' . ($summary['pruned_outages'] ?? 0));
        CLI::write('Durasi    : ' . $elapsed . 's');
        CLI::write('Selesai.', 'green');

        return EXIT_SUCCESS;
    }
}
