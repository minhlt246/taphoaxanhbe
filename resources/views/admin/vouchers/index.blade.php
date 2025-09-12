@extends('layouts.admin')

@section('title', 'Voucher - Tạp Hóa Xanh Admin')

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
    <h1 class="h2">Quản lý voucher</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.vouchers.create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Thêm voucher
        </a>
    </div>
</div>

<!-- Vouchers Table -->
<div class="card">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách voucher</h6>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Mã voucher</th>
                        <th>Loại</th>
                        <th>Giá trị</th>
                        <th>Số lượng</th>
                        <th>Đã sử dụng</th>
                        <th>Ngày bắt đầu</th>
                        <th>Ngày kết thúc</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody id="vouchers-table">
                    <tr>
                        <td colspan="10" class="text-center">
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
            <div id="pagination-info" class="text-muted">
                <!-- Pagination info will be inserted here -->
            </div>
            <nav aria-label="Voucher pagination">
                <ul id="pagination-nav" class="pagination pagination-sm mb-0">
                    <!-- Pagination buttons will be inserted here -->
                </ul>
            </nav>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Pagination variables
    let currentPage = 1;
    let totalPages = 1;
    let totalVouchers = 0;
    const vouchersPerPage = 20;

    // Fetch vouchers data
    async function fetchVouchers(page = 1) {
        try {
            const response = await fetch(`/api/vouchers?page=${page}`);
            const data = await response.json();
            
            const tbody = document.getElementById('vouchers-table');
            if (data.data && data.data.length > 0) {
                tbody.innerHTML = data.data.map(voucher => `
                    <tr>
                        <td>${voucher.id}</td>
                        <td><code>${voucher.code}</code></td>
                        <td>
                            <span class="badge ${voucher.type === 'discount' ? 'bg-primary' : 'bg-info'}">
                                ${voucher.type === 'discount' ? 'Giảm giá' : 'Phần trăm'}
                            </span>
                        </td>
                        <td>${voucher.max_discount ? voucher.max_discount.toLocaleString() + ' ₫' : 'Không giới hạn'}</td>
                        <td>${voucher.quantity}</td>
                        <td>
                            <span class="badge ${voucher.used_count >= voucher.quantity ? 'bg-danger' : 'bg-info'}">
                                ${voucher.used_count} / ${voucher.quantity}
                            </span>
                        </td>
                        <td>${new Date(voucher.start_date).toLocaleDateString('vi-VN')}</td>
                        <td>${new Date(voucher.end_date).toLocaleDateString('vi-VN')}</td>
                        <td>
                            <span class="badge ${getVoucherStatusBadge(voucher)}">
                                ${getVoucherStatusText(voucher)}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="/admin/vouchers/${voucher.id}" class="btn btn-sm btn-info" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="/admin/vouchers/${voucher.id}/edit" class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-danger" onclick="deleteVoucher(${voucher.id})" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
                
                // Update pagination info
                currentPage = data.pagination.current_page;
                totalPages = data.pagination.last_page;
                totalVouchers = data.pagination.total;
                updatePagination();
            } else {
                tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted">Chưa có voucher nào</td></tr>';
            }
        } catch (error) {
            console.error('Error fetching vouchers:', error);
            document.getElementById('vouchers-table').innerHTML = '<tr><td colspan="10" class="text-center text-danger">Lỗi khi tải dữ liệu</td></tr>';
        }
    }

    // Update pagination display
    function updatePagination() {
        const paginationInfo = document.getElementById('pagination-info');
        const paginationNav = document.getElementById('pagination-nav');
        
        if (paginationInfo) {
            const startItem = (currentPage - 1) * vouchersPerPage + 1;
            const endItem = Math.min(currentPage * vouchersPerPage, totalVouchers);
            paginationInfo.textContent = `Hiển thị ${startItem}-${endItem} trong tổng số ${totalVouchers} voucher`;
        }
        
        if (paginationNav) {
            let paginationHTML = '';
            
            // Previous button
            if (currentPage > 1) {
                paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(${currentPage - 1})">Trước</a></li>`;
            }
            
            // Page numbers
            const startPage = Math.max(1, currentPage - 2);
            const endPage = Math.min(totalPages, currentPage + 2);
            
            if (startPage > 1) {
                paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(1)">1</a></li>`;
                if (startPage > 2) {
                    paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
            }
            
            for (let i = startPage; i <= endPage; i++) {
                const activeClass = i === currentPage ? 'active' : '';
                paginationHTML += `<li class="page-item ${activeClass}"><a class="page-link" href="#" onclick="changePage(${i})">${i}</a></li>`;
            }
            
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
                paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(${totalPages})">${totalPages}</a></li>`;
            }
            
            // Next button
            if (currentPage < totalPages) {
                paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(${currentPage + 1})">Sau</a></li>`;
            }
            
            paginationNav.innerHTML = paginationHTML;
        }
    }

    // Change page function
    function changePage(page) {
        if (page >= 1 && page <= totalPages) {
            fetchVouchers(page);
        }
    }

    // Show alert function
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="fas ${icon}"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Insert alert at the top of card-body
        const cardBody = document.querySelector('.card-body');
        const existingAlert = cardBody.querySelector('.alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        cardBody.insertAdjacentHTML('afterbegin', alertHtml);
    }

    // Hàm lấy class badge cho trạng thái voucher
    function getVoucherStatusBadge(voucher) {
        if (!voucher.is_valid) {
            return 'bg-danger'; // Không hợp lệ (hết hạn)
        }
        
        const remaining = voucher.remaining_count;
        const total = voucher.quantity;
        const usedPercentage = ((total - remaining) / total) * 100;
        
        if (remaining <= 0) {
            return 'bg-danger'; // Đã hết
        } else if (usedPercentage >= 80) {
            return 'bg-warning'; // Gần hết (đã sử dụng 80% trở lên)
        } else {
            return 'bg-success'; // Hoạt động
        }
    }

    // Hàm lấy text cho trạng thái voucher
    function getVoucherStatusText(voucher) {
        if (!voucher.is_valid) {
            return 'Không hợp lệ';
        }
        
        const remaining = voucher.remaining_count;
        const total = voucher.quantity;
        const usedPercentage = ((total - remaining) / total) * 100;
        
        if (remaining <= 0) {
            return 'Đã hết';
        } else if (usedPercentage >= 80) {
            return 'Gần hết';
        } else {
            return 'Hoạt động';
        }
    }

    // Delete voucher function
    async function deleteVoucher(id) {
        if (confirm('Bạn có chắc chắn muốn xóa voucher này?')) {
            try {
                const response = await fetch(`/api/vouchers/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    showAlert('success', result.message);
                    fetchVouchers(currentPage); // Reload current page
                } else {
                    showAlert('error', result.message || 'Có lỗi xảy ra khi xóa voucher');
                }
            } catch (error) {
                console.error('Error deleting voucher:', error);
                showAlert('error', 'Có lỗi xảy ra khi xóa voucher');
            }
        }
    }

    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
        fetchVouchers();
    });
</script>
@endpush