<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProductController extends Controller
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
        
        // Get products for the view
        $products = Product::with(['category'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // Get total statistics for all products (not just paginated)
        $totalProducts = Product::count();
        $inStockProducts = Product::where('quantity', '>', 0)->count();
        $outOfStockProducts = Product::where('quantity', '=', 0)->count();
        $activeProducts = Product::where('status', 'active')->count();
        
        // Otherwise return view with data
        return view('admin.products.index', compact('products', 'totalProducts', 'inStockProducts', 'outOfStockProducts', 'activeProducts'));
    }

    /**
     * API endpoint for listing products
     */
    public function apiIndex(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'creator', 'updater']);

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by brand
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by name or description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('limit', 20);
        $products = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'products' => $products->items(),
            'pagination' => [
                'page' => $products->currentPage(),
                'limit' => $perPage,
                'total' => $products->total(),
                'totalPages' => $products->lastPage()
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::where('id', '!=', 0)->get();
        return view('admin.products.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|integer',
            'images' => 'nullable|array',
            'stock' => 'nullable|integer|min:0',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        // Check if product name already exists
        $existingProduct = Product::where('name', $request->name)->first();
        if ($existingProduct) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'Tên sản phẩm đã tồn tại'
                ], 400);
            }
            return back()->withErrors(['name' => 'Tên sản phẩm đã tồn tại']);
        }

        $quantity = $request->stock ?? 0;
        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
            'images' => $request->images,
            'quantity' => $quantity,
            'totalQuantity' => $quantity, // Set totalQuantity = quantity khi tạo mới
            'status' => $request->status ?? 'active',
            'slug' => Str::slug($request->name),
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'id' => $product->id,
                'message' => 'Sản phẩm đã được tạo thành công',
                'product' => $product->load(['category', 'creator'])
            ], 201);
        }

        return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được tạo thành công');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        // If it's an API request, return JSON
        if (request()->expectsJson() || request()->is('api/*')) {
            $product->load(['category', 'reviews.user', 'creator', 'updater']);
            return response()->json($product);
        }
        
        // For web request, return view
        $product->load(['category', 'creator', 'updater']);
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $categories = Category::where('id', '!=', 0)->get();
        $product->load(['category']);
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|integer',
            'images' => 'nullable|array',
            'stock' => 'nullable|integer|min:0',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        // Check if product name already exists (excluding current product)
        if ($request->filled('name')) {
            $existingProduct = Product::where('name', $request->name)
                ->where('id', '!=', $product->id)
                ->first();
            if ($existingProduct) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'error' => 'Tên sản phẩm đã tồn tại'
                    ], 400);
                }
                return back()->withErrors(['name' => 'Tên sản phẩm đã tồn tại']);
            }
        }

        $updateData = $request->only([
            'name', 'description', 'price', 'category_id', 'brand_id', 
            'images', 'status'
        ]);

        if ($request->filled('stock')) {
            $updateData['quantity'] = $request->stock;
        }

        if ($request->filled('name')) {
            $updateData['slug'] = Str::slug($request->name);
        }

        $updateData['updated_by'] = Auth::id();

        $product->update($updateData);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Sản phẩm đã được cập nhật thành công',
                'product' => $product->load(['category', 'creator', 'updater'])
            ]);
        }

        return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được cập nhật thành công');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json([
                'message' => 'Sản phẩm đã được xóa thành công'
            ]);
        }

        return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được xóa thành công');
    }

    /**
     * Get product statistics
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total' => Product::count(),
            'active' => Product::where('status', 'active')->count(),
            'inactive' => Product::where('status', 'inactive')->count(),
            'in_stock' => Product::where('quantity', '>', 0)->count(),
            'out_of_stock' => Product::where('quantity', '=', 0)->count(),
            'total_stock' => Product::sum('quantity'),
            'average_rating' => Product::avg('avg_rating'),
        ];

        return response()->json($stats);
    }

    /**
     * Get top purchased products
     */
    public function topPurchased(): JsonResponse
    {
        $products = Product::orderBy('purchase', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'purchase', 'price', 'images']);

        return response()->json($products);
    }
}
