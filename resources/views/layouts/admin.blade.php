<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - SBGEAR Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Admin CSS -->
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fa;
        }
        
        #layoutSidenav {
            display: flex;
        }
        
        #layoutSidenav_nav {
            flex-basis: 225px;
            flex-shrink: 0;
            transition: transform .15s ease-in-out;
            z-index: 1038;
            transform: translateX(0);
        }
        
        #layoutSidenav_content {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-width: 0;
            flex-grow: 1;
            min-height: calc(100vh - 56px);
            margin-left: -225px;
        }
        
        .sb-sidenav-toggled #layoutSidenav_nav {
            transform: translateX(-225px);
        }
        
        .sb-sidenav-toggled #layoutSidenav_content {
            margin-left: -225px;
        }
        
        @media (min-width: 992px) {
            #layoutSidenav_nav {
                transform: translateX(0);
            }
            
            #layoutSidenav_content {
                margin-left: 0;
                transition: margin .15s ease-in-out;
            }
            
            .sb-sidenav-toggled #layoutSidenav_nav {
                transform: translateX(0);
            }
            
            .sb-sidenav-toggled #layoutSidenav_content {
                margin-left: 225px;
            }
        }
        
        .sb-nav-fixed #layoutSidenav #layoutSidenav_nav {
            width: 225px;
            height: 100vh;
            z-index: 1038;
        }
        
        .sb-nav-fixed #layoutSidenav #layoutSidenav_nav .sb-sidenav {
            padding-top: 56px;
        }
        
        .sb-nav-fixed #layoutSidenav #layoutSidenav_nav .sb-sidenav .sb-sidenav-menu {
            overflow-y: auto;
        }
        
        .sb-nav-fixed #layoutSidenav #layoutSidenav_content {
            padding-left: 225px;
            top: 56px;
        }
        
        .sb-sidenav {
            display: flex;
            flex-direction: column;
            height: 100%;
            flex-wrap: nowrap;
        }
        
        .sb-sidenav .sb-sidenav-menu {
            flex-grow: 1;
        }
        
        .sb-sidenav .sb-sidenav-menu .nav {
            flex-direction: column;
            flex-wrap: nowrap;
        }
        
        .sb-sidenav .sb-sidenav-menu .nav .sb-sidenav-menu-heading {
            padding: 1.75rem 1rem 0.75rem;
            font-size: 0.75rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .sb-sidenav .sb-sidenav-menu .nav .nav-link {
            display: flex;
            align-items: center;
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
            position: relative;
        }
        
        .sb-sidenav .sb-sidenav-menu .nav .nav-link .sb-nav-link-icon {
            font-size: 0.9rem;
            padding-right: 0.5rem;
        }
        
        .sb-sidenav .sb-sidenav-menu .nav .nav-link .sb-sidenav-collapse-arrow {
            display: inline-block;
            margin-left: auto;
            transition: transform .15s ease;
        }
        
        .sb-sidenav .sb-sidenav-menu .nav .nav-link.collapsed .sb-sidenav-collapse-arrow {
            transform: rotate(-90deg);
        }
        
        .sb-sidenav .sb-sidenav-menu .nav .sb-sidenav-menu-nested {
            margin-left: 1.5rem;
            flex-direction: column;
        }
        
        .sb-sidenav .sb-sidenav-footer {
            padding: 0.75rem;
            flex-shrink: 0;
        }
        
        .sb-sidenav-dark {
            background-color: #212529;
            color: rgba(255, 255, 255, 0.5);
        }
        
        .sb-sidenav-dark .sb-sidenav-menu .sb-sidenav-menu-heading {
            color: rgba(255, 255, 255, 0.25);
        }
        
        .sb-sidenav-dark .sb-sidenav-menu .nav-link {
            color: rgba(255, 255, 255, 0.5);
        }
        
        .sb-sidenav-dark .sb-sidenav-menu .nav-link .sb-nav-link-icon {
            color: rgba(255, 255, 255, 0.25);
        }
        
        .sb-sidenav-dark .sb-sidenav-menu .nav-link:hover {
            color: #fff;
        }
        
        .sb-sidenav-dark .sb-sidenav-menu .nav-link.active {
            color: #fff;
        }
        
        .sb-sidenav-dark .sb-sidenav-menu .nav-link.active .sb-nav-link-icon {
            color: #fff;
        }
        
        .sb-sidenav-dark .sb-sidenav-footer {
            background-color: #343a40;
        }
    </style>
    
    @yield('styles')
</head>
<body class="sb-nav-fixed">
    <!-- Top Navigation -->
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <!-- Navbar Brand-->
        <a class="navbar-brand ps-3" href="/admin">SBGEAR Admin</a>
        
        <!-- Sidebar Toggle-->
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- Navbar Search-->
        <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
            <div class="input-group">
                <input class="form-control" type="text" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch" />
                <button class="btn btn-primary" id="btnNavbarSearch" type="button"><i class="fas fa-search"></i></button>
            </div>
        </form>
        
        <!-- Navbar-->
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user fa-fw"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="/account">Settings</a></li>
                    <li><hr class="dropdown-divider" /></li>
                    <li>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                        <a class="dropdown-item" href="{{ route('logout') }}" 
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Logout
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
    
    <div id="layoutSidenav">
        <!-- Sidebar Navigation -->
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading">Core</div>
                        <a class="nav-link" href="/admin">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Dashboard
                        </a>
                        
                        <div class="sb-sidenav-menu-heading">Management</div>
                        <a class="nav-link" href="/admin/products">
                            <div class="sb-nav-link-icon"><i class="fas fa-box"></i></div>
                            Products
                        </a>
                        <a class="nav-link" href="/admin/categories">
                            <div class="sb-nav-link-icon"><i class="fas fa-tags"></i></div>
                            Categories
                        </a>
                        <a class="nav-link" href="/admin/orders">
                            <div class="sb-nav-link-icon"><i class="fas fa-shopping-cart"></i></div>
                            Orders
                        </a>
                        <a class="nav-link" href="/admin/users">
                            <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                            Users
                        </a>
                        
                        <div class="sb-sidenav-menu-heading">Reports</div>
                        <a class="nav-link" href="/admin/reports/sales">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-line"></i></div>
                            Sales Reports
                        </a>
                        <a class="nav-link" href="/admin/reports/inventory">
                            <div class="sb-nav-link-icon"><i class="fas fa-warehouse"></i></div>
                            Inventory Reports
                        </a>
                        
                        <div class="sb-sidenav-menu-heading">System</div>
                        <a class="nav-link" href="/admin/settings">
                            <div class="sb-nav-link-icon"><i class="fas fa-cogs"></i></div>
                            Settings
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small">Logged in as:</div>
                    Administrator
                </div>
            </nav>
        </div>
        
        <!-- Page Content -->
        <div id="layoutSidenav_content">
            <main>
                @yield('content')
            </main>
            
            <!-- Footer -->
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; SBGEAR 2023-2024</div>
                        <div>
                            <a href="#">Privacy Policy</a>
                            &middot;
                            <a href="#">Terms &amp; Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Core admin JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle the side navigation
            const sidebarToggle = document.getElementById('sidebarToggle');
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.body.classList.toggle('sb-sidenav-toggled');
                    localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sb-sidenav-toggled'));
                });
            }
            
            // Set active nav item based on current URL
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.nav-link');
            
            navLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (currentPath === href || (href !== '/admin' && currentPath.startsWith(href))) {
                    link.classList.add('active');
                }
            });
        });
    </script>
    
    @yield('scripts')
</body>
</html> 