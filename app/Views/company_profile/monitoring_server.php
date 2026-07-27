<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="seo-page">
    <header class="seo-top">
        <div class="container seo-top-inner">
            <a href="<?= base_url('/') ?>" class="logo-area">
                <img src="<?= base_url('assets/img/logo-internsoft.png') ?>" alt="Internsoft Technology Solutions" class="logo-img">
                <span class="logo-text">Internsoft</span>
            </a>
            <div class="seo-top-actions">
                <a href="<?= base_url('/') ?>" class="btn btn-outline">Beranda</a>
                <a href="<?= base_url('register') ?>" class="btn btn-primary">Daftar</a>
            </div>
        </div>
    </header>

    <section class="seo-hero">
        <div class="container seo-hero-grid">
            <div class="seo-hero-copy">
                <p class="kicker">Jasa Monitoring Server Indonesia</p>
                <div class="seo-hero-title-row">
                    <h1>Monitoring Server 24/7 dengan Notifikasi WhatsApp</h1>
                    <span class="seo-gratis-badge">Gratis</span>
                </div>
                <p class="seo-gratis-line">Layanan monitoring server <strong>gratis</strong> — tanpa biaya berlangganan.</p>
                <p class="seo-lead">
                    Pantau uptime website dan server Anda secara otomatis.
                    Saat situs <strong>down</strong> atau kembali <strong>up</strong>,
                    Internsoft langsung kirim peringatan ke WhatsApp — cepat, jelas, tanpa ribet.
                </p>
                <div class="seo-hero-actions">
                    <a href="<?= base_url('register') ?>" class="btn btn-primary">
                        Daftar Gratis
                    </a>
                    <a href="<?= base_url('login') ?>" class="btn btn-outline">Masuk Dashboard</a>
                </div>
                <p class="seo-hero-secondary">
                    Ada pertanyaan? <a href="<?= esc($waUrl) ?>" target="_blank" rel="noopener">Chat WhatsApp</a>
                </p>
                <ul class="seo-hero-points">
                    <li>100% gratis dipakai</li>
                    <li>Cek berkala otomatis</li>
                    <li>Alert WA saat status berubah</li>
                    <li>Riwayat downtime tersimpan</li>
                </ul>
            </div>
            <div class="seo-hero-visual">
                <img
                    src="<?= base_url('assets/img/tentang-kami-monitoring-server.png') ?>"
                    alt="Ilustrasi jasa monitoring server Internsoft"
                    width="560"
                    height="360"
                    loading="eager"
                >
            </div>
        </div>
    </section>

    <section class="seo-section" id="manfaat">
        <div class="container">
            <div class="section-head center">
                <p class="kicker">Kenapa Perlu</p>
                <h2>Manfaat Monitoring Server untuk Bisnis</h2>
                <p>Website offline tanpa diketahui bisa merugikan penjualan, reputasi, dan kepercayaan pelanggan.</p>
            </div>
            <div class="seo-cards">
                <article class="seo-card">
                    <h3>Deteksi gangguan lebih cepat</h3>
                    <p>Tim Anda tahu lebih awal saat server atau domain bermasalah, bukan menunggu laporan pelanggan.</p>
                </article>
                <article class="seo-card">
                    <h3>Notifikasi langsung ke WhatsApp</h3>
                    <p>Tidak perlu buka dashboard terus-menerus. Status DOWN/UP dikirim otomatis ke nomor yang Anda tentukan.</p>
                </article>
                <article class="seo-card">
                    <h3>Gratis untuk dipakai</h3>
                    <p>Monitoring server Internsoft bisa Anda gunakan secara <strong>gratis</strong> — fokus pantau uptime tanpa biaya berlangganan.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="seo-section seo-section-alt" id="cara-kerja">
        <div class="container">
            <div class="section-head center">
                <p class="kicker">Cara Kerja</p>
                <h2>Bagaimana Sistem Monitoring Server Internsoft Bekerja</h2>
            </div>
            <ol class="seo-steps">
                <li>
                    <strong>Tambah domain</strong>
                    <span>Daftarkan URL website/server yang ingin dipantau di dashboard.</span>
                </li>
                <li>
                    <strong>Pasang nomor WhatsApp</strong>
                    <span>Tentukan penerima notifikasi untuk setiap domain.</span>
                </li>
                <li>
                    <strong>Pengecekan otomatis</strong>
                    <span>Sistem mengecek ketersediaan secara berkala dan mencatat status UP/DOWN.</span>
                </li>
                <li>
                    <strong>Alert saat berubah</strong>
                    <span>Hanya saat status berubah, WhatsApp dikirim — supaya tidak spam.</span>
                </li>
            </ol>
        </div>
    </section>

    <section class="seo-section" id="fitur">
        <div class="container seo-split">
            <div>
                <p class="kicker">Fitur Utama</p>
                <h2>Yang Anda Dapatkan dari Jasa Monitoring Server Kami</h2>
                <ul class="seo-list">
                    <li>Monitoring uptime website/domain</li>
                    <li>Status UP / DOWN / belum dicek</li>
                    <li>Notifikasi WhatsApp on status change</li>
                    <li>Durasi downtime di pesan WA</li>
                    <li>Riwayat pengecekan & outage</li>
                    <li>Multi nomor WhatsApp per domain</li>
                </ul>
                <a href="<?= base_url('register') ?>" class="btn btn-primary">Daftar Gratis</a>
            </div>
            <aside class="seo-aside-box">
                <h3>Siap mulai monitoring?</h3>
                <p>Daftar akun lalu tambahkan domain Anda di dashboard — layanan monitoringnya <strong>gratis</strong>.</p>
                <a href="<?= base_url('register') ?>" class="btn btn-primary btn-block">Daftar Sekarang</a>
                <p class="seo-aside-note">
                    Gratis dipakai · Sudah punya akun? <a href="<?= base_url('login') ?>">Masuk</a>
                    · Ada pertanyaan? <a href="<?= esc($waUrl) ?>" target="_blank" rel="noopener">Chat WhatsApp</a>
                </p>
            </aside>
        </div>
    </section>

    <section class="seo-section seo-section-alt" id="faq">
        <div class="container">
            <div class="section-head center">
                <p class="kicker">FAQ</p>
                <h2>Pertanyaan Umum Monitoring Server</h2>
            </div>
            <div class="seo-faq">
                <details open>
                    <summary>Apa itu monitoring server?</summary>
                    <p>Monitoring server adalah layanan memantau ketersediaan server atau website secara berkala agar gangguan bisa diketahui lebih cepat.</p>
                </details>
                <details>
                    <summary>Apakah ada notifikasi WhatsApp saat server down?</summary>
                    <p>Ya. Internsoft mengirim notifikasi WhatsApp otomatis saat status berubah menjadi DOWN atau kembali UP, termasuk waktu dan durasi gangguan.</p>
                </details>
                <details>
                    <summary>Apakah cocok untuk UMKM dan perusahaan?</summary>
                    <p>Cocok untuk keduanya — terutama yang mengandalkan website, portal, atau sistem online yang harus selalu tersedia.</p>
                </details>
                <details>
                    <summary>Apakah monitoring server Internsoft gratis?</summary>
                    <p>Ya. Layanan monitoring server Internsoft dapat digunakan secara <strong>gratis</strong>. Daftar akun di dashboard untuk mulai memakai.</p>
                </details>
                <details>
                    <summary>Bagaimana cara mulai?</summary>
                    <p>Daftar akun gratis, lalu tambahkan domain yang ingin dipantau di dashboard. Ada pertanyaan? Chat WhatsApp Internsoft untuk bantuan.</p>
                </details>
            </div>
        </div>
    </section>

    <section class="seo-cta-final">
        <div class="container">
            <h2>Siap pantau server Anda lebih awas — gratis?</h2>
            <p>Daftar sekarang dan mulai monitoring server gratis di dashboard Internsoft.</p>
            <a href="<?= base_url('register') ?>" class="btn btn-primary">Daftar Gratis</a>
            <p class="seo-cta-secondary">
                Ada pertanyaan? <a href="<?= esc($waUrl) ?>" target="_blank" rel="noopener">Chat WhatsApp</a>
            </p>
        </div>
    </section>

    <footer class="seo-footer">
        <div class="container">
            <p>
                <a href="<?= base_url('/') ?>">Internsoft Technology Solutions</a>
                · <a href="<?= base_url('monitoring-server') ?>">Monitoring Server</a>
                · <a href="<?= base_url('register') ?>">Daftar</a>
                · <a href="<?= esc($waUrl) ?>" target="_blank" rel="noopener">Chat WhatsApp</a>
            </p>
            <p class="copyright">© <?= date('Y') ?> Internsoft. Halaman SEO jasa monitoring server.</p>
        </div>
    </footer>

    <a href="<?= esc($waUrl) ?>" class="seo-wa-float" target="_blank" rel="noopener" aria-label="Chat WhatsApp Internsoft">
        WA
    </a>
</div>
<?= $this->endSection() ?>
