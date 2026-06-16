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
    <title>ඒ රtin (E RATIN) - Treats, Beauty, Moments</title>
    <link rel="stylesheet" href="style.css?v=11">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Overlay Animation -->
    <div id="loader" class="loader">
        <div class="spinner"></div>
        <h2>Welcome to ඒ රtin...</h2>
    </div>

    <!-- Order Success Modal -->
    <div id="order-success-modal" class="modal-overlay hidden" style="z-index: 99999;">
        <div class="checkout-modal-content" style="text-align: center; max-width: 450px; padding: 40px; border: 2px solid var(--primary-gold); background: var(--bg-dark); color: white; position: relative;">
            <div class="success-icon" style="font-size: 4rem; margin-bottom: 20px;">🎉</div>
            <h2 style="color: var(--primary-gold); margin-bottom: 15px;">Order Placed!</h2>
            <p style="font-size: 1.2rem; line-height: 1.6; color: var(--light-text); margin-bottom: 30px;">
                Ready your <span style="color: var(--primary-pink); font-weight: 800; text-shadow: 0 0 10px rgba(255, 51, 102, 0.3);">Order Status</span>,<br> 
                View your <span class="highlight-profile">Beautiful Profile</span>
            </p>
            
            <div style="position: relative; display: inline-block;">
                <a href="CustomerData/profile.php" class="btn-confirm pulse-button" style="display: flex; align-items: center; gap: 15px; padding: 18px 35px; text-decoration: none; font-size: 1.2rem; background: var(--primary-gold); border-radius: 50px; box-shadow: 0 10px 25px rgba(196, 138, 90, 0.4); color: white; font-weight: bold;">
                    Check Profile <span class="bounce-arrow">➔</span>
                </a>
            </div>
            <button id="btn-close-success" style="display: block; margin: 20px auto 0; background: none; border: none; color: #94a3b8; text-decoration: underline; cursor: pointer;">Stay on Shop</button>
        </div>
    </div>

    <style>
        /* Ensure Success Modal is visible when hidden is removed */
        #order-success-modal:not(.hidden) {
            display: flex !important;
            opacity: 1 !important;
            pointer-events: all !important;
        }
        .highlight-profile {
            background: linear-gradient(120deg, #f6d365 0%, #fda085 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 800;
            font-size: 1.5rem;
            filter: drop-shadow(0 0 5px rgba(246, 211, 101, 0.5));
        }
        @keyframes bounceRight {
            0%, 100% { transform: translateX(0); }
            50% { transform: translateX(8px); }
        }
        .bounce-arrow {
            display: inline-block;
            animation: bounceRight 0.8s infinite;
            font-size: 1.4rem;
        }
        @keyframes pulseGlow {
            0% { box-shadow: 0 0 0 0 rgba(196, 138, 90, 0.7); }
            70% { box-shadow: 0 0 0 15px rgba(196, 138, 90, 0); }
            100% { box-shadow: 0 0 0 0 rgba(196, 138, 90, 0); }
        }
        .pulse-button {
            animation: pulseGlow 2s infinite;
        }
        #order-success-modal .checkout-modal-content {
            animation: popIn 0.5s cubic-bezier(0.26, 0.53, 0.74, 1.48);
        }
        @keyframes popIn {
            0% { opacity: 0; transform: scale(0.5); }
            100% { opacity: 1; transform: scale(1); }
        }
    </style>

    <header class="main-header">
        <nav class="navbar">
            <div class="logo"><img src="logo.jpg" alt="Logo" class="brand-logo"> <span class="brand-name">ඒ රtin</span></div>
            <ul class="nav-links">
                <li><a href="#all" data-category="All">All Items</a></li>
                <li><a href="#chocolates" data-category="Chocolates">Chocolates</a></li>
                <li><a href="#cosmetics" data-category="Cosmetics">Cosmetics</a></li>
                <li><a href="#nuts" data-category="Nuts">Nuts</a></li>
                <?php if (isset($_SESSION['customer_id'])): ?>
                    <li>
                        <a href="CustomerData/profile.php" class="profile-icon-link" title="Your Profile">
                            <?php 
                                $avatar_url = !empty($_SESSION['profile_image']) ? 'CustomerData/' . htmlspecialchars($_SESSION['profile_image']) : 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['customer_name'] ?? 'User') . '&background=c48a5a&color=fff&rounded=true';
                            ?>
                            <img src="<?php echo $avatar_url; ?>" class="profile-icon" alt="Profile">
                        </a>
                    </li>
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
                    <a href="#all" class="btn-shop-now" data-category="All">SHOP NOW →</a>
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

            <!-- All Reviews Panel -->
            <section class="home-reviews-panel" id="home-reviews-panel">
                <div class="section-title">
                    <h2>What Our Customers Say</h2>
                    <p class="home-reviews-sub">Real experiences from our happy shoppers ♥</p>
                </div>
                <div class="home-reviews-carousel-wrapper">
                    <div class="home-reviews-track" id="home-reviews-track">
                        <!-- injected by JS -->
                    </div>
                    <div class="home-reviews-dots" id="home-reviews-dots"></div>
                    <p class="home-reviews-empty" id="home-reviews-empty" style="display:none;">
                        No reviews yet — be the first to share your experience!
                    </p>
                </div>
            </section>
        </div>

        <!-- Category View: Products Grid -->
        <div id="category-view" class="hidden-view">
            <button id="btn-back-home" class="btn-back">← Back to Categories</button>
            <section class="products-section">
                <div class="section-title">
                    <h2 id="category-title">All Items</h2>
                </div>
                
                <div id="products-grid" class="products-grid">
                    <!-- Products will be dynamically loaded here via JS -->
                </div>
            </section>

            <!-- Customer Reviews Section -->
            <section class="reviews-section" id="reviews-section">
                <div class="section-title">
                    <h2>Customer Reviews</h2>
                </div>

                <!-- Write a Review Form -->
                <?php if (isset($_SESSION['customer_id'])): ?>
                <div class="review-form-card">
                    <h3 class="review-form-title">Share Your Experience ✨</h3>
                    <form id="review-form" class="review-form">
                        <div class="review-form-row">
                            <div class="review-form-group">
                                <label for="review-name">Your Name</label>
                                <input type="text" id="review-name" placeholder="e.g. Anjali Perera" required maxlength="80" value="<?php echo htmlspecialchars($_SESSION['customer_name'] ?? ''); ?>" readonly>
                            </div>
                            <div class="review-form-group review-stars-group">
                                <label>Rating</label>
                                <div class="star-rating" id="star-rating">
                                    <span class="star" data-val="1">★</span>
                                    <span class="star" data-val="2">★</span>
                                    <span class="star" data-val="3">★</span>
                                    <span class="star" data-val="4">★</span>
                                    <span class="star" data-val="5">★</span>
                                </div>
                                <input type="hidden" id="review-rating" value="0">
                            </div>
                        </div>
                        <div class="review-form-group">
                            <label for="review-text">Your Review</label>
                            <textarea id="review-text" placeholder="Tell us about your experience with this product..." required maxlength="500" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn-submit-review" id="btn-submit-review">Post Review</button>
                    </form>
                </div>
                <?php else: ?>
                <div class="review-form-card" style="text-align: center; padding: 40px;">
                    <p style="font-size: 1.1rem; color: var(--light-text);">Please <a href="CustomerData/login.php" style="color: var(--primary-gold); font-weight: bold; text-decoration: underline;">Sign In</a> to share your review for this category.</p>
                </div>
                <?php endif; ?>

                <!-- Review Carousel Panel -->
                <div class="review-carousel-wrapper" id="review-carousel-wrapper">
                    <div class="review-carousel-track" id="review-carousel-track">
                        <!-- Review cards injected by JS -->
                    </div>
                    <div class="review-carousel-dots" id="review-carousel-dots"></div>
                    <p class="review-empty-msg" id="review-empty-msg" style="display:none;">No reviews yet. Be the first to share your experience!</p>
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

    <!-- Stock Alert Modal -->
    <div id="stock-alert-modal" class="modal-overlay hidden">
        <div class="checkout-modal-content" style="text-align: center; max-width: 400px; padding: 30px;">
            <h2 style="color: var(--primary-pink); margin-bottom: 20px;">Stock Limit Reached</h2>
            <p id="stock-alert-text" style="margin-bottom: 25px; line-height: 1.6;"></p>
            <div style="display: flex; gap: 15px; justify-content: center;">
                <button id="btn-stock-cancel" class="btn-cancel" style="padding: 10px 20px; border-radius: 8px; flex: 1; background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); cursor: pointer;">Not OK</button>
                <button id="btn-stock-ok" class="btn-confirm" style="padding: 10px 20px; border-radius: 8px; flex: 1; background: var(--primary-gold); color: white; border: none; cursor: pointer; font-weight: 600;">OK</button>
            </div>
        </div>
    </div>

    <footer>
        <p>ඒ රtin (E RATIN) &copy; 2026 | <a href="admin.php" style="color:var(--dark-gold); text-decoration:none;">Admin Access</a></p>
    </footer>

    <script>
        const isLoggedIn = <?php echo isset($_SESSION['customer_id']) ? 'true' : 'false'; ?>;
    </script>
    <script src="script.js?v=11"></script>
</body>
</html>
