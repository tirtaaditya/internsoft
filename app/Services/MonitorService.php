<?php

namespace App\Services;

use App\Models\DomainCheckModel;
use App\Models\DomainContactModel;
use App\Models\DomainModel;
use App\Models\OutageEventModel;

class MonitorService
{
    /**
     * UA mirip browser supaya tidak diblok WAF/anti-bot, tapi tetap bisa dikenali pemilik server.
     */
    private const USER_AGENT = 'Mozilla/5.0 (compatible; InternsoftMonitor/1.0; +https://internsoft.my.id)';

    /**
     * Kode error cURL yang berkaitan dengan SSL/sertifikat.
     * 35 SSL_CONNECT_ERROR, 51 PEER_FAILED_VERIFICATION, 58 SSL_CERTPROBLEM,
     * 60 SSL_CACERT, 77 SSL_CACERT_BADFILE, 83 SSL_ISSUER_ERROR.
     */
    private const SSL_ERROR_CODES = [35, 51, 58, 60, 77, 83];

    private DomainModel $domains;
    private DomainCheckModel $checks;
    private OutageEventModel $outages;
    private DomainContactModel $contacts;
    private WhatsAppService $whatsapp;
    private int $timeoutSeconds;
    private int $retentionDays;
    private int $concurrency;

    public function __construct(int $timeoutSeconds = 10, int $retentionDays = 7, int $concurrency = 15)
    {
        $this->domains        = new DomainModel();
        $this->checks         = new DomainCheckModel();
        $this->outages        = new OutageEventModel();
        $this->contacts       = new DomainContactModel();
        $this->whatsapp       = new WhatsAppService();
        $this->timeoutSeconds = max(3, $timeoutSeconds);
        $this->retentionDays  = max(1, $retentionDays);
        $this->concurrency    = max(1, $concurrency);
    }

    /**
     * @return array{checked:int, up:int, down:int, changed:int, notified:int, pruned_checks:int, pruned_outages:int, results:list<array<string,mixed>>}
     */
    public function run(?int $domainId = null): array
    {
        $builder = $this->domains->where('is_active', 1);

        if ($domainId !== null) {
            $builder->where('id', $domainId);
        }

        $domains = $builder->orderBy('id', 'ASC')->findAll();

        $summary = [
            'checked'        => 0,
            'up'             => 0,
            'down'           => 0,
            'changed'        => 0,
            'notified'       => 0,
            'pruned_checks'  => 0,
            'pruned_outages' => 0,
            'results'        => [],
        ];

        // Cek domain paralel per batch (default 15 sekaligus) untuk efisiensi.
        foreach (array_chunk($domains, $this->concurrency) as $batch) {
            $probes = $this->checkUrlBatch($batch);

            foreach ($batch as $domain) {
                $probe  = $probes[(int) $domain['id']] ?? $this->checkUrl((string) $domain['domain_url']);
                $result = $this->processDomain($domain, $probe);

                $summary['checked']++;
                $summary[$result['status'] === 'UP' ? 'up' : 'down']++;

                if ($result['changed']) {
                    $summary['changed']++;
                }

                $summary['notified'] += $result['notified'];
                $summary['results'][] = $result;
            }
        }

        // Bersihkan riwayat lama (default 7 hari) setiap kali monitor jalan.
        $pruned = $this->pruneOldHistory($this->retentionDays);
        $summary['pruned_checks']  = $pruned['checks'];
        $summary['pruned_outages'] = $pruned['outages'];

        return $summary;
    }

    /**
     * Hapus riwayat lebih dari N hari.
     * Outage yang masih open (ended_at NULL) tidak dihapus.
     *
     * @return array{checks:int, outages:int}
     */
    public function pruneOldHistory(int $days = 7): array
    {
        $days      = max(1, $days);
        $threshold = date('Y-m-d H:i:s', strtotime('-' . $days . ' days'));
        $db        = db_connect();

        $db->table('domain_checks')
            ->where('checked_at <', $threshold)
            ->delete();
        $checksDeleted = $db->affectedRows();

        $db->table('outage_events')
            ->where('ended_at <', $threshold)
            ->where('ended_at IS NOT NULL', null, false)
            ->delete();
        $outagesDeleted = $db->affectedRows();

        return [
            'checks'  => max(0, $checksDeleted),
            'outages' => max(0, $outagesDeleted),
        ];
    }

    /**
     * @param array<string, mixed> $domain
     * @param array<string, mixed>|null $probe Hasil cek yang sudah dihitung (untuk mode paralel).
     * @return array<string, mixed>
     */
    public function processDomain(array $domain, ?array $probe = null): array
    {
        $checkedAt = date('Y-m-d H:i:s');
        $probe     = $probe ?? $this->checkUrl((string) $domain['domain_url']);
        $newStatus = $probe['status'];
        $oldStatus = (string) ($domain['last_status'] ?? 'UNKNOWN');
        $statusTransition = $oldStatus !== $newStatus;
        $domainId  = (int) $domain['id'];

        // Riwayat UP/DOWN: status sama berturut-turut → update baris terakhir;
        // status berubah → insert baris baru (baris lama tidak dihapus).
        $this->recordCheck($domainId, $newStatus, $checkedAt, $probe);

        $this->domains->update($domainId, [
            'last_status'     => $newStatus,
            'last_checked_at' => $checkedAt,
        ]);

        if ($statusTransition) {
            $this->handleOutageTransition((int) $domain['id'], $oldStatus, $newStatus, $checkedAt);
        }

        $notified = 0;

        // Hanya kirim WA saat status benar-benar berubah:
        // UP/UNKNOWN -> DOWN = kirim
        // DOWN -> UP = kirim
        // UP -> UP / DOWN -> DOWN = jangan kirim
        $shouldNotify = ($newStatus === 'DOWN' && $oldStatus !== 'DOWN')
            || ($newStatus === 'UP' && $oldStatus === 'DOWN');

        if ($shouldNotify) {
            $notified = $this->notifyContacts(
                $domain,
                $newStatus,
                $checkedAt,
                (int) $domain['id']
            );
        }

        return [
            'domain_id'        => (int) $domain['id'],
            'domain_url'       => (string) $domain['domain_url'],
            'old_status'       => $oldStatus,
            'status'           => $newStatus,
            'http_code'        => $probe['http_code'],
            'response_time_ms' => $probe['response_time_ms'],
            'error_message'    => $probe['error_message'],
            'changed'          => $statusTransition,
            'notified'         => $notified,
            'checked_at'       => $checkedAt,
        ];
    }

    /**
     * @return array{status:string, http_code:int|null, response_time_ms:int|null, error_message:string|null}
     */
    public function checkUrl(string $url): array
    {
        if (! preg_match('#^https?://#i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }

        $result = $this->performHttpCheck($url, true);

        // Di Windows/XAMPP sering gagal karena CA bundle lokal belum lengkap.
        // Retry tanpa verifikasi SSL supaya status domain tetap akurat.
        if (
            $result['status'] === 'DOWN'
            && (
                in_array((int) ($result['errno'] ?? 0), self::SSL_ERROR_CODES, true)
                || $this->isLocalSslIssuerProblem((string) ($result['error_message'] ?? ''))
            )
        ) {
            $retry = $this->performHttpCheck($url, false);
            if ($retry['status'] === 'UP') {
                $retry['error_message'] = null;
            }

            return $retry;
        }

        return $result;
    }

    /**
     * Cek banyak domain sekaligus (paralel) memakai curl_multi.
     *
     * @param list<array<string, mixed>> $domains
     * @return array<int, array{status:string, http_code:int|null, response_time_ms:int|null, error_message:string|null}>
     */
    public function checkUrlBatch(array $domains): array
    {
        if ($domains === []) {
            return [];
        }

        // Percobaan pertama: HEAD + verifikasi SSL.
        $results = $this->runMultiCheck($domains, true, true);

        // Ulang tanpa verifikasi SSL untuk domain yang gagal karena masalah sertifikat/CA.
        $sslRetry = $this->filterDomains(
            $domains,
            $results,
            fn (array $res): bool => $res['status'] === 'DOWN'
                && (
                    in_array((int) ($res['errno'] ?? 0), self::SSL_ERROR_CODES, true)
                    || $this->isLocalSslIssuerProblem((string) ($res['error_message'] ?? ''))
                )
        );

        if ($sslRetry !== []) {
            foreach ($this->runMultiCheck($sslRetry, false, true) as $id => $res) {
                if ($res['status'] === 'UP') {
                    $res['error_message'] = null;
                }
                $results[$id] = $res;
            }
        }

        // Sebagian server menolak HEAD (405/501) atau balas kosong — ulang pakai GET.
        // Hanya untuk yang tidak punya error transport, supaya domain mati tidak dicek dua kali.
        $getRetry = $this->filterDomains(
            $domains,
            $results,
            static fn (array $res): bool => $res['status'] === 'DOWN'
                && (int) ($res['errno'] ?? 0) === 0
                && in_array($res['http_code'], [null, 0, 405, 501], true)
        );

        if ($getRetry !== []) {
            foreach ($this->runMultiCheck($getRetry, false, false) as $id => $res) {
                // Hanya timpa kalau GET memberi hasil yang lebih baik.
                if ($res['status'] === 'UP') {
                    $results[$id] = $res;
                }
            }
        }

        return $results;
    }

    /**
     * @param list<array<string, mixed>> $domains
     * @param array<int, array<string, mixed>> $results
     * @param callable(array<string, mixed>): bool $predicate
     * @return list<array<string, mixed>>
     */
    private function filterDomains(array $domains, array $results, callable $predicate): array
    {
        $matched = [];

        foreach ($domains as $domain) {
            $res = $results[(int) $domain['id']] ?? null;

            if ($res !== null && $predicate($res)) {
                $matched[] = $domain;
            }
        }

        return $matched;
    }

    /**
     * @param list<array<string, mixed>> $domains
     * @return array<int, array{status:string, http_code:int|null, response_time_ms:int|null, error_message:string|null}>
     */
    private function runMultiCheck(array $domains, bool $verifySsl, bool $useHead): array
    {
        $multi   = curl_multi_init();
        $handles = [];
        $start   = microtime(true);

        foreach ($domains as $domain) {
            $id  = (int) $domain['id'];
            $url = (string) $domain['domain_url'];

            if (! preg_match('#^https?://#i', $url)) {
                $url = 'https://' . ltrim($url, '/');
            }

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS      => 5,
                CURLOPT_CONNECTTIMEOUT => $this->timeoutSeconds,
                CURLOPT_TIMEOUT        => $this->timeoutSeconds,
                CURLOPT_NOBODY         => $useHead,
                CURLOPT_USERAGENT      => self::USER_AGENT,
                CURLOPT_ENCODING       => '',
                CURLOPT_SSL_VERIFYPEER => $verifySsl,
                CURLOPT_SSL_VERIFYHOST => $verifySsl ? 2 : 0,
            ]);

            curl_multi_add_handle($multi, $ch);
            $handles[$id] = $ch;
        }

        do {
            $status = curl_multi_exec($multi, $running);
            if ($running) {
                curl_multi_select($multi, 1.0);
            }
        } while ($running && $status === CURLM_OK);

        // curl_errno() tidak terisi untuk handle di dalam curl_multi.
        // Kode error asli hanya tersedia lewat curl_multi_info_read().
        $errors = [];
        while ($info = curl_multi_info_read($multi)) {
            $errors[spl_object_id($info['handle'])] = (int) $info['result'];
        }

        $elapsedMs = (int) round((microtime(true) - $start) * 1000);
        $results   = [];

        foreach ($handles as $id => $ch) {
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $totalMs  = (int) round(((float) curl_getinfo($ch, CURLINFO_TOTAL_TIME)) * 1000);
            $errno    = (int) ($errors[spl_object_id($ch)] ?? curl_errno($ch));
            $error    = $errno !== 0 ? (curl_error($ch) ?: (string) curl_strerror($errno)) : '';

            if ($totalMs <= 0) {
                $totalMs = $elapsedMs;
            }

            $results[$id] = $this->evaluateProbe($errno, (string) $error, $httpCode, $totalMs);

            curl_multi_remove_handle($multi, $ch);
            curl_close($ch);
        }

        curl_multi_close($multi);

        return $results;
    }

    /**
     * @return array{status:string, http_code:int|null, response_time_ms:int|null, error_message:string|null, errno:int}
     */
    private function evaluateProbe(int $errno, string $error, int $httpCode, int $elapsedMs): array
    {
        if ($errno !== 0) {
            return [
                'status'           => 'DOWN',
                'http_code'        => null,
                'response_time_ms' => $elapsedMs,
                'error_message'    => $error !== '' ? $error : 'Koneksi gagal',
                'errno'            => $errno,
            ];
        }

        if ($httpCode <= 0) {
            return [
                'status'           => 'DOWN',
                'http_code'        => null,
                'response_time_ms' => $elapsedMs,
                'error_message'    => 'Tidak ada respons HTTP',
                'errno'            => 0,
            ];
        }

        if ($httpCode >= 500) {
            return [
                'status'           => 'DOWN',
                'http_code'        => $httpCode,
                'response_time_ms' => $elapsedMs,
                'error_message'    => 'Server error HTTP ' . $httpCode,
                'errno'            => 0,
            ];
        }

        return [
            'status'           => 'UP',
            'http_code'        => $httpCode,
            'response_time_ms' => $elapsedMs,
            'error_message'    => null,
            'errno'            => 0,
        ];
    }

    /**
     * @return array{status:string, http_code:int|null, response_time_ms:int|null, error_message:string|null, errno:int}
     */
    private function performHttpCheck(string $url, bool $verifySsl): array
    {
        $start = microtime(true);
        $ch    = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_CONNECTTIMEOUT => $this->timeoutSeconds,
            CURLOPT_TIMEOUT        => $this->timeoutSeconds,
            CURLOPT_NOBODY         => true,
            CURLOPT_USERAGENT      => self::USER_AGENT,
            CURLOPT_ENCODING       => '',
            CURLOPT_SSL_VERIFYPEER => $verifySsl,
            CURLOPT_SSL_VERIFYHOST => $verifySsl ? 2 : 0,
        ]);

        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno    = curl_errno($ch);
        $error    = curl_error($ch);

        // Some servers reject HEAD — fallback to GET.
        if ($errno === 0 && (int) $httpCode === 0) {
            curl_setopt($ch, CURLOPT_NOBODY, false);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $errno    = curl_errno($ch);
            $error    = curl_error($ch);
        }

        // If HEAD not allowed, retry GET.
        if ($errno === 0 && in_array((int) $httpCode, [405, 501], true)) {
            curl_setopt($ch, CURLOPT_NOBODY, false);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $errno    = curl_errno($ch);
            $error    = curl_error($ch);
        }

        $elapsedMs = (int) round((microtime(true) - $start) * 1000);
        curl_close($ch);

        return $this->evaluateProbe((int) $errno, (string) $error, (int) $httpCode, $elapsedMs);
    }

    private function isLocalSslIssuerProblem(string $error): bool
    {
        $error = strtolower($error);

        return str_contains($error, 'ssl certificate problem')
            || str_contains($error, 'unable to get local issuer certificate')
            || str_contains($error, 'certificate verify failed');
    }

    /**
     * Catat hasil pengecekan ke domain_checks:
     * - Status sama berturut-turut (UP→UP / DOWN→DOWN): update baris terakhir
     *   (checked_at + metrik), tanpa insert/delete.
     * - Status berubah (UP→DOWN / DOWN→UP) atau belum ada riwayat: insert baris baru
     *   (baris lama tidak dihapus).
     *
     * @param array{http_code:int|null, response_time_ms:int|null, error_message:string|null} $probe
     */
    private function recordCheck(int $domainId, string $status, string $checkedAt, array $probe): void
    {
        $payload = [
            'domain_id'        => $domainId,
            'checked_at'       => $checkedAt,
            'status'           => $status,
            'http_code'        => $probe['http_code'],
            'response_time_ms' => $probe['response_time_ms'],
            'error_message'    => $probe['error_message'],
        ];

        $latest = $this->checks
            ->where('domain_id', $domainId)
            ->orderBy('checked_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->first();

        if ($latest && ($latest['status'] ?? '') === $status) {
            // UP → UP / DOWN → DOWN: perbarui timestamp/metrik pada baris terakhir saja.
            $this->checks->update((int) $latest['id'], [
                'checked_at'       => $checkedAt,
                'http_code'        => $probe['http_code'],
                'response_time_ms' => $probe['response_time_ms'],
                'error_message'    => $probe['error_message'],
            ]);

            return;
        }

        // Status berubah atau belum ada riwayat: insert baris baru.
        $this->checks->insert($payload);
    }

    private function handleOutageTransition(int $domainId, string $oldStatus, string $newStatus, string $checkedAt): void
    {
        if ($newStatus === 'DOWN' && $oldStatus !== 'DOWN') {
            $open = $this->outages
                ->where('domain_id', $domainId)
                ->where('ended_at', null)
                ->orderBy('started_at', 'DESC')
                ->first();

            if (! $open) {
                $this->outages->insert([
                    'domain_id'        => $domainId,
                    'started_at'       => $checkedAt,
                    'ended_at'         => null,
                    'duration_seconds' => null,
                    'is_acknowledged'  => 0,
                ]);
            }

            return;
        }

        if ($newStatus === 'UP' && $oldStatus === 'DOWN') {
            $open = $this->outages
                ->where('domain_id', $domainId)
                ->where('ended_at', null)
                ->orderBy('started_at', 'DESC')
                ->first();

            if ($open) {
                $started = strtotime((string) $open['started_at']) ?: time();
                $ended   = strtotime($checkedAt) ?: time();

                $this->outages->update((int) $open['id'], [
                    'ended_at'         => $checkedAt,
                    'duration_seconds' => max(0, $ended - $started),
                ]);
            }
        }
    }

    /**
     * @param array<string, mixed> $domain
     */
    private function notifyContacts(array $domain, string $status, string $checkedAt, int $domainId): int
    {
        $contacts = $this->contacts
            ->where('domain_id', $domainId)
            ->findAll();

        if ($contacts === []) {
            return 0;
        }

        $downAt   = $checkedAt;
        $upAt     = '-';
        $duration = '-';

        if ($status === 'DOWN') {
            $open = $this->outages
                ->where('domain_id', $domainId)
                ->where('ended_at', null)
                ->orderBy('started_at', 'DESC')
                ->first();

            if ($open && ! empty($open['started_at'])) {
                $downAt = (string) $open['started_at'];
            }

            $upAt = '-';

            // Durasi down berjalan (dari mulai down sampai sekarang).
            $startedTs = strtotime($downAt) ?: time();
            $nowTs     = strtotime($checkedAt) ?: time();
            $duration  = $this->whatsapp->formatDuration(max(0, $nowTs - $startedTs));
        } else {
            // Status UP setelah recovery — ambil outage terakhir yang sudah ditutup.
            $closed = $this->outages
                ->where('domain_id', $domainId)
                ->where('ended_at !=', null)
                ->orderBy('ended_at', 'DESC')
                ->first();

            if ($closed) {
                $downAt = (string) ($closed['started_at'] ?? $checkedAt);
                $upAt   = (string) ($closed['ended_at'] ?? $checkedAt);

                if (! empty($closed['duration_seconds'])) {
                    $duration = $this->whatsapp->formatDuration((int) $closed['duration_seconds']);
                } else {
                    $startedTs = strtotime($downAt) ?: time();
                    $endedTs   = strtotime($upAt) ?: time();
                    $duration  = $this->whatsapp->formatDuration(max(0, $endedTs - $startedTs));
                }
            } else {
                $downAt   = '-';
                $upAt     = $checkedAt;
                $duration = '-';
            }
        }

        $message = $this->whatsapp->buildStatusChangeMessage(
            (string) $domain['domain_url'],
            $status,
            $downAt,
            $upAt,
            $duration
        );

        $sent = 0;

        foreach ($contacts as $contact) {
            $result = $this->whatsapp->sendMessage((string) $contact['phone_number'], $message);
            if ($result['success']) {
                $sent++;
            } else {
                log_message('error', 'Monitor WA notify failed for {phone}: {error}', [
                    'phone' => $contact['phone_number'],
                    'error' => $result['error'] ?? $result['response'] ?? 'unknown',
                ]);
            }
        }

        return $sent;
    }
}
