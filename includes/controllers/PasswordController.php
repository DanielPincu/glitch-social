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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $user = $this->model->findUserByEmail($email);

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));
                $this->model->saveResetToken($user['id'], $token, $expires);

                $resetLink = "https://danielpincu.dev/index.php?page=reset_password&token=$token";
                $this->sendResetEmail($user['email'], $resetLink);
            }

            $message = "If that email exists, a password reset link has been sent.";
            require __DIR__ . '/../views/forgot_password.php';
            return;
        }

        require __DIR__ . '/../views/forgot_password.php';
    }

    public function resetPassword() {
        $token = $_GET['token'] ?? null;
        if (!$token) {
            echo "<p>Invalid request.</p>";
            return;
        }

        $user = $this->model->findUserByToken($token);
        if (!$user) {
            echo "<p>Invalid or expired token.</p>";
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $this->model->updatePassword($user['id'], $newPassword);
            echo "<p>Password updated successfully. <a href='index.php?page=login'>Login now</a></p>";
            return;
        }

        require __DIR__ . '/../views/reset_password.php';
    }

    private function sendResetEmail($to, $link) {
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
            $mail->Body = "
    <div style='font-family: Arial, sans-serif; color: #222; background-color: #f4f6fa; padding: 30px;'>
        <div style='max-width: 600px; margin: auto; background: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); overflow: hidden;'>
            <div style='background: linear-gradient(135deg, #3A6EA5, #5CACEE); padding: 20px; text-align: center;'>
                <h2 style='color: #ffffff; margin: 0; font-size: 24px;'>Glitch Social</h2>
            </div>
            <div style='padding: 25px;'>
                <p style='font-size: 16px; color: #333;'>Hi there,</p>
                <p style='font-size: 15px; color: #555; line-height: 1.6;'>
                    We received a request to reset your password. Click the button below to set a new one.
                </p>
                <div style='text-align: center; margin: 25px 0;'>
                    <a href='$link' style='background-color: #2563eb; color: white; padding: 12px 20px; border-radius: 6px; text-decoration: none; font-weight: bold; display: inline-block;'>Reset Password</a>
                </div>
                <p style='font-size: 14px; color: #555;'>
                    If the button doesn't work, copy and paste this link into your browser:
                </p>
                <p style='word-break: break-all; color: #2563eb; font-size: 13px;'><a href='$link'>$link</a></p>
                <hr style='border: none; border-top: 1px solid #e5e7eb; margin: 25px 0;'>
                <p style='font-size: 12px; color: #888; text-align: center;'>
                    This link will expire in 1 hour.<br>
                    If you didn’t request this, please ignore this email.
                </p>
            </div>
            <div style='background-color: #f0f2f5; padding: 10px; text-align: center; font-size: 12px; color: #777;'>
                © " . date('Y') . " Glitch Social — All rights reserved.
            </div>
        </div>
    </div>
";
            $mail->send();
        } catch (Exception $e) {
            error_log('Mail Error: ' . $mail->ErrorInfo);
        }
    }
}