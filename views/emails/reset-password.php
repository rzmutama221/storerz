<?php
/**
 * Email Template: Password Reset
 * 
 * Variables:
 * - $username: string
 * - $reset_url: string
 * - $expires_in: string
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background-color:#171717; font-family:'Segoe UI',Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#171717; padding:40px 20px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width:520px; background-color:#2c2c2c; border-radius:12px; border:1px solid #3a3a3a; overflow:hidden;">
                    
                    <!-- Header -->
                    <tr>
                        <td style="padding:32px 32px 24px; text-align:center; border-bottom:1px solid #3a3a3a;">
                            <span style="font-size:24px; font-weight:bold; color:#01a35a;">RZDK</span>
                            <span style="font-size:24px; font-weight:bold; color:#ffffff;"> Store</span>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding:32px;">
                            <h2 style="margin:0 0 16px; font-size:20px; font-weight:600; color:#ffffff;">Reset Password</h2>
                            
                            <p style="margin:0 0 16px; font-size:14px; line-height:1.6; color:#a0a0a0;">
                                Halo <strong style="color:#ffffff;"><?= htmlspecialchars($username) ?></strong>,
                            </p>
                            
                            <p style="margin:0 0 24px; font-size:14px; line-height:1.6; color:#a0a0a0;">
                                Kami menerima permintaan reset password untuk akun Anda. Klik tombol di bawah ini untuk membuat password baru.
                            </p>

                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding:8px 0 24px;">
                                        <a href="<?= htmlspecialchars($reset_url) ?>" style="display:inline-block; padding:14px 32px; background-color:#01a35a; color:#ffffff; font-size:14px; font-weight:600; text-decoration:none; border-radius:8px;">
                                            Reset Password
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Alternative Link -->
                            <p style="margin:0 0 8px; font-size:12px; color:#a0a0a0;">Atau salin link berikut ke browser:</p>
                            <p style="margin:0 0 24px; font-size:12px; color:#01a35a; word-break:break-all;">
                                <?= htmlspecialchars($reset_url) ?>
                            </p>

                            <!-- Notice -->
                            <div style="padding:12px 16px; background-color:#171717; border-radius:8px; border:1px solid #3a3a3a;">
                                <p style="margin:0 0 8px; font-size:12px; color:#a0a0a0;">
                                    &#9432; Link ini berlaku selama <strong style="color:#ffffff;"><?= htmlspecialchars($expires_in) ?></strong>.
                                </p>
                                <p style="margin:0; font-size:12px; color:#a0a0a0;">
                                    &#9888; Jika Anda tidak meminta reset password, abaikan email ini. Akun Anda tetap aman.
                                </p>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding:24px 32px; border-top:1px solid #3a3a3a; text-align:center;">
                            <p style="margin:0 0 8px; font-size:12px; color:#6b7280;">
                                &copy; <?= date('Y') ?> RZDK Store &middot; rzdkstore.my.id
                            </p>
                            <p style="margin:0; font-size:11px; color:#4b5563;">
                                Email ini dikirim otomatis, mohon tidak membalas.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
