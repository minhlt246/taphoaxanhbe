@extends('layouts.admin')

@section('title', 'Chi tiết sản phẩm')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-eye me-2"></i>Chi tiết sản phẩm
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            @if($product->images)
                                @php $images = json_decode($product->images, true); @endphp
                                @if(is_array($images) && count($images) > 0)
                                    <img src="{{ asset('images/product/' . $images[0]) }}" 
                                         alt="{{ $product->name }}" 
                                         class="img-fluid rounded"
                                         style="max-height: 300px; object-fit: cover;"
                                         onerror="this.src='{{ asset('images/product/default.jpg') }}'">
                                @else
                                    <img src="{{ asset('images/product/default.jpg') }}" 
                                         alt="{{ $product->name }}" 
                                         class="img-fluid rounded"
                                         style="max-height: 300px; object-fit: cover;">
                                @endif
                            @else
                                <img src="{{ asset('images/product/default.jpg') }}" 
                                     alt="{{ $product->name }}" 
                                     class="img-fluid rounded"
                                     style="max-height: 300px; object-fit: cover;">
                            @endif
                        </div>
                        <div class="col-md-8">
                            <h3 class="mb-3">{{ $product->name }}</h3>
                            
                            <div class="row mb-3">
                                <div class="col-sm-3"><strong>ID:</strong></div>
                                <div class="col-sm-9">{{ $product->id }}</div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-sm-3"><strong>Mô tả:</strong></div>
                                <div class="col-sm-9">{{ $product->description }}</div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-sm-3"><strong>Giá:</strong></div>
                                <div class="col-sm-9">
                                    <span class="h4 text-primary">{{ number_format($product->price, 0, ',', '.') }}đ</span>
                                    @if($product->discount > 0)
                                        <br><small class="text-success">Giảm: {{ number_format($product->discount, 0, ',', '.') }}đ</small>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-sm-3"><strong>Số lượng:</strong></div>
                                <div class="col-sm-9">
                                    <span class="badge bg-{{ $product->quantity > 0 ? 'success' : 'warning' }} fs-6">
                                        <i class="fas fa-box me-1"></i>{{ number_format($product->quantity) }}
                                    </span>
                                    @if($product->quantity > 0)
                                        <br><small class="text-success">
                                            <i class="fas fa-check-circle me-1"></i>Còn hàng
                                        </small>
                                    @else
                                        <br><small class="text-danger">
                                            <i class="fas fa-times-circle me-1"></i>Hết hàng
                                        </small>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-sm-3"><strong>Trạng thái:</strong></div>
                                <div class="col-sm-9">
                                    <span class="badge bg-{{ $product->quantity > 0 ? 'success' : 'warning' }} fs-6">
                                        <i class="fas fa-{{ $product->quantity > 0 ? 'check-circle' : 'times-circle' }} me-1"></i>
                                        {{ $product->quantity > 0 ? 'Còn hàng' : 'Hết hàng' }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-sm-3"><strong>Danh mục:</strong></div>
                                <div class="col-sm-9">
                                    @if($product->category)
                                        <span class="badge bg-info">{{ $product->category->name }}</span>
                                    @else
                                        <span class="text-muted">Chưa phân loại</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-sm-3"><strong>Ngày tạo:</strong></div>
                                <div class="col-sm-9">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        {{ $product->createdAt ? \Carbon\Carbon::parse($product->createdAt)->format('d/m/Y H:i') : 'N/A' }}
                                    </small>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-sm-3"><strong>Ngày cập nhật:</strong></div>
                                <div class="col-sm-9">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        {{ $product->updatedAt ? \Carbon\Carbon::parse($product->updatedAt)->format('d/m/Y H:i') : 'N/A' }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Quay lại
                        </a>
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i>Chỉnh sửa
                        </a>
                        <button type="button" class="btn btn-danger" onclick="deleteProduct({{ $product->id }})">
                            <i class="fas fa-trash me-1"></i>Xóa
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deleteProduct(id) {
    if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
        fetch(`/admin/products/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                alert(data.message);
                window.location.href = '/admin/products';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi xóa sản phẩm');
        });
    }
}
</script>
@endsection
