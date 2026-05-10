<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->string('q')->toString(), fn ($q, $term) =>
                $q->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        return view('admin.users.show', [
            'user' => $user->load('orders'),
        ]);
    }

    /**
     * TODO(team-backend): allow promoting / demoting role with policy check.
     */
    public function updateRole(Request $request, User $user)
    {
        return back()->with('status', 'Role updated (stub).');
    }
}
