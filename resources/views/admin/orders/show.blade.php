@extends("layouts.admin")
@section("title", "Order Details")
@section("scripts")
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Load order details when page loads
    const urlParts = window.location.pathname.split("/");
    const orderId = urlParts[urlParts.length - 1];
    loadOrderDetails(orderId);
});
</script>
@endsection
@section("content")
<div class="container-fluid px-4">
    <h1 class="mt-4">Order Details</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route(\"admin.dashboard\") }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route(\"admin.orders.index\") }}">Orders</a></li>
        <li class="breadcrumb-item active">Order #<span id="order-number">Loading...</span></li>
    </ol>
    <div id="order-details-container">
        <p class="text-center">Loading order details...</p>
    </div>
</div>
@endsection
