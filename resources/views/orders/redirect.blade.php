@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h1 class="mb-4">My Orders</h1>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body p-4 text-center">
                    <div class="d-grid">
                        <a href="{{ $angularRoute ?? '#!/orders' }}" class="btn btn-primary">
                            Go to My Orders
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-redirect after a short delay
    setTimeout(function() {
        window.location.href = "{{ $angularRoute ?? '#!/orders' }}";
    }, 1500);
</script>
@endsection 