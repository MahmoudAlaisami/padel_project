<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');

        $users = User::withCount('reservations')
            ->when($search, fn ($q) => $q->where(function ($q2) use ($search) {
                $q2->where('full_name', 'like', "%{$search}%")
                   ->orWhere('email', 'like', "%{$search}%");
            }))
            ->latest()
            ->get();

        return view('admin.users', compact('users', 'search'));
    }

    public function action(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'action'  => ['required', 'in:promote,demote,delete'],
        ]);

        if ((int) $request->user_id === auth()->id()) {
            return redirect()->route('admin.users')->with('success', 'Cannot modify your own account here.');
        }

        $user = User::findOrFail($request->user_id);

        match ($request->action) {
            'promote' => $user->update(['role' => 'admin']),
            'demote'  => $user->update(['role' => 'user']),
            'delete'  => $user->delete(),
        };

        $messages = [
            'promote' => 'User promoted to admin.',
            'demote'  => 'User demoted to regular user.',
            'delete'  => 'User deleted.',
        ];

        return redirect()->route('admin.users')->with('success', $messages[$request->action]);
    }
}
