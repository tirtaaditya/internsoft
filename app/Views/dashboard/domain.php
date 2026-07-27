<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
    $domainId = (int) $domain['id'];
    $status   = (string) $domain['last_status'];
    $active   = (int) $domain['is_active'] === 1;
    $localPhone = static function (string $phone): string {
        return str_starts_with($phone, '62') ? substr($phone, 2) : $phone;
    };
    $formatDuration = static function ($seconds): string {
        if ($seconds === null || $seconds === '') {
            return '-';
        }
        $seconds = (int) $seconds;
        if ($seconds < 60) {
            return $seconds . ' detik';
        }
        if ($seconds < 3600) {
            return floor($seconds / 60) . ' menit';
        }
        $hours = floor($seconds / 3600);
        $mins  = floor(($seconds % 3600) / 60);
        return $hours . ' jam ' . $mins . ' menit';
    };
?>
<div class="dash-shell">
    <header class="dash-top">
        <div class="dash-brand">
            <a href="<?= base_url('dashboard') ?>" class="auth-back-inline">← Kembali ke daftar</a>
            <p class="dash-welcome">Halo, <?= esc($name) ?></p>
        </div>

        <form method="post" action="<?= base_url('logout') ?>">
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

    <section class="domain-detail-head">
        <div>
            <div class="domain-badges">
                <div class="status-cell">
                    <span class="status-cell-label">Kondisi situs</span>
                    <span class="badge status-<?= esc(strtolower($status), 'attr') ?>">
                        <span class="badge-dot"></span>
                        <?= esc(match ($status) {
                            'UP'   => 'Online',
                            'DOWN' => 'Offline',
                            default => 'Belum dicek',
                        }) ?>
                    </span>
                </div>
                <div class="status-cell">
                    <span class="status-cell-label">Pengecekan</span>
                    <span class="badge <?= $active ? 'badge-on' : 'badge-off' ?>">
                        <?= $active ? 'Berjalan' : 'Dijeda' ?>
                    </span>
                </div>
            </div>
            <h1><?= esc($domain['domain_url']) ?></h1>
            <p class="domain-meta">
                Terakhir dicek:
                <?= ! empty($domain['last_checked_at']) ? esc($domain['last_checked_at']) : 'Belum pernah' ?>
            </p>
        </div>

        <div class="domain-actions">
            <form method="post" action="<?= base_url('dashboard/domains/' . $domainId . '/check') ?>">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-primary btn-sm">Cek Sekarang</button>
            </form>
            <form method="post" action="<?= base_url('dashboard/domains/' . $domainId . '/toggle') ?>">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline btn-sm">
                    <?= $active ? 'Jeda Monitoring' : 'Aktifkan Monitoring' ?>
                </button>
            </form>
            <form method="post" action="<?= base_url('dashboard/domains/' . $domainId . '/delete') ?>" onsubmit="return confirm('Hapus domain ini beserta nomor WhatsApp-nya?');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-danger btn-sm">Hapus Domain</button>
            </form>
        </div>
    </section>

    <div class="domain-detail-grid">
        <section class="dash-panel history-panel">
            <div class="dash-panel-head">
                <div>
                    <h2>Riwayat UP / DOWN</h2>
                    <p>Menampilkan data <?= (int) ($retentionDays ?? 7) ?> hari terakhir. Data lebih lama dihapus otomatis.</p>
                </div>
            </div>

            <?php if ($history === []): ?>
                <div class="history-empty">
                    <p>Belum ada riwayat. Riwayat akan muncul setelah monitoring berjalan.</p>
                </div>
            <?php else: ?>
                <ul class="history-list">
                    <?php foreach ($history as $item): ?>
                        <?php
                            $itemStatus = strtolower((string) $item['status']);
                            $at = ! empty($item['at']) ? date('d M Y, H:i:s', strtotime((string) $item['at'])) : '-';
                        ?>
                        <li class="history-item status-<?= esc($itemStatus, 'attr') ?>">
                            <div class="history-marker" aria-hidden="true"></div>
                            <div class="history-body">
                                <div class="history-top">
                                    <strong><?= esc($item['title']) ?></strong>
                                    <span class="badge status-<?= esc($itemStatus === 'recovered' ? 'up' : $itemStatus, 'attr') ?>">
                                        <?= esc((string) $item['status']) ?>
                                    </span>
                                </div>
                                <p class="history-time"><?= esc($at) ?></p>

                                <?php if ($item['type'] === 'outage'): ?>
                                    <p class="history-meta">
                                        Mulai: <?= esc(date('d M Y, H:i:s', strtotime((string) $item['started']))) ?>
                                        <?php if (! empty($item['ended'])): ?>
                                            · Selesai: <?= esc(date('d M Y, H:i:s', strtotime((string) $item['ended']))) ?>
                                            · Durasi: <?= esc($formatDuration($item['duration'])) ?>
                                        <?php else: ?>
                                            · Masih DOWN
                                        <?php endif; ?>
                                    </p>
                                <?php else: ?>
                                    <p class="history-meta">
                                        HTTP: <?= $item['http'] !== null && $item['http'] !== '' ? esc((string) $item['http']) : '-' ?>
                                        · Response: <?= $item['ms'] !== null && $item['ms'] !== '' ? esc((string) $item['ms']) . ' ms' : '-' ?>
                                        <?php if (! empty($item['error'])): ?>
                                            · <?= esc((string) $item['error']) ?>
                                        <?php endif; ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <aside class="dash-panel side-panel">
            <div class="dash-panel-head">
                <div>
                    <h2>WhatsApp Notifikasi</h2>
                    <p><?= count($contacts) ?> nomor penerima alert.</p>
                </div>
            </div>

            <?php if ($contacts === []): ?>
                <p class="contact-empty">Belum ada nomor. Tambahkan minimal 1 nomor.</p>
            <?php else: ?>
                <ul class="contact-list">
                    <?php foreach ($contacts as $contact): ?>
                        <?php $contactId = (int) $contact['id']; ?>
                        <li>
                            <div class="contact-identity">
                                <span class="wa-mark" aria-hidden="true">WA</span>
                                <div>
                                    <strong>+<?= esc((string) $contact['phone_number']) ?></strong>
                                    <span>Notifikasi aktif</span>
                                </div>
                            </div>
                            <div class="contact-actions">
                                <button
                                    type="button"
                                    class="btn btn-outline btn-sm js-open-edit-wa"
                                    data-modal="edit-wa-<?= $contactId ?>"
                                >Edit</button>
                                <form method="post" action="<?= base_url('dashboard/contacts/' . $contactId . '/delete') ?>" onsubmit="return confirm('Hapus nomor ini?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="contact-delete" title="Hapus nomor" aria-label="Hapus nomor">×</button>
                                </form>
                            </div>

                            <div class="wa-modal" id="edit-wa-<?= $contactId ?>" hidden>
                                <div class="wa-modal-backdrop js-close-edit-wa" data-modal="edit-wa-<?= $contactId ?>"></div>
                                <div class="wa-modal-card" role="dialog" aria-modal="true" aria-labelledby="edit-wa-title-<?= $contactId ?>">
                                    <div class="wa-modal-head">
                                        <h3 id="edit-wa-title-<?= $contactId ?>">Edit nomor WhatsApp</h3>
                                        <button type="button" class="wa-modal-close js-close-edit-wa" data-modal="edit-wa-<?= $contactId ?>" aria-label="Tutup">×</button>
                                    </div>
                                    <form method="post" action="<?= base_url('dashboard/contacts/' . $contactId . '/update') ?>" class="wa-modal-form">
                                        <?= csrf_field() ?>
                                        <label for="edit_phone_<?= $contactId ?>">Nomor WhatsApp</label>
                                        <div class="input-prefix">
                                            <span class="input-prefix-label">+62</span>
                                            <input
                                                id="edit_phone_<?= $contactId ?>"
                                                name="phone_number"
                                                type="tel"
                                                value="<?= esc($localPhone((string) $contact['phone_number'])) ?>"
                                                inputmode="numeric"
                                                autocomplete="tel-national"
                                                required
                                            >
                                        </div>
                                        <div class="wa-modal-actions">
                                            <button type="button" class="btn btn-outline js-close-edit-wa" data-modal="edit-wa-<?= $contactId ?>">Batal</button>
                                            <button type="submit" class="btn btn-primary">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <form method="post" action="<?= base_url('dashboard/domains/' . $domainId . '/contacts') ?>" class="dash-form contact-add">
                <?= csrf_field() ?>
                <div class="field">
                    <label for="phone_<?= $domainId ?>">Tambah nomor</label>
                    <div class="input-prefix">
                        <span class="input-prefix-label">+62</span>
                        <input id="phone_<?= $domainId ?>" name="phone_number" type="tel" placeholder="81234567890" inputmode="numeric" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Tambah WA</button>
            </form>

            <details class="domain-settings">
                <summary>Edit URL domain</summary>
                <form method="post" action="<?= base_url('dashboard/domains/' . $domainId . '/update') ?>" class="dash-form">
                    <?= csrf_field() ?>
                    <div class="field">
                        <label for="domain_url_<?= $domainId ?>">URL Domain</label>
                        <input id="domain_url_<?= $domainId ?>" name="domain_url" type="text" value="<?= esc($domain['domain_url']) ?>" required>
                    </div>
                    <button type="submit" class="btn btn-outline btn-block">Simpan URL</button>
                </form>
            </details>
        </aside>
    </div>
</div>

<script>
    (function() {
        document.querySelectorAll('input[name="phone_number"]').forEach(function(input) {
            function sanitize(value) {
                var digits = value.replace(/\D/g, '');
                if (digits.startsWith('62')) digits = digits.slice(2);
                return digits.replace(/^0+/, '');
            }

            input.value = sanitize(input.value);
            input.addEventListener('input', function() {
                var cleaned = sanitize(input.value);
                if (cleaned !== input.value) input.value = cleaned;
            });
        });

        function setModal(id, open) {
            var modal = document.getElementById(id);
            if (!modal) return;
            modal.hidden = !open;
            document.body.classList.toggle('modal-open', open && !!document.querySelector('.wa-modal:not([hidden])'));
            if (open) {
                var input = modal.querySelector('input[name="phone_number"]');
                if (input) setTimeout(function() { input.focus(); input.select(); }, 40);
            }
        }

        document.querySelectorAll('.js-open-edit-wa').forEach(function(btn) {
            btn.addEventListener('click', function() {
                setModal(btn.getAttribute('data-modal'), true);
            });
        });

        document.querySelectorAll('.js-close-edit-wa').forEach(function(btn) {
            btn.addEventListener('click', function() {
                setModal(btn.getAttribute('data-modal'), false);
            });
        });

        document.addEventListener('keydown', function(e) {
            if (e.key !== 'Escape') return;
            document.querySelectorAll('.wa-modal:not([hidden])').forEach(function(modal) {
                modal.hidden = true;
            });
            document.body.classList.remove('modal-open');
        });
    })();
</script>
<?= $this->endSection() ?>
