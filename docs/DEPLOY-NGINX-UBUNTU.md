# Deploy Internsoft di Nginx (Ubuntu)

Panduan install aplikasi Internsoft (CodeIgniter 4) di Ubuntu + Nginx.

Dua tahap:

1. **Sementara** lewat IP + prefix: `http://10.30.20.32/internsoft`
2. **Produksi** lewat domain tanpa prefix: `https://internsoft.my.id`

> Ganti `10.30.20.32` dengan IP server Anda.  
> (Catatan: `10.30.20.322` tidak valid sebagai IP — tiap oktet maksimal 255.)

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

## 4. Tahap 1 — Nginx dengan prefix `/internsoft`

Buat config:

```bash
sudo nano /etc/nginx/sites-available/internsoft
```

Isi:

```nginx
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    server_name 10.30.20.32;

    client_max_body_size 20M;

    # Aplikasi di http://10.30.20.32/internsoft
    location /internsoft {
        alias /var/www/internsoft/public;
        index index.php index.html;

        try_files $uri $uri/ @internsoft_ci;

        location ~ ^/internsoft/(.+\.php)$ {
            include fastcgi_params;
            fastcgi_param SCRIPT_FILENAME /var/www/internsoft/public/$1;
            fastcgi_pass unix:/run/php/php8.3-fpm.sock;
            fastcgi_index index.php;
            fastcgi_read_timeout 120;
        }

        location ~* ^/internsoft/(.+\.(js|css|png|jpg|jpeg|gif|ico|svg|woff2?))$ {
            alias /var/www/internsoft/public/$1;
            expires 7d;
            access_log off;
        }
    }

    location @internsoft_ci {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME /var/www/internsoft/public/index.php;
        fastcgi_param REQUEST_URI $uri;
        # CI4 butuh path relatif terhadap baseURL /internsoft/
        fastcgi_param SCRIPT_NAME /internsoft/index.php;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }
}
```

> Kalau rewrite path bermasalah di subdirectory, alternatif paling stabil: lihat bagian **Tips alias** di bawah.

Aktifkan site:

```bash
sudo rm -f /etc/nginx/sites-enabled/default
sudo ln -sf /etc/nginx/sites-available/internsoft /etc/nginx/sites-enabled/internsoft
sudo nginx -t
sudo systemctl reload nginx
sudo systemctl reload php8.3-fpm
```

Cek di browser:

```text
http://10.30.20.32/internsoft
http://10.30.20.32/internsoft/login
```

### Tips alias (kalau 404 / asset pecah)

Subdirectory + `alias` di Nginx sering ribet. Cara paling aman:

```bash
sudo mkdir -p /var/www/html
sudo ln -sfn /var/www/internsoft/public /var/www/html/internsoft
```

Lalu config lebih sederhana:

```nginx
server {
    listen 80 default_server;
    server_name 10.30.20.32;
    root /var/www/html;
    index index.php index.html;
    client_max_body_size 20M;

    location /internsoft {
        try_files $uri $uri/ /internsoft/index.php?$query_string;
    }

    location ~ ^/internsoft/index\.php(/|$) {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_read_timeout 120;
    }

    location ~ ^/internsoft/(.+\.php)$ {
        deny all;
    }
}
```

Pastikan `.env`:

```ini
app.baseURL = 'http://10.30.20.32/internsoft/'
```

Trailing slash `/` di akhir `baseURL` wajib.

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
