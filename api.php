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
                // Send confirmation email via PHPMailer (wrapped to prevent response blocking)
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

                    // Content
                    $mail->isHTML(false);
                    $mail->Subject = $final_subject;
                    $mail->Body    = $final_body;

                    $mail->send();
                } catch (\Exception $e) {
                    // We don't want the frontend to fail just because email failed, so we log it silently.
                    error_log("Order Email failed: " . $e->getMessage());
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

if ($action === 'get_admin_notifications') {
    try {
        // Ensure messages table exists in case api.php is hit before admin.php
        $pdo->exec("CREATE TABLE IF NOT EXISTS messages (id INT AUTO_INCREMENT PRIMARY KEY, customer_id INT NOT NULL, subject VARCHAR(255) NOT NULL, body TEXT, is_read TINYINT(1) DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // Count of new orders (pending, unseen, not deleted)
        $stmt_order_count = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='pending' AND is_viewed=0 AND is_deleted=0");
        $order_count = (int)$stmt_order_count->fetchColumn();

        // Count of unread messages
        $stmt_msg_count = $pdo->query("SELECT COUNT(*) FROM messages WHERE is_read = 0");
        $msg_count = (int)$stmt_msg_count->fetchColumn();

        // Fetch pending orders for sidebar
        $stmt_orders = $pdo->query("SELECT id, created_at FROM orders WHERE status='pending' AND is_viewed=0 AND is_deleted=0 ORDER BY id DESC");
        $orders = $stmt_orders->fetchAll();

        // Fetch recent unread messages
        $stmt_msgs = $pdo->query("SELECT subject, created_at FROM messages WHERE is_read = 0 ORDER BY created_at DESC LIMIT 5");
        $messages = $stmt_msgs->fetchAll();

        echo json_encode([
            'success' => true,
            'total_count' => $order_count + $msg_count,
            'orders' => $orders,
            'messages' => $messages
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false]);
    }
    exit;
}

if ($action === 'get_all_reviews') {
    try {
        $stmt = $pdo->query("SELECT id, category, customer_name, rating, review_text, created_at FROM reviews WHERE is_approved = 1 ORDER BY created_at DESC");
        $reviews = $stmt->fetchAll();
        echo json_encode(['success' => true, 'reviews' => $reviews]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'get_reviews') {
    $category = $_GET['category'] ?? '';
    if (empty($category)) {
        echo json_encode(['success' => false, 'message' => 'Category required']);
        exit;
    }
    try {
        $stmt = $pdo->prepare("SELECT id, customer_name, rating, review_text, created_at FROM reviews WHERE category = ? AND is_approved = 1 ORDER BY created_at DESC");
        $stmt->execute([$category]);
        $reviews = $stmt->fetchAll();
        echo json_encode(['success' => true, 'reviews' => $reviews]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'submit_review') {
    $data = json_decode(file_get_contents("php://input"), true);
    $category    = trim($data['category'] ?? '');
    $name        = trim($data['customer_name'] ?? '');
    $rating      = intval($data['rating'] ?? 5);
    $review_text = trim($data['review_text'] ?? '');

    if (empty($category) || empty($name) || empty($review_text) || $rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'All fields are required and rating must be 1-5.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO reviews (category, customer_name, rating, review_text) VALUES (?, ?, ?, ?)");
        $stmt->execute([$category, $name, $rating, $review_text]);
        echo json_encode(['success' => true, 'message' => 'Review submitted!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'get_stock_report') {
    try {
        // All products with current stock
        $stmt_p = $pdo->query("SELECT id, name, category, stock FROM products ORDER BY name ASC");
        $products_raw = $stmt_p->fetchAll();

        // Last 7 days date keys
        $date_keys   = [];
        $date_labels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date_keys[]   = date('Y-m-d', strtotime("-{$i} days"));
            $date_labels[] = date('M d',   strtotime("-{$i} days"));
        }
        $sales_by_day = array_fill_keys($date_keys, 0);

        // Orders from last 7 days for daily + top-product chart
        $stmt_7 = $pdo->query("SELECT cart_details, created_at FROM orders WHERE is_deleted = 0 AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $orders_7 = $stmt_7->fetchAll();

        $product_sales = []; // [id => ['name','category','sold']]
        foreach ($orders_7 as $order) {
            $date  = substr($order['created_at'], 0, 10);
            $items = json_decode($order['cart_details'], true);
            if (!is_array($items)) continue;
            foreach ($items as $item) {
                $qty = (int)($item['quantity'] ?? 0);
                if (isset($sales_by_day[$date])) $sales_by_day[$date] += $qty;
                $pid = $item['id'] ?? null;
                if ($pid) {
                    if (!isset($product_sales[$pid])) {
                        $product_sales[$pid] = ['name' => $item['name'] ?? 'Unknown', 'category' => $item['category'] ?? '', 'sold' => 0];
                    }
                    $product_sales[$pid]['sold'] += $qty;
                }
            }
        }

        // Category sales – last 30 days
        $stmt_30 = $pdo->query("SELECT cart_details FROM orders WHERE is_deleted = 0 AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $orders_30 = $stmt_30->fetchAll();
        $category_sales = ['Chocolates' => 0, 'Cosmetics' => 0, 'Nuts' => 0];
        foreach ($orders_30 as $order) {
            $items = json_decode($order['cart_details'], true);
            if (!is_array($items)) continue;
            foreach ($items as $item) {
                $cat = $item['category'] ?? '';
                if (isset($category_sales[$cat])) $category_sales[$cat] += (int)($item['quantity'] ?? 0);
            }
        }

        echo json_encode([
            'success'        => true,
            'products'       => $products_raw,
            'daily_labels'   => $date_labels,
            'daily_sales'    => array_values($sales_by_day),
            'product_sales'  => array_values($product_sales),
            'category_sales' => $category_sales,
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'get_sales_report') {
    try {
        $date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-6 days'));
        $date_to   = $_GET['date_to']   ?? date('Y-m-d');

        // Validate dates
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from)) $date_from = date('Y-m-d', strtotime('-6 days'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to))   $date_to   = date('Y-m-d');
        if ($date_from > $date_to) { $tmp = $date_from; $date_from = $date_to; $date_to = $tmp; }

        // Fetch all orders in range
        $stmt = $pdo->prepare("SELECT cart_details, created_at, total_amount FROM orders WHERE is_deleted=0 AND DATE(created_at) BETWEEN ? AND ? ORDER BY created_at ASC");
        $stmt->execute([$date_from, $date_to]);
        $orders = $stmt->fetchAll();

        // --- Daily breakdown ---
        $daily_map  = [];
        $daily_rev  = [];
        $dt = new DateTime($date_from);
        $dtEnd = new DateTime($date_to);
        while ($dt <= $dtEnd) {
            $key = $dt->format('Y-m-d');
            $daily_map[$key] = 0;
            $daily_rev[$key] = 0.0;
            $dt->modify('+1 day');
        }

        // --- Monthly breakdown ---
        $monthly_map = [];
        $monthly_rev = [];

        // --- Category breakdown ---
        $category_sales = ['Chocolates' => 0, 'Cosmetics' => 0, 'Nuts' => 0];
        $category_rev   = ['Chocolates' => 0.0, 'Cosmetics' => 0.0, 'Nuts' => 0.0];

        // Product-level sold counts
        $product_sales = [];

        // Fetch product prices
        $prices = [];
        $pstmt  = $pdo->query("SELECT id, price FROM products");
        foreach ($pstmt->fetchAll() as $pr) { $prices[$pr['id']] = (float)$pr['price']; }

        foreach ($orders as $order) {
            $day   = substr($order['created_at'], 0, 10);
            $month = substr($order['created_at'], 0, 7);
            $items = json_decode($order['cart_details'], true);
            if (!is_array($items)) continue;
            foreach ($items as $item) {
                $qty  = (int)($item['quantity'] ?? 0);
                $pid  = $item['id'] ?? null;
                $cat  = $item['category'] ?? '';
                $name = $item['name'] ?? 'Unknown';
                $price = $pid && isset($prices[$pid]) ? $prices[$pid] : (float)($item['price'] ?? 0);
                $rev  = $qty * $price;

                if (isset($daily_map[$day])) {
                    $daily_map[$day] += $qty;
                    $daily_rev[$day] += $rev;
                }
                $monthly_map[$month] = ($monthly_map[$month] ?? 0) + $qty;
                $monthly_rev[$month] = ($monthly_rev[$month] ?? 0.0) + $rev;

                if (isset($category_sales[$cat])) {
                    $category_sales[$cat] += $qty;
                    $category_rev[$cat]   += $rev;
                }

                if ($pid) {
                    if (!isset($product_sales[$pid])) $product_sales[$pid] = ['name' => $name, 'sold' => 0, 'revenue' => 0.0];
                    $product_sales[$pid]['sold']    += $qty;
                    $product_sales[$pid]['revenue'] += $rev;
                }
            }
        }

        // Sort monthly labels
        ksort($monthly_map);

        $daily_labels   = [];
        $daily_values   = [];
        $daily_revenues = [];
        foreach ($daily_map as $d => $qty) {
            $daily_labels[]   = date('M d', strtotime($d));
            $daily_values[]   = $qty;
            $daily_revenues[] = round($daily_rev[$d], 2);
        }

        $monthly_labels   = [];
        $monthly_values   = [];
        $monthly_revenues = [];
        foreach ($monthly_map as $m => $qty) {
            $monthly_labels[]   = date('M Y', strtotime($m . '-01'));
            $monthly_values[]   = $qty;
            $monthly_revenues[] = round($monthly_rev[$m], 2);
        }

        echo json_encode([
            'success'          => true,
            'date_from'        => $date_from,
            'date_to'          => $date_to,
            'daily_labels'     => $daily_labels,
            'daily_values'     => $daily_values,
            'daily_revenues'   => $daily_revenues,
            'monthly_labels'   => $monthly_labels,
            'monthly_values'   => $monthly_values,
            'monthly_revenues' => $monthly_revenues,
            'category_sales'   => $category_sales,
            'category_rev'     => $category_rev,
            'product_sales'    => array_values($product_sales),
            'total_units'      => array_sum($daily_values),
            'total_revenue'    => round(array_sum($daily_revenues), 2),
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>
