<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../models/Password.php';
require_once __DIR__ . '/../../phpmailer/PHPMailer.php';
require_once __DIR__ . '/../../phpmailer/SMTP.php';
require_once __DIR__ . '/../../phpmailer/Exception.php';

class PasswordController {
    private $model;

    public function __construct($pdo) {
        $this->model = new PasswordModel($pdo);
    }

    public function forgotPassword() {
        ob_start();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Please enter a valid email address.";
                require __DIR__ . '/../views/forgot_password.php';
                return ob_get_clean();
            }
            $user = $this->model->findUserByEmail($email);
            if ($user && $this->model->hasActiveResetToken($email)) {
                $error = "A reset link has already been sent. You can send only one request per hour. Entering now Guru Meditation..";
                require __DIR__ . '/../views/forgot_password.php';
                return ob_get_clean();
            }
            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));
                $this->model->saveResetToken($user['id'], $token, $expires);

                $resetLink = "https://danielpincu.dev/index.php?page=reset_password&token=" . urlencode($token);
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

        $user = $this->model->findUserByToken($token);
        if (!$user) {
            $error = "Invalid or expired token.";
            require __DIR__ . '/../views/reset_error.php';
            return ob_get_clean();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = trim($_POST['password']);
            $confirmPassword = trim($_POST['confirm_password']);

            if (empty($password) || empty($confirmPassword) || $password !== $confirmPassword) {
                $error = "Passwords do not match.";
                require __DIR__ . '/../views/reset_password.php';
                return ob_get_clean();
            }

            $newPassword = password_hash($password, PASSWORD_DEFAULT);
            $this->model->updatePassword($user['id'], $newPassword);
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
            $mail->Username   = 'echo@danielpincu.dev';
            $mail->Password   = 'password_here';  //Password here
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('echo@danielpincu.dev', 'Glitch Social');
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