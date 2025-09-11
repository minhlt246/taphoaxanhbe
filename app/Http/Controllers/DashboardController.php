<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard view
     */
    public function index()
    {
        return view('dashboard');
    }

    /**
     * Get dashboard metrics
     */
    public function metrics(): JsonResponse
    {
        $today = now()->startOfDay();
        $yesterday = now()->subDay()->startOfDay();
        $thisWeek = now()->startOfWeek();
        $lastWeek = now()->subWeek()->startOfWeek();
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        $metrics = [
            // Tổng số liệu
            'total_users' => User::count(),
            'total_orders' => Order::count(),
            'total_revenue' => Order::sum('total_price'),
            'total_products' => Product::count(),
            'total_reviews' => DB::table('reviews')->count(),
            'total_articles' => News::count(),
            
            // Hôm nay
            'today_orders' => Order::whereDate('created_at', $today)->count(),
            'today_revenue' => Order::whereDate('created_at', $today)->sum('total_price'),
            'today_users' => User::whereDate('created_at', $today)->count(),
            
            // Hôm qua
            'yesterday_orders' => Order::whereDate('created_at', $yesterday)->count(),
            'yesterday_revenue' => Order::whereDate('created_at', $yesterday)->sum('total_price'),
            
            // Tuần này
            'this_week_orders' => Order::where('created_at', '>=', $thisWeek)->count(),
            'this_week_revenue' => Order::where('created_at', '>=', $thisWeek)->sum('total_price'),
            
            // Tuần trước
            'last_week_orders' => Order::whereBetween('created_at', [$lastWeek, $thisWeek])->count(),
            'last_week_revenue' => Order::whereBetween('created_at', [$lastWeek, $thisWeek])->sum('total_price'),
            
            // Tháng này
            'this_month_orders' => Order::where('created_at', '>=', $thisMonth)->count(),
            'this_month_revenue' => Order::where('created_at', '>=', $thisMonth)->sum('total_price'),
            
            // Tháng trước
            'last_month_orders' => Order::whereBetween('created_at', [$lastMonth, $thisMonth])->count(),
            'last_month_revenue' => Order::whereBetween('created_at', [$lastMonth, $thisMonth])->sum('total_price'),
            
            // Trạng thái đơn hàng
            'pending_orders' => Order::where('status', 'pending')->count(),
            'processing_orders' => Order::where('status', 'processing')->count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            
            // Đánh giá
            'pending_reviews' => DB::table('reviews')->where('status', 'pending')->count(),
            'approved_reviews' => DB::table('reviews')->where('status', 'approved')->count(),
            'rejected_reviews' => DB::table('reviews')->where('status', 'rejected')->count(),
            
            // Bài viết
            'published_articles' => News::where('is_approved', true)->count(),
            'draft_articles' => News::where('is_approved', false)->count(),
        ];

        return response()->json($metrics);
    }

    /**
     * Get revenue overview
     */
    public function revenueOverview(): JsonResponse
    {
        $revenue = [
            'total' => Order::sum('total_price'),
            'today' => Order::whereDate('created_at', today())->sum('total_price'),
            'yesterday' => Order::whereDate('created_at', today()->subDay())->sum('total_price'),
            'this_week' => Order::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('total_price'),
            'last_week' => Order::whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])->sum('total_price'),
            'this_month' => Order::whereMonth('created_at', now()->month)->sum('total_price'),
            'last_month' => Order::whereMonth('created_at', now()->subMonth()->month)->sum('total_price'),
        ];

        return response()->json($revenue);
    }

    /**
     * Get daily revenue chart data
     */
    public function dailyRevenue(): JsonResponse
    {
        $dailyRevenue = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total_price) as revenue')
        )
        ->where('created_at', '>=', now()->subDays(30))
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        return response()->json($dailyRevenue);
    }

    /**
     * Get weekly revenue chart data
     */
    public function weeklyRevenue(): JsonResponse
    {
        $weeklyRevenue = Order::select(
            DB::raw('WEEK(created_at) as week'),
            DB::raw('YEAR(created_at) as year'),
            DB::raw('SUM(total_price) as revenue')
        )
        ->where('created_at', '>=', now()->subWeeks(12))
        ->groupBy('year', 'week')
        ->orderBy('year')
        ->orderBy('week')
        ->get();

        return response()->json($weeklyRevenue);
    }

    /**
     * Get monthly revenue chart data
     */
    public function monthlyRevenue(): JsonResponse
    {
        $monthlyRevenue = Order::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('YEAR(created_at) as year'),
            DB::raw('SUM(total_price) as revenue')
        )
        ->where('created_at', '>=', now()->subMonths(12))
        ->groupBy('year', 'month')
        ->orderBy('year')
        ->orderBy('month')
        ->get();

        return response()->json($monthlyRevenue);
    }

    /**
     * Get recent orders
     */
    public function recentOrders(): JsonResponse
    {
        $recentOrders = Order::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json($recentOrders);
    }

    /**
     * Get system statistics
     */
    public function systemStats(): JsonResponse
    {
        $stats = [
            'total_users' => User::count(),
            'total_orders' => Order::count(),
            'total_products' => Product::count(),
            'total_reviews' => DB::table('reviews')->count(),
            'total_articles' => News::count(),
            'total_revenue' => Order::sum('total_price'),
            'pending_reviews' => DB::table('reviews')->where('status', 'pending')->count(),
            'pending_articles' => News::where('is_approved', false)->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Get top selling products
     */
    public function topSellingProducts(): JsonResponse
    {
        $topProducts = DB::table('order_item')
            ->join('products', 'order_item.product_id', '=', 'products.id')
            ->select(
                'products.id',
                'products.name',
                'products.price',
                DB::raw('SUM(order_item.quantity) as total_sold'),
                DB::raw('SUM(order_item.quantity * order_item.unit_price) as total_revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.price')
            ->orderBy('total_sold', 'desc')
            ->limit(10)
            ->get();

        return response()->json($topProducts);
    }

    /**
     * Get user growth statistics
     */
    public function userGrowth(): JsonResponse
    {
        $userGrowth = User::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as new_users')
        )
        ->where('created_at', '>=', now()->subDays(30))
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        return response()->json($userGrowth);
    }

    /**
     * Get order status distribution
     */
    public function orderStatusDistribution(): JsonResponse
    {
        $statusDistribution = Order::select(
            'status',
            DB::raw('COUNT(*) as count')
        )
        ->groupBy('status')
        ->get();

        return response()->json($statusDistribution);
    }

    /**
     * Get monthly sales comparison
     */
    public function monthlySalesComparison(): JsonResponse
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $lastMonth = now()->subMonth()->month;
        $lastYear = now()->subMonth()->year;

        $currentMonthSales = Order::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->sum('total_price');

        $lastMonthSales = Order::whereMonth('created_at', $lastMonth)
            ->whereYear('created_at', $lastYear)
            ->sum('total_price');

        $growthRate = $lastMonthSales > 0 
            ? (($currentMonthSales - $lastMonthSales) / $lastMonthSales) * 100 
            : 0;

        return response()->json([
            'current_month' => $currentMonthSales,
            'last_month' => $lastMonthSales,
            'growth_rate' => round($growthRate, 2),
            'growth_direction' => $growthRate >= 0 ? 'up' : 'down'
        ]);
    }

    /**
     * Get average order value
     */
    public function averageOrderValue(): JsonResponse
    {
        $avgOrderValue = Order::avg('total_price');
        $avgOrderValueToday = Order::whereDate('created_at', today())->avg('total_price');
        $avgOrderValueThisMonth = Order::whereMonth('created_at', now()->month)->avg('total_price');

        return response()->json([
            'overall' => round($avgOrderValue, 2),
            'today' => round($avgOrderValueToday, 2),
            'this_month' => round($avgOrderValueThisMonth, 2)
        ]);
    }

    /**
     * Get conversion rate
     */
    public function conversionRate(): JsonResponse
    {
        $totalUsers = User::count();
        $usersWithOrders = User::whereHas('orders')->count();
        
        $conversionRate = $totalUsers > 0 ? ($usersWithOrders / $totalUsers) * 100 : 0;

        return response()->json([
            'conversion_rate' => round($conversionRate, 2),
            'total_users' => $totalUsers,
            'users_with_orders' => $usersWithOrders
        ]);
    }

    /**
     * Get revenue chart data based on time range
     */
    public function revenueChart(Request $request): JsonResponse
    {
        $range = $request->get('range', 'day');
        $data = [];

        switch ($range) {
            case 'day':
                $data = $this->getDailyRevenueData();
                break;
            case 'week':
                $data = $this->getWeeklyRevenueData();
                break;
            case 'month':
                $data = $this->getMonthlyRevenueData();
                break;
            default:
                $data = $this->getDailyRevenueData();
        }

        return response()->json($data);
    }

    /**
     * Get daily revenue data for the current week
     */
    private function getDailyRevenueData(): array
    {
        $data = [];
        
        // Lấy 7 ngày gần nhất có dữ liệu đơn hàng
        $recentOrders = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total_price) as revenue')
        )
        ->where('created_at', '>=', now()->subDays(30)) // Lấy 30 ngày gần nhất để có đủ dữ liệu
        ->groupBy('date')
        ->orderBy('date', 'desc')
        ->limit(7)
        ->get();
        
        // Nếu không đủ 7 ngày, lấy các ngày gần nhất
        if ($recentOrders->count() < 7) {
            $recentOrders = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_price) as revenue')
            )
            ->where('created_at', '>=', now()->subDays(60)) // Mở rộng lên 60 ngày
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(7)
            ->get();
        }
        
        // Đảo ngược để hiển thị từ cũ đến mới
        $recentOrders = $recentOrders->reverse();
        
        foreach ($recentOrders as $order) {
            $date = \Carbon\Carbon::parse($order->date);
            $data[] = [
                'label' => $date->format('d/m'),
                'revenue' => $order->revenue
            ];
        }
        
        return $data;
    }

    /**
     * Get weekly revenue data for the current month
     */
    private function getWeeklyRevenueData(): array
    {
        $data = [];
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();
        
        // Lấy các tuần trong tháng hiện tại
        $currentWeek = $startOfMonth->copy()->startOfWeek();
        
        while ($currentWeek->lte($endOfMonth)) {
            $weekEnd = $currentWeek->copy()->endOfWeek();
            if ($weekEnd->gt($endOfMonth)) {
                $weekEnd = $endOfMonth;
            }
            
            $revenue = Order::whereBetween('created_at', [$currentWeek, $weekEnd])->sum('total_price');
            
            $data[] = [
                'label' => 'Tuần ' . $currentWeek->format('W') . ' (' . $currentWeek->format('d/m') . ' - ' . $weekEnd->format('d/m') . ')',
                'revenue' => $revenue
            ];
            
            $currentWeek->addWeek();
        }
        
        return $data;
    }

    /**
     * Get monthly revenue data from May 2025
     */
    private function getMonthlyRevenueData(): array
    {
        $data = [];
        $previousMonthRevenue = null;
        
        // Bắt đầu từ tháng 5/2025
        $startMonth = \Carbon\Carbon::create(2025, 5, 1);
        $currentMonth = now()->startOfMonth();
        
        $month = $startMonth->copy();
        while ($month->lte($currentMonth)) {
            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth = $month->copy()->endOfMonth();
            
            $revenue = Order::whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('total_price');
            
            // Calculate percentage change compared to previous month
            $percentageChange = null;
            if ($previousMonthRevenue !== null && $previousMonthRevenue > 0) {
                $percentageChange = round((($revenue - $previousMonthRevenue) / $previousMonthRevenue) * 100, 1);
            }
            
            $data[] = [
                'label' => $startOfMonth->format('m/Y'),
                'revenue' => $revenue,
                'percentage_change' => $percentageChange,
                'is_increase' => $percentageChange !== null ? $percentageChange >= 0 : null
            ];
            
            $previousMonthRevenue = $revenue;
            $month->addMonth();
        }
        
        return $data;
    }
}
