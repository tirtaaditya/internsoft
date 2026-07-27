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

            <h2>Mulai pantau server Anda hari ini</h2>
            <p>
                Daftar sekarang untuk memonitor uptime domain Anda, menerima notifikasi WhatsApp
                saat terjadi gangguan, dan melihat riwayat downtime secara lengkap.
            </p>

            <ul class="auth-points">
                <li>Monitoring domain secara real-time</li>
                <li>Notifikasi WhatsApp saat server down</li>
                <li>Riwayat dan durasi downtime tercatat</li>
            </ul>
        </aside>

        <section class="auth-card">
            <div class="auth-card-head">
                <h1>Daftar Akun</h1>
                <p>Isi data di bawah untuk membuat akun baru.</p>
            </div>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert success"><?= esc((string) session()->getFlashdata('success')) ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert error"><?= esc($error) ?></div>
            <?php endif; ?>

            <?php if (isset($validation)): ?>
                <div class="alert error"><?= $validation->listErrors() ?></div>
            <?php endif; ?>

            <form method="post" action="<?= base_url('register') ?>" class="auth-form">
                <?= csrf_field() ?>

                <div class="field">
                    <label for="name">Nama</label>
                    <input id="name" name="name" type="text" value="<?= esc(old('name')) ?>" placeholder="Nama lengkap" autocomplete="name" required>
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="<?= esc(old('email')) ?>" placeholder="nama@email.com" autocomplete="email" required>
                </div>

                <div class="field">
                    <label for="wa_number">Nomor WhatsApp</label>
                    <div class="input-prefix">
                        <span class="input-prefix-label">+62</span>
                        <input id="wa_number" name="wa_number" type="tel" value="<?= esc(old('wa_number')) ?>" placeholder="81234567890" inputmode="numeric" autocomplete="tel-national" required>
                    </div>
                    <small>Tanpa angka 0 di depan. Digunakan untuk verifikasi OTP dan notifikasi.</small>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" minlength="8" placeholder="Minimal 8 karakter" autocomplete="new-password" required>
                    <div class="password-meter" aria-live="polite">
                        <div class="password-meter-track">
                            <span class="password-meter-bar" id="passwordMeterBar"></span>
                        </div>
                        <div class="password-meter-meta">
                            <span id="passwordMeterLabel">Minimal 8 karakter</span>
                            <span id="passwordMeterCount">0/8</span>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Daftar Sekarang</button>
            </form>

            <p class="auth-note">Sudah punya akun? <a href="<?= base_url('login') ?>">Login di sini</a></p>
            <a href="<?= base_url('/') ?>" class="auth-back">Kembali ke beranda</a>
        </section>
    </div>
</main>

<script>
    (function() {
        var input = document.getElementById('wa_number');
        if (!input) return;

        function sanitize(value) {
            var digits = value.replace(/\D/g, '');

            if (digits.startsWith('62')) {
                digits = digits.slice(2);
            }

            return digits.replace(/^0+/, '');
        }

        input.value = sanitize(input.value);

        input.addEventListener('input', function() {
            var cleaned = sanitize(input.value);
            if (cleaned !== input.value) input.value = cleaned;
        });
    })();

    (function() {
        var password = document.getElementById('password');
        var bar = document.getElementById('passwordMeterBar');
        var label = document.getElementById('passwordMeterLabel');
        var count = document.getElementById('passwordMeterCount');
        if (!password || !bar || !label || !count) return;

        var minLength = 8;

        function updateMeter() {
            var length = password.value.length;
            var progress = Math.min(length / minLength, 1) * 100;
            var level = 'weak';
            var text = 'Terlalu pendek';

            if (length === 0) {
                text = 'Minimal 8 karakter';
                level = 'empty';
                progress = 0;
            } else if (length < 8) {
                text = 'Terlalu pendek';
                level = 'weak';
            } else if (length < 12) {
                text = 'Cukup kuat';
                level = 'fair';
            } else {
                text = 'Sangat kuat';
                level = 'strong';
            }

            bar.style.width = progress + '%';
            bar.dataset.level = level;
            label.textContent = text;
            count.textContent = length + '/' + minLength;
        }

        password.addEventListener('input', updateMeter);
        updateMeter();
    })();
</script>
<?= $this->endSection() ?>
