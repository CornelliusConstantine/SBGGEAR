'use strict';

app.controller('AdminReportController', ['$scope', '$http', '$window', function($scope, $http, $window) {
    // Initialize controller
    $scope.init = function() {
        $scope.loading = false;
        $scope.reports = {};
        $scope.selectedRange = 'last30days';
        $scope.chartPeriod = 'daily';
        $scope.startDate = null;
        $scope.endDate = null;
        
        // Set default date range (last 30 days)
        $scope.updateDateRange();
        
        // Generate initial reports
        $scope.generateReports();
    };
    
    // Update date range based on selected option
    $scope.updateDateRange = function() {
        var today = new Date();
        var startDate, endDate;
        
        switch ($scope.selectedRange) {
            case 'today':
                startDate = new Date(today);
                endDate = new Date(today);
                break;
                
            case 'yesterday':
                startDate = new Date(today);
                startDate.setDate(startDate.getDate() - 1);
                endDate = new Date(startDate);
                break;
                
            case 'last7days':
                endDate = new Date(today);
                startDate = new Date(today);
                startDate.setDate(startDate.getDate() - 6);
                break;
                
            case 'last30days':
                endDate = new Date(today);
                startDate = new Date(today);
                startDate.setDate(startDate.getDate() - 29);
                break;
                
            case 'thisMonth':
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                endDate = new Date(today);
                break;
                
            case 'lastMonth':
                startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                endDate = new Date(today.getFullYear(), today.getMonth(), 0);
                break;
                
            case 'custom':
                // Don't change dates for custom range
                return;
                
            default:
                // Default to last 30 days
                endDate = new Date(today);
                startDate = new Date(today);
                startDate.setDate(startDate.getDate() - 29);
                break;
        }
        
        // Format dates for input fields
        $scope.startDate = formatDateForInput(startDate);
        $scope.endDate = formatDateForInput(endDate);
    };
    
    // Update custom date range
    $scope.updateCustomRange = function() {
        // Validation will be handled by the date inputs
    };
    
    // Generate reports
    $scope.generateReports = function() {
        $scope.loading = true;
        
        // Prepare date parameters
        var params = {
            start_date: $scope.startDate,
            end_date: $scope.endDate,
            period: $scope.chartPeriod
        };
        
        $http.get('/api/admin/dashboard/sales', { params: params })
            .then(function(response) {
                $scope.reports = response.data;
                
                // Initialize chart after data is loaded
                setTimeout(function() {
                    initSalesChart($scope.reports.salesChart);
                }, 100);
            })
            .catch(function(error) {
                console.error('Error generating reports', error);
                
                // Show error message
                $scope.showError('Failed to generate reports. Please try again later.');
            })
            .finally(function() {
                $scope.loading = false;
            });
    };
    
    // Update chart when period changes
    $scope.updateChart = function() {
        $scope.generateReports();
    };
    
    // Export sales report
    $scope.exportSalesReport = function() {
        var params = {
            start_date: $scope.startDate,
            end_date: $scope.endDate,
            type: 'sales'
        };
        
        $window.location.href = '/api/admin/reports/export?' + $.param(params);
    };
    
    // Export products report
    $scope.exportProductsReport = function() {
        var params = {
            start_date: $scope.startDate,
            end_date: $scope.endDate,
            type: 'products'
        };
        
        $window.location.href = '/api/admin/reports/export?' + $.param(params);
    };
    
    // Export customers report
    $scope.exportCustomersReport = function() {
        var params = {
            start_date: $scope.startDate,
            end_date: $scope.endDate,
            type: 'customers'
        };
        
        $window.location.href = '/api/admin/reports/export?' + $.param(params);
    };
    
    // Export inventory report
    $scope.exportInventoryReport = function() {
        var params = {
            type: 'inventory'
        };
        
        $window.location.href = '/api/admin/reports/export?' + $.param(params);
    };
    
    // Show success message
    $scope.showSuccess = function(message) {
        $scope.alertMessage = message;
        $scope.alertType = 'success';
        $scope.showAlert = true;
        
        // Hide alert after 5 seconds
        setTimeout(function() {
            $scope.$apply(function() {
                $scope.showAlert = false;
            });
        }, 5000);
    };
    
    // Show error message
    $scope.showError = function(message) {
        $scope.alertMessage = message;
        $scope.alertType = 'danger';
        $scope.showAlert = true;
        
        // Hide alert after 5 seconds
        setTimeout(function() {
            $scope.$apply(function() {
                $scope.showAlert = false;
            });
        }, 5000);
    };
    
    // Helper function to format date for input fields
    function formatDateForInput(date) {
        var year = date.getFullYear();
        var month = (date.getMonth() + 1).toString().padStart(2, '0');
        var day = date.getDate().toString().padStart(2, '0');
        
        return year + '-' + month + '-' + day;
    }
    
    // Initialize sales chart
    function initSalesChart(data) {
        if (!data) {
            return;
        }
        
        var ctx = document.getElementById('salesChart').getContext('2d');
        
        // Destroy existing chart if it exists
        if ($scope.salesChart) {
            $scope.salesChart.destroy();
        }
        
        $scope.salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Sales',
                    data: data.sales,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
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
                                return 'Rp' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Sales: Rp' + context.raw.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Make Math available to the view
    $scope.Math = window.Math;
}]); 