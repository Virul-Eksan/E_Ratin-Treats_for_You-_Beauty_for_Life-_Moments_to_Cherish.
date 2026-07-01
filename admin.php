<?php
require_once 'db.php';

$message = '';
$order_message = '';
$active_tab = 'view-stock';
$active_sub_tab = 'pending';

// Handle success messages
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'email') {
        $message = "Email template updated successfully.";
        $active_tab = 'view-email';
    } elseif ($_GET['success'] === 'stock') {
        $message = "Stock updated successfully.";
        $active_tab = 'view-stock';
    } elseif ($_GET['success'] === 'perm_delete') {
        $order_message = "Order permanently deleted.";
        $active_tab = 'view-orders';
    } elseif ($_GET['success'] === 'price') {
        $message = "Price updated successfully.";
        $active_tab = 'view-stock';
    } elseif ($_GET['success'] === 'cost') {
        $message = "Cost updated successfully.";
        $active_tab = 'view-stock';
    } elseif ($_GET['success'] === 'blacklist') {
        $message = "Customer blacklisted successfully.";
        $active_tab = 'view-customers';
        $active_sub_tab = 'blacklisted';
    } elseif ($_GET['success'] === 'unblacklist') {
        $message = "Customer removed from blacklist.";
        $active_tab = 'view-customers';
        $active_sub_tab = 'all';
    } elseif ($_GET['success'] === 'review_approved') {
        $message = "Review approved successfully.";
        $active_tab = 'view-reviews';
    } elseif ($_GET['success'] === 'review_deapproved') {
        $message = "Review deapproved successfully.";
        $active_tab = 'view-reviews';
    } elseif ($_GET['success'] === 'review_deleted') {
        $message = "Review deleted successfully.";
        $active_tab = 'view-reviews';
    } else {
        $message = "Product added successfully.";
        $active_tab = 'view-stock';
    }
}

// Handle Update Email Template
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_email'])) {
    $subject = $_POST['email_subject'];
    $body = $_POST['email_body'];
    $smtp_user = $_POST['smtp_username'];
    $smtp_pass = $_POST['smtp_password'];

    $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
    $stmt->execute([$subject, 'email_subject']);
    $stmt->execute([$body, 'email_body']);
    $stmt->execute([$smtp_user, 'smtp_username']);
    
    // Only update password if a new one is provided
    if (!empty($smtp_pass)) {
        $stmt->execute([$smtp_pass, 'smtp_password']);
    }

    header("Location: admin.php?success=email");
    exit();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // fetch image path to delete file
    $stmt = $pdo->prepare("SELECT image_path FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    
    if ($product && file_exists($product['image_path'])) {
        unlink($product['image_path']);
    }
    
    $del = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $del->execute([$id]);
    $message = "Product deleted successfully.";
    $active_tab = 'view-stock';
}

// Handle Delete Order
if (isset($_GET['delete_order'])) {
    $order_id = $_GET['delete_order'];
    $del_order = $pdo->prepare("UPDATE orders SET is_deleted = 1 WHERE id = ?");
    if ($del_order->execute([$order_id])) {
        $order_message = "Order deleted successfully.";
        $undo_order_id = $order_id;
        $active_tab = 'view-orders';
    }
}

// Handle Undo Order
if (isset($_GET['undo_order'])) {
    $order_id = $_GET['undo_order'];
    $undo_stmt = $pdo->prepare("UPDATE orders SET is_deleted = 0 WHERE id = ?");
    if ($undo_stmt->execute([$order_id])) {
        $order_message = "Order restored successfully.";
        $active_tab = 'view-orders';
    }
}

// Handle Permanent Delete Order
if (isset($_GET['perm_delete_order'])) {
    $order_id = (int)$_GET['perm_delete_order'];
    $perm_stmt = $pdo->prepare("DELETE FROM orders WHERE id = ? AND is_deleted = 1");
    if ($perm_stmt->execute([$order_id])) {
        header("Location: admin.php?success=perm_delete");
        exit();
    }
}

// Handle Mark Ongoing
if (isset($_GET['mark_ongoing'])) {
    $order_id = $_GET['mark_ongoing'];
    $stmt = $pdo->prepare("UPDATE orders SET status = 'ongoing' WHERE id = ?");
    if ($stmt->execute([$order_id])) {
        $order_message = "Order marked as ongoing.";
        $active_tab = 'view-orders';
        $active_sub_tab = 'ongoing';
    }
}

// Handle Mark Closed
if (isset($_GET['mark_closed'])) {
    $order_id = $_GET['mark_closed'];
    $stmt = $pdo->prepare("UPDATE orders SET status = 'closed' WHERE id = ?");
    if ($stmt->execute([$order_id])) {
        $order_message = "Order marked as closed.";
        $active_tab = 'view-orders';
        $active_sub_tab = 'closed';
    }
}

// Handle Mark Pending
if (isset($_GET['mark_pending'])) {
    $order_id = $_GET['mark_pending'];
    $stmt = $pdo->prepare("UPDATE orders SET status = 'pending' WHERE id = ?");
    if ($stmt->execute([$order_id])) {
        $order_message = "Order marked as pending.";
        $active_tab = 'view-orders';
        $active_sub_tab = 'pending';
    }
}

// Handle Update Stock
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $id = $_POST['update_stock_id'];
    $stock = $_POST['new_stock'];
    $stmt = $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?");
    if ($stmt->execute([$stock, $id])) {
        // Redirect to avoid form resubmission on refresh
        header("Location: admin.php?success=stock&id={$id}");
        exit();
    }
}

// Handle Update Price
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_price'])) {
    $id = $_POST['update_price_id'];
    $price = $_POST['new_price'];
    $stmt = $pdo->prepare("UPDATE products SET price = ? WHERE id = ?");
    if ($stmt->execute([$price, $id])) {
        // Redirect to avoid form resubmission on refresh
        header("Location: admin.php?success=price&id={$id}");
        exit();
    }
}

// Handle Update Cost
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cost'])) {
    $id = $_POST['update_cost_id'];
    $cost = $_POST['new_cost'];
    $stmt = $pdo->prepare("UPDATE products SET cost = ? WHERE id = ?");
    if ($stmt->execute([$cost, $id])) {
        // Redirect to avoid form resubmission on refresh
        header("Location: admin.php?success=cost&id={$id}");
        exit();
    }
}

// Handle Blacklist Customer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_blacklist'])) {
    $cust_id = $_POST['blacklist_customer_id'];
    $reason = $_POST['blacklist_reason'];
    $stmt = $pdo->prepare("UPDATE customers SET is_blacklisted = 1, blacklist_reason = ? WHERE id = ?");
    if ($stmt->execute([$reason, $cust_id])) {
        header("Location: admin.php?success=blacklist");
        exit();
    }
}

// Handle Un-blacklist Customer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_unblacklist'])) {
    $cust_id = $_POST['unblacklist_customer_id'];
    $stmt = $pdo->prepare("UPDATE customers SET is_blacklisted = 0, blacklist_reason = NULL WHERE id = ?");
    if ($stmt->execute([$cust_id])) {
        header("Location: admin.php?success=unblacklist");
        exit();
    }
}

// Handle Approve Review
if (isset($_GET['approve_review'])) {
    $id = $_GET['approve_review'];
    $stmt = $pdo->prepare("UPDATE reviews SET is_approved = 1 WHERE id = ?");
    if ($stmt->execute([$id])) {
        header("Location: admin.php?success=review_approved");
        exit();
    }
}

// Handle Deapprove Review
if (isset($_GET['deapprove_review'])) {
    $id = $_GET['deapprove_review'];
    $stmt = $pdo->prepare("UPDATE reviews SET is_approved = 0 WHERE id = ?");
    if ($stmt->execute([$id])) {
        header("Location: admin.php?success=review_deapproved");
        exit();
    }
}

// Handle Delete Review
if (isset($_GET['delete_review'])) {
    $id = $_GET['delete_review'];
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
    if ($stmt->execute([$id])) {
        header("Location: admin.php?success=review_deleted");
        exit();
    }
}

// Handle Add Product

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $cost = $_POST['cost'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $description = $_POST['description'];
    
    // Image Upload
    $imagePath = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $targetFilePath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
            $imagePath = $targetFilePath;
        } else {
            $message = "Error uploading image.";
        }
    }
    
    if ($imagePath) {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, category, cost, price, stock, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $description, $category, $cost, $price, $stock, $imagePath])) {
            header("Location: admin.php?success=1");
            exit();
        } else {
            $message = "Error adding product to database.";
        }
    } else {
        $message = "Image is required.";
    }
}

// Fetch all products
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();

// Fetch pending orders
$stmt_pending = $pdo->prepare("SELECT * FROM orders WHERE is_deleted = 0 AND status = 'pending' AND is_viewed = 0 ORDER BY id DESC");
$stmt_pending->execute();
$pending_orders = $stmt_pending->fetchAll();


// Fetch ongoing orders
$stmt_ongoing = $pdo->query("SELECT * FROM orders WHERE is_deleted = 0 AND status = 'ongoing' ORDER BY id DESC");
$ongoing_orders = $stmt_ongoing->fetchAll();

// Fetch closed orders
$stmt_closed = $pdo->query("SELECT * FROM orders WHERE is_deleted = 0 AND status = 'closed' ORDER BY id DESC");
$closed_orders = $stmt_closed->fetchAll();

// Fetch deleted orders
$stmt_deleted_orders = $pdo->query("SELECT * FROM orders WHERE is_deleted = 1 ORDER BY id DESC");
$deleted_orders = $stmt_deleted_orders->fetchAll();

// Fetch all customers and their orders
$stmt_customers = $pdo->query("SELECT * FROM customers ORDER BY id DESC");
$customers_list = $stmt_customers->fetchAll();

$active_customers = [];
$blacklisted_customers = [];
foreach ($customers_list as $cust) {
    if (isset($cust['is_blacklisted']) && $cust['is_blacklisted'] == 1) {
        $blacklisted_customers[] = $cust;
    } else {
        $active_customers[] = $cust;
    }
}

// Group orders by customer ID for the customer view
$stmt_orders_all = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC");
$all_orders = $stmt_orders_all->fetchAll();

// Fetch all reviews for moderation
$stmt_reviews_all = $pdo->query("SELECT * FROM reviews ORDER BY created_at DESC");
$all_reviews = $stmt_reviews_all->fetchAll();

$orders_by_customer = [];
foreach ($all_orders as $o) {
    if ($o['customer_id']) {
        $orders_by_customer[$o['customer_id']][] = $o;
    }
}

    // Ensure messages table exists (create if missing)
    $createMessagesTableSql = "CREATE TABLE IF NOT EXISTS messages (\n        id INT AUTO_INCREMENT PRIMARY KEY,\n        customer_id INT NOT NULL,\n        subject VARCHAR(255) NOT NULL,\n        body TEXT,\n        is_read TINYINT(1) DEFAULT 0,\n        created_at DATETIME DEFAULT CURRENT_TIMESTAMP\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($createMessagesTableSql);

    // New notifications: count of unread customer messages
// New notifications: count of unread customer messages
$stmt_new_messages = $pdo->query("SELECT COUNT(*) FROM messages WHERE is_read = 0");
$new_messages_count = $stmt_new_messages->fetchColumn();
// Count of new orders (pending and unseen)
$stmt_new_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='pending' AND is_viewed=0");
$new_orders_count = $stmt_new_orders->fetchColumn();

// Fetch Email Settings
$stmt_settings = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('email_subject', 'email_body', 'smtp_username', 'smtp_password')");
$settings_rows = $stmt_settings->fetchAll(PDO::FETCH_KEY_PAIR);
$email_subject = $settings_rows['email_subject'] ?? '';
$email_body = $settings_rows['email_body'] ?? '';
$smtp_username = $settings_rows['smtp_username'] ?? '';
$smtp_password = $settings_rows['smtp_password'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - ඒ රtin (E RATIN)</title>
    <link rel="stylesheet" href="admin.css?v=5">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .nav-scroll-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            flex: 1;
            margin: 0 20px;
            overflow: hidden;
        }
        .admin-nav {
            display: flex !important;
            gap: 20px;
            overflow-x: auto;
            white-space: nowrap;
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE/Edge */
            scroll-behavior: smooth;
            padding: 5px 0;
            width: 100%;
        }
        .admin-nav::-webkit-scrollbar {
            display: none; /* Chrome/Safari */
        }
        .nav-link {
            flex-shrink: 0;
        }
        .nav-arrow {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            transition: all 0.3s ease;
            z-index: 5;
            user-select: none;
        }
        .nav-arrow:hover { background: var(--primary-pink); }
        .nav-arrow.hidden { visibility: hidden; opacity: 0; }
    </style>
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h2><img src="logo.jpg" alt="Logo" class="admin-brand-logo"   style="height:40px; width:40px; border-radius:50%; object-fit:cover;"> Store Admin Panel</h2>
            <div class="nav-scroll-wrapper">
                <button class="nav-arrow left hidden" id="nav-prev">&#9664;</button>
                <nav class="admin-nav" id="admin-navbar">
                    <a href="#" class="nav-link <?php echo $active_tab == 'view-stock' ? 'active' : ''; ?>" data-target="view-stock">Stock</a>
                    <a href="#" class="nav-link <?php echo $active_tab == 'view-gallery' ? 'active' : ''; ?>" data-target="view-gallery">Gallery</a>
                    <a href="#" class="nav-link <?php echo $active_tab == 'view-orders' ? 'active' : ''; ?>" data-target="view-orders">Orders</a>
                    <a href="#" class="nav-link <?php echo $active_tab == 'view-customers' ? 'active' : ''; ?>" data-target="view-customers">Customers</a>
                    <a href="#" class="nav-link <?php echo $active_tab == 'view-email' ? 'active' : ''; ?>" data-target="view-email">Email Template</a>
                    <a href="#" class="nav-link" data-target="view-reviews">Customer Reviews</a>
                    <a href="#" class="nav-link" data-target="view-reports">📊 Reports</a>
                </nav>
                <button class="nav-arrow right" id="nav-next">&#9654;</button>
            </div>
        <a href="index.php" class="btn-view" target="_blank">View Live Site</a>
        <div class="notification-wrapper" style="position: relative; display: inline-block; margin-left: 20px;">
            <span id="notificationIcon" class="notification-icon" style="cursor: pointer; font-size: 1.5rem;" title="Notifications">🔔</span>
            <span id="notifCount" class="notif-count" style="position: absolute; top: -5px; right: -10px; background: #ef4444; color: #fff; border-radius: 50%; padding: 2px 6px; font-size: 0.8rem; pointer-events: none; <?php echo ($new_orders_count + $new_messages_count) > 0 ? '' : 'display: none;'; ?>"><?php echo $new_orders_count + $new_messages_count; ?></span>
        </div>
    </header>

        <?php if ($message): ?>
            <div class="alert" id="success-alert"><?php echo htmlspecialchars($message); ?></div>
            <script>
                setTimeout(() => {
                    const alertEl = document.getElementById('success-alert');
                    if (alertEl) {
                        alertEl.style.transition = 'opacity 0.5s ease';
                        alertEl.style.opacity = '0';
                        setTimeout(() => alertEl.remove(), 500);
                    }
                }, 5000);
            </script>
        <?php endif; ?>

        <!-- Stock View -->
        <div id="view-stock" class="admin-view <?php echo $active_tab == 'view-stock' ? '' : 'hidden'; ?>">
            <div class="admin-grid">
                <!-- Add Product Form -->
            <div class="card add-product-card">
                <h3>Add New Product</h3>
                <form action="admin.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Product Name</label>
                        <input type="text" name="name" required placeholder="E.g., Dark Chocolate">
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" required>
                            <option value="Chocolates">Chocolates</option>
                            <option value="Cosmetics">Cosmetics</option>
                            <option value="Nuts">Nuts</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Cost ($)</label>
                        <input type="number" step="0.01" name="cost" required placeholder="5.99">
                    </div>
                    <div class="form-group">
                        <label>Selling Price ($)</label>
                        <input type="number" step="0.01" name="price" required placeholder="9.99">
                    </div>
                    <div class="form-group">
                        <label>Stock Count</label>
                        <input type="number" name="stock" required min="0" placeholder="e.g. 50">
                    </div>
                    <div class="form-group">
                        <label>Product Image</label>
                        <input type="file" name="image" accept="image/*" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="4" required placeholder="Describe the product..."></textarea>
                    </div>
                    <button type="submit" name="add_product" class="btn-submit">Add Product</button>
                </form>
            </div>

            <!-- Manage Products -->
            <div class="card manage-products-card">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--glass-border); margin-bottom: 25px; padding-bottom: 15px; flex-wrap: wrap; gap: 15px;">
                    <h3 style="border-bottom: none; margin-bottom: 0; padding-bottom: 0;">Manage Products</h3>
                    <input type="text" id="productSearchInput" placeholder="🔍 Search name, category..." style="padding: 8px 15px; border-radius: 8px; border: 1px solid var(--glass-border); background: rgba(0,0,0,0.2); color: white; outline: none; min-width: 250px; font-family: inherit;">
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Cost</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($products as $p): ?>
                            <tr>
                                <td><img src="<?php echo htmlspecialchars($p['image_path']); ?>" alt="product" width="50"></td>
                                <td><?php echo htmlspecialchars($p['name']); ?></td>
                                <td><span class="badge <?php echo strtolower($p['category']); ?>"><?php echo htmlspecialchars($p['category']); ?></span></td>
                                <td>
                                    <form action="admin.php" method="POST" style="display: flex; gap: 5px; align-items: center; justify-content: center;">
                                        <input type="hidden" name="update_cost_id" value="<?php echo $p['id']; ?>">
                                        <span style="color: white; margin-right: 2px;">$</span>
                                        <input type="number" name="new_cost" value="<?php echo htmlspecialchars($p['cost'] ?? 0.00); ?>" step="0.01" min="0" style="width: 70px; padding: 4px; border-radius: 4px; border: 1px solid var(--glass-border); background: rgba(0,0,0,0.2); color: white;">
                                        <button type="submit" name="update_cost" class="btn-submit" style="padding: 4px 10px; font-size: 0.8rem; border-radius: 4px;">Save</button>
                                    </form>
                                </td>
                                <td>
                                    <form action="admin.php" method="POST" style="display: flex; gap: 5px; align-items: center; justify-content: center;">
                                        <input type="hidden" name="update_price_id" value="<?php echo $p['id']; ?>">
                                        <span style="color: white; margin-right: 2px;">$</span>
                                        <input type="number" name="new_price" value="<?php echo htmlspecialchars($p['price'] ?? 0.00); ?>" step="0.01" min="0" style="width: 70px; padding: 4px; border-radius: 4px; border: 1px solid var(--glass-border); background: rgba(0,0,0,0.2); color: white;">
                                        <button type="submit" name="update_price" class="btn-submit" style="padding: 4px 10px; font-size: 0.8rem; border-radius: 4px;">Save</button>
                                    </form>
                                </td>
                                <td>
                                    <form action="admin.php" method="POST" style="display: flex; gap: 5px; align-items: center; justify-content: center;">
                                        <input type="hidden" name="update_stock_id" value="<?php echo $p['id']; ?>">
                                        <input type="number" name="new_stock"
                                            data-product-id="<?php echo $p['id']; ?>"
                                            value="<?php echo htmlspecialchars($p['stock'] ?? 0); ?>"
                                            min="0"
                                            style="width: 60px; padding: 4px; border-radius: 4px; border: 1px solid var(--glass-border); background: rgba(0,0,0,0.2); color: white; transition: background 0.4s;">
                                        <button type="submit" name="update_stock" class="btn-submit" style="padding: 4px 10px; font-size: 0.8rem; border-radius: 4px;">Save</button>
                                        <span class="live-stock-dot"
                                            data-product-id="<?php echo $p['id']; ?>"
                                            title="Live: <?php echo (int)($p['stock'] ?? 0); ?> in stock"
                                            style="font-size: 0.85rem; cursor: default; transition: color 0.4s; color: <?php echo ((int)($p['stock'] ?? 0)) <= 0 ? '#ef4444' : '#34d399'; ?>;">&#9679;</span>
                                    </form>
                                </td>
                                <td>
                                    <a href="admin.php?delete=<?php echo $p['id']; ?>" class="btn-delete" onclick="confirmDeletion(event, this.href, 'product');">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($products)): ?>
                                <tr><td colspan="6">No products found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            </div>
        </div>

        <!-- Orders View -->
        <div id="view-orders" class="admin-view <?php echo $active_tab == 'view-orders' ? '' : 'hidden'; ?>">
            <?php if ($order_message): ?>
                <div class="alert" id="order-alert" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <span><?php echo htmlspecialchars($order_message); ?></span>
                    <?php if (isset($undo_order_id)): ?>
                        <a href="admin.php?undo_order=<?php echo $undo_order_id; ?>" style="padding: 4px 14px; font-size: 0.85rem; text-decoration: none; margin-left: auto; background: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.4); border-radius: 6px; font-weight: 600; cursor: pointer;">Undo</a>
                    <?php endif; ?>
                </div>
                <script>
                    setTimeout(() => {
                        const alertEl = document.getElementById('order-alert');
                        if (alertEl) {
                            alertEl.style.transition = 'opacity 0.5s ease';
                            alertEl.style.opacity = '0';
                            setTimeout(() => alertEl.remove(), 500);
                        }
                    }, 5000);
                </script>
            <?php endif; ?>
            <div class="card manage-orders-card">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--glass-border); margin-bottom: 25px; padding-bottom: 15px; flex-wrap: wrap; gap: 15px;">
                <h3 id="orders-section-title" style="border-bottom: none; margin-bottom: 0; padding-bottom: 0;">Customer Orders</h3>
                <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                    <input type="text" id="orderSearchInput" placeholder="🔍 Search name, email, ID..." style="padding: 8px 15px; border-radius: 8px; border: 1px solid var(--glass-border); background: rgba(0,0,0,0.2); color: white; outline: none; min-width: 250px; font-family: inherit;">
                    <button id="toggleBinBtn" class="btn-view" style="padding: 6px 15px; border-radius: 8px; font-size: 0.9rem;">
                        🗑️ Bin (<?php echo count($deleted_orders); ?>)
                    </button>
                </div>
            </div>

            <!-- Sub Tabs for Orders -->
            <div class="order-sub-tabs" style="display: flex; gap: 10px; margin-bottom: 20px;">
                <button class="btn-view order-tab-btn active" data-target="pending-orders-table" style="padding: 6px 15px; border-radius: 20px; font-size: 0.85rem; border: none; background: rgba(59, 130, 246, 0.2); color: #60a5fa;">Pending (<?php echo count($pending_orders); ?>)</button>
                <button class="btn-view order-tab-btn" data-target="ongoing-orders-table" style="padding: 6px 15px; border-radius: 20px; font-size: 0.85rem; border: none;">Ongoing (<?php echo count($ongoing_orders); ?>)</button>
                <button class="btn-view order-tab-btn" data-target="closed-orders-table" style="padding: 6px 15px; border-radius: 20px; font-size: 0.85rem; border: none;">Closed (<?php echo count($closed_orders); ?>)</button>
            </div>

            <!-- Active Orders Container -->
            <div id="active-orders-container">
            
            <div id="pending-orders-table" class="table-responsive order-table-view">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Contact</th>
                            <th>Address</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($pending_orders as $o): 
                            $items = json_decode($o['cart_details'], true);
                            $itemsList = '';
                            if($items) {
                                foreach($items as $item) {
                                    $itemsList .= htmlspecialchars($item['name']) . ' (x' . $item['quantity'] . ')<br>';
                                }
                            }
                        ?>
                        <tr data-order-id="<?php echo $o['id']; ?>">
                            <td>#<?php echo htmlspecialchars($o['id']); ?> <br> <span class="badge pending" style="margin-top: 5px; display: inline-block;">Pending</span></td>
                            <td><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($o['created_at']))); ?></td>
                            <td><?php echo htmlspecialchars($o['full_name']); ?></td>
                            <td>
                                <a href="mailto:<?php echo htmlspecialchars($o['email']); ?>" style="color:var(--text-color)"><?php echo htmlspecialchars($o['email']); ?></a><br>
                                <?php echo htmlspecialchars($o['mobile_number']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($o['home_address']); ?><br>
                                <?php echo htmlspecialchars($o['home_town']); ?> - <?php echo htmlspecialchars($o['postal_code']); ?>
                            </td>
                            <td style="font-size: 0.9em;"><?php echo $itemsList; ?></td>
                            <td style="font-weight: bold; color: var(--primary-pink);">$<?php echo htmlspecialchars($o['total_amount']); ?></td>
                            <td style="display: flex; gap: 5px; flex-direction: column;">
                                <a href="admin.php?mark_ongoing=<?php echo $o['id']; ?>" class="btn-ongoing">Ongoing</a>
                                <a href="admin.php?delete_order=<?php echo $o['id']; ?>" class="btn-delete" onclick="confirmDeletion(event, this.href, 'order');">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($pending_orders)): ?>
                            <tr><td colspan="8">No pending orders found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Ongoing Orders Table -->
            <div id="ongoing-orders-table" class="table-responsive order-table-view hidden">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Contact</th>
                            <th>Address</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($ongoing_orders as $o): 
                            $items = json_decode($o['cart_details'], true);
                            $itemsList = '';
                            if($items) {
                                foreach($items as $item) {
                                    $itemsList .= htmlspecialchars($item['name']) . ' (x' . $item['quantity'] . ')<br>';
                                }
                            }
                        ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($o['id']); ?> <br> <span class="badge ongoing" style="margin-top: 5px; display: inline-block;">Ongoing</span></td>
                            <td><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($o['created_at']))); ?></td>
                            <td><?php echo htmlspecialchars($o['full_name']); ?></td>
                            <td>
                                <a href="mailto:<?php echo htmlspecialchars($o['email']); ?>" style="color:var(--text-color)"><?php echo htmlspecialchars($o['email']); ?></a><br>
                                <?php echo htmlspecialchars($o['mobile_number']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($o['home_address']); ?><br>
                                <?php echo htmlspecialchars($o['home_town']); ?> - <?php echo htmlspecialchars($o['postal_code']); ?>
                            </td>
                            <td style="font-size: 0.9em;"><?php echo $itemsList; ?></td>
                            <td style="font-weight: bold; color: var(--primary-pink);">$<?php echo htmlspecialchars($o['total_amount']); ?></td>
                            <td style="display: flex; gap: 5px; flex-direction: column;">
                                <a href="admin.php?mark_closed=<?php echo $o['id']; ?>" class="btn-closed">Closed</a>
                                <a href="admin.php?mark_pending=<?php echo $o['id']; ?>" class="btn-return" title="Return to Pending">R.T.P</a>
                                <a href="admin.php?delete_order=<?php echo $o['id']; ?>" class="btn-delete" onclick="confirmDeletion(event, this.href, 'order');">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($ongoing_orders)): ?>
                            <tr><td colspan="8">No ongoing orders found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Closed Orders Table -->
            <div id="closed-orders-table" class="table-responsive order-table-view hidden">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Contact</th>
                            <th>Address</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($closed_orders as $o): 
                            $items = json_decode($o['cart_details'], true);
                            $itemsList = '';
                            if($items) {
                                foreach($items as $item) {
                                    $itemsList .= htmlspecialchars($item['name']) . ' (x' . $item['quantity'] . ')<br>';
                                }
                            }
                        ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($o['id']); ?> <br> <span class="badge closed" style="margin-top: 5px; display: inline-block;">Closed</span></td>
                            <td><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($o['created_at']))); ?></td>
                            <td><?php echo htmlspecialchars($o['full_name']); ?></td>
                            <td>
                                <a href="mailto:<?php echo htmlspecialchars($o['email']); ?>" style="color:var(--text-color)"><?php echo htmlspecialchars($o['email']); ?></a><br>
                                <?php echo htmlspecialchars($o['mobile_number']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($o['home_address']); ?><br>
                                <?php echo htmlspecialchars($o['home_town']); ?> - <?php echo htmlspecialchars($o['postal_code']); ?>
                            </td>
                            <td style="font-size: 0.9em;"><?php echo $itemsList; ?></td>
                            <td style="font-weight: bold; color: var(--primary-pink);">$<?php echo htmlspecialchars($o['total_amount']); ?></td>
                            <td style="display: flex; gap: 5px; flex-direction: column;">
                                <a href="admin.php?mark_ongoing=<?php echo $o['id']; ?>" class="btn-return" title="Return to Ongoing">R.T.O</a>
                                <a href="admin.php?mark_pending=<?php echo $o['id']; ?>" class="btn-return" title="Return to Pending">R.T.P</a>
                                <a href="admin.php?delete_order=<?php echo $o['id']; ?>" class="btn-delete" onclick="confirmDeletion(event, this.href, 'order');">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($closed_orders)): ?>
                            <tr><td colspan="8">No closed orders found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            </div> <!-- End Active Orders Container -->

            <!-- Deleted Orders Table -->
            <div id="deleted-orders-table" class="table-responsive hidden">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Contact</th>
                            <th>Address</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($deleted_orders as $o): 
                            $items = json_decode($o['cart_details'], true);
                            $itemsList = '';
                            if(is_array($items)) {
                                foreach($items as $item) {
                                    $itemName = htmlspecialchars($item['name'] ?? 'Unknown Item');
                                    $itemQty  = (int)($item['quantity'] ?? 1);
                                    $itemsList .= $itemName . ' (x' . $itemQty . ')<br>';
                                }
                            }
                        ?>
                        <tr style="opacity: 0.7;">
                            <td>#<?php echo htmlspecialchars($o['id']); ?></td>
                            <td><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($o['created_at']))); ?></td>
                            <td><?php echo htmlspecialchars($o['full_name']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($o['email']); ?><br>
                                <?php echo htmlspecialchars($o['mobile_number']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($o['home_address']); ?><br>
                                <?php echo htmlspecialchars($o['home_town']); ?> - <?php echo htmlspecialchars($o['postal_code']); ?>
                            </td>
                            <td style="font-size: 0.9em;"><?php echo $itemsList; ?></td>
                            <td style="font-weight: bold; color: var(--primary-pink);">$<?php echo htmlspecialchars($o['total_amount']); ?></td>
                            <td style="display: flex; gap: 5px; flex-direction: column;">
                                <a href="admin.php?undo_order=<?php echo $o['id']; ?>" style="padding: 6px 14px; font-size: 0.85rem; text-decoration: none; background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); border-radius: 8px; font-weight: 600; text-align:center;">Restore</a>
                                <a href="admin.php?perm_delete_order=<?php echo $o['id']; ?>" class="btn-delete" onclick="confirmDeletion(event, this.href, 'order');" style="text-align:center;">Delete Permanently</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($deleted_orders)): ?>
                            <tr><td colspan="8">No deleted orders in the bin.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        </div>

        <!-- Gallery View -->
        <div id="view-gallery" class="admin-view <?php echo $active_tab == 'view-gallery' ? '' : 'hidden'; ?>">
            <div class="card">
                <h3>Product Gallery</h3>
                <div class="gallery-grid">
                    <?php foreach($products as $p): ?>
                        <div class="gallery-item">
                            <img src="<?php echo htmlspecialchars($p['image_path']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
                            <div class="gallery-info"><?php echo htmlspecialchars($p['name']); ?></div>
                        </div>
                    <?php endforeach; ?>
                    <?php if(empty($products)): ?>
                        <p>No images found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Reports View -->
        <div id="view-reports" class="admin-view hidden">

            <!-- Report Sub-Tab Buttons -->
            <div style="display:flex; gap:12px; margin-bottom:24px; flex-wrap:wrap;">
                <button id="btn-sales-report" class="report-sub-tab-btn active-sub-tab" onclick="switchReportTab('sales')"
                    style="display:flex; align-items:center; gap:8px; padding:12px 28px; border-radius:30px; border:2px solid rgba(251,191,36,0.5); background:rgba(251,191,36,0.12); color:#fbbf24; font-size:1rem; font-weight:700; cursor:pointer; transition:all .25s;">
                    📈 Sales Report
                </button>
                <button id="btn-stock-report" class="report-sub-tab-btn" onclick="switchReportTab('stock')"
                    style="display:flex; align-items:center; gap:8px; padding:12px 28px; border-radius:30px; border:2px solid rgba(96,165,250,0.3); background:transparent; color:#94a3b8; font-size:1rem; font-weight:700; cursor:pointer; transition:all .25s;">
                    📦 Stock Report
                </button>
                <button id="btn-profit-report" class="report-sub-tab-btn" onclick="switchReportTab('profit')"
                    style="display:flex; align-items:center; gap:8px; padding:12px 28px; border-radius:30px; border:2px solid rgba(236,72,153,0.3); background:transparent; color:#94a3b8; font-size:1rem; font-weight:700; cursor:pointer; transition:all .25s;">
                    💰 Profit Report
                </button>
            </div>

            <!-- ===== SALES REPORT SUB-TAB ===== -->
            <div id="sub-sales-report">
                <!-- Header -->
                <div class="card" style="margin-bottom:20px; padding:20px 25px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                    <div>
                        <h3 style="margin:0 0 4px; font-size:1.4rem;">📈 Sales Report</h3>
                        <p style="margin:0; color:#94a3b8; font-size:0.88rem;">Customizable date-range sales analytics</p>
                    </div>
                    <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                        <a href="sales_report.php" target="_blank"
                           style="display:inline-flex; align-items:center; gap:7px; background:linear-gradient(135deg,#d4a017,#f59e0b); color:#0f172a; font-weight:700; font-size:0.88rem; padding:9px 20px; border-radius:25px; text-decoration:none; box-shadow:0 4px 15px rgba(212,160,23,0.35); transition:all .2s;"
                           onmouseover="this.style.transform='scale(1.04)'" onmouseout="this.style.transform='scale(1)'">
                            📄 Download Sales Report
                        </a>
                    </div>
                </div>

                <!-- Date Range Picker -->
                <div class="card" style="padding:18px 24px; margin-bottom:22px; display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
                    <span style="color:#94a3b8; font-size:0.9rem; font-weight:600;">📅 Date Range:</span>
                    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                        <input type="date" id="sales-date-from" style="padding:8px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.15); background:rgba(0,0,0,0.3); color:white; font-family:inherit; font-size:0.9rem; outline:none;">
                        <span style="color:#94a3b8;">to</span>
                        <input type="date" id="sales-date-to" style="padding:8px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.15); background:rgba(0,0,0,0.3); color:white; font-family:inherit; font-size:0.9rem; outline:none;">
                        <button onclick="loadSalesReport()" style="padding:8px 20px; border-radius:8px; border:none; background:linear-gradient(135deg,#8b5cf6,#6d28d9); color:white; font-weight:700; cursor:pointer; font-size:0.9rem; transition:all .2s;" onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'">Apply</button>
                        <button onclick="setSalesRange(7)" style="padding:8px 14px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.06); color:#cbd5e1; font-size:0.82rem; cursor:pointer;">Last 7d</button>
                        <button onclick="setSalesRange(30)" style="padding:8px 14px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.06); color:#cbd5e1; font-size:0.82rem; cursor:pointer;">Last 30d</button>
                        <button onclick="setSalesRange(90)" style="padding:8px 14px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.06); color:#cbd5e1; font-size:0.82rem; cursor:pointer;">Last 90d</button>
                    </div>
                    <span id="sales-last-updated" style="margin-left:auto; font-size:0.8rem; color:#94a3b8;"></span>
                </div>

                <!-- Summary Stat Cards -->
                <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:14px; margin-bottom:22px;">
                    <div class="card" style="padding:20px; text-align:center; background:linear-gradient(135deg,rgba(251,191,36,0.12),rgba(245,158,11,0.04)); border:1px solid rgba(251,191,36,0.3);">
                        <div id="sr-stat-units" style="font-size:2rem; font-weight:700; color:#fbbf24;">—</div>
                        <div style="color:#94a3b8; font-size:0.8rem; margin-top:4px;">Total Units Sold</div>
                    </div>
                    <div class="card" style="padding:20px; text-align:center; background:linear-gradient(135deg,rgba(52,211,153,0.12),rgba(16,185,129,0.04)); border:1px solid rgba(52,211,153,0.3);">
                        <div id="sr-stat-revenue" style="font-size:2rem; font-weight:700; color:#34d399;">—</div>
                        <div style="color:#94a3b8; font-size:0.8rem; margin-top:4px;">Total Revenue</div>
                    </div>
                    <div class="card" style="padding:20px; text-align:center; background:linear-gradient(135deg,rgba(236,72,153,0.12),rgba(219,39,119,0.04)); border:1px solid rgba(236,72,153,0.3);">
                        <div id="sr-stat-profit" style="font-size:2rem; font-weight:700; color:#ec4899;">—</div>
                        <div style="color:#94a3b8; font-size:0.8rem; margin-top:4px;">Total Profit</div>
                    </div>
                    <div class="card" style="padding:20px; text-align:center; background:linear-gradient(135deg,rgba(139,92,246,0.12),rgba(109,40,217,0.04)); border:1px solid rgba(139,92,246,0.3);">
                        <div id="sr-stat-days" style="font-size:2rem; font-weight:700; color:#a78bfa;">—</div>
                        <div style="color:#94a3b8; font-size:0.8rem; margin-top:4px;">Days in Range</div>
                    </div>
                    <div class="card" style="padding:20px; text-align:center; background:linear-gradient(135deg,rgba(96,165,250,0.12),rgba(59,130,246,0.04)); border:1px solid rgba(96,165,250,0.3);">
                        <div id="sr-stat-avg" style="font-size:2rem; font-weight:700; color:#60a5fa;">—</div>
                        <div style="color:#94a3b8; font-size:0.8rem; margin-top:4px;">Avg Units/Day</div>
                    </div>
                </div>

                <!-- Daily Sales Bar + Pie -->
                <div class="card" style="padding:18px 20px; margin-bottom:10px;">
                    <h4 style="margin:0 0 4px; color:var(--primary-pink); font-size:1rem;">📈 Daily Sales</h4>
                    <p style="margin:0 0 16px; color:#64748b; font-size:0.8rem;">Units sold per day in selected range</p>
                </div>
                <div style="display:grid; grid-template-columns:2fr 1fr; gap:20px; margin-bottom:22px;">
                    <div class="card" style="padding:24px;">
                        <div style="position:relative; height:280px;"><canvas id="sr-chart-daily"></canvas></div>
                    </div>
                    <div class="card" style="padding:24px;">
                        <h4 style="margin:0 0 12px; color:#94a3b8; font-size:0.85rem; text-align:center;">Category Share — Daily</h4>
                        <div style="position:relative; height:240px;"><canvas id="sr-chart-daily-pie"></canvas></div>
                    </div>
                </div>

                <!-- Monthly Sales Bar + Pie -->
                <div class="card" style="padding:18px 20px; margin-bottom:10px;">
                    <h4 style="margin:0 0 4px; color:var(--primary-pink); font-size:1rem;">📅 Monthly Sales</h4>
                    <p style="margin:0 0 16px; color:#64748b; font-size:0.8rem;">Units sold per month in selected range</p>
                </div>
                <div style="display:grid; grid-template-columns:2fr 1fr; gap:20px; margin-bottom:22px;">
                    <div class="card" style="padding:24px;">
                        <div style="position:relative; height:280px;"><canvas id="sr-chart-monthly"></canvas></div>
                    </div>
                    <div class="card" style="padding:24px;">
                        <h4 style="margin:0 0 12px; color:#94a3b8; font-size:0.85rem; text-align:center;">Category Share — Monthly</h4>
                        <div style="position:relative; height:240px;"><canvas id="sr-chart-monthly-pie"></canvas></div>
                    </div>
                </div>
            </div>

            <!-- ===== STOCK REPORT SUB-TAB ===== -->
            <div id="sub-stock-report" class="hidden">
                <!-- Header -->
                <div class="card" style="margin-bottom:20px; padding:20px 25px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                    <div>
                        <h3 style="margin:0 0 4px; font-size:1.4rem;">📦 Stock Report</h3>
                        <p style="margin:0; color:#94a3b8; font-size:0.88rem;">Live analytics — auto-refreshes every 30 seconds</p>
                    </div>
                    <span id="report-last-updated" style="font-size:0.8rem; color:#94a3b8; background:rgba(255,255,255,0.05); padding:5px 12px; border-radius:20px; border:1px solid rgba(255,255,255,0.08);">Not loaded yet</span>
                </div>

                <!-- Summary Stat Cards -->
                <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:16px; margin-bottom:22px;">
                    <div class="card" style="padding:22px; text-align:center; background:linear-gradient(135deg,rgba(52,211,153,0.1),rgba(16,185,129,0.04)); border:1px solid rgba(52,211,153,0.25);">
                        <div id="stat-total-products" style="font-size:2.2rem; font-weight:700; color:#34d399;">—</div>
                        <div style="color:#94a3b8; font-size:0.82rem; margin-top:5px;">Total Products</div>
                    </div>
                    <div class="card" style="padding:22px; text-align:center; background:linear-gradient(135deg,rgba(96,165,250,0.1),rgba(59,130,246,0.04)); border:1px solid rgba(96,165,250,0.25);">
                        <div id="stat-total-stock" style="font-size:2.2rem; font-weight:700; color:#60a5fa;">—</div>
                        <div style="color:#94a3b8; font-size:0.82rem; margin-top:5px;">Units in Stock</div>
                    </div>
                    <div class="card" style="padding:22px; text-align:center; background:linear-gradient(135deg,rgba(251,191,36,0.1),rgba(245,158,11,0.04)); border:1px solid rgba(251,191,36,0.25);">
                        <div id="stat-sold-7d" style="font-size:2.2rem; font-weight:700; color:#fbbf24;">—</div>
                        <div style="color:#94a3b8; font-size:0.82rem; margin-top:5px;">Units Sold (7 Days)</div>
                    </div>
                    <div class="card" style="padding:22px; text-align:center; background:linear-gradient(135deg,rgba(239,68,68,0.1),rgba(220,38,38,0.04)); border:1px solid rgba(239,68,68,0.25);">
                        <div id="stat-sold-out" style="font-size:2.2rem; font-weight:700; color:#f87171;">—</div>
                        <div style="color:#94a3b8; font-size:0.82rem; margin-top:5px;">Sold Out Products</div>
                    </div>
                </div>

                <!-- Charts Row 1: Daily Sales Bar + 7-day Pie -->
                <div class="card" style="padding:18px 20px; margin-bottom:10px;">
                    <h4 style="margin:0 0 4px; color:var(--primary-pink); font-size:1rem;">📈 Daily Sales — Last 7 Days</h4>
                </div>
                <div style="display:grid; grid-template-columns:2fr 1fr; gap:20px; margin-bottom:20px;">
                    <div class="card" style="padding:24px;">
                        <div style="position:relative; height:270px;"><canvas id="chart-daily-sales"></canvas></div>
                    </div>
                    <div class="card" style="padding:24px;">
                        <h4 style="margin:0 0 12px; color:#94a3b8; font-size:0.85rem; text-align:center;">Category Share — 7 Days %</h4>
                        <div style="position:relative; height:240px;"><canvas id="chart-category-sales"></canvas></div>
                    </div>
                </div>

                <!-- Charts Row 2: Monthly Sales Bar + Monthly Pie -->
                <div class="card" style="padding:18px 20px; margin-bottom:10px;">
                    <h4 style="margin:0 0 4px; color:var(--primary-pink); font-size:1rem;">📅 Monthly Sales</h4>
                </div>
                <div style="display:grid; grid-template-columns:2fr 1fr; gap:20px; margin-bottom:20px;">
                    <div class="card" style="padding:24px;">
                        <div style="position:relative; height:270px;"><canvas id="chart-monthly-sales"></canvas></div>
                    </div>
                    <div class="card" style="padding:24px;">
                        <h4 style="margin:0 0 12px; color:#94a3b8; font-size:0.85rem; text-align:center;">Category Share — Monthly %</h4>
                        <div style="position:relative; height:240px;"><canvas id="chart-monthly-category"></canvas></div>
                    </div>
                </div>

                <!-- Charts Row 3: Remaining Stock + Top Products -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
                    <div class="card" style="padding:24px;">
                        <h4 style="margin:0 0 18px; color:var(--primary-pink);">📦 Remaining Stock per Product</h4>
                        <div style="position:relative; height:300px;"><canvas id="chart-stock-levels"></canvas></div>
                    </div>
                    <div class="card" style="padding:24px;">
                        <h4 style="margin:0 0 18px; color:var(--primary-pink);">🏆 Top Products Sold — 7 Days</h4>
                        <div style="position:relative; height:300px;"><canvas id="chart-top-products"></canvas></div>
                    </div>
                </div>

                <!-- Full Stock Status Table -->
                <div class="card" style="padding:24px;">
                    <h4 style="margin:0 0 18px; color:var(--primary-pink);">🗂️ Full Stock Status</h4>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Stock Remaining</th>
                                    <th>Sold (7 Days)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="stock-report-tbody">
                                <tr><td colspan="5" style="text-align:center; color:#94a3b8; padding:30px;">Click "Reports" to load data…</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ===== PROFIT REPORT SUB-TAB ===== -->
            <div id="sub-profit-report" class="hidden">
                <!-- Header -->
                <div class="card" style="margin-bottom:20px; padding:20px 25px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                    <div>
                        <h3 style="margin:0 0 4px; font-size:1.4rem;">💰 Profit Report</h3>
                        <p style="margin:0; color:#94a3b8; font-size:0.88rem;">Category and item-wise profit analysis</p>
                    </div>
                </div>

                <!-- Date Range Picker -->
                <div class="card" style="padding:18px 24px; margin-bottom:22px; display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
                    <span style="color:#94a3b8; font-size:0.9rem; font-weight:600;">📅 Date Range:</span>
                    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                        <input type="date" id="profit-date-from" style="padding:8px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.15); background:rgba(0,0,0,0.3); color:white; font-family:inherit; font-size:0.9rem; outline:none;">
                        <span style="color:#94a3b8;">to</span>
                        <input type="date" id="profit-date-to" style="padding:8px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.15); background:rgba(0,0,0,0.3); color:white; font-family:inherit; font-size:0.9rem; outline:none;">
                        <button onclick="loadProfitReport()" style="padding:8px 20px; border-radius:8px; border:none; background:linear-gradient(135deg,#ec4899,#db2777); color:white; font-weight:700; cursor:pointer; font-size:0.9rem; transition:all .2s;">Apply</button>
                        <button onclick="setProfitRange(7)" style="padding:8px 14px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.06); color:#cbd5e1; font-size:0.82rem; cursor:pointer;">7 Days</button>
                        <button onclick="setProfitRange(30)" style="padding:8px 14px; border-radius:8px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.06); color:#cbd5e1; font-size:0.82rem; cursor:pointer;">30 Days (Month)</button>
                    </div>
                </div>

                <!-- Category Profit Table -->
                <div class="card" style="padding:24px; margin-bottom:20px;">
                    <h4 style="margin:0 0 18px; color:#ec4899;">📦 Category-wise Profit</h4>
                    <div class="table-responsive">
                        <table>
                            <thead><tr><th>Category</th><th>Units Sold</th><th>Revenue</th><th>Profit</th></tr></thead>
                            <tbody id="profit-cat-tbody"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Item Profit Table -->
                <div class="card" style="padding:24px;">
                    <h4 style="margin:0 0 18px; color:#ec4899;">🛍️ Item-wise Profit</h4>
                    <div class="table-responsive">
                        <table>
                            <thead><tr><th>Item Name</th><th>Category</th><th>Cost ($)</th><th>Price ($)</th><th>Units Sold</th><th>Revenue</th><th>Profit</th></tr></thead>
                            <tbody id="profit-item-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Template View -->
        <div id="view-email" class="admin-view <?php echo $active_tab == 'view-email' ? '' : 'hidden'; ?>">
            <div class="card">
                <h3>Order Confirmation Email Template</h3>
                <p style="margin-bottom: 20px; font-size: 0.9em; color: var(--text-color); opacity: 0.8;">
                    Available variables: <code>{customer_name}</code>, <code>{order_date}</code>, <code>{total_amount}</code>, <code>{send_date}</code>
                </p>
                <form action="admin.php" method="POST">
                    <h4 style="margin-top: 30px; margin-bottom: 15px; color: var(--primary-pink); border-bottom: 1px solid var(--glass-border); padding-bottom: 10px;">SMTP Configuration</h4>
                    <div class="form-group">
                        <label>Sending Gmail Address (e.g. you@gmail.com)</label>
                        <input type="email" name="smtp_username" value="<?php echo htmlspecialchars($smtp_username); ?>" placeholder="Enter Gmail Address">
                    </div>
                    <div class="form-group">
                        <label>Google App Password (16-letters)</label>
                        <input type="password" name="smtp_password" placeholder="<?php echo !empty($smtp_password) ? '******** (Password is set)' : 'Enter App Password'; ?>">
                        <small style="color: #94a3b8; display: block; margin-top: 5px;">Leave blank to keep existing password.</small>
                    </div>

                    <h4 style="margin-top: 30px; margin-bottom: 15px; color: var(--primary-pink); border-bottom: 1px solid var(--glass-border); padding-bottom: 10px;">Email Content</h4>
                    <div class="form-group">
                        <label>Email Subject</label>
                        <input type="text" name="email_subject" value="<?php echo htmlspecialchars($email_subject); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email Body</label>
                        <textarea name="email_body" rows="12" required style="width: 100%; padding: 12px; background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: var(--text-color); resize: vertical;"><?php echo htmlspecialchars($email_body); ?></textarea>
                    </div>
                    <button type="submit" name="update_email" class="btn-submit">Save Template</button>
                </form>
            </div>
        </div>

        <!-- Customer Reviews View -->
        <div id="view-reviews" class="admin-view <?php echo $active_tab == 'view-reviews' ? '' : 'hidden'; ?>">
            <div class="card manage-products-card">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--glass-border); margin-bottom: 25px; padding-bottom: 15px;">
                    <h3 style="border-bottom: none; margin-bottom: 0;">Review Moderation</h3>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Category</th>
                                <th>Rating</th>
                                <th>Review</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($all_reviews as $rev): ?>
                            <tr>
                                <td style="font-weight: 600;"><?php echo htmlspecialchars($rev['customer_name']); ?></td>
                                <td><span class="badge <?php echo strtolower($rev['category']); ?>"><?php echo htmlspecialchars($rev['category']); ?></span></td>
                                <td style="color: var(--primary-gold); font-size: 1.1rem;"><?php echo str_repeat('★', $rev['rating']); ?></td>
                                <td style="max-width: 300px; font-style: italic; font-size: 0.9rem;"><?php echo htmlspecialchars($rev['review_text']); ?></td>
                                <td>
                                    <span class="badge <?php echo $rev['is_approved'] ? 'closed' : 'pending'; ?>">
                                        <?php echo $rev['is_approved'] ? 'Approved' : 'Pending'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!$rev['is_approved']): ?>
                                        <a href="admin.php?approve_review=<?php echo $rev['id']; ?>" class="btn-ongoing" style="margin-bottom: 5px; display: inline-block; padding: 5px 10px; font-size: 0.75rem; text-transform: capitalize;">Approve</a>
                                    <?php else: ?>
                                        <a href="admin.php?deapprove_review=<?php echo $rev['id']; ?>" class="btn-closed" style="margin-bottom: 5px; display: inline-block; padding: 5px 10px; font-size: 0.75rem; text-transform: capitalize;">Deapprove</a>
                                    <?php endif; ?>
                                    <a href="admin.php?delete_review=<?php echo $rev['id']; ?>" class="btn-delete" style="padding: 5px 10px; display: inline-block; font-size: 0.75rem;" onclick="return confirm('Permanently delete this review?')">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($all_reviews)): ?>
                                <tr><td colspan="6">No reviews submitted yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Customers View -->
        <div id="view-customers" class="admin-view <?php echo $active_tab == 'view-customers' ? '' : 'hidden'; ?>">
            <div class="card manage-customers-card">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--glass-border); margin-bottom: 25px; padding-bottom: 15px; flex-wrap: wrap; gap: 15px;">
                    <h3 style="border-bottom: none; margin-bottom: 0; padding-bottom: 0;">Registered Customers</h3>
                    <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                        <input type="text" id="customerSearchInput" placeholder="🔍 Search name, email..." style="padding: 8px 15px; border-radius: 8px; border: 1px solid var(--glass-border); background: rgba(0,0,0,0.2); color: white; outline: none; min-width: 250px; font-family: inherit;">
                    </div>
                </div>

                <!-- Sub Tabs for Customers -->
                <div class="customer-sub-tabs" style="display: flex; gap: 10px; margin-bottom: 20px;">
                    <button class="btn-view customer-tab-btn active" data-target="active-customers-table" style="padding: 6px 15px; border-radius: 20px; font-size: 0.85rem; border: none; background: rgba(59, 130, 246, 0.2); color: #60a5fa;">All Customers (<?php echo count($active_customers); ?>)</button>
                    <button class="btn-view customer-tab-btn" data-target="blacklisted-customers-table" style="padding: 6px 15px; border-radius: 20px; font-size: 0.85rem; border: none;">Blacklisted Base (<?php echo count($blacklisted_customers); ?>)</button>
                </div>

                <!-- Active Customers Table -->
                <div id="active-customers-table" class="table-responsive customer-table-view">
                    <table id="customers-table">
                        <thead>
                            <tr>
                                <th>Avatar</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Joined Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($active_customers as $cust): 
                                $avatar_url = !empty($cust['profile_image']) ? 'CustomerData/' . htmlspecialchars($cust['profile_image']) : 'https://ui-avatars.com/api/?name=' . urlencode($cust['name']) . '&background=c48a5a&color=fff&rounded=true';
                            ?>
                            <tr>
                                <td><img src="<?php echo $avatar_url; ?>" alt="Profile" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;"></td>
                                <td style="font-weight: 600;"><?php echo htmlspecialchars($cust['name']); ?></td>
                                <td><a href="mailto:<?php echo htmlspecialchars($cust['email']); ?>" style="color:var(--text-color)"><?php echo htmlspecialchars($cust['email']); ?></a></td>
                                <td><?php echo htmlspecialchars(date('M d, Y', strtotime($cust['created_at']))); ?></td>
                                <td>
                                    <button class="btn-view btn-view-customer" 
                                            data-customer='<?php echo htmlspecialchars(json_encode([
                                                'id' => $cust['id'],
                                                'name' => $cust['name'],
                                                'email' => $cust['email'],
                                                'phone' => $cust['phone'] ?? 'N/A',
                                                'address' => $cust['address'] ?? 'N/A',
                                                'avatar' => $avatar_url,
                                                'joined' => date('M d, Y', strtotime($cust['created_at'])),
                                                'is_blacklisted' => 0,
                                                'blacklist_reason' => ''
                                            ]), ENT_QUOTES, 'UTF-8'); ?>'
                                            data-orders='<?php echo htmlspecialchars(json_encode($orders_by_customer[$cust['id']] ?? []), ENT_QUOTES, 'UTF-8'); ?>'
                                            style="padding: 6px 15px; border-radius: 8px; font-size: 0.85rem; cursor: pointer;">
                                        View Profile
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($active_customers)): ?>
                                <tr><td colspan="5" style="text-align: center;">No registered customers found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Blacklisted Customers Table -->
                <div id="blacklisted-customers-table" class="table-responsive customer-table-view hidden">
                    <table>
                        <thead>
                            <tr>
                                <th>Avatar</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Blacklist Reason</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($blacklisted_customers as $cust): 
                                $avatar_url = !empty($cust['profile_image']) ? 'CustomerData/' . htmlspecialchars($cust['profile_image']) : 'https://ui-avatars.com/api/?name=' . urlencode($cust['name']) . '&background=ef4444&color=fff&rounded=true';
                            ?>
                            <tr style="background: rgba(239, 68, 68, 0.05);">
                                <td><img src="<?php echo $avatar_url; ?>" alt="Profile" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid #ef4444;"></td>
                                <td style="font-weight: 600; color: #fca5a5;"><?php echo htmlspecialchars($cust['name']); ?> <span style="font-size:0.8rem; color:#ef4444;">🚫</span></td>
                                <td><a href="mailto:<?php echo htmlspecialchars($cust['email']); ?>" style="color:var(--text-color)"><?php echo htmlspecialchars($cust['email']); ?></a></td>
                                <td style="font-size: 0.9rem; color: #fca5a5; max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo htmlspecialchars($cust['blacklist_reason'] ?? 'No reason provided'); ?>">
                                    <?php echo htmlspecialchars($cust['blacklist_reason'] ?? 'No reason provided'); ?>
                                </td>
                                <td>
                                    <button class="btn-view btn-view-customer" 
                                            data-customer='<?php echo htmlspecialchars(json_encode([
                                                'id' => $cust['id'],
                                                'name' => $cust['name'],
                                                'email' => $cust['email'],
                                                'phone' => $cust['phone'] ?? 'N/A',
                                                'address' => $cust['address'] ?? 'N/A',
                                                'avatar' => $avatar_url,
                                                'joined' => date('M d, Y', strtotime($cust['created_at'])),
                                                'is_blacklisted' => 1,
                                                'blacklist_reason' => $cust['blacklist_reason'] ?? ''
                                            ]), ENT_QUOTES, 'UTF-8'); ?>'
                                            data-orders='<?php echo htmlspecialchars(json_encode($orders_by_customer[$cust['id']] ?? []), ENT_QUOTES, 'UTF-8'); ?>'
                                            style="padding: 6px 15px; border-radius: 8px; font-size: 0.85rem; cursor: pointer; border-color: rgba(239,68,68,0.4); color: #fca5a5;">
                                        View Details
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($blacklisted_customers)): ?>
                                <tr><td colspan="5" style="text-align: center; color: #94a3b8; padding: 20px;">No blacklisted customers found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Delete Modal -->
    <div id="deleteModal" class="modal-overlay">
        <div class="modal-content">
            <h3>Confirm Deletion</h3>
            <p>Are you sure you want to delete this <span id="modalType">item</span>? This action cannot be undone immediately.</p>
            <div class="modal-actions">
                <button onclick="closeModal()" class="btn-cancel">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="btn-delete" style="padding: 10px 20px; font-size: 1rem; border-radius: 10px;">Yes, Delete</a>
            </div>
        </div>
    </div>

    <!-- Customer Profile Modal -->
    <div id="customerProfileModal" class="modal-overlay">
        <div class="modal-content" style="max-width: 800px; width: 90%;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 20px;">
                <h3 style="margin: 0; border: none; padding: 0;">Customer Profile</h3>
                <button onclick="closeCustomerModal()" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            
            <div class="customer-profile-container" style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 30px;">
                <div class="customer-avatar" style="flex-shrink: 0;">
                    <img id="cp-avatar" src="" alt="Avatar" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary-gold);">
                </div>
                <div class="customer-details" style="flex-grow: 1;">
                    <h2 style="margin-top: 0; margin-bottom: 10px; color: var(--primary-pink); display: flex; align-items: center; gap: 12px;">
                        <span id="cp-name"></span>
                        <span id="cp-blacklist-symbol" style="cursor: pointer; font-size: 1.2rem; user-select: none;" title="Blacklist Customer">🚫</span>
                    </h2>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; font-size: 0.95rem;">
                        <p><strong>Email:</strong> <span id="cp-email"></span></p>
                        <p><strong>Phone:</strong> <span id="cp-phone"></span></p>
                        <p><strong>Joined:</strong> <span id="cp-joined"></span></p>
                        <p style="grid-column: 1 / -1;"><strong>Address:</strong> <span id="cp-address"></span></p>
                    </div>
                </div>
            </div>

            <!-- Blacklist Form (Toggled by clicking the 🚫 symbol) -->
            <div id="blacklist-form-container" class="hidden" style="margin-bottom: 25px; padding: 20px; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 12px; text-align: left;">
                <h4 style="margin-top: 0; margin-bottom: 12px; color: #f87171; display: flex; align-items: center; gap: 8px; font-size: 1.1rem;">🚫 Blacklist Customer</h4>
                <form action="admin.php" method="POST" id="blacklist-form" style="margin: 0;">
                    <input type="hidden" name="blacklist_customer_id" id="blacklist-customer-id">
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="color: #fca5a5; font-size: 0.85rem; margin-bottom: 6px; display: block;">Reason for Blacklisting (optional)</label>
                        <textarea name="blacklist_reason" id="blacklist-reason-input" rows="3" placeholder="e.g. Repeated non-payment, fraudulent behavior, or abusive conduct..." style="width:100%; padding: 10px 14px; background: rgba(0,0,0,0.3); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 8px; color: white; font-family: inherit; font-size: 0.95rem; resize: vertical; outline: none;"></textarea>
                    </div>
                    <div style="display: flex; gap: 12px; justify-content: flex-end;">
                        <button type="button" onclick="toggleBlacklistForm()" class="btn-cancel" style="padding: 8px 16px; font-size: 0.85rem; border-radius: 8px;">Cancel</button>
                        <button type="submit" name="submit_blacklist" class="btn-submit" style="width: auto; padding: 8px 20px; font-size: 0.85rem; border-radius: 8px; background: #ef4444; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4); border: none; color: white;">Blacklist Customer</button>
                    </div>
                </form>
            </div>

            <!-- Blacklist Status Banner (Shown if customer is already blacklisted) -->
            <div id="blacklist-status-banner" class="hidden" style="margin-bottom: 25px; padding: 20px; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); border-radius: 12px; text-align: left;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; gap: 10px;">
                    <h4 style="margin: 0; color: #ef4444; display: flex; align-items: center; gap: 8px; font-size: 1.1rem;">🚫 Blacklisted Customer</h4>
                    <form action="admin.php" method="POST" style="margin: 0;">
                        <input type="hidden" name="unblacklist_customer_id" id="unblacklist-customer-id">
                        <button type="submit" name="submit_unblacklist" class="btn-cancel" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 6px; border-color: rgba(239, 68, 68, 0.4); color: #fca5a5; background: rgba(239, 68, 68, 0.1);">Remove from Blacklist</button>
<button type="submit" name="submit_unblacklist" class="btn-submit" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 6px; margin-left: 8px; border-color: rgba(34,197,94,0.4); color: #a7f3d0; background: rgba(34,197,94,0.1);">Return as Good</button>
                    </form>
                </div>
                <p style="margin: 0; font-size: 0.95rem; color: #fca5a5; line-height: 1.4;"><strong>Reason:</strong> <span id="cp-blacklist-reason-text">None provided</span></p>
            </div>

            <h4 style="border-bottom: 1px solid var(--glass-border); padding-bottom: 10px; margin-bottom: 15px; color: var(--primary-gold);">Purchase History</h4>
            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                <table id="cp-orders-table" style="font-size: 0.9rem;">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="cp-orders-body">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </div>
            <div id="cp-no-orders" style="display: none; text-align: center; padding: 20px; color: #94a3b8;">
                No purchase history found for this customer.
            </div>
        </div>
    </div>

    <script>
        function confirmDeletion(e, url, type) {
            e.preventDefault();
            document.getElementById('confirmDeleteBtn').href = url;
            document.getElementById('modalType').innerText = type;
            document.getElementById('deleteModal').classList.add('show');
        }

        function closeModal() {
            document.getElementById('deleteModal').classList.remove('show');
        }
        
        function closeCustomerModal() {
            document.getElementById('customerProfileModal').classList.remove('show');
        }

        function toggleBlacklistForm() {
            const form = document.getElementById('blacklist-form-container');
            if (form.classList.contains('hidden')) {
                form.classList.remove('hidden');
                form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                form.classList.add('hidden');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Remove URL parameters so refresh doesn't trigger actions again
            if (window.history.replaceState) {
                const url = new URL(window.location);
                if (url.searchParams.has('delete_order') || url.searchParams.has('undo_order') || url.searchParams.has('success') || url.searchParams.has('delete') || url.searchParams.has('mark_ongoing') || url.searchParams.has('mark_closed') || url.searchParams.has('mark_pending')) {
                    url.searchParams.delete('delete_order');
                    url.searchParams.delete('undo_order');
                    url.searchParams.delete('success');
                    url.searchParams.delete('delete');
                    url.searchParams.delete('mark_ongoing');
                    url.searchParams.delete('mark_closed');
                    url.searchParams.delete('mark_pending');
                    window.history.replaceState({path:url.href}, '', url.href);
                }
            }

            const toggleBinBtn = document.getElementById('toggleBinBtn');
            const ordersTitle = document.getElementById('orders-section-title');
            if (toggleBinBtn) {
                toggleBinBtn.addEventListener('click', function() {
                    const activeTable = document.getElementById('active-orders-container');
                    const subTabs = document.querySelector('.order-sub-tabs');
                    const deletedTable = document.getElementById('deleted-orders-table');
                    if (activeTable.classList.contains('hidden')) {
                        activeTable.classList.remove('hidden');
                        if (subTabs) subTabs.style.display = 'flex';
                        deletedTable.classList.add('hidden');
                        this.innerHTML = '🗑️ Bin (<?php echo count($deleted_orders); ?>)';
                        this.classList.remove('btn-submit');
                        this.classList.add('btn-view');
                        if (ordersTitle) ordersTitle.innerText = 'Customer Orders';
                    } else {
                        activeTable.classList.add('hidden');
                        if (subTabs) subTabs.style.display = 'none';
                        deletedTable.classList.remove('hidden');
                        this.innerHTML = '← Back to Active Orders';
                        this.classList.remove('btn-view');
                        this.classList.add('btn-submit');
                        if (ordersTitle) ordersTitle.innerText = 'Deleted Orders Bin';
                        deletedTable.scrollIntoView({behavior: 'smooth', block: 'nearest'});
                    }
                });
            }

            const orderSearchInput = document.getElementById('orderSearchInput');
            if (orderSearchInput) {
                orderSearchInput.addEventListener('input', function() {
                    const query = this.value.toLowerCase();
                    const pendingRows = document.querySelectorAll('#pending-orders-table tbody tr');
                    const ongoingRows = document.querySelectorAll('#ongoing-orders-table tbody tr');
                    const closedRows = document.querySelectorAll('#closed-orders-table tbody tr');
                    const deletedRows = document.querySelectorAll('#deleted-orders-table tbody tr');
                    
                    const filterRows = (rows) => {
                        rows.forEach(row => {
                            if (row.children.length <= 1) return; // Skip empty/no-results rows
                            const text = row.innerText.toLowerCase();
                            if (text.includes(query)) {
                                row.style.display = '';
                            } else {
                                row.style.display = 'none';
                            }
                        });
                    };
                    
                    filterRows(pendingRows);
                    filterRows(ongoingRows);
                    filterRows(closedRows);
                    filterRows(deletedRows);
                });
            }

            // Product Search
            const productSearchInput = document.getElementById('productSearchInput');
            if (productSearchInput) {
                productSearchInput.addEventListener('input', function() {
                    const query = this.value.toLowerCase();
                    const productRows = document.querySelectorAll('.manage-products-card tbody tr');
                    
                    productRows.forEach(row => {
                        if (row.children.length <= 1) return; // Skip "No products found." row
                        const nameText = row.children[1] ? row.children[1].innerText.toLowerCase() : '';
                        const categoryText = row.children[2] ? row.children[2].innerText.toLowerCase() : '';
                        
                        if (nameText.includes(query) || categoryText.includes(query)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }

            // Customer Search
            const customerSearchInput = document.getElementById('customerSearchInput');
            if (customerSearchInput) {
                customerSearchInput.addEventListener('input', function() {
                    const query = this.value.toLowerCase();
                    const customerRows = document.querySelectorAll('.customer-table-view tbody tr');
                    
                    customerRows.forEach(row => {
                        if (row.children.length <= 1) return;
                        const text = row.innerText.toLowerCase();
                        if (text.includes(query)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }

            // Customer View Profile Button Logic
            const btnViewCustomers = document.querySelectorAll('.btn-view-customer');
            btnViewCustomers.forEach(btn => {
                btn.addEventListener('click', function() {
                    const customerData = JSON.parse(this.getAttribute('data-customer'));
                    const ordersData = JSON.parse(this.getAttribute('data-orders'));
                    
                    // Populate Profile
                    document.getElementById('cp-avatar').src = customerData.avatar;
                    document.getElementById('cp-name').innerText = customerData.name;
                    document.getElementById('cp-email').innerHTML = `<a href="mailto:${customerData.email}" style="color:var(--text-color)">${customerData.email}</a>`;
                    document.getElementById('cp-phone').innerText = customerData.phone || 'N/A';
                    document.getElementById('cp-address').innerText = customerData.address || 'N/A';
                    document.getElementById('cp-joined').innerText = customerData.joined;

                    // Blacklist fields setup
                    const isBlacklisted = customerData.is_blacklisted == 1;
                    const blacklistReason = customerData.blacklist_reason || '';
                    
                    document.getElementById('blacklist-customer-id').value = customerData.id;
                    document.getElementById('unblacklist-customer-id').value = customerData.id;
                    document.getElementById('blacklist-form-container').classList.add('hidden');
                    document.getElementById('blacklist-reason-input').value = '';
                    
                    const symbolEl = document.getElementById('cp-blacklist-symbol');
                    const bannerEl = document.getElementById('blacklist-status-banner');
                    
                    if (isBlacklisted) {
                        symbolEl.style.color = '#ef4444';
                        symbolEl.title = 'Blacklisted Customer';
                        bannerEl.classList.remove('hidden');
                        document.getElementById('cp-blacklist-reason-text').innerText = blacklistReason || 'No reason provided';
                    } else {
                        symbolEl.style.color = '#94a3b8';
                        symbolEl.title = 'Click to Blacklist';
                        bannerEl.classList.add('hidden');
                    }
                    
                    // Populate Orders
                    const tbody = document.getElementById('cp-orders-body');
                    const table = document.getElementById('cp-orders-table');
                    const noOrdersMsg = document.getElementById('cp-no-orders');
                    
                    tbody.innerHTML = ''; // Clear previous
                    
                    if (ordersData && ordersData.length > 0) {
                        table.style.display = 'table';
                        noOrdersMsg.style.display = 'none';
                        
                        ordersData.forEach(o => {
                            let itemsHTML = '';
                            try {
                                const items = JSON.parse(o.cart_details);
                                if (items) {
                                    items.forEach(item => {
                                        itemsHTML += `${item.name} (x${item.quantity})<br>`;
                                    });
                                }
                            } catch(e) { }
                            
                            let statusColor = '';
                            if (o.status === 'pending') statusColor = 'var(--primary-gold)';
                            else if (o.status === 'ongoing') statusColor = '#fbbf24';
                            else if (o.status === 'closed') statusColor = '#34d399';
                            
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td>#${o.id}</td>
                                <td>${new Date(o.created_at).toLocaleString('en-US', {month: 'short', day: 'numeric', year: 'numeric'})}</td>
                                <td style="font-size:0.85em;">${itemsHTML}</td>
                                <td style="color:var(--primary-pink); font-weight:bold;">$${o.total_amount}</td>
                                <td><span class="badge ${o.status}" style="margin:0;">${o.status.charAt(0).toUpperCase() + o.status.slice(1)}</span></td>
                            `;
                            tbody.appendChild(tr);
                        });
                    } else {
                        table.style.display = 'none';
                        noOrdersMsg.style.display = 'block';
                    }
                    
                    // Show Modal
                    document.getElementById('customerProfileModal').classList.add('show');
                });
            });

            const orderTabBtns = document.querySelectorAll('.order-tab-btn');
            const orderTableViews = document.querySelectorAll('.order-table-view');
            
            orderTabBtns.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    if (e) e.preventDefault();
                    orderTabBtns.forEach(b => {
                        b.classList.remove('active');
                        b.style.background = 'transparent';
                        b.style.color = '#fff';
                    });
                    
                    btn.classList.add('active');
                    const targetId = btn.getAttribute('data-target');
                    if (targetId === 'ongoing-orders-table') {
                        btn.style.background = 'rgba(245, 158, 11, 0.2)';
                        btn.style.color = '#fbbf24';
                    } else if (targetId === 'closed-orders-table') {
                        btn.style.background = 'rgba(16, 185, 129, 0.2)';
                        btn.style.color = '#34d399';
                    } else {
                        btn.style.background = 'rgba(59, 130, 246, 0.2)';
                        btn.style.color = '#60a5fa';
                    }
                    
                    orderTableViews.forEach(view => view.classList.add('hidden'));
                    document.getElementById(targetId).classList.remove('hidden');
                });
            });

            // Initialize active sub-tab if needed
            const initialSubTab = '<?php echo $active_sub_tab; ?>';
            if (initialSubTab === 'ongoing') {
                const ongoingBtn = document.querySelector('.order-tab-btn[data-target="ongoing-orders-table"]');
                if(ongoingBtn) ongoingBtn.click();
            } else if (initialSubTab === 'closed') {
                const closedBtn = document.querySelector('.order-tab-btn[data-target="closed-orders-table"]');
                if(closedBtn) closedBtn.click();
            } else if (initialSubTab === 'blacklisted') {
                const blacklistedBtn = document.querySelector('.customer-tab-btn[data-target="blacklisted-customers-table"]');
                if(blacklistedBtn) blacklistedBtn.click();
            }

            const customerTabBtns = document.querySelectorAll('.customer-tab-btn');
            const customerTableViews = document.querySelectorAll('.customer-table-view');
            
            customerTabBtns.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    if (e) e.preventDefault();
                    customerTabBtns.forEach(b => {
                        b.classList.remove('active');
                        b.style.background = 'transparent';
                        b.style.color = '#fff';
                    });
                    
                    btn.classList.add('active');
                    const targetId = btn.getAttribute('data-target');
                    if (targetId === 'blacklisted-customers-table') {
                        btn.style.background = 'rgba(239, 68, 68, 0.2)';
                        btn.style.color = '#fca5a5';
                    } else {
                        btn.style.background = 'rgba(59, 130, 246, 0.2)';
                        btn.style.color = '#60a5fa';
                    }
                    
                    customerTableViews.forEach(view => view.classList.add('hidden'));
                    document.getElementById(targetId).classList.remove('hidden');
                });
            });

            const cpBlacklistSymbol = document.getElementById('cp-blacklist-symbol');
            if (cpBlacklistSymbol) {
                cpBlacklistSymbol.addEventListener('click', function() {
                    toggleBlacklistForm();
                });
            }

            const navLinks = document.querySelectorAll('.admin-nav .nav-link');
            const views = document.querySelectorAll('.admin-view');

            navLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    
                    // Remove active from all links
                    navLinks.forEach(l => l.classList.remove('active'));
                    // Add active to clicked link
                    link.classList.add('active');

                    // Hide all views
                    views.forEach(v => v.classList.add('hidden'));
                    
                    // Show target view
                    const targetId = link.getAttribute('data-target');
                    document.getElementById(targetId).classList.remove('hidden');
                });
            });

            // Notification Sidebar Toggle Logic (Improved for touch responsiveness)
            const notifWrapper = document.querySelector('.notification-wrapper');
            const notifPanel = document.getElementById('notificationPanel');
            const closeNotifBtn = document.getElementById('closeNotifPanel');

            if (notifWrapper && notifPanel) {
                notifWrapper.addEventListener('click', (e) => {
                    e.stopPropagation();
                    notifPanel.classList.toggle('hidden');
                });
            }

            // Notification Item Click Logic: Navigate, Clear Search, and Scroll
            if (notifPanel) {
                notifPanel.addEventListener('click', (e) => {
                    const item = e.target.closest('.order-notif-item');
                    if (item) {
                        const orderId = item.getAttribute('data-order-id');
                        if (!orderId) return;

                        // 1. Switch to Orders Tab
                        const ordersNavLink = document.querySelector('.nav-link[data-target="view-orders"]');
                        if (ordersNavLink) ordersNavLink.click();

                        // 2. Switch to Pending Sub-tab
                        const pendingTabBtn = document.querySelector('.order-tab-btn[data-target="pending-orders-table"]');
                        if (pendingTabBtn) pendingTabBtn.click();

                        // 3. Clear search filter to ensure row is visible
                        const orderSearchInput = document.getElementById('orderSearchInput');
                        if (orderSearchInput) {
                            orderSearchInput.value = '';
                            orderSearchInput.dispatchEvent(new Event('input'));
                        }

                        // 4. Scroll to the specific order row
                        setTimeout(() => {
                            const targetRow = document.querySelector(`#pending-orders-table tr[data-order-id="${orderId}"]`);
                            if (targetRow) {
                                targetRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                targetRow.style.backgroundColor = 'rgba(52, 211, 153, 0.4)';
                                setTimeout(() => targetRow.style.backgroundColor = '', 2000);
                            }
                        }, 350);

                        // 5. Close notification panel
                        notifPanel.classList.add('hidden');
                    }
                });
            }

            // Live Notifications Polling
            async function refreshAdminNotifications() {
                try {
                    const res = await fetch('api.php?action=get_admin_notifications');
                    const data = await res.json();
                    if (data.success) {
                        // Update Badge
                        const badge = document.getElementById('notifCount');
                        if (badge) {
                            badge.innerText = data.total_count;
                            badge.style.display = data.total_count > 0 ? 'block' : 'none';
                        }

                        // Update Orders List
                        const ordersUl = document.getElementById('notif-orders-list');
                        if (ordersUl) {
                            ordersUl.innerHTML = data.orders.length > 0 
                                ? data.orders.map(o => `<li class="order-notif-item" data-order-id="${o.id}" style="background:rgba(52, 211, 153, 0.08); border: 1px solid rgba(52, 211, 153, 0.15); padding:12px; margin:10px 0; border-radius:10px; font-size:0.9rem; color:#a7f3d0; cursor:pointer; transition: 0.2s;"><div style="display:flex; justify-content:space-between; margin-bottom: 4px;"><strong>Order #${o.id}</strong><span style="color:#34d399; font-size: 0.7rem;">PENDING</span></div><span style="font-size: 0.8rem; opacity: 0.7;">${new Date(o.created_at).toLocaleString('en-US', {month:'short', day:'numeric', year:'numeric', hour:'numeric', minute:'numeric', hour12:true})}</span></li>`).join('')
                                : '<li style="color: #94a3b8; font-size: 0.9rem; padding: 10px; background: rgba(255,255,255,0.03); border-radius: 8px; text-align: center;">No new orders</li>';
                        }

                        // Update Messages List
                        const msgsUl = document.getElementById('notif-messages-list');
                        if (msgsUl) {
                            msgsUl.innerHTML = data.messages.length > 0
                                ? data.messages.map(m => `<li style="background:rgba(96, 165, 250, 0.08); border: 1px solid rgba(96, 165, 250, 0.15); padding:12px; margin:10px 0; border-radius:10px; font-size:0.9rem; color:#bfdbfe;"><strong>${m.subject || 'Message'}</strong><br><span style="font-size: 0.8rem; opacity: 0.7;">${new Date(m.created_at).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'})}</span></li>`).join('')
                                : '<li style="color: #94a3b8; font-size: 0.9rem; padding: 10px; background: rgba(255,255,255,0.03); border-radius: 8px; text-align: center;">No unread messages</li>';
                        }
                    }
                } catch (e) { console.error("Notif update error", e); }
            }

            // Start Polling every 10 seconds
            setInterval(refreshAdminNotifications, 10000);

            if (closeNotifBtn && notifPanel) {
                closeNotifBtn.addEventListener('click', () => {
                    notifPanel.classList.add('hidden');
                });
            }

            // Close notification panel when clicking outside
            document.addEventListener('click', (e) => {
                if (notifPanel && !notifPanel.contains(e.target) && !notifWrapper.contains(e.target)) {
                    notifPanel.classList.add('hidden');
                }
            });

            // Navbar Horizontal Scroll Logic with Mouse Wheel Support
            const navBar = document.getElementById('admin-navbar');
            const prevBtn = document.getElementById('nav-prev');
            const nextBtn = document.getElementById('nav-next');

            if (navBar && prevBtn && nextBtn) {
                const checkScroll = () => {
                    const sl = Math.ceil(navBar.scrollLeft);
                    const sw = navBar.scrollWidth;
                    const cw = navBar.clientWidth;
                    const max = sw - cw;

                    // Toggle arrow visibility with a small buffer for sub-pixel accuracy
                    prevBtn.classList.toggle('hidden', sl <= 5 || max <= 0);
                    nextBtn.classList.toggle('hidden', sl >= max - 5 || max <= 0);
                };

                prevBtn.addEventListener('click', () => navBar.scrollTo({ left: 0, behavior: 'smooth' }));
                nextBtn.addEventListener('click', () => navBar.scrollTo({ left: navBar.scrollWidth, behavior: 'smooth' }));

                // Intercept vertical mouse wheel and convert to horizontal scroll
                navBar.addEventListener('wheel', (e) => {
                    if (e.deltaY !== 0) {
                        e.preventDefault();
                        navBar.scrollLeft += e.deltaY;
                    }
                });

                navBar.addEventListener('scroll', checkScroll);
                window.addEventListener('resize', checkScroll);
                setTimeout(checkScroll, 100);
            }

            // =============================================
            // --- Admin Live Stock Polling ---
            // =============================================
            async function refreshAdminStock() {
                try {
                    const res = await fetch(`api.php?action=get_products&_t=${Date.now()}`, { cache: 'no-store' });
                    const data = await res.json();
                    if (!data.success) return;

                    data.products.forEach(p => {
                        const stockInput = document.querySelector(`input[data-product-id="${p.id}"]`);
                        const dot = document.querySelector(`.live-stock-dot[data-product-id="${p.id}"]`);
                        const newStock = parseInt(p.stock) || 0;

                        // Update input only if admin is NOT currently typing in it
                        if (stockInput && document.activeElement !== stockInput) {
                            const currentVal = parseInt(stockInput.value);
                            if (currentVal !== newStock) {
                                stockInput.value = newStock;
                                // Flash green to signal a live update
                                stockInput.style.background = 'rgba(52, 211, 153, 0.25)';
                                setTimeout(() => { stockInput.style.background = 'rgba(0,0,0,0.2)'; }, 1200);
                            }
                        }

                        // Update live dot color: green = in stock, red = sold out
                        if (dot) {
                            dot.style.color = newStock <= 0 ? '#ef4444' : '#34d399';
                            dot.title = newStock <= 0 ? 'Sold Out (live)' : `Live: ${newStock} in stock`;
                        }
                    });
                } catch(e) { console.error('Admin live stock poll failed:', e); }
            }

            // Poll every 5 seconds and run once immediately on load
            refreshAdminStock();
            setInterval(refreshAdminStock, 5000);

            // =============================================
            // --- Stock Report Charts ---
            // =============================================
            const stockCharts = {};

            async function loadStockReport() {
                try {
                    const res = await fetch(`api.php?action=get_stock_report&_t=${Date.now()}`, { cache: 'no-store' });
                    const data = await res.json();
                    if (!data.success) return;

                    // --- Stat Cards ---
                    const totalStock = data.products.reduce((s, p) => s + parseInt(p.stock || 0), 0);
                    const sold7d     = data.daily_sales.reduce((s, v) => s + v, 0);
                    const soldOut    = data.products.filter(p => parseInt(p.stock || 0) <= 0).length;
                    document.getElementById('stat-total-products').textContent = data.products.length;
                    document.getElementById('stat-total-stock').textContent    = totalStock;
                    document.getElementById('stat-sold-7d').textContent        = sold7d;
                    document.getElementById('stat-sold-out').textContent       = soldOut;
                    document.getElementById('report-last-updated').textContent = `Updated: ${new Date().toLocaleTimeString()}`;

                    const gridOpts = (extra = {}) => ({
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { labels: { color: 'rgba(255,255,255,0.65)', font: { size: 12 } } } },
                        scales: {
                            x: { ticks: { color: '#94a3b8', font: { size: 11 } }, grid: { color: 'rgba(255,255,255,0.05)' } },
                            y: { ticks: { color: '#94a3b8', font: { size: 11 } }, grid: { color: 'rgba(255,255,255,0.06)' }, beginAtZero: true }
                        },
                        ...extra
                    });

                    // --- Chart 1: Daily Sales Bar ---
                    const c1 = document.getElementById('chart-daily-sales');
                    if (c1) {
                        if (stockCharts.daily) stockCharts.daily.destroy();
                        stockCharts.daily = new Chart(c1, {
                            type: 'bar',
                            data: {
                                labels: data.daily_labels,
                                datasets: [{
                                    label: 'Units Sold',
                                    data: data.daily_sales,
                                    backgroundColor: data.daily_sales.map(() => 'rgba(251,191,36,0.35)'),
                                    borderColor: '#fbbf24',
                                    borderWidth: 2,
                                    borderRadius: 7,
                                }]
                            },
                            options: gridOpts()
                        });
                    }

                    // --- Chart 2: Category Doughnut (7-day) ---
                    const c2 = document.getElementById('chart-category-sales');
                    if (c2) {
                        if (stockCharts.category) stockCharts.category.destroy();
                        const cats = data.category_sales;
                        const hasData = Object.values(cats).some(v => v > 0);
                        const total7 = Object.values(cats).reduce((a,b)=>a+b,0);
                        stockCharts.category = new Chart(c2, {
                            type: 'doughnut',
                            data: {
                                labels: Object.keys(cats),
                                datasets: [{
                                    data: hasData ? Object.values(cats) : [1, 1, 1],
                                    backgroundColor: ['rgba(139,90,43,0.8)', 'rgba(196,116,106,0.8)', 'rgba(107,142,35,0.8)'],
                                    borderColor: ['#8b5a2b', '#c4746a', '#6b8e23'],
                                    borderWidth: 2,
                                }]
                            },
                            options: {
                                responsive: true, maintainAspectRatio: false,
                                plugins: {
                                    legend: { position: 'bottom', labels: { color: 'rgba(255,255,255,0.65)', padding: 16, font: { size: 12 } } },
                                    tooltip: { callbacks: { label: ctx => {
                                        const pct = total7 > 0 ? ((ctx.parsed/total7)*100).toFixed(1) : 0;
                                        return hasData ? ` ${ctx.label}: ${ctx.parsed} units (${pct}%)` : ` ${ctx.label}: No sales yet`;
                                    }}}
                                },
                                cutout: '60%',
                            }
                        });
                    }

                    // --- Monthly Sales Bar + Category Pie (Stock Report) ---
                    // Fetch monthly data using the sales_report endpoint for last 90 days
                    try {
                        const mRes = await fetch(`api.php?action=get_sales_report&date_from=${encodeURIComponent(new Date(Date.now()-89*86400000).toISOString().slice(0,10))}&date_to=${encodeURIComponent(new Date().toISOString().slice(0,10))}&_t=${Date.now()}`, {cache:'no-store'});
                        const mData = await mRes.json();
                        if (mData.success) {
                            const cm = document.getElementById('chart-monthly-sales');
                            if (cm) {
                                if (stockCharts.monthly) stockCharts.monthly.destroy();
                                stockCharts.monthly = new Chart(cm, {
                                    type: 'bar',
                                    data: { labels: mData.monthly_labels, datasets: [{
                                        label: 'Units Sold', data: mData.monthly_values,
                                        backgroundColor: 'rgba(139,92,246,0.45)', borderColor: '#8b5cf6', borderWidth: 2, borderRadius: 6
                                    }]},
                                    options: gridOpts()
                                });
                            }
                            const cmp = document.getElementById('chart-monthly-category');
                            if (cmp) {
                                if (stockCharts.monthlyCat) stockCharts.monthlyCat.destroy();
                                const mCats = mData.category_sales;
                                const mHas  = Object.values(mCats).some(v => v > 0);
                                const mTot  = Object.values(mCats).reduce((a,b)=>a+b,0);
                                stockCharts.monthlyCat = new Chart(cmp, {
                                    type: 'doughnut',
                                    data: { labels: Object.keys(mCats), datasets: [{ data: mHas ? Object.values(mCats) : [1,1,1], backgroundColor: ['rgba(139,90,43,0.8)','rgba(196,116,106,0.8)','rgba(107,142,35,0.8)'], borderColor: ['#8b5a2b','#c4746a','#6b8e23'], borderWidth: 2 }]},
                                    options: { responsive:true, maintainAspectRatio:false, plugins: { legend:{ position:'bottom', labels:{ color:'rgba(255,255,255,0.65)', padding:14, font:{size:11} }}, tooltip:{ callbacks:{ label: ctx => { const pct = mTot>0?((ctx.parsed/mTot)*100).toFixed(1):0; return mHas?` ${ctx.label}: ${ctx.parsed} units (${pct}%)`:`No sales yet`; }}}}, cutout:'60%' }
                                });
                            }
                        }
                    } catch(me) { console.error('Monthly stock chart failed:', me); }

                    // --- Chart 3: Remaining Stock Horizontal Bar ---
                    const c3 = document.getElementById('chart-stock-levels');
                    if (c3) {
                        if (stockCharts.stock) stockCharts.stock.destroy();
                        const pNames  = data.products.map(p => p.name.length > 18 ? p.name.slice(0,16) + '…' : p.name);
                        const pStocks = data.products.map(p => parseInt(p.stock || 0));
                        const barBg   = pStocks.map(s => s <= 0 ? 'rgba(239,68,68,0.55)' : s <= 5 ? 'rgba(251,191,36,0.55)' : 'rgba(52,211,153,0.55)');
                        const barBdr  = pStocks.map(s => s <= 0 ? '#ef4444' : s <= 5 ? '#fbbf24' : '#34d399');
                        stockCharts.stock = new Chart(c3, {
                            type: 'bar',
                            data: {
                                labels: pNames,
                                datasets: [{ label: 'Remaining Stock', data: pStocks, backgroundColor: barBg, borderColor: barBdr, borderWidth: 1.5, borderRadius: 5 }]
                            },
                            options: gridOpts({ indexAxis: 'y', plugins: { legend: { display: false } } })
                        });
                    }

                    // --- Chart 4: Top Products Sold ---
                    const c4 = document.getElementById('chart-top-products');
                    if (c4) {
                        if (stockCharts.top) stockCharts.top.destroy();
                        const sorted = [...data.product_sales].sort((a, b) => b.sold - a.sold).slice(0, 8);
                        stockCharts.top = new Chart(c4, {
                            type: 'bar',
                            data: {
                                labels: sorted.map(p => p.name.length > 14 ? p.name.slice(0,12) + '…' : p.name),
                                datasets: [{ label: 'Units Sold', data: sorted.map(p => p.sold), backgroundColor: 'rgba(96,165,250,0.45)', borderColor: '#60a5fa', borderWidth: 2, borderRadius: 6 }]
                            },
                            options: gridOpts({ plugins: { legend: { display: false } } })
                        });
                    }

                    // --- Stock Status Table ---
                    const tbody = document.getElementById('stock-report-tbody');
                    if (tbody) {
                        const soldMap = {};
                        data.product_sales.forEach(ps => { soldMap[ps.name] = ps.sold; });
                        tbody.innerHTML = data.products.length === 0
                            ? '<tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:30px;">No products found.</td></tr>'
                            : data.products.map(p => {
                                const stock = parseInt(p.stock || 0);
                                const sold  = soldMap[p.name] || 0;
                                const badge = stock <= 0
                                    ? '<span style="background:rgba(239,68,68,0.18);color:#f87171;padding:3px 12px;border-radius:20px;font-size:0.78rem;font-weight:600;">Sold Out</span>'
                                    : stock <= 5
                                    ? '<span style="background:rgba(251,191,36,0.18);color:#fbbf24;padding:3px 12px;border-radius:20px;font-size:0.78rem;font-weight:600;">Low Stock</span>'
                                    : '<span style="background:rgba(52,211,153,0.18);color:#34d399;padding:3px 12px;border-radius:20px;font-size:0.78rem;font-weight:600;">In Stock</span>';
                                const catClass = p.category ? p.category.toLowerCase() : '';
                                return `<tr>
                                    <td style="font-weight:500;">${p.name}</td>
                                    <td><span class="badge ${catClass}">${p.category}</span></td>
                                    <td style="font-weight:700;font-size:1.05rem;color:${stock<=0?'#f87171':stock<=5?'#fbbf24':'#34d399'}">${stock}</td>
                                    <td style="color:#60a5fa;font-weight:600;">${sold}</td>
                                    <td>${badge}</td>
                                </tr>`;
                            }).join('');
                    }

                } catch(e) { console.error('Stock report failed:', e); }
            }

            // =============================================
            // --- Reports Tab: Sub-tab Switching ---
            // =============================================
            window.switchReportTab = function(tab) {
                const salesDiv = document.getElementById('sub-sales-report');
                const stockDiv = document.getElementById('sub-stock-report');
                const profitDiv = document.getElementById('sub-profit-report');
                const btnSales = document.getElementById('btn-sales-report');
                const btnStock = document.getElementById('btn-stock-report');
                const btnProfit = document.getElementById('btn-profit-report');

                salesDiv.classList.add('hidden');
                stockDiv.classList.add('hidden');
                profitDiv.classList.add('hidden');
                
                btnSales.style.cssText = 'display:flex;align-items:center;gap:8px;padding:12px 28px;border-radius:30px;border:2px solid rgba(251,191,36,0.2);background:transparent;color:#94a3b8;font-size:1rem;font-weight:700;cursor:pointer;transition:all .25s;';
                btnStock.style.cssText = 'display:flex;align-items:center;gap:8px;padding:12px 28px;border-radius:30px;border:2px solid rgba(96,165,250,0.3);background:transparent;color:#94a3b8;font-size:1rem;font-weight:700;cursor:pointer;transition:all .25s;';
                btnProfit.style.cssText = 'display:flex;align-items:center;gap:8px;padding:12px 28px;border-radius:30px;border:2px solid rgba(236,72,153,0.3);background:transparent;color:#94a3b8;font-size:1rem;font-weight:700;cursor:pointer;transition:all .25s;';

                if (tab === 'sales') {
                    salesDiv.classList.remove('hidden');
                    btnSales.style.cssText = 'display:flex;align-items:center;gap:8px;padding:12px 28px;border-radius:30px;border:2px solid rgba(251,191,36,0.5);background:rgba(251,191,36,0.12);color:#fbbf24;font-size:1rem;font-weight:700;cursor:pointer;transition:all .25s;';
                    if (!window._salesLoaded) loadSalesReport();
                } else if (tab === 'stock') {
                    stockDiv.classList.remove('hidden');
                    btnStock.style.cssText = 'display:flex;align-items:center;gap:8px;padding:12px 28px;border-radius:30px;border:2px solid rgba(96,165,250,0.5);background:rgba(96,165,250,0.12);color:#60a5fa;font-size:1rem;font-weight:700;cursor:pointer;transition:all .25s;';
                    setTimeout(loadStockReport, 120);
                } else if (tab === 'profit') {
                    profitDiv.classList.remove('hidden');
                    btnProfit.style.cssText = 'display:flex;align-items:center;gap:8px;padding:12px 28px;border-radius:30px;border:2px solid rgba(236,72,153,0.5);background:rgba(236,72,153,0.12);color:#ec4899;font-size:1rem;font-weight:700;cursor:pointer;transition:all .25s;';
                    if (!window._profitLoaded) loadProfitReport();
                }
            };

            // =============================================
            // --- Sales Report Charts ---
            // =============================================
            const salesCharts = {};

            window.setSalesRange = function(days) {
                const to   = new Date();
                const from = new Date();
                from.setDate(from.getDate() - (days - 1));
                document.getElementById('sales-date-from').value = from.toISOString().slice(0,10);
                document.getElementById('sales-date-to').value   = to.toISOString().slice(0,10);
                loadSalesReport();
            };

            // Init default date range (last 7 days)
            (function initDates() {
                const to   = new Date();
                const from = new Date();
                from.setDate(from.getDate() - 6);
                document.getElementById('sales-date-from').value = from.toISOString().slice(0,10);
                document.getElementById('sales-date-to').value   = to.toISOString().slice(0,10);
            })();

            window.loadSalesReport = async function() {
                const from = document.getElementById('sales-date-from').value;
                const to   = document.getElementById('sales-date-to').value;
                if (!from || !to) return;
                try {
                    const res  = await fetch(`api.php?action=get_sales_report&date_from=${from}&date_to=${to}&_t=${Date.now()}`, { cache:'no-store' });
                    const data = await res.json();
                    if (!data.success) return;
                    window._salesLoaded = true;

                    // Stat cards
                    const days = Math.round((new Date(to) - new Date(from)) / 86400000) + 1;
                    const avg  = days > 0 ? (data.total_units / days).toFixed(1) : 0;
                    document.getElementById('sr-stat-units').textContent   = data.total_units;
                    document.getElementById('sr-stat-revenue').textContent = '$' + data.total_revenue.toFixed(2);
                    document.getElementById('sr-stat-profit').textContent  = '$' + (data.total_profit ? data.total_profit.toFixed(2) : '0.00');
                    document.getElementById('sr-stat-days').textContent    = days;
                    document.getElementById('sr-stat-avg').textContent     = avg;
                    document.getElementById('sales-last-updated').textContent = 'Updated: ' + new Date().toLocaleTimeString();

                    const pieColors = ['rgba(139,90,43,0.85)','rgba(196,116,106,0.85)','rgba(107,142,35,0.85)'];
                    const pieBorder = ['#8b5a2b','#c4746a','#6b8e23'];

                    const pieOpts = (hasData, catLabels, catData, revData) => ({
                        responsive: true, maintainAspectRatio: false,
                        plugins: {
                            legend: { position:'bottom', labels:{ color:'rgba(255,255,255,0.7)', padding:14, font:{size:11} } },
                            tooltip: { callbacks: { label: ctx => {
                                const total = catData.reduce((a,b)=>a+b,0);
                                const pct   = total > 0 ? ((ctx.parsed/total)*100).toFixed(1) : 0;
                                return hasData ? ` ${ctx.label}: ${ctx.parsed} units (${pct}%)` : ` ${ctx.label}: No sales yet`;
                            }}}
                        },
                        cutout: '58%'
                    });

                    const gridOpts = () => ({
                        responsive:true, maintainAspectRatio:false,
                        plugins:{ legend:{ labels:{ color:'rgba(255,255,255,0.65)', font:{size:12} } } },
                        scales:{
                            x:{ ticks:{ color:'#94a3b8', font:{size:11} }, grid:{ color:'rgba(255,255,255,0.05)' } },
                            y:{ ticks:{ color:'#94a3b8', font:{size:11} }, grid:{ color:'rgba(255,255,255,0.06)' }, beginAtZero:true }
                        }
                    });

                    // Daily Bar
                    const c1 = document.getElementById('sr-chart-daily');
                    if (c1) {
                        if (salesCharts.daily) salesCharts.daily.destroy();
                        salesCharts.daily = new Chart(c1, {
                            type:'bar',
                            data:{ labels: data.daily_labels, datasets:[{
                                label:'Units Sold', data: data.daily_values,
                                backgroundColor:'rgba(251,191,36,0.35)', borderColor:'#fbbf24', borderWidth:2, borderRadius:6
                            },{
                                label:'Revenue ($)', data: data.daily_revenues,
                                backgroundColor:'rgba(52,211,153,0.25)', borderColor:'#34d399', borderWidth:2, borderRadius:6, yAxisID:'y'
                            }]},
                            options: gridOpts()
                        });
                    }

                    // Daily Pie
                    const c2 = document.getElementById('sr-chart-daily-pie');
                    if (c2) {
                        if (salesCharts.dailyPie) salesCharts.dailyPie.destroy();
                        const cats    = data.category_sales;
                        const catKeys = Object.keys(cats);
                        const catVals = Object.values(cats);
                        const hasData = catVals.some(v => v > 0);
                        salesCharts.dailyPie = new Chart(c2, {
                            type:'doughnut',
                            data:{ labels: catKeys, datasets:[{ data: hasData ? catVals : [1,1,1], backgroundColor: pieColors, borderColor: pieBorder, borderWidth:2 }]},
                            options: pieOpts(hasData, catKeys, hasData ? catVals : [1,1,1], Object.values(data.category_rev))
                        });
                    }

                    // Monthly Bar
                    const c3 = document.getElementById('sr-chart-monthly');
                    if (c3) {
                        if (salesCharts.monthly) salesCharts.monthly.destroy();
                        salesCharts.monthly = new Chart(c3, {
                            type:'bar',
                            data:{ labels: data.monthly_labels, datasets:[{
                                label:'Units Sold', data: data.monthly_values,
                                backgroundColor:'rgba(139,92,246,0.4)', borderColor:'#8b5cf6', borderWidth:2, borderRadius:6
                            },{
                                label:'Revenue ($)', data: data.monthly_revenues,
                                backgroundColor:'rgba(96,165,250,0.3)', borderColor:'#60a5fa', borderWidth:2, borderRadius:6
                            }]},
                            options: gridOpts()
                        });
                    }

                    // Monthly Pie
                    const c4 = document.getElementById('sr-chart-monthly-pie');
                    if (c4) {
                        if (salesCharts.monthlyPie) salesCharts.monthlyPie.destroy();
                        const cats    = data.category_sales;
                        const catKeys = Object.keys(cats);
                        const catVals = Object.values(cats);
                        const hasData = catVals.some(v => v > 0);
                        salesCharts.monthlyPie = new Chart(c4, {
                            type:'doughnut',
                            data:{ labels: catKeys, datasets:[{ data: hasData ? catVals : [1,1,1], backgroundColor: pieColors, borderColor: pieBorder, borderWidth:2 }]},
                            options: pieOpts(hasData, catKeys, hasData ? catVals : [1,1,1], Object.values(data.category_rev))
                        });
                    }

                } catch(e) { console.error('Sales report failed:', e); }
            };

            // =============================================
            // --- Profit Report JS Logic ---
            // =============================================
            window.setProfitRange = function(days) {
                const to   = new Date();
                const from = new Date();
                from.setDate(from.getDate() - (days - 1));
                document.getElementById('profit-date-from').value = from.toISOString().slice(0,10);
                document.getElementById('profit-date-to').value   = to.toISOString().slice(0,10);
                loadProfitReport();
            };

            (function initProfitDates() {
                const to   = new Date();
                const from = new Date();
                from.setDate(from.getDate() - 6);
                document.getElementById('profit-date-from').value = from.toISOString().slice(0,10);
                document.getElementById('profit-date-to').value   = to.toISOString().slice(0,10);
            })();

            window.loadProfitReport = async function() {
                const from = document.getElementById('profit-date-from').value;
                const to   = document.getElementById('profit-date-to').value;
                if (!from || !to) return;
                try {
                    const res  = await fetch(`api.php?action=get_sales_report&date_from=${from}&date_to=${to}&_t=${Date.now()}`);
                    const data = await res.json();
                    if (!data.success) return;
                    window._profitLoaded = true;

                    const catTbody = document.getElementById('profit-cat-tbody');
                    catTbody.innerHTML = '';
                    let tSold=0, tRev=0, tProf=0;
                    for (const cat in data.category_sales) {
                        const s = data.category_sales[cat] || 0;
                        const r = data.category_rev[cat] || 0;
                        const p = data.category_prof[cat] || 0;
                        tSold+=s; tRev+=r; tProf+=p;
                        catTbody.innerHTML += `<tr>
                            <td>${cat}</td><td>${s}</td><td>$${r.toFixed(2)}</td>
                            <td style="color:#ec4899; font-weight:bold;">$${p.toFixed(2)}</td>
                        </tr>`;
                    }
                    catTbody.innerHTML += `<tr style="background:rgba(255,255,255,0.05); font-weight:bold;">
                        <td>TOTAL</td><td>${tSold}</td><td>$${tRev.toFixed(2)}</td>
                        <td style="color:#ec4899;">$${tProf.toFixed(2)}</td>
                    </tr>`;

                    const itemTbody = document.getElementById('profit-item-tbody');
                    itemTbody.innerHTML = '';
                    const items = data.product_sales.sort((a,b) => b.profit - a.profit);
                    if (items.length === 0) {
                        itemTbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">No items sold in this period</td></tr>';
                    } else {
                        items.forEach(it => {
                            itemTbody.innerHTML += `<tr>
                                <td>${it.name}</td><td>${it.category}</td>
                                <td>$${(it.unit_cost||0).toFixed(2)}</td><td>$${(it.unit_price||0).toFixed(2)}</td>
                                <td>${it.sold}</td><td>$${it.revenue.toFixed(2)}</td>
                                <td style="color:#ec4899; font-weight:bold;">$${it.profit.toFixed(2)}</td>
                            </tr>`;
                        });
                    }
                } catch(e) { console.error('Profit report failed', e); }
            };

            // Load when Reports nav tab is clicked
            document.querySelectorAll('.nav-link[data-target="view-reports"]').forEach(link => {
                link.addEventListener('click', () => setTimeout(() => {
                    // Default: load sales report on first open
                    if (!window._salesLoaded) loadSalesReport();
                }, 120));
            });

            // Auto-refresh Sales Report every 60s if visible
            setInterval(() => {
                const rv = document.getElementById('sub-sales-report');
                if (rv && !rv.classList.contains('hidden') && document.getElementById('view-reports') && !document.getElementById('view-reports').classList.contains('hidden')) {
                    loadSalesReport();
                }
            }, 60000);

            // Auto-refresh Stock Report every 30s if visible
            setInterval(() => {
                const rv = document.getElementById('sub-stock-report');
                if (rv && !rv.classList.contains('hidden') && document.getElementById('view-reports') && !document.getElementById('view-reports').classList.contains('hidden')) {
                    loadStockReport();
                }
            }, 30000);

        });
    </script>

    <!-- Floating Notification Sidebar (Separate Panel) -->
    <div id="notificationPanel" class="notification-panel hidden" style="position: fixed; top: 0; right: 0; height: 100vh; width: 350px; background: rgba(15, 23, 42, 0.98); backdrop-filter: blur(20px); border-left: 1px solid rgba(255, 255, 255, 0.1); z-index: 10000; padding: 30px; box-shadow: -10px 0 30px rgba(0,0,0,0.6); color: white; overflow-y: auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px;">
            <h3 style="margin:0; color:#34d399; font-size: 1.2rem;">Notifications</h3>
            <button id="closeNotifPanel" style="background:none; border:none; color:#fff; font-size:1.8rem; cursor:pointer; line-height:1;">&times;</button>
        </div>
        
        <div style="margin-bottom: 30px;">
            <h4 style="margin:0 0 15px; color:#34d399; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">New Orders</h4>
            <ul id="notif-orders-list" style="list-style:none; padding:0; margin:0;">
                <?php foreach($pending_orders as $order): ?>
                    <li class="order-notif-item" data-order-id="<?php echo $order['id']; ?>" style="background:rgba(52, 211, 153, 0.08); border: 1px solid rgba(52, 211, 153, 0.15); padding:12px; margin:10px 0; border-radius:10px; font-size:0.9rem; color:#a7f3d0; cursor:pointer; transition: 0.2s;">
                        <div style="display:flex; justify-content:space-between; margin-bottom: 4px;">
                            <strong>Order #<?php echo $order['id']; ?></strong>
                            <span style="color:#34d399; font-size: 0.7rem;">PENDING</span>
                        </div>
                        <span style="font-size: 0.8rem; opacity: 0.7;"><?php echo htmlspecialchars(date('M d, Y h:i A', strtotime($order['created_at']))); ?></span>
                    </li>
                <?php endforeach; ?>
                <?php if(empty($pending_orders)): ?>
                    <li style="color: #94a3b8; font-size: 0.9rem; padding: 10px; background: rgba(255,255,255,0.03); border-radius: 8px; text-align: center;">No new orders</li>
                <?php endif; ?>
            </ul>
        </div>

        <div>
            <h4 style="margin:0 0 15px; color:#60a5fa; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">Unread Messages</h4>
            <ul id="notif-messages-list" style="list-style:none; padding:0; margin:0;">
                <?php
                $stmt_msgs = $pdo->query("SELECT * FROM messages WHERE is_read = 0 ORDER BY created_at DESC LIMIT 5");
                $messages = $stmt_msgs->fetchAll();
                foreach($messages as $msg): ?>
                    <li style="background:rgba(96, 165, 250, 0.08); border: 1px solid rgba(96, 165, 250, 0.15); padding:12px; margin:10px 0; border-radius:10px; font-size:0.9rem; color:#bfdbfe;">
                        <strong><?php echo htmlspecialchars($msg['subject'] ?? 'Message'); ?></strong><br>
                        <span style="font-size: 0.8rem; opacity: 0.7;"><?php echo htmlspecialchars(date('M d, Y', strtotime($msg['created_at']))); ?></span>
                    </li>
                <?php endforeach; ?>
                <?php if(empty($messages)): ?>
                    <li style="color: #94a3b8; font-size: 0.9rem; padding: 10px; background: rgba(255,255,255,0.03); border-radius: 8px; text-align: center;">No unread messages</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <style>
        .notification-panel.hidden { display: none !important; }
    </style>
</body>
</html>
