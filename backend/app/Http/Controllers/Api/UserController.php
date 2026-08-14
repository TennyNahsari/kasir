<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['outlet.location', 'location']);

        // Filter by role
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Filter by outlet
        if ($request->has('outlet_id')) {
            $query->where('outlet_id', $request->outlet_id);
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Filter by is_technician flag
        if ($request->has('is_technician')) {
            $query->where('is_technician', $request->is_technician);
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 25);

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in(['owner', 'inventory', 'supervisor', 'staff', 'admin', 'kasir', 'kitchen', 'procurement', 'warehouse'])],
            'outlet_id' => 'nullable|exists:outlets,id',
            'location_id' => 'nullable|exists:locations,id',
            'is_active' => 'nullable|boolean',
            'is_technician' => 'nullable|boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $validated['is_active'] ?? true;

        $user = User::create($validated);

        return response()->json($user->load(['outlet.location', 'location']), 201);
    }

    public function show(User $user)
    {
        return response()->json($user->load(['outlet.location', 'location']));
    }

    public function update(Request $request, User $user)
    {
        \Log::info('Update user request', [
            'user_id' => $user->id,
            'request_data' => $request->all()
        ]);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'role' => ['sometimes', Rule::in(['owner', 'inventory', 'supervisor', 'staff', 'admin', 'kasir', 'kitchen', 'procurement', 'warehouse'])],
            'outlet_id' => 'nullable|exists:outlets,id',
            'location_id' => 'nullable|exists:locations,id',
            'is_active' => 'sometimes|boolean',
            'is_technician' => 'sometimes|boolean',
        ]);

        \Log::info('Validated data', ['validated' => $validated]);

        // Only hash password if provided
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
        
        \Log::info('User updated', [
            'user_id' => $user->id,
            'outlet_id' => $user->outlet_id
        ]);

        return response()->json($user->fresh()->load('outlet.location'));
    }

    public function destroy(User $user)
    {
        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Cannot delete your own account'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
