# Deploy Internsoft di Nginx (Ubuntu)

Panduan install aplikasi Internsoft (CodeIgniter 4) di Ubuntu + Nginx.

Dua tahap:

1. **Sementara** lewat IP + prefix: `http://198.167.141.15/internsoft`
2. **Produksi** lewat domain tanpa prefix: `https://internsoft.my.id`

> Ganti IP jika berbeda. Contoh di server ini: `198.167.141.15`.

---

## 1. Persiapan server

```bash
sudo apt update
sudo apt install -y nginx php8.3-fpm php8.3-cli php8.3-mysql php8.3-curl \
  php8.3-mbstring php8.3-xml php8.3-zip php8.3-intl unzip git curl
```

Cek PHP:

```bash
php -v   # contoh di server: PHP 8.3.x (minimal 8.2)
```

Install Composer (kalau belum ada):

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer -V
```

---

## 2. Clone project

```bash
sudo mkdir -p /var/www
cd /var/www
sudo git clone https://github.com/tirtaaditya/internsoft.git internsoft
cd /var/www/internsoft
```

Install dependency:

```bash
sudo composer install --no-dev --optimize-autoloader
```

Permission folder writable:

```bash
sudo chown -R www-data:www-data /var/www/internsoft
sudo find /var/www/internsoft/writable -type d -exec chmod 775 {} \;
sudo find /var/www/internsoft/writable -type f -exec chmod 664 {} \;
```

---

## 3. Environment (`.env`)

```bash
cd /var/www/internsoft
sudo cp env .env
sudo nano .env
```

Isi minimal:

```ini
CI_ENVIRONMENT = production

# Tahap 1 (IP + prefix) — pakai ini dulu:
app.baseURL = 'http://10.30.20.32/internsoft/'

# Database
database.default.hostname = 127.0.0.1
database.default.database = db_internsoft
database.default.username = app_internsoft
database.default.password = GANTI_PASSWORD
database.default.DBDriver = MySQLi
database.default.port = 3306
database.default.charset = utf8mb4
database.default.DBCollat = utf8mb4_unicode_ci
```

Import schema:

```bash
mysql -u app_internsoft -p db_internsoft < /var/www/internsoft/database/db_internsoft_schema.sql
```

---

## 4. Tahap 1 — Tambah Internsoft ke Nginx yang sudah ada

Di server ini sudah ada aplikasi di `/etc/nginx/sites-available/default`
(`/Api/`, `/uploads/`, `/wa/`). **Jangan buat site baru yang merebut port 80.**
Cukup tambah path `/internsoft` di file `default`.

Contoh akses sementara (keduanya harus boleh):

```text
http://198.167.141.15/internsoft/
http://api.kopperpensibdn.com/internsoft/
```

### 4.1 Pastikan file project ada

```bash
ls -la /var/www/internsoft/public/index.php
ls -la /var/www/internsoft/public/assets/css/company-profile.css
```

### 4.2 Symlink (opsional, untuk asset static)

```bash
sudo ln -sfn /var/www/internsoft/public /var/www/html/internsoft
ls -la /var/www/html/internsoft/index.php
```

### 4.3 Edit `.env`

Pakai host yang dibuka di browser:

```ini
CI_ENVIRONMENT = production
app.baseURL = 'http://api.kopperpensibdn.com/internsoft/'
app.indexPage = ''
```

Trailing slash `/` di akhir `baseURL` wajib.

### 4.4 Config Nginx yang memperbaiki "File not found."

Pesan **`File not found.`** biasanya dari PHP-FPM (`Primary script unknown`),
bukan dari CodeIgniter. Artinya path `SCRIPT_FILENAME` salah.

```bash
sudo nano /etc/nginx/sites-available/default
```

Hapus blok `/internsoft` lama, lalu tempel ini **SEBELUM** `location ~ \.php$`:

```nginx
    # ===== Internsoft (CI4) =====
    # Butuh: sudo ln -sfn /var/www/internsoft/public /var/www/html/internsoft
    location = /internsoft {
        return 301 /internsoft/;
    }

    location /internsoft/ {
        # File statis (css/img/js) dilayani lewat symlink di /var/www/html/internsoft
        try_files $uri $uri/ @internsoft;
    }

    location @internsoft {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        # Path absolut — hindari "File not found." / Primary script unknown
        fastcgi_param SCRIPT_FILENAME /var/www/internsoft/public/index.php;
        fastcgi_param SCRIPT_NAME /internsoft/index.php;
        fastcgi_param REQUEST_URI $request_uri;
        fastcgi_param DOCUMENT_ROOT /var/www/internsoft/public;
        fastcgi_read_timeout 120;
        fastcgi_buffers 16 16k;
        fastcgi_intercept_errors on;
    }
    # ===== /Internsoft =====
```

> Jangan nested `location ~ \.php` di dalam `alias` — itu sumber paling umum `File not found.`

Diagnosa:

```bash
curl -I http://127.0.0.1/internsoft/ -H 'Host: api.kopperpensibdn.com'
sudo tail -n 30 /var/log/nginx/error.log
```

### 4.5 Reload (tanpa hapus default)

```bash
sudo rm -f /etc/nginx/sites-enabled/internsoft
sudo nginx -t
sudo systemctl reload nginx
sudo systemctl reload php8.3-fpm
```

Cek:

```text
http://api.kopperpensibdn.com/Api/        → aplikasi lama
http://api.kopperpensibdn.com/wa/         → aplikasi lama
http://api.kopperpensibdn.com/internsoft/ → Internsoft
http://198.167.141.15/internsoft/         → Internsoft
```

Kalau sempat merusak aplikasi lama:

```bash
sudo ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default
sudo rm -f /etc/nginx/sites-enabled/internsoft
sudo nginx -t
sudo systemctl reload nginx
```

---

## 4b. (Opsional) Site terpisah — hanya jika server masih kosong

Jangan pakai bagian ini di server yang sudah punya `/Api` dan `/wa`.
Simpan sebagai referensi saja.

```bash
sudo nano /etc/nginx/sites-available/internsoft
```

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name 198.167.141.15;
    # JANGAN pakai default_server

    client_max_body_size 20M;
    root /var/www/html;

    location /internsoft {
        try_files $uri $uri/ /internsoft/index.php?$query_string;
    }

    location ~ ^/internsoft/index\.php(/|$) {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_read_timeout 120;
    }
}
```

---

### Tips symlink (wajib untuk cara di atas)

```bash
sudo ln -sfn /var/www/internsoft/public /var/www/html/internsoft
```

Pastikan `.env`:

```ini
app.baseURL = 'http://198.167.141.15/internsoft/'
app.indexPage = ''
```

Trailing slash `/` di akhir `baseURL` wajib.

### CSS/JS tidak muncul di `/internsoft`

Biasanya karena:

1. `app.baseURL` belum pakai prefix `/internsoft/`
2. Kode masih hardcode `/assets/...` (sudah diganti ke `base_url()`)
3. Symlink belum ada: `ls -la /var/www/html/internsoft/assets`

Tes langsung:

```text
http://198.167.141.15/internsoft/assets/css/company-profile.css
```

Kalau 404 → masalah Nginx/symlink. Kalau 200 tapi halaman masih polos → hard refresh (`Ctrl+Shift+R`) atau `baseURL` salah.

---

## 5. Pindah ke `https://internsoft.my.id` + matikan `/internsoft`

Tujuan akhir:

| Sebelum | Sesudah |
|---------|---------|
| `http://api.kopperpensibdn.com/internsoft/` | **mati** (404 / hilang) |
| `http://198.167.141.15/internsoft/` | **mati** |
| — | `https://internsoft.my.id/` **hidup + SSL** |
| `/Api/`, `/wa/` di api.kopperpensibdn.com | **tetap jalan** (tidak diubah) |

### 5.1 DNS

Di DNS domain `internsoft.my.id`, buat A record:

```text
@     A    198.167.141.15
www   A    198.167.141.15
```

Cek propagasi:

```bash
dig +short internsoft.my.id
# harus keluar 198.167.141.15
```

### 5.2 Buat site Nginx khusus domain (tanpa prefix)

```bash
sudo nano /etc/nginx/sites-available/internsoft.my.id
```

Isi:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name internsoft.my.id www.internsoft.my.id;

    root /var/www/internsoft/public;
    index index.php index.html;
    client_max_body_size 20M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_index index.php;
        fastcgi_read_timeout 120;
        fastcgi_buffers 16 16k;
        fastcgi_intercept_errors on;
    }

    location ~ /\. {
        deny all;
    }

    # Jangan expose folder sensitif (kalau ada yang salah root)
    location ~ ^/(app|writable|tests|vendor|spark)/ {
        deny all;
    }
}
```

Aktifkan:

```bash
sudo ln -sf /etc/nginx/sites-available/internsoft.my.id /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

Tes dulu HTTP:

```text
http://internsoft.my.id/
```

### 5.3 Pasang SSL (Certbot)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d internsoft.my.id -d www.internsoft.my.id
```

Ikuti prompt (email, agree ToS). Certbot akan:
- buat sertifikat Let's Encrypt
- ubah config jadi HTTPS
- redirect `http://` → `https://`

Cek:

```text
https://internsoft.my.id/
https://internsoft.my.id/login
```

Perpanjang otomatis biasanya sudah ada via timer:

```bash
sudo systemctl status certbot.timer
```

### 5.4 Update `.env` ke domain HTTPS

```bash
sudo nano /var/www/internsoft/.env
```

```ini
CI_ENVIRONMENT = production
app.baseURL = 'https://internsoft.my.id/'
app.indexPage = ''
app.forceGlobalSecureRequests = true
```

Simpan, lalu clear cache CI kalau ada:

```bash
sudo rm -f /var/www/internsoft/writable/cache/*
```

### 5.5 Matikan akses lama `/internsoft` di site `default`

Ini yang mematikan `http://api.kopperpensibdn.com/internsoft/`.

```bash
sudo nano /etc/nginx/sites-available/default
```

**Hapus seluruh blok** yang terkait Internsoft, misalnya:

- `location = /internsoft { ... }`
- `location /internsoft/ { ... }`
- `location @internsoft { ... }`
- atau blok `alias` / nested PHP `/internsoft` lainnya

**Jangan hapus** yang ini:

- `location /Api/`
- `location /uploads/`
- `location /wa/`
- `location ~ \.php$` (untuk API lama)

Opsional — ganti jadi 404 eksplisit (kalau mau pesan jelas):

```nginx
    location /internsoft {
        return 404;
    }
```

Hapus symlink (opsional, biar bersih):

```bash
sudo rm -f /var/www/html/internsoft
```

Reload:

```bash
sudo nginx -t
sudo systemctl reload nginx
```

### 5.6 Checklist akhir

| URL | Harapan |
|-----|---------|
| `https://internsoft.my.id/` | Internsoft OK + CSS |
| `https://internsoft.my.id/login` | Login OK |
| `http://internsoft.my.id/` | Redirect ke HTTPS |
| `http://api.kopperpensibdn.com/internsoft/` | **404 / mati** |
| `http://api.kopperpensibdn.com/Api/` | API lama tetap jalan |
| `http://api.kopperpensibdn.com/wa/` | WA proxy tetap jalan |

### 5.7 Troubleshooting domain

**DNS belum mengarah**

```bash
dig +short internsoft.my.id
```

**Certbot gagal** — pastikan port 80 terbuka ke internet dan DNS sudah benar.

**CSS pecah di domain** — cek `app.baseURL` sudah `https://internsoft.my.id/` (dengan `/` di akhir), hard refresh.

**API lama ikut rusak** — berarti kamu tidak sengaja menghapus `location /Api/` atau `/wa/`. Restore dari backup config:

```bash
sudo nano /etc/nginx/sites-available/default
# atau
sudo nginx -t
```

---

## 6. Cron monitor uptime

Jalankan pengecekan domain berkala (contoh tiap 1 menit):

```bash
sudo crontab -u www-data -e
```

Tambahkan:

```cron
* * * * * cd /var/www/internsoft && /usr/bin/php spark monitor:run >> /var/www/internsoft/writable/logs/monitor-cron.log 2>&1
```

Tes manual:

```bash
cd /var/www/internsoft
sudo -u www-data php spark monitor:run
```

---

## 7. Checklist cepat

| Item | Prefix IP | Domain |
|------|-----------|--------|
| URL | `http://IP/internsoft/` | `https://internsoft.my.id/` |
| `app.baseURL` | `http://IP/internsoft/` | `https://internsoft.my.id/` |
| Nginx `root`/`alias` | `/internsoft` → `public` | `root` = `.../public` |
| SSL | tidak wajib | Certbot |
| Document root | bukan folder project, tapi **`public/`** | sama |

---

## 8. Troubleshooting

**404 di semua route**  
- Cek `try_files` / rewrite ke `index.php`  
- Cek `app.baseURL` ada trailing slash

**CSS/JS tidak load**  
- Biasanya `baseURL` salah, atau symlink `/var/www/html/internsoft` belum benar

**Permission denied / tidak bisa tulis log/session**  
```bash
sudo chown -R www-data:www-data /var/www/internsoft/writable
```

**Blank page**  
```bash
sudo tail -n 50 /var/www/internsoft/writable/logs/log-*.log
sudo tail -n 50 /var/log/nginx/error.log
```

Sementara debug:

```ini
CI_ENVIRONMENT = development
```

Kembalikan ke `production` setelah beres.

**PHP socket salah**  
```bash
ls /run/php/
# sesuaikan: php8.3-fpm.sock / php8.3-fpm.sock
```

---

## Ringkas alur

```text
1. apt install nginx + php8.3-fpm + composer
2. git clone → composer install → chown writable
3. Sementara: /internsoft di site default + baseURL prefix
4. DNS A record internsoft.my.id → 198.167.141.15
5. Buat site Nginx internsoft.my.id (root = public/)
6. certbot --nginx -d internsoft.my.id -d www.internsoft.my.id
7. .env baseURL = https://internsoft.my.id/
8. Hapus location /internsoft dari default (Api/wa tetap)
9. Cron: php spark monitor:run
```
