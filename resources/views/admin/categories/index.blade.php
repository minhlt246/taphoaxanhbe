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
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Danh sách danh mục</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tên danh mục</th>
                                        <th>Mô tả</th>
                                        <th>Ngày tạo</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody id="categories-table">
                                    <tr>
                                        <td colspan="5" class="text-center">
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
        // Fetch categories data
        async function fetchCategories() {
            try {
                const response = await fetch('/api/categories');
                const data = await response.json();
                
                const tbody = document.getElementById('categories-table');
                if (data.data && data.data.length > 0) {
                    tbody.innerHTML = data.data.map(category => `
                        <tr>
                            <td>${category.id}</td>
                            <td>${category.name}</td>
                            <td>${category.slug || 'Không có slug'}</td>
                            <td>${category.created_at ? new Date(category.created_at).toLocaleDateString('vi-VN') : 'N/A'}</td>
                            <td>
                                <a href="/admin/categories/${category.id}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="/admin/categories/${category.id}/edit" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-danger" onclick="deleteCategory(${category.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `).join('');
                } else {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Chưa có danh mục nào</td></tr>';
                }
            } catch (error) {
                console.error('Error fetching categories:', error);
                document.getElementById('categories-table').innerHTML = '<tr><td colspan="5" class="text-center text-danger">Lỗi khi tải dữ liệu</td></tr>';
            }
        }

        // Delete category
        async function deleteCategory(id) {
            if (confirm('Bạn có chắc chắn muốn xóa danh mục này?')) {
                try {
                    const response = await fetch(`/api/categories/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json'
                        }
                    });
                    
                    if (response.ok) {
                        fetchCategories(); // Reload the table
                    } else {
                        alert('Có lỗi xảy ra khi xóa danh mục');
                    }
                } catch (error) {
                    console.error('Error deleting category:', error);
                    alert('Có lỗi xảy ra khi xóa danh mục');
                }
            }
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            fetchCategories();
        });
</script>
@endpush
