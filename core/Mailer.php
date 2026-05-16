<?php
/**
 * Mailer - Email Sender for RZDK Store
 * 
 * Sends emails via SMTP using PHP's native socket connection.
 * Compatible with cPanel shared hosting (no Composer/PHPMailer needed).
 * 
 * Supports:
 * - SMTP with SSL/TLS
 * - HTML emails
 * - Template-based emails
 * 
 * Usage:
 *   $mailer = new Mailer();
 *   $mailer->to('user@email.com', 'User Name')
 *          ->subject('Verifikasi Email')
 *          ->body($htmlContent)
 *          ->send();
 */

class Mailer
{
    private array $config;
    private string $toEmail = '';
    private string $toName = '';
    private string $subject = '';
    private string $body = '';
    private string $altBody = '';
    private array $errors = [];

    public function __construct()
    {
        $this->config = require BASE_PATH . '/config/mail.php';
    }

    /**
     * Set recipient
     */
    public function to(string $email, string $name = ''): static
    {
        $this->toEmail = $email;
        $this->toName = $name;
        return $this;
    }

    /**
     * Set subject
     */
    public function subject(string $subject): static
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Set HTML body
     */
    public function body(string $html): static
    {
        $this->body = $html;
        $this->altBody = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $html));
        return $this;
    }

    /**
     * Send email using PHP mail() with proper headers
     * Falls back to mail() if SMTP fails
     */
    public function send(): bool
    {
        if (empty($this->toEmail) || empty($this->subject) || empty($this->body)) {
            $this->errors[] = 'Email, subject, dan body wajib diisi.';
            return false;
        }

        // Try SMTP first, fallback to mail()
        if ($this->config['use_smtp']) {
            return $this->sendSmtp();
        }

        return $this->sendMail();
    }

    /**
     * Send using PHP mail() function
     */
    private function sendMail(): bool
    {
        $fromEmail = $this->config['from_email'];
        $fromName = $this->config['from_name'];

        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=UTF-8';
        $headers[] = "From: {$fromName} <{$fromEmail}>";
        $headers[] = "Reply-To: {$fromEmail}";
        $headers[] = 'X-Mailer: RZDK-Store/1.0';

        $to = $this->toName ? "{$this->toName} <{$this->toEmail}>" : $this->toEmail;

        $result = @mail($to, $this->subject, $this->body, implode("\r\n", $headers));

        if (!$result) {
            $this->errors[] = 'Gagal mengirim email via mail().';
        }

        return $result;
    }

    /**
     * Send using SMTP socket connection
     */
    private function sendSmtp(): bool
    {
        $host = $this->config['smtp_host'];
        $port = (int) $this->config['smtp_port'];
        $username = $this->config['smtp_username'];
        $password = $this->config['smtp_password'];
        $encryption = $this->config['smtp_encryption']; // ssl or tls
        $fromEmail = $this->config['from_email'];
        $fromName = $this->config['from_name'];

        try {
            // Connect
            $contextPrefix = ($encryption === 'ssl') ? 'ssl://' : '';
            $socket = @fsockopen($contextPrefix . $host, $port, $errno, $errstr, 30);

            if (!$socket) {
                $this->errors[] = "SMTP connection failed: {$errstr} ({$errno})";
                return $this->sendMail(); // Fallback
            }

            // Read greeting
            $this->smtpRead($socket);

            // EHLO
            $this->smtpCommand($socket, "EHLO " . gethostname());

            // STARTTLS if needed
            if ($encryption === 'tls') {
                $this->smtpCommand($socket, "STARTTLS");
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                $this->smtpCommand($socket, "EHLO " . gethostname());
            }

            // AUTH LOGIN
            $this->smtpCommand($socket, "AUTH LOGIN");
            $this->smtpCommand($socket, base64_encode($username));
            $this->smtpCommand($socket, base64_encode($password));

            // MAIL FROM
            $this->smtpCommand($socket, "MAIL FROM: <{$fromEmail}>");

            // RCPT TO
            $this->smtpCommand($socket, "RCPT TO: <{$this->toEmail}>");

            // DATA
            $this->smtpCommand($socket, "DATA");

            // Build message
            $message = "From: {$fromName} <{$fromEmail}>\r\n";
            $message .= "To: {$this->toEmail}\r\n";
            $message .= "Subject: {$this->subject}\r\n";
            $message .= "MIME-Version: 1.0\r\n";
            $message .= "Content-Type: text/html; charset=UTF-8\r\n";
            $message .= "X-Mailer: RZDK-Store/1.0\r\n";
            $message .= "\r\n";
            $message .= $this->body;
            $message .= "\r\n.\r\n";

            fwrite($socket, $message);
            $this->smtpRead($socket);

            // QUIT
            $this->smtpCommand($socket, "QUIT");
            fclose($socket);

            return true;

        } catch (\Exception $e) {
            $this->errors[] = 'SMTP Error: ' . $e->getMessage();
            return $this->sendMail(); // Fallback to mail()
        }
    }

    /**
     * Send SMTP command and read response
     */
    private function smtpCommand($socket, string $command): string
    {
        fwrite($socket, $command . "\r\n");
        return $this->smtpRead($socket);
    }

    /**
     * Read SMTP response
     */
    private function smtpRead($socket): string
    {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') break;
        }
        return $response;
    }

    /**
     * Get errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Render an email template
     */
    public static function renderTemplate(string $template, array $data = []): string
    {
        $templatePath = BASE_PATH . '/views/emails/' . $template . '.php';

        if (!file_exists($templatePath)) {
            return '';
        }

        extract($data);
        ob_start();
        require $templatePath;
        return ob_get_clean();
    }

    /**
     * Quick send helper (static)
     */
    public static function quickSend(string $toEmail, string $subject, string $template, array $data = []): bool
    {
        $html = self::renderTemplate($template, $data);
        if (empty($html)) {
            return false;
        }

        $mailer = new self();
        return $mailer->to($toEmail)->subject($subject)->body($html)->send();
    }
}
