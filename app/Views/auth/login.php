<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<main class="auth-page">
    <div class="auth-atmosphere" aria-hidden="true">
        <span class="auth-glow auth-glow-a"></span>
        <span class="auth-glow auth-glow-b"></span>
        <span class="auth-grid"></span>
    </div>

    <div class="auth-shell">
        <aside class="auth-aside">
            <a href="<?= base_url('/') ?>" class="auth-brand">
                <img src="<?= base_url('assets/img/logo-internsoft.png') ?>" alt="Internsoft" class="auth-brand-logo">
                <span>Internsoft</span>
            </a>

            <h2>Selamat datang kembali</h2>
            <p>
                Masuk untuk melihat status domain Anda, memantau uptime, dan mengelola
                nomor penerima notifikasi.
            </p>

            <ul class="auth-points">
                <li>Status domain UP/DOWN terkini</li>
                <li>Notifikasi WhatsApp otomatis</li>
                <li>Ringkasan downtime harian</li>
            </ul>
        </aside>

        <section class="auth-card">
            <div class="auth-card-head">
                <h1>Login</h1>
                <p>Masuk menggunakan email dan password Anda.</p>
            </div>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert success"><?= esc((string) session()->getFlashdata('success')) ?></div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('warning')): ?>
                <div class="alert error"><?= esc((string) session()->getFlashdata('warning')) ?></div>
            <?php endif; ?>

            <?php if (isset($validation)): ?>
                <div class="alert error"><?= $validation->listErrors() ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert error"><?= esc($error) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= base_url('login') ?>" class="auth-form">
                <?= csrf_field() ?>

                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="<?= esc(old('email')) ?>" placeholder="nama@email.com" autocomplete="email" required>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" placeholder="Password Anda" autocomplete="current-password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Masuk</button>
            </form>

            <p class="auth-note">Belum punya akun? <a href="<?= base_url('register') ?>">Daftar di sini</a></p>
            <a href="<?= base_url('/') ?>" class="auth-back">Kembali ke beranda</a>
        </section>
    </div>
</main>
<?= $this->endSection() ?>
