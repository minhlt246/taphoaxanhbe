@extends('layouts.admin')

@section('title', 'Đánh giá - Tạp Hóa Xanh Admin')

@push('styles')
    <style>
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .btn-group .btn {
            margin: 0 1px;
        }
        .btn-group .btn:hover {
            transform: translateY(-1px);
            transition: all 0.2s ease;
        }
        .card-header .btn {
            margin-left: 5px;
        }
        .card-header .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .card-header .btn:not(:disabled):hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
@endpush

@section('content')
                <!-- Top Navigation -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Quản lý đánh giá</h1>
                </div>

                <!-- Reviews Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Danh sách đánh giá</h6>
                        <div class="d-flex gap-2 align-items-center">
                            <span class="text-muted">Danh sách đánh giá sản phẩm</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Sản phẩm</th>
                                        <th>Khách hàng</th>
                                        <th>Đánh giá</th>
                                        <th>Bình luận</th>
                                        <th>Ngày tạo</th>
                                    </tr>
                                </thead>
                                <tbody id="reviews-table">
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
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted">
                Hiển thị <span id="showingStart">0</span> - <span id="showingEnd">0</span> 
                trong tổng số <span id="totalReviews">0</span> đánh giá
                    </div>
            <nav aria-label="Reviews pagination">
                <ul class="pagination pagination-sm mb-0" id="pagination">
                    <!-- Pagination buttons will be inserted here -->
                </ul>
            </nav>
                </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        let allReviews = []; // Lưu trữ tất cả đánh giá
    let currentPage = 1;
    let totalPages = 1;
    let totalReviews = 0;
    let reviewsPerPage = 20;
        
        // Fetch reviews data
    async function fetchReviews(page = 1) {
        fetchReviewsWithFilter(page);
    }
    
    // Display reviews
    function displayReviews(reviews) {
                const tbody = document.getElementById('reviews-table');
        if (reviews.length > 0) {
            tbody.innerHTML = reviews.map(review => `
                        <tr>
                            <td>${review.id}</td>
                            <td>${review.product?.name || 'N/A'}</td>
                            <td>${review.user?.name || 'Khách hàng'}</td>
                            <td>
                                <div class="d-flex">
                                    ${Array.from({length: 5}, (_, i) => 
                                        `<i class="fas fa-star ${i < review.rating ? 'text-warning' : 'text-muted'}"></i>`
                                    ).join('')}
                                </div>
                            </td>
                            <td>${review.comment ? review.comment.substring(0, 50) + '...' : 'Không có bình luận'}</td>
                            <td>${review.createdAt ? new Date(review.createdAt).toLocaleDateString('vi-VN') : 'N/A'}</td>
                        </tr>
                    `).join('');
                } else {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Chưa có đánh giá nào</td></tr>';
                }
    }
    
    // Update pagination
    function updatePagination() {
        const pagination = document.getElementById('pagination');
        const showingStart = document.getElementById('showingStart');
        const showingEnd = document.getElementById('showingEnd');
        const totalReviewsSpan = document.getElementById('totalReviews');
        
        // Update showing info
        const start = (currentPage - 1) * reviewsPerPage + 1;
        const end = Math.min(currentPage * reviewsPerPage, totalReviews);
        showingStart.textContent = start;
        showingEnd.textContent = end;
        totalReviewsSpan.textContent = totalReviews;
        
        // Generate pagination buttons
        let paginationHTML = '';
        
        // Previous button
        if (currentPage > 1) {
            paginationHTML += `<li class="page-item">
                <a class="page-link" href="#" onclick="changePage(${currentPage - 1})">Trước</a>
            </li>`;
        } else {
            paginationHTML += `<li class="page-item disabled">
                <span class="page-link">Trước</span>
            </li>`;
        }
        
        // Page numbers
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);
        
        if (startPage > 1) {
            paginationHTML += `<li class="page-item">
                <a class="page-link" href="#" onclick="changePage(1)">1</a>
            </li>`;
            if (startPage > 2) {
                paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            if (i === currentPage) {
                paginationHTML += `<li class="page-item active">
                    <span class="page-link">${i}</span>
                </li>`;
            } else {
                paginationHTML += `<li class="page-item">
                    <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
                </li>`;
            }
        }
        
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
            paginationHTML += `<li class="page-item">
                <a class="page-link" href="#" onclick="changePage(${totalPages})">${totalPages}</a>
            </li>`;
        }
        
        // Next button
        if (currentPage < totalPages) {
            paginationHTML += `<li class="page-item">
                <a class="page-link" href="#" onclick="changePage(${currentPage + 1})">Sau</a>
            </li>`;
        } else {
            paginationHTML += `<li class="page-item disabled">
                <span class="page-link">Sau</span>
            </li>`;
        }
        
        pagination.innerHTML = paginationHTML;
    }
    
    // Change page
    function changePage(page) {
        if (page >= 1 && page <= totalPages && page !== currentPage) {
            fetchReviewsWithFilter(page);
            }
        }

    // Fetch reviews với filter
    async function fetchReviewsWithFilter(page = 1) {
        try {
            let url = `/api/reviews?page=${page}&limit=${reviewsPerPage}`;
            
            const response = await fetch(url);
            const data = await response.json();
            
            // Lưu trữ dữ liệu gốc
            allReviews = data.data || [];
            currentPage = data.current_page || 1;
            totalPages = data.last_page || 1;
            totalReviews = data.total || 0;
            
            displayReviews(allReviews);
            updatePagination();
        } catch (error) {
            console.error('Error fetching reviews:', error);
            document.getElementById('reviews-table').innerHTML = '<tr><td colspan="6" class="text-center text-danger">Lỗi khi tải dữ liệu</td></tr>';
            }
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            fetchReviews();
        });
    </script>
@endpush