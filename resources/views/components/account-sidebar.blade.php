<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">My Account</h5>
    </div>
    <div class="list-group list-group-flush">
        <a href="{{ route('account') }}" class="list-group-item list-group-item-action {{ request()->routeIs('account') ? 'active' : '' }}">
            <i class="bi bi-person me-2"></i> Account Overview
        </a>
        <a href="{{ route('orders.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('orders.*') ? 'active' : '' }}">
            <i class="bi bi-box-seam me-2"></i> My Orders
        </a>
        <a href="{{ route('account.edit') }}" class="list-group-item list-group-item-action {{ request()->routeIs('account.edit') ? 'active' : '' }}">
            <i class="bi bi-pencil-square me-2"></i> Edit Profile
        </a>
        <a href="#" class="list-group-item list-group-item-action" 
            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="bi bi-box-arrow-right me-2"></i> Logout
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </div>
</div> 