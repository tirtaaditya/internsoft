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
