@extends('layouts.admin')

@section('title', 'Danh mục - Tạp Hóa Xanh Admin')

@section('content')
                <!-- Top Navigation -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Quản lý danh mục</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                        <a href="{{ route('admin.categories.create') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Thêm danh mục
                        </a>
                    </div>
                </div>

                <!-- Categories Table -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Danh sách danh mục</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tên danh mục</th>
                                        <th>Slug</th>
                                        <th>Danh mục cha</th>
                                        <th>Ngày tạo</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody id="categories-table">
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
                                Hiển thị <span id="showing-info">0</span> trong tổng số <span id="total-info">0</span> danh mục
                            </div>
                            <nav>
                                <ul class="pagination pagination-sm mb-0" id="pagination-links">
                                    <!-- Pagination links will be generated here -->
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
@endsection

@push('scripts')
<script>
    let allCategories = [];
    let currentPage = 1;
    let totalPages = 1;
    let totalCategories = 0;
    const categoriesPerPage = 20;

    // Fetch categories data with pagination
    async function fetchCategories(page = 1) {
        try {
            const response = await fetch(`/api/categories?page=${page}&limit=${categoriesPerPage}`);
            const data = await response.json();
            
            allCategories = data.data || [];
            currentPage = data.pagination.current_page || 1;
            totalPages = data.pagination.last_page || 1;
            totalCategories = data.pagination.total || 0;
            
            const tbody = document.getElementById('categories-table');
            if (allCategories.length > 0) {
                tbody.innerHTML = allCategories.map(category => `
                    <tr>
                        <td>${category.id}</td>
                        <td>
                            <div class="fw-bold">${category.name}</div>
                            ${category.description ? `<small class="text-muted">${category.description}</small>` : ''}
                        </td>
                        <td>
                            <code>${category.slug || 'N/A'}</code>
                        </td>
                        <td>
                            ${category.parent ? `<span class="badge bg-secondary">${category.parent.name}</span>` : '<span class="text-muted">Danh mục gốc</span>'}
                        </td>
                        <td>${category.created_at ? new Date(category.created_at).toLocaleDateString('vi-VN') : 'N/A'}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="/admin/categories/${category.id}" class="btn btn-sm btn-info" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="/admin/categories/${category.id}/edit" class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-danger" onclick="deleteCategory(${category.id})" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Chưa có danh mục nào</td></tr>';
            }
            
            updatePagination();
        } catch (error) {
            console.error('Error fetching categories:', error);
            document.getElementById('categories-table').innerHTML = '<tr><td colspan="6" class="text-center text-danger">Lỗi khi tải dữ liệu</td></tr>';
        }
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
        showingInfo.textContent = allCategories.length;
        totalInfo.textContent = totalCategories;
        
        let paginationHTML = '';
        
        // Previous button
        if (currentPage > 1) {
            paginationHTML += `<li class="page-item">
                <a class="page-link" href="#" onclick="fetchCategories(${currentPage - 1})">Trước</a>
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
                    <a class="page-link" href="#" onclick="fetchCategories(${i})">${i}</a>
                </li>`;
            }
        }
        
        // Next button
        if (currentPage < totalPages) {
            paginationHTML += `<li class="page-item">
                <a class="page-link" href="#" onclick="fetchCategories(${currentPage + 1})">Sau</a>
            </li>`;
        } else {
            paginationHTML += `<li class="page-item disabled">
                <span class="page-link">Sau</span>
            </li>`;
        }
        
        paginationLinks.innerHTML = paginationHTML;
    }

    // Delete category
    async function deleteCategory(id) {
        if (confirm('Bạn có chắc chắn muốn xóa danh mục này? Các sản phẩm trong danh mục này sẽ được chuyển về "Chưa có danh mục nào".')) {
            try {
                const response = await fetch(`/api/categories/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    alert(result.message);
                    fetchCategories(currentPage); // Reload current page
                } else {
                    alert(result.message || 'Có lỗi xảy ra khi xóa danh mục');
                }
            } catch (error) {
                console.error('Error deleting category:', error);
                alert('Có lỗi xảy ra khi xóa danh mục');
            }
        }
    }

    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
        fetchCategories(1);
    });
</script>
@endpush
