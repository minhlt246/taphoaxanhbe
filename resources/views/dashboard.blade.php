@extends('layouts.admin')

@section('title', 'Tạp Hóa Xanh - Admin Dashboard')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .card {
        transition: transform 0.3s ease;
    }
    .card:hover {
        transform: translateY(-5px);
    }
    .metric-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .metric-card .card-body {
        padding: 2rem;
    }
    .metric-icon {
        font-size: 3rem;
        opacity: 0.8;
    }
    .chart-container {
        position: relative;
        height: 400px;
    }
    .navbar-brand {
        font-weight: bold;
        font-size: 1.5rem;
    }
</style>
@endpush

@section('content')
                <!-- Top Navigation -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Thêm mới
                        </button>
                    </div>
                </div>

                <!-- Metrics Cards -->
                <div class="row mb-4" id="metrics-cards">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card metric-card">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                                            Tổng khách hàng
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold" id="total-users">0</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users metric-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card metric-card">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                                            Tổng đơn hàng
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold" id="total-orders">0</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-shopping-cart metric-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card metric-card">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                                            Tổng doanh thu
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold" id="total-revenue">0 ₫</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-dollar-sign metric-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card metric-card">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                                            Sản phẩm
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold" id="total-products">0</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-box metric-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Revenue Overview -->
                <div class="row mb-4">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Tổng quan doanh thu</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 text-center">
                                        <h4 class="text-success" id="today-revenue">0 ₫</h4>
                                        <small class="text-muted">Hôm nay</small>
                                    </div>
                                    <div class="col-md-3 text-center">
                                        <h4 class="text-info" id="week-revenue">0 ₫</h4>
                                        <small class="text-muted">Tuần này</small>
                                    </div>
                                    <div class="col-md-3 text-center">
                                        <h4 class="text-warning" id="month-revenue">0 ₫</h4>
                                        <small class="text-muted">Tháng này</small>
                                    </div>
                                    <div class="col-md-3 text-center">
                                        <h4 class="text-primary" id="total-revenue-overview">0 ₫</h4>
                                        <small class="text-muted">Tổng cộng</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mb-4">
                    <div class="col-xl-8 col-lg-7">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Doanh thu theo ngày</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="dailyRevenueChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-5">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">Đơn hàng gần đây</h6>
                            </div>
                            <div class="card-body">
                                <div id="recent-orders">
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
@endsection

@push('scripts')
<script>
        // CSRF Token setup
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Fetch metrics data
        async function fetchMetrics() {
            try {
                const response = await fetch('/api/metrics');
                const data = await response.json();
                
                document.getElementById('total-users').textContent = data.total_users.toLocaleString();
                document.getElementById('total-orders').textContent = data.total_orders.toLocaleString();
                document.getElementById('total-revenue').textContent = new Intl.NumberFormat('vi-VN', {
                    style: 'currency',
                    currency: 'VND'
                }).format(data.total_revenue);
                document.getElementById('total-products').textContent = data.total_products.toLocaleString();
            } catch (error) {
                console.error('Error fetching metrics:', error);
            }
        }

        // Fetch revenue overview
        async function fetchRevenueOverview() {
            try {
                const response = await fetch('/api/revenue-overview');
                const data = await response.json();
                
                document.getElementById('today-revenue').textContent = new Intl.NumberFormat('vi-VN', {
                    style: 'currency',
                    currency: 'VND'
                }).format(data.today);
                document.getElementById('week-revenue').textContent = new Intl.NumberFormat('vi-VN', {
                    style: 'currency',
                    currency: 'VND'
                }).format(data.this_week);
                document.getElementById('month-revenue').textContent = new Intl.NumberFormat('vi-VN', {
                    style: 'currency',
                    currency: 'VND'
                }).format(data.this_month);
                document.getElementById('total-revenue-overview').textContent = new Intl.NumberFormat('vi-VN', {
                    style: 'currency',
                    currency: 'VND'
                }).format(data.total);
            } catch (error) {
                console.error('Error fetching revenue overview:', error);
            }
        }

        // Fetch daily revenue chart
        async function fetchDailyRevenueChart() {
            try {
                const response = await fetch('/api/daily-revenue');
                const data = await response.json();
                
                const ctx = document.getElementById('dailyRevenueChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.map(item => item.date),
                        datasets: [{
                            label: 'Doanh thu (VND)',
                            data: data.map(item => item.revenue),
                            borderColor: 'rgb(75, 192, 192)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return new Intl.NumberFormat('vi-VN', {
                                            style: 'currency',
                                            currency: 'VND'
                                        }).format(value);
                                    }
                                }
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('Error fetching daily revenue chart:', error);
            }
        }

        // Fetch recent orders
        async function fetchRecentOrders() {
            try {
                const response = await fetch('/api/recent-orders');
                const orders = await response.json();
                
                const container = document.getElementById('recent-orders');
                if (orders.length === 0) {
                    container.innerHTML = '<p class="text-muted text-center">Chưa có đơn hàng nào</p>';
                    return;
                }
                
                container.innerHTML = orders.map(order => `
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-shopping-cart text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fw-bold">${order.order_code}</div>
                            <small class="text-muted">${order.user?.name || 'Khách hàng'}</small>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-success">${new Intl.NumberFormat('vi-VN', {
                                style: 'currency',
                                currency: 'VND'
                            }).format(parseFloat(order.total_price))}</div>
                            <small class="text-muted">${new Date(order.created_at).toLocaleDateString('vi-VN')}</small>
                        </div>
                    </div>
                `).join('');
            } catch (error) {
                console.error('Error fetching recent orders:', error);
                document.getElementById('recent-orders').innerHTML = '<p class="text-danger text-center">Lỗi khi tải dữ liệu</p>';
            }
        }

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            fetchMetrics();
            fetchRevenueOverview();
            fetchDailyRevenueChart();
            fetchRecentOrders();
        });

        // Navigation is now handled by Laravel routes - no JavaScript needed
</script>
@endpush
