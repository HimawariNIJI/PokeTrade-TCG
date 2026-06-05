<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $term = $request->string('q')->toString();

        $users = User::query()

            // FILTER ROLE
            ->when($filter !== 'all', function ($q) use ($filter) {
                $q->where('role', $filter);
            })

            // SEARCH
            ->when($term, function ($q) use ($term) {
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
            })

            ->latest()
            ->paginate(20)
            ->withQueryString();

        $statusCounts = [
            'all' => User::count(),
            'customer' => User::where('role', 'customer')->count(),
            'admin' => User::where('role', 'admin')->count(),
        ];
        
        return view('admin.users.index', compact('users', 'filter', 'statusCounts'));
    }

    public function show(User $user)
    {
        return view('admin.users.show', [
            'user' => $user->load('orders'),
        ]);
    }

    /**
     * Promote a customer to admin or demote an admin to customer.
     */
    public function updateRole(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors([
                'role' => 'You cannot change your own role.'
            ]);
        }
        
        $validated = $request->validate([
            'role' => [
                'required',
                Rule::in([User::ROLE_CUSTOMER, User::ROLE_ADMIN]),
            ],
        ]);

        $user->update(['role' => $validated['role']]);

        return back()->with('status', "Role updated to {$validated['role']}.");
    }
}
