/**
 * ShopEase Main JavaScript
 * This file contains all the JavaScript functionality for the website
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Header scroll effect
    const header = document.querySelector('.main-header');
    if (header) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }

    // Add animation to hero section
    const heroSection = document.querySelector('.hero-section');
    if (heroSection) {
        setTimeout(() => {
            heroSection.classList.add('animated');
        }, 300);
    }

    // Animate elements when they come into view
    const animateOnScroll = function() {
        const elements = document.querySelectorAll('.animate-on-scroll');
        
        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;
            
            if (elementPosition < windowHeight - 50) {
                element.classList.add('animated');
            }
        });
    };

    // Add animate-on-scroll class to elements
    document.querySelectorAll('.product-card, .category-card, .section-title, .feature-box').forEach(el => {
        el.classList.add('animate-on-scroll');
    });

    // Run animation check on load and scroll
    window.addEventListener('load', animateOnScroll);
    window.addEventListener('scroll', animateOnScroll);

    // Product quantity increment/decrement
    initQuantityControls();

    // Product image gallery
    initProductGallery();

    // Cart quantity update
    initCartQuantityUpdate();

    // Add to cart animation
    initAddToCartAnimation();

    // Product filters
    initProductFilters();

    // Newsletter form
    initNewsletterForm();

    // Mobile menu
    initMobileMenu();
    
    // Add smooth scrolling to all links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 100,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Add hover effect to product cards
    document.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.classList.add('hovered');
        });
        
        card.addEventListener('mouseleave', function() {
            this.classList.remove('hovered');
        });
    });
});

/**
 * Initialize quantity controls for product detail page
 */
function initQuantityControls() {
    const quantityInputs = document.querySelectorAll('.quantity-input');
    
    if (quantityInputs.length === 0) return;
    
    quantityInputs.forEach(input => {
        const decrementBtn = input.parentElement.querySelector('.decrement');
        const incrementBtn = input.parentElement.querySelector('.increment');
        
        if (decrementBtn) {
            decrementBtn.addEventListener('click', function() {
                let value = parseInt(input.value);
                if (value > 1) {
                    input.value = value - 1;
                    triggerChangeEvent(input);
                }
            });
        }
        
        if (incrementBtn) {
            incrementBtn.addEventListener('click', function() {
                let value = parseInt(input.value);
                input.value = value + 1;
                triggerChangeEvent(input);
            });
        }
        
        input.addEventListener('change', function() {
            let value = parseInt(input.value);
            if (isNaN(value) || value < 1) {
                input.value = 1;
            }
        });
    });
}

/**
 * Trigger change event on input
 */
function triggerChangeEvent(element) {
    const event = new Event('change', { bubbles: true });
    element.dispatchEvent(event);
}

/**
 * Initialize product gallery on product detail page
 */
function initProductGallery() {
    const mainImage = document.querySelector('.product-detail-image img');
    const thumbnails = document.querySelectorAll('.thumbnail');
    
    if (!mainImage || thumbnails.length === 0) return;
    
    thumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('click', function() {
            // Remove active class from all thumbnails
            thumbnails.forEach(t => t.classList.remove('active'));
            
            // Add active class to clicked thumbnail
            this.classList.add('active');
            
            // Update main image
            const imgSrc = this.querySelector('img').getAttribute('src');
            mainImage.setAttribute('src', imgSrc);
        });
    });
}

/**
 * Initialize cart quantity update
 */
function initCartQuantityUpdate() {
    const cartQuantityInputs = document.querySelectorAll('.cart-quantity input');
    
    if (cartQuantityInputs.length === 0) return;
    
    cartQuantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const cartId = this.getAttribute('data-cart-id');
            const quantity = parseInt(this.value);
            
            if (cartId && !isNaN(quantity) && quantity > 0) {
                updateCartItemQuantity(cartId, quantity);
            }
        });
    });
}

/**
 * Update cart item quantity via AJAX
 */
function updateCartItemQuantity(cartId, quantity) {
    fetch('update-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `cart_id=${cartId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update cart total
            document.querySelector('.cart-total').textContent = data.total;
            
            // Update item subtotal
            document.querySelector(`#item-total-${cartId}`).textContent = data.item_total;
            
            // Show success message
            showAlert('Cart updated successfully', 'success');
        } else {
            showAlert('Failed to update cart', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred', 'danger');
    });
}

/**
 * Initialize add to cart animation
 */
function initAddToCartAnimation() {
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    
    if (addToCartButtons.length === 0) return;
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!this.classList.contains('product-detail-page')) {
                e.preventDefault();
                
                const productId = this.getAttribute('data-product-id');
                
                // Add product to cart via AJAX
                addToCart(productId, 1);
                
                // Animate button
                this.innerHTML = '<i class="fas fa-check"></i> Added';
                this.classList.add('added');
                
                setTimeout(() => {
                    this.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
                    this.classList.remove('added');
                }, 2000);
            }
        });
    });
}

/**
 * Add product to cart via AJAX
 */
function addToCart(productId, quantity) {
    // Get the base URL from the meta tag
    const baseUrl = document.querySelector('meta[name="base-url"]').getAttribute('content');
    
    fetch(baseUrl + '/add-to-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update cart count
            document.querySelector('.cart-count').textContent = data.cart_count;
            
            // Show success message
            showAlert('Product added to cart', 'success');
        } else {
            showAlert(data.message || 'Failed to add product to cart', 'danger');
            
            // Handle redirect if needed
            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect_url;
                }, 2000); // Redirect after 2 seconds
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred', 'danger');
    });
}

/**
 * Initialize product filters
 */
function initProductFilters() {
    const filterForm = document.querySelector('.filter-form');
    
    if (!filterForm) return;
    
    // Price range slider
    const priceRange = document.querySelector('#price-range');
    const priceMin = document.querySelector('#price-min');
    const priceMax = document.querySelector('#price-max');
    
    if (priceRange && priceMin && priceMax) {
        noUiSlider.create(priceRange, {
            start: [parseInt(priceMin.value), parseInt(priceMax.value)],
            connect: true,
            range: {
                'min': parseInt(priceRange.getAttribute('data-min')),
                'max': parseInt(priceRange.getAttribute('data-max'))
            }
        });
        
        priceRange.noUiSlider.on('update', function(values, handle) {
            const value = Math.round(values[handle]);
            
            if (handle === 0) {
                priceMin.value = value;
            } else {
                priceMax.value = value;
            }
            
            document.querySelector('.price-range-value').textContent = `$${priceMin.value} - $${priceMax.value}`;
        });
    }
    
    // Filter form submit
    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const params = new URLSearchParams(formData);
        
        // Redirect to filtered URL
        window.location.href = `${window.location.pathname}?${params.toString()}`;
    });
    
    // Clear filters
    const clearFiltersBtn = document.querySelector('.clear-filters');
    
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = window.location.pathname;
        });
    }
}

/**
 * Initialize newsletter form
 */
function initNewsletterForm() {
    const newsletterForm = document.querySelector('.newsletter-form');
    
    if (!newsletterForm) return;
    
    newsletterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = this.querySelector('input[type="email"]').value;
        
        fetch('subscribe.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `email=${email}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Thank you for subscribing to our newsletter!', 'success');
                this.reset();
            } else {
                showAlert(data.message || 'Failed to subscribe', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred', 'danger');
        });
    });
}

/**
 * Initialize mobile menu
 */
function initMobileMenu() {
    const mobileMenuToggle = document.querySelector('.navbar-toggler');
    const mobileMenu = document.querySelector('#navbarMain');
    
    if (!mobileMenuToggle || !mobileMenu) return;
    
    document.addEventListener('click', function(e) {
        if (mobileMenu.classList.contains('show') && !mobileMenu.contains(e.target) && e.target !== mobileMenuToggle) {
            mobileMenuToggle.click();
        }
    });
}

/**
 * Show alert message
 */
function showAlert(message, type = 'success') {
    const alertContainer = document.createElement('div');
    alertContainer.className = `alert alert-${type} position-fixed top-0 end-0 m-3`;
    alertContainer.setAttribute('role', 'alert');
    alertContainer.style.zIndex = '9999';
    
    alertContainer.innerHTML = message;
    
    document.body.appendChild(alertContainer);
    
    // Auto close after 3 seconds
    setTimeout(() => {
        alertContainer.remove();
    }, 3000);
}

/**
 * Add to wishlist via AJAX
 */
function addToWishlist(productId) {
    fetch('add-to-wishlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showAlert('Product added to wishlist', 'success');
            
            // Update wishlist icon
            const wishlistBtn = document.querySelector(`.wishlist-btn[data-product-id="${productId}"]`);
            if (wishlistBtn) {
                wishlistBtn.classList.add('active');
                wishlistBtn.querySelector('i').classList.remove('far');
                wishlistBtn.querySelector('i').classList.add('fas');
            }
        } else {
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                showAlert(data.message || 'Failed to add product to wishlist', 'danger');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred', 'danger');
    });
}

/**
 * Remove from wishlist via AJAX
 */
function removeFromWishlist(wishlistId) {
    if (confirm('Are you sure you want to remove this item from your wishlist?')) {
        fetch('remove-from-wishlist.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `wishlist_id=${wishlistId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove item from DOM
                document.querySelector(`#wishlist-item-${wishlistId}`).remove();
                
                // Show success message
                showAlert('Product removed from wishlist', 'success');
                
                // If no items left, show empty message
                if (document.querySelectorAll('.wishlist-item').length === 0) {
                    document.querySelector('.wishlist-items').innerHTML = '<div class="alert alert-info">Your wishlist is empty.</div>';
                }
            } else {
                showAlert('Failed to remove product from wishlist', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred', 'danger');
        });
    }
}

/**
 * Apply coupon code via AJAX
 */
function applyCoupon(couponCode) {
    fetch('apply-coupon.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `coupon_code=${couponCode}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update cart summary
            document.querySelector('.subtotal-value').textContent = data.subtotal;
            document.querySelector('.discount-value').textContent = data.discount;
            document.querySelector('.total-value').textContent = data.total;
            
            // Show discount row
            document.querySelector('.discount-row').classList.remove('d-none');
            
            // Show success message
            showAlert('Coupon applied successfully', 'success');
        } else {
            showAlert(data.message || 'Invalid coupon code', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred', 'danger');
    });
}

/**
 * Remove from cart via AJAX
 */
function removeFromCart(cartId) {
    if (confirm('Are you sure you want to remove this item from your cart?')) {
        fetch('remove-from-cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cart_id=${cartId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove item from DOM
                document.querySelector(`#cart-item-${cartId}`).remove();
                
                // Update cart total
                document.querySelector('.cart-total').textContent = data.total;
                
                // Update cart count
                document.querySelector('.cart-count').textContent = data.cart_count;
                
                // Show success message
                showAlert('Product removed from cart', 'success');
                
                // If no items left, show empty message
                if (document.querySelectorAll('.cart-item').length === 0) {
                    document.querySelector('.cart-items').innerHTML = '<div class="alert alert-info">Your cart is empty.</div>';
                    document.querySelector('.cart-summary').classList.add('d-none');
                }
            } else {
                showAlert('Failed to remove product from cart', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred', 'danger');
        });
    }
}

/**
 * Submit product review via AJAX
 */
function submitReview(productId, rating, comment) {
    fetch('submit-review.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}&rating=${rating}&comment=${encodeURIComponent(comment)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showAlert('Your review has been submitted and is pending approval', 'success');
            
            // Reset form
            document.querySelector('#review-form').reset();
        } else {
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                showAlert(data.message || 'Failed to submit review', 'danger');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred', 'danger');
    });
} 