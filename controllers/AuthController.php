<?php
class AuthController
{
    public function login(): void
    {
        if (auth_check()) redirect('?page=dashboard');
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $u = (new User())->findByUsername((string) post('username', ''));
            if ($u && (int) $u['is_active'] === 1 && password_verify((string) post('password', ''), $u['password'])) {
                auth_login($u);
                $dest = match (auth_role()) {
                    'petugas' => '?page=scan',
                    'instruktur' => '?page=absensi',
                    default => '?page=dashboard',
                };
                redirect($dest);
            }
            $error = 'Username atau password salah, atau akun tidak aktif.';
        }
        $GLOBALS['login_error'] = $error;
        require __DIR__ . '/../views/pages/login.php';
    }

    public function logout(): void
    {
        auth_logout();
        redirect('?page=login');
    }
}
