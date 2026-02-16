// Needle & Thread - Main JavaScript

// Image Gallery Functionality
function initImageGalleries() {
    const galleries = document.querySelectorAll('.product-gallery');

    galleries.forEach(gallery => {
        const mainImage = gallery.querySelector('.main-image img');
        const thumbnails = gallery.querySelectorAll('.thumbnail');

        if (mainImage && thumbnails.length > 0) {
            thumbnails.forEach(thumb => {
                thumb.addEventListener('click', function () {
                    // Update main image
                    mainImage.src = this.src;

                    // Update active thumbnail
                    thumbnails.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                });
            });
        }
    });
}

// Cart Interactions
function initCartInteractions() {
    // Quantity updates
    const quantityInputs = document.querySelectorAll('input[type="number"]');
    quantityInputs.forEach(input => {
        input.addEventListener('change', function () {
            if (this.value < 1) this.value = 1;
            if (this.max && this.value > this.max) this.value = this.max;
        });
    });

    // Add to cart animations
    const addToCartButtons = document.querySelectorAll('button[name="add_to_cart"]');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            const form = this.closest('form');
            const quantity = form.querySelector('input[name="quantity"]');

            if (!quantity || quantity.value >= 1) {
                // Add visual feedback
                this.classList.add('loading');
                this.textContent = 'Adding...';

                setTimeout(() => {
                    this.classList.remove('loading');
                    this.textContent = 'Add to Cart';
                }, 1000);
            }
        });
    });
}

// Form Validations
function initFormValidations() {
    const forms = document.querySelectorAll('form');

    forms.forEach(form => {
        // Skip the filter form - it has its own handler
        if (form.id === 'filter-form') {
            return;
        }

        form.addEventListener('submit', function (e) {
            const requiredFields = this.querySelectorAll('[required]');
            let valid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    valid = false;
                    field.style.borderColor = '#F44336';

                    // Add error message
                    if (!field.nextElementSibling || !field.nextElementSibling.classList.contains('error-message')) {
                        const errorMsg = document.createElement('div');
                        errorMsg.className = 'error-message';
                        errorMsg.style.color = '#F44336';
                        errorMsg.style.fontSize = '0.8rem';
                        errorMsg.style.marginTop = '0.25rem';
                        errorMsg.textContent = 'This field is required';
                        field.parentNode.appendChild(errorMsg);
                    }
                } else {
                    field.style.borderColor = '';
                    const errorMsg = field.parentNode.querySelector('.error-message');
                    if (errorMsg) errorMsg.remove();
                }
            });

            if (!valid) {
                e.preventDefault();
                showNotification('Please fill in all required fields', 'error');
            }
        });
    });
}

// Smooth Scrolling
function initSmoothScrolling() {
    const links = document.querySelectorAll('a[href^="#"]');

    links.forEach(link => {
        link.addEventListener('click', function (e) {
            const href = this.getAttribute('href');

            if (href !== '#') {
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
}

// Quantity Selectors
function initQuantitySelectors() {
    const quantityContainers = document.querySelectorAll('.quantity-selector');

    quantityContainers.forEach(container => {
        const input = container.querySelector('input[type="number"]');
        const minusBtn = container.querySelector('.qty-minus');
        const plusBtn = container.querySelector('.qty-plus');

        if (minusBtn && plusBtn && input) {
            minusBtn.addEventListener('click', () => {
                if (input.value > parseInt(input.min)) {
                    input.value = parseInt(input.value) - 1;
                    input.dispatchEvent(new Event('change'));
                }
            });

            plusBtn.addEventListener('click', () => {
                if (input.value < parseInt(input.max || 999)) {
                    input.value = parseInt(input.value) + 1;
                    input.dispatchEvent(new Event('change'));
                }
            });
        }
    });
}

// Comment System
function initCommentSystem() {
    const commentForms = document.querySelectorAll('.comment-form form');

    commentForms.forEach(form => {
        const commentsSection = form.closest('.comments-section');
        const productId = commentsSection?.dataset.productId;

        // If we don't have enough context to handle the submission via AJAX,
        // fall back to the native form post so the backend can process it.
        if (!productId) {
            return;
        }

        form.addEventListener('submit', async function (e) {
            if (form.dataset.submitting === '1') {
                e.preventDefault();
                return;
            }

            e.preventDefault();
            form.dataset.submitting = '1';

            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;

            try {
                submitBtn.classList.add('loading');
                submitBtn.textContent = 'Submitting...';
                submitBtn.disabled = true;

                const response = await NeedleThread.API.post('api/comments.php', {
                    product_id: productId,
                    name: formData.get('name'),
                    email: formData.get('email'),
                    comment: formData.get('comment')
                });

                if (response.success) {
                    showNotification(response.message, 'success');
                    this.reset();

                    // Reload comments
                    loadComments(productId);
                } else {
                    showNotification(response.message, 'error');
                }
            } catch (error) {
                showNotification('Failed to submit comment', 'error');
                console.error('Comment submission error:', error);
            } finally {
                submitBtn.classList.remove('loading');
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                delete form.dataset.submitting;
            }
        });
    });
}

// Load comments for a product
async function loadComments(productId) {
    try {
        const response = await NeedleThread.API.get(`api/comments.php?product_id=${productId}`);

        if (response.success) {
            const commentsList = document.querySelector('.comments-list');
            if (commentsList) {
                commentsList.innerHTML = response.data.map(comment => `
                    <div class="comment">
                        <div class="comment-header">
                            <strong>${comment.name}</strong>
                            <span class="comment-date">${new Date(comment.created_at).toLocaleDateString()}</span>
                        </div>
                        <p>${comment.comment}</p>
                    </div>
                `).join('');
            }
        }
    } catch (error) {
        console.error('Error loading comments:', error);
    }
}

// Admin Features
function initAdminFeatures() {
    // Order status updates
    const statusSelects = document.querySelectorAll('.status-select');
    statusSelects.forEach(select => {
        select.addEventListener('change', function () {
            this.form.submit();
        });
    });

    // Image management
    const deleteCheckboxes = document.querySelectorAll('input[name="delete_images[]"]');
    deleteCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const imageItem = this.closest('.image-item');
            if (this.checked) {
                imageItem.style.opacity = '0.5';
            } else {
                imageItem.style.opacity = '1';
            }
        });
    });
}

// Products Search and Filter Functionality (Simple - no AJAX, no auto-refresh)
function initProductsFilter() {
    const filterForm = document.getElementById('filter-form');
    const searchInput = document.getElementById('search-input');
    const statusSelect = document.getElementById('status-select');
    const minPriceInput = document.getElementById('min-price');
    const maxPriceInput = document.getElementById('max-price');

    if (!filterForm) {
        return; // Not on products page
    }

    // Track if button was explicitly clicked
    let buttonClicked = false;
    const applyButton = filterForm.querySelector('button[type="submit"]');

    // Mark when button is clicked - use capture phase to catch it early
    if (applyButton) {
        applyButton.addEventListener('click', function (e) {
            buttonClicked = true;
            // Allow a small delay to ensure the flag is set before form submit
            setTimeout(() => {
                buttonClicked = false;
            }, 100);
        }, true); // Use capture phase
    }

    // Prevent ALL form submissions except when button is explicitly clicked
    // Use capture phase to catch it before any other handlers
    filterForm.addEventListener('submit', function (e) {
        if (!buttonClicked) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            return false;
        }
        // Reset flag after allowing submission
        buttonClicked = false;
    }, true); // Use capture phase to run before other handlers

    // Prevent Enter key from submitting form in ANY field
    const allInputs = [searchInput, minPriceInput, maxPriceInput, statusSelect].filter(Boolean);
    allInputs.forEach(input => {
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                return false;
            }
        }, true); // Use capture phase
    });

    // Prevent any input change from triggering form submission
    allInputs.forEach(input => {
        input.addEventListener('change', function (e) {
            e.stopPropagation();
            e.stopImmediatePropagation();
        }, true);

        input.addEventListener('input', function (e) {
            e.stopPropagation();
            e.stopImmediatePropagation();
        }, true);
    });

    // Also prevent form submission on any other event
    ['change', 'input', 'keyup', 'keypress'].forEach(eventType => {
        filterForm.addEventListener(eventType, function (e) {
            // Only prevent if it's from form inputs, not the button
            if (e.target !== applyButton && e.target.type !== 'submit') {
                e.stopPropagation();
            }
        }, true);
    });
}

// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Lazy Loading Images
function initLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');

    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                imageObserver.unobserve(img);
            }
        });
    });

    images.forEach(img => imageObserver.observe(img));
}

// Notification System
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 4px;
        color: white;
        z-index: 10000;
        max-width: 300px;
        animation: slideInRight 0.3s ease;
    `;

    notification.textContent = message;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Cart Counter Update
function updateCartCounter() {
    const cart = JSON.parse(localStorage.getItem('needle_thread_cart') || '[]');
    const counter = document.querySelector('.cart-counter');

    if (counter) {
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        counter.textContent = totalItems;
        counter.style.display = totalItems > 0 ? 'inline' : 'none';
    }
}

// Price Formatting
function formatPrice(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

// API Helper Functions
const API = {
    async get(endpoint) {
        try {
            const response = await fetch(endpoint);
            return await response.json();
        } catch (error) {
            console.error('API GET Error:', error);
            throw error;
        }
    },

    async post(endpoint, data) {
        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
            return await response.json();
        } catch (error) {
            console.error('API POST Error:', error);
            throw error;
        }
    },

    async put(endpoint, data) {
        try {
            const response = await fetch(endpoint, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify(data)
            });
            return await response.json();
        } catch (error) {
            console.error('API PUT Error:', error);
            throw error;
        }
    },

    async delete(endpoint) {
        try {
            const response = await fetch(endpoint, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });
            return await response.json();
        } catch (error) {
            console.error('API DELETE Error:', error);
            throw error;
        }
    }
};

// PayPal Integration Helper
const PayPal = {
    async createOrder(amount) {
        try {
            const response = await API.post('/paypal-proxy/proxy_server.php?endpoint=v2/checkout/orders', {
                intent: 'CAPTURE',
                purchase_units: [{
                    amount: {
                        currency_code: 'USD',
                        value: amount.toString()
                    }
                }]
            });

            return response;
        } catch (error) {
            console.error('PayPal Create Order Error:', error);
            throw error;
        }
    },

    async captureOrder(orderID) {
        try {
            const response = await API.post(`/paypal-proxy/proxy_server.php?endpoint=v2/checkout/orders/${orderID}/capture`);
            return response;
        } catch (error) {
            console.error('PayPal Capture Order Error:', error);
            throw error;
        }
    }
};

// PayPal Button Initialization
function initPayPalButtons() {
    const container = document.getElementById('paypal-button-container');
    if (!container || !window.paypal) return;

    window.paypal.Buttons({
        createOrder: async (data, actions) => {
            try {
                if (typeof ORDER_TOTAL === 'undefined') {
                    throw new Error('Order total is not defined');
                }
                const response = await NeedleThread.PayPal.createOrder(ORDER_TOTAL);
                return response.id;
            } catch (error) {
                console.error('Failed to create PayPal order:', error);
                showNotification('Failed to initialize PayPal payment', 'error');
            }
        },
        onApprove: async (data, actions) => {
            try {
                const captureData = await NeedleThread.PayPal.captureOrder(data.orderID);

                if (captureData.status === 'COMPLETED') {
                    // Create order in our database
                    const form = document.querySelector('.checkout-form form');
                    const formData = new FormData(form);

                    // Prepare order data
                    const orderData = {
                        name: formData.get('name'),
                        email: formData.get('email'),
                        address: formData.get('address'),
                        phone: formData.get('phone'),
                        total_price: ORDER_TOTAL,
                        status: 'paid', // Mark as paid
                        items: [] // We need to get items from cart... 
                        // Actually, the backend api/orders.php expects items array.
                        // But checkout.php uses session cart. 
                        // We should probably rely on the PHP session cart if we submit via PHP,
                        // BUT api/orders.php expects 'items' in the payload.
                        // This is a discrepancy. 
                        // Let's fix this by submitting the form to checkout.php but with a flag?
                        // No, checkout.php handles POST and creates order.
                        // If we use JS to create order via API, we need to send items.
                        // Alternative: Submit the form programmatically to checkout.php
                        // but we need to tell it the payment was successful.
                        // Let's add a hidden input 'paypal_order_id' and submit the form.
                    };

                    // Better approach: Submit the form to checkout.php
                    // We can add a hidden input for 'paypal_transaction_id' and 'payment_status'
                    // But checkout.php needs to be updated to handle this.
                    // Let's stick to the plan: use api/orders.php? 
                    // No, that requires reconstructing the cart in JS.
                    // EASIER: Add hidden input 'payment_status' = 'paid' and submit the form.

                    const paymentInput = document.createElement('input');
                    paymentInput.type = 'hidden';
                    paymentInput.name = 'payment_status';
                    paymentInput.value = 'paid';
                    form.appendChild(paymentInput);

                    const txnInput = document.createElement('input');
                    txnInput.type = 'hidden';
                    txnInput.name = 'transaction_id';
                    txnInput.value = captureData.id;
                    form.appendChild(txnInput);

                    // Submit the form
                    const submitBtn = document.getElementById('place-order-btn');
                    // We need to simulate the button click or add the name param
                    const placeOrderInput = document.createElement('input');
                    placeOrderInput.type = 'hidden';
                    placeOrderInput.name = 'place_order';
                    placeOrderInput.value = '1';
                    form.appendChild(placeOrderInput);

                    form.submit();
                }
            } catch (error) {
                console.error('PayPal Capture Error:', error);
                showNotification('Payment failed. Please try again.', 'error');
            }
        },
        onError: (err) => {
            console.error('PayPal Error:', err);
            showNotification('An error occurred with PayPal', 'error');
        }
    }).render('#paypal-button-container');
}

// Payment Method Toggle
function initPaymentMethodToggle() {
    const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
    const placeOrderBtn = document.getElementById('place-order-btn');
    const paypalContainer = document.getElementById('paypal-button-container');

    if (!placeOrderBtn || !paypalContainer) return;

    function updateVisibility() {
        const selected = document.querySelector('input[name="payment_method"]:checked')?.value;
        if (selected === 'paypal') {
            placeOrderBtn.style.display = 'none';
            paypalContainer.style.display = 'block';
        } else {
            placeOrderBtn.style.display = 'block';
            paypalContainer.style.display = 'none';
        }
    }

    paymentRadios.forEach(radio => {
        radio.addEventListener('change', updateVisibility);
    });

    // Initial check
    updateVisibility();
}

// View order details (Admin)
function viewOrder(orderId) {
    fetch(`../api/orders.php?id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const order = data.data;
                const modal = document.getElementById('orderModal');
                const details = document.getElementById('orderDetails');

                details.innerHTML = `
                    <h2>Order #${order.id}</h2>
                    <div class="order-info">
                        <p><strong>Customer:</strong> ${order.name}</p>
                        <p><strong>Email:</strong> ${order.email}</p>
                        <p><strong>Date:</strong> ${new Date(order.created_at).toLocaleString()}</p>
                        <p><strong>Status:</strong> <span class="status-badge status-${order.status}">${order.status}</span></p>
                        <p><strong>Address:</strong> ${order.address.replace(/\n/g, '<br>')}</p>
                    </div>
                    <h3>Order Items</h3>
                    <div class="order-items">
                        ${order.items.map(item => `
                            <div class="order-item" style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee;">
                                <div>
                                    <strong>${item.title}</strong>
                                    <br>Qty: ${item.quantity} × ${formatPrice(item.price)}
                                </div>
                                <div>${formatPrice(item.price * item.quantity)}</div>
                            </div>
                        `).join('')}
                    </div>
                    <div class="order-total" style="text-align: right; margin-top: 20px; font-size: 1.2em; font-weight: bold;">
                        Total: ${formatPrice(order.total_price)}
                    </div>
                `;

                modal.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error fetching order details:', error);
            showNotification('Error loading order details', 'error');
        });
}

// Close modal
function initModal() {
    const closeBtn = document.querySelector('.close');
    const modal = document.getElementById('orderModal');

    if (closeBtn && modal) {
        closeBtn.addEventListener('click', function () {
            modal.style.display = 'none';
        });

        window.addEventListener('click', function (event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    }
}

// Initialize everything when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
} else {
    initAll();
}

function initAll() {
    initImageGalleries();
    initCartInteractions();
    initFormValidations();
    initSmoothScrolling();
    initQuantitySelectors();
    initProductsFilter();
    initLazyLoading();
    initCommentSystem();
    initAdminFeatures();
    initModal();
    updateCartCounter();
    initPayPalButtons();
    initPaymentMethodToggle();
}

// Export for global access
window.NeedleThread = {
    API,
    PayPal,
    showNotification,
    formatPrice,
    updateCartCounter,
    viewOrder
};