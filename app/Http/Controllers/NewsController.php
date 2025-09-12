<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.news.index');
    }

    /**
     * API endpoint for listing news
     */
    public function apiIndex(Request $request): JsonResponse
    {
        $query = News::orderBy('created_at', 'desc');
        
        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            switch ($request->status) {
                case 'published':
                    $query->where('is_published', true)->where('is_approved', true);
                    break;
                case 'approved':
                    $query->where('is_approved', true)->where('is_published', false);
                    break;
                case 'pending':
                    $query->where('is_approved', false)->where('is_rejected', false);
                    break;
                case 'rejected':
                    $query->where('is_rejected', true);
                    break;
            }
        }
        
        // Get pagination parameters
        $perPage = $request->get('limit', 20);
        $page = $request->get('page', 1);
        
        $news = $query->paginate($perPage, ['*'], 'page', $page);
        
        return response()->json([
            'data' => $news->items(),
            'current_page' => $news->currentPage(),
            'last_page' => $news->lastPage(),
            'per_page' => $news->perPage(),
            'total' => $news->total(),
            'from' => $news->firstItem(),
            'to' => $news->lastItem(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.news.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'summary' => 'required|string|max:500',
            'author_id' => 'required|exists:users,id',
            'author_name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'tags' => 'nullable|string',
            'featured_image' => 'nullable|string',
            'images' => 'nullable|string',
        ]);

        $validated['is_published'] = false;
        $validated['is_approved'] = false;
        $validated['is_rejected'] = false;
        $validated['view_count'] = 0;
        $validated['like_count'] = 0;

        News::create($validated);

        return redirect()->route('admin.news.index')
            ->with('success', 'Bài viết đã được tạo thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(News $news)
    {
        return view('admin.news.show', compact('news'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(News $news)
    {
        return view('admin.news.edit', compact('news'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, News $news)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'summary' => 'required|string|max:500',
            'author_id' => 'required|exists:users,id',
            'author_name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'tags' => 'nullable|string',
            'featured_image' => 'nullable|string',
            'images' => 'nullable|string',
            'is_published' => 'boolean',
            'is_approved' => 'boolean',
            'is_rejected' => 'boolean',
            'rejection_reason' => 'nullable|string',
        ]);

        $news->update($validated);

        return redirect()->route('admin.news.index')
            ->with('success', 'Bài viết đã được cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(News $news)
    {
        $news->delete();

        return redirect()->route('admin.news.index')
            ->with('success', 'Bài viết đã được xóa thành công!');
    }

    /**
     * Approve news
     */
    public function approve(News $news)
    {
        $news->update([
            'is_approved' => true,
            'is_rejected' => false,
            'rejection_reason' => null,
        ]);

        return response()->json(['success' => true, 'message' => 'Bài viết đã được duyệt!']);
    }

    /**
     * Reject news
     */
    public function reject(Request $request, News $news)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $news->update([
            'is_approved' => false,
            'is_rejected' => true,
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return response()->json(['success' => true, 'message' => 'Bài viết đã bị từ chối!']);
    }

    /**
     * Publish news
     */
    public function publish(News $news)
    {
        if (!$news->is_approved) {
            return response()->json(['success' => false, 'message' => 'Bài viết chưa được duyệt!'], 400);
        }

        $news->update([
            'is_published' => true,
            'published_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Bài viết đã được xuất bản!']);
    }

    /**
     * Unpublish news
     */
    public function unpublish(News $news)
    {
        $news->update([
            'is_published' => false,
            'published_at' => null,
        ]);

        return response()->json(['success' => true, 'message' => 'Bài viết đã được gỡ xuất bản!']);
    }

    /**
     * Get news statistics
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total' => News::count(),
            'published' => News::where('is_published', true)->where('is_approved', true)->count(),
            'approved' => News::where('is_approved', true)->where('is_published', false)->count(),
            'pending' => News::where('is_approved', false)->where('is_rejected', false)->count(),
            'rejected' => News::where('is_rejected', true)->count(),
        ];

        return response()->json($stats);
    }
}