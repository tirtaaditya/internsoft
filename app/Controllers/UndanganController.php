<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

class UndanganController extends Controller
{
    private string $file;

    public function __construct()
    {
        $this->file = WRITEPATH . 'undangan-komentar.txt';
    }

    /** GET /api/undangan/komentar */
    public function getKomentar(): ResponseInterface
    {
        $komentar = $this->readAll();
        return $this->response
            ->setContentType('application/json')
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setJSON(['data' => $komentar]);
    }

    /** POST /api/undangan/komentar */
    public function simpanKomentar(): ResponseInterface
    {
        $this->response->setHeader('Access-Control-Allow-Origin', '*');

        $nama  = trim($this->request->getPost('nama')  ?? '');
        $pesan = trim($this->request->getPost('pesan') ?? '');

        if ($nama === '' || $pesan === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'error' => 'Nama dan pesan tidak boleh kosong.',
            ]);
        }

        // Batasi panjang
        $nama  = mb_substr($nama,  0, 80);
        $pesan = mb_substr($pesan, 0, 500);

        $entry = json_encode([
            'nama'  => $nama,
            'pesan' => $pesan,
            'waktu' => date('Y-m-d H:i:s'),
        ], JSON_UNESCAPED_UNICODE);

        file_put_contents($this->file, $entry . "\n", FILE_APPEND | LOCK_EX);

        return $this->response->setStatusCode(201)->setJSON(['ok' => true]);
    }

    // ── Private ──────────────────────────────────────────────────────────

    private function readAll(): array
    {
        if (!file_exists($this->file)) {
            return [];
        }

        $lines = file($this->file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $result = [];
        foreach (array_reverse($lines) as $line) {   // terbaru di atas
            $obj = json_decode($line, true);
            if ($obj) {
                $result[] = $obj;
            }
        }
        return $result;
    }
}
