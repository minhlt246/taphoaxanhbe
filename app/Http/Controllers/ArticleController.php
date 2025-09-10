<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ArticleController extends Controller
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
        return view('admin.articles.index');
    }

    /**
     * API endpoint for listing articles
     */
    public function apiIndex(Request $request)
    {
        // Lấy dữ liệu từ bảng news
        $news = \DB::table('news')
            ->select([
                'id',
                'name as title',
                'description as content',
                'summary',
                'author_id',
                'images as featured_image',
                'views as view_count',
                'likes as like_count',
                'createdAt as created_at',
                'updatedAt as updated_at'
            ])
            ->orderBy('createdAt', 'desc')
            ->paginate(20);
        
        // Transform data để phù hợp với frontend
        $transformedData = $news->items();
        foreach ($transformedData as $item) {
            $item->author_name = 'Admin'; // Có thể lấy từ bảng users nếu cần
            $item->category = 'Tin tức'; // Có thể lấy từ bảng categories nếu cần
            $item->is_published = true;
            $item->is_approved = true;
            $item->is_rejected = false;
        }
        
        return response()->json([
            'data' => $transformedData,
            'pagination' => [
                'current_page' => $news->currentPage(),
                'last_page' => $news->lastPage(),
                'per_page' => $news->perPage(),
                'total' => $news->total(),
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.articles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'summary' => 'nullable|string|max:500',
            'images' => 'nullable|string',
        ]);

        \DB::table('news')->insert([
            'name' => $request->name,
            'description' => $request->description,
            'summary' => $request->summary,
            'images' => $request->images,
            'views' => 0,
            'likes' => 0,
            'comments_count' => 0,
            'author_id' => auth()->id() ?? 1,
            'createdAt' => now(),
            'updatedAt' => now(),
        ]);

        return redirect()->route('admin.articles.index')->with('success', 'Bài viết đã được tạo thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $article = \DB::table('news')->where('id', $id)->first();
        if (!$article) {
            abort(404, 'Bài viết không tồn tại');
        }
        return view('admin.articles.show', compact('article'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $article = \DB::table('news')->where('id', $id)->first();
        if (!$article) {
            abort(404, 'Bài viết không tồn tại');
        }
        return view('admin.articles.edit', compact('article'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $article = \DB::table('news')->where('id', $id)->first();
        if (!$article) {
            abort(404, 'Bài viết không tồn tại');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'summary' => 'nullable|string|max:500',
            'images' => 'nullable|string',
        ]);

        \DB::table('news')->where('id', $id)->update([
            'name' => $request->name,
            'description' => $request->description,
            'summary' => $request->summary,
            'images' => $request->images,
            'updatedAt' => now(),
        ]);

        return redirect()->route('admin.articles.index')->with('success', 'Bài viết đã được cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $article = \DB::table('news')->where('id', $id)->first();
        if (!$article) {
            return response()->json(['error' => 'Bài viết không tồn tại'], 404);
        }

        \DB::table('news')->where('id', $id)->delete();
        
        return response()->json(['message' => 'Bài viết đã được xóa thành công']);
    }
}
