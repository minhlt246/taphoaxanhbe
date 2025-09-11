<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
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
        return view('admin.users.index');
    }
    
    /**
     * Update user's last activity
     */
    public function updateActivity(Request $request)
    {
        $user = auth()->user();
        if ($user) {
            $user->update(['lastActivity' => now()]);
        }
        return response()->json(['success' => true]);
    }

    /**
     * API endpoint for listing users
     */
    public function apiIndex(Request $request)
    {
        $query = User::query();

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role if exists
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $perPage = $request->get('limit', 20);
        $users = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'data' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Người dùng đã được tạo thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);
            $user->update([
                'password' => bcrypt($request->password),
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Người dùng đã được cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Người dùng đã được xóa thành công!');
    }
}