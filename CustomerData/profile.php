<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

$customer_id = $_SESSION['customer_id'];
$success_msg = '';
$error_msg = '';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    // Handle File Upload
    $profile_image = $_SESSION['profile_image'] ?? null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'avatars/';
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $tmp_name = $_FILES['profile_image']['tmp_name'];
        $file_ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_ext, $allowed_exts)) {
            $new_filename = uniqid('avatar_') . '.' . $file_ext;
            if (move_uploaded_file($tmp_name, $upload_dir . $new_filename)) {
                $profile_image = 'avatars/' . $new_filename;
                $_SESSION['profile_image'] = $profile_image;
            }
        } else {
            $error_msg = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
        }
    }

    if (empty($error_msg)) {
        try {
            $stmt = $pdo->prepare("UPDATE customers SET name = ?, email = ?, phone = ?, address = ?, profile_image = ? WHERE id = ?");
            $stmt->execute([$name, $email, $phone, $address, $profile_image, $customer_id]);
            $_SESSION['customer_name'] = $name;
            $success_msg = "Profile updated successfully!";
        } catch (PDOException $e) {
            $error_msg = "Error updating profile. Email might already be in use.";
        }
    }
}

// Fetch Customer Data
$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch Order History
$stmt_orders = $pdo->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC");
$stmt_orders->execute([$customer_id]);
$orders = $stmt_orders->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - ඒ රtin</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Cormorant+Garamond:wght@500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        body {
            background: linear-gradient(135deg, #fff7f2, #fdf1ea);
            color: var(--text-color);
        }
        .profile-container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 40px;
        }

        .profile-card {
            background: rgba(255, 248, 244, 0.9);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(196, 138, 90, 0.1);
            border: 1px solid rgba(216, 156, 138, 0.2);
            height: fit-content;
        }

        .profile-card h2 {
            font-family: 'Cormorant Garamond', serif;
            color: var(--text-color);
            margin-bottom: 25px;
            font-size: 2rem;
            border-bottom: 1px solid rgba(216, 156, 138, 0.2);
            padding-bottom: 15px;
        }

        .avatar-upload {
            text-align: center;
            margin-bottom: 30px;
        }

        .avatar-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--primary-gold);
            margin-bottom: 15px;
            box-shadow: 0 5px 15px rgba(196, 138, 90, 0.3);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--light-text);
            font-weight: 500;
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid rgba(216, 156, 138, 0.3);
            border-radius: 10px;
            background: #fff;
            font-family: 'Poppins', sans-serif;
            color: var(--text-color);
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-gold);
            box-shadow: 0 0 8px rgba(196, 138, 90, 0.2);
        }

        .btn-update {
            width: 100%;
            background: linear-gradient(135deg, var(--rose-gold), var(--primary-gold));
            color: white;
            border: none;
            padding: 14px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(196, 138, 90, 0.3);
        }

        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(196, 138, 90, 0.4);
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* Order History Table */
        .order-history-card {
            background: rgba(255, 248, 244, 0.9);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(196, 138, 90, 0.1);
            border: 1px solid rgba(216, 156, 138, 0.2);
            overflow-x: auto;
        }

        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .order-table th, .order-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(216, 156, 138, 0.2);
        }

        .order-table th {
            color: var(--primary-gold);
            font-weight: 600;
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.2rem;
        }

        .order-table td {
            color: var(--text-color);
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-ongoing { background: #cce5ff; color: #004085; }
        .status-closed { background: #d4edda; color: #155724; }

        @media (max-width: 900px) {
            .profile-container { grid-template-columns: 1fr; }
        }
        
    </style>
</head>
<body>

    <header class="main-header">
        <nav class="navbar">
            <a href="../index.php" style="text-decoration:none;"><div class="logo"><img src="../logo.jpg" alt="Logo" class="brand-logo"> <span class="brand-name">ඒ රtin</span></div></a>
            <ul class="nav-links">
                <li><a href="../index.php" class="btn-nav-auth" style="background: var(--muted-gold);">Back to Store</a></li>
            </ul>
        </nav>
    </header>

    <div class="profile-container">
        
        <!-- Left Column: Profile Update Form -->
        <div class="profile-card">
            <h2>My Profile</h2>
            
            <?php if($success_msg): ?><div class="alert alert-success"><?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
            <?php if($error_msg): ?><div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div><?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data">
                <div class="avatar-upload">
                    <?php 
                        $avatar_url = !empty($customer['profile_image']) ? htmlspecialchars($customer['profile_image']) : 'https://ui-avatars.com/api/?name=' . urlencode($customer['name']) . '&background=c48a5a&color=fff&size=150';
                    ?>
                    <img src="<?= $avatar_url ?>" alt="Profile" class="avatar-preview" id="avatarPreview">
                    <br><br>
                    <input type="file" name="profile_image" accept="image/*" onchange="previewImage(this)">
                </div>

                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($customer['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($customer['email']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Mobile Number</label>
                    <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($customer['phone'] ?? '') ?>" placeholder="e.g. +94 77 123 4567">
                </div>

                <div class="form-group">
                    <label>Default Delivery Address</label>
                    <textarea name="address" class="form-control" rows="3" placeholder="Enter your full home address..."><?= htmlspecialchars($customer['address'] ?? '') ?></textarea>
                </div>

                <button type="submit" name="update_profile" class="btn-update">Save Changes</button>
            </form>
        </div>

        <!-- Right Column: Order History -->
        <div class="order-history-card">
            <h2>My Order History</h2>
            
            <?php if (empty($orders)): ?>
                <p style="color: var(--light-text); text-align: center; margin-top: 50px;">You haven't placed any orders yet.</p>
                <div style="text-align:center; margin-top: 20px;">
                    <a href="../index.php" class="btn-update" style="display:inline-block; width:auto; text-decoration:none;">Start Shopping</a>
                </div>
            <?php else: ?>
                <table class="order-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($orders as $order): ?>
                        <tr>
                            <td>#<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></td>
                            <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                            <td style="max-width: 250px; font-size: 0.9rem; line-height: 1.4;">
                                <?php
                                    $cart = json_decode($order['cart_details'], true);
                                    if(is_array($cart)){
                                        $items = [];
                                        foreach($cart as $item) {
                                            $qty = $item['quantity'] ?? $item['qty'] ?? 1;
                                            $items[] = '<strong>' . $qty . 'x</strong> ' . htmlspecialchars($item['name']);
                                        }
                                        echo implode('<br>', $items);
                                    } else {
                                        echo "Details unavailable";
                                    }
                                ?>
                            </td>
                            <td style="font-weight: 600;">$<?= number_format($order['total_amount'], 2) ?></td>
                            <td>
                                <span class="status-badge status-<?= strtolower($order['status']) ?>">
                                    <?= htmlspecialchars(ucfirst($order['status'])) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatarPreview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
