<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="dash-shell">
    <header class="dash-top">
        <div class="dash-brand">
            <a href="/" class="auth-brand">
                <img src="/assets/img/logo-internsoft.png" alt="Internsoft" class="auth-brand-logo">
                <span>Internsoft</span>
            </a>
            <p class="dash-welcome">Halo, <?= esc($name) ?></p>
        </div>

        <form method="post" action="/logout">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline">Logout</button>
        </form>
    </header>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert success"><?= esc((string) session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert error"><?= nl2br(esc((string) session()->getFlashdata('error'))) ?></div>
    <?php endif; ?>

    <section class="dash-stats">
        <article class="dash-stat">
            <strong><?= (int) $stats['total'] ?></strong>
            <span>Total Domain</span>
        </article>
        <article class="dash-stat">
            <strong><?= (int) $stats['active'] ?></strong>
            <span>Aktif</span>
        </article>
        <article class="dash-stat up">
            <strong><?= (int) $stats['up'] ?></strong>
            <span>UP</span>
        </article>
        <article class="dash-stat down">
            <strong><?= (int) $stats['down'] ?></strong>
            <span>DOWN</span>
        </article>
    </section>

    <section class="dash-panel">
        <div class="dash-panel-head">
            <div>
                <h1>Daftar Domain</h1>
                <p>Klik domain untuk melihat riwayat UP/DOWN dan mengatur nomor WhatsApp.</p>
            </div>
            <form method="post" action="/dashboard/check-all">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline">Cek Semua Sekarang</button>
            </form>
        </div>

        <form method="post" action="/dashboard/domains" class="dash-form dash-form-inline">
            <?= csrf_field() ?>
            <div class="field grow">
                <label for="domain_url">URL Domain</label>
                <input id="domain_url" name="domain_url" type="text" placeholder="https://contoh.com" required>
            </div>
            <button type="submit" class="btn btn-primary">Tambah Domain</button>
        </form>
    </section>

    <section class="domain-table-wrap">
        <?php if ($domains === []): ?>
            <div class="dash-empty">
                <h2>Belum ada domain</h2>
                <p>Tambahkan domain pertama Anda untuk mulai monitoring uptime.</p>
            </div>
        <?php else: ?>
            <div class="domain-table-head">
                <span>Domain</span>
                <span>Kondisi situs</span>
                <span>Pengecekan</span>
                <span>WhatsApp</span>
                <span>Terakhir dicek</span>
                <span>Aksi</span>
            </div>

            <ul class="domain-table">
                <?php foreach ($domains as $domain): ?>
                    <?php
                        $domainId = (int) $domain['id'];
                        $status   = (string) $domain['last_status'];
                        $active   = (int) $domain['is_active'] === 1;
                        $waCount  = (int) ($contactCountByDomain[$domainId] ?? 0);
                        $statusLabel = match ($status) {
                            'UP'   => 'Online',
                            'DOWN' => 'Offline',
                            default => 'Belum dicek',
                        };
                    ?>
                    <li class="domain-row">
                        <a class="domain-row-main" href="/dashboard/domains/<?= $domainId ?>" data-label="Domain">
                            <strong><?= esc($domain['domain_url']) ?></strong>
                            <span class="domain-row-hint">Lihat riwayat UP/DOWN</span>
                        </a>

                        <span class="badge status-<?= esc(strtolower($status), 'attr') ?>" data-label="Kondisi situs">
                            <span class="badge-dot"></span><?= esc($statusLabel) ?>
                        </span>

                        <span class="badge <?= $active ? 'badge-on' : 'badge-off' ?>" data-label="Pengecekan">
                            <?= $active ? 'Berjalan' : 'Dijeda' ?>
                        </span>

                        <span class="domain-wa-count" data-label="WhatsApp"><?= $waCount ?> nomor</span>

                        <span class="domain-checked" data-label="Terakhir dicek">
                            <?= ! empty($domain['last_checked_at']) ? esc($domain['last_checked_at']) : 'Belum pernah' ?>
                        </span>

                        <div class="domain-row-actions" data-label="Aksi">
                            <a class="btn btn-outline btn-sm" href="/dashboard/domains/<?= $domainId ?>">Detail</a>
                            <form method="post" action="/dashboard/domains/<?= $domainId ?>/toggle">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-outline btn-sm">
                                    <?= $active ? 'Jeda' : 'Aktifkan' ?>
                                </button>
                            </form>
                            <form method="post" action="/dashboard/domains/<?= $domainId ?>/delete" onsubmit="return confirm('Hapus domain ini beserta nomor WhatsApp-nya?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</div>
<?= $this->endSection() ?>
