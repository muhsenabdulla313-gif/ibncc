@extends('layouts.admin')

@section('body')
<div class="page-inner">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h3 class="fw-bold mb-1">Orders Management</h3>
            <p class="text-muted mb-0">Manage customer retail and wholesale orders, delivery addresses and statuses.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- FILTER PILLS --}}
    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('admin.orders.index') }}"
           class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline-primary' }} btn-round">
            All ({{ $counts['all'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'placed']) }}"
           class="btn btn-sm {{ request('status') === 'placed' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark' }} btn-round">
            <i class="fas fa-clock me-1"></i> Placed ({{ $counts['placed'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'processing']) }}"
           class="btn btn-sm {{ request('status') === 'processing' ? 'btn-info' : 'btn-outline-info' }} btn-round">
            <i class="fas fa-spinner me-1"></i> Processing ({{ $counts['processing'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'completed']) }}"
           class="btn btn-sm {{ request('status') === 'completed' ? 'btn-success' : 'btn-outline-success' }} btn-round">
            <i class="fas fa-check-circle me-1"></i> Completed ({{ $counts['completed'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'cancelled']) }}"
           class="btn btn-sm {{ request('status') === 'cancelled' ? 'btn-danger' : 'btn-outline-danger' }} btn-round">
            <i class="fas fa-ban me-1"></i> Cancelled ({{ $counts['cancelled'] }})
        </a>
    </div>

    {{-- SEARCH BAR --}}
    <div class="card card-round mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-2 align-items-center">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="col-md-9">
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search by order #, buyer name, phone, email...">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1"><i class="fa fa-search me-1"></i> Search</button>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-light">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- ORDERS TABLE --}}
    <div class="card card-round">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order #</th>
                        <th>Date</th>
                        <th>Buyer Info</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        @php
                            $badgeColor = match($order->status) {
                                'placed' => 'warning text-dark',
                                'processing' => 'info',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                default => 'secondary'
                            };
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="fw-bold text-dark text-decoration-none">
                                    {{ $order->order_no }}
                                </a>
                            </td>
                            <td>
                                {{ $order->created_at->format('d M Y, h:i A') }}
                                <small class="text-muted d-block">{{ $order->created_at->diffForHumans() }}</small>
                            </td>
                            <td>
                                <strong>{{ $order->buyer_name }}</strong>
                                <small class="text-muted d-block">{{ $order->buyer_phone }}</small>
                                @if($order->buyer_email)
                                    <small class="text-muted d-block">{{ $order->buyer_email }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $order->items->count() }} items</span>
                            </td>
                            <td>
                                <strong class="fs-6 text-primary">₹{{ number_format($order->total, 2) }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-{{ $badgeColor }}">{{ ucfirst($order->status) }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary btn-round">
                                    <i class="fa fa-eye me-1"></i> View Order
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="fas fa-shopping-bag fa-3x mb-3 text-muted"></i>
                                <p class="mb-0">No orders found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="card-footer d-flex justify-content-end p-2">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
