<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<main class="auth-page">
    <div class="auth-atmosphere" aria-hidden="true">
        <span class="auth-glow auth-glow-a"></span>
        <span class="auth-glow auth-glow-b"></span>
        <span class="auth-grid"></span>
    </div>

    <div class="auth-shell auth-shell-narrow">
        <section class="auth-card">
            <a href="/" class="auth-brand auth-brand-center">
                <img src="/assets/img/logo-internsoft.png" alt="Internsoft" class="auth-brand-logo">
                <span>Internsoft</span>
            </a>

            <div class="auth-card-head">
                <h1>Verifikasi OTP</h1>
                <p>Kode OTP sudah dikirim ke WhatsApp <strong><?= esc($waNumber) ?></strong></p>
            </div>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert success"><?= esc((string) session()->getFlashdata('success')) ?></div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('warning')): ?>
                <div class="alert error"><?= esc((string) session()->getFlashdata('warning')) ?></div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert error"><?= esc((string) session()->getFlashdata('error')) ?></div>
            <?php endif; ?>

            <?php if (isset($validation)): ?>
                <div class="alert error"><?= $validation->listErrors() ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert error"><?= esc($error) ?></div>
            <?php endif; ?>

            <form method="post" action="/verify-otp" class="auth-form">
                <?= csrf_field() ?>

                <div class="field">
                    <label for="otp_code">Kode OTP</label>
                    <input id="otp_code" name="otp_code" class="otp-input" type="text" inputmode="numeric" maxlength="6" placeholder="000000" autocomplete="one-time-code" required>
                    <small>Kode berlaku 10 menit. Jangan bagikan ke siapa pun.</small>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Verifikasi OTP</button>
            </form>

            <form method="post" action="/resend-otp" class="auth-resend">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline btn-block">Kirim Ulang OTP</button>
            </form>

            <a href="/" class="auth-back">Kembali ke beranda</a>
        </section>
    </div>
</main>
<?= $this->endSection() ?>
