<?php

namespace App\Services;

use Config\Services;

class WhatsAppService
{
    private const API_URL = 'http://198.167.141.15/wa/api/rekonbdn/messages/send';

    /**
     * @return array{success: bool, status: int|null, response: string|null, error: string|null}
     */
    public function sendMessage(string $to, string $message): array
    {
        try {
            $client = Services::curlrequest([
                'timeout'    => 15,
                'http_errors' => false,
            ]);

            $response = $client->post(self::API_URL, [
                'json' => [
                    'to'      => $to,
                    'message' => $message,
                ],
            ]);

            $status = $response->getStatusCode();
            $body   = $response->getBody();

            return [
                'success'  => $status >= 200 && $status < 300,
                'status'   => $status,
                'response' => $body,
                'error'    => null,
            ];
        } catch (\Throwable $e) {
            return [
                'success'  => false,
                'status'   => null,
                'response' => null,
                'error'    => $e->getMessage(),
            ];
        }
    }

    public function buildServerStatusMessage(string $domain, string $status, string $detail): string
    {
        return "Internsoft Monitoring: {$domain} saat ini {$status}. {$detail}";
    }

    public function buildStatusChangeMessage(
        string $domain,
        string $status,
        string $downAt,
        string $upAt,
        string $duration = '-'
    ): string {
        $statusLabel = $status === 'UP' ? 'UP (Online)' : 'DOWN (Offline)';

        return "Internsoft Monitoring\n"
            . "Domain: {$domain}\n"
            . "Status: {$statusLabel}\n"
            . "down : {$downAt}\n"
            . "up : {$upAt}\n"
            . "durasi down : {$duration}";
    }

    /**
     * Format detik jadi teks durasi yang mudah dibaca.
     * Contoh: 75 -> "1 menit 15 detik", 3725 -> "1 jam 2 menit 5 detik"
     */
    public function formatDuration(int $seconds): string
    {
        $seconds = max(0, $seconds);

        if ($seconds < 60) {
            return $seconds . ' detik';
        }

        $hours   = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $secs    = $seconds % 60;
        $parts   = [];

        if ($hours > 0) {
            $parts[] = $hours . ' jam';
        }
        if ($minutes > 0) {
            $parts[] = $minutes . ' menit';
        }
        if ($secs > 0 && $hours === 0) {
            $parts[] = $secs . ' detik';
        }

        return $parts !== [] ? implode(' ', $parts) : '0 detik';
    }
}
