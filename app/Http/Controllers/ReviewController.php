<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReviewController extends Controller
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
        return view('admin.reviews.index');
    }

    /**
     * API endpoint for listing reviews
     */
    public function apiIndex(Request $request)
    {
        $query = \App\Models\Review::with(['product', 'user'])->orderBy('createdAt', 'desc');
        
        // Get pagination parameters
        $perPage = $request->get('limit', 20);
        $page = $request->get('page', 1);
        
        $reviews = $query->paginate($perPage, ['*'], 'page', $page);
        
        return response()->json([
            'data' => $reviews->items(),
            'current_page' => $reviews->currentPage(),
            'last_page' => $reviews->lastPage(),
            'per_page' => $reviews->perPage(),
            'total' => $reviews->total(),
            'from' => $reviews->firstItem(),
            'to' => $reviews->lastItem(),
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
        $review = \App\Models\Review::findOrFail($id);
        $review->delete();

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json([
                'message' => 'Đánh giá đã được xóa thành công'
            ]);
        }

        return back()->with('success', 'Đánh giá đã được xóa thành công');
    }

    /**
     * Approve a review
     */
    public function approve(string $id)
    {
        $review = \App\Models\Review::findOrFail($id);
        $review->update([
            'status' => 'approved',
            'admin_id' => auth()->id() ?? 1, // Default to admin ID 1 if not authenticated
            'reviewed_at' => now(),
        ]);

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json([
                'message' => 'Đánh giá đã được phê duyệt',
                'review' => $review
            ]);
        }

        return back()->with('success', 'Đánh giá đã được phê duyệt');
    }

    /**
     * Reject a review
     */
    public function reject(string $id)
    {
        $review = \App\Models\Review::findOrFail($id);
        $review->update([
            'status' => 'rejected',
            'admin_id' => auth()->id() ?? 1, // Default to admin ID 1 if not authenticated
            'reviewed_at' => now(),
        ]);

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json([
                'message' => 'Đánh giá đã bị từ chối',
                'review' => $review
            ]);
        }

        return back()->with('success', 'Đánh giá đã bị từ chối');
    }
}
