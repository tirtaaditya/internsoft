<?php

namespace App\Controllers;

class CompanyProfileController extends BaseController
{
    public function index()
    {
        return view('company_profile/index', [
            'title' => 'Internsoft Technology Solutions | Solusi Teknologi & Monitoring Server',
            'metaDescription' => 'Internsoft menyediakan monitoring server, API WhatsApp, pembuatan aplikasi, dan instalasi komputer. Solusi teknologi andal untuk bisnis Anda.',
            'metaKeywords' => 'internsoft, monitoring server, jasa monitoring server, uptime monitoring',
            'canonicalUrl' => base_url('/'),
            'companyName' => 'Internsoft Technology Solutions',
            'domain' => 'Internsoft.my.id',
            'comingSoonFeature' => 'API WhatsApp',
        ]);
    }

    public function undanganDigital()
    {
        $canonical = base_url('undangan-digital');
        $waUrl = 'https://wa.me/6285655666546?text=' . rawurlencode(
            'Halo Internsoft, saya ingin pesan/konsultasi undangan digital.'
        );

        $faqs = [
            ['q' => 'Berapa lama proses pembuatan undangan?', 'a' => 'Rata-rata 1–3 jam kerja setelah data lengkap diterima. Untuk desain custom yang kompleks bisa 1 hari kerja.'],
            ['q' => 'Apakah bisa request desain sendiri?', 'a' => 'Ya! Kamu bisa request tema, warna, font, foto, bahkan layout sesuai keinginan. Tim kami akan mewujudkannya.'],
            ['q' => 'Bagaimana cara kerja QR buku tamu?', 'a' => 'Kami buatkan QR code unik untuk acaramu. Tamu cukup scan QR → isi nama & konfirmasi kehadiran → data langsung tersimpan.'],
            ['q' => 'Bagaimana cara membagikan undangan?', 'a' => 'Undangan berupa link URL yang bisa dikirim via WhatsApp, Instagram, SMS, atau platform apa saja. Satu link untuk semua tamu.'],
            ['q' => 'Apakah nama tamu bisa berbeda-beda tiap link?', 'a' => 'Ya, tersedia di paket Premium ke atas. Setiap tamu mendapat link personal dengan namanya masing-masing.'],
            ['q' => 'Berapa lama link undangan aktif?', 'a' => 'Tergantung paket: Starter 3 bulan, Premium 6 bulan, Eksklusif 1 tahun. Bisa diperpanjang dengan biaya tambahan.'],
            ['q' => 'Bagaimana cara pembayaran?', 'a' => 'Transfer bank / e-wallet setelah deal. Undangan dikirim setelah pembayaran dikonfirmasi.'],
        ];

        $jsonLd = json_encode([
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Service',
                    'name' => 'Jasa Undangan Digital',
                    'serviceType' => 'Digital Invitation',
                    'provider' => [
                        '@type' => 'Organization',
                        'name' => 'Internsoft Technology Solutions',
                        'url' => base_url('/'),
                        'telephone' => '+6285655666546',
                    ],
                    'areaServed' => 'ID',
                    'description' => 'Jasa pembuatan undangan digital murah untuk pernikahan, khitan, ulang tahun. Lengkap dengan QR buku tamu, kirim via WA, desain custom.',
                    'url' => $canonical,
                    'offers' => [
                        '@type' => 'AggregateOffer',
                        'lowPrice' => '10000',
                        'highPrice' => '50000',
                        'priceCurrency' => 'IDR',
                    ],
                ],
                [
                    '@type' => 'FAQPage',
                    'mainEntity' => array_map(fn($f) => [
                        '@type' => 'Question',
                        'name' => $f['q'],
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
                    ], $faqs),
                ],
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return view('company_profile/undangan-digital', [
            'title'           => 'Jasa Undangan Digital Murah & Modern — Internsoft',
            'metaDescription' => 'Pesan undangan digital murah mulai Rp50.000. Desain custom, kirim via WhatsApp, QR buku tamu, animasi modern. Tersedia untuk pernikahan, khitan, ulang tahun.',
            'metaKeywords'    => 'undangan digital, undangan online murah, undangan pernikahan digital, undangan khitan digital, undangan digital indonesia, jasa undangan digital',
            'canonicalUrl'    => $canonical,
            'ogTitle'         => 'Jasa Undangan Digital Murah & Modern — Internsoft',
            'ogDescription'   => 'Undangan digital mulai Rp50.000. Kirim via WA, QR buku tamu, desain custom. Pesan sekarang!',
            'ogImage'         => base_url('assets/img/tentang-kami-undangan-digital.png'),
            'jsonLd'          => $jsonLd,
            'waUrl'           => $waUrl,
            'faqs'            => $faqs,
        ]);
    }

    public function monitoringServer()
    {
        $canonical = base_url('monitoring-server');
        $waUrl = 'https://wa.me/6285655666546?text=' . rawurlencode(
            'Halo Internsoft, saya ingin konsultasi/info tentang jasa monitoring server.'
        );

        $jsonLd = json_encode([
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Service',
                    'name' => 'Jasa Monitoring Server',
                    'serviceType' => 'Server Monitoring',
                    'provider' => [
                        '@type' => 'Organization',
                        'name' => 'Internsoft Technology Solutions',
                        'url' => base_url('/'),
                        'telephone' => '+6285655666546',
                    ],
                    'areaServed' => 'ID',
                    'description' => 'Layanan monitoring server dan website 24/7 gratis dengan notifikasi WhatsApp saat situs down atau up.',
                    'url' => $canonical,
                ],
                [
                    '@type' => 'FAQPage',
                    'mainEntity' => [
                        [
                            '@type' => 'Question',
                            'name' => 'Apa itu monitoring server?',
                            'acceptedAnswer' => [
                                '@type' => 'Answer',
                                'text' => 'Monitoring server adalah layanan yang memantau ketersediaan (uptime) server atau website secara berkala, lalu memberi peringatan jika terjadi gangguan.',
                            ],
                        ],
                        [
                            '@type' => 'Question',
                            'name' => 'Apakah ada notifikasi WhatsApp saat server down?',
                            'acceptedAnswer' => [
                                '@type' => 'Answer',
                                'text' => 'Ya. Internsoft mengirim notifikasi WhatsApp otomatis saat status domain berubah menjadi DOWN atau kembali UP, lengkap dengan waktu dan durasi gangguan.',
                            ],
                        ],
                        [
                            '@type' => 'Question',
                            'name' => 'Apakah monitoring server Internsoft gratis?',
                            'acceptedAnswer' => [
                                '@type' => 'Answer',
                                'text' => 'Ya. Layanan monitoring server Internsoft dapat digunakan secara gratis. Daftar akun di dashboard untuk mulai memakai.',
                            ],
                        ],
                        [
                            '@type' => 'Question',
                            'name' => 'Bagaimana cara mulai memakai monitoring server Internsoft?',
                            'acceptedAnswer' => [
                                '@type' => 'Answer',
                                'text' => 'Daftar akun gratis, lalu tambahkan domain yang ingin dipantau di dashboard monitoring. Ada pertanyaan? Chat WhatsApp Internsoft untuk bantuan.',
                            ],
                        ],
                    ],
                ],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return view('company_profile/monitoring_server', [
            'title' => 'Jasa Monitoring Server Indonesia | Notifikasi WhatsApp - Internsoft',
            'metaDescription' => 'Jasa monitoring server & website 24/7 gratis dari Internsoft. Pantau uptime domain, dapatkan notifikasi WhatsApp otomatis saat down/up. Daftar gratis sekarang.',
            'metaKeywords' => 'monitoring server, jasa monitoring server, monitoring website, uptime monitoring, notifikasi whatsapp server down, monitoring server indonesia',
            'canonicalUrl' => $canonical,
            'ogTitle' => 'Jasa Monitoring Server Indonesia | Internsoft',
            'ogDescription' => 'Monitoring server & website 24/7 gratis. Notifikasi WhatsApp otomatis saat down/up. Daftar gratis sekarang.',
            'ogImage' => base_url('assets/img/tentang-kami-monitoring-server.png'),
            'waUrl' => $waUrl,
            'jsonLd' => $jsonLd,
        ]);
    }
}
