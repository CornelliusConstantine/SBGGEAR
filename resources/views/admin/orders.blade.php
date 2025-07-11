<!DOCTYPE html>
<html>
<head>
    <title>Admin Orders</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .badge-pending { background-color: #ffc107; }
        .badge-processing { background-color: #17a2b8; }
        .badge-shipped { background-color: #6610f2; }
        .badge-delivered { background-color: #28a745; }
        .badge-cancelled { background-color: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Admin Orders</h1>
            <div>
                <span class="me-3">Logged in as: {{ auth()->user()->name }} ({{ auth()->user()->role }})</span>
                <a href="/" class="btn btn-secondary">Back to Home</a>
            </div>
        </div>

        <div id="auth-status" class="alert alert-info mb-3">
            Checking authentication status...
        </div>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                Order Management
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="statusFilter" class="form-label">Filter by Status:</label>
                            <select id="statusFilter" class="form-select">
                                <option value="all">All</option>
                                <option value="pending">Pending</option>
                                <option value="processing">Processing</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="ordersTableBody">
                            <tr>
                                <td colspan="6" class="text-center">Loading orders...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="loadingIndicator" class="text-center d-none">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check authentication status
            const authStatus = document.getElementById('auth-status');
            const isAdmin = {{ auth()->user()->isAdmin() ? 'true' : 'false' }};
            
            if (isAdmin) {
                authStatus.classList.remove('alert-info');
                authStatus.classList.add('alert-success');
                authStatus.textContent = 'Authentication verified: You have admin privileges';
                
                // Load orders data
                loadOrders();
            } else {
                authStatus.classList.remove('alert-info');
                authStatus.classList.add('alert-danger');
                authStatus.textContent = 'Access denied: You do not have admin privileges';
            }
            
            // Set up status filter
            document.getElementById('statusFilter').addEventListener('change', function() {
                loadOrders(this.value);
            });
        });
        
        function loadOrders(status = 'all') {
            const tableBody = document.getElementById('ordersTableBody');
            const loadingIndicator = document.getElementById('loadingIndicator');
            
            // Show loading indicator
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Loading orders...</td></tr>';
            loadingIndicator.classList.remove('d-none');
            
            // Fetch orders from API
            let url = '/admin/get-orders';
            if (status !== 'all') {
                url += `?status=${status}`;
            }
            
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Hide loading indicator
                    loadingIndicator.classList.add('d-none');
                    
                    // Check if data is available
                    if (!data || !data.data || data.data.length === 0) {
                        tableBody.innerHTML = '<tr><td colspan="6" class="text-center">No orders found</td></tr>';
                        return;
                    }
                    
                    // Populate table with orders
                    tableBody.innerHTML = '';
                    data.data.forEach(order => {
                        const row = document.createElement('tr');
                        
                        // Format date
                        const orderDate = new Date(order.created_at);
                        const formattedDate = orderDate.toLocaleDateString('id-ID');
                        
                        // Format currency
                        const formatter = new Intl.NumberFormat('id-ID', {
                            style: 'currency',
                            currency: 'IDR',
                            minimumFractionDigits: 0
                        });
                        
                        row.innerHTML = `
                            <td>${order.order_number}</td>
                            <td>${formattedDate}</td>
                            <td>${order.user ? order.user.name : 'Unknown'}</td>
                            <td>${formatter.format(order.total_amount)}</td>
                            <td><span class="badge badge-${order.status.toLowerCase()} text-white">${order.status}</span></td>
                            <td>
                                <a href="/admin/orders/${order.id}" class="btn btn-sm btn-primary">View</a>
                                <button class="btn btn-sm btn-success update-btn" data-id="${order.id}">Update</button>
                            </td>
                        `;
                        
                        tableBody.appendChild(row);
                    });
                    
                    // Add event listeners to update buttons
                    document.querySelectorAll('.update-btn').forEach(button => {
                        button.addEventListener('click', function() {
                            const orderId = this.getAttribute('data-id');
                            window.location.href = `/admin/orders/${orderId}`;
                        });
                    });
                })
                .catch(error => {
                    console.error('Error fetching orders:', error);
                    loadingIndicator.classList.add('d-none');
                    tableBody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Error loading orders: ${error.message}</td></tr>`;
                });
        }
    </script>
</body>
</html>
