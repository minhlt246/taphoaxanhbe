@extends('layouts.admin')

@section('title', 'Chi tiết danh mục - Tạp Hóa Xanh Admin')

@section('content')
                <!-- Top Navigation -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Chi tiết danh mục</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                        <a href="/admin/categories/{{ $category->id }}/edit" class="btn btn-sm btn-primary ms-2">
                            <i class="fas fa-edit"></i> Chỉnh sửa
                        </a>
                    </div>
                </div>

                <!-- Category Details -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Thông tin danh mục</h6>
                            </div>
                            <div class="card-body">
                                <div id="category-details">
                                    <div class="text-center">
                                        <div class="spinner-border" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Thống kê</h6>
                            </div>
                            <div class="card-body">
                                <div id="category-stats">
                                    <div class="text-center">
                                        <div class="spinner-border" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products in Category -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Sản phẩm trong danh mục</h6>
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
                                        <th>Thao tác</th>
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
                    </div>
                </div>
@endsection

@push('scripts')
<script>
        const categoryId = {{ $category->id }};
        
        // Load category details
        async function loadCategoryDetails() {
            try {
                const response = await fetch(`/api/categories/${categoryId}`);
                const result = await response.json();
                
                if (result.success) {
                    const category = result.data;
                    document.getElementById('category-details').innerHTML = `
                        <div class="row">
                            <div class="col-sm-3"><strong>ID:</strong></div>
                            <div class="col-sm-9">${category.id}</div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3"><strong>Tên danh mục:</strong></div>
                            <div class="col-sm-9">${category.name}</div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3"><strong>Slug:</strong></div>
                            <div class="col-sm-9">${category.slug || 'Không có'}</div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3"><strong>Danh mục cha:</strong></div>
                            <div class="col-sm-9">${category.parent ? category.parent.name : 'Không có'}</div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3"><strong>Hình ảnh:</strong></div>
                            <div class="col-sm-9">
                                ${category.image_url ? 
                                    `<img src="${category.image_url}" alt="${category.name}" class="img-thumbnail" style="max-width: 200px;">` : 
                                    'Không có'
                                }
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3"><strong>Ngày tạo:</strong></div>
                            <div class="col-sm-9">${category.created_at ? new Date(category.created_at).toLocaleString('vi-VN') : 'N/A'}</div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3"><strong>Cập nhật lần cuối:</strong></div>
                            <div class="col-sm-9">${category.updated_at ? new Date(category.updated_at).toLocaleString('vi-VN') : 'N/A'}</div>
                        </div>
                    `;
                    
                    // Update stats
                    document.getElementById('category-stats').innerHTML = `
                        <div class="text-center mb-3">
                            <h4 class="text-primary">${category.products ? category.products.length : 0}</h4>
                            <p class="text-muted mb-0">Sản phẩm</p>
                        </div>
                        <div class="text-center mb-3">
                            <h4 class="text-success">${category.children ? category.children.length : 0}</h4>
                            <p class="text-muted mb-0">Danh mục con</p>
                        </div>
                    `;
                    
                    // Load products
                    loadProducts(category.products || []);
                } else {
                    document.getElementById('category-details').innerHTML = '<div class="alert alert-danger">Không thể tải thông tin danh mục</div>';
                }
            } catch (error) {
                console.error('Error loading category:', error);
                document.getElementById('category-details').innerHTML = '<div class="alert alert-danger">Lỗi khi tải thông tin danh mục</div>';
            }
        }

        // Load products in category
        function loadProducts(products) {
            const tbody = document.getElementById('products-table');
            if (products && products.length > 0) {
                tbody.innerHTML = products.map(product => `
                    <tr>
                        <td>${product.id}</td>
                        <td>${product.name}</td>
                        <td>${product.price ? new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(product.price) : 'N/A'}</td>
                        <td>${product.quantity || 0}</td>
                        <td>
                            <span class="badge ${product.status === 'active' ? 'bg-success' : 'bg-secondary'}">
                                ${product.status === 'active' ? 'Hoạt động' : 'Không hoạt động'}
                            </span>
                        </td>
                        <td>
                            <a href="/admin/products/${product.id}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Chưa có sản phẩm nào trong danh mục này</td></tr>';
            }
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadCategoryDetails();
        });
</script>
@endpush

