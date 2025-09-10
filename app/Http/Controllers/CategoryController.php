<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CategoryController extends Controller
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
        return view('admin.categories.index');
    }

    /**
     * API endpoint for listing categories
     */
    public function apiIndex(Request $request)
    {
        // Loại bỏ danh mục "chưa có danh mục nào" (id = 0) khỏi danh sách
        $categories = \App\Models\Category::with('parent')
            ->where('id', '!=', 0)
            ->paginate(20);
        
        return response()->json([
            'data' => $categories->items(),
            'pagination' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
            ]
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
     */
    public function destroy(string $id)
    {
        try {
            // Không cho phép xóa danh mục mặc định (id = 0)
            if ($id == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa danh mục mặc định!'
                ], 400);
            }

            $category = \App\Models\Category::findOrFail($id);
            
            // Cập nhật tất cả sản phẩm trong danh mục này về danh mục mặc định (id = 0)
            \App\Models\Product::where('category_id', $id)->update(['category_id' => 0]);
            
            // Xóa danh mục
            $category->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Danh mục đã được xóa thành công! Các sản phẩm trong danh mục này đã được chuyển về "Chưa có danh mục nào".'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa danh mục: ' . $e->getMessage()
            ], 500);
        }
    }
}
