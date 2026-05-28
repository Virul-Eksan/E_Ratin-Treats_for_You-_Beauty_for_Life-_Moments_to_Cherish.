<?php
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    $pdo_setup = new PDO("mysql:host=$host", $user, $pass);
    $pdo_setup->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo_setup->exec("CREATE DATABASE IF NOT EXISTS product_seller_db");
    $pdo_setup = null; // Close connection
} catch(PDOException $e) {
    die("Setup DB Connection failed: " . $e->getMessage());
}

require_once 'db.php';

try {
    // Create products table
    $sql = "CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        category VARCHAR(100) NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        stock INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $pdo->exec($sql);
    
    // Ensure existing products table is updated with stock column
    try { $pdo->exec("ALTER TABLE products ADD COLUMN stock INT NOT NULL DEFAULT 0"); } catch (PDOException $e) {}
    
    // Create customers table
    $sql_customers = "CREATE TABLE IF NOT EXISTS customers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(50) NULL,
        address TEXT NULL,
        profile_image VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql_customers);
    
    // Ensure existing customers table is updated
    try { $pdo->exec("ALTER TABLE customers ADD COLUMN phone VARCHAR(50) NULL"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE customers ADD COLUMN address TEXT NULL"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE customers ADD COLUMN profile_image VARCHAR(255) NULL"); } catch (PDOException $e) {}

    // Create orders table
    $sql_orders = "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT NULL,
        full_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        mobile_number VARCHAR(50) NOT NULL,
        home_address TEXT NOT NULL,
        home_town VARCHAR(100) NOT NULL,
        postal_code VARCHAR(20) NOT NULL,
        cart_details TEXT NOT NULL,
        total_amount DECIMAL(10, 2) NOT NULL,
        is_deleted TINYINT(1) DEFAULT 0,
        status VARCHAR(20) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $pdo->exec($sql_orders);
    
    // In case the orders table already exists, try to add the column (ignore error if it exists)
    try {
        $pdo->exec("ALTER TABLE orders ADD COLUMN customer_id INT NULL");
        $pdo->exec("ALTER TABLE orders ADD FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL");
    } catch (PDOException $e) {
        // Column might already exist, which is fine
    }
    
    // Create settings table
    $sql_settings = "CREATE TABLE IF NOT EXISTS settings (
        setting_key VARCHAR(100) PRIMARY KEY,
        setting_value TEXT NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql_settings);

    // Insert default email template if not exists
    $default_subject = "Order Confirmation - ඒ රtin (E RATIN)";
    $default_body = "Hello {customer_name},\n\nThank you for joining us and shopping at ඒ රtin (E RATIN)!\n\nWe have successfully received your order on {order_date}.\nTotal Amount: \${total_amount}\n\nYour order is being processed and is expected to be shipped around {send_date}.\n\nIf you have any questions, feel free to reply to this email.\n\nBest Regards,\nE RATIN Team";
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?), (?, ?), (?, ?), (?, ?)");
    $stmt->execute(['email_subject', $default_subject, 'email_body', $default_body, 'smtp_username', '', 'smtp_password', '']);

    // Create uploads directory if it doesn't exist
    if (!file_exists('uploads')) {
        mkdir('uploads', 0777, true);
    }

    echo "<h3>Database, tables, and folders setup successfully!</h3>";
    echo "<a href='admin.php'>Go to Admin Panel</a> | <a href='index.php'>Go to Frontend</a>";
} catch(PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>
