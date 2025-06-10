@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12 mb-4">
            <h2>Midtrans Test Page</h2>
            <p class="lead">This page is for testing Midtrans integration.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Test Payment</h5>
                </div>
                <div class="card-body">
                    <p>Click the button below to test Midtrans payment with a sample order.</p>
                    <p>Order Details:</p>
                    <ul>
                        <li>Order Number: <strong>{{ $orderNumber }}</strong></li>
                        <li>Amount: <strong>IDR {{ number_format($amount, 0, ',', '.') }}</strong></li>
                        <li>Customer: <strong>{{ $customerName }}</strong></li>
                    </ul>
                    
                    <button id="pay-button" class="btn btn-primary">
                        Pay Now
                    </button>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Midtrans Configuration</h5>
                </div>
                <div class="card-body">
                    <p><strong>Environment:</strong> {{ config('app.env') }}</p>
                    <p><strong>Midtrans Mode:</strong> {{ env('MIDTRANS_IS_PRODUCTION') ? 'Production' : 'Sandbox' }}</p>
                    <p><strong>Client Key:</strong> {{ substr(env('MIDTRANS_CLIENT_KEY'), 0, 10) }}...</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Midtrans Client Key -->
<script src="{{ env('MIDTRANS_SNAP_URL') }}" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const payButton = document.getElementById('pay-button');
        
        payButton.addEventListener('click', function() {
            // Disable the button to prevent multiple clicks
            payButton.disabled = true;
            payButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing...';
            
            // Open Snap payment page
            snap.pay('{{ $snapToken }}', {
                onSuccess: function(result) {
                    console.log('Payment success:', result);
                    alert('Payment successful! Transaction ID: ' + result.transaction_id);
                    payButton.disabled = false;
                    payButton.innerHTML = 'Pay Now';
                },
                onPending: function(result) {
                    console.log('Payment pending:', result);
                    alert('Payment pending. Please complete your payment according to the instructions.');
                    payButton.disabled = false;
                    payButton.innerHTML = 'Pay Now';
                },
                onError: function(result) {
                    console.error('Payment failed:', result);
                    alert('Payment failed. Please try again.');
                    payButton.disabled = false;
                    payButton.innerHTML = 'Pay Now';
                },
                onClose: function() {
                    console.log('Customer closed the payment popup without completing payment');
                    payButton.disabled = false;
                    payButton.innerHTML = 'Pay Now';
                },
                language: "en"
            });
        });
    });
</script>
@endsection 