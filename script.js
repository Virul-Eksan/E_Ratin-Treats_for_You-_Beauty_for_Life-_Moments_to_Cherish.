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
        
        saveCart();
        
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
            saveCart();
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
});
