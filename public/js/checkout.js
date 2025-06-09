/**
 * Checkout JavaScript functionality
 * Handles shipping calculations, form validations, and other checkout-related operations
 */

document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const shippingForm = document.getElementById('shippingForm');
    const courierSelect = document.getElementById('courier');
    const citySelect = document.getElementById('shipping_city');
    const weightInput = document.getElementById('weight');
    const shippingServicesDiv = document.getElementById('shippingServices');
    const loadingDiv = document.getElementById('loadingShipping');
    const serviceInput = document.getElementById('service');
    const shippingCostInput = document.getElementById('shipping_cost');
    const shippingCostDisplay = document.getElementById('shipping-cost-display');
    const totalAmountDisplay = document.getElementById('total-amount');
    const subtotalAmountEl = document.getElementById('subtotal-amount');
    const continueBtn = document.getElementById('continueBtn');
    
    // Check if we're on the shipping page
    if (shippingForm) {
        // Get subtotal amount from the display element
        const subtotalText = subtotalAmountEl ? subtotalAmountEl.textContent : '0';
        const subtotalAmount = parseFloat(subtotalText.replace(/[^0-9]/g, ''));
        
        // Format currency function
        function formatCurrency(amount) {
            return 'IDR ' + amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
        
        // Calculate shipping when courier or city changes
        function calculateShipping() {
            const courier = courierSelect.value;
            const city = citySelect.value;
            const weight = weightInput.value;
            
            if (!courier || !city) {
                return;
            }
            
            // Show loading
            loadingDiv.style.display = 'flex';
            shippingServicesDiv.innerHTML = '';
            serviceInput.value = '';
            shippingCostInput.value = '';
            continueBtn.disabled = true;
            
            // Get CSRF token from meta tag
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Calculate shipping via AJAX
            fetch('/checkout/calculate-shipping', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    courier: courier,
                    city: city,
                    weight: weight
                })
            })
            .then(response => response.json())
            .then(data => {
                loadingDiv.style.display = 'none';
                
                if (data.services && data.services.length > 0) {
                    let servicesHtml = '';
                    
                    data.services.forEach(service => {
                        servicesHtml += `
                            <div class="shipping-option" data-service="${service.service}" data-cost="${service.cost}">
                                <div class="form-check">
                                    <input class="form-check-input shipping-radio" type="radio" name="shipping_service_radio" 
                                           id="service_${service.service}" value="${service.service}">
                                    <label class="form-check-label" for="service_${service.service}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>${data.courier.toUpperCase()} - ${service.description}</strong>
                                                <div class="text-muted">Estimated delivery: ${service.estimate}</div>
                                            </div>
                                            <div class="fw-bold">
                                                ${formatCurrency(service.cost)}
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        `;
                    });
                    
                    shippingServicesDiv.innerHTML = servicesHtml;
                    
                    // Add event listeners to shipping options
                    document.querySelectorAll('.shipping-option').forEach(option => {
                        option.addEventListener('click', function() {
                            const service = this.dataset.service;
                            const cost = parseFloat(this.dataset.cost);
                            
                            // Update hidden inputs
                            serviceInput.value = service;
                            shippingCostInput.value = cost;
                            
                            // Update display
                            shippingCostDisplay.textContent = formatCurrency(cost);
                            totalAmountDisplay.textContent = formatCurrency(subtotalAmount + cost);
                            
                            // Update UI
                            document.querySelectorAll('.shipping-option').forEach(opt => {
                                opt.classList.remove('selected');
                            });
                            this.classList.add('selected');
                            
                            // Check the radio button
                            const radio = this.querySelector('.shipping-radio');
                            radio.checked = true;
                            
                            // Enable continue button
                            continueBtn.disabled = false;
                        });
                    });
                } else {
                    shippingServicesDiv.innerHTML = '<div class="alert alert-warning">No shipping services available for this destination.</div>';
                }
            })
            .catch(error => {
                loadingDiv.style.display = 'none';
                shippingServicesDiv.innerHTML = '<div class="alert alert-danger">Error calculating shipping. Please try again.</div>';
                console.error('Error:', error);
            });
        }
        
        // Add event listeners
        courierSelect.addEventListener('change', calculateShipping);
        citySelect.addEventListener('change', calculateShipping);
        
        // Form validation
        shippingForm.addEventListener('submit', function(e) {
            if (!serviceInput.value || !shippingCostInput.value) {
                e.preventDefault();
                alert('Please select a shipping method');
            }
        });
        
        // Initialize if values are pre-selected (e.g., from validation errors)
        if (courierSelect.value && citySelect.value) {
            calculateShipping();
        }
        
        // Save form data to localStorage for draft functionality
        const formInputs = shippingForm.querySelectorAll('input, select, textarea');
        
        // Load saved data
        formInputs.forEach(input => {
            const savedValue = localStorage.getItem('checkout_' + input.name);
            if (savedValue && input.type !== 'hidden' && input.name !== '_token') {
                if (input.type === 'checkbox' || input.type === 'radio') {
                    input.checked = savedValue === 'true';
                } else {
                    input.value = savedValue;
                }
            }
            
            // Save data on change
            input.addEventListener('change', function() {
                if (input.type === 'checkbox' || input.type === 'radio') {
                    localStorage.setItem('checkout_' + input.name, input.checked);
                } else {
                    localStorage.setItem('checkout_' + input.name, input.value);
                }
            });
        });
        
        // Clear localStorage after successful form submission
        shippingForm.addEventListener('submit', function() {
            if (shippingForm.checkValidity()) {
                formInputs.forEach(input => {
                    localStorage.removeItem('checkout_' + input.name);
                });
            }
        });
    }
    
    // Payment page functionality
    const payButton = document.getElementById('pay-button');
    if (payButton) {
        payButton.addEventListener('click', function() {
            // Disable the button to prevent multiple clicks
            payButton.disabled = true;
            payButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing...';
            
            // The actual payment processing is handled by the Snap.js library included in the payment view
        });
    }
}); 