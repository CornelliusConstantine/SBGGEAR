@extends('layouts.admin')

@section('title', 'Order Management')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Order Management</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Orders</li>
    </ol>
    
    <!-- Success Message -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    
    <!-- Auth Debug Info (Development Only) -->
    @if(config('app.debug'))
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <i class="fas fa-bug me-1"></i>
            Authentication Debug Info
        </div>
        <div class="card-body">
            <p><strong>User:</strong> {{ Auth::user()->name }} ({{ Auth::user()->email }})</p>
            <p><strong>Role:</strong> {{ Auth::user()->role }}</p>
            <p><strong>Authenticated:</strong> {{ Auth::check() ? 'Yes' : 'No' }}</p>
            <p><strong>Session ID:</strong> {{ session()->getId() }}</p>
        </div>
    </div>
    @endif
    
    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter me-1"></i>
            Filter Orders
        </div>
        <div class="card-body">
            <form id="orders-filter-form" class="row align-items-end">
                <div class="col-md-3 mb-3">
                    <label for="status-filter" class="form-label">Status</label>
                    <select class="form-select" id="status-filter" name="status">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="payment-status-filter" class="form-label">Payment Status</label>
                    <select class="form-select" id="payment-status-filter" name="payment_status">
                        <option value="">All Payment Statuses</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="paid">Paid</option>
                        <option value="refunded">Refunded</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="date-from" class="form-label">Date From</label>
                    <input type="date" class="form-control" id="date-from" name="date_from">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="date-to" class="form-label">Date To</label>
                    <input type="date" class="form-control" id="date-to" name="date_to">
                </div>
                <div class="col-md-3 mb-3">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                    <button type="button" id="clear-filters" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Orders Table -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-table me-1"></i>
                Orders
            </div>
            <div>
                <button id="refresh-orders" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-sync"></i> Refresh
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="orders-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Payment Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="orders-body">
                        <tr>
                            <td colspan="7" class="text-center">Loading orders...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="pagination-info">
                    Showing <span id="pagination-from">0</span> to <span id="pagination-to">0</span> of <span id="pagination-total">0</span> orders
                </div>
                <div>
                    <select id="per-page" class="form-select form-select-sm d-inline-block me-2" style="width: auto">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <ul class="pagination mb-0" id="pagination-controls"></ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initial load
    loadOrders();
    
    // Event listeners
    document.getElementById('orders-filter-form').addEventListener('submit', function(e) {
        e.preventDefault();
        loadOrders(1);
    });
    
    document.getElementById('clear-filters').addEventListener('click', function() {
        document.getElementById('orders-filter-form').reset();
        loadOrders(1);
    });
    
    document.getElementById('refresh-orders').addEventListener('click', function() {
        loadOrders(getCurrentPage());
    });
    
    document.getElementById('per-page').addEventListener('change', function() {
        loadOrders(1);
    });
});

function getCurrentPage() {
    const activePage = document.querySelector('#pagination-controls .page-item.active');
    return activePage ? parseInt(activePage.dataset.page) : 1;
}

function loadOrders(page = 1) {
    const perPage = document.getElementById('per-page').value;
    const statusFilter = document.getElementById('status-filter').value;
    const paymentStatusFilter = document.getElementById('payment-status-filter').value;
    const dateFrom = document.getElementById('date-from').value;
    const dateTo = document.getElementById('date-to').value;
    
    // Build query string
    let queryParams = `per_page=${perPage}&page=${page}`;
    if (statusFilter) queryParams += `&status=${statusFilter}`;
    if (paymentStatusFilter) queryParams += `&payment_status=${paymentStatusFilter}`;
    if (dateFrom) queryParams += `&date_from=${dateFrom}`;
    if (dateTo) queryParams += `&date_to=${dateTo}`;
    
    // Show loading
    document.getElementById('orders-body').innerHTML = '<tr><td colspan="7" class="text-center">Loading orders...</td></tr>';
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Fetch orders with credentials and CSRF token
    fetch(`/admin/api/orders?${queryParams}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin' // Include cookies in the request
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}, URL: ${response.url}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.data.length === 0) {
                document.getElementById('orders-body').innerHTML = '<tr><td colspan="7" class="text-center">No orders found</td></tr>';
                document.getElementById('pagination-from').textContent = '0';
                document.getElementById('pagination-to').textContent = '0';
                document.getElementById('pagination-total').textContent = '0';
                document.getElementById('pagination-controls').innerHTML = '';
                return;
            }
            
            // Update table body
            let html = '';
            data.data.forEach(order => {
                const date = new Date(order.created_at).toLocaleDateString();
                const statusClass = getStatusClass(order.status);
                const paymentStatusClass = getPaymentStatusClass(order.payment_status);
                
                html += `
                    <tr>
                        <td>${order.order_number}</td>
                        <td>${date}</td>
                        <td>${order.user ? order.user.name : 'Guest'}</td>
                        <td>Rp${formatNumber(order.total_amount)}</td>
                        <td><span class="badge ${statusClass}">${order.status}</span></td>
                        <td><span class="badge ${paymentStatusClass}">${order.payment_status}</span></td>
                        <td>
                            <a href="/admin/orders/${order.id}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                `;
            });
            
            document.getElementById('orders-body').innerHTML = html;
            
            // Update pagination
            document.getElementById('pagination-from').textContent = data.from || 0;
            document.getElementById('pagination-to').textContent = data.to || 0;
            document.getElementById('pagination-total').textContent = data.total || 0;
            
            // Create pagination controls
            createPaginationControls(data);
        })
        .catch(error => {
            console.error('Error loading orders:', error);
            document.getElementById('orders-body').innerHTML = `<tr><td colspan="7" class="text-center text-danger">Failed to load orders: ${error.message}</td></tr>`;
        });
}

function createPaginationControls(paginationData) {
    const paginationControls = document.getElementById('pagination-controls');
    paginationControls.innerHTML = '';
    
    if (!paginationData.last_page || paginationData.last_page <= 1) {
        return;
    }
    
    // Previous button
    const prevLi = document.createElement('li');
    prevLi.className = `page-item ${paginationData.current_page <= 1 ? 'disabled' : ''}`;
    prevLi.innerHTML = `<a class="page-link" href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>`;
    prevLi.addEventListener('click', (e) => {
        e.preventDefault();
        if (paginationData.current_page > 1) {
            loadOrders(paginationData.current_page - 1);
        }
    });
    paginationControls.appendChild(prevLi);
    
    // Page buttons
    let startPage = Math.max(1, paginationData.current_page - 2);
    let endPage = Math.min(paginationData.last_page, startPage + 4);
    
    if (endPage - startPage < 4) {
        startPage = Math.max(1, endPage - 4);
    }
    
    for (let i = startPage; i <= endPage; i++) {
        const pageLi = document.createElement('li');
        pageLi.className = `page-item ${i === paginationData.current_page ? 'active' : ''}`;
        pageLi.dataset.page = i;
        pageLi.innerHTML = `<a class="page-link" href="#">${i}</a>`;
        pageLi.addEventListener('click', (e) => {
            e.preventDefault();
            loadOrders(i);
        });
        paginationControls.appendChild(pageLi);
    }
    
    // Next button
    const nextLi = document.createElement('li');
    nextLi.className = `page-item ${paginationData.current_page >= paginationData.last_page ? 'disabled' : ''}`;
    nextLi.innerHTML = `<a class="page-link" href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>`;
    nextLi.addEventListener('click', (e) => {
        e.preventDefault();
        if (paginationData.current_page < paginationData.last_page) {
            loadOrders(paginationData.current_page + 1);
        }
    });
    paginationControls.appendChild(nextLi);
}

function getStatusClass(status) {
    switch(status) {
        case 'pending': return 'bg-warning text-dark';
        case 'processing': return 'bg-info text-dark';
        case 'shipped': return 'bg-primary';
        case 'delivered': return 'bg-success';
        case 'cancelled': return 'bg-danger';
        default: return 'bg-secondary';
    }
}

function getPaymentStatusClass(status) {
    switch(status) {
        case 'paid': return 'bg-success';
        case 'unpaid': return 'bg-warning text-dark';
        case 'refunded': return 'bg-danger';
        default: return 'bg-secondary';
    }
}

function formatNumber(number) {
    return new Intl.NumberFormat('id-ID').format(number);
}
</script>
@endsection 