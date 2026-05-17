<?php
/**
 * Email Template: Order Completed
 * Sent when admin marks an order as COMPLETED.
 * 
 * Variables:
 * - $username: string
 * - $order_number: string
 * - $product_name: string
 * - $variant_name: string
 * - $dashboard_url: string
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
                            <h2 style="margin:0 0 16px; font-size:20px; font-weight:600; color:#ffffff;">Order Selesai! &#127881;</h2>
                            
                            <p style="margin:0 0 16px; font-size:14px; line-height:1.6; color:#a0a0a0;">
                                Halo <strong style="color:#ffffff;"><?= htmlspecialchars($username) ?></strong>,
                            </p>
                            
                            <p style="margin:0 0 24px; font-size:14px; line-height:1.6; color:#a0a0a0;">
                                Pesanan Anda telah selesai diproses. Detail produk sudah tersedia di dashboard Anda.
                            </p>

                            <!-- Order Info Box -->
                            <div style="padding:16px; background-color:#171717; border-radius:8px; border:1px solid #3a3a3a; margin-bottom:24px;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding:4px 0; font-size:12px; color:#a0a0a0;">No. Order</td>
                                        <td style="padding:4px 0; font-size:12px; color:#ffffff; text-align:right; font-weight:600;"><?= htmlspecialchars($order_number) ?></td>
                                    </tr>
                                    <tr>
                                        <td style="padding:4px 0; font-size:12px; color:#a0a0a0;">Produk</td>
                                        <td style="padding:4px 0; font-size:12px; color:#ffffff; text-align:right;"><?= htmlspecialchars($product_name) ?></td>
                                    </tr>
                                    <tr>
                                        <td style="padding:4px 0; font-size:12px; color:#a0a0a0;">Varian</td>
                                        <td style="padding:4px 0; font-size:12px; color:#ffffff; text-align:right;"><?= htmlspecialchars($variant_name) ?></td>
                                    </tr>
                                    <tr>
                                        <td style="padding:4px 0; font-size:12px; color:#a0a0a0;">Status</td>
                                        <td style="padding:4px 0; font-size:12px; color:#10b981; text-align:right; font-weight:600;">Selesai &#10003;</td>
                                    </tr>
                                </table>
                            </div>

                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding:8px 0 24px;">
                                        <a href="<?= htmlspecialchars($dashboard_url) ?>" style="display:inline-block; padding:14px 32px; background-color:#01a35a; color:#ffffff; font-size:14px; font-weight:600; text-decoration:none; border-radius:8px;">
                                            Lihat Detail di Dashboard
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Info -->
                            <div style="padding:12px 16px; background-color:#171717; border-radius:8px; border:1px solid #3a3a3a;">
                                <p style="margin:0; font-size:12px; color:#a0a0a0;">
                                    &#128274; Data akun/produk Anda tersimpan dengan aman di dashboard. Jika ada masalah, silakan ajukan klaim garansi melalui menu <strong style="color:#ffffff;">Garansi</strong> di dashboard.
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
