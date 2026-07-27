<?php

namespace App\Controllers;

use App\Models\DomainCheckModel;
use App\Models\DomainContactModel;
use App\Models\DomainModel;
use App\Models\OutageEventModel;

class DashboardController extends BaseController
{
    public function index()
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $userId      = (int) session()->get('auth_user_id');
        $domainModel = new DomainModel();

        $domains = $domainModel
            ->where('user_id', $userId)
            ->orderBy('id', 'DESC')
            ->findAll();

        $domainIds = array_column($domains, 'id');
        $contactCountByDomain = [];

        if ($domainIds !== []) {
            $rows = db_connect()->table('domain_contacts')
                ->select('domain_id, COUNT(*) AS total')
                ->whereIn('domain_id', $domainIds)
                ->groupBy('domain_id')
                ->get()
                ->getResultArray();

            foreach ($rows as $row) {
                $contactCountByDomain[(int) $row['domain_id']] = (int) $row['total'];
            }
        }

        $total  = count($domains);
        $active = 0;
        $up     = 0;
        $down   = 0;

        foreach ($domains as $domain) {
            if ((int) $domain['is_active'] === 1) {
                $active++;
            }

            if ($domain['last_status'] === 'UP') {
                $up++;
            } elseif ($domain['last_status'] === 'DOWN') {
                $down++;
            }
        }

        return view('dashboard/index', [
            'title'                => 'Dashboard',
            'name'                 => (string) session()->get('auth_user_name'),
            'domains'              => $domains,
            'contactCountByDomain' => $contactCountByDomain,
            'stats'                => [
                'total'  => $total,
                'active' => $active,
                'up'     => $up,
                'down'   => $down,
            ],
        ]);
    }

    public function showDomain(int $id)
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        $domain = $this->findOwnedDomain($id);
        if (! $domain) {
            return redirect()->to('dashboard')->with('error', 'Domain tidak ditemukan.');
        }

        $contacts = (new DomainContactModel())
            ->where('domain_id', $id)
            ->orderBy('id', 'ASC')
            ->findAll();

        $since = date('Y-m-d H:i:s', strtotime('-7 days'));

        $outages = (new OutageEventModel())
            ->where('domain_id', $id)
            ->groupStart()
                ->where('started_at >=', $since)
                ->orWhere('ended_at', null)
            ->groupEnd()
            ->orderBy('started_at', 'DESC')
            ->findAll(100);

        $checks = (new DomainCheckModel())
            ->where('domain_id', $id)
            ->where('checked_at >=', $since)
            ->orderBy('checked_at', 'DESC')
            ->findAll(100);

        $history = [];

        foreach ($outages as $outage) {
            $isOpen = empty($outage['ended_at']);
            $history[] = [
                'type'    => 'outage',
                'status'  => $isOpen ? 'DOWN' : 'RECOVERED',
                'title'   => $isOpen ? 'Domain DOWN' : 'Domain kembali UP',
                'at'      => $isOpen ? $outage['started_at'] : $outage['ended_at'],
                'started' => $outage['started_at'],
                'ended'   => $outage['ended_at'],
                'duration'=> $outage['duration_seconds'],
            ];
        }

        foreach ($checks as $check) {
            $history[] = [
                'type'    => 'check',
                'status'  => $check['status'],
                'title'   => 'Pengecekan ' . $check['status'],
                'at'      => $check['checked_at'],
                'http'    => $check['http_code'],
                'ms'      => $check['response_time_ms'],
                'error'   => $check['error_message'],
            ];
        }

        usort($history, static function (array $a, array $b): int {
            return strcmp((string) $b['at'], (string) $a['at']);
        });

        return view('dashboard/domain', [
            'title'         => 'Detail Domain',
            'name'          => (string) session()->get('auth_user_name'),
            'domain'        => $domain,
            'contacts'      => $contacts,
            'history'       => $history,
            'outages'       => $outages,
            'retentionDays' => 7,
        ]);
    }

    public function storeDomain()
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        if (! $this->request->is('post')) {
            return redirect()->to('dashboard');
        }

        $rules = [
            'domain_url' => [
                'rules'  => 'required|min_length[3]|max_length[255]',
                'errors' => [
                    'required' => 'URL domain wajib diisi.',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('dashboard')->with('error', implode("\n", $this->validator->getErrors()));
        }

        $userId    = (int) session()->get('auth_user_id');
        $domainUrl = $this->normalizeDomainUrl((string) $this->request->getPost('domain_url'));

        if ($domainUrl === '') {
            return redirect()->to('dashboard')->with('error', 'URL domain tidak valid.');
        }

        $domainModel = new DomainModel();
        $exists = $domainModel
            ->where('user_id', $userId)
            ->where('domain_url', $domainUrl)
            ->first();

        if ($exists) {
            return redirect()->to('dashboard')->with('error', 'Domain sudah ada di daftar Anda.');
        }

        try {
            $domainModel->insert([
                'user_id'    => $userId,
                'domain_url' => $domainUrl,
                'is_active'  => 1,
                'last_status'=> 'UNKNOWN',
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Store domain failed: {message}', ['message' => $e->getMessage()]);
            return redirect()->to('dashboard')->with('error', 'Gagal menambah domain. Cek koneksi database.');
        }

        return redirect()->to('dashboard')->with('success', 'Domain berhasil ditambahkan.');
    }

    public function updateDomain(int $id)
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        if (! $this->request->is('post')) {
            return redirect()->to('dashboard');
        }

        $domain = $this->findOwnedDomain($id);
        if (! $domain) {
            return redirect()->to('dashboard')->with('error', 'Domain tidak ditemukan.');
        }

        $rules = [
            'domain_url' => [
                'rules'  => 'required|min_length[3]|max_length[255]',
                'errors' => [
                    'required' => 'URL domain wajib diisi.',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('dashboard')->with('error', implode("\n", $this->validator->getErrors()));
        }

        $userId    = (int) session()->get('auth_user_id');
        $domainUrl = $this->normalizeDomainUrl((string) $this->request->getPost('domain_url'));

        if ($domainUrl === '') {
            return redirect()->to('dashboard')->with('error', 'URL domain tidak valid.');
        }

        $domainModel = new DomainModel();
        $exists = $domainModel
            ->where('user_id', $userId)
            ->where('domain_url', $domainUrl)
            ->where('id !=', $id)
            ->first();

        if ($exists) {
            return redirect()->to('dashboard')->with('error', 'Domain sudah ada di daftar Anda.');
        }

        $domainModel->update($id, [
            'domain_url' => $domainUrl,
        ]);

        return redirect()->to('/dashboard/domains/' . $id)->with('success', 'Domain berhasil diperbarui.');
    }

    public function toggleDomain(int $id)
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        if (! $this->request->is('post')) {
            return redirect()->to('dashboard');
        }

        $domain = $this->findOwnedDomain($id);
        if (! $domain) {
            return redirect()->to('dashboard')->with('error', 'Domain tidak ditemukan.');
        }

        $next = (int) $domain['is_active'] === 1 ? 0 : 1;
        (new DomainModel())->update($id, ['is_active' => $next]);

        $message = $next === 1 ? 'Monitoring domain diaktifkan.' : 'Monitoring domain dinonaktifkan.';

        return redirect()->to('dashboard')->with('success', $message);
    }

    public function checkDomain(int $id)
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        if (! $this->request->is('post')) {
            return redirect()->to('dashboard');
        }

        $domain = $this->findOwnedDomain($id);
        if (! $domain) {
            return redirect()->to('dashboard')->with('error', 'Domain tidak ditemukan.');
        }

        if ((int) $domain['is_active'] !== 1) {
            return redirect()->to('/dashboard/domains/' . $id)->with('error', 'Domain sedang dijeda. Aktifkan dulu sebelum dicek.');
        }

        try {
            $result = (new \App\Services\MonitorService())->run($id);
            $row    = $result['results'][0] ?? null;

            if (! $row) {
                return redirect()->to('/dashboard/domains/' . $id)->with('error', 'Pengecekan gagal.');
            }

            $msg = sprintf(
                'Hasil cek: %s (HTTP %s, %s ms).',
                $row['status'],
                $row['http_code'] ?? '-',
                $row['response_time_ms'] ?? '-'
            );

            return redirect()->to('/dashboard/domains/' . $id)->with('success', $msg);
        } catch (\Throwable $e) {
            log_message('error', 'Manual check failed: {message}', ['message' => $e->getMessage()]);

            return redirect()->to('/dashboard/domains/' . $id)->with('error', 'Pengecekan gagal: ' . $e->getMessage());
        }
    }

    public function checkAllDomains()
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        if (! $this->request->is('post')) {
            return redirect()->to('dashboard');
        }

        $userId = (int) session()->get('auth_user_id');

        try {
            $service = new \App\Services\MonitorService();
            $domains = (new \App\Models\DomainModel())
                ->where('user_id', $userId)
                ->where('is_active', 1)
                ->findAll();

            $checked = 0;
            $up      = 0;
            $down    = 0;

            // Cek paralel per batch 15 domain.
            foreach (array_chunk($domains, 15) as $batch) {
                $probes = $service->checkUrlBatch($batch);

                foreach ($batch as $domain) {
                    $probe = $probes[(int) $domain['id']] ?? null;
                    $row   = $service->processDomain($domain, $probe);
                    $checked++;
                    if ($row['status'] === 'UP') {
                        $up++;
                    } else {
                        $down++;
                    }
                }
            }

            $service->pruneOldHistory(7);

            return redirect()->to('dashboard')->with(
                'success',
                "Pengecekan selesai: {$checked} domain (UP {$up}, DOWN {$down})."
            );
        } catch (\Throwable $e) {
            log_message('error', 'Manual check-all failed: {message}', ['message' => $e->getMessage()]);

            return redirect()->to('dashboard')->with('error', 'Pengecekan gagal: ' . $e->getMessage());
        }
    }

    public function deleteDomain(int $id)
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        if (! $this->request->is('post')) {
            return redirect()->to('dashboard');
        }

        $domain = $this->findOwnedDomain($id);
        if (! $domain) {
            return redirect()->to('dashboard')->with('error', 'Domain tidak ditemukan.');
        }

        (new DomainModel())->delete($id);

        return redirect()->to('dashboard')->with('success', 'Domain berhasil dihapus.');
    }

    public function storeContact(int $domainId)
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        if (! $this->request->is('post')) {
            return redirect()->to('dashboard');
        }

        $domain = $this->findOwnedDomain($domainId);
        if (! $domain) {
            return redirect()->to('dashboard')->with('error', 'Domain tidak ditemukan.');
        }

        $rules = [
            'phone_number' => [
                'rules'  => 'required|regex_match[/^0?[1-9][0-9]{7,13}$/]',
                'errors' => [
                    'required'    => 'Nomor WhatsApp wajib diisi.',
                    'regex_match' => 'Nomor WhatsApp tidak valid. Contoh: 81234567890.',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/dashboard/domains/' . $domainId)->with('error', implode("\n", $this->validator->getErrors()));
        }

        $phone = $this->normalizeWaNumber((string) $this->request->getPost('phone_number'));
        $contactModel = new DomainContactModel();

        $exists = $contactModel
            ->where('domain_id', $domainId)
            ->where('phone_number', $phone)
            ->first();

        if ($exists) {
            return redirect()->to('/dashboard/domains/' . $domainId)->with('error', 'Nomor WhatsApp sudah terdaftar untuk domain ini.');
        }

        $contactModel->insert([
            'domain_id'    => $domainId,
            'phone_number' => $phone,
        ]);

        return redirect()->to('/dashboard/domains/' . $domainId)->with('success', 'Nomor WhatsApp berhasil ditambahkan.');
    }

    public function updateContact(int $id)
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        if (! $this->request->is('post')) {
            return redirect()->to('dashboard');
        }

        $contact = $this->findOwnedContact($id);
        if (! $contact) {
            return redirect()->to('dashboard')->with('error', 'Kontak WhatsApp tidak ditemukan.');
        }

        $domainId = (int) $contact['domain_id'];

        $rules = [
            'phone_number' => [
                'rules'  => 'required|regex_match[/^0?[1-9][0-9]{7,13}$/]',
                'errors' => [
                    'required'    => 'Nomor WhatsApp wajib diisi.',
                    'regex_match' => 'Nomor WhatsApp tidak valid. Contoh: 81234567890.',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/dashboard/domains/' . $domainId)->with('error', implode("\n", $this->validator->getErrors()));
        }

        $phone = $this->normalizeWaNumber((string) $this->request->getPost('phone_number'));
        $contactModel = new DomainContactModel();

        $exists = $contactModel
            ->where('domain_id', $domainId)
            ->where('phone_number', $phone)
            ->where('id !=', $id)
            ->first();

        if ($exists) {
            return redirect()->to('/dashboard/domains/' . $domainId)->with('error', 'Nomor WhatsApp sudah terdaftar untuk domain ini.');
        }

        $contactModel->update($id, [
            'phone_number' => $phone,
        ]);

        return redirect()->to('/dashboard/domains/' . $domainId)->with('success', 'Nomor WhatsApp berhasil diperbarui.');
    }

    public function deleteContact(int $id)
    {
        if ($redirect = $this->requireLogin()) {
            return $redirect;
        }

        if (! $this->request->is('post')) {
            return redirect()->to('dashboard');
        }

        $contact = $this->findOwnedContact($id);
        if (! $contact) {
            return redirect()->to('dashboard')->with('error', 'Kontak WhatsApp tidak ditemukan.');
        }

        $domainId = (int) $contact['domain_id'];
        (new DomainContactModel())->delete($id);

        return redirect()->to('/dashboard/domains/' . $domainId)->with('success', 'Nomor WhatsApp berhasil dihapus.');
    }

    private function requireLogin()
    {
        if (! session()->get('is_logged_in')) {
            return redirect()->to('login');
        }

        return null;
    }

    private function findOwnedDomain(int $id): ?array
    {
        $userId = (int) session()->get('auth_user_id');

        return (new DomainModel())
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    private function findOwnedContact(int $id): ?array
    {
        $userId = (int) session()->get('auth_user_id');
        $db      = db_connect();

        $row = $db->table('domain_contacts c')
            ->select('c.*')
            ->join('domains d', 'd.id = c.domain_id')
            ->where('c.id', $id)
            ->where('d.user_id', $userId)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    private function normalizeDomainUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        if (! preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }

        $parts = parse_url($url);
        if ($parts === false || empty($parts['host'])) {
            return '';
        }

        $scheme = strtolower($parts['scheme'] ?? 'https');
        $host   = strtolower($parts['host']);
        $port   = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path   = $parts['path'] ?? '';

        if ($path === '/' || $path === '') {
            $path = '';
        } else {
            $path = rtrim($path, '/');
        }

        return $scheme . '://' . $host . $port . $path;
    }

    private function normalizeWaNumber(string $waNumber): string
    {
        $digits = preg_replace('/\D/', '', trim($waNumber)) ?? '';

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '62')) {
            return $digits;
        }

        return '62' . ltrim($digits, '0');
    }
}
