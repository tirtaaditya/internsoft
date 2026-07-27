<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Services\WhatsAppService;

class AuthController extends BaseController
{
    public function register()
    {
        if ($this->request->is('post')) {
            try {
                $rules = [
                    'name'      => 'required|min_length[3]|max_length[100]',
                    'email'     => [
                        'rules'  => 'required|valid_email|max_length[150]|is_unique[users.email]',
                        'errors' => [
                            'required'    => 'Email wajib diisi.',
                            'valid_email' => 'Format email tidak valid.',
                            'is_unique'   => 'Email sudah terdaftar. Silakan login atau pakai email lain.',
                        ],
                    ],
                    'wa_number' => [
                        'rules'  => 'required|regex_match[/^0?[1-9][0-9]{7,13}$/]',
                        'errors' => [
                            'required'    => 'Nomor WhatsApp wajib diisi.',
                            'regex_match' => 'Nomor WhatsApp tidak valid. Contoh: 81234567890.',
                        ],
                    ],
                    'password'  => 'required|min_length[8]|max_length[72]',
                ];

                if (! $this->validate($rules)) {
                    return view('auth/register', [
                        'title'      => 'Daftar Akun',
                        'validation' => $this->validator,
                    ]);
                }

                $name     = trim((string) $this->request->getPost('name'));
                $email    = strtolower(trim((string) $this->request->getPost('email')));
                $waNumber = $this->normalizeWaNumber((string) $this->request->getPost('wa_number'));
                $otpCode  = $this->generateOtpCode();

                $userModel = new UserModel();

                if ($userModel->where('wa_number', $waNumber)->first()) {
                    return view('auth/register', [
                        'title' => 'Daftar Akun',
                        'error' => 'Nomor WhatsApp sudah terdaftar. Silakan login atau pakai nomor lain.',
                    ]);
                }

                $saved = $userModel->insert([
                    'name'          => $name,
                    'email'         => $email,
                    'wa_number'     => $waNumber,
                    'is_wa_verified'=> 0,
                    // Salt dikelola otomatis oleh password_hash.
                    'password_hash' => password_hash((string) $this->request->getPost('password'), PASSWORD_BCRYPT),
                    'otp_code_hash' => password_hash($otpCode, PASSWORD_BCRYPT),
                    'otp_expires_at'=> date('Y-m-d H:i:s', time() + 600),
                    'otp_last_sent_at' => date('Y-m-d H:i:s'),
                ]);

                if (! $saved) {
                    return view('auth/register', [
                        'title' => 'Daftar Akun',
                        'error' => 'Registrasi gagal. Silakan coba lagi.',
                    ]);
                }

                $userId = (int) $userModel->getInsertID();
                session()->set('otp_user_id', $userId);

                $sent = $this->sendOtpToWhatsApp($waNumber, $name, $otpCode);

                if (! $sent['success']) {
                    session()->setFlashdata('warning', 'Akun dibuat, namun OTP gagal dikirim. Klik kirim ulang OTP.');
                } else {
                    session()->setFlashdata('success', 'Akun berhasil dibuat. OTP sudah dikirim ke WhatsApp Anda.');
                }

                return redirect()->to('/verify-otp');
            } catch (\Throwable $e) {
                log_message('error', 'Register failed: {message}', ['message' => $e->getMessage()]);

                return view('auth/register', [
                    'title' => 'Daftar Akun',
                    'error' => 'Registrasi gagal: tidak bisa terhubung ke database. Coba lagi nanti atau cek koneksi DB.',
                ]);
            }
        }

        return view('auth/register', ['title' => 'Daftar Akun']);
    }

    public function verifyOtp()
    {
        $otpUserId = (int) session()->get('otp_user_id');

        if ($otpUserId <= 0) {
            return redirect()->to('/login');
        }

        $userModel = new UserModel();
        $user      = $userModel->find($otpUserId);

        if (! $user) {
            session()->remove('otp_user_id');
            return redirect()->to('/register');
        }

        if ((int) $user['is_wa_verified'] === 1) {
            session()->remove('otp_user_id');
            return redirect()->to('/login');
        }

        if ($this->request->is('post')) {
            $rules = [
                'otp_code' => 'required|numeric|exact_length[6]',
            ];

            if (! $this->validate($rules)) {
                return view('auth/verify_otp', [
                    'title'      => 'Verifikasi OTP',
                    'waNumber'   => (string) $user['wa_number'],
                    'validation' => $this->validator,
                ]);
            }

            if (empty($user['otp_expires_at']) || strtotime((string) $user['otp_expires_at']) < time()) {
                return view('auth/verify_otp', [
                    'title'    => 'Verifikasi OTP',
                    'waNumber' => (string) $user['wa_number'],
                    'error'    => 'Kode OTP sudah kadaluarsa. Silakan kirim ulang OTP.',
                ]);
            }

            $otpCode = (string) $this->request->getPost('otp_code');

            if (! password_verify($otpCode, (string) $user['otp_code_hash'])) {
                return view('auth/verify_otp', [
                    'title'    => 'Verifikasi OTP',
                    'waNumber' => (string) $user['wa_number'],
                    'error'    => 'Kode OTP tidak valid.',
                ]);
            }

            $userModel->update($otpUserId, [
                'is_wa_verified'  => 1,
                'otp_code_hash'   => null,
                'otp_expires_at'  => null,
            ]);

            session()->remove('otp_user_id');
            session()->regenerate();
            session()->set([
                'auth_user_id'    => (int) $user['id'],
                'auth_user_name'  => (string) $user['name'],
                'auth_user_email' => (string) $user['email'],
                'is_logged_in'    => true,
            ]);

            return redirect()->to('/dashboard');
        }

        return view('auth/verify_otp', [
            'title'    => 'Verifikasi OTP',
            'waNumber' => (string) $user['wa_number'],
        ]);
    }

    public function resendOtp()
    {
        $otpUserId = (int) session()->get('otp_user_id');

        if ($otpUserId <= 0) {
            return redirect()->to('/login');
        }

        $userModel = new UserModel();
        $user      = $userModel->find($otpUserId);

        if (! $user || (int) $user['is_wa_verified'] === 1) {
            session()->remove('otp_user_id');
            return redirect()->to('/login');
        }

        $otpCode = $this->generateOtpCode();

        $userModel->update($otpUserId, [
            'otp_code_hash'    => password_hash($otpCode, PASSWORD_BCRYPT),
            'otp_expires_at'   => date('Y-m-d H:i:s', time() + 600),
            'otp_last_sent_at' => date('Y-m-d H:i:s'),
        ]);

        $sent = $this->sendOtpToWhatsApp((string) $user['wa_number'], (string) $user['name'], $otpCode);

        if (! $sent['success']) {
            session()->setFlashdata('error', 'Gagal kirim ulang OTP. Silakan coba lagi.');
        } else {
            session()->setFlashdata('success', 'OTP baru sudah dikirim ke WhatsApp Anda.');
        }

        return redirect()->to('/verify-otp');
    }

    public function login()
    {
        if ($this->request->is('post')) {
            $rules = [
                'email'    => 'required|valid_email',
                'password' => 'required',
            ];

            if (! $this->validate($rules)) {
                return view('auth/login', [
                    'title'      => 'Login',
                    'validation' => $this->validator,
                ]);
            }

            $email    = strtolower(trim((string) $this->request->getPost('email')));
            $password = (string) $this->request->getPost('password');

            $userModel = new UserModel();
            $user      = $userModel->where('email', $email)->first();

            if (! $user || ! password_verify($password, (string) $user['password_hash'])) {
                return view('auth/login', [
                    'title' => 'Login',
                    'error' => 'Email atau password salah.',
                ]);
            }

            if ((int) ($user['is_wa_verified'] ?? 0) !== 1) {
                session()->set('otp_user_id', (int) $user['id']);
                return redirect()->to('/verify-otp')->with('warning', 'Akun Anda belum verifikasi WhatsApp. Masukkan OTP terlebih dahulu.');
            }

            session()->regenerate();
            session()->set([
                'auth_user_id'    => (int) $user['id'],
                'auth_user_name'  => (string) $user['name'],
                'auth_user_email' => (string) $user['email'],
                'is_logged_in'    => true,
            ]);

            return redirect()->to('/dashboard');
        }

        return view('auth/login', ['title' => 'Login']);
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/login');
    }

    private function normalizeWaNumber(string $waNumber): string
    {
        $digits = preg_replace('/\D/', '', trim($waNumber)) ?? '';

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '62')) {
            return $digits;
        }

        return '62' . ltrim($digits, '0');
    }

    private function generateOtpCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * @return array{success: bool, status: int|null, response: string|null, error: string|null}
     */
    private function sendOtpToWhatsApp(string $waNumber, string $name, string $otpCode): array
    {
        $message = "Halo {$name},\n"
            . "Selamat datang di Internsoft Technology Solutions.\n"
            . "Kode OTP verifikasi akun Anda adalah: {$otpCode}.\n"
            . "Kode ini berlaku 10 menit. Demi keamanan, jangan bagikan kode ini ke siapa pun.";

        $service = new WhatsAppService();
        return $service->sendMessage($waNumber, $message);
    }
}
