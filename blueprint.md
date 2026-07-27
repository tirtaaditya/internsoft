# Blueprint Sistem Monitoring Uptime (CodeIgniter)

## 0. Identitas Brand
- Company Name: Internsoft Technology Solutions
- Domain Resmi: Internsoft.my.id
- Contact Point (CP): Internsoft Technology Solutions

## 1. Ringkasan
Project ini adalah aplikasi monitoring uptime domain berbasis CodeIgniter dengan fitur:
- Halaman awal berupa company profile sebelum autentikasi.
- Menampilkan badge/section Coming Soon untuk API WhatsApp.
- User wajib daftar akun dulu sebelum bisa login.
- User dapat menambahkan banyak domain untuk dimonitor.
- Setiap domain bisa punya banyak nomor HP penerima notifikasi.
- Dashboard menampilkan status domain (UP/DOWN), histori kejadian, dan metrik downtime.
- Sistem mencatat kapan service down, kapan up kembali, dan menghitung durasi downtime.

## 2. Kebutuhan Teknis
- Framework: CodeIgniter (disarankan CI4, bisa diadaptasi ke CI3).
- DB Server:
  - Host: 202.43.248.21
  - Username: app_internsoft
  - Password: InT3RNS0ft2026
  - Database: db_internsoft
- Scheduler:
  - Linux: cron tiap 1 menit.
  - Windows: Task Scheduler tiap 1 menit.
- HTTP client untuk ping domain: cURL (HEAD/GET) dengan timeout.

Catatan keamanan penting:
- Jangan hardcode kredensial DB di source code.
- Simpan di file environment (.env) dan batasi akses file.

## 3. Fitur Utama

### 3.0 Company Profile (Halaman Awal)
- Halaman pertama yang diakses user sebelum login/register.
- Menampilkan profil singkat Internsoft Technology Solutions.
- Menampilkan domain resmi: Internsoft.my.id.
- CTA utama:
  - Daftar Akun
  - Login
- Menampilkan status roadmap: Coming Soon - API WhatsApp.

### 3.1 Autentikasi
- Register:
  - Nama
  - Email (unik)
  - Nomor WA
  - Password
- Login:
  - Email + Password
- Logout
- Password disimpan dengan BCrypt + salt otomatis dari PHP:
  - password_hash($password, PASSWORD_BCRYPT)
  - verifikasi dengan password_verify($inputPassword, $hash)
- OTP WA saat registrasi:
  - Setelah register, sistem kirim OTP 6 digit ke nomor WA user.
  - User wajib verifikasi OTP sebelum akun bisa login ke dashboard.

Endpoint WA API OTP:
- URL: http://198.167.141.15/wa/api/rekonbdn/messages/send
- Method: POST JSON
- Payload:
  - to: nomor WA tujuan
  - message: isi pesan OTP

Template pesan OTP (lebih rapi):
- Halo {Nama},
- Selamat datang di Internsoft Technology Solutions.
- Kode OTP verifikasi akun Anda adalah: {OTP}.
- Kode ini berlaku 10 menit. Demi keamanan, jangan bagikan kode ini ke siapa pun.

### 3.2 Manajemen Domain
- Tambah domain (contoh: https://example.com)
- Edit domain
- Hapus domain
- Aktif/nonaktif monitoring domain
- Domain dimiliki oleh user (isolasi data per user)

### 3.3 Nomor HP Multiple per Domain
- Setiap domain memiliki banyak nomor HP notifikasi.
- CRUD nomor HP per domain.
- Validasi format nomor (contoh E.164 atau format lokal yang disepakati).

### 3.4 Monitoring Status
- Worker/scheduler cek setiap domain berkala (misalnya tiap 1 menit).
- Simpan hasil cek:
  - timestamp
  - status UP/DOWN
  - response time (ms)
  - HTTP code
  - error message jika ada

### 3.5 Downtime Tracking
- Saat status berubah dari UP -> DOWN:
  - Buat event outage dengan started_at.
- Saat status berubah dari DOWN -> UP:
  - Tutup event outage dengan ended_at.
  - Hitung duration_seconds = ended_at - started_at.
- Jika masih DOWN dan belum UP:
  - event outage tetap open (ended_at NULL).

### 3.6 Dashboard
- Ringkasan:
  - Total domain
  - Domain UP saat ini
  - Domain DOWN saat ini
  - Total downtime hari ini
- Tabel domain:
  - Domain
  - Status terakhir
  - Last checked
  - Uptime 24 jam (persen)
- Grafik/tren (opsional tahap 2):
  - Jumlah outage per hari
  - Total downtime per hari

### 3.7 Coming Soon - API WhatsApp
- Status: coming soon (belum aktif di rilis awal).
- Tujuan:
  - Kirim WhatsApp dari aplikasi lain melalui API.
- Ditampilkan di company profile dan dashboard sebagai roadmap fitur.

Roadmap versi:
- V1: Monitoring Server
- V2: Aplikasi WhatsApp

## 4. Arsitektur Modul

### 4.1 Web Layer (Controller + View)
- AuthController: register, login, logout.
- DomainController: CRUD domain.
- ContactController: CRUD nomor HP per domain.
- DashboardController: ringkasan status, list domain, histori.

### 4.2 Service Layer
- MonitorService:
  - checkDomain(domain): cek HTTP status.
  - processResult(domain, result): update status & outage event.
- NotificationService:
  - kirim notifikasi SMS/WA (integrasi gateway tahap berikutnya).

### 4.3 Data Layer (Model)
- UserModel
- DomainModel
- DomainContactModel
- DomainCheckModel
- OutageEventModel

## 5. Desain Database (Draft)

### 5.1 users
- id (bigint, PK)
- name (varchar 100)
- email (varchar 150, unique)
- wa_number (varchar 30)
- is_wa_verified (tinyint/bool, default 0)
- password_hash (varchar 255)
- otp_code_hash (varchar 255, null)
- otp_expires_at (datetime, null)
- otp_last_sent_at (datetime, null)
- created_at (datetime)
- updated_at (datetime)

### 5.2 domains
- id (bigint, PK)
- user_id (bigint, FK -> users.id)
- domain_url (varchar 255)
- is_active (tinyint/bool, default 1)
- last_status (enum: UP, DOWN, UNKNOWN)
- last_checked_at (datetime, null)
- created_at (datetime)
- updated_at (datetime)

Index penting:
- idx_domains_user_id
- idx_domains_is_active

### 5.3 domain_contacts
- id (bigint, PK)
- domain_id (bigint, FK -> domains.id)
- phone_number (varchar 30)
- created_at (datetime)
- updated_at (datetime)

Index penting:
- idx_contacts_domain_id

### 5.4 domain_checks
- id (bigint, PK)
- domain_id (bigint, FK -> domains.id)
- checked_at (datetime)
- status (enum: UP, DOWN)
- http_code (int, null)
- response_time_ms (int, null)
- error_message (text, null)

Index penting:
- idx_checks_domain_time (domain_id, checked_at)

### 5.5 outage_events
- id (bigint, PK)
- domain_id (bigint, FK -> domains.id)
- started_at (datetime)
- ended_at (datetime, null)
- duration_seconds (int, null)
- is_acknowledged (tinyint/bool, default 0)
- created_at (datetime)
- updated_at (datetime)

Index penting:
- idx_outage_domain_started (domain_id, started_at)
- idx_outage_open (domain_id, ended_at)

## 6. Alur Monitoring dan Hitung Downtime
1. Scheduler ambil semua domain aktif.
2. Lakukan HTTP check dengan timeout (misalnya 10 detik).
3. Simpan hasil ke domain_checks.
4. Bandingkan status baru vs last_status di domains.
5. Jika UP -> DOWN:
   - Insert outage_events dengan started_at sekarang, ended_at NULL.
6. Jika DOWN -> UP:
   - Cari outage open terbaru (ended_at NULL), isi ended_at sekarang.
   - Hitung duration_seconds.
7. Update domains.last_status dan domains.last_checked_at.

Rumus durasi downtime:
- duration_seconds = TIMESTAMPDIFF(SECOND, started_at, ended_at)
- Tampilkan dalam format manusia: jam, menit, detik.

## 7. Endpoint/Route (Draft)

Public:
- GET /

Auth:
- GET /register
- POST /register
- GET /login
- POST /login
- GET /verify-otp
- POST /verify-otp
- POST /resend-otp
- POST /logout

Domain:
- GET /domains
- GET /domains/create
- POST /domains
- GET /domains/{id}/edit
- POST /domains/{id}/update
- POST /domains/{id}/delete

Contacts:
- GET /domains/{id}/contacts
- POST /domains/{id}/contacts
- POST /contacts/{id}/update
- POST /contacts/{id}/delete

Dashboard:
- GET /dashboard
- GET /dashboard/outages
- GET /dashboard/checks

Worker (internal):
- CLI: php spark monitor:run

## 8. Validasi dan Rules
- Register:
  - Email wajib valid dan unik.
  - Password minimal 8 karakter.
- Domain:
  - Wajib URL valid.
  - Tidak boleh duplikat per user (opsional: unique user_id + domain_url).
- Phone:
  - Hanya karakter numerik + plus (+) di depan jika internasional.
  - Boleh multiple per domain.

## 9. Rencana Notifikasi
Tahap 1:
- OTP verifikasi register via API WhatsApp.

Tahap 2:
- Gunakan endpoint WA API yang sama untuk notifikasi status server.
- Kirim notifikasi saat:
  - domain DOWN (sekali saat transisi)
  - domain UP kembali (recovery notification)
- Tinggal ganti template message sesuai event (OTP / DOWN / UP).

## 10. Keamanan dan Operasional
- Pakai CSRF protection pada form.
- Session secure, regenerasi session saat login.
- Rate limit endpoint login.
- Logging error monitoring ke file log aplikasi.
- Backup database harian.

## 11. Tahapan Implementasi
1. Setup project CodeIgniter + koneksi DB via .env.
2. Buat migration tabel inti: users, domains, domain_contacts, domain_checks, outage_events.
3. Implement auth register/login.
4. Implement CRUD domain + multiple nomor HP.
5. Implement command monitor:run + scheduler.
6. Implement dashboard ringkasan + histori outage.
7. Tambah notifikasi provider eksternal.
8. UAT dan hardening.

## 12. Struktur Folder yang Disarankan (CI4)
- app/Controllers/AuthController.php
- app/Controllers/DashboardController.php
- app/Controllers/DomainController.php
- app/Controllers/ContactController.php
- app/Models/UserModel.php
- app/Models/DomainModel.php
- app/Models/DomainContactModel.php
- app/Models/DomainCheckModel.php
- app/Models/OutageEventModel.php
- app/Services/MonitorService.php
- app/Services/NotificationService.php
- app/Commands/MonitorRun.php
- app/Database/Migrations/
- app/Views/

## 13. Definition of Done
- User bisa register dan login.
- User bisa tambah minimal 1 domain dan >1 nomor HP.
- Scheduler berjalan dan mencatat status UP/DOWN.
- Saat domain down lalu up, downtime tercatat dengan durasi akurat.
- Dashboard menampilkan status terkini dan histori outage.
- Data antar user terisolasi dengan benar.

## 14. Draft Konten Company Profile (Siap Implementasi)

### 14.1 Hero Section
- Headline:
  - Monitoring Domain Bisnis Anda, Real-time dan Terukur.
- Subheadline:
  - Internsoft Technology Solutions membantu Anda memantau status website 24/7, mencatat downtime otomatis, dan menjaga keandalan layanan digital.
- Primary CTA:
  - Daftar Akun
- Secondary CTA:
  - Login
- Highlight kecil di bawah CTA:
  - Coming Soon: API WhatsApp |

### 14.2 Tentang Perusahaan
- Judul:
  - Tentang Internsoft Technology Solutions
- Isi singkat:
  - Internsoft Technology Solutions adalah penyedia solusi teknologi yang berfokus pada keandalan sistem, observability, dan otomasi monitoring untuk kebutuhan bisnis modern.
- Informasi identitas:
  - Domain resmi: Internsoft.my.id
  - Contact Point: Internsoft Technology Solutions

### 14.3 Layanan Utama (Ringkas)
- Uptime Monitoring Domain
- Tracking Insiden UP/DOWN
- Perhitungan Durasi Downtime Otomatis
- Dashboard Ringkasan Performa Layanan

### 14.4 Coming Soon Section
- Judul:
  - Coming Soon: API WhatsApp
- Deskripsi:
  - API WhatsApp disiapkan untuk kebutuhan kirim WhatsApp dari aplikasi lain.
- Status badge:
  - In Development

### 14.5 Footer Singkat
- Internsoft Technology Solutions
- Internsoft.my.id
- All rights reserved.

## 15. Struktur View Company Profile (CI4)
- app/Views/company_profile/index.php
- app/Views/layouts/main.php
- public/assets/css/company-profile.css

Contoh section urutan di view:
1. Navbar sederhana (logo + menu Login/Daftar)
2. Hero
3. Tentang Perusahaan
4. Layanan Utama
5. Coming Soon API WhatsApp
6. Footer

## 16. Route dan Controller Company Profile (CI4)
- Route:
  - GET / -> CompanyProfileController::index
- Controller:
  - app/Controllers/CompanyProfileController.php
  - method index() me-render view company_profile/index
