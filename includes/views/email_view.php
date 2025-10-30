<?php
/** @var string $link — The password reset link */
?>
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
        <a href="<?php echo $link; ?>" style='background-color: #2563eb; color: white; padding: 12px 20px; border-radius: 6px; text-decoration: none; font-weight: bold; display: inline-block;'>Reset Password</a>
      </div>
      <p style='font-size: 14px; color: #555;'>
        If the button doesn't work, copy and paste this link into your browser:
      </p>
      <p style='word-break: break-all; color: #2563eb; font-size: 13px;'>
        <a href="<?php echo htmlspecialchars($link, ENT_QUOTES); ?>"><?php echo htmlspecialchars($link, ENT_QUOTES); ?></a>
      </p>
      <hr style='border: none; border-top: 1px solid #e5e7eb; margin: 25px 0;'>
      <p style='font-size: 12px; color: #888; text-align: center;'>
        This link will expire in 1 hour.<br>
        If you didn’t request this, please ignore this email.
      </p>
    </div>
    <div style='background-color: #f0f2f5; padding: 10px; text-align: center; font-size: 12px; color: #777;'>
      © <?php echo date('Y'); ?> Glitch Social — All rights reserved.
    </div>
  </div>
</div>