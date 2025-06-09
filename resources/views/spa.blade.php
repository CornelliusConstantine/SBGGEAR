@extends('layouts.spa')

@section('content')
    <!-- Header -->
    <header id="main-header" ng-controller="HeaderController">
        <nav class="navbar navbar-expand-lg navbar-light bg-white py-3">
            <div class="container">
                <a class="navbar-brand fw-bold fs-4" href="/">SBGEAR</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="/">Shop</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/on-sale">On Sale</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/new-arrivals">New Arrivals</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/products">Products</a>
                        </li>
                    </ul>
                    <form class="d-flex mx-auto search-form" ng-submit="submitSearch()">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search for products..." ng-model="searchQuery" ng-keyup="searchProducts()">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <div class="search-results" ng-show="showSearchResults">
                            <div class="list-group">
                                <a href="/product/{{product.id}}" class="list-group-item list-group-item-action" ng-repeat="product in searchResults">
                                    <div class="d-flex align-items-center">
                                        <img ng-src="{{product.image_url || 'assets/img/product-placeholder.png'}}" class="search-thumbnail me-2">
                                        <div>
                                            <h6 class="mb-0">{{product.name}}</h6>
                                            <small class="text-muted">Rp{{product.price | number}}</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </form>
                    <div class="d-flex align-items-center">
                        <a href="/cart" class="position-relative me-3" ng-if="isLoggedIn">
                            <i class="fas fa-shopping-cart fs-5"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" ng-if="cartItemCount > 0">
                                {{cartItemCount}}
                            </span>
                        </a>
                        <div class="dropdown" ng-if="isLoggedIn">
                            <a class="dropdown-toggle text-dark" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas" ng-class="{'fa-user-circle': !isAdmin, 'fa-user-shield': isAdmin}" ng-style="{'color': isAdmin ? '#007bff' : '#212529'}"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <!-- Admin-specific menu items -->
                                <li ng-if="isAdmin"><span class="dropdown-item-text text-primary"><i class="fas fa-user-shield me-2"></i>Admin Account</span></li>
                                <li ng-if="isAdmin"><a class="dropdown-item" href="/admin">Admin Dashboard</a></li>
                                <li ng-if="isAdmin"><hr class="dropdown-divider"></li>
                                
                                <!-- Regular user menu items -->
                                <li ng-if="!isAdmin"><a class="dropdown-item" href="/profile">My Profile</a></li>
                                <li ng-if="!isAdmin"><a class="dropdown-item" href="/orders">My Orders</a></li>
                                <li ng-if="!isAdmin"><hr class="dropdown-divider"></li>
                                
                                <!-- Common menu items -->
                                <li><a class="dropdown-item" href="#" ng-click="logout()">Logout</a></li>
                            </ul>
                        </div>
                        <a href="/login" class="ms-3" ng-if="!isLoggedIn">
                            <i class="fas fa-user-circle fs-5"></i>
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main>
        <div ng-view></div>
    </main>

    <!-- Footer -->
    <footer class="bg-light mt-5 py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-3 mb-4 mb-md-0">
                    <h5 class="fw-bold mb-4">SBGEAR</h5>
                    <p class="small">We offer affordable, comfortable for safety gear.</p>
                    <div class="d-flex mt-3">
                        <a href="#" class="me-2 social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="me-2 social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="me-2 social-icon"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-md-3 mb-4 mb-md-0">
                    <h6 class="text-uppercase fw-bold mb-4">COMPANY</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="/about" class="text-decoration-none text-dark">About</a></li>
                        <li class="mb-2"><a href="/features" class="text-decoration-none text-dark">Features</a></li>
                        <li class="mb-2"><a href="/works" class="text-decoration-none text-dark">Works</a></li>
                        <li class="mb-2"><a href="/career" class="text-decoration-none text-dark">Career</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4 mb-md-0">
                    <h6 class="text-uppercase fw-bold mb-4">HELP</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="/customer-support" class="text-decoration-none text-dark">Customer Support</a></li>
                        <li class="mb-2"><a href="/delivery-details" class="text-decoration-none text-dark">Delivery Details</a></li>
                        <li class="mb-2"><a href="/terms" class="text-decoration-none text-dark">Terms & Conditions</a></li>
                        <li class="mb-2"><a href="/privacy-policy" class="text-decoration-none text-dark">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6 class="text-uppercase fw-bold mb-4">FAQ</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="/account" class="text-decoration-none text-dark">Account</a></li>
                        <li class="mb-2"><a href="/manage-shipping" class="text-decoration-none text-dark">Manage Deliveries</a></li>
                        <li class="mb-2"><a href="/orders" class="text-decoration-none text-dark">Orders</a></li>
                        <li class="mb-2"><a href="/payments" class="text-decoration-none text-dark">Payments</a></li>
                    </ul>
                </div>
            </div>
            <div class="row mt-4 pt-4 border-top">
                <div class="col-md-6 text-center text-md-start">
                    <p class="small text-muted mb-0">© 2023-2024 SBGEAR All Rights Reserved.</p>
                </div>
            </div>
        </div>
    </footer>
@endsection