<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // If it's an API request, return JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->apiIndex($request);
        }
        
        // Otherwise return view
        return view('admin.orders.index');
    }

    /**
     * API endpoint for listing orders
     */
    public function apiIndex(Request $request)
    {
        $perPage = $request->get('limit', 10);
        $statusFilter = $request->get('status');
        
        $query = \App\Models\Order::with('user')->orderBy('createdAt', 'desc');
        
        // Filter by status if provided
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }
        
        $orders = $query->paginate($perPage);
        
        return response()->json([
            'data' => $orders->items(),
            'current_page' => $orders->currentPage(),
            'last_page' => $orders->lastPage(),
            'per_page' => $orders->perPage(),
            'total' => $orders->total(),
            'from' => $orders->firstItem(),
            'to' => $orders->lastItem(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * Note: Admin không thể xóa đơn hàng, chỉ có thể thay đổi trạng thái
     */
    public function destroy(string $id)
    {
        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json([
                'message' => 'Admin không thể xóa đơn hàng. Chỉ có thể thay đổi trạng thái.',
                'error' => 'FORBIDDEN'
            ], 403);
        }

        return back()->with('error', 'Admin không thể xóa đơn hàng. Chỉ có thể thay đổi trạng thái.');
    }

    /**
     * Approve an order
     */
    public function approve(string $id)
    {
        $order = \App\Models\Order::findOrFail($id);
        
        // Chỉ approve nếu thanh toán thành công hoặc pending
        if ($order->payment_status === 'failed') {
            if (request()->expectsJson() || request()->is('api/*')) {
                return response()->json([
                    'message' => 'Không thể duyệt đơn hàng có thanh toán thất bại',
                    'error' => true
                ], 400);
            }
            return back()->with('error', 'Không thể duyệt đơn hàng có thanh toán thất bại');
        }
        
        $order->update([
            'status' => 'confirmed',
            'approved_by' => auth()->id() ?? 1, // Default to admin ID 1 if not authenticated
            'approved_at' => now(),
        ]);

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json([
                'message' => 'Đơn hàng đã được duyệt',
                'order' => $order
            ]);
        }

        return back()->with('success', 'Đơn hàng đã được duyệt');
    }

    /**
     * Reject an order
     */
    public function reject(string $id)
    {
        $order = \App\Models\Order::findOrFail($id);
        
        // Nếu thanh toán thất bại, chỉ đặt status là pending (chờ thanh toán lại)
        if ($order->payment_status === 'failed') {
            $order->update([
                'status' => 'pending',
                'approved_by' => auth()->id() ?? 1,
                'approved_at' => now(),
            ]);
            
            $message = 'Đơn hàng đã được đặt về trạng thái chờ thanh toán lại';
        } else {
            $order->update([
                'status' => 'cancelled',
                'approved_by' => auth()->id() ?? 1,
                'approved_at' => now(),
            ]);
            
            $message = 'Đơn hàng đã bị từ chối';
        }

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json([
                'message' => $message,
                'order' => $order
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Mark order as shipped
     */
    public function markAsShipped(string $id)
    {
        $order = \App\Models\Order::findOrFail($id);
        
        // Chỉ có thể gửi hàng nếu đã thanh toán thành công
        if ($order->payment_status !== 'paid') {
            if (request()->expectsJson() || request()->is('api/*')) {
                return response()->json([
                    'message' => 'Chỉ có thể gửi hàng khi đã thanh toán thành công',
                    'error' => true
                ], 400);
            }
            return back()->with('error', 'Chỉ có thể gửi hàng khi đã thanh toán thành công');
        }
        
        $order->update([
            'status' => 'shipped',
            'shipped_at' => now(),
        ]);

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json([
                'message' => 'Đơn hàng đã được đánh dấu là đã gửi',
                'order' => $order
            ]);
        }

        return back()->with('success', 'Đơn hàng đã được đánh dấu là đã gửi');
    }

    /**
     * Mark order as delivered
     */
    public function markAsDelivered(string $id)
    {
        $order = \App\Models\Order::findOrFail($id);
        $order->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json([
                'message' => 'Đơn hàng đã được đánh dấu là đã giao',
                'order' => $order
            ]);
        }

        return back()->with('success', 'Đơn hàng đã được đánh dấu là đã giao');
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(string $id, Request $request)
    {
        $order = \App\Models\Order::findOrFail($id);
        $paymentStatus = $request->input('payment_status');
        
        // Validate payment status
        if (!in_array($paymentStatus, ['pending', 'paid', 'failed'])) {
            if (request()->expectsJson() || request()->is('api/*')) {
                return response()->json([
                    'message' => 'Trạng thái thanh toán không hợp lệ',
                    'error' => true
                ], 400);
            }
            return back()->with('error', 'Trạng thái thanh toán không hợp lệ');
        }
        
        $order->update([
            'payment_status' => $paymentStatus,
        ]);
        
        // Nếu thanh toán thành công và đơn hàng đang pending, tự động confirm
        if ($paymentStatus === 'paid' && $order->status === 'pending') {
            $order->update([
                'status' => 'confirmed',
                'approved_by' => auth()->id() ?? 1,
                'approved_at' => now(),
            ]);
        }
        
        // Nếu thanh toán thất bại, đặt về pending
        if ($paymentStatus === 'failed') {
            $order->update([
                'status' => 'pending',
            ]);
        }

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json([
                'message' => 'Trạng thái thanh toán đã được cập nhật',
                'order' => $order
            ]);
        }

        return back()->with('success', 'Trạng thái thanh toán đã được cập nhật');
    }
}
