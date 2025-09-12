@extends('layouts.admin')

@section('title', 'Người dùng - Tạp Hóa Xanh Admin')

@push('styles')
<style>
    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }
</style>
@endpush

@section('content')
<!-- Top Navigation -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Quản lý người dùng</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Thêm người dùng
        </a>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách người dùng</h6>
        <div class="d-flex gap-2 align-items-center">
            <label class="form-label mb-0 me-2">Tìm kiếm:</label>
            <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Tên hoặc email..." style="width: 200px;">
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên</th>
                        <th>Email</th>
                        <th>Vai trò</th>
                        <th>Ngày tạo</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody id="users-table">
                    <tr>
                        <td colspan="7" class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-4 p-3 bg-light rounded" id="pagination-container" style="display: none !important;">
            <div class="text-muted">
                <i class="fas fa-info-circle me-2"></i>
                Hiển thị <strong id="pagination-info">0</strong> người dùng
            </div>
            <div class="pagination-wrapper">
                <nav aria-label="User pagination">
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
    let allUsers = []; // Lưu trữ tất cả người dùng
    let currentPage = 1;
    let totalPages = 1;
    let totalUsers = 0;
    
    // Fetch users data with pagination
    async function fetchUsers(page = 1) {
        try {
            const response = await fetch(`/api/users?page=${page}&limit=10`);
            const data = await response.json();
            
            // Lưu trữ dữ liệu gốc
            allUsers = data.data || [];
            currentPage = data.pagination.current_page;
            totalPages = data.pagination.last_page;
            totalUsers = data.pagination.total;
            
            const tbody = document.getElementById('users-table');
            if (allUsers.length > 0) {
                tbody.innerHTML = allUsers.map(user => `
                    <tr>
                        <td>${user.id}</td>
                        <td>${user.name}</td>
                        <td>${user.email}</td>
                        <td>
                            <span class="badge ${getRoleBadgeClass(user.role || 'USER')}">
                                ${getRoleText(user.role || 'USER')}
                            </span>
                        </td>
                        <td>${user.created_at ? new Date(user.created_at).toLocaleDateString('vi-VN') : 'N/A'}</td>
                        <td>
                            <span class="badge ${getStatusBadgeClass(user.status || 'active')}">
                                ${getStatusText(user.status || 'active')}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="/admin/users/${user.id}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="/admin/users/${user.id}/edit" class="btn btn-sm btn-outline-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(${user.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
                
                // Update pagination
                updatePagination();
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Chưa có người dùng nào</td></tr>';
                document.getElementById('pagination-container').style.display = 'none';
            }
        } catch (error) {
            console.error('Error fetching users:', error);
            document.getElementById('users-table').innerHTML = '<tr><td colspan="7" class="text-center text-danger">Lỗi khi tải dữ liệu</td></tr>';
        }
    }

    // Update pagination
    function updatePagination() {
        const paginationContainer = document.getElementById('pagination-container');
        const paginationInfo = document.getElementById('pagination-info');
        const paginationLinks = document.getElementById('pagination-links');
        
        // Show pagination container
        paginationContainer.style.display = 'flex';
        
        // Update pagination info
        const startItem = (currentPage - 1) * 10 + 1;
        const endItem = Math.min(currentPage * 10, totalUsers);
        paginationInfo.textContent = `${startItem} đến ${endItem} trong tổng số ${totalUsers}`;
        
        // Generate pagination links
        let paginationHTML = '';
        
        // Previous button
        if (currentPage > 1) {
            paginationHTML += `<li class="page-item">
                <a class="page-link" href="#" onclick="fetchUsers(${currentPage - 1}); return false;">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>`;
        } else {
            paginationHTML += `<li class="page-item disabled">
                <span class="page-link"><i class="fas fa-chevron-left"></i></span>
            </li>`;
        }
        
        // Page numbers
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);
        
        for (let i = startPage; i <= endPage; i++) {
            if (i === currentPage) {
                paginationHTML += `<li class="page-item active">
                    <span class="page-link">${i}</span>
                </li>`;
            } else {
                paginationHTML += `<li class="page-item">
                    <a class="page-link" href="#" onclick="fetchUsers(${i}); return false;">${i}</a>
                </li>`;
            }
        }
        
        // Next button
        if (currentPage < totalPages) {
            paginationHTML += `<li class="page-item">
                <a class="page-link" href="#" onclick="fetchUsers(${currentPage + 1}); return false;">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>`;
        } else {
            paginationHTML += `<li class="page-item disabled">
                <span class="page-link"><i class="fas fa-chevron-right"></i></span>
            </li>`;
        }
        
        paginationLinks.innerHTML = paginationHTML;
    }

    // Search users (simplified for pagination)
    function searchUsers() {
        // For now, just reload the current page
        // In a real implementation, you'd want to pass search terms to the API
        fetchUsers(currentPage);
    }

    function getRoleBadgeClass(role) {
        switch(role) {
            case 'ADMIN': return 'bg-danger';
            case 'USER': return 'bg-primary';
            default: return 'bg-secondary';
        }
    }

    function getRoleText(role) {
        switch(role) {
            case 'ADMIN': return 'Quản trị viên';
            case 'USER': return 'Người dùng';
            default: return 'Không xác định';
        }
    }

    function getStatusBadgeClass(status) {
        switch(status) {
            case 'active': return 'bg-success';
            case 'suspended': return 'bg-warning';
            case 'inactive': return 'bg-danger';
            default: return 'bg-secondary';
        }
    }

    function getStatusText(status) {
        switch(status) {
            case 'active': return 'Hoạt động';
            case 'suspended': return 'Tạm ngưng';
            case 'inactive': return 'Ngưng hoạt động';
            default: return 'Không xác định';
        }
    }

    // Delete user
    async function deleteUser(id) {
        if (confirm('Bạn có chắc chắn muốn xóa người dùng này?')) {
            try {
                const response = await fetch(`/admin/users/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    }
                });
                
                if (response.ok) {
                    alert('Người dùng đã được xóa thành công');
                    fetchUsers(currentPage); // Reload the current page
                } else {
                    alert('Có lỗi xảy ra khi xóa người dùng');
                }
            } catch (error) {
                console.error('Error deleting user:', error);
                alert('Có lỗi xảy ra khi xóa người dùng');
            }
        }
    }

    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
        fetchUsers(1); // Load first page
        
        // Add search functionality
        document.getElementById('searchInput').addEventListener('input', searchUsers);
    });
</script>
@endpush
