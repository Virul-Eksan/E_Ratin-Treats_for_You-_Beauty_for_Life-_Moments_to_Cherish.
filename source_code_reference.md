# Product Seller App - Source Code Reference


## index.php


`$ext

<?php
session_start();

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>à¶’ à¶»tin (E RATIN) - Treats, Beauty, Moments</title>
    <link rel="stylesheet" href="style.css?v=6">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Overlay Animation -->
    <div id="loader" class="loader">
        <div class="spinner"></div>
        <h2>Welcome to à¶’ à¶»tin...</h2>
    </div>

    <header class="main-header">
        <nav class="navbar">
            <div class="logo"><img src="logo.jpg" alt="Logo" class="brand-logo"> <span class="brand-name">à¶’ à¶»tin</span></div>
            <ul class="nav-links">
                <li><a href="#all" data-category="All">All Items</a></li>
                <li><a href="#chocolates" data-category="Chocolates">Chocolates</a></li>
                <li><a href="#cosmetics" data-category="Cosmetics">Cosmetics</a></li>
                <li><a href="#nuts" data-category="Nuts">Nuts</a></li>
                <?php if (isset($_SESSION['customer_id'])): ?>
                    <li><a href="index.php?action=logout" class="btn-nav-auth">Logout</a></li>
                <?php else: ?>
                    <li><a href="CustomerData/login.php" class="btn-nav-auth">Sign Up / Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="hero">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>Treats for You,<br><span class="hero-highlight">Beauty</span> for Life,<br><span class="hero-highlight">Moments</span> to Cherish.</h1>
                    <p>Discover handpicked treats and beauty essentials for your most beautiful moments.</p>
                    <a href="#all" class="btn-shop-now" data-category="All">SHOP NOW â†’</a>
                </div>
                <div class="hero-image">
                    <img src="logo.jpg" alt="E RATIN - Treats, Beauty, Moments" class="hero-logo-img">
                </div>
            </div>
        </div>
    </header>

    <main class="container">
        <!-- Home View: Category Sliders -->
        <div id="home-view">
            <section class="slider-section">
                <div class="section-title">
                    <h2>Premium Chocolates</h2>
                </div>
                <div class="slider-container" id="slider-chocolates">
                    <!-- Javascript will inject slider cards here -->
                </div>
            </section>

            <section class="slider-section">
                <div class="section-title">
                    <h2>Luxury Cosmetics</h2>
                </div>
                <div class="slider-container" id="slider-cosmetics">
                </div>
            </section>

            <section class="slider-section">
                <div class="section-title">
                    <h2>Premium Nuts</h2>
                </div>
                <div class="slider-container" id="slider-nuts">
                </div>
            </section>
        </div>

        <!-- Category View: Products Grid -->
        <div id="category-view" class="hidden-view">
            <button id="btn-back-home" class="btn-back">â† Back to Categories</button>
            <section class="products-section">
                <div class="section-title">
                    <h2 id="category-title">All Items</h2>
                </div>
                
                <div id="products-grid" class="products-grid">
                    <!-- Products will be dynamically loaded here via JS -->
                </div>
            </section>
        </div>
    </main>

    <!-- Floating Cart Button -->
    <div id="cart-btn" class="cart-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
        <span id="cart-badge" class="cart-badge">0</span>
    </div>

    <!-- Cart Sidebar -->
    <div id="cart-sidebar" class="cart-sidebar">
        <div class="cart-header">
            <h2>Your Cart</h2>
            <button id="close-cart" class="close-cart">&times;</button>
        </div>
        <div id="cart-items" class="cart-items">
            <!-- Cart items will be dynamically injected here -->
        </div>
        <div class="cart-footer">
            <div class="cart-total">
                <span>Total:</span>
                <span id="cart-total-price">$0.00</span>
            </div>
            <button id="btn-checkout" class="btn-checkout">Checkout</button>
        </div>
    </div>

    <!-- Checkout Modal -->
    <div id="checkout-modal" class="modal-overlay hidden">
        <div class="checkout-modal-content">
            <button id="close-checkout" class="close-modal">&times;</button>
            <h2>Checkout Details</h2>
            <form id="checkout-form" class="checkout-form">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" id="co-name" required placeholder="John Doe">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="co-email" required placeholder="john@example.com">
                </div>
                <div class="form-group mobile-group">
                    <label>Mobile Number</label>
                    <div class="mobile-input-wrapper">
                        <select id="co-region" class="region-select">
                            <option value="+1">+1 (US)</option>
                            <option value="+44">+44 (UK)</option>
                            <option value="+91">+91 (IN)</option>
                            <option value="+61">+61 (AU)</option>
                            <option value="+94">+94 (LK)</option>
                        </select>
                        <input type="tel" id="co-mobile" required placeholder="771234567" pattern="[0-9]{7,15}" title="Please enter a valid mobile number (digits only)">
                    </div>
                </div>
                <div class="form-group">
                    <label>Home Address</label>
                    <input type="text" id="co-address" required placeholder="123 Main St, Apt 4B">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Home Town</label>
                        <input type="text" id="co-town" required placeholder="New York">
                    </div>
                    <div class="form-group">
                        <label>Postal Code</label>
                        <input type="text" id="co-postal" required placeholder="10001">
                    </div>
                </div>
                
                <div class="checkout-summary">
                    <p>Total Amount: <strong id="co-total-amount" style="color: var(--primary-gold);">$0.00</strong></p>
                </div>

                <button type="submit" class="btn-confirm">Confirm Order</button>
            </form>
        </div>
    </div>

    <footer>
        <p>à¶’ à¶»tin (E RATIN) &copy; 2026 | <a href="admin.php" style="color:var(--dark-gold); text-decoration:none;">Admin Access</a></p>
    </footer>

    <script>
        const isLoggedIn = <?php echo isset($_SESSION['customer_id']) ? 'true' : 'false'; ?>;
    </script>
    <script src="script.js?v=4"></script>
</body>
</html>


``n

## style.css


`$ext

:root {

    /* Main Luxury Colors */
    --primary-gold: #c48a5a;
    --dark-gold: #a56a43;
    --light-gold: #f7e7dc;
    --muted-gold: #d8b49c;

    /* Background Colors */
    --bg-color: #fff7f2;
    --card-bg: rgba(255, 248, 244, 0.85);

    /* Text Colors */
    --text-color: #4b2e2b;
    --light-text: #7b5e57;

    /* Extra Accent Colors */
    --soft-pink: #f3d6d2;
    --rose-gold: #d89c8a;
    --cream: #f8f1ec;

}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #fff7f2, #fdf1ea);
    color: var(--text-color);
    overflow-x: hidden;
}

/* Loader Animation */
.loader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: linear-gradient(135deg, var(--cream), var(--soft-pink));
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    transition: opacity 1s ease-out, visibility 1s ease-out;
}

.loader.hidden {
    opacity: 0;
    visibility: hidden;
}

.loader h2 {
    color: var(--primary-gold);
    font-family: 'Cormorant Garamond', serif;
    font-size: 2.5rem;
    margin-top: 20px;
    text-shadow: 1px 1px 4px rgba(196, 138, 90, 0.3);
    animation: pulseText 1.5s infinite;
}


.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid rgba(196, 138, 90, 0.2);
    border-top-color: var(--primary-gold);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

@keyframes pulseText {
    0% { transform: scale(1); opacity: 0.8; }
    50% { transform: scale(1.05); opacity: 1; }
    100% { transform: scale(1); opacity: 0.8; }
}

/* Navbar */
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 50px;
    background: rgba(255, 248, 244, 0.92);
    backdrop-filter: blur(12px);
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 4px 20px rgba(196, 138, 90, 0.12);
    border-bottom: 1px solid rgba(216, 156, 138, 0.15);
}

.logo {
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--primary-gold);
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
}

.brand-logo {
    height: 40px;
    width: 40px;
    border-radius: 50%;
    object-fit: cover;
    box-shadow: 0 2px 8px rgba(196, 138, 90, 0.25);
}

.nav-links {
    list-style: none;
    display: flex;
    gap: 30px;
}

.nav-links a {
    text-decoration: none;
    color: var(--text-color);
    font-weight: 500;
    font-size: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: color 0.3s;
    position: relative;
}

.nav-links a::after {
    content: '';
    position: absolute;
    bottom: -4px;
    left: 0;
    width: 0;
    height: 2px;
    background: var(--rose-gold);
    transition: width 0.3s;
}

.nav-links a:hover {
    color: var(--primary-gold);
}

.nav-links a:hover::after {
    width: 100%;
}

.btn-nav-auth {
    background: linear-gradient(45deg, #e4b89a, #c48a5a);
    color: white !important;
    padding: 8px 24px;
    border-radius: 25px;
    font-weight: 600;
    letter-spacing: 0.5px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    box-shadow: 0 4px 15px rgba(196, 138, 90, 0.3);
}

.btn-nav-auth::after {
    display: none !important;
}

.btn-nav-auth:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(196, 138, 90, 0.5);
    color: white !important;
}

/* Hero Section */
.hero {
    text-align: left;
    padding: 60px 50px 80px;
    background: linear-gradient(
        135deg,
        rgba(243, 214, 210, 0.4),
        rgba(248, 241, 236, 0.6),
        rgba(255, 247, 242, 0.8)
    );
    border-bottom-left-radius: 50% 20px;
    border-bottom-right-radius: 50% 20px;
    border-bottom: 1px solid rgba(216, 156, 138, 0.15);
}

.hero-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    max-width: 1200px;
    margin: 0 auto;
    gap: 40px;
}

.hero-text {
    flex: 1;
    max-width: 550px;
}

.hero h1 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 3.5rem;
    color: var(--text-color);
    margin-bottom: 20px;
    font-weight: 700;
    line-height: 1.2;
}

.hero-highlight {
    color: var(--rose-gold);
}

.hero p {
    font-size: 1.15rem;
    color: var(--light-text);
    max-width: 480px;
    line-height: 1.8;
    margin-bottom: 30px;
}

.btn-shop-now {
    display: inline-block;
    background: linear-gradient(135deg, var(--rose-gold), var(--primary-gold));
    color: white;
    text-decoration: none;
    padding: 14px 32px;
    border-radius: 30px;
    font-weight: 600;
    font-size: 0.95rem;
    letter-spacing: 1px;
    transition: transform 0.3s, box-shadow 0.3s;
    box-shadow: 0 6px 18px rgba(216, 156, 138, 0.35);
}

.btn-shop-now:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(216, 156, 138, 0.5);
}

.hero-image {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
}

.hero-logo-img {
    max-width: 420px;
    width: 100%;
    height: auto;
    border-radius: 50%;
    box-shadow: 0 15px 50px rgba(196, 138, 90, 0.2);
    animation: floatHero 4s ease-in-out infinite;
}

@keyframes floatHero {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-12px); }
}

@media (max-width: 768px) {
    .hero-content {
        flex-direction: column;
        text-align: center;
    }
    .hero-text {
        max-width: 100%;
    }
    .hero h1 {
        font-size: 2.5rem;
    }
    .hero p {
        max-width: 100%;
    }
    .hero-logo-img {
        max-width: 280px;
    }
}

/* Products Section */
.container {
    max-width: 1200px;
    margin: 50px auto;
    padding: 0 20px;
    min-height: 50vh;
}

.section-title {
    text-align: center;
    margin-bottom: 50px;
}

.section-title h2 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 2.5rem;
    color: var(--text-color);
    position: relative;
    display: inline-block;
    font-weight: 700;
}

.section-title h2::after {
    content: 'â™¥';
    position: absolute;
    bottom: -20px;
    left: 50%;
    transform: translateX(-50%);
    color: var(--rose-gold);
    font-size: 1rem;
    width: auto;
    height: auto;
    background: none;
    box-shadow: none;
}

/* Grid */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 30px;
}

.product-card {
    background: rgba(255, 255, 255, 0.75);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(75, 46, 43, 0.08);
    transition: transform 0.3s, box-shadow 0.3s;
    backdrop-filter: blur(5px);
    border: 1px solid rgba(216, 156, 138, 0.12);
    display: flex;
    flex-direction: column;
}

.product-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 35px rgba(196, 138, 90, 0.15);
    border-color: rgba(216, 156, 138, 0.3);
}

.product-img {
    width: 100%;
    height: 250px;
    object-fit: cover;
    transition: transform 0.4s;
}

.product-card:hover .product-img {
    transform: scale(1.03);
}

.product-info {
    padding: 20px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.badge-cat {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 10px;
    align-self: flex-start;
    letter-spacing: 0.5px;
}

.badge-chocolates { background: rgba(139, 90, 43, 0.12); color: #8b5a2b; }
.badge-cosmetics { background: rgba(216, 156, 138, 0.2); color: #c4746a; }
.badge-nuts { background: rgba(107, 142, 35, 0.12); color: #6b8e23; }

.product-title {
    font-size: 1.3rem;
    margin-bottom: 10px;
    color: var(--text-color);
    font-weight: 600;
}

.product-desc {
    font-size: 0.9rem;
    color: var(--light-text);
    margin-bottom: 20px;
    line-height: 1.6;
    flex-grow: 1;
}

.product-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: auto;
}

.product-price {
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--primary-gold);
}

.btn-buy {
    background: linear-gradient(135deg, #d89c8a, #c48a5a);
    color: white;
    border: none;
    padding: 10px 22px;
    border-radius: 30px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s;
    box-shadow: 0 6px 15px rgba(216, 156, 138, 0.35);
}

.btn-buy:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(216, 156, 138, 0.45);
}

footer {
    text-align: center;
    padding: 30px;
    background: var(--cream);
    color: var(--light-text);
    font-weight: 500;
    margin-top: 50px;
    border-top: 1px solid rgba(216, 156, 138, 0.15);
}

footer a {
    color: var(--primary-gold);
    text-decoration: none;
    transition: color 0.3s;
}

footer a:hover {
    color: var(--dark-gold);
}

@media (max-width: 768px) {
    .nav-links {
        display: none;
    }
    .hero h1 {
        font-size: 2.5rem;
    }
}

/* --- Cart Styles --- */

.cart-btn {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: linear-gradient(135deg, var(--rose-gold), var(--primary-gold));
    color: white;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    box-shadow: 0 8px 20px rgba(196, 138, 90, 0.35);
    z-index: 999;
    transition: transform 0.3s, box-shadow 0.3s;
}

.cart-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 12px 30px rgba(196, 138, 90, 0.5);
}

.cart-btn.pulse {
    animation: cartPulse 0.5s ease-out;
}

@keyframes cartPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

.cart-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: var(--rose-gold);
    color: white;
    font-size: 0.8rem;
    font-weight: bold;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    border: 2px solid var(--bg-color);
}

.cart-sidebar {
    position: fixed;
    top: 0;
    right: -400px;
    width: 380px;
    height: 100vh;
    background: rgba(255, 248, 244, 0.97);
    backdrop-filter: blur(12px);
    box-shadow: -10px 0 40px rgba(75, 46, 43, 0.12);
    z-index: 1000;
    transition: right 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
    border-left: 1px solid rgba(216, 156, 138, 0.15);
}

.cart-sidebar.open {
    right: 0;
}

.cart-header {
    padding: 20px;
    border-bottom: 1px solid rgba(216, 156, 138, 0.15);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.cart-header h2 {
    color: var(--text-color);
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.5rem;
}

.close-cart {
    background: none;
    border: none;
    color: var(--light-text);
    font-size: 2rem;
    cursor: pointer;
    transition: color 0.3s;
}

.close-cart:hover {
    color: var(--rose-gold);
}

.cart-items {
    flex-grow: 1;
    overflow-y: auto;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.cart-item {
    display: flex;
    background: rgba(255, 255, 255, 0.8);
    border-radius: 12px;
    padding: 12px;
    border: 1px solid rgba(216, 156, 138, 0.1);
    box-shadow: 0 2px 8px rgba(75, 46, 43, 0.05);
}

.cart-item-img {
    width: 60px;
    height: 60px;
    border-radius: 10px;
    object-fit: cover;
    margin-right: 15px;
}

.cart-item-info {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.cart-item-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-color);
}

.cart-item-price {
    color: var(--primary-gold);
    font-weight: bold;
}

.cart-item-controls {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 5px;
}

.qty-btn {
    background: rgba(216, 156, 138, 0.15);
    border: 1px solid rgba(216, 156, 138, 0.3);
    color: var(--text-color);
    width: 26px;
    height: 26px;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    justify-content: center;
    align-items: center;
    transition: background 0.3s;
}

.qty-btn:hover {
    background: var(--rose-gold);
    color: white;
}

.cart-item-qty {
    font-size: 0.9rem;
    font-weight: bold;
}

.cart-footer {
    padding: 20px;
    border-top: 1px solid rgba(216, 156, 138, 0.15);
    background: var(--cream);
}

.cart-total {
    display: flex;
    justify-content: space-between;
    font-size: 1.2rem;
    font-weight: bold;
    margin-bottom: 20px;
    color: var(--text-color);
}

.cart-total span:last-child {
    color: var(--primary-gold);
}

.btn-checkout {
    width: 100%;
    background: linear-gradient(135deg, var(--rose-gold), var(--primary-gold));
    color: white;
    border: none;
    padding: 15px;
    border-radius: 30px;
    font-size: 1.1rem;
    font-weight: bold;
    cursor: pointer;
    transition: transform 0.3s, box-shadow 0.3s;
    box-shadow: 0 6px 15px rgba(196, 138, 90, 0.3);
}

.btn-checkout:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(196, 138, 90, 0.4);
}

/* --- Checkout Modal Styles --- */

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(75, 46, 43, 0.4);
    backdrop-filter: blur(6px);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 2000;
    opacity: 1;
    visibility: visible;
    transition: opacity 0.3s, visibility 0.3s;
}

.modal-overlay.hidden {
    opacity: 0;
    visibility: hidden;
}

.checkout-modal-content {
    background: rgba(255, 248, 244, 0.97);
    width: 90%;
    max-width: 500px;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 20px 50px rgba(75, 46, 43, 0.15);
    border: 1px solid rgba(216, 156, 138, 0.2);
    position: relative;
    max-height: 90vh;
    overflow-y: auto;
}

.close-modal {
    position: absolute;
    top: 20px;
    right: 20px;
    background: none;
    border: none;
    color: var(--light-text);
    font-size: 1.5rem;
    cursor: pointer;
    transition: color 0.3s;
}

.close-modal:hover {
    color: var(--rose-gold);
}

.checkout-modal-content h2 {
    color: var(--text-color);
    font-family: 'Cormorant Garamond', serif;
    margin-bottom: 20px;
    font-size: 1.8rem;
    text-align: center;
}

.checkout-form .form-group {
    margin-bottom: 15px;
}

.checkout-form label {
    display: block;
    margin-bottom: 5px;
    color: var(--light-text);
    font-weight: 600;
    font-size: 0.9rem;
}

.checkout-form input[type="text"],
.checkout-form input[type="email"],
.checkout-form input[type="tel"],
.checkout-form select {
    width: 100%;
    padding: 12px 15px;
    border-radius: 10px;
    border: 1px solid rgba(216, 156, 138, 0.25);
    background: rgba(255, 255, 255, 0.8);
    color: var(--text-color);
    font-size: 1rem;
    font-family: 'Poppins', sans-serif;
    outline: none;
    transition: border-color 0.3s, box-shadow 0.3s;
}

.checkout-form input:focus,
.checkout-form select:focus {
    border-color: var(--rose-gold);
    box-shadow: 0 0 8px rgba(216, 156, 138, 0.25);
}

.form-row {
    display: flex;
    gap: 15px;
}

.form-row .form-group {
    flex: 1;
}

.mobile-input-wrapper {
    display: flex;
    gap: 10px;
}

.region-select {
    width: 120px !important;
}

.checkout-summary {
    margin: 20px 0;
    padding-top: 15px;
    border-top: 1px solid rgba(216, 156, 138, 0.15);
    font-size: 1.2rem;
    text-align: right;
}

.btn-confirm {
    width: 100%;
    background: linear-gradient(135deg, var(--rose-gold), var(--primary-gold));
    color: white;
    border: none;
    padding: 15px;
    border-radius: 30px;
    font-size: 1.1rem;
    font-weight: bold;
    cursor: pointer;
    transition: transform 0.3s, box-shadow 0.3s;
    box-shadow: 0 6px 15px rgba(196, 138, 90, 0.3);
    margin-top: 10px;
}

.btn-confirm:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(196, 138, 90, 0.4);
}

/* --- Sliders & Navigation Styles --- */

.hidden-view {
    display: none !important;
}

.slider-section {
    margin-bottom: 60px;
}

.slider-container {
    display: flex;
    overflow-x: auto;
    gap: 20px;
    padding: 10px 0 30px 0;
    scroll-snap-type: x mandatory;
    scrollbar-width: thin;
    scrollbar-color: var(--muted-gold) rgba(216, 156, 138, 0.1);
}

.slider-container::-webkit-scrollbar {
    height: 6px;
}
.slider-container::-webkit-scrollbar-track {
    background: rgba(216, 156, 138, 0.1);
    border-radius: 4px;
}
.slider-container::-webkit-scrollbar-thumb {
    background: var(--muted-gold);
    border-radius: 4px;
}

.slider-card {
    flex: 0 0 300px;
    scroll-snap-align: start;
    background: rgba(255, 255, 255, 0.75);
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 6px 18px rgba(75, 46, 43, 0.08);
    cursor: pointer;
    transition: transform 0.3s, box-shadow 0.3s;
    border: 1px solid rgba(216, 156, 138, 0.1);
    display: flex;
    flex-direction: column;
}

.slider-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 30px rgba(196, 138, 90, 0.18);
    border-color: rgba(216, 156, 138, 0.3);
}

.slider-card-img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    transition: transform 0.3s;
}

.slider-card:hover .slider-card-img {
    transform: scale(1.05);
}

.slider-card-info {
    padding: 15px;
    text-align: center;
}

.slider-card-info h3 {
    color: var(--text-color);
    font-size: 1.15rem;
    margin-bottom: 5px;
    font-weight: 600;
}

.slider-card-info span {
    color: var(--primary-gold);
    font-weight: bold;
    font-size: 1.1rem;
}

.btn-back {
    background: rgba(216, 156, 138, 0.1);
    color: var(--text-color);
    border: 1px solid rgba(216, 156, 138, 0.25);
    padding: 10px 20px;
    border-radius: 25px;
    font-size: 1rem;
    cursor: pointer;
    margin-bottom: 20px;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
}

.btn-back:hover {
    background: var(--rose-gold);
    color: white;
    border-color: var(--rose-gold);
}


``n

## script.js


`$ext

document.addEventListener("DOMContentLoaded", () => {
    // 1. Loader Animation Timeout
    const loader = document.getElementById('loader');
    setTimeout(() => {
        loader.classList.add('hidden');
    }, 1000); // 1s simple animation

    // 3. Fetch products and render
    const productsGrid = document.getElementById('products-grid');
    let allProducts = [];

    const fetchProducts = async () => {
        try {
            const response = await fetch('api.php?action=get_products');
            const data = await response.json();
            if (data.success) {
                allProducts = data.products;
                if(typeof renderSliders === 'function') renderSliders();
                renderProducts(allProducts);
            } else {
                productsGrid.innerHTML = '<p>Error loading products...</p>';
            }
        } catch (error) {
            productsGrid.innerHTML = '<p>Could not connect to the store.</p>';
        }
    };

    const renderProducts = (products) => {
        productsGrid.innerHTML = '';
        if (products.length === 0) {
            productsGrid.innerHTML = '<p style="grid-column: 1/-1; text-align:center; font-size: 1.2rem;">No items found in this category.</p>';
            return;
        }

        products.forEach(product => {
            const card = document.createElement('div');
            card.className = 'product-card';

            const catClass = `badge-${product.category.toLowerCase()}`;

            card.innerHTML = `
                <img src="${product.image_path}" alt="${product.name}" class="product-img" onerror="this.src='https://placehold.co/280x250/1e293b/FFF?text=Image'">
                <div class="product-info">
                    <span class="badge-cat ${catClass}">${product.category}</span>
                    <h3 class="product-title">${product.name}</h3>
                    <p class="product-desc">${product.description}</p>
                    <div class="product-footer">
                        <span class="product-price">$${product.price}</span>
                        <button class="btn-buy" onclick="addToCart(${product.id})">Add to Cart</button>
                    </div>
                </div>
            `;
            productsGrid.appendChild(card);
        });
    };

    // --- NEW: Render Sliders ---
    const renderSliders = () => {
        const sliderChocolates = document.getElementById('slider-chocolates');
        const sliderCosmetics = document.getElementById('slider-cosmetics');
        const sliderNuts = document.getElementById('slider-nuts');

        if(sliderChocolates) sliderChocolates.innerHTML = '';
        if(sliderCosmetics) sliderCosmetics.innerHTML = '';
        if(sliderNuts) sliderNuts.innerHTML = '';

        allProducts.forEach(product => {
            const card = document.createElement('div');
            card.className = 'slider-card';
            card.onclick = () => openCategoryView(product.category);

            card.innerHTML = `
                <div style="overflow:hidden;"><img src="${product.image_path}" class="slider-card-img" onerror="this.src='https://placehold.co/280x250/1e293b/FFF?text=Image'"></div>
                <div class="slider-card-info">
                    <h3>${product.name}</h3>
                    <span>$${product.price}</span>
                </div>
            `;

            if (product.category === 'Chocolates' && sliderChocolates) sliderChocolates.appendChild(card);
            else if (product.category === 'Cosmetics' && sliderCosmetics) sliderCosmetics.appendChild(card);
            else if (product.category === 'Nuts' && sliderNuts) sliderNuts.appendChild(card);
        });
    };

    // --- NEW: View Switching Logic ---
    const homeView = document.getElementById('home-view');
    const categoryView = document.getElementById('category-view');
    const categoryTitle = document.getElementById('category-title');
    const btnBackHome = document.getElementById('btn-back-home');

    window.openCategoryView = (category) => {
        if(homeView && categoryView) {
            homeView.classList.add('hidden-view');
            categoryView.classList.remove('hidden-view');
        }
        
        if(categoryTitle) {
            categoryTitle.textContent = category === 'All' ? 'All Items' : category;
        }

        if (category === 'All') {
            renderProducts(allProducts);
        } else {
            const filtered = allProducts.filter(p => p.category === category);
            renderProducts(filtered);
        }
        // Scroll directly to the category-view element
        setTimeout(() => {
            const navbarHeight = document.querySelector('.navbar')?.offsetHeight || 80;
            const top = categoryView.getBoundingClientRect().top + window.pageYOffset - navbarHeight - 10;
            window.scrollTo({ top, behavior: 'smooth' });
        }, 50);
    };

    window.openHomeView = () => {
        if(homeView && categoryView) {
            categoryView.classList.add('hidden-view');
            homeView.classList.remove('hidden-view');
        }
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    if(btnBackHome) {
        btnBackHome.addEventListener('click', openHomeView);
    }

    // 4. Category filtering
    const navLinks = document.querySelectorAll('.nav-links a');
    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            const category = e.target.getAttribute('data-category');
            if (category) {
                e.preventDefault();
                openCategoryView(category);
            }
        });
    });

    // 5. Logo click to go home
    document.querySelector('.logo').addEventListener('click', () => {
        openHomeView();
    });

    // 6. SHOP NOW hero button
    const shopNowBtn = document.querySelector('.btn-shop-now');
    if (shopNowBtn) {
        shopNowBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const category = shopNowBtn.getAttribute('data-category');
            if (category) openCategoryView(category);
        });
    }

    // --- Cart Logic ---
    let cart = [];
    const cartBtn = document.getElementById('cart-btn');
    const cartSidebar = document.getElementById('cart-sidebar');
    const closeCartBtn = document.getElementById('close-cart');
    const cartBadge = document.getElementById('cart-badge');
    const cartItemsContainer = document.getElementById('cart-items');
    const cartTotalPrice = document.getElementById('cart-total-price');

    // Open/Close Cart
    cartBtn.addEventListener('click', () => {
        cartSidebar.classList.add('open');
    });

    closeCartBtn.addEventListener('click', () => {
        cartSidebar.classList.remove('open');
    });

    // Expose globally for inline onclick
    window.addToCart = (productId) => {
        const product = allProducts.find(p => p.id == productId);
        if (!product) return;

        const existingItem = cart.find(item => item.id == productId);
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({ ...product, quantity: 1 });
        }
        
        // Pulse animation
        cartBtn.classList.remove('pulse');
        void cartBtn.offsetWidth; // trigger reflow
        cartBtn.classList.add('pulse');
        
        updateCartUI();
    };

    window.changeQuantity = (productId, delta) => {
        const itemIndex = cart.findIndex(item => item.id == productId);
        if (itemIndex > -1) {
            cart[itemIndex].quantity += delta;
            if (cart[itemIndex].quantity <= 0) {
                cart.splice(itemIndex, 1);
            }
            updateCartUI();
        }
    };

    const updateCartUI = () => {
        // Update badge
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        cartBadge.textContent = totalItems;
        
        // Render items
        cartItemsContainer.innerHTML = '';
        let totalPrice = 0;

        if (cart.length === 0) {
            cartItemsContainer.innerHTML = '<p style="text-align:center; color: var(--dark-cream); margin-top: 20px;">Your cart is empty.</p>';
        } else {
            cart.forEach(item => {
                totalPrice += item.price * item.quantity;
                const itemEl = document.createElement('div');
                itemEl.className = 'cart-item';
                itemEl.innerHTML = `
                    <img src="${item.image_path}" alt="${item.name}" class="cart-item-img" onerror="this.src='https://placehold.co/60x60/1e293b/FFF?text=Img'">
                    <div class="cart-item-info">
                        <div class="cart-item-title">${item.name}</div>
                        <div class="cart-item-price">$${item.price}</div>
                        <div class="cart-item-controls">
                            <button class="qty-btn" onclick="changeQuantity(${item.id}, -1)">-</button>
                            <span class="cart-item-qty">${item.quantity}</span>
                            <button class="qty-btn" onclick="changeQuantity(${item.id}, 1)">+</button>
                        </div>
                    </div>
                `;
                cartItemsContainer.appendChild(itemEl);
            });
        }

        // Update total
        cartTotalPrice.textContent = '$' + totalPrice.toFixed(2);
        
        // Also update modal total if it exists
        const coTotal = document.getElementById('co-total-amount');
        if(coTotal) coTotal.textContent = '$' + totalPrice.toFixed(2);
    };

    // --- Checkout Logic ---
    const btnCheckout = document.getElementById('btn-checkout');
    const checkoutModal = document.getElementById('checkout-modal');
    const closeCheckoutBtn = document.getElementById('close-checkout');
    const checkoutForm = document.getElementById('checkout-form');

    if(btnCheckout) {
        btnCheckout.addEventListener('click', () => {
            if (cart.length === 0) {
                alert('Your cart is empty!');
                return;
            }
            if (typeof isLoggedIn !== 'undefined' && !isLoggedIn) {
                window.location.href = 'CustomerData/login.php';
                return;
            }
            cartSidebar.classList.remove('open');
            checkoutModal.classList.remove('hidden');
        });
    }

    if(closeCheckoutBtn) {
        closeCheckoutBtn.addEventListener('click', () => {
            checkoutModal.classList.add('hidden');
        });
    }

    if(checkoutForm) {
        checkoutForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (cart.length === 0) return;

            const submitBtn = checkoutForm.querySelector('.btn-confirm');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';

            const orderData = {
                full_name: document.getElementById('co-name').value,
                email: document.getElementById('co-email').value,
                mobile_number: document.getElementById('co-region').value + ' ' + document.getElementById('co-mobile').value,
                home_address: document.getElementById('co-address').value,
                home_town: document.getElementById('co-town').value,
                postal_code: document.getElementById('co-postal').value,
                cart_details: cart,
                total_amount: cart.reduce((sum, item) => sum + item.price * item.quantity, 0)
            };

            try {
                const response = await fetch('api.php?action=create_order', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(orderData)
                });
                const data = await response.json();
                
                if (data.success) {
                    alert('Order placed successfully!');
                    cart = [];
                    updateCartUI();
                    checkoutForm.reset();
                    checkoutModal.classList.add('hidden');
                } else {
                    alert('Failed to place order: ' + data.message);
                }
            } catch (err) {
                alert('An error occurred while placing the order.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Confirm Order';
            }
        });
    }
    
    // Initial UI render
    updateCartUI();

    // Initial fetch
    fetchProducts();
});


``n

## admin.php


`$ext

<?php
require_once 'db.php';

$message = '';
$order_message = '';
$active_tab = 'view-stock';
$active_sub_tab = 'pending';

if (isset($_GET['success'])) {
    if ($_GET['success'] === 'email') {
        $message = "Email template updated successfully.";
        $active_tab = 'view-email';
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

// Handle Add Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
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
        $stmt = $pdo->prepare("INSERT INTO products (name, description, category, price, image_path) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $description, $category, $price, $imagePath])) {
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
    <title>Admin Dashboard - à¶’ à¶»tin (E RATIN)</title>
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
                                    <a href="admin.php?delete=<?php echo $p['id']; ?>" class="btn-delete" onclick="confirmDeletion(event, this.href, 'product');">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($products)): ?>
                                <tr><td colspan="5">No products found.</td></tr>
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
                    <input type="text" id="orderSearchInput" placeholder="ðŸ” Search name, email, ID..." style="padding: 8px 15px; border-radius: 8px; border: 1px solid var(--glass-border); background: rgba(0,0,0,0.2); color: white; outline: none; min-width: 250px; font-family: inherit;">
                    <button id="toggleBinBtn" class="btn-view" style="padding: 6px 15px; border-radius: 8px; font-size: 0.9rem;">
                        ðŸ—‘ï¸ Bin (<?php echo count($deleted_orders); ?>)
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
                        this.innerHTML = 'ðŸ—‘ï¸ Bin (<?php echo count($deleted_orders); ?>)';
                        this.classList.remove('btn-submit');
                        this.classList.add('btn-view');
                        if (ordersTitle) ordersTitle.innerText = 'Customer Orders';
                    } else {
                        activeTable.classList.add('hidden');
                        if (subTabs) subTabs.style.display = 'none';
                        deletedTable.classList.remove('hidden');
                        this.innerHTML = 'â† Back to Active Orders';
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


``n

## admin.css


`$ext

@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap');

:root {
    --primary-pink: #ff3366;
    --dark-pink: #e6004c;
    --primary-purple: #8b5cf6;
    --bg-dark: #0a0a0f;
    --text-color: #f8fafc;
    --glass-bg: rgba(15, 23, 42, 0.6);
    --glass-border: rgba(255, 255, 255, 0.1);
    --glass-hover: rgba(255, 255, 255, 0.15);
}

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Outfit', sans-serif;
}

@keyframes gradientBG {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

body {
    background: linear-gradient(-45deg, #0f172a, #1e1b4b, #2e1065, #111827);
    background-size: 400% 400%;
    animation: gradientBG 15s ease infinite;
    color: var(--text-color);
    padding: 20px;
    min-height: 100vh;
}

/* Custom Scrollbar */
::-webkit-scrollbar { width: 8px; }
::-webkit-scrollbar-track { background: rgba(0,0,0,0.2); }
::-webkit-scrollbar-thumb { background: rgba(255, 51, 102, 0.5); border-radius: 4px; }
::-webkit-scrollbar-thumb:hover { background: var(--primary-pink); }

.admin-container {
    max-width: 1200px;
    margin: 0 auto;
}

/* --- Glassmorphism Headers & Cards --- */
.admin-header, .card {
    background: var(--glass-bg);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid var(--glass-border);
    border-radius: 16px;
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
}

.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 30px;
    margin-bottom: 30px;
}

.admin-header h2 {
    font-size: 1.8rem;
    background: linear-gradient(to right, #fff, #ffb6c1);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 15px;
}

.admin-brand-logo {
    height: 32px;
    width: 32px;
    border-radius: 50%;
    object-fit: cover;
    box-shadow: 0 0 8px rgba(255, 255, 255, 0.2);
    /* reset text fill for image just in case */
    -webkit-text-fill-color: initial;
}

.card {
    padding: 30px;
}

.card h3 {
    margin-bottom: 25px;
    color: #fff;
    border-bottom: 1px solid var(--glass-border);
    padding-bottom: 15px;
    font-size: 1.4rem;
    font-weight: 600;
}

/* --- Admin Navigation --- */
.admin-nav {
    display: flex;
    gap: 15px;
    background: rgba(0, 0, 0, 0.3);
    padding: 8px 16px;
    border-radius: 30px;
    border: 1px solid var(--glass-border);
}

.nav-link {
    color: #cbd5e1;
    text-decoration: none;
    padding: 8px 20px;
    border-radius: 20px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-weight: 600;
    font-size: 0.95rem;
}

.nav-link:hover {
    background: var(--glass-hover);
    color: #fff;
    transform: translateY(-1px);
}

.nav-link.active {
    background: linear-gradient(45deg, var(--primary-pink), var(--primary-purple));
    color: white;
    box-shadow: 0 4px 15px rgba(255, 51, 102, 0.4);
}

.hidden {
    display: none !important;
    opacity: 0;
}

.admin-view {
    animation: fadeIn 0.4s ease forwards;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* --- Buttons --- */
.btn-view, .btn-submit {
    text-decoration: none;
    padding: 12px 24px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    border: none;
    display: inline-block;
    text-align: center;
}

.btn-view {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    border: 1px solid var(--glass-border);
}

.btn-view:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.btn-submit {
    background: linear-gradient(45deg, var(--primary-pink), var(--primary-purple));
    color: white;
    width: 100%;
    box-shadow: 0 4px 15px rgba(255, 51, 102, 0.3);
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 51, 102, 0.5);
}

.btn-delete {
    background: rgba(231, 76, 60, 0.1);
    color: #ff6b6b;
    border: 1px solid rgba(231, 76, 60, 0.3);
    text-decoration: none;
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-block;
}

.btn-delete:hover {
    background: #e74c3c;
    color: #fff;
    border-color: #e74c3c;
    box-shadow: 0 0 10px rgba(231, 76, 60, 0.4);
}

.btn-ongoing {
    background: linear-gradient(45deg, #d97706, #fbbf24);
    color: #fff;
    border: none;
    text-decoration: none;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    display: inline-block;
    box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3);
}

.btn-ongoing:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(245, 158, 11, 0.6);
}

.btn-closed {
    background: linear-gradient(45deg, #059669, #34d399);
    color: #fff;
    border: none;
    text-decoration: none;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    display: inline-block;
    box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
}

.btn-closed:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(16, 185, 129, 0.6);
}

.btn-return {
    background: rgba(255, 255, 255, 0.05);
    color: #cbd5e1;
    border: 1px solid rgba(255, 255, 255, 0.2);
    text-decoration: none;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 700;
    transition: all 0.3s ease;
    display: inline-block;
    text-align: center;
}

.btn-return:hover {
    background: rgba(255, 255, 255, 0.15);
    color: #fff;
    border-color: rgba(255, 255, 255, 0.4);
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
}

/* --- Alerts --- */
.alert {
    background: rgba(16, 185, 129, 0.1);
    color: #34d399;
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    border: 1px solid rgba(16, 185, 129, 0.3);
    font-weight: 600;
    backdrop-filter: blur(4px);
    animation: fadeIn 0.5s ease;
}

/* --- Layout Grids --- */
.admin-grid {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 30px;
}

/* --- Forms --- */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #94a3b8;
    font-size: 0.9rem;
}

.form-group input, 
.form-group select, 
.form-group textarea {
    width: 100%;
    padding: 12px 16px;
    background: rgba(0, 0, 0, 0.2);
    border: 1px solid var(--glass-border);
    border-radius: 10px;
    font-family: inherit;
    color: #fff;
    transition: all 0.3s ease;
}

.form-group select option {
    background: #1e293b;
    color: #fff;
}

.form-group input:focus, 
.form-group select:focus, 
.form-group textarea:focus {
    outline: none;
    border-color: var(--primary-pink);
    background: rgba(0, 0, 0, 0.4);
    box-shadow: 0 0 0 3px rgba(255, 51, 102, 0.2);
}

/* --- Tables --- */
.table-responsive {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 16px;
    text-align: left;
    border-bottom: 1px solid var(--glass-border);
}

th {
    background-color: rgba(0, 0, 0, 0.2);
    color: #94a3b8;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 1px;
}

tr {
    transition: background 0.3s;
}

tr:hover {
    background: rgba(255, 255, 255, 0.03);
}

td {
    color: #e2e8f0;
    vertical-align: middle;
}

td img {
    border-radius: 10px;
    object-fit: cover;
    width: 60px;
    height: 60px;
    border: 2px solid rgba(255,255,255,0.1);
}

/* --- Badges --- */
.badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.badge.chocolates { background: linear-gradient(45deg, #6d4c41, #8d6e63); color: #fff; }
.badge.cosmetics { background: linear-gradient(45deg, #ff4081, #ff79b0); color: #fff; }
.badge.nuts { background: linear-gradient(45deg, #558b2f, #8bc34a); color: #fff; }

.badge.pending { background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.4); }
.badge.ongoing { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); }
.badge.closed { background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); }

/* --- Gallery Grid --- */
.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 25px;
    margin-top: 10px;
}

.gallery-item {
    background: rgba(0, 0, 0, 0.2);
    border: 1px solid var(--glass-border);
    border-radius: 16px;
    overflow: hidden;
    text-align: center;
    transition: all 0.3s ease;
}

.gallery-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.3);
    border-color: rgba(255, 51, 102, 0.3);
}

.gallery-item img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-bottom: 1px solid var(--glass-border);
}

.gallery-info {
    padding: 15px;
    font-size: 1rem;
    font-weight: 600;
    color: #e2e8f0;
}

/* --- Responsive --- */
@media (max-width: 900px) {
    .admin-grid {
        grid-template-columns: 1fr;
    }
    .admin-header {
        flex-direction: column;
        gap: 20px;
    }
    .admin-nav {
        flex-wrap: wrap;
        justify-content: center;
    }
}

/* --- Custom Modals --- */
.modal-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(8px);
    display: flex; align-items: center; justify-content: center; z-index: 1000;
    opacity: 0; pointer-events: none; transition: opacity 0.3s ease;
}
.modal-overlay.show { opacity: 1; pointer-events: all; }
.modal-content {
    background: var(--glass-bg); border: 1px solid var(--glass-border);
    padding: 35px; border-radius: 16px; text-align: center; max-width: 420px; width: 90%;
    transform: translateY(20px); transition: transform 0.3s ease;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
}
.modal-overlay.show .modal-content { transform: translateY(0); }
.modal-content h3 { color: #fff; margin-bottom: 15px; font-size: 1.5rem; }
.modal-content p { color: #cbd5e1; margin-bottom: 25px; line-height: 1.5; }
.modal-actions { display: flex; justify-content: center; gap: 15px; }
.btn-cancel {
    background: rgba(255, 255, 255, 0.1); color: #fff; border: 1px solid var(--glass-border);
    padding: 10px 20px; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; text-decoration: none; display: inline-block;
}
.btn-cancel:hover { background: rgba(255, 255, 255, 0.2); }


``n

## api.php


`$ext

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
            
            // Fetch template and SMTP config from DB
            $stmt_settings = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('email_subject', 'email_body', 'smtp_username', 'smtp_password')");
            $settings_rows = $stmt_settings->fetchAll(PDO::FETCH_KEY_PAIR);
            $db_subject = $settings_rows['email_subject'] ?? 'Order Confirmation - à¶’ à¶»tin (E RATIN)';
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

                    // Content
                    $mail->isHTML(false);
                    $mail->Subject = $final_subject;
                    $mail->Body    = $final_body;

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


``n

## setup.php


`$ext

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
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $pdo->exec($sql);
    
    // Create customers table
    $sql_customers = "CREATE TABLE IF NOT EXISTS customers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql_customers);

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
    $default_subject = "Order Confirmation - à¶’ à¶»tin (E RATIN)";
    $default_body = "Hello {customer_name},\n\nThank you for joining us and shopping at à¶’ à¶»tin (E RATIN)!\n\nWe have successfully received your order on {order_date}.\nTotal Amount: \${total_amount}\n\nYour order is being processed and is expected to be shipped around {send_date}.\n\nIf you have any questions, feel free to reply to this email.\n\nBest Regards,\nE RATIN Team";
    
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


``n

## db.php


`$ext

<?php
$host = 'localhost';
$user = 'root';
$pass = ''; // Default XAMPP password is empty
$db   = 'product_seller_db';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => true, // Using true to prevent some XAMPP MySQL disconnect issues
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Database connection failed. Please run setup.php first! Error: " . $e->getMessage());
}
?>


``n

## CustomerData\login.php


`$ext

<?php
session_start();
require_once '../db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login_submit'])) {
        $email = trim($_POST['login_email']);
        $password = $_POST['login_password'];

        $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        $customer = $stmt->fetch();

        if ($customer && password_verify($password, $customer['password'])) {
            $_SESSION['customer_id'] = $customer['id'];
            $_SESSION['customer_name'] = $customer['name'];
            header("Location: ../index.php");
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    } elseif (isset($_POST['register_submit'])) {
        $name = trim($_POST['reg_name']);
        $email = trim($_POST['reg_email']);
        $password = $_POST['reg_password'];

        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email is already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO customers (name, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$name, $email, $hashedPassword])) {
                $_SESSION['customer_id'] = $pdo->lastInsertId();
                $_SESSION['customer_name'] = $name;
                header("Location: ../index.php");
                exit;
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}

$showAlert = ($_SERVER['REQUEST_METHOD'] !== 'POST' && empty($error));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Authentication - à¶’ à¶»tin (E RATIN)</title>
    <link rel="stylesheet" href="auth.css">
    <style>
        .error-message {
            color: #ef4444;
            background: #fee2e2;
            border: 1px solid #f87171;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<?php if ($showAlert): ?>
<style>
    @keyframes fadeInZoom {
        0% { opacity: 0; transform: scale(1); }
        28% { opacity: 1; } /* 2 seconds (28% of 7s) to fully fade in */
        100% { opacity: 1; transform: scale(1.03); }
    }
</style>
<div id="imageAlertOverlay" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: #fffbf5; z-index: 9999; display: flex; justify-content: center; align-items: center; transition: opacity 2s ease; overflow: hidden;">
    <img src="alertpage.png" alt="Welcome Alert" style="width: 100vw; height: 100vh; object-fit: cover; animation: fadeInZoom 7s linear forwards;">
</div>
<?php endif; ?>

<div class="auth-container">
    <div class="auth-card" id="authCard">
        <a href="../index.php"><img src="../logo.jpg" alt="Logo" class="auth-logo"></a>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Login Form -->
        <div id="loginFormContainer" <?php echo (isset($_POST['register_submit']) && !empty($error)) ? 'class="hidden-form" style="display:none;"' : ''; ?>>
            <h2 class="auth-title">Welcome Back</h2>
            <p class="auth-subtitle">Login to access your account</p>
            
            <form action="" method="POST">
                <div class="form-group">
                    <input type="email" name="login_email" class="form-control" placeholder="Email Address" required value="<?php echo isset($_POST['login_email']) ? htmlspecialchars($_POST['login_email']) : ''; ?>">
                </div>
                <div class="form-group">
                    <input type="password" name="login_password" class="form-control" placeholder="Password" required>
                </div>
                <button type="submit" name="login_submit" class="btn-primary">Login</button>
            </form>
            
            <p class="toggle-text">Don't have an account? <span class="toggle-link" onclick="toggleForms()">Register Here</span></p>
        </div>

        <!-- Registration Form -->
        <div id="registerFormContainer" <?php echo (isset($_POST['register_submit']) && !empty($error)) ? '' : 'class="hidden-form" style="display:none;"'; ?>>
            <h2 class="auth-title">Create Account</h2>
            <p class="auth-subtitle">Join us to start shopping</p>
            
            <form action="" method="POST">
                <div class="form-group">
                    <input type="text" name="reg_name" class="form-control" placeholder="Full Name" required value="<?php echo isset($_POST['reg_name']) ? htmlspecialchars($_POST['reg_name']) : ''; ?>">
                </div>
                <div class="form-group">
                    <input type="email" name="reg_email" class="form-control" placeholder="Email Address" required value="<?php echo isset($_POST['reg_email']) ? htmlspecialchars($_POST['reg_email']) : ''; ?>">
                </div>
                <div class="form-group">
                    <input type="password" name="reg_password" class="form-control" placeholder="Password" required>
                </div>
                <button type="submit" name="register_submit" class="btn-primary">Create Account</button>
            </form>
            
            <p class="toggle-text">Already have an account? <span class="toggle-link" onclick="toggleForms()">Login Here</span></p>
        </div>
    </div>
</div>

<script>
    function toggleForms() {
        const loginForm = document.getElementById('loginFormContainer');
        const registerForm = document.getElementById('registerFormContainer');
        const authCard = document.getElementById('authCard');
        const errorMsg = document.querySelector('.error-message');

        if (errorMsg) errorMsg.style.display = 'none';

        // Add a small bounce animation to the card when toggling
        authCard.style.transform = 'scale(0.95)';
        setTimeout(() => {
            authCard.style.transform = 'scale(1)';
        }, 150);

        if (loginForm.classList.contains('hidden-form')) {
            loginForm.style.display = 'block';
            setTimeout(() => { loginForm.classList.remove('hidden-form'); }, 10);
            
            registerForm.classList.add('hidden-form');
            setTimeout(() => { registerForm.style.display = 'none'; }, 400);
        } else {
            registerForm.style.display = 'block';
            setTimeout(() => { registerForm.classList.remove('hidden-form'); }, 10);
            
            loginForm.classList.add('hidden-form');
            setTimeout(() => { loginForm.style.display = 'none'; }, 400);
        }
    }

    <?php if ($showAlert): ?>
    document.addEventListener("DOMContentLoaded", () => {
        const authCard = document.getElementById('authCard');
        authCard.style.opacity = '0'; // Hide login form initially
        
        setTimeout(() => {
            const alertBox = document.getElementById('imageAlertOverlay');
            if(alertBox) {
                alertBox.style.opacity = '0';
                setTimeout(() => alertBox.remove(), 2000);
            }
            authCard.style.transition = 'opacity 2s ease';
            authCard.style.opacity = '1';
        }, 7000);
    });
    <?php endif; ?>
</script>

</body>
</html>


``n

## CustomerData\auth.css


`$ext

@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap');

:root {
    --primary-pink: #ff3366;
    --primary-purple: #8b5cf6;
    --bg-dark: #0f172a;
    --text-color: #f8fafc;
    --glass-bg: rgba(15, 23, 42, 0.6);
    --glass-border: rgba(255, 255, 255, 0.1);
    --input-bg: rgba(0, 0, 0, 0.2);
}

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Outfit', sans-serif;
}

@keyframes gradientBG {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

body {
    background: linear-gradient(-45deg, #0f172a, #1e1b4b, #2e1065, #111827);
    background-size: 400% 400%;
    animation: gradientBG 15s ease infinite;
    color: var(--text-color);
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

.auth-container {
    width: 100%;
    max-width: 450px;
    position: relative;
    perspective: 1000px;
}

.auth-card {
    background: var(--glass-bg);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--glass-border);
    border-radius: 24px;
    padding: 40px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
    text-align: center;
    transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.auth-logo {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid rgba(255, 255, 255, 0.2);
    margin-bottom: 20px;
    box-shadow: 0 0 20px rgba(255, 51, 102, 0.3);
}

.auth-title {
    font-size: 2rem;
    font-weight: 800;
    margin-bottom: 5px;
    background: linear-gradient(to right, #fff, #ffb6c1);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}

.auth-subtitle {
    font-size: 0.95rem;
    color: #94a3b8;
    margin-bottom: 30px;
}

.form-group {
    position: relative;
    margin-bottom: 25px;
    text-align: left;
}

.form-control {
    width: 100%;
    padding: 14px 16px;
    background: var(--input-bg);
    border: 1px solid var(--glass-border);
    border-radius: 12px;
    color: #fff;
    font-size: 1rem;
    transition: all 0.3s ease;
    outline: none;
}

.form-control:focus {
    border-color: var(--primary-pink);
    box-shadow: 0 0 10px rgba(255, 51, 102, 0.2);
    background: rgba(0, 0, 0, 0.4);
}

.form-control::placeholder {
    color: #64748b;
}

.btn-primary {
    width: 100%;
    padding: 14px;
    background: linear-gradient(45deg, #e4b89a, #c48a5a);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(196, 138, 90, 0.3);
    margin-bottom: 20px;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(196, 138, 90, 0.5);
}

.toggle-text {
    font-size: 0.95rem;
    color: #94a3b8;
}

.toggle-link {
    color: #ff3366;
    text-decoration: none;
    font-weight: 600;
    cursor: pointer;
    transition: color 0.3s ease;
}

.toggle-link:hover {
    color: #ff80a0;
    text-decoration: underline;
}

.hidden-form {
    display: none;
    opacity: 0;
    animation: fadeIn 0.4s ease forwards;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}






``n

## CustomerData\test.php


`$ext

<!-- ALERT OVERLAY -->
<div id="welcomeAlert" class="welcome-alert">

    <div class="alert-box">

        <h1 class="title">
            Welcome to <span>Eratin</span>
        </h1>

        <p class="desc">
            To get your Order Summary and see your purchased history,<br>
            <strong>Register with Eratin</strong> for the best customer service.
        </p>

        <p class="sub-desc">
            Discover handpicked treats and beauty essentials for your most beautiful moments.
        </p>

        <!-- Countdown -->
        <div class="redirect-box">
            <div class="lock">ðŸ”’</div>

            <div class="text">
                Redirecting to Login / Registration...<br>
                <small>You will be redirected automatically in <span id="count">5</span> seconds.</small>
            </div>

            <div class="circle">
                <span id="countCircle">5</span>
            </div>
        </div>

    </div>
</div>

<script>
let count = 5;

let timer = setInterval(() => {
    count--;
    document.getElementById("count").innerText = count;
    document.getElementById("countCircle").innerText = count;

    if (count <= 0) {
        clearInterval(timer);
        window.location.href = "login.php"; // change your page here
    }
}, 1000);
</script>

``n

## CustomerData\test.css


`$ext

.welcome-alert{
    position: fixed;
    top:0;
    left:0;
    width:100%;
    height:100vh;
    background: url('alertImage.png') center/cover no-repeat;
    background-color: #fff1e6;
    display:flex;
    justify-content:center;
    align-items:center;
    z-index:9999;
    font-family: Arial, sans-serif;
}

.alert-box{
    width: 60%;
    background: rgba(255,255,255,0.85);
    padding: 40px;
    border-radius: 25px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    text-align:center;
    position: relative;
}

.title{
    font-size: 36px;
    color:#333;
}

.title span{
    color:#c07a4a;
    font-weight:bold;
}

.desc{
    margin-top:15px;
    font-size:16px;
    color:#444;
    line-height:1.5;
}

.sub-desc{
    margin-top:15px;
    font-size:14px;
    color:#777;
}

.redirect-box{
    margin-top:30px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    background:#fff;
    padding:20px;
    border-radius:15px;
    box-shadow:0 5px 20px rgba(0,0,0,0.08);
}

.lock{
    font-size:30px;
}

.text{
    text-align:left;
    font-size:14px;
    color:#333;
}

.circle{
    width:50px;
    height:50px;
    border-radius:50%;
    border:3px solid #c07a4a;
    display:flex;
    justify-content:center;
    align-items:center;
    font-weight:bold;
    color:#c07a4a;
}

``n

