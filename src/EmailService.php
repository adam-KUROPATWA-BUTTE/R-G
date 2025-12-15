<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config_email.php';

/**
 * Envoie un email de confirmation de commande
 * 
 * @param array $order Données de la commande
 * @param array $items Articles de la commande
 * @return bool
 */
function send_order_confirmation_email(array $order, array $items): bool {
    $to = $order['email_client'];
    $subject = "Confirmation de commande #" . $order['id'] . " - " . COMPANY_NAME;
    
    // Générer le HTML de l'email
    $html = generate_order_email_html($order, $items);
    
    // Headers
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . EMAIL_FROM_NAME . ' <' . EMAIL_FROM_ADDRESS . '>',
        'Reply-To: ' . COMPANY_EMAIL,
        'X-Mailer: PHP/' . phpversion()
    ];
    
    // Envoi
    $sent = mail($to, $subject, $html, implode("\r\n", $headers));
    
    if ($sent) {
        error_log("✅ Email de confirmation envoyé à : $to");
    } else {
        error_log("❌ Erreur lors de l'envoi de l'email à : $to");
    }
    
    return $sent;
}

/**
 * Envoie une notification à l'admin pour nouvelle commande
 * 
 * @param array $order Données de la commande
 * @return bool
 */
function send_admin_order_notification(array $order): bool {
    $to = EMAIL_ADMIN;
    $subject = "🔔 Nouvelle commande #" . $order['id'] . " reçue";
    
    $html = generate_admin_notification_html($order);
    
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . EMAIL_FROM_NAME . ' <' . EMAIL_FROM_ADDRESS . '>',
        'Reply-To: ' . $order['email_client'],
        'X-Mailer: PHP/' . phpversion()
    ];
    
    $sent = mail($to, $subject, $html, implode("\r\n", $headers));
    
    if ($sent) {
        error_log("✅ Notification admin envoyée pour commande #" . $order['id']);
    } else {
        error_log("❌ Erreur notification admin pour commande #" . $order['id']);
    }
    
    return $sent;
}

/**
 * Génère le HTML de l'email de confirmation client
 */
function generate_order_email_html(array $order, array $items): string {
    $orderNumber = (int)$order['id'];
    $orderDate = date('d/m/Y à H:i', strtotime($order['date_creation']));
    $total = number_format((float)$order['total'], 2, ',', ' ');
    
    $itemsHtml = '';
    foreach ($items as $item) {
        $itemTotal = number_format((float)$item['prix_unitaire'] * (int)$item['quantite'], 2, ',', ' ');
        $itemsHtml .= "
        <tr>
            <td style='padding: 12px; border-bottom: 1px solid #e5e7eb;'>
                <strong>" . htmlspecialchars($item['nom_produit']) . "</strong><br>
                " . (!empty($item['taille']) ? "Taille: " . htmlspecialchars($item['taille']) : "") . "
            </td>
            <td style='padding: 12px; border-bottom: 1px solid #e5e7eb; text-align: center;'>
                " . (int)$item['quantite'] . "
            </td>
            <td style='padding: 12px; border-bottom: 1px solid #e5e7eb; text-align: right;'>
                " . number_format((float)$item['prix_unitaire'], 2, ',', ' ') . " €
            </td>
            <td style='padding: 12px; border-bottom: 1px solid #e5e7eb; text-align: right;'>
                <strong>" . $itemTotal . " €</strong>
            </td>
        </tr>";
    }
    
    return "
<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Confirmation de commande</title>
</head>
<body style='margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f3f4f6;'>
    <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #f3f4f6; padding: 20px 0;'>
        <tr>
            <td align='center'>
                <table width='600' cellpadding='0' cellspacing='0' style='background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>
                    <!-- Header -->
                    <tr>
                        <td style='background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); padding: 40px 30px; text-align: center;'>
                            <h1 style='color: #ffffff; margin: 0; font-size: 28px;'>" . COMPANY_NAME . "</h1>
                            <p style='color: #e0e7ff; margin: 10px 0 0 0;'>Merci pour votre commande !</p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style='padding: 40px 30px;'>
                            <div style='background: #dbeafe; border-left: 4px solid #3b82f6; padding: 15px; margin-bottom: 30px;'>
                                <p style='margin: 0; color: #1e3a8a;'>
                                    <strong>Commande confirmée !</strong><br>
                                    Votre paiement a été reçu avec succès.
                                </p>
                            </div>
                            
                            <h2 style='color: #1f2937; margin: 0 0 20px 0; font-size: 20px;'>
                                📦 Détails de votre commande
                            </h2>
                            
                            <table width='100%' cellpadding='0' cellspacing='0' style='margin-bottom: 20px;'>
                                <tr>
                                    <td style='padding: 8px 0;'><strong>Numéro de commande :</strong></td>
                                    <td style='padding: 8px 0; text-align: right;'>#$orderNumber</td>
                                </tr>
                                <tr>
                                    <td style='padding: 8px 0;'><strong>Date :</strong></td>
                                    <td style='padding: 8px 0; text-align: right;'>$orderDate</td>
                                </tr>
                                <tr>
                                    <td style='padding: 8px 0;'><strong>Client :</strong></td>
                                    <td style='padding: 8px 0; text-align: right;'>" . htmlspecialchars($order['prenom_client'] . ' ' . $order['nom_client']) . "</td>
                                </tr>
                            </table>
                            
                            <h3 style='color: #1f2937; margin: 30px 0 15px 0; font-size: 18px;'>Articles commandés</h3>
                            
                            <table width='100%' cellpadding='0' cellspacing='0' style='border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;'>
                                <thead>
                                    <tr style='background-color: #f9fafb;'>
                                        <th style='padding: 12px; text-align: left; border-bottom: 2px solid #e5e7eb;'>Produit</th>
                                        <th style='padding: 12px; text-align: center; border-bottom: 2px solid #e5e7eb;'>Qté</th>
                                        <th style='padding: 12px; text-align: right; border-bottom: 2px solid #e5e7eb;'>Prix unitaire</th>
                                        <th style='padding: 12px; text-align: right; border-bottom: 2px solid #e5e7eb;'>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    $itemsHtml
                                </tbody>
                                <tfoot>
                                    <tr style='background-color: #f9fafb;'>
                                        <td colspan='3' style='padding: 15px; text-align: right; border-top: 2px solid #e5e7eb;'>
                                            <strong style='font-size: 18px;'>TOTAL</strong>
                                        </td>
                                        <td style='padding: 15px; text-align: right; border-top: 2px solid #e5e7eb;'>
                                            <strong style='font-size: 18px; color: #1e3a8a;'>$total €</strong>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                            
                            <h3 style='color: #1f2937; margin: 30px 0 15px 0; font-size: 18px;'>📍 Adresse de livraison</h3>
                            <div style='background: #f9fafb; padding: 15px; border-radius: 8px;'>
                                <p style='margin: 0; line-height: 1.6;'>
                                    <strong>" . htmlspecialchars($order['prenom_client'] . ' ' . $order['nom_client']) . "</strong><br>
                                    " . nl2br(htmlspecialchars($order['adresse_livraison'])) . "
                                </p>
                            </div>
                            
                            <div style='background: #fef3c7; border: 1px solid #fbbf24; border-radius: 8px; padding: 15px; margin-top: 30px;'>
                                <p style='margin: 0; color: #92400e;'>
                                    <strong>ℹ️ Prochaines étapes :</strong><br>
                                    Votre commande sera traitée dans les plus brefs délais. Vous recevrez un email de confirmation d'expédition avec un numéro de suivi.
                                </p>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style='background-color: #f9fafb; padding: 30px; text-align: center; border-top: 1px solid #e5e7eb;'>
                            <p style='margin: 0 0 10px 0; color: #6b7280; font-size: 14px;'>
                                Besoin d'aide ? Contactez-nous :
                            </p>
                            <p style='margin: 0 0 10px 0;'>
                                📧 <a href='mailto:" . COMPANY_EMAIL . "' style='color: #3b82f6; text-decoration: none;'>" . COMPANY_EMAIL . "</a><br>
                                📞 " . COMPANY_PHONE . "
                            </p>
                            <p style='margin: 20px 0 0 0; color: #9ca3af; font-size: 12px;'>
                                © " . date('Y') . " " . COMPANY_NAME . ". Tous droits réservés.<br>
                                <a href='" . SITE_URL . "' style='color: #3b82f6; text-decoration: none;'>" . SITE_URL . "</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>";
}

/**
 * Génère le HTML de notification pour l'admin
 */
function generate_admin_notification_html(array $order): string {
    $orderNumber = (int)$order['id'];
    $total = number_format((float)$order['total'], 2, ',', ' ');
    
    return "
<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <title>Nouvelle commande</title>
</head>
<body style='font-family: Arial, sans-serif; padding: 20px; background-color: #f3f4f6;'>
    <div style='max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px;'>
        <h1 style='color: #1e3a8a; margin-top: 0;'>🔔 Nouvelle commande reçue !</h1>
        
        <p><strong>Commande #$orderNumber</strong></p>
        
        <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
            <tr>
                <td style='padding: 8px; border-bottom: 1px solid #e5e7eb;'><strong>Client :</strong></td>
                <td style='padding: 8px; border-bottom: 1px solid #e5e7eb;'>" . htmlspecialchars($order['prenom_client'] . ' ' . $order['nom_client']) . "</td>
            </tr>
            <tr>
                <td style='padding: 8px; border-bottom: 1px solid #e5e7eb;'><strong>Email :</strong></td>
                <td style='padding: 8px; border-bottom: 1px solid #e5e7eb;'>" . htmlspecialchars($order['email_client']) . "</td>
            </tr>
            <tr>
                <td style='padding: 8px; border-bottom: 1px solid #e5e7eb;'><strong>Téléphone :</strong></td>
                <td style='padding: 8px; border-bottom: 1px solid #e5e7eb;'>" . htmlspecialchars($order['telephone']) . "</td>
            </tr>
            <tr>
                <td style='padding: 8px; border-bottom: 1px solid #e5e7eb;'><strong>Total :</strong></td>
                <td style='padding: 8px; border-bottom: 1px solid #e5e7eb;'><strong style='color: #1e3a8a; font-size: 18px;'>$total €</strong></td>
            </tr>
        </table>
        
        <a href='" . SITE_URL . "/admin/order_show.php?id=$orderNumber' 
           style='display: inline-block; background: #3b82f6; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin-top: 20px;'>
            Voir la commande
        </a>
    </div>
</body>
</html>";
}
?>