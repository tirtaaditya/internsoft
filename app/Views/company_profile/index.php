<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="landing-shell">
    <div class="page-loader" id="pageLoader" aria-live="polite" aria-label="Memuat halaman">
        <div>
            <div class="page-loader-core" aria-hidden="true">
                <img src="<?= base_url('assets/img/logo-internsoft.png') ?>" alt="Internsoft" class="page-loader-logo">
                <span class="page-loader-ring"></span>
            </div>
            <p class="page-loader-text">Loading ...</p>
        </div>
    </div>

    <section class="top-stage" id="beranda">
        <div class="container">
            <div class="top-stage-inner">
                <div class="hero-atmosphere" aria-hidden="true">
                    <span class="hero-glow hero-glow-a"></span>
                    <span class="hero-glow hero-glow-b"></span>
                    <span class="hero-glow hero-glow-c"></span>
                    <span class="hero-grid"></span>
                </div>

                <div class="nav-backdrop" id="navBackdrop" hidden></div>

                <header class="top-nav">
                    <div class="nav-inner">
                        <a href="#beranda" class="logo-area">
                            <img src="<?= base_url('assets/img/logo-internsoft.png') ?>" alt="Internsoft Technology Solutions" class="logo-img">
                            <span class="logo-text">Internsoft</span>
                        </a>

                        <nav class="menu-links" id="mainMenu">
                            <a href="#beranda" class="is-active">Beranda</a>
                            <a href="#layanan">Layanan</a>
                            <a href="#tentang">Tentang Kami</a>
                            <a href="#kontak">Kontak</a>
                        </nav>

                        <div class="nav-end">
                            <a href="<?= base_url('login') ?>" class="btn btn-primary nav-login">Masuk</a>
                            <button
                                type="button"
                                class="nav-toggle"
                                id="navToggle"
                                aria-label="Buka menu"
                                aria-expanded="false"
                                aria-controls="mainMenu"
                            >
                                <span></span>
                                <span></span>
                                <span></span>
                            </button>
                        </div>
                    </div>
                </header>

                <section class="hero-v2">
                    <div class="hero-v2-grid">
                        <div class="hero-v2-copy">
                            <p class="kicker">Solutions for Today, Technology for Tomorrow</p>
                            <h1>Internsoft<br>Technology <span class="accent">Solutions</span></h1>
                            <p>
                                Kami menyediakan solusi teknologi yang andal, efisien, dan terintegrasi
                                untuk membantu bisnis Anda tumbuh di era digital.
                            </p>
                            <div class="hero-v2-actions">
                                <a href="#layanan" class="btn btn-primary">Lihat Layanan</a>
                                <a href="#tentang" class="btn btn-outline">Tentang Kami</a>
                            </div>
                            <div class="hero-v2-meta">
                                <span>Aman</span>
                                <span>Cepat</span>
                                <span>Terpercaya</span>
                            </div>
                        </div>

                        <div class="hero-visual" aria-hidden="true"></div>
                    </div>
                </section>

                <a href="#layanan" class="hero-scroll-hint">
                    <span>Lihat layanan</span>
                    <span class="hero-scroll-arrow" aria-hidden="true"></span>
                </a>
            </div>
        </div>
    </section>

    <main>

        <section class="services-v2" id="layanan">
            <div class="container">
                <div class="section-head center" data-reveal>
                    <p class="kicker">Yang Kami Tawarkan</p>
                    <h2>Layanan Kami</h2>
                    <p>Solusi teknologi lengkap untuk kebutuhan Anda</p>
                </div>

                <div class="service-grid-v2" data-reveal-group>
                    <article class="service-card-v2" data-reveal>
                        <div class="service-image">
                            <img src="<?= base_url('assets/img/tentang-kami-monitoring-server.png') ?>" alt="Monitoring Server">
                        </div>
                        <h3>Monitoring Server</h3>
                        <p>Pantau server secara real-time dan dapatkan notifikasi langsung saat terjadi masalah.</p>
                        <a href="<?= base_url('register') ?>">Selengkapnya</a>
                    </article>

                    <article class="service-card-v2" data-reveal>
                        <div class="service-image whatsapp">
                            <img src="<?= base_url('assets/img/tentang-kami-api-whatsapp.png') ?>" alt="API WhatsApp">
                        </div>
                        <h3>API WhatsApp</h3>
                        <p>Integrasi kirim WhatsApp ke aplikasi atau sistem Anda dengan mudah dan fleksibel.</p>
                        <span class="coming-pill">Coming Soon</span>
                    </article>

                    <article class="service-card-v2" data-reveal>
                        <div class="service-image">
                            <img src="<?= base_url('assets/img/tentang-kami-pembuatan-website.png') ?>" alt="Pembuatan Aplikasi">
                        </div>
                        <h3>Pembuatan Aplikasi</h3>
                        <p>Kami membangun aplikasi website dan mobile yang modern, responsif, dan sesuai kebutuhan.</p>
                        <a href="https://wa.me/628999188009?text=Halo%20Internsoft%2C%20saya%20ingin%20konsultasi%20tentang%20pembuatan%20aplikasi." target="_blank" rel="noopener">Konsultasi via WhatsApp</a>
                    </article>

                    <article class="service-card-v2" data-reveal>
                        <div class="service-image">
                            <img src="<?= base_url('assets/img/tentang-kami-instalasi-komputer.png') ?>" alt="Instalasi Komputer">
                        </div>
                        <h3>Instalasi Komputer</h3>
                        <p>Layanan instalasi sistem operasi, software, dan konfigurasi komputer untuk kebutuhan Anda.</p>
                        <a href="https://wa.me/628999188009?text=Halo%20Internsoft%2C%20saya%20ingin%20konsultasi%20tentang%20layanan%20instalasi%20komputer." target="_blank" rel="noopener">Konsultasi via WhatsApp</a>
                    </article>
                </div>
            </div>
        </section>

        <div class="section-break" aria-hidden="true"></div>

        <section class="about-v2" id="tentang">
            <div class="container about-grid">
                <div class="about-copy" data-reveal>
                    <p class="kicker">Tentang Kami</p>
                    <h2>Solusi Teknologi, Hasil Terbaik</h2>
                    <p>
                        Internsoft Technology Solutions hadir memberikan layanan teknologi informasi yang profesional,
                        tepat, dan terpercaya untuk individu maupun bisnis.
                    </p>
                    <a href="https://wa.me/628999188009?text=Halo%20Internsoft%2C%20saya%20ingin%20konsultasi." target="_blank" rel="noopener" class="btn btn-primary">Konsultasi Sekarang</a>
                </div>

                <div class="stats-grid" data-reveal-group>
                    <article class="stat-item" data-reveal><strong>99.9%</strong><span>Uptime Monitoring</span></article>
                    <article class="stat-item" data-reveal><strong>24/7</strong><span>Support</span></article>
                </div>
            </div>
        </section>

        <section class="cta-strip" id="kontak">
            <div class="container">
                <div class="cta-strip-inner" data-reveal>
                    <div class="cta-copy">
                        <p class="kicker">Siap Diskusi</p>
                        <h3>Punya Kebutuhan Lain?</h3>
                        <p>Kami siap membantu mewujudkan solusi terbaik untuk bisnis Anda.</p>
                    </div>
                    <a href="https://wa.me/628999188009" target="_blank" rel="noopener" class="btn btn-primary cta-phone">Hubungi Kami</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer-v2">
        <div class="container footer-v2-grid" data-reveal-group>
            <div class="footer-brand" data-reveal>
                <a href="#beranda" class="footer-logo">
                    <img src="<?= base_url('assets/img/logo-internsoft.png') ?>" alt="Internsoft" class="footer-logo-img">
                    <span>Internsoft</span>
                </a>
                <p>Memberikan solusi teknologi yang inovatif, andal, dan terintegrasi untuk masa depan yang lebih baik.</p>
            </div>

            <div data-reveal>
                <h4>Layanan</h4>
                <ul>
                    <li><a href="#layanan">Monitoring Server</a></li>
                    <li><a href="#layanan">API WhatsApp</a></li>
                    <li><a href="#layanan">Pembuatan Aplikasi</a></li>
                    <li><a href="#layanan">Instalasi Komputer</a></li>
                </ul>
            </div>

            <div data-reveal>
                <h4>Perusahaan</h4>
                <ul>
                    <li><a href="https://internsoft.my.id" target="_blank" rel="noopener">Internsoft.my.id</a></li>
                    <li><a href="#tentang">Tentang Kami</a></li>
                    <li>Indonesia</li>
                </ul>
            </div>

            <div data-reveal>
                <h4>Hubungi Kami</h4>
                <ul>
                    <li><a href="https://wa.me/628999188009" target="_blank" rel="noopener">08999188009</a></li>
                    <li><a href="#kontak">Konsultasi</a></li>
                </ul>
            </div>
        </div>
        <div class="container footer-bottom">
            <p class="copyright">© 2026 Internsoft Technology Solutions. All rights reserved.</p>
        </div>
    </footer>
</div>

<script>
    (function() {
        var loader = document.getElementById('pageLoader');
        if (!loader) return;

        document.body.classList.add('page-loading');

        window.addEventListener('load', function() {
            loader.classList.add('is-hidden');
            document.body.classList.remove('page-loading');

            setTimeout(function() {
                loader.remove();
            }, 380);
        });
    })();

    (function() {
        var items = document.querySelectorAll('[data-reveal]');
        if (!items.length) return;

        var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (reduced || !('IntersectionObserver' in window)) {
            items.forEach(function(item) {
                item.classList.add('is-visible');
            });
            return;
        }

        document.querySelectorAll('[data-reveal-group]').forEach(function(group) {
            group.querySelectorAll('[data-reveal]').forEach(function(item, index) {
                item.style.setProperty('--reveal-delay', (index * 110) + 'ms');
            });
        });

        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, {
            threshold: 0.15,
            rootMargin: '0px 0px -60px 0px'
        });

        items.forEach(function(item) {
            observer.observe(item);
        });
    })();

    (function() {
        var links = document.querySelectorAll('.menu-links a[href^="#"]');
        var sections = [];

        links.forEach(function(link) {
            var target = document.querySelector(link.getAttribute('href'));
            if (target) sections.push({ link: link, target: target });
        });

        if (!sections.length || !('IntersectionObserver' in window)) return;

        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (!entry.isIntersecting) return;

                links.forEach(function(link) {
                    link.classList.remove('is-active');
                });

                var match = sections.find(function(item) {
                    return item.target === entry.target;
                });

                if (match) match.link.classList.add('is-active');
            });
        }, {
            threshold: 0.4
        });

        sections.forEach(function(item) {
            observer.observe(item.target);
        });
    })();

    (function() {
        var toggle = document.getElementById('navToggle');
        var menu = document.getElementById('mainMenu');
        var backdrop = document.getElementById('navBackdrop');
        if (!toggle || !menu) return;

        function setOpen(open) {
            document.body.classList.toggle('nav-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
            if (backdrop) {
                if (open) {
                    backdrop.hidden = false;
                } else {
                    backdrop.hidden = true;
                }
            }
        }

        toggle.addEventListener('click', function(event) {
            event.stopPropagation();
            setOpen(!document.body.classList.contains('nav-open'));
        });

        menu.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {
                setOpen(false);
            });
        });

        if (backdrop) {
            backdrop.addEventListener('click', function() {
                setOpen(false);
            });
        }

        document.addEventListener('click', function(event) {
            if (!document.body.classList.contains('nav-open')) return;
            if (menu.contains(event.target) || toggle.contains(event.target)) return;
            if (backdrop && backdrop.contains(event.target)) return;
            setOpen(false);
        });

        window.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') setOpen(false);
        });

        window.addEventListener('resize', function() {
            if (window.innerWidth > 900) setOpen(false);
        });
    })();
</script>
<?= $this->endSection() ?>
