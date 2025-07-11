@extends("layouts.admin")
@section("title", "Admin Dashboard")
@section("scripts")
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset("js/admin-dashboard.js") }}"></script>
@endsection
@section("content")
<div class="container-fluid px-4">
    <h1 class="mt-4">Dashboard</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Dashboard</li>
    </ol>
    
    <!-- Stats Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="mb-0">Total Sales</h5>
                            <h2 class="mb-0" id="total-sales">Rp0</h2>
                        </div>
                        <div>
                            <i class="fas fa-dollar-sign fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span id="total-sales-period">This month</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="mb-0">Orders</h5>
                            <h2 class="mb-0" id="total-orders">0</h2>
                        </div>
                        <div>
                            <i class="fas fa-shopping-cart fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span id="total-orders-period">This month</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="mb-0">Products</h5>
                            <h2 class="mb-0" id="total-products">0</h2>
                        </div>
                        <div>
                            <i class="fas fa-box fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span>In inventory</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="mb-0">Customers</h5>
                            <h2 class="mb-0" id="total-customers">0</h2>
                        </div>
                        <div>
                            <i class="fas fa-users fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span>Registered users</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Recent Orders -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-table me-1"></i>
                        Recent Orders
                    </div>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="recent-orders-table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="recent-orders-body">
                                <!-- Orders will be loaded here via JS -->
                                <tr>
                                    <td colspan="6" class="text-center">Loading orders...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Low Stock Products -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Low Stock Products
                    </div>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-primary">Manage Stock</a>
                </div>
                <div class="card-body">
                    <div id="low-stock-products">
                        <!-- Low stock products will be loaded here via JS -->
                        <p class="text-center">Loading products...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sales Chart -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-chart-line me-1"></i>
                        Sales Overview
                    </div>
                    <div>
                        <select id="chart-period" class="form-select form-select-sm">
                            <option value="daily">Daily (Last 30 days)</option>
                            <option value="monthly">Monthly (Last 12 months)</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="sales-chart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
