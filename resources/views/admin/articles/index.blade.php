@extends('layouts.admin')

@section('title', 'Bài viết - Tạp Hóa Xanh Admin')

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
    <h1 class="h2">Quản lý bài viết</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.articles.create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Thêm bài viết
        </a>
    </div>
</div>

<!-- Articles Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách bài viết</h6>
        <div class="d-flex gap-2 align-items-center">
            <label class="form-label mb-0 me-2">Lọc theo trạng thái:</label>
            <select class="form-select form-select-sm" id="statusFilter" onchange="filterByStatus()" style="width: 150px;">
                <option value="">Tất cả</option>
                <option value="draft">Bản nháp</option>
                <option value="published">Đã xuất bản</option>
                <option value="pending">Chờ duyệt</option>
                <option value="approved">Đã duyệt</option>
                <option value="rejected">Từ chối</option>
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
                        <th>Ngày tạo</th>
                    </tr>
                </thead>
                <tbody id="articles-table">
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
    </div>
</div>
@endsection

@push('scripts')
<script>
    let allArticles = []; // Lưu trữ tất cả bài viết
    
    // Fetch articles data
    async function fetchArticles() {
        try {
            const response = await fetch('/api/articles');
            const data = await response.json();
            
            // Lưu trữ dữ liệu gốc
            allArticles = data.data || [];
            
            const tbody = document.getElementById('articles-table');
            if (allArticles.length > 0) {
                tbody.innerHTML = allArticles.map(article => `
                    <tr>
                        <td>${article.id}</td>
                        <td>${article.title}</td>
                        <td>${article.author_name}</td>
                        <td>${article.category}</td>
                        <td>
                            <span class="badge ${getStatusBadgeClass(article)}">
                                ${getStatusText(article)}
                            </span>
                        </td>
                        <td>${article.view_count}</td>
                        <td>${new Date(article.created_at).toLocaleDateString('vi-VN')}</td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Chưa có bài viết nào</td></tr>';
            }
        } catch (error) {
            console.error('Error fetching articles:', error);
            document.getElementById('articles-table').innerHTML = '<tr><td colspan="7" class="text-center text-danger">Lỗi khi tải dữ liệu</td></tr>';
        }
    }

    // Lọc bài viết theo trạng thái
    function filterByStatus() {
        const statusFilter = document.getElementById('statusFilter').value;
        const tbody = document.getElementById('articles-table');
        
        let filteredArticles = allArticles;
        if (statusFilter) {
            filteredArticles = allArticles.filter(article => {
                switch(statusFilter) {
                    case 'draft':
                        return !article.is_published && !article.is_approved && !article.is_rejected;
                    case 'published':
                        return article.is_published;
                    case 'pending':
                        return !article.is_published && !article.is_approved && !article.is_rejected;
                    case 'approved':
                        return article.is_approved;
                    case 'rejected':
                        return article.is_rejected;
                    default:
                        return true;
                }
            });
        }
        
        if (filteredArticles.length > 0) {
            tbody.innerHTML = filteredArticles.map(article => `
                <tr>
                    <td>${article.id}</td>
                    <td>${article.title}</td>
                    <td>${article.author_name}</td>
                    <td>${article.category}</td>
                    <td>
                        <span class="badge ${getStatusBadgeClass(article)}">
                            ${getStatusText(article)}
                        </span>
                    </td>
                    <td>${article.view_count}</td>
                    <td>${new Date(article.created_at).toLocaleDateString('vi-VN')}</td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Không có bài viết nào</td></tr>';
        }
    }

    function getStatusBadgeClass(article) {
        if (article.is_published) return 'bg-success';
        if (article.is_approved) return 'bg-info';
        if (article.is_rejected) return 'bg-danger';
        return 'bg-warning';
    }

    function getStatusText(article) {
        if (article.is_published) return 'Đã xuất bản';
        if (article.is_approved) return 'Đã duyệt';
        if (article.is_rejected) return 'Từ chối';
        return 'Chờ duyệt';
    }

    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
        fetchArticles();
    });
</script>
@endpush