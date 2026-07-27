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

Contoh akses sementara:

```text
http://198.167.141.15/internsoft
```

### 4.1 Symlink public ke document root

Karena `root` Nginx kamu sudah `/var/www/html`:

```bash
sudo ln -sfn /var/www/internsoft/public /var/www/html/internsoft
ls -la /var/www/html/internsoft
```

### 4.2 Edit `.env`

```ini
CI_ENVIRONMENT = production
app.baseURL = 'http://198.167.141.15/internsoft/'
app.indexPage = ''
```

Trailing slash `/` di akhir `baseURL` wajib.  
`app.indexPage = ''` penting supaya URL bersih (tanpa `index.php`) di balik Nginx rewrite.

### 4.3 Tambah location di `default`

```bash
sudo nano /etc/nginx/sites-available/default
```

Tambahkan **di dalam** `server { ... }` yang sudah ada, **sebelum**
`location / { return 444; }` supaya `/internsoft` tidak tertolak:

```nginx
    # ===== Internsoft (CI4) =====
    location /internsoft {
        try_files $uri $uri/ /internsoft/index.php?$query_string;
    }

    location ~ ^/internsoft/index\.php(/|$) {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_index index.php;
        fastcgi_read_timeout 120;
        fastcgi_buffers 16 16k;
        fastcgi_intercept_errors on;
    }

    # Blokir PHP lain di folder internsoft (kecuali index.php di atas)
    location ~ ^/internsoft/(.+\.php)$ {
        deny all;
    }
    # ===== /Internsoft =====
```

Urutan lokasi yang aman di file `default` kira-kira:

```text
1. location ~ \.php$          (yang sudah ada — untuk /Api dll)
2. location /internsoft       (baru)
3. location ~ ^/internsoft/index\.php  (baru)
4. location ~ ^/internsoft/(.+\.php)$  (baru)
5. location /                 (return 444 — biarkan)
6. location /Api/ ...
7. location /wa/ ...
```

> Catatan: di Nginx, regex `location ~ \.php$` bisa menangkap
> `/internsoft/index.php` lebih dulu. Kalau PHP Internsoft aneh/404,
> pindahkan blok `location ~ ^/internsoft/index\.php` **di atas**
> `location ~ \.php$`, atau ubah jadi `location ^~ /internsoft/`
> untuk static + rewrite terpisah.

Varian lebih aman (prefix menang sebelum regex PHP global):

```nginx
    # Letakkan SEBELUM "location ~ \.php$"
    location ^~ /internsoft/ {
        try_files $uri $uri/ /internsoft/index.php?$query_string;

        location ~ \.php(?:$|/) {
            include fastcgi_params;
            fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            fastcgi_index index.php;
            fastcgi_read_timeout 120;
            fastcgi_buffers 16 16k;
            fastcgi_intercept_errors on;
        }
    }

    location = /internsoft {
        return 301 /internsoft/;
    }
```

### 4.4 Reload (tanpa hapus default)

```bash
# Pastikan site internsoft terpisah TIDAK aktif
sudo rm -f /etc/nginx/sites-enabled/internsoft

sudo nginx -t
sudo systemctl reload nginx
```

Cek:

```text
http://198.167.141.15/Api/          → aplikasi lama
http://198.167.141.15/wa/           → aplikasi lama
http://198.167.141.15/internsoft/   → Internsoft
http://198.167.141.15/internsoft/login
```

Host sudah diizinkan di config kamu (`198.167.141.15`), jadi akses via IP aman.

### 4.5 Kalau sempat merusak aplikasi lama

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

## 5. Tahap 2 — Domain `https://internsoft.my.id` (tanpa prefix)

### 5.1 DNS

Arahkan A record:

```text
internsoft.my.id  →  IP publik server
```

### 5.2 Update `.env`

```bash
sudo nano /var/www/internsoft/.env
```

Ganti jadi:

```ini
CI_ENVIRONMENT = production
app.baseURL = 'https://internsoft.my.id/'
app.forceGlobalSecureRequests = true
```

### 5.3 Nginx domain + SSL

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo nano /etc/nginx/sites-available/internsoft.my.id
```

Isi HTTP dulu (untuk certbot):

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
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_read_timeout 120;
    }

    location ~ /\. {
        deny all;
    }

    # Jangan expose folder app/writable lewat web
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

SSL:

```bash
sudo certbot --nginx -d internsoft.my.id -d www.internsoft.my.id
```

Certbot biasanya otomatis menambah redirect HTTPS.

### 5.4 Opsional: matikan akses IP/prefix lama

Kalau sudah full domain, bisa nonaktifkan site IP:

```bash
sudo rm -f /etc/nginx/sites-enabled/internsoft
sudo nginx -t
sudo systemctl reload nginx
```

Atau biarkan IP tetap hidup untuk internal, tapi ingat `baseURL` sudah domain — link generate akan mengarah ke `https://internsoft.my.id`.

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
3. .env baseURL = http://IP/internsoft/
4. Nginx location /internsoft (atau symlink ke /var/www/html/internsoft)
5. Tes IP
6. DNS A record → IP
7. .env baseURL = https://internsoft.my.id/
8. Nginx root = .../public + certbot
9. Cron: php spark monitor:run
```
