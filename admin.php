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

// Handle Add Product

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $category = $_POST['category'];
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
        $stmt = $pdo->prepare("INSERT INTO products (name, description, category, price, stock, image_path) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $description, $category, $price, $stock, $imagePath])) {
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
$stmt_pending = $pdo->query("SELECT * FROM orders WHERE is_deleted = 0 AND status = 'pending' ORDER BY id DESC");
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

$stmt_orders_by_customer = $pdo->query("SELECT * FROM orders WHERE customer_id IS NOT NULL ORDER BY id DESC");
$orders_by_customer = [];
foreach ($stmt_orders_by_customer->fetchAll() as $order) {
    $orders_by_customer[$order['customer_id']][] = $order;
}

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
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h2><img src="logo.jpg" alt="Logo" class="admin-brand-logo"   style="height:40px; width:40px; border-radius:50%; object-fit:cover;"> Store Admin Panel</h2>
            <nav class="admin-nav">
                <a href="#" class="nav-link <?php echo $active_tab == 'view-stock' ? 'active' : ''; ?>" data-target="view-stock">Stock</a>
                <a href="#" class="nav-link <?php echo $active_tab == 'view-gallery' ? 'active' : ''; ?>" data-target="view-gallery">Gallery</a>
                <a href="#" class="nav-link <?php echo $active_tab == 'view-orders' ? 'active' : ''; ?>" data-target="view-orders">Orders</a>
                <a href="#" class="nav-link <?php echo $active_tab == 'view-customers' ? 'active' : ''; ?>" data-target="view-customers">Customers</a>
                <a href="#" class="nav-link <?php echo $active_tab == 'view-email' ? 'active' : ''; ?>" data-target="view-email">Email Template</a>
            </nav>
            <a href="index.php" class="btn-view" target="_blank">View Live Site</a>
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
                        <label>Price ($)</label>
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
                <h3>Manage Products</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
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
                                <td>$<?php echo htmlspecialchars($p['price']); ?></td>
                                <td>
                                    <form action="admin.php" method="POST" style="display: flex; gap: 5px; align-items: center; justify-content: center;">
                                        <input type="hidden" name="update_stock_id" value="<?php echo $p['id']; ?>">
                                        <input type="number" name="new_stock" value="<?php echo htmlspecialchars($p['stock'] ?? 0); ?>" min="0" style="width: 60px; padding: 4px; border-radius: 4px; border: 1px solid var(--glass-border); background: rgba(0,0,0,0.2); color: white;">
                                        <button type="submit" name="update_stock" class="btn-submit" style="padding: 4px 10px; font-size: 0.8rem; border-radius: 4px;">Save</button>
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
                        <tr>
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
                            if($items) {
                                foreach($items as $item) {
                                    $itemsList .= htmlspecialchars($item['name']) . ' (x' . $item['quantity'] . ')<br>';
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
                            <td>
                                <a href="admin.php?undo_order=<?php echo $o['id']; ?>" style="padding: 6px 14px; font-size: 0.85rem; text-decoration: none; background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); border-radius: 8px; font-weight: 600;">Restore</a>
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

        <!-- Customers View -->
        <div id="view-customers" class="admin-view <?php echo $active_tab == 'view-customers' ? '' : 'hidden'; ?>">
            <div class="card manage-customers-card">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--glass-border); margin-bottom: 25px; padding-bottom: 15px;">
                    <h3 style="border-bottom: none; margin-bottom: 0; padding-bottom: 0;">Registered Customers</h3>
                    <input type="text" id="customerSearchInput" placeholder="🔍 Search name, email..." style="padding: 8px 15px; border-radius: 8px; border: 1px solid var(--glass-border); background: rgba(0,0,0,0.2); color: white; outline: none; min-width: 250px; font-family: inherit;">
                </div>
                <div class="table-responsive">
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
                            <?php foreach($customers_list as $cust): 
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
                                                'joined' => date('M d, Y', strtotime($cust['created_at']))
                                            ]), ENT_QUOTES, 'UTF-8'); ?>'
                                            data-orders='<?php echo htmlspecialchars(json_encode($orders_by_customer[$cust['id']] ?? []), ENT_QUOTES, 'UTF-8'); ?>'
                                            style="padding: 6px 15px; border-radius: 8px; font-size: 0.85rem; cursor: pointer;">
                                        View Profile
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($customers_list)): ?>
                                <tr><td colspan="5" style="text-align: center;">No registered customers found.</td></tr>
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
                    <h2 id="cp-name" style="margin-top: 0; margin-bottom: 10px; color: var(--primary-pink);"></h2>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; font-size: 0.95rem;">
                        <p><strong>Email:</strong> <span id="cp-email"></span></p>
                        <p><strong>Phone:</strong> <span id="cp-phone"></span></p>
                        <p><strong>Joined:</strong> <span id="cp-joined"></span></p>
                        <p style="grid-column: 1 / -1;"><strong>Address:</strong> <span id="cp-address"></span></p>
                    </div>
                </div>
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

            // Customer Search
            const customerSearchInput = document.getElementById('customerSearchInput');
            if (customerSearchInput) {
                customerSearchInput.addEventListener('input', function() {
                    const query = this.value.toLowerCase();
                    const customerRows = document.querySelectorAll('#customers-table tbody tr');
                    
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
        });
    </script>
</body>
</html>
