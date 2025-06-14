/**
 * SBGEAR E-commerce Shipping Calculation
 * Custom shipping calculation implementation using AngularJS
 */

// Make sure Angular is available
if (typeof angular === 'undefined') {
    console.error('AngularJS is not loaded. Please include AngularJS before shipping.js');
}

// Create the Angular module and controller
var shippingApp = angular.module('shippingApp', []);

shippingApp.controller('ShippingController', ['$scope', '$http', function($scope, $http) {
    // Initialize variables
    $scope.provinces = [];
    $scope.cities = [];
    $scope.couriers = [
        { id: 'jne', name: 'JNE' },
        { id: 'pos', name: 'POS Indonesia' },
        { id: 'tiki', name: 'TIKI' }
    ];
    $scope.shippingOptions = [];
    $scope.selectedProvince = '';
    $scope.selectedCity = '';
    $scope.selectedCourier = '';
    $scope.selectedShipping = null;
    $scope.weight = 0;
    $scope.subtotal = 0;
    $scope.shippingCost = 0;
    $scope.total = 0;
    $scope.loading = {
        provinces: false,
        cities: false,
        shipping: false
    };
    $scope.errors = {
        provinces: null,
        cities: null,
        shipping: null
    };

    // Initialize the controller
    $scope.init = function(subtotal, cartWeight) {
        console.log('ShippingController initialized with subtotal:', subtotal, 'and weight:', cartWeight);
        $scope.subtotal = parseFloat(subtotal) || 0;
        $scope.weight = parseInt(cartWeight) || 250; // Default to 250g if not provided
        $scope.updateTotal();
        $scope.loadProvinces();
    };

    // Load provinces (static data)
    $scope.loadProvinces = function() {
        console.log('Loading provinces...');
        $scope.loading.provinces = true;
        $scope.errors.provinces = null;

        // Static province data
        $scope.provinces = [
            { province_id: '1', province: 'Bali' },
            { province_id: '2', province: 'Bangka Belitung' },
            { province_id: '3', province: 'Banten' },
            { province_id: '4', province: 'Bengkulu' },
            { province_id: '5', province: 'DI Yogyakarta' },
            { province_id: '6', province: 'DKI Jakarta' },
            { province_id: '7', province: 'Gorontalo' },
            { province_id: '8', province: 'Jambi' },
            { province_id: '9', province: 'Jawa Barat' },
            { province_id: '10', province: 'Jawa Tengah' },
            { province_id: '11', province: 'Jawa Timur' },
            { province_id: '12', province: 'Kalimantan Barat' },
            { province_id: '13', province: 'Kalimantan Selatan' },
            { province_id: '14', province: 'Kalimantan Tengah' },
            { province_id: '15', province: 'Kalimantan Timur' },
            { province_id: '16', province: 'Kalimantan Utara' },
            { province_id: '17', province: 'Kepulauan Riau' },
            { province_id: '18', province: 'Lampung' },
            { province_id: '19', province: 'Maluku' },
            { province_id: '20', province: 'Maluku Utara' },
            { province_id: '21', province: 'Nanggroe Aceh Darussalam (NAD)' },
            { province_id: '22', province: 'Nusa Tenggara Barat (NTB)' },
            { province_id: '23', province: 'Nusa Tenggara Timur (NTT)' },
            { province_id: '24', province: 'Papua' },
            { province_id: '25', province: 'Papua Barat' },
            { province_id: '26', province: 'Riau' },
            { province_id: '27', province: 'Sulawesi Barat' },
            { province_id: '28', province: 'Sulawesi Selatan' },
            { province_id: '29', province: 'Sulawesi Tengah' },
            { province_id: '30', province: 'Sulawesi Tenggara' },
            { province_id: '31', province: 'Sulawesi Utara' },
            { province_id: '32', province: 'Sumatera Barat' },
            { province_id: '33', province: 'Sumatera Selatan' },
            { province_id: '34', province: 'Sumatera Utara' }
        ];
        
        $scope.loading.provinces = false;
    };

    // Load cities based on selected province (static data)
    $scope.loadCities = function() {
        if (!$scope.selectedProvince) {
            $scope.cities = [];
            return;
        }

        console.log('Loading cities for province:', $scope.selectedProvince);
        $scope.loading.cities = true;
        $scope.errors.cities = null;
        $scope.selectedCity = '';
        $scope.cities = [];

        // Static city data based on province
        const citiesByProvince = {
            // Bali
            '1': [
                { city_id: '17', type: 'Kabupaten', city_name: 'Badung' },
                { city_id: '32', type: 'Kabupaten', city_name: 'Bangli' },
                { city_id: '94', type: 'Kabupaten', city_name: 'Buleleng' },
                { city_id: '114', type: 'Kabupaten', city_name: 'Gianyar' },
                { city_id: '128', type: 'Kabupaten', city_name: 'Jembrana' },
                { city_id: '161', type: 'Kabupaten', city_name: 'Karangasem' },
                { city_id: '170', type: 'Kabupaten', city_name: 'Klungkung' },
                { city_id: '291', type: 'Kabupaten', city_name: 'Tabanan' },
                { city_id: '108', type: 'Kota', city_name: 'Denpasar' }
            ],
            // Bangka Belitung
            '2': [
                { city_id: '33', type: 'Kabupaten', city_name: 'Bangka' },
                { city_id: '34', type: 'Kabupaten', city_name: 'Bangka Barat' },
                { city_id: '35', type: 'Kabupaten', city_name: 'Bangka Selatan' },
                { city_id: '36', type: 'Kabupaten', city_name: 'Bangka Tengah' },
                { city_id: '37', type: 'Kabupaten', city_name: 'Belitung' },
                { city_id: '38', type: 'Kabupaten', city_name: 'Belitung Timur' },
                { city_id: '280', type: 'Kota', city_name: 'Pangkal Pinang' }
            ],
            // Banten
            '3': [
                { city_id: '106', type: 'Kabupaten', city_name: 'Lebak' },
                { city_id: '207', type: 'Kabupaten', city_name: 'Pandeglang' },
                { city_id: '249', type: 'Kabupaten', city_name: 'Serang' },
                { city_id: '302', type: 'Kabupaten', city_name: 'Tangerang' },
                { city_id: '74', type: 'Kota', city_name: 'Cilegon' },
                { city_id: '250', type: 'Kota', city_name: 'Serang' },
                { city_id: '301', type: 'Kota', city_name: 'Tangerang' },
                { city_id: '303', type: 'Kota', city_name: 'Tangerang Selatan' }
            ],
            // DKI Jakarta
            '6': [
                { city_id: '151', type: 'Kota', city_name: 'Jakarta Barat' },
                { city_id: '152', type: 'Kota', city_name: 'Jakarta Pusat' },
                { city_id: '153', type: 'Kota', city_name: 'Jakarta Selatan' },
                { city_id: '154', type: 'Kota', city_name: 'Jakarta Timur' },
                { city_id: '155', type: 'Kota', city_name: 'Jakarta Utara' },
                { city_id: '189', type: 'Kabupaten', city_name: 'Kepulauan Seribu' }
            ],
            // Default for other provinces
            'default': [
                { city_id: '1', type: 'Kota', city_name: 'Default City' }
            ]
        };

        // Get cities for the selected province, or use default if not found
        $scope.cities = citiesByProvince[$scope.selectedProvince] || citiesByProvince['default'];
        
        $scope.loading.cities = false;
    };

    // Calculate shipping cost
    $scope.calculateShipping = function() {
        if (!$scope.selectedCity || !$scope.selectedCourier) {
            return;
        }

        console.log('Calculating shipping for city:', $scope.selectedCity, 'and courier:', $scope.selectedCourier);
        $scope.loading.shipping = true;
        $scope.errors.shipping = null;
        $scope.shippingOptions = [];
        $scope.selectedShipping = null;

        // Calculate shipping based on weight and courier
        const weightInKg = Math.ceil($scope.weight / 1000);
        const weightMultiplier = Math.max(1, Math.ceil($scope.weight / 250));
        
        let shippingCosts = [];
        
        switch ($scope.selectedCourier) {
            case 'jne':
                shippingCosts = [
                    {
                        service: 'OKE',
                        description: 'Ongkos Kirim Ekonomis',
                        cost: [
                            {
                                value: weightMultiplier * 8000,
                                etd: '3-6',
                                note: ''
                            }
                        ]
                    },
                    {
                        service: 'REG',
                        description: 'Layanan Reguler',
                        cost: [
                            {
                                value: weightMultiplier * 12000,
                                etd: '2-3',
                                note: ''
                            }
                        ]
                    },
                    {
                        service: 'YES',
                        description: 'Yakin Esok Sampai',
                        cost: [
                            {
                                value: weightMultiplier * 18000,
                                etd: '1',
                                note: ''
                            }
                        ]
                    }
                ];
                break;
            
            case 'pos':
                shippingCosts = [
                    {
                        service: 'Pos Reguler',
                        description: 'Pos Reguler',
                        cost: [
                            {
                                value: weightMultiplier * 7000,
                                etd: '3-5',
                                note: ''
                            }
                        ]
                    },
                    {
                        service: 'Pos Express',
                        description: 'Pos Express',
                        cost: [
                            {
                                value: weightMultiplier * 15000,
                                etd: '1-2',
                                note: ''
                            }
                        ]
                    }
                ];
                break;
            
            case 'tiki':
                shippingCosts = [
                    {
                        service: 'ECO',
                        description: 'Economy Service',
                        cost: [
                            {
                                value: weightMultiplier * 9000,
                                etd: '3-4',
                                note: ''
                            }
                        ]
                    },
                    {
                        service: 'REG',
                        description: 'Regular Service',
                        cost: [
                            {
                                value: weightMultiplier * 13000,
                                etd: '2',
                                note: ''
                            }
                        ]
                    },
                    {
                        service: 'ONS',
                        description: 'Over Night Service',
                        cost: [
                            {
                                value: weightMultiplier * 19000,
                                etd: '1',
                                note: ''
                            }
                        ]
                    }
                ];
                break;
        }
        
        // Simulate API delay
        setTimeout(function() {
            $scope.$apply(function() {
                $scope.shippingOptions = shippingCosts;
                $scope.loading.shipping = false;
            });
        }, 500);
    };

    // Select shipping option
    $scope.selectShippingOption = function(option, cost) {
        console.log('Selected shipping option:', option, 'with cost:', cost);
        $scope.selectedShipping = {
            service: option.service,
            description: option.description,
            cost: cost.value,
            etd: cost.etd
        };
        $scope.shippingCost = cost.value;
        $scope.updateTotal();
        
        // Update hidden inputs for form submission
        const shippingCostInput = document.getElementById('shipping_cost');
        if (shippingCostInput) {
            shippingCostInput.value = cost.value;
        }
        
        const shippingServiceInput = document.getElementById('shipping_service');
        if (shippingServiceInput) {
            shippingServiceInput.value = $scope.selectedCourier.toUpperCase() + ' ' + option.service + ' (' + option.description + ')';
        }
    };

    // Update total amount
    $scope.updateTotal = function() {
        $scope.total = $scope.subtotal + $scope.shippingCost;
        console.log('Updated total:', $scope.total);
    };

    // Format currency
    $scope.formatCurrency = function(amount) {
        return 'Rp' + amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    };

    // Watch for province changes
    $scope.$watch('selectedProvince', function(newVal, oldVal) {
        if (newVal !== oldVal) {
            console.log('Province changed from', oldVal, 'to', newVal);
            $scope.loadCities();
        }
    });

    // Watch for courier changes
    $scope.$watch('selectedCourier', function(newVal, oldVal) {
        if (newVal !== oldVal && $scope.selectedCity) {
            console.log('Courier changed from', oldVal, 'to', newVal);
            $scope.calculateShipping();
        }
    });

    // Watch for city changes
    $scope.$watch('selectedCity', function(newVal, oldVal) {
        if (newVal !== oldVal && $scope.selectedCourier) {
            console.log('City changed from', oldVal, 'to', newVal);
            $scope.calculateShipping();
        }
    });
}]);
