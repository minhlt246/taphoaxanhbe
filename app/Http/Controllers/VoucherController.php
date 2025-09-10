<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VoucherController extends Controller
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
        return view('admin.vouchers.index');
    }

    /**
     * API endpoint for listing vouchers
     */
    public function apiIndex(Request $request)
    {
        $vouchers = \App\Models\Voucher::with('usages')->orderBy('createdAt', 'desc')->paginate(20);
        
        // Thêm thông tin số lượng đã sử dụng cho mỗi voucher
        $vouchers->getCollection()->transform(function ($voucher) {
            $voucher->used_count = $voucher->getUsedCount();
            $voucher->remaining_count = $voucher->getRemainingCount();
            $voucher->is_valid = $voucher->isValid();
            return $voucher;
        });
        
        return response()->json([
            'data' => $vouchers->items(),
            'pagination' => [
                'current_page' => $vouchers->currentPage(),
                'last_page' => $vouchers->lastPage(),
                'per_page' => $vouchers->perPage(),
                'total' => $vouchers->total(),
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.vouchers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:255|unique:voucher,code',
            'type' => 'required|in:PERCENTAGE,NORMAL',
            'value' => 'required|integer|min:0',
            'max_discount' => 'nullable|integer|min:0',
            'min_order_value' => 'required|integer|min:0',
            'quantity' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        try {
            $voucher = \App\Models\Voucher::create([
                'code' => $request->code,
                'type' => $request->type,
                'value' => $request->value,
                'max_discount' => $request->max_discount ?? 0,
                'min_order_value' => $request->min_order_value,
                'quantity' => $request->quantity,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_used' => false,
            ]);

            return redirect()->route('admin.vouchers.index')
                ->with('success', 'Voucher đã được tạo thành công!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi tạo voucher: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $voucher = \App\Models\Voucher::with('usages.user', 'usages.order')->findOrFail($id);
            $voucher->used_count = $voucher->getUsedCount();
            $voucher->remaining_count = $voucher->getRemainingCount();
            $voucher->is_valid = $voucher->isValid();
            
            return view('admin.vouchers.show', compact('voucher'));
        } catch (\Exception $e) {
            return redirect()->route('admin.vouchers.index')
                ->with('error', 'Không tìm thấy voucher!');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $voucher = \App\Models\Voucher::findOrFail($id);
            return view('admin.vouchers.edit', compact('voucher'));
        } catch (\Exception $e) {
            return redirect()->route('admin.vouchers.index')
                ->with('error', 'Không tìm thấy voucher!');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'code' => 'required|string|max:255|unique:voucher,code,' . $id,
            'type' => 'required|in:PERCENTAGE,NORMAL',
            'value' => 'required|integer|min:0',
            'max_discount' => 'nullable|integer|min:0',
            'min_order_value' => 'required|integer|min:0',
            'quantity' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        try {
            $voucher = \App\Models\Voucher::findOrFail($id);
            
            $voucher->update([
                'code' => $request->code,
                'type' => $request->type,
                'value' => $request->value,
                'max_discount' => $request->max_discount ?? 0,
                'min_order_value' => $request->min_order_value,
                'quantity' => $request->quantity,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);

            return redirect()->route('admin.vouchers.index')
                ->with('success', 'Voucher đã được cập nhật thành công!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi cập nhật voucher: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $voucher = \App\Models\Voucher::findOrFail($id);
            
            // Kiểm tra xem voucher đã được sử dụng chưa
            if ($voucher->getUsedCount() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa voucher đã được sử dụng!'
                ], 400);
            }
            
            $voucher->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Voucher đã được xóa thành công!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa voucher: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API endpoint để sử dụng voucher
     */
    public function useVoucher(Request $request)
    {
        $request->validate([
            'voucher_code' => 'required|string',
            'user_id' => 'required|integer',
            'order_id' => 'required|integer',
            'order_amount' => 'required|numeric|min:0',
        ]);

        try {
            $voucher = \App\Models\Voucher::where('code', $request->voucher_code)->first();
            
            if (!$voucher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Voucher không tồn tại'
                ], 404);
            }

            if (!$voucher->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Voucher không hợp lệ hoặc đã hết hạn'
                ], 400);
            }

            if ($request->order_amount < $voucher->min_order_value) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đơn hàng phải có giá trị tối thiểu ' . number_format($voucher->min_order_value) . ' ₫'
                ], 400);
            }

            $usage = $voucher->useVoucher(
                $request->user_id,
                $request->order_id,
                $request->order_amount
            );

            return response()->json([
                'success' => true,
                'message' => 'Sử dụng voucher thành công',
                'data' => [
                    'voucher' => $voucher,
                    'discount_amount' => $usage->discount_amount,
                    'remaining_count' => $voucher->getRemainingCount()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * API endpoint để kiểm tra voucher
     */
    public function checkVoucher(Request $request)
    {
        $request->validate([
            'voucher_code' => 'required|string',
            'order_amount' => 'required|numeric|min:0',
        ]);

        try {
            $voucher = \App\Models\Voucher::where('code', $request->voucher_code)->first();
            
            if (!$voucher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Voucher không tồn tại'
                ], 404);
            }

            $isValid = $voucher->isValid();
            $discountAmount = $isValid ? $voucher->calculateDiscount($request->order_amount) : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'voucher' => $voucher,
                    'is_valid' => $isValid,
                    'discount_amount' => $discountAmount,
                    'remaining_count' => $voucher->getRemainingCount(),
                    'min_order_value' => $voucher->min_order_value
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
