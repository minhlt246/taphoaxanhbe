@extends('layouts.admin')

@section('title', 'Quản lý tin tức')

@push('styles')
<style>
    .news-card {
        transition: transform 0.2s;
    }
    .news-card:hover {
        transform: translateY(-2px);
    }
    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    .dropdown-menu {
        min-width: 120px;
    }
    .table-responsive {
        border-radius: 0.375rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quản lý tin tức</h1>
        <a href="{{ route('admin.news.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm tin tức mới
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng tin tức
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-news">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-newspaper fa-2x text-gray-300"></i>
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
                                Đã xuất bản
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="published-news">0</div>
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
                                Chờ duyệt
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="pending-news">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Bị từ chối
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="rejected-news">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- News Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách tin tức</h6>
            <div class="d-flex gap-2 align-items-center">
                <label class="form-label mb-0 me-2">Lọc theo trạng thái:</label>
                <select class="form-select form-select-sm" id="statusFilter" onchange="filterByStatus()" style="width: 150px;">
                    <option value="">Tất cả</option>
                    <option value="published">Đã xuất bản</option>
                    <option value="approved">Đã duyệt</option>
                    <option value="pending">Chờ duyệt</option>
                    <option value="rejected">Bị từ chối</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tiêu đề</th>
                            <th>Tác giả</th>
                            <th>Danh mục</th>
                            <th>Trạng thái</th>
                            <th>Lượt xem</th>
                            <th>Lượt thích</th>
                            <th>Ngày tạo</th>
                        </tr>
                    </thead>
                    <tbody id="news-table">
                        <!-- News data will be loaded here via JavaScript -->
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    <i class="fas fa-info-circle me-2"></i>
                    Hiển thị <strong id="pagination-info">0</strong> tin tức
                </div>
                <div class="pagination-wrapper">
                    <nav aria-label="News pagination">
                        <ul class="pagination pagination-sm mb-0" id="pagination-links">
                            <!-- Pagination links will be generated here -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let allNews = []; // Lưu trữ tất cả tin tức
    let currentPage = 1;
    let totalPages = 1;
    let totalNews = 0;
    const newsPerPage = 20;
    
    // Fetch news data with pagination
    async function fetchNews(page = 1) {
        try {
            const statusFilter = document.getElementById('statusFilter').value;
            let url = `/api/news?page=${page}&limit=${newsPerPage}`;
            if (statusFilter) {
                url += `&status=${statusFilter}`;
            }
            
            const response = await fetch(url);
            const data = await response.json();
            
            // Lưu trữ dữ liệu gốc
            allNews = data.data || [];
            currentPage = data.current_page || 1;
            totalPages = data.last_page || 1;
            totalNews = data.total || 0;
            
            const tbody = document.getElementById('news-table');
            if (allNews.length > 0) {
                tbody.innerHTML = allNews.map(news => `
                    <tr class="news-card" onclick="showDropdownMenu(event, ${news.id})">
                        <td>${news.id}</td>
                        <td>
                            <div class="fw-bold">${news.title}</div>
                            <small class="text-muted">${news.summary ? news.summary.substring(0, 100) + '...' : ''}</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div>
                                    <div class="fw-bold">${news.author_name || 'N/A'}</div>
                                    <small class="text-muted">ID: ${news.author_id}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-info">${news.category || 'N/A'}</span>
                        </td>
                        <td>
                            <span class="badge status-badge ${getStatusBadgeClass(news)}">
                                ${getStatusText(news)}
                            </span>
                        </td>
                        <td>${news.view_count || 0}</td>
                        <td>${news.like_count || 0}</td>
                        <td>${news.createdAt ? new Date(news.createdAt).toLocaleDateString('vi-VN') : 'N/A'}</td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Không có tin tức nào</td></tr>';
            }
            
            updatePagination();
            updateStatistics();
            
        } catch (error) {
            console.error('Error fetching news:', error);
            document.getElementById('news-table').innerHTML = '<tr><td colspan="8" class="text-center text-danger">Lỗi khi tải dữ liệu</td></tr>';
        }
    }
    
    // Filter by status
    function filterByStatus() {
        fetchNews(1);
    }
    
    // Get status badge class
    function getStatusBadgeClass(news) {
        if (news.is_rejected) {
            return 'bg-danger';
        } else if (news.is_approved && news.is_published) {
            return 'bg-success';
        } else if (news.is_approved) {
            return 'bg-warning';
        } else {
            return 'bg-secondary';
        }
    }
    
    // Get status text
    function getStatusText(news) {
        if (news.is_rejected) {
            return 'Từ chối';
        } else if (news.is_approved && news.is_published) {
            return 'Đã xuất bản';
        } else if (news.is_approved) {
            return 'Đã duyệt';
        } else {
            return 'Chờ duyệt';
        }
    }
    
    // Show dropdown menu
    function showDropdownMenu(event, newsId) {
        event.stopPropagation();
        
        // Remove existing dropdowns
        document.querySelectorAll('.dropdown-menu').forEach(menu => menu.remove());
        
        const row = event.currentTarget;
        const rect = row.getBoundingClientRect();
        
        const dropdown = document.createElement('div');
        dropdown.className = 'dropdown-menu show';
        dropdown.style.position = 'fixed';
        dropdown.style.left = rect.right - 120 + 'px';
        dropdown.style.top = rect.bottom + 'px';
        dropdown.style.zIndex = '1000';
        
        dropdown.innerHTML = `
            <a class="dropdown-item" href="/admin/news/${newsId}">
                <i class="fas fa-eye me-2"></i>Xem chi tiết
            </a>
            <a class="dropdown-item" href="/admin/news/${newsId}/edit">
                <i class="fas fa-edit me-2"></i>Chỉnh sửa
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item text-success" href="#" onclick="approveNews(${newsId})">
                <i class="fas fa-check me-2"></i>Duyệt
            </a>
            <a class="dropdown-item text-warning" href="#" onclick="publishNews(${newsId})">
                <i class="fas fa-upload me-2"></i>Xuất bản
            </a>
            <a class="dropdown-item text-danger" href="#" onclick="rejectNews(${newsId})">
                <i class="fas fa-times me-2"></i>Từ chối
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item text-danger" href="#" onclick="deleteNews(${newsId})">
                <i class="fas fa-trash me-2"></i>Xóa
            </a>
        `;
        
        document.body.appendChild(dropdown);
        
        // Close dropdown when clicking outside
        setTimeout(() => {
            document.addEventListener('click', function closeDropdown() {
                dropdown.remove();
                document.removeEventListener('click', closeDropdown);
            });
        }, 100);
    }
    
    // Approve news
    async function approveNews(newsId) {
        if (confirm('Bạn có chắc chắn muốn duyệt tin tức này?')) {
            try {
                const response = await fetch(`/admin/news/${newsId}/approve`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                });
                
                if (response.ok) {
                    alert('Tin tức đã được duyệt!');
                    fetchNews(currentPage);
                } else {
                    alert('Có lỗi xảy ra khi duyệt tin tức!');
                }
            } catch (error) {
                console.error('Error approving news:', error);
                alert('Có lỗi xảy ra khi duyệt tin tức!');
            }
        }
    }
    
    // Publish news
    async function publishNews(newsId) {
        if (confirm('Bạn có chắc chắn muốn xuất bản tin tức này?')) {
            try {
                const response = await fetch(`/admin/news/${newsId}/publish`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                });
                
                if (response.ok) {
                    alert('Tin tức đã được xuất bản!');
                    fetchNews(currentPage);
                } else {
                    alert('Có lỗi xảy ra khi xuất bản tin tức!');
                }
            } catch (error) {
                console.error('Error publishing news:', error);
                alert('Có lỗi xảy ra khi xuất bản tin tức!');
            }
        }
    }
    
    // Reject news
    async function rejectNews(newsId) {
        const reason = prompt('Nhập lý do từ chối:');
        if (reason && reason.trim()) {
            try {
                const response = await fetch(`/admin/news/${newsId}/reject`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ rejection_reason: reason }),
                });
                
                if (response.ok) {
                    alert('Tin tức đã bị từ chối!');
                    fetchNews(currentPage);
                } else {
                    alert('Có lỗi xảy ra khi từ chối tin tức!');
                }
            } catch (error) {
                console.error('Error rejecting news:', error);
                alert('Có lỗi xảy ra khi từ chối tin tức!');
            }
        }
    }
    
    // Delete news
    async function deleteNews(newsId) {
        if (confirm('Bạn có chắc chắn muốn xóa tin tức này? Hành động này không thể hoàn tác!')) {
            try {
                const response = await fetch(`/admin/news/${newsId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                });
                
                if (response.ok) {
                    alert('Tin tức đã được xóa!');
                    fetchNews(currentPage);
                } else {
                    alert('Có lỗi xảy ra khi xóa tin tức!');
                }
            } catch (error) {
                console.error('Error deleting news:', error);
                alert('Có lỗi xảy ra khi xóa tin tức!');
            }
        }
    }
    
    // Update pagination
    function updatePagination() {
        const paginationContainer = document.getElementById('pagination-links');
        const paginationInfo = document.getElementById('pagination-info');
        
        paginationInfo.textContent = `${allNews.length} / ${totalNews}`;
        
        let paginationHTML = '';
        
        // Previous button
        if (currentPage > 1) {
            paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="fetchNews(${currentPage - 1})">Trước</a></li>`;
        }
        
        // Page numbers
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);
        
        for (let i = startPage; i <= endPage; i++) {
            paginationHTML += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="fetchNews(${i})">${i}</a>
            </li>`;
        }
        
        // Next button
        if (currentPage < totalPages) {
            paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="fetchNews(${currentPage + 1})">Sau</a></li>`;
        }
        
        paginationContainer.innerHTML = paginationHTML;
    }
    
    // Update statistics
    function updateStatistics() {
        const total = allNews.length;
        const published = allNews.filter(news => news.is_approved && news.is_published).length;
        const pending = allNews.filter(news => !news.is_approved && !news.is_rejected).length;
        const rejected = allNews.filter(news => news.is_rejected).length;
        
        document.getElementById('total-news').textContent = total;
        document.getElementById('published-news').textContent = published;
        document.getElementById('pending-news').textContent = pending;
        document.getElementById('rejected-news').textContent = rejected;
    }
    
    // Load news on page load
    document.addEventListener('DOMContentLoaded', function() {
        fetchNews();
    });
</script>
@endpush
