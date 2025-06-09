# E-commerce Checkout System

This document provides an overview of the checkout system implementation for the e-commerce platform. The system handles the complete checkout flow from cart to confirmation, including shipping calculation, payment integration with Midtrans, and order management.

## Features

- Complete checkout flow: Cart → Shipping Information → Payment → Confirmation
- Real-time shipping cost calculation based on weight, courier, and destination
- Integration with Midtrans payment gateway
- Order management and tracking
- Automatic cancellation of abandoned orders
- Secure storage of transaction data

## Setup Instructions

### 1. Database Migration

Run the following commands to set up the database tables:

```bash
php artisan migrate
```

This will create the necessary tables for orders, order items, shipping information, and payment records.

### 2. Midtrans Configuration

Update your `.env` file with the following Midtrans configuration:

```
MIDTRANS_SERVER_KEY=SB-Mid-server-LpZQd_jNi7NF9dMqdaDFFH95
MIDTRANS_CLIENT_KEY=SB-Mid-client-NWRbGj3Ndj-152Ps
MIDTRANS_MERCHANT_ID=G375790714
MIDTRANS_IS_PRODUCTION=false
```

For production, replace the sandbox keys with your production keys and set `MIDTRANS_IS_PRODUCTION=true`.

### 3. Schedule Abandoned Order Cancellation

Make sure your Laravel scheduler is running to automatically cancel abandoned orders:

```bash
php artisan schedule:run
```

Or add the following to your crontab:

```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### 4. Set Up Webhook

Configure your Midtrans dashboard to send webhook notifications to:

```
https://your-domain.com/payment/webhook
```

## Checkout Flow

### 1. Cart

- Displays all items in the user's cart
- Shows subtotal, shipping cost (if calculated), and total
- Validates stock availability before proceeding to checkout
- Provides a "Proceed to Checkout" button

### 2. Shipping Information

- Collects recipient information (name, phone, address)
- Allows selection of city/destination
- Calculates shipping cost based on weight and courier selection
- Supports multiple couriers (JNE, J&T, SiCepat) with different service levels
- Saves draft information to prevent data loss on page refresh

### 3. Payment

- Integrates with Midtrans payment gateway
- Supports multiple payment methods (credit card, bank transfer, e-wallet, etc.)
- Securely processes payment information
- Handles payment callbacks and notifications

### 4. Confirmation

- Displays order summary and payment status
- Shows shipping information and estimated delivery time
- Provides order tracking information
- Includes customer service contact details

## Technical Implementation

### Shipping Calculation

Shipping costs are calculated based on:
- Total weight of items in the cart
- Selected courier and service level
- Destination city

The calculation uses a weight-based formula with rounding up to the nearest 250g:
```
shipping_cost = ceil(total_weight / 250) * rate_per_250g
```

### Payment Integration

The system uses the Midtrans PHP library for payment processing:
- Generates a Snap token for the payment page
- Handles payment notifications via webhook
- Updates order status based on payment status
- Logs all transaction details for auditing

### Order Management

Orders are managed through several states:
- `pending`: Initial state after order creation
- `processing`: Payment confirmed, order being prepared
- `shipped`: Order has been shipped
- `delivered`: Order has been delivered
- `cancelled`: Order has been cancelled

Payment statuses include:
- `unpaid`: Initial state
- `pending`: Payment in progress
- `paid`: Payment confirmed
- `failed`: Payment failed
- `expired`: Payment window expired

## Security Considerations

- All user inputs are validated both client-side and server-side
- CSRF protection is implemented for all forms
- Payment data is never stored directly in the database
- All API communications use HTTPS
- Webhook signatures are verified to prevent tampering

## Performance Optimization

- Database indexes on frequently queried fields
- Efficient shipping calculation algorithm
- Asynchronous AJAX for real-time updates
- Caching of static data
- CDN for static assets

## Troubleshooting

### Common Issues

1. **Payment Gateway Error**
   - Check Midtrans credentials in `.env` file
   - Ensure webhook URL is correctly configured in Midtrans dashboard

2. **Shipping Calculation Issues**
   - Verify product weights are correctly set in grams
   - Check courier rates configuration

3. **Order Status Not Updating**
   - Check webhook endpoint is accessible
   - Verify webhook signature verification

For any other issues, check the Laravel logs at `storage/logs/laravel.log`.

## Contributors

- Development Team @ SBGEAR

## License

Proprietary - All rights reserved 