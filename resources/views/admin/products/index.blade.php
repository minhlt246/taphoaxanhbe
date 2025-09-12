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

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng sản phẩm
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-products">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Còn hàng
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="in-stock-products">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Hết hàng
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="out-of-stock-products">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Đang hoạt động
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="active-products">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-play-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card shadow mb-4 product-table">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách sản phẩm</h6>
            <div class="d-flex gap-2 align-items-center">
                <label class="form-label mb-0 me-2">Lọc theo trạng thái:</label>
                <select class="form-select form-select-sm" id="statusFilter" onchange="filterByStatus()" style="width: 150px;">
                    <option value="">Tất cả</option>
                    <option value="active">Hoạt động</option>
                    <option value="inactive">Tạm dừng</option>
                </select>
            </div>
        </div>
        <div class="card-body">
                <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên sản phẩm</th>
                                <th>Giá</th>
                                <th>Số lượng</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                            </tr>
                        </thead>
                    <tbody id="products-table">
                        <tr>
                            <td colspan="6" class="text-center"> 
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </td>
                                </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
            <div id="pagination-container" class="d-flex justify-content-between align-items-center mt-3" style="display: none !important;">
                    <div class="text-muted">
                    Hiển thị <span id="showing-info">0</span> trong tổng số <span id="total-info">0</span> sản phẩm
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="pagination-links">
                        <!-- Pagination links will be generated here -->
                    </ul>
                </nav>
                </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let allProducts = []; // Lưu trữ tất cả sản phẩm
    let currentPage = 1;
    let totalPages = 1;
    let totalProducts = 0;
    const productsPerPage = 20;
    
    // Fetch products data with pagination
    async function fetchProducts(page = 1) {
        try {
            const statusFilter = document.getElementById('statusFilter').value;
            let url = `/api/products?page=${page}&limit=${productsPerPage}`;
            if (statusFilter) {
                url += `&status=${statusFilter}`;
            }
            
            const response = await fetch(url);
            const data = await response.json();
            
            // Lưu trữ dữ liệu gốc
            allProducts = data.products || [];
            currentPage = data.pagination.page || 1;
            totalPages = data.pagination.totalPages || 1;
            totalProducts = data.pagination.total || 0;
            
            const tbody = document.getElementById('products-table');
            if (allProducts.length > 0) {
                tbody.innerHTML = allProducts.map(product => `
                    <tr>
                        <td>${product.id}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                ${getProductImage(product)}
                                <div>
                                    <div class="fw-bold">${product.name}</div>
                                    <small class="text-muted">${product.category?.name || 'Không có danh mục'}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-primary">${parseFloat(product.price).toLocaleString()} ₫</div>
                            ${product.discount > 0 ? `<small class="text-success">Giảm ${product.discount}%</small>` : ''}
                        </td>
                        <td>
                            <span class="badge ${product.actual_quantity > 0 ? 'bg-success' : 'bg-danger'}">
                                ${product.actual_quantity}/${product.total_quantity || product.quantity}
                            </span>
                        </td>
                        <td>
                            <span class="badge ${product.actual_quantity > 0 ? 'bg-success' : 'bg-secondary'}">
                                ${product.actual_quantity > 0 ? 'Hoạt động' : 'Tạm dừng'}
                            </span>
                        </td>
                        <td>${product.createdAt ? new Date(product.createdAt).toLocaleDateString('vi-VN') : 'N/A'}</td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Chưa có sản phẩm nào</td></tr>';
            }
            
            updatePagination();
        } catch (error) {
            console.error('Error fetching products:', error);
            document.getElementById('products-table').innerHTML = '<tr><td colspan="6" class="text-center text-danger">Lỗi khi tải dữ liệu</td></tr>';
        }
    }

    // Get product image HTML
    function getProductImage(product) {
        if (product.images) {
            try {
                const images = JSON.parse(product.images);
                if (Array.isArray(images) && images.length > 0) {
                    return `<img src="/images/product/${images[0]}" 
                                 alt="${product.name}" 
                                 class="me-2 product-image" 
                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;"
                                 onerror="this.src='/images/product/default.jpg'">`;
                }
            } catch (e) {
                console.error('Error parsing product images:', e);
            }
        }
        return `<img src="/images/product/default.jpg" 
                     alt="${product.name}" 
                     class="me-2 product-image" 
                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">`;
    }

    // Update pagination UI
    function updatePagination() {
        const container = document.getElementById('pagination-container');
        const showingInfo = document.getElementById('showing-info');
        const totalInfo = document.getElementById('total-info');
        const paginationLinks = document.getElementById('pagination-links');
        
        if (totalPages <= 1) {
            container.style.display = 'none';
            return;
        }
        
        container.style.display = 'flex';
        showingInfo.textContent = allProducts.length;
        totalInfo.textContent = totalProducts;
        
        let paginationHTML = '';
        
        // Previous button
        if (currentPage > 1) {
            paginationHTML += `<li class="page-item">
                <a class="page-link" href="#" onclick="fetchProducts(${currentPage - 1})">Trước</a>
            </li>`;
        } else {
            paginationHTML += `<li class="page-item disabled">
                <span class="page-link">Trước</span>
            </li>`;
        }
        
        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            if (i === currentPage) {
                paginationHTML += `<li class="page-item active">
                    <span class="page-link">${i}</span>
                </li>`;
            } else {
                paginationHTML += `<li class="page-item">
                    <a class="page-link" href="#" onclick="fetchProducts(${i})">${i}</a>
                </li>`;
            }
        }
        
        // Next button
        if (currentPage < totalPages) {
            paginationHTML += `<li class="page-item">
                <a class="page-link" href="#" onclick="fetchProducts(${currentPage + 1})">Sau</a>
            </li>`;
        } else {
            paginationHTML += `<li class="page-item disabled">
                <span class="page-link">Sau</span>
            </li>`;
        }
        
        paginationLinks.innerHTML = paginationHTML;
    }

    // Filter by status
    function filterByStatus() {
        fetchProducts(1); // Reset về trang đầu khi filter
    }

    // Load statistics
    async function loadStatistics() {
        try {
            const response = await fetch('/api/products-statistics');
            const data = await response.json();
            
            document.getElementById('total-products').textContent = data.total || 0;
            document.getElementById('in-stock-products').textContent = data.in_stock || 0;
            document.getElementById('out-of-stock-products').textContent = data.out_of_stock || 0;
            document.getElementById('active-products').textContent = data.active || 0;
        } catch (error) {
            console.error('Error loading statistics:', error);
        }
    }

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
                    fetchProducts(currentPage); // Reload current page
                    loadStatistics(); // Reload statistics
                } else {
                    alert('Có lỗi xảy ra khi xóa sản phẩm');
                }
            } catch (error) {
                console.error('Error deleting product:', error);
                alert('Có lỗi xảy ra khi xóa sản phẩm');
            }
        }
    }

    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
        fetchProducts();
        loadStatistics();
    });
</script>
@endpush
