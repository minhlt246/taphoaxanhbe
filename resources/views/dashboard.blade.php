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
        height: 120px;
        display: flex;
        align-items: center;
    }
    .metric-card .card-body {
        padding: 1.5rem;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .metric-content {
        display: flex;
        flex-direction: column;
        justify-content: center;
        flex: 1;
    }
    .metric-icon {
        font-size: 3rem;
        opacity: 0.8;
        margin-left: 1rem;
    }
    .metric-text {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }
    .metric-value {
        font-size: 1.8rem;
        font-weight: 700;
        margin: 0;
        line-height: 1.2;
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
                                <div class="metric-content">
                                    <div class="metric-text">Tổng khách hàng</div>
                                    <div class="metric-value" id="total-users">0</div>
                                </div>
                                <i class="fas fa-users metric-icon"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card metric-card">
                            <div class="card-body">
                                <div class="metric-content">
                                    <div class="metric-text">Tổng đơn hàng</div>
                                    <div class="metric-value" id="total-orders">0</div>
                                </div>
                                <i class="fas fa-shopping-cart metric-icon"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card metric-card">
                            <div class="card-body">
                                <div class="metric-content">
                                    <div class="metric-text">Tổng doanh thu</div>
                                    <div class="metric-value" id="total-revenue">0 ₫</div>
                                </div>
                                <i class="fas fa-dollar-sign metric-icon"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card metric-card">
                            <div class="card-body">
                                <div class="metric-content">
                                    <div class="metric-text">Sản phẩm</div>
                                    <div class="metric-value" id="total-products">0</div>
                                </div>
                                <i class="fas fa-box metric-icon"></i>
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
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary">Biểu đồ doanh thu</h6>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="d-flex align-items-center">
                                        <label class="form-label mb-0 me-2">Chọn khoảng thời gian:</label>
                                        <select class="form-select form-select-sm" id="chart-period" onchange="switchChart(this.value)" style="width: 150px;">
                                            <option value="daily">Theo ngày trong tháng</option>
                                            <option value="weekly">Theo tuần trong tháng</option>
                                            <option value="monthly">So sánh các tháng</option>
                                        </select>
                                    </div>
                                    <div class="d-flex align-items-center" id="month-selector">
                                        <label class="form-label mb-0 me-2">Chọn tháng:</label>
                                        <select class="form-select form-select-sm" id="chart-month" onchange="switchChart(currentChartType)" style="width: 150px;">
                                            <option value="2025-05">Tháng 5/2025</option>
                                            <option value="2025-06">Tháng 6/2025</option>
                                            <option value="2025-07">Tháng 7/2025</option>
                                            <option value="2025-08">Tháng 8/2025</option>
                                            <option value="2025-09" selected>Tháng 9/2025</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="revenueChart"></canvas>
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

        let revenueChart = null;
        let currentChartType = 'daily';

        // Fetch revenue chart data
        async function fetchRevenueChart(type = 'daily') {
            try {
                let url = '';
                
                switch(type) {
                    case 'daily':
                        const selectedMonth = document.getElementById('chart-month').value;
                        url = `/api/daily-revenue?month=${selectedMonth}`;
                        break;
                    case 'weekly':
                        const selectedMonthWeekly = document.getElementById('chart-month').value;
                        url = `/api/weekly-revenue?month=${selectedMonthWeekly}`;
                        break;
                    case 'monthly':
                        url = '/api/monthly-revenue';
                        break;
                }
                
                const response = await fetch(url);
                const data = await response.json();
                
                const ctx = document.getElementById('revenueChart').getContext('2d');
                
                // Destroy existing chart
                if (revenueChart) {
                    revenueChart.destroy();
                }
                
                // Calculate percentage changes for daily data
                let labels = data.map(item => item.date || item.label);
                let chartData = data.map(item => item.revenue);
                let backgroundColors = [];
                
                if (type === 'daily' && data.length > 1) {
                    // Calculate percentage changes for daily revenue
                    for (let i = 0; i < chartData.length; i++) {
                        if (i === 0) {
                            backgroundColors.push('rgba(102, 126, 234, 0.8)'); // First day - default color
                        } else {
                            const prevRevenue = chartData[i - 1];
                            const currentRevenue = chartData[i];
                            
                            if (prevRevenue === 0) {
                                backgroundColors.push('rgba(102, 126, 234, 0.8)'); // Default if previous is 0
                            } else {
                                const percentageChange = ((currentRevenue - prevRevenue) / prevRevenue) * 100;
                                
                                if (percentageChange > 0) {
                                    backgroundColors.push('rgba(34, 197, 94, 0.8)'); // Green for increase
                                } else if (percentageChange < 0) {
                                    backgroundColors.push('rgba(239, 68, 68, 0.8)'); // Red for decrease
                                } else {
                                    backgroundColors.push('rgba(102, 126, 234, 0.8)'); // Default for no change
                                }
                            }
                        }
                    }
                } else {
                    // Default colors for weekly and monthly
                    backgroundColors = data.map(() => 'rgba(102, 126, 234, 0.8)');
                }

                // Create new chart
                revenueChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Doanh thu (VND)',
                            data: chartData,
                            backgroundColor: backgroundColors,
                            borderColor: backgroundColors.map(color => color.replace('0.8', '1')),
                            borderWidth: 1,
                            borderRadius: 4,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: type === 'daily',
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20,
                                    generateLabels: function(chart) {
                                        if (type === 'daily') {
                                            return [
                                                {
                                                    text: 'Ngày đầu tiên',
                                                    fillStyle: 'rgba(102, 126, 234, 0.8)',
                                                    strokeStyle: 'rgba(102, 126, 234, 1)',
                                                    lineWidth: 1,
                                                    pointStyle: 'rect'
                                                },
                                                {
                                                    text: 'Tăng so với ngày trước',
                                                    fillStyle: 'rgba(34, 197, 94, 0.8)',
                                                    strokeStyle: 'rgba(34, 197, 94, 1)',
                                                    lineWidth: 1,
                                                    pointStyle: 'rect'
                                                },
                                                {
                                                    text: 'Giảm so với ngày trước',
                                                    fillStyle: 'rgba(239, 68, 68, 0.8)',
                                                    strokeStyle: 'rgba(239, 68, 68, 1)',
                                                    lineWidth: 1,
                                                    pointStyle: 'rect'
                                                }
                                            ];
                                        }
                                        return [];
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const value = context.parsed.y;
                                        let tooltipText = 'Doanh thu: ' + new Intl.NumberFormat('vi-VN').format(value) + ' ₫';
                                        
                                        // Add percentage change for daily data
                                        if (type === 'daily' && context.dataIndex > 0) {
                                            const prevValue = chartData[context.dataIndex - 1];
                                            if (prevValue > 0) {
                                                const percentageChange = ((value - prevValue) / prevValue) * 100;
                                                const changeText = percentageChange > 0 ? '+' : '';
                                                tooltipText += ` (${changeText}${percentageChange.toFixed(1)}%)`;
                                            }
                                        }
                                        
                                        return tooltipText;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return new Intl.NumberFormat('vi-VN', {
                                            style: 'currency',
                                            currency: 'VND',
                                            minimumFractionDigits: 0
                                        }).format(value);
                                    }
                                }
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('Error fetching revenue chart:', error);
            }
        }

        // Switch chart type
        function switchChart(type) {
            currentChartType = type;
            
            // Show/hide month selector
            const monthSelector = document.getElementById('month-selector');
            if (type === 'monthly') {
                monthSelector.style.display = 'none';
            } else {
                monthSelector.style.display = 'flex';
            }
            
            // Fetch new data
            fetchRevenueChart(type);
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
                            <small class="text-muted">${new Date(order.createdAt).toLocaleDateString('vi-VN')}</small>
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
            fetchRevenueChart('daily'); // Start with daily chart
            fetchRecentOrders();
        });

        // Navigation is now handled by Laravel routes - no JavaScript needed
</script>
@endpush
