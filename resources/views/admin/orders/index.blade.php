@extends('layouts.admin')

@section('title', 'Đơn hàng - Tạp Hóa Xanh Admin')

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
    
    /* Cải thiện màu sắc cho badge trạng thái */
    .badge {
        font-size: 0.8rem;
        padding: 0.4em 0.8em;
        border-radius: 6px;
        font-weight: 500;
        border: 1px solid transparent;
    }
    
    /* Trạng thái đơn hàng */
    .badge.bg-warning {
        background-color: #fff3cd !important;
        color: #856404 !important;
        border-color: #ffeaa7 !important;
    }
    .badge.bg-info {
        background-color: #d1ecf1 !important;
        color: #0c5460 !important;
        border-color: #bee5eb !important;
    }
    .badge.bg-primary {
        background-color: #cce5ff !important;
        color: #004085 !important;
        border-color: #99d6ff !important;
    }
    .badge.bg-success {
        background-color: #d4edda !important;
        color: #155724 !important;
        border-color: #c3e6cb !important;
    }
    .badge.bg-danger {
        background-color: #f8d7da !important;
        color: #721c24 !important;
        border-color: #f5c6cb !important;
    }
    .badge.bg-secondary {
        background-color: #e2e3e5 !important;
        color: #383d41 !important;
        border-color: #d6d8db !important;
    }
    
    /* Pagination */
    .pagination .page-link {
        color: #007bff;
        border-color: #dee2e6;
        border-radius: 6px;
        margin: 0 2px;
    }
    .pagination .page-link:hover {
        color: #0056b3;
        background-color: #e9ecef;
        border-color: #adb5bd;
    }
    .pagination .page-item.active .page-link {
        background-color: #007bff;
        border-color: #007bff;
        }
    </style>
@endpush

@section('content')
                <!-- Top Navigation -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Quản lý đơn hàng</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Orders Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Danh sách đơn hàng</h6>
                        <div class="d-flex gap-2 align-items-center">
                            <label class="form-label mb-0 me-2">Lọc theo trạng thái:</label>
                            <select class="form-select form-select-sm" id="statusFilter" onchange="filterByStatus()" style="width: 150px;">
                                <option value="">Tất cả</option>
                                <option value="pending">Chờ xác nhận</option>
                                <option value="confirmed">Đã xác nhận</option>
                                <option value="shipped">Đã gửi</option>
                                <option value="delivered">Đã giao</option>
                                <option value="cancelled">Đã hủy</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                        </th>
                                        <th>ID</th>
                                        <th>Mã đơn hàng</th>
                                        <th>Khách hàng</th>
                                        <th>Tổng tiền</th>
                                        <th>Trạng thái</th>
                                        <th>Thanh toán</th>
                                        <th>Ngày tạo</th>
                                    </tr>
                                </thead>
                                <tbody id="orders-table">
                                    <tr>
                                        <td colspan="8" class="text-center"> 
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
                Hiển thị <span id="showing-info">0</span> trong tổng số <span id="total-info">0</span> đơn hàng
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
        let allOrders = []; // Lưu trữ tất cả đơn hàng
    let currentPage = 1;
    let totalPages = 1;
    let totalOrders = 0;
    const ordersPerPage = 10;
        
    // Fetch orders data with pagination
    async function fetchOrders(page = 1) {
        try {
            const statusFilter = document.getElementById('statusFilter').value;
            let url = `/api/orders?page=${page}&limit=${ordersPerPage}`;
            if (statusFilter) {
                url += `&status=${statusFilter}`;
            }
            
            const response = await fetch(url);
                const data = await response.json();
                
                // Lưu trữ dữ liệu gốc
                allOrders = data.data || [];
            currentPage = data.current_page || 1;
            totalPages = data.last_page || 1;
            totalOrders = data.total || 0;
                
                const tbody = document.getElementById('orders-table');
                if (allOrders.length > 0) {
                    tbody.innerHTML = allOrders.map(order => `
                        <tr>
                            <td>
                                <input type="checkbox" class="order-checkbox" value="${order.id}">
                            </td>
                            <td>${order.id}</td>
                            <td>${order.order_code}</td>
                            <td>${order.user?.name || 'Khách hàng'}</td>
                            <td>${parseFloat(order.total_price).toLocaleString()} ₫</td>
                            <td>
                                <span class="badge ${getStatusBadgeClass(order.status)}">
                                    ${getStatusText(order.status)}
                                </span>
                            </td>
                            <td>
                                <span class="badge ${getPaymentBadgeClass(order.payment_status)}">
                                    ${getPaymentText(order.payment_status)}
                                </span>
                            </td>
                            <td>${order.createdAt ? new Date(order.createdAt).toLocaleDateString('vi-VN') : 'N/A'}</td>
                        </tr>
                    `).join('');
                } else {
                    tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Chưa có đơn hàng nào</td></tr>';
                }
            
            updatePagination();
            } catch (error) {
                console.error('Error fetching orders:', error);
                document.getElementById('orders-table').innerHTML = '<tr><td colspan="8" class="text-center text-danger">Lỗi khi tải dữ liệu</td></tr>';
            }
        }

    // Update pagination UI
    function updatePagination() {
        const container = document.getElementById('pagination-container');
        const showingInfo = document.getElementById('showing-info');
        const totalInfo = document.getElementById('total-info');
        const paginationLinks = document.getElementById('pagination-links');
        
        if (totalOrders === 0) {
            container.style.display = 'none';
            return;
        }
        
        container.style.display = 'flex';
        
        // Update info text
        const startItem = (currentPage - 1) * ordersPerPage + 1;
        const endItem = Math.min(currentPage * ordersPerPage, totalOrders);
        showingInfo.textContent = `${startItem}-${endItem}`;
        totalInfo.textContent = totalOrders;
        
        // Generate pagination links
        let paginationHTML = '';
        
        // Previous button
        if (currentPage > 1) {
            paginationHTML += `<li class="page-item">
                <a class="page-link" href="#" onclick="fetchOrders(${currentPage - 1})">Trước</a>
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
                <a class="page-link" href="#" onclick="fetchOrders(1)">1</a>
            </li>`;
            if (startPage > 2) {
                paginationHTML += `<li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>`;
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            if (i === currentPage) {
                paginationHTML += `<li class="page-item active">
                    <span class="page-link">${i}</span>
                </li>`;
            } else {
                paginationHTML += `<li class="page-item">
                    <a class="page-link" href="#" onclick="fetchOrders(${i})">${i}</a>
                </li>`;
            }
        }
        
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                paginationHTML += `<li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>`;
            }
            paginationHTML += `<li class="page-item">
                <a class="page-link" href="#" onclick="fetchOrders(${totalPages})">${totalPages}</a>
            </li>`;
        }
        
        // Next button
        if (currentPage < totalPages) {
            paginationHTML += `<li class="page-item">
                <a class="page-link" href="#" onclick="fetchOrders(${currentPage + 1})">Sau</a>
            </li>`;
        } else {
            paginationHTML += `<li class="page-item disabled">
                <span class="page-link">Sau</span>
            </li>`;
        }
        
        paginationLinks.innerHTML = paginationHTML;
    }

    // Lọc đơn hàng theo trạng thái
    function filterByStatus() {
        fetchOrders(1); // Reset về trang đầu khi filter
        }

        function getStatusBadgeClass(status) {
            switch(status) {
                case 'pending': return 'bg-warning';
                case 'confirmed': 
            case 'approved': 
            case 'completed': return 'bg-info'; // Xử lý trạng thái cũ
                case 'shipped': return 'bg-primary';
                case 'delivered': return 'bg-success';
                case 'cancelled': 
                case 'rejected': return 'bg-danger'; // Xử lý trạng thái cũ
                default: return 'bg-secondary';
            }
        }

        function getStatusText(status) {
            switch(status) {
                case 'pending': return 'Chờ xác nhận';
                case 'confirmed': 
            case 'approved': 
            case 'completed': return 'Đã xác nhận'; // Xử lý trạng thái cũ
                case 'shipped': return 'Đã gửi';
                case 'delivered': return 'Đã giao';
                case 'cancelled': 
                case 'rejected': return 'Đã hủy'; // Xử lý trạng thái cũ
                default: return status;
            }
        }

        function getPaymentBadgeClass(payment) {
            switch(payment) {
                case 'pending': return 'bg-warning';
            case 'paid': 
            case 'completed': return 'bg-success';
                case 'failed': return 'bg-danger';
                default: return 'bg-secondary';
            }
        }

        function getPaymentText(payment) {
            switch(payment) {
                case 'pending': return 'Chưa thanh toán';
            case 'paid': 
            case 'completed': return 'Đã thanh toán';
                case 'failed': return 'Thanh toán thất bại';
                default: return payment || 'Chưa thanh toán';
            }
        }

        // Approve order
        async function approveOrder(id) {
            if (confirm('Bạn có chắc chắn muốn duyệt đơn hàng này?')) {
                try {
                    const response = await fetch(`/api/orders/${id}/approve`, {
                        method: 'GET'
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        alert(data.message || 'Đơn hàng đã được duyệt');
                    fetchOrders(currentPage); // Reload the table
                    } else {
                        alert('Có lỗi xảy ra khi duyệt đơn hàng');
                    }
                } catch (error) {
                    console.error('Error approving order:', error);
                    alert('Có lỗi xảy ra khi duyệt đơn hàng');
                }
            }
        }

        // Reject order
        async function rejectOrder(id) {
            if (confirm('Bạn có chắc chắn muốn từ chối đơn hàng này?')) {
                try {
                    const response = await fetch(`/api/orders/${id}/reject`, {
                        method: 'GET'
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        alert(data.message || 'Đơn hàng đã bị từ chối');
                    fetchOrders(currentPage); // Reload the table
                    } else {
                        alert('Có lỗi xảy ra khi từ chối đơn hàng');
                    }
                } catch (error) {
                    console.error('Error rejecting order:', error);
                    alert('Có lỗi xảy ra khi từ chối đơn hàng');
                }
            }
        }

        // Mark order as shipped
        async function markOrderAsShipped(id) {
            if (confirm('Bạn có chắc chắn muốn đánh dấu đơn hàng này là đã gửi?')) {
                try {
                    const response = await fetch(`/api/orders/${id}/shipped`, {
                        method: 'GET'
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        alert(data.message || 'Đơn hàng đã được đánh dấu là đã gửi');
                    fetchOrders(currentPage); // Reload the table
                    } else {
                        alert('Có lỗi xảy ra khi đánh dấu đơn hàng');
                    }
                } catch (error) {
                    console.error('Error marking order as shipped:', error);
                    alert('Có lỗi xảy ra khi đánh dấu đơn hàng');
                }
            }
        }

        // Mark order as delivered
        async function markOrderAsDelivered(id) {
            if (confirm('Bạn có chắc chắn muốn đánh dấu đơn hàng này là đã giao?')) {
                try {
                    const response = await fetch(`/api/orders/${id}/delivered`, {
                        method: 'GET'
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        alert(data.message || 'Đơn hàng đã được đánh dấu là đã giao');
                    fetchOrders(currentPage); // Reload the table
                    } else {
                        alert('Có lỗi xảy ra khi đánh dấu đơn hàng');
                    }
                } catch (error) {
                    console.error('Error marking order as delivered:', error);
                    alert('Có lỗi xảy ra khi đánh dấu đơn hàng');
                }
            }
        }

        // Toggle select all
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.order-checkbox');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
        fetchOrders(1);
        });
    </script>
@endpush