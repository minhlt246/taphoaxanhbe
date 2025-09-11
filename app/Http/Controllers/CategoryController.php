<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

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
    public function apiIndex(Request $request): JsonResponse
    {
        // Loại bỏ danh mục "chưa có danh mục nào" (id = 0) khỏi danh sách
        $categories = Category::with('parent')
            ->where('id', '!=', 0)
            ->orderBy('created_at', 'desc')
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
        return view('admin.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'parent_id' => 'nullable|exists:categories,id',
            'image_url' => 'nullable|string|max:500',
        ]);

        try {
            $category = Category::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'parent_id' => $request->parent_id,
                'image_url' => $request->image_url,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Danh mục đã được tạo thành công!',
                'data' => $category
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo danh mục: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $category = Category::with(['parent', 'children', 'products'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy danh mục: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $category = Category::findOrFail($id);
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
            'parent_id' => 'nullable|exists:categories,id',
            'image_url' => 'nullable|string|max:500',
        ]);

        try {
            $category = Category::findOrFail($id);
            
            // Không cho phép cập nhật danh mục mặc định (id = 0)
            if ($id == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể cập nhật danh mục mặc định!'
                ], 400);
            }

            $category->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'parent_id' => $request->parent_id,
                'image_url' => $request->image_url,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Danh mục đã được cập nhật thành công!',
                'data' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật danh mục: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            // Không cho phép xóa danh mục mặc định (id = 0)
            if ($id == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa danh mục mặc định!'
                ], 400);
            }

            $category = Category::findOrFail($id);
            
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
