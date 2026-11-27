
<?php
use PHPMailer\PHPMailer\PHPMailer;

function sendResetPasswordEmail(
    string $email,
    string $name,
    string $resetLink
): void {

    $mail = new PHPMailer(true);

    // Konfigurasi SMTP
    $mail->isSMTP();
    $mail->Host = $_ENV['MAIL_HOST'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['MAIL_USERNAME'];
    $mail->Password = $_ENV['MAIL_PASSWORD'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = (int) $_ENV['MAIL_PORT'];
    $mail->CharSet = 'UTF-8';
    $mail->Timeout = 15;

    // Pengirim
    $mail->setFrom(
        $_ENV['MAIL_FROM_ADDRESS'],
        $_ENV['MAIL_FROM_NAME']
    );

    // Penerima
    $mail->addAddress($email, $name);

    // Sanitasi konten
    $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $safeLink = htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8');
    $expiry = (int) ($_ENV['RESET_TOKEN_EXPIRY'] ?? 30);

    // Isi email
    $mail->isHTML(true);
    $mail->Subject = 'Reset Password - Sudut Biru POS';

    $mail->Body = "
        <h2>Reset Password</h2>
        <p>Halo, {$safeName}.</p>

        <p>Kami menerima permintaan untuk mengatur
        ulang password akun Sudut Biru POS Anda.</p>

        <p>
            <a href='{$safeLink}'
               style='background:#2563eb;
                      color:white;
                      padding:12px 24px;
                      text-decoration:none;
                      border-radius:6px;'>
                Reset Password
            </a>
        </p>

        <p>Link ini berlaku selama {$expiry} menit
        dan hanya dapat digunakan satu kali.</p>

        <p>Jika Anda tidak mengajukan permintaan ini,
        silakan abaikan email tersebut.</p>

        <p>Terima kasih,<br>
        Sudut Biru POS</p>
    ";

    $mail->AltBody =
        "Halo, {$name}.\n\n" .
        "Reset password melalui link berikut:\n" .
        $resetLink . "\n\n" .
        "Link berlaku selama {$expiry} menit.";

    $mail->send();
}
