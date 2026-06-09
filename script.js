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

            const isSoldOut = parseInt(product.stock) <= 0;
            const buttonHtml = isSoldOut 
                ? `<button class="btn-buy" style="background: #334155; color: #94a3b8; cursor: not-allowed;" disabled>Sold Out</button>` 
                : `<button class="btn-buy" onclick="addToCart(${product.id})">Add to Cart</button>`;

            card.innerHTML = `
                <img src="${product.image_path}" alt="${product.name}" class="product-img" onerror="this.src='https://placehold.co/280x250/1e293b/FFF?text=Image'">
                <div class="product-info">
                    <span class="badge-cat ${catClass}">${product.category}</span>
                    <h3 class="product-title">${product.name}</h3>
                    <p class="product-desc">${product.description}</p>
                    <div class="product-footer">
                        <span class="product-price">$${product.price}</span>
                        ${buttonHtml}
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
    let cart = JSON.parse(localStorage.getItem('eratincart')) || [];
    
    const saveCart = () => {
        localStorage.setItem('eratincart', JSON.stringify(cart));
    };
    const cartBtn = document.getElementById('cart-btn');
    const cartSidebar = document.getElementById('cart-sidebar');
    const closeCartBtn = document.getElementById('close-cart');
    const cartBadge = document.getElementById('cart-badge');
    const cartItemsContainer = document.getElementById('cart-items');
    const cartTotalPrice = document.getElementById('cart-total-price');

    // Stock alert elements
    const stockAlertModal = document.getElementById('stock-alert-modal');
    const stockAlertText = document.getElementById('stock-alert-text');
    const btnStockOk = document.getElementById('btn-stock-ok');
    const btnStockCancel = document.getElementById('btn-stock-cancel');
    let pendingStockItem = null;

    if (btnStockOk) {
        btnStockOk.addEventListener('click', () => {
            if (pendingStockItem) {
                const { product, maxStock } = pendingStockItem;
                const existingItem = cart.find(item => item.id == product.id);
                if (existingItem) {
                    existingItem.quantity = maxStock;
                } else {
                    cart.push({ ...product, quantity: maxStock });
                }
                saveCart();
                updateCartUI();
            }
            stockAlertModal.classList.add('hidden');
            pendingStockItem = null;
        });
    }

    if (btnStockCancel) {
        btnStockCancel.addEventListener('click', () => {
            cart = [];
            saveCart();
            updateCartUI();
            cartSidebar.classList.remove('open');
            stockAlertModal.classList.add('hidden');
            pendingStockItem = null;
            openCategoryView('All');
        });
    }

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

        const maxStock = parseInt(product.stock) || 0;
        const existingItem = cart.find(item => item.id == productId);
        const currentQty = existingItem ? existingItem.quantity : 0;

        if (currentQty + 1 > maxStock) {
            stockAlertText.textContent = `Only ${maxStock} available. You can buy ${maxStock} ${product.name}. Are you ok with it or not?`;
            pendingStockItem = { product, maxStock };
            stockAlertModal.classList.remove('hidden');
            return;
        }

        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({ ...product, quantity: 1 });
        }
        
        saveCart();
        
        // Pulse animation
        cartBtn.classList.remove('pulse');
        void cartBtn.offsetWidth; // trigger reflow
        cartBtn.classList.add('pulse');
        
        updateCartUI();
        showToast(`${product.name} added to cart!`);
    };

    window.changeQuantity = (productId, delta) => {
        const itemIndex = cart.findIndex(item => item.id == productId);
        if (itemIndex > -1) {
            const product = allProducts.find(p => p.id == productId);
            const maxStock = parseInt(product.stock) || 0;
            const newQty = cart[itemIndex].quantity + delta;

            if (delta > 0 && newQty > maxStock) {
                stockAlertText.textContent = `Only ${maxStock} available. You can buy ${maxStock} ${product.name}. Are you ok with it or not?`;
                pendingStockItem = { product, maxStock };
                stockAlertModal.classList.remove('hidden');
                return;
            }

            cart[itemIndex].quantity = newQty;
            if (cart[itemIndex].quantity <= 0) {
                cart.splice(itemIndex, 1);
            }
            saveCart();
            updateCartUI();
        }
    };

    window.removeFromCart = (productId) => {
        cart = cart.filter(item => item.id != productId);
        saveCart();
        updateCartUI();
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
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div class="cart-item-title">${item.name}</div>
                            <button class="remove-item-btn" onclick="removeFromCart(${item.id})" title="Remove item">&times;</button>
                        </div>
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
                    saveCart();
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

    // --- Toast Notification System ---
    const toastContainer = document.createElement('div');
    toastContainer.className = 'toast-container';
    document.body.appendChild(toastContainer);

    window.showToast = (message) => {
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.innerHTML = `<span class="toast-icon">✓</span> <span>${message}</span>`;
        toastContainer.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 10);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 400);
        }, 3000);
    };

    // =============================================
    // --- Customer Reviews System ---
    // =============================================
    let currentReviewCategory = '';
    let reviewCarouselIndex = 0;
    let reviewCarouselTimer = null;
    let reviewsData = [];

    const reviewCarouselTrack = document.getElementById('review-carousel-track');
    const reviewCarouselDots  = document.getElementById('review-carousel-dots');
    const reviewEmptyMsg      = document.getElementById('review-empty-msg');
    const reviewForm          = document.getElementById('review-form');
    const starRatingEl        = document.getElementById('star-rating');
    const reviewRatingInput   = document.getElementById('review-rating');

    // --- Star Rating Interaction ---
    if (starRatingEl) {
        const stars = starRatingEl.querySelectorAll('.star');
        stars.forEach(star => {
            star.addEventListener('mouseover', () => {
                const val = parseInt(star.dataset.val);
                stars.forEach(s => s.classList.toggle('hovered', parseInt(s.dataset.val) <= val));
            });
            star.addEventListener('mouseout', () => {
                stars.forEach(s => s.classList.remove('hovered'));
            });
            star.addEventListener('click', () => {
                const val = parseInt(star.dataset.val);
                reviewRatingInput.value = val;
                stars.forEach(s => s.classList.toggle('selected', parseInt(s.dataset.val) <= val));
            });
        });
    }

    // --- Load Reviews for a category ---
    const loadReviews = async (category) => {
        currentReviewCategory = category;
        reviewCarouselIndex = 0;
        clearInterval(reviewCarouselTimer);
        if (reviewCarouselTrack) reviewCarouselTrack.innerHTML = '';
        if (reviewCarouselDots)  reviewCarouselDots.innerHTML  = '';
        if (reviewEmptyMsg) reviewEmptyMsg.style.display = 'none';

        try {
            const res  = await fetch(`api.php?action=get_reviews&category=${encodeURIComponent(category)}`);
            const data = await res.json();
            if (data.success) {
                reviewsData = data.reviews;
                renderReviewCarousel(reviewsData);
            }
        } catch (e) {
            console.error('Could not load reviews', e);
        }
    };

    // --- Render Review Carousel ---
    const renderReviewCarousel = (reviews) => {
        if (!reviewCarouselTrack || !reviewCarouselDots) return;
        reviewCarouselTrack.innerHTML = '';
        reviewCarouselDots.innerHTML  = '';

        if (reviews.length === 0) {
            if (reviewEmptyMsg) reviewEmptyMsg.style.display = 'block';
            return;
        }
        if (reviewEmptyMsg) reviewEmptyMsg.style.display = 'none';

        reviews.forEach((r, i) => {
            // Card
            const card = document.createElement('div');
            card.className = 'review-card';
            const stars = '★'.repeat(r.rating) + '☆'.repeat(5 - r.rating);
            const date  = new Date(r.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            card.innerHTML = `
                <div class="review-card-header">
                    <div class="review-avatar">${r.customer_name.charAt(0).toUpperCase()}</div>
                    <div>
                        <div class="review-author">${escapeHtml(r.customer_name)}</div>
                        <div class="review-date">${date}</div>
                    </div>
                    <div class="review-stars-display">${stars}</div>
                </div>
                <p class="review-body">${escapeHtml(r.review_text)}</p>
            `;
            reviewCarouselTrack.appendChild(card);

            // Dot
            const dot = document.createElement('span');
            dot.className = 'review-dot' + (i === 0 ? ' active' : '');
            dot.addEventListener('click', () => goToSlide(i));
            reviewCarouselDots.appendChild(dot);
        });

        goToSlide(0);

        // Auto-slide every 7 seconds
        if (reviews.length > 1) {
            reviewCarouselTimer = setInterval(() => {
                reviewCarouselIndex = (reviewCarouselIndex + 1) % reviews.length;
                goToSlide(reviewCarouselIndex);
            }, 7000);
        }
    };

    const goToSlide = (index) => {
        reviewCarouselIndex = index;
        const cards = reviewCarouselTrack ? reviewCarouselTrack.querySelectorAll('.review-card') : [];
        const dots  = reviewCarouselDots  ? reviewCarouselDots.querySelectorAll('.review-dot')  : [];
        cards.forEach((c, i) => c.classList.toggle('active', i === index));
        dots.forEach((d, i)  => d.classList.toggle('active', i === index));
    };

    // Helper
    const escapeHtml = (str) => str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');

    // --- Submit Review ---
    if (reviewForm) {
        reviewForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const name   = document.getElementById('review-name').value.trim();
            const rating = parseInt(reviewRatingInput.value);
            const text   = document.getElementById('review-text').value.trim();

            if (!name || !text) { showToast('Please fill all fields.'); return; }
            if (rating < 1)     { showToast('Please select a star rating.'); return; }

            const btn = document.getElementById('btn-submit-review');
            btn.disabled = true;
            btn.textContent = 'Posting...';

            try {
                const res  = await fetch('api.php?action=submit_review', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ category: currentReviewCategory, customer_name: name, rating, review_text: text })
                });
                const data = await res.json();
                if (data.success) {
                    showToast('✨ Review posted successfully!');
                    reviewForm.reset();
                    reviewRatingInput.value = '0';
                    if (starRatingEl) starRatingEl.querySelectorAll('.star').forEach(s => s.classList.remove('selected'));
                    await loadReviews(currentReviewCategory);
                } else {
                    showToast('Error: ' + data.message);
                }
            } catch (err) {
                showToast('Could not post review. Try again.');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Post Review';
            }
        });
    }

    // Hook into openCategoryView to load reviews whenever category changes
    const _origOpenCategoryView = window.openCategoryView;
    window.openCategoryView = (category) => {
        _origOpenCategoryView(category);
        if (category !== 'All') {
            loadReviews(category);
        } else {
            // Hide review section for "All" view
            const revSec = document.getElementById('reviews-section');
            if (revSec) revSec.style.display = 'none';
        }
        // Show reviews section for specific categories
        const revSec = document.getElementById('reviews-section');
        if (revSec) revSec.style.display = category === 'All' ? 'none' : 'block';
    };
});
