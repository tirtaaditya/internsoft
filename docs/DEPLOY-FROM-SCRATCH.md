# Deploy Internsoft — Runut dari Awal

Server contoh:
- IP: `198.167.141.15`
- PHP: `8.3`
- Nginx sudah ada aplikasi lama di `default` (`/Api/`, `/wa/`)
- Domain akhir: `https://internsoft.my.id`

Jangan hapus site `default` — API lama harus tetap hidup.

---

## A. Persiapan server

```bash
sudo apt update
sudo apt install -y nginx git unzip curl \
  php8.3-fpm php8.3-cli php8.3-mysql php8.3-curl \
  php8.3-mbstring php8.3-xml php8.3-zip php8.3-intl \
  composer
```

Cek:

```bash
php -v
# PHP 8.3.x
```

---

## B. Clone & install project

```bash
sudo mkdir -p /var/www
cd /var/www
sudo git clone https://github.com/tirtaaditya/internsoft.git internsoft
cd /var/www/internsoft
sudo composer install --no-dev --optimize-autoloader
```

Permission:

```bash
sudo chown -R www-data:www-data /var/www/internsoft
sudo find /var/www/internsoft/writable -type d -exec chmod 775 {} \;
sudo find /var/www/internsoft/writable -type f -exec chmod 664 {} \;
```

---

## C. Database & `.env`

```bash
cd /var/www/internsoft
sudo cp env .env
sudo nano .env
```

Isi sementara (masih pakai prefix dulu, boleh skip ke tahap domain langsung kalau DNS sudah siap):

```ini
CI_ENVIRONMENT = production

# Nanti diganti ke https://internsoft.my.id/
app.baseURL = 'http://api.kopperpensibdn.com/internsoft/'
app.indexPage = ''

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

## D. (Opsional) Tes cepat via `/internsoft`

Kalau mau tes dulu sebelum domain:

```bash
sudo ln -sfn /var/www/internsoft/public /var/www/html/internsoft
sudo nano /etc/nginx/sites-available/default
```

Tambahkan **sebelum** `location ~ \.php$`:

```nginx
    location = /internsoft {
        return 301 /internsoft/;
    }

    location /internsoft/ {
        try_files $uri $uri/ @internsoft;
    }

    location @internsoft {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME /var/www/internsoft/public/index.php;
        fastcgi_param SCRIPT_NAME /internsoft/index.php;
        fastcgi_param REQUEST_URI $request_uri;
        fastcgi_param DOCUMENT_ROOT /var/www/internsoft/public;
        fastcgi_read_timeout 120;
        fastcgi_buffers 16 16k;
        fastcgi_intercept_errors on;
    }
```

```bash
sudo nginx -t
sudo systemctl reload nginx
```

Tes: `http://api.kopperpensibdn.com/internsoft/`

Kalau CSS tidak muncul → pastikan sudah `git pull origin main` (kode `base_url`) dan `app.baseURL` ada `/internsoft/`.

---

## E. Domain `internsoft.my.id` + SSL (tahap final)

### E1. DNS

Di panel DNS:

```text
internsoft.my.id   A   198.167.141.15
www.internsoft.my.id   A   198.167.141.15
```

Cek:

```bash
dig +short internsoft.my.id
# 198.167.141.15
```

### E2. Buat file Nginx domain

```bash
sudo nano /etc/nginx/sites-available/internsoft.my.id
```

**Isi penuh:**

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

Tes HTTP: `http://internsoft.my.id/`

### E3. SSL Certbot

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d internsoft.my.id -d www.internsoft.my.id
```

Tes HTTPS: `https://internsoft.my.id/`

### E4. Update `.env` ke domain

```bash
sudo nano /var/www/internsoft/.env
```

```ini
CI_ENVIRONMENT = production
app.baseURL = 'https://internsoft.my.id/'
app.indexPage = ''
app.forceGlobalSecureRequests = true
```

```bash
sudo rm -f /var/www/internsoft/writable/cache/*
```

Hard refresh browser.

---

## F. Matikan `http://api.kopperpensibdn.com/internsoft/`

Jangan hapus file `default` — hanya hapus blok Internsoft.

```bash
sudo nano /etc/nginx/sites-available/default
```

Hapus semua yang mirip:

- `location = /internsoft { ... }`
- `location /internsoft/ { ... }`
- `location @internsoft { ... }`

**Jangan hapus:**

- `location /Api/`
- `location /uploads/`
- `location /wa/`
- `location ~ \.php$` (untuk API lama)

Opsional:

```bash
sudo rm -f /var/www/html/internsoft
```

```bash
sudo nginx -t
sudo systemctl reload nginx
```

---

## G. Cron monitoring

```bash
sudo crontab -u www-data -e
```

Tambah:

```cron
* * * * * cd /var/www/internsoft && /usr/bin/php spark monitor:run >> /var/www/internsoft/writable/logs/monitor-cron.log 2>&1
```

Tes:

```bash
cd /var/www/internsoft
sudo -u www-data php spark monitor:run
```

---

## H. Checklist akhir

| URL | Harapan |
|-----|---------|
| `https://internsoft.my.id/` | Internsoft OK + CSS |
| `https://internsoft.my.id/login` | Login OK |
| `http://internsoft.my.id/` | Redirect ke HTTPS |
| `http://api.kopperpensibdn.com/internsoft/` | **404 / mati** |
| `http://api.kopperpensibdn.com/Api/` | API lama OK |
| `http://api.kopperpensibdn.com/wa/` | WA proxy OK |

---

## I. Kalau error

```bash
# DNS
dig +short internsoft.my.id

# Nginx / PHP log
sudo nginx -t
sudo tail -n 50 /var/log/nginx/error.log
sudo tail -n 50 /var/www/internsoft/writable/logs/log-*.log

# Asset
curl -I https://internsoft.my.id/assets/css/company-profile.css

# Socket PHP
ls /var/run/php/
# harus ada php8.3-fpm.sock
```

**CSS polos** → `app.baseURL` salah / belum `git pull origin main`  
**File not found.** → `SCRIPT_FILENAME` Nginx salah  
**API lama mati** → jangan hapus `default`, jangan pakai `default_server` di site Internsoft
