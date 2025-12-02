<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../phpmailer/PHPMailer.php';
require_once __DIR__ . '/../../phpmailer/SMTP.php';
require_once __DIR__ . '/../../phpmailer/Exception.php';

class ResetController {
    private $resetModel;
    private $session;
    private $pdo;

    public function __construct($pdo, $session, $resetModel) {
        $this->pdo = $pdo;
        $this->session = $session;
        $this->resetModel = $resetModel;
    }

    public function showForgotPassword() {
        $session = $this->session;
        $skipUI = true;
        $skipNotifications = true;
        require __DIR__ . '/../views/header.php';
        echo $this->forgotPassword();
        require __DIR__ . '/../views/footer.php';
    }

    public function showResetPassword() {
        $session = $this->session;
        $skipUI = true;
        $skipNotifications = true;
        require __DIR__ . '/../views/header.php';
        echo $this->resetPassword();
        require __DIR__ . '/../views/footer.php';
    }

    public function forgotPassword() {
        ob_start();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
                $error = "Invalid session token. Please try again.";
                require __DIR__ . '/../views/forgot_password.php';
                return ob_get_clean();
            }
            $email = trim($_POST['email']);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Please enter a valid email address.";
                require __DIR__ . '/../views/forgot_password.php';
                return ob_get_clean();
            }
            $user = $this->resetModel->findUserByEmail($email);
            if ($user && $this->resetModel->hasActiveResetToken($email)) {
                $error = "A reset link has already been sent. You can send only one request per hour. Entering now Guru Meditation..";
                require __DIR__ . '/../views/forgot_password.php';
                return ob_get_clean();
            }
            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));
                $this->resetModel->saveResetToken($user['id'], $token, $expires);

                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
                $host = $_SERVER['HTTP_HOST'] ?? '';
                $baseUrl = $protocol . rtrim($host, '/');
                $resetLink = $baseUrl . "/index.php?page=reset_password&token=" . urlencode($token);
                $this->sendResetEmail($user['email'], $user['username'], $resetLink);
            }

            $message = "If that email exists, a password reset link has been sent.";
            require __DIR__ . '/../views/forgot_password.php';
            return ob_get_clean();
        }

        require __DIR__ . '/../views/forgot_password.php';
        return ob_get_clean();
    }

    public function resetPassword() {
        ob_start();
        $token = $_GET['token'] ?? null;
        if (!$token) {
            $error = "Invalid request.";
            require __DIR__ . '/../views/reset_error.php';
            return ob_get_clean();
        }

        $user = $this->resetModel->findUserByToken($token);
        if (!$user) {
            $error = "Invalid or expired token.";
            require __DIR__ . '/../views/reset_error.php';
            return ob_get_clean();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = trim($_POST['password']);
            $confirmPassword = trim($_POST['confirm_password']);

            if (strlen($password) < 6) {
                $error = "Password must be at least 6 characters long.";
            } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[^a-zA-Z0-9]/', $password) || !preg_match('/[0-9]/', $password)) {
                $error = "Password must contain at least one uppercase letter, one symbol, and one number.";
            } elseif (empty($password) || empty($confirmPassword) || $password !== $confirmPassword) {
                $error = "Passwords do not match.";
            }

            if (isset($error)) {
                require __DIR__ . '/../views/reset_password.php';
                return ob_get_clean();
            }

            $newPassword = password_hash($password, PASSWORD_DEFAULT);
            $this->resetModel->updatePassword($user['id'], $newPassword);
            require __DIR__ . '/../views/reset_success.php';
            return ob_get_clean();
        }

        require __DIR__ . '/../views/reset_password.php';
        return ob_get_clean();
    }

    private function sendResetEmail($to, $username, $link) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'websmtp.simply.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $_SERVER['APP_EMAIL'];
            $mail->Password   = $_SERVER['SMTP_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom($_SERVER['APP_EMAIL'], 'Glitch Social');
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = 'Reset your Glitch Social password';
            ob_start();
            $safeLink = htmlspecialchars($link, ENT_QUOTES);
            $safeUser = htmlspecialchars($username, ENT_QUOTES);
            require __DIR__ . '/../views/email_view.php';
            $mail->Body = ob_get_clean();
            $mail->send();
        } catch (Exception $e) {
            error_log('Mail Error: ' . $mail->ErrorInfo);
        }
    }
}