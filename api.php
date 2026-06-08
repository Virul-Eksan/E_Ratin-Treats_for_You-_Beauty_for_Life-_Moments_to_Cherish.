<?php
session_start();
require_once 'db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'get_products') {
    try {
        $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
        $products = $stmt->fetchAll();
        echo json_encode(['success' => true, 'products' => $products]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'create_order') {
    $data = json_decode(file_get_contents("php://input"), true);
    if ($data) {
        try {
            $stmt = $pdo->prepare("INSERT INTO orders (customer_id, full_name, email, mobile_number, home_address, home_town, postal_code, cart_details, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $customer_id = $_SESSION['customer_id'] ?? null;
            $stmt->execute([
                $customer_id,
                $data['full_name'],
                $data['email'],
                $data['mobile_number'],
                $data['home_address'],
                $data['home_town'],
                $data['postal_code'],
                json_encode($data['cart_details']),
                $data['total_amount']
            ]);
            
            // Decrease stock for purchased items
            if (isset($data['cart_details']) && is_array($data['cart_details'])) {
                $stock_stmt = $pdo->prepare("UPDATE products SET stock = GREATEST(stock - ?, 0) WHERE id = ?");
                foreach ($data['cart_details'] as $item) {
                    $stock_stmt->execute([$item['quantity'], $item['id']]);
                }
            }
            
            // Fetch template and SMTP config from DB
            $stmt_settings = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('email_subject', 'email_body', 'smtp_username', 'smtp_password')");
            $settings_rows = $stmt_settings->fetchAll(PDO::FETCH_KEY_PAIR);
            $db_subject = $settings_rows['email_subject'] ?? 'Order Confirmation - ඒ රtin (E RATIN)';
            $db_body = $settings_rows['email_body'] ?? "Hello {customer_name},\n\nWe have received your order on {order_date}.\nTotal: \${total_amount}\nSend Date: {send_date}";
            $smtp_user = $settings_rows['smtp_username'] ?? '';
            $smtp_pass = $settings_rows['smtp_password'] ?? '';

            // Only attempt to send if SMTP credentials exist
            if (!empty($smtp_user) && !empty($smtp_pass)) {
                // Send confirmation email via PHPMailer
                $mail = new PHPMailer(true);
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com'; 
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $smtp_user;
                    $mail->Password   = $smtp_pass;
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port       = 465;

                    // Recipients
                    $mail->setFrom($smtp_user, 'E RATIN');
                    $mail->addAddress($data['email'], $data['full_name']);

                    $order_date = date("F j, Y");
                    $send_date = date("F j, Y", strtotime("+3 days"));

                    // Replace variables
                    $search = ['{customer_name}', '{order_date}', '{total_amount}', '{send_date}'];
                    $replace = [$data['full_name'], $order_date, $data['total_amount'], $send_date];
                    
                    $final_subject = str_replace($search, $replace, $db_subject);
                    $final_body = str_replace($search, $replace, $db_body);
                    $final_body_html = nl2br($final_body);

                    // Professional Colorful HTML Template to wrap admin text
                    $html_template = "
                    <div style='background-color: #f8fafc; padding: 40px 10px; font-family: \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
                        <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;'>
                            <div style='background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); padding: 50px 20px; text-align: center; color: #ffffff;'>
                                <h1 style='margin: 0; font-size: 30px; font-weight: 800; letter-spacing: -0.5px;'>Order Confirmed!</h1>
                                <p style='margin: 12px 0 0; opacity: 0.9; font-size: 17px;'>We are processing your items now.</p>
                            </div>
                            <div style='padding: 40px; color: #1a202c; line-height: 1.8; font-size: 16px;'>
                                $final_body_html
                            </div>
                            <div style='background-color: #f1f5f9; padding: 30px; text-align: center; color: #64748b; font-size: 13px; border-top: 1px solid #e2e8f0;'>
                                <p style='margin: 0; font-weight: 600; color: #475569;'>E RATIN</p>
                                <p style='margin: 10px 0 0;'>You received this email because you made a purchase on our store.</p>
                                <p style='margin: 5px 0 0;'>&copy; " . date('Y') . " All rights reserved.</p>
                            </div>
                        </div>
                    </div>";

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = $final_subject;
                    $mail->Body    = $html_template;
                    $mail->AltBody = strip_tags($final_body);

                    $mail->send();
                } catch (Exception $e) {
                    // We don't want the frontend to fail just because email failed, so we log it silently.
                    error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
                }
            }

            echo json_encode(['success' => true, 'message' => 'Order placed successfully!']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No data received']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>
