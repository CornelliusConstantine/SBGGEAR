// Admin Dashboard JavaScript

/**
 * Admin Dashboard JavaScript
 * Handles fetching and displaying dashboard data
 */
document.addEventListener('DOMContentLoaded', function() {
    // Fetch dashboard stats
    fetchDashboardStats();
    
    // Fetch recent orders
    fetchRecentOrders();
    
    // Fetch low stock products
    fetchLowStockProducts();
    
    // Initialize sales chart if element exists
    if (document.getElementById('sales-chart')) {
        initSalesChart();
    }
    
    // Add event listener for chart period change
    const chartPeriod = document.getElementById('chart-period');
    if (chartPeriod) {
        chartPeriod.addEventListener('change', function() {
            fetchSalesChartData(this.value);
        });
    }
});

/**
 * Fetch dashboard statistics
 */
function fetchDashboardStats() {
    fetch('/admin/dashboard/stats')
        .then(response => response.json())
        .then(data => {
            document.getElementById('total-sales').textContent = 'Rp' + formatNumber(data.revenue_this_month || 0);
            document.getElementById('total-orders').textContent = data.orders_this_month || 0;
            document.getElementById('total-products').textContent = data.total_products || 0;
            document.getElementById('total-customers').textContent = data.total_customers || 0;
            
            // Update period labels if needed
            if (document.getElementById('total-sales-period')) {
                document.getElementById('total-sales-period').textContent = 'This month';
            }
            if (document.getElementById('total-orders-period')) {
                document.getElementById('total-orders-period').textContent = 'This month';
            }
        })
        .catch(error => {
            console.error('Error fetching dashboard stats:', error);
            // Show error state
            document.getElementById('total-sales').textContent = 'Rp0';
            document.getElementById('total-orders').textContent = '0';
            document.getElementById('total-products').textContent = '0';
            document.getElementById('total-customers').textContent = '0';
        });
}

/**
 * Fetch recent orders
 */
function fetchRecentOrders() {
    fetch('/admin/dashboard/recent-orders')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(orders => {
            const tableBody = document.getElementById('recent-orders-body');
            if (!tableBody) return;
            
            if (!orders || orders.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center">No orders found</td></tr>';
                return;
            }
            
            let html = '';
            
            orders.forEach(order => {
                const date = new Date(order.created_at).toLocaleDateString();
                const statusClass = getStatusClass(order.status);
                
                html += `
                    <tr>
                        <td>${order.order_number || `#ORD-${order.id}`}</td>
                        <td>${date}</td>
                        <td>${order.user ? order.user.name : 'Guest'}</td>
                        <td>Rp${formatNumber(order.total_amount)}</td>
                        <td><span class="badge ${statusClass}">${order.status}</span></td>
                        <td>
                            <a href="/admin/orders/${order.id}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                `;
            });
            
            tableBody.innerHTML = html;
        })
        .catch(error => {
            console.error('Error fetching recent orders:', error);
            const tableBody = document.getElementById('recent-orders-body');
            if (tableBody) {
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Failed to load orders</td></tr>';
            }
        });
}

/**
 * Fetch low stock products
 */
function fetchLowStockProducts() {
    fetch('/admin/dashboard/low-stock-products')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(products => {
            const container = document.getElementById('low-stock-products');
            if (!container) return;
            
            if (!products || products.length === 0) {
                container.innerHTML = '<p class="text-center">No low stock products</p>';
                return;
            }
            
            let html = '<ul class="list-group">';
            
            products.forEach(product => {
                let stockText = '';
                let stockClass = '';
                
                if (product.stock === 0) {
                    stockText = 'Out of stock';
                    stockClass = 'bg-danger text-white';
                } else if (product.stock <= 5) {
                    stockText = `${product.stock} left`;
                    stockClass = 'bg-danger text-white';
                } else {
                    stockText = `${product.stock} left`;
                    stockClass = 'bg-warning text-dark';
                }
                
                html += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${product.name}</strong>
                            <br>
                            <small class="text-muted">SKU: ${product.sku || 'N/A'}</small>
                        </div>
                        <span class="badge ${stockClass} rounded-pill">
                            ${stockText}
                        </span>
                    </li>
                `;
            });
            
            html += '</ul>';
            container.innerHTML = html;
        })
        .catch(error => {
            console.error('Error fetching low stock products:', error);
            const container = document.getElementById('low-stock-products');
            if (container) {
                container.innerHTML = '<p class="text-center text-danger">Failed to load products</p>';
            }
        });
}

/**
 * Initialize sales chart
 */
let salesChart = null;

function initSalesChart() {
    const chartElement = document.getElementById('sales-chart');
    if (!chartElement) return;
    
    const ctx = chartElement.getContext('2d');
    salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Sales Amount',
                data: [],
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp' + formatNumber(value);
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Sales: Rp' + formatNumber(context.raw);
                        }
                    }
                }
            }
        }
    });
    
    // Initial data load
    fetchSalesChartData('daily');
}

/**
 * Fetch sales chart data
 */
function fetchSalesChartData(period) {
    if (!salesChart) return;
    
    fetch(`/admin/dashboard/sales-chart?period=${period}`)
        .then(response => response.json())
        .then(data => {
            const labels = [];
            const salesData = [];
            
            if (period === 'daily') {
                data.forEach(item => {
                    labels.push(new Date(item.date).toLocaleDateString());
                    salesData.push(item.total_sales);
                });
            } else {
                data.forEach(item => {
                    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    labels.push(monthNames[item.month - 1] + ' ' + item.year);
                    salesData.push(item.total_sales);
                });
            }
            
            salesChart.data.labels = labels;
            salesChart.data.datasets[0].data = salesData;
            salesChart.update();
        })
        .catch(error => {
            console.error('Error fetching sales chart data:', error);
        });
}

/**
 * Get status class for badges
 */
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

/**
 * Format number with thousand separators
 */
function formatNumber(number) {
    return new Intl.NumberFormat('id-ID').format(number);
}
