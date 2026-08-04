<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<style>
/* ── Page-specific overrides ── */
.ud-hero {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    background:
        radial-gradient(ellipse 55% 45% at 75% 50%, rgba(180,100,255,.14), transparent 70%),
        radial-gradient(ellipse 40% 35% at 15% 80%, rgba(90,60,200,.12), transparent 68%),
        #081327;
    position: relative;
    overflow: hidden;
}
.ud-hero-grid {
    position: absolute; inset: 0;
    background-image:
        linear-gradient(rgba(160,110,255,.05) 1px, transparent 1px),
        linear-gradient(90deg, rgba(160,110,255,.05) 1px, transparent 1px);
    background-size: 48px 48px;
    pointer-events: none;
}
.ud-hero-body {
    flex: 1;
    display: flex;
    align-items: center;
    padding: 120px 40px 80px;
    max-width: 1180px;
    margin: 0 auto;
    width: 100%;
    gap: 60px;
}
.ud-hero-copy { flex: 1; }
.ud-hero-copy .kicker { color: #c084fc; }
.ud-hero-copy h1 {
    font-size: clamp(2rem, 4.5vw, 3.6rem);
    line-height: 1.15;
    letter-spacing: -.02em;
    margin: 0 0 20px;
}
.ud-hero-copy h1 .accent { color: #c084fc; }
.ud-hero-copy > p {
    color: var(--muted);
    line-height: 1.85;
    max-width: 52ch;
    font-size: 1rem;
    margin: 0 0 36px;
}
.ud-hero-img {
    flex: 0 0 420px;
    max-width: 420px;
    animation: floatY 7s ease-in-out infinite;
}
.ud-hero-img img { width: 100%; display: block; filter: drop-shadow(0 24px 48px rgba(160,100,255,.3)); }

/* ── Features ── */
.ud-features {
    padding: 80px 24px;
    background: radial-gradient(ellipse 50% 40% at 50% 0%, rgba(160,100,255,.07), transparent 70%), #081327;
}
.ud-features .container { max-width: 1180px; margin: 0 auto; }
.ud-features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
    gap: 20px;
    margin-top: 44px;
}
.ud-feat-card {
    background: linear-gradient(180deg, rgba(24,16,46,.95) 0%, rgba(14,10,30,.98) 100%);
    border: 1px solid rgba(140,80,255,.3);
    border-radius: 18px;
    padding: 28px 24px;
    transition: transform .25s ease, border-color .25s ease, box-shadow .25s ease;
}
.ud-feat-card:hover {
    transform: translateY(-5px);
    border-color: rgba(192,132,252,.55);
    box-shadow: 0 18px 42px rgba(100,40,200,.2);
}
.ud-feat-icon { font-size: 2.4rem; margin-bottom: 14px; }
.ud-feat-card h3 { font-size: 1.05rem; margin: 0 0 8px; }
.ud-feat-card p { color: var(--muted); font-size: .9rem; line-height: 1.65; margin: 0; }

/* ── Pricing ── */
.ud-pricing {
    padding: 80px 24px;
    background: #060e1e;
    text-align: center;
}
.ud-pricing .container { max-width: 900px; margin: 0 auto; }
.ud-price-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-top: 44px;
}
.ud-price-card {
    background: linear-gradient(180deg, rgba(22,14,44,.95) 0%, rgba(12,8,26,.98) 100%);
    border: 1px solid rgba(100,60,200,.35);
    border-radius: 20px;
    padding: 32px 24px;
    position: relative;
    transition: transform .25s ease, box-shadow .25s ease;
}
.ud-price-card:hover { transform: translateY(-5px); box-shadow: 0 20px 50px rgba(100,40,200,.25); }
.ud-price-card.popular {
    border-color: rgba(192,132,252,.7);
    box-shadow: 0 0 0 1px rgba(192,132,252,.2), 0 20px 50px rgba(100,40,200,.2);
}
.popular-badge {
    position: absolute;
    top: -13px;
    left: 50%;
    transform: translateX(-50%);
    background: linear-gradient(135deg, #a855f7, #7c3aed);
    color: #fff;
    font-size: .7rem;
    font-weight: 700;
    padding: 4px 14px;
    border-radius: 20px;
    letter-spacing: 1px;
    text-transform: uppercase;
    white-space: nowrap;
}
.ud-price-card h3 { font-size: 1.1rem; margin: 0 0 6px; color: #e2d9f3; }
.ud-price-type { font-size: .8rem; color: #9b85c0; margin-bottom: 18px; }
.ud-price-num {
    font-size: clamp(1.8rem, 4vw, 2.4rem);
    font-weight: 800;
    color: #c084fc;
    margin: 0 0 4px;
    line-height: 1;
}
.ud-price-num sup { font-size: .6em; vertical-align: top; margin-top: 6px; }
.ud-price-sub { font-size: .75rem; color: #7a6a9a; margin-bottom: 22px; }
.ud-price-list { list-style: none; padding: 0; margin: 0 0 28px; text-align: left; }
.ud-price-list li {
    font-size: .88rem;
    color: #c4b5e0;
    padding: 6px 0;
    border-bottom: 1px solid rgba(255,255,255,.05);
    display: flex;
    gap: 8px;
    align-items: flex-start;
}
.ud-price-list li::before { content: '✓'; color: #a855f7; font-weight: 700; flex-shrink: 0; }

/* ── How it works ── */
.ud-how {
    padding: 80px 24px;
    background: radial-gradient(ellipse 50% 40% at 50% 100%, rgba(160,100,255,.07), transparent 70%), #081327;
}
.ud-how .container { max-width: 1000px; margin: 0 auto; }
.ud-steps {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 0;
    margin-top: 44px;
    position: relative;
}
.ud-steps::before {
    content: '';
    position: absolute;
    top: 36px;
    left: 10%;
    right: 10%;
    height: 2px;
    background: linear-gradient(90deg, transparent, rgba(168,85,247,.4), transparent);
}
.ud-step { text-align: center; padding: 24px 20px; position: relative; }
.ud-step-num {
    width: 56px; height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #7c3aed, #a855f7);
    color: #fff;
    font-size: 1.2rem;
    font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px;
    box-shadow: 0 8px 24px rgba(124,58,237,.4);
    position: relative; z-index: 1;
}
.ud-step h4 { font-size: 1rem; margin: 0 0 8px; }
.ud-step p { font-size: .85rem; color: var(--muted); line-height: 1.6; margin: 0; }

/* ── FAQ ── */
.ud-faq {
    padding: 80px 24px;
    background: #060e1e;
}
.ud-faq .container { max-width: 800px; margin: 0 auto; }
.ud-faq-list { margin-top: 40px; }
.ud-faq-item {
    border: 1px solid rgba(100,60,200,.25);
    border-radius: 14px;
    margin-bottom: 12px;
    overflow: hidden;
}
.ud-faq-q {
    width: 100%;
    background: rgba(22,14,44,.8);
    border: none;
    color: #e2d9f3;
    padding: 18px 20px;
    text-align: left;
    font-family: inherit;
    font-size: .95rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    transition: background .2s ease;
}
.ud-faq-q:hover { background: rgba(40,20,70,.9); }
.ud-faq-q .arrow { transition: transform .3s ease; color: #a855f7; font-size: .8rem; flex-shrink: 0; }
.ud-faq-item.open .ud-faq-q .arrow { transform: rotate(180deg); }
.ud-faq-a {
    max-height: 0;
    overflow: hidden;
    transition: max-height .35s ease;
    background: rgba(14,8,28,.8);
}
.ud-faq-item.open .ud-faq-a { max-height: 300px; }
.ud-faq-a p { padding: 16px 20px; color: var(--muted); font-size: .9rem; line-height: 1.75; margin: 0; }

/* ── CTA ── */
.ud-cta {
    padding: 80px 24px;
    text-align: center;
    background: linear-gradient(135deg, #12073a 0%, #1e0a50 50%, #120738 100%);
    position: relative;
    overflow: hidden;
}
.ud-cta::before {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(ellipse 60% 50% at 50% 50%, rgba(168,85,247,.15), transparent 70%);
    pointer-events: none;
}
.ud-cta h2 { font-size: clamp(1.8rem, 4vw, 2.8rem); margin: 0 0 16px; position: relative; }
.ud-cta p { color: var(--muted); max-width: 50ch; margin: 0 auto 36px; line-height: 1.8; position: relative; }
.ud-cta-actions { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; position: relative; }
.btn-purple {
    background: linear-gradient(135deg, #7c3aed, #a855f7);
    color: #fff;
    box-shadow: 0 10px 28px rgba(124,58,237,.4);
}
.btn-purple:hover { transform: translateY(-2px); box-shadow: 0 16px 36px rgba(124,58,237,.55); }

/* ── Nav override ── */
.ud-nav-kicker { color: #c084fc !important; }

/* Responsive */
@media (max-width: 900px) {
    .ud-hero-body { flex-direction: column; padding: 100px 24px 60px; gap: 40px; }
    .ud-hero-img { flex: unset; max-width: 280px; margin: 0 auto; }
    .ud-steps::before { display: none; }
}
@media (max-width: 600px) {
    .ud-hero-body { padding: 90px 16px 50px; }
}
</style>

<div class="landing-shell">

    <!-- ── Nav ── -->
    <section class="top-stage" style="min-height:unset">
      <div class="container">
        <div class="top-stage-inner" style="min-height:unset;border-radius:0">
            <div class="ud-hero-grid" aria-hidden="true"></div>
            <div class="nav-backdrop" id="navBackdrop" hidden></div>
            <header class="top-nav">
                <div class="nav-inner">
                    <a href="<?= base_url('/') ?>" class="logo-area">
                        <img src="<?= base_url('assets/img/logo-internsoft.png') ?>" alt="Internsoft" class="logo-img">
                        <span class="logo-text">Internsoft</span>
                    </a>
                    <nav class="menu-links" id="mainMenu">
                        <a href="<?= base_url('/') ?>">Beranda</a>
                        <a href="<?= base_url('/') ?>#layanan">Layanan</a>
                        <a href="<?= base_url('/') ?>#kontak">Kontak</a>
                    </nav>
                    <div class="nav-end">
                        <a href="<?= base_url('login') ?>" class="btn btn-primary nav-login">Masuk</a>
                        <button type="button" class="nav-toggle" id="navToggle" aria-label="Buka menu" aria-expanded="false" aria-controls="mainMenu">
                            <span></span><span></span><span></span>
                        </button>
                    </div>
                </div>
            </header>

            <!-- ── Hero ── -->
            <div class="ud-hero-body">
                <div class="ud-hero-copy" data-reveal>
                    <p class="kicker ud-nav-kicker">Layanan Internsoft</p>
                    <h1>Undangan Digital <span class="accent">Murah</span>, Modern & Berkesan</h1>
                    <p>Buat undangan pernikahan, khitan, ulang tahun, dan acara spesial lainnya dalam format digital yang elegan. Dibagikan lewat WhatsApp, bisa request desain sesuai keinginan, dilengkapi buku tamu QR.</p>
                    <div style="display:flex;gap:12px;flex-wrap:wrap">
                        <a href="<?= $waUrl ?>" target="_blank" rel="noopener" class="btn btn-purple">💬 Pesan Sekarang via WA</a>
                        <a href="#fitur" class="btn btn-outline">Lihat Fitur ↓</a>
                    </div>
                    <div style="margin-top:24px;display:flex;gap:20px;flex-wrap:wrap;font-size:.82rem;color:#9b85c0">
                        <span>✅ Harga mulai 10 ribu</span>
                        <span>✅ Revisi bebas</span>
                        <span>✅ Kirim via WA</span>
                    </div>
                </div>
                <div class="ud-hero-img" data-reveal>
                    <img src="<?= base_url('assets/img/tentang-kami-undangan-digital.png') ?>" alt="Undangan Digital Internsoft">
                </div>
            </div>
        </div>
      </div>
    </section>

    <!-- ── Fitur ── -->
    <section class="ud-features" id="fitur">
        <div class="container">
            <div class="section-head center" data-reveal>
                <p class="kicker ud-nav-kicker">Kenapa Pilih Kami</p>
                <h2>Fitur Lengkap, Harga Bersahabat</h2>
                <p>Semua yang kamu butuhkan untuk undangan digital yang sempurna</p>
            </div>
            <div class="ud-features-grid">
                <div class="ud-feat-card" data-reveal>
                    <div class="ud-feat-icon">📱</div>
                    <h3>Kirim via WhatsApp</h3>
                    <p>Undangan berbentuk link, mudah dibagikan ke siapa saja lewat WhatsApp, Instagram, atau media sosial lainnya.</p>
                </div>
                <div class="ud-feat-card" data-reveal>
                    <div class="ud-feat-icon">🎨</div>
                    <h3>Desain Sesuai Keinginan</h3>
                    <p>Request tema, warna, foto, dan konten sesuka hati. Tim kami siap mewujudkan undangan impian kamu.</p>
                </div>
                <div class="ud-feat-card" data-reveal>
                    <div class="ud-feat-icon">📷</div>
                    <h3>Buku Tamu QR Code</h3>
                    <p>Tamu cukup scan QR untuk konfirmasi kehadiran. Data terkumpul otomatis, tanpa buku tamu fisik.</p>
                </div>
                <div class="ud-feat-card" data-reveal>
                    <div class="ud-feat-icon">🎵</div>
                    <h3>Musik & Animasi</h3>
                    <p>Lengkap dengan musik latar pilihan dan animasi elegan yang membuat undangan terasa mewah.</p>
                </div>
                <div class="ud-feat-card" data-reveal>
                    <div class="ud-feat-icon">💌</div>
                    <h3>Ucapan Tamu Online</h3>
                    <p>Kolom ucapan dan doa dari tamu tersimpan langsung di undangan, bisa dibaca kapan saja.</p>
                </div>
                <div class="ud-feat-card" data-reveal>
                    <div class="ud-feat-icon">⏰</div>
                    <h3>Countdown Timer</h3>
                    <p>Hitung mundur otomatis menuju hari H, membangun antusiasme tamu menjelang acara.</p>
                </div>
                <div class="ud-feat-card" data-reveal>
                    <div class="ud-feat-icon">🗺️</div>
                    <h3>Integrasi Google Maps</h3>
                    <p>Titik lokasi acara langsung terhubung ke Google Maps agar tamu mudah menemukan tempat.</p>
                </div>
                <div class="ud-feat-card" data-reveal>
                    <div class="ud-feat-icon">💰</div>
                    <h3>Info Transfer Hadiah</h3>
                    <p>Tampilkan nomor rekening dengan tombol salin sekali klik untuk kemudahan tamu memberikan hadiah.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ── Contoh Undangan ── -->
    <section style="padding:80px 24px;background:#060e1e;text-align:center" id="contoh">
        <div class="container" style="max-width:900px;margin:0 auto">
            <div class="section-head center" data-reveal>
                <p class="kicker ud-nav-kicker">Demo Langsung</p>
                <h2>Lihat Contoh Undangan</h2>
                <p>Coba sendiri tampilan undangan digital yang akan kamu dapatkan</p>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px;margin-top:44px">
                <div class="ud-feat-card" data-reveal style="text-align:center">
                    <div class="ud-feat-icon">💍</div>
                    <h3>Undangan Pernikahan</h3>
                    <p style="margin-bottom:20px">Tema romantis dengan musik, galeri foto, countdown, buku tamu online, dan info transfer hadiah.</p>
                    <a href="<?= base_url('undangan-nikah.html') ?>" target="_blank" rel="noopener"
                       class="btn btn-purple" style="display:inline-flex">Lihat Contoh →</a>
                </div>
                <div class="ud-feat-card" data-reveal style="text-align:center">
                    <div class="ud-feat-icon">✂️</div>
                    <h3>Undangan Khitan</h3>
                    <p style="margin-bottom:20px">Tema Islami hijau emas dengan splash screen, foto, countdown, ucapan tamu, dan info rekening.</p>
                    <a href="<?= base_url('undangan.html') ?>" target="_blank" rel="noopener"
                       class="btn btn-outline" style="display:inline-flex">Lihat Contoh →</a>
                </div>
            </div>
        </div>
    </section>

    <!-- ── Pricing ── -->
    <section class="ud-pricing" id="harga">
        <div class="container">
            <div class="section-head center" data-reveal>
                <p class="kicker ud-nav-kicker">Harga Transparan</p>
                <h2>Pilih Paket Sesuai Kebutuhan</h2>
                <p>Semua paket bisa dikustom. Hubungi kami untuk diskusi lebih lanjut.</p>
            </div>
            <div class="ud-price-cards">
                <div class="ud-price-card" data-reveal>
                    <h3>Starter</h3>
                    <p class="ud-price-type">Acara sederhana</p>
                    <div class="ud-price-num"><sup>Rp</sup>10K</div>
                    <p class="ud-price-sub">sekali bayar</p>
                    <ul class="ud-price-list">
                        <li>Desain template pilihan</li>
                        <li>Musik latar</li>
                        <li>Countdown timer</li>
                        <li>Google Maps</li>
                        <li>Link undangan aktif 3 bulan</li>
                    </ul>
                    <a href="<?= $waUrl ?>" target="_blank" rel="noopener" class="btn btn-outline" style="width:100%;justify-content:center">Pesan via WA</a>
                </div>
                <div class="ud-price-card popular" data-reveal>
                    <span class="popular-badge">⭐ Terpopuler</span>
                    <h3>Premium</h3>
                    <p class="ud-price-type">Pernikahan / Khitan</p>
                    <div class="ud-price-num"><sup>Rp</sup>30K</div>
                    <p class="ud-price-sub">sekali bayar</p>
                    <ul class="ud-price-list">
                        <li>Desain custom sesuai request</li>
                        <li>Musik latar pilihan</li>
                        <li>Countdown timer</li>
                        <li>Google Maps titik lokasi</li>
                        <li>Ucapan tamu online</li>
                        <li>Info transfer hadiah</li>
                        <li>Animasi & efek modern</li>
                        <li>Link aktif 6 bulan</li>
                        <li>Revisi 3x</li>
                    </ul>
                    <a href="<?= $waUrl ?>" target="_blank" rel="noopener" class="btn btn-purple" style="width:100%;justify-content:center">Pesan via WA</a>
                </div>
                <div class="ud-price-card" data-reveal>
                    <h3>Eksklusif</h3>
                    <p class="ud-price-type">Acara besar / Korporat</p>
                    <div class="ud-price-num"><sup>Rp</sup>50K</div>
                    <p class="ud-price-sub">sekali bayar</p>
                    <ul class="ud-price-list">
                        <li>Semua fitur Premium</li>
                        <li>QR Code buku tamu digital</li>
                        <li>Nama tamu personalisasi</li>
                        <li>Galeri foto / video</li>
                        <li>Link aktif 1 tahun</li>
                        <li>Revisi unlimited</li>
                        <li>Prioritas support</li>
                    </ul>
                    <a href="<?= $waUrl ?>" target="_blank" rel="noopener" class="btn btn-outline" style="width:100%;justify-content:center">Pesan via WA</a>
                </div>
            </div>
            <p style="margin-top:24px;font-size:.8rem;color:#6a5a8a;text-align:center">* Harga dapat berubah sewaktu-waktu. Hubungi kami untuk penawaran spesial.</p>
        </div>
    </section>

    <!-- ── How it works ── -->
    <section class="ud-how" id="cara-pesan">
        <div class="container">
            <div class="section-head center" data-reveal>
                <p class="kicker ud-nav-kicker">Cara Pesan</p>
                <h2>Mudah, Cepat, Selesai</h2>
                <p>Proses pesan undangan digital dari nol sampai jadi hanya dalam hitungan jam</p>
            </div>
            <div class="ud-steps">
                <div class="ud-step" data-reveal>
                    <div class="ud-step-num">1</div>
                    <h4>Chat via WA</h4>
                    <p>Hubungi kami lewat WhatsApp, ceritakan acara dan keinginan desain kamu.</p>
                </div>
                <div class="ud-step" data-reveal>
                    <div class="ud-step-num">2</div>
                    <h4>Kirim Data</h4>
                    <p>Kirim nama, tanggal, lokasi, foto, dan detail lain yang perlu ditampilkan.</p>
                </div>
                <div class="ud-step" data-reveal>
                    <div class="ud-step-num">3</div>
                    <h4>Review & Revisi</h4>
                    <p>Kami kirimkan draft undangan. Kamu bisa minta revisi sampai puas.</p>
                </div>
                <div class="ud-step" data-reveal>
                    <div class="ud-step-num">4</div>
                    <h4>Undangan Siap 🎉</h4>
                    <p>Link undangan aktif dan siap disebar ke semua tamu via WhatsApp.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ── FAQ ── -->
    <section class="ud-faq" id="faq">
        <div class="container">
            <div class="section-head center" data-reveal>
                <p class="kicker ud-nav-kicker">FAQ</p>
                <h2>Pertanyaan Umum</h2>
            </div>
            <div class="ud-faq-list" data-reveal>
                <?php foreach ($faqs as $faq): ?>
                <div class="ud-faq-item">
                    <button class="ud-faq-q" type="button">
                        <?= esc($faq['q']) ?>
                        <span class="arrow">▼</span>
                    </button>
                    <div class="ud-faq-a"><p><?= esc($faq['a']) ?></p></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ── CTA ── -->
    <section class="ud-cta">
        <h2 data-reveal>Siap Buat Undangan Digital? 🎊</h2>
        <p data-reveal>Jangan ragu konsultasi dulu — gratis! Tim kami siap membantu mewujudkan undangan impian kamu.</p>
        <div class="ud-cta-actions" data-reveal>
            <a href="<?= $waUrl ?>" target="_blank" rel="noopener" class="btn btn-purple">💬 Chat WhatsApp Sekarang</a>
            <a href="<?= base_url('/') ?>" class="btn btn-outline">← Kembali ke Beranda</a>
        </div>
    </section>

    <!-- ── Footer credit ── -->
    <div style="background:#040c1a;padding:18px 24px;text-align:center">
        <p style="font-size:.78rem;color:rgba(255,255,255,.3)">© <?= date('Y') ?> <a href="<?= base_url('/') ?>" style="color:rgba(255,255,255,.5);text-decoration:none">Internsoft Technology Solutions</a> · All rights reserved</p>
    </div>

</div><!-- /landing-shell -->

<script>
// Reveal on scroll
const ro = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('is-visible'); ro.unobserve(e.target); } });
}, { threshold: .12 });
document.querySelectorAll('[data-reveal]').forEach(el => ro.observe(el));

// FAQ accordion
document.querySelectorAll('.ud-faq-q').forEach(btn => {
    btn.addEventListener('click', () => {
        const item = btn.closest('.ud-faq-item');
        const isOpen = item.classList.contains('open');
        document.querySelectorAll('.ud-faq-item.open').forEach(i => i.classList.remove('open'));
        if (!isOpen) item.classList.add('open');
    });
});

// Mobile nav
const toggle = document.getElementById('navToggle');
const menu   = document.getElementById('mainMenu');
const backdrop = document.getElementById('navBackdrop');
if (toggle) {
    toggle.addEventListener('click', () => {
        const open = document.body.classList.toggle('nav-open');
        toggle.setAttribute('aria-expanded', open);
        if (backdrop) backdrop.hidden = !open;
    });
}
</script>
<?= $this->endSection() ?>
