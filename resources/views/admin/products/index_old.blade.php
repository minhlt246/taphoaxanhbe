@extends('layouts.admin')

@section('title', 'Sản phẩm - Tạp Hóa Xanh Admin')

@push('styles')
<style>
    .product-table {
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        border-radius: 0.35rem;
    }
    
    .product-table .table {
        margin-bottom: 0;
    }
    
    .product-table .table thead th {
        border-top: none;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }
    
    .product-table .table tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.02);
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }
    
    .badge {
        font-size: 0.8rem;
        padding: 0.4em 0.8em;
        border-radius: 6px;
        font-weight: 500;
        border: 1px solid transparent;
    }
    
    .badge.bg-success {
        background-color: #d4edda !important;
        color: #155724 !important;
        border-color: #c3e6cb !important;
    }
    
    .badge.bg-warning {
        background-color: #fff3cd !important;
        color: #856404 !important;
        border-color: #ffeaa7 !important;
    }
    
    .badge.bg-secondary {
        background-color: #e2e3e5 !important;
        color: #383d41 !important;
        border-color: #d6d8db !important;
    }
    
    .pagination-wrapper .pagination {
        margin-bottom: 0;
    }
    
    .pagination-wrapper .page-link {
        color: #5a5c69;
        border-color: #dddfeb;
        padding: 0.5rem 0.75rem;
    }
    
    .pagination-wrapper .page-link:hover {
        color: #3a3b45;
        background-color: #eaecf4;
        border-color: #dddfeb;
    }
    
    .pagination-wrapper .page-item.active .page-link {
        background-color: #4e73df;
        border-color: #4e73df;
    }
    
    .btn-group .btn {
        border-radius: 0.35rem;
        margin: 0 2px;
    }
    
    .product-image {
        border: 2px solid #e3e6f0;
        transition: all 0.2s ease;
    }
    
    .product-image:hover {
        border-color: #4e73df;
        transform: scale(1.05);
    }
    
    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .stats-number {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .stats-label {
        font-size: 0.875rem;
        opacity: 0.9;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Top Navigation -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Quản lý sản phẩm</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>
            <a href="{{ route('admin.products.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Thêm sản phẩm
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="stats-number">{{ $totalProducts }}</div>
                <div class="stats-label">Tổng sản phẩm</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="stats-number">{{ $inStockProducts }}</div>
                <div class="stats-label">Còn hàng</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="stats-number">{{ $outOfStockProducts }}</div>
                <div class="stats-label">Hết hàng</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="stats-number">{{ $activeProducts }}</div>
                <div class="stats-label">Đang hoạt động</div>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card product-table">
        <div class="card-header text-black d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-box me-2"></i>Danh sách sản phẩm
            </h6>
            
        </div>
        <div class="card-body p-0">
            @if($products->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Tên sản phẩm</th>
                                <th>Giá</th>
                                <th>Số lượng</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr>
                                    <td>{{ $product->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($product->images)
                                                @php
                                                    $images = json_decode($product->images, true);
                                                @endphp
                                                @if(is_array($images) && count($images) > 0)
                                                    <img src="{{ asset('images/product/' . $images[0]) }}" 
                                                         alt="{{ $product->name }}" 
                                                         class="me-2 product-image" 
                                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;"
                                                         onerror="this.src='{{ asset('images/product/default.jpg') }}'">
                                                @else
                                                    <img src="{{ asset('images/product/default.jpg') }}" 
                                                         alt="{{ $product->name }}" 
                                                         class="me-2 product-image" 
                                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                                @endif
                                            @else
                                                <img src="{{ asset('images/product/default.jpg') }}" 
                                                     alt="{{ $product->name }}" 
                                                     class="me-2 product-image" 
                                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                            @endif
                                            <div>
                                                <strong>{{ $product->name }}</strong>
                                                @if($product->category)
                                                    <br><small class="text-muted">{{ $product->category->name }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong class="text-primary">{{ number_format($product->price, 0, ',', '.') }}đ</strong>
                                        @if($product->discount > 0)
                                            <br><small class="text-success">Giảm: {{ number_format($product->discount, 0, ',', '.') }}đ</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="fs-6 me-2">
                                                <i class="fas fa-box me-1"></i>{{ number_format($product->quantity) }}
                                            </span>
                                            <span class="text-muted">
                                                / {{ number_format($product->totalQuantity) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $product->quantity > 0 ? 'success' : 'warning' }} fs-6">
                                            <i class="fas fa-{{ $product->quantity > 0 ? 'check-circle' : 'times-circle' }} me-1"></i>
                                            {{ $product->quantity > 0 ? 'Còn hàng' : 'Hết hàng' }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            {{ $product->createdAt ? \Carbon\Carbon::parse($product->createdAt)->format('d/m/Y H:i') : 'N/A' }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.products.show', $product) }}" 
                                               class="btn btn-sm btn-outline-info" 
                                               title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.products.edit', $product) }}" 
                                               class="btn btn-sm btn-outline-warning" 
                                               title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    title="Xóa"
                                                    onclick="deleteProduct({{ $product->id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4 p-3 bg-light rounded">
                    <div class="text-muted">
                        <i class="fas fa-info-circle me-2"></i>
                        Hiển thị <strong>{{ $products->firstItem() }}</strong> đến <strong>{{ $products->lastItem() }}</strong> 
                        trong tổng số <strong class="text-primary">{{ $products->total() }}</strong> sản phẩm
                    </div>
                    <div class="pagination-wrapper">
                        {{ $products->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Chưa có sản phẩm nào</h5>
                    <p class="text-muted">Hãy thêm sản phẩm đầu tiên của bạn</p>
                    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Thêm sản phẩm đầu tiên
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Delete product
    async function deleteProduct(id) {
        if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
            try {
                const response = await fetch(`/api/products/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                });
                
                if (response.ok) {
                    location.reload(); // Reload the page
                } else {
                    alert('Có lỗi xảy ra khi xóa sản phẩm');
                }
            } catch (error) {
                console.error('Error deleting product:', error);
                alert('Có lỗi xảy ra khi xóa sản phẩm');
            }
        }
    }
</script>
@endpush