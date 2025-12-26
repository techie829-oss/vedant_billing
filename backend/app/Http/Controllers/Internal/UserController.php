<?php

namespace App\Http\Controllers\Internal;

use App\Enums\InternalRole;
use App\Http\Controllers\Controller;
use App\Models\InUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Enum;

class UserController extends Controller
{
    /**
     * Display a listing of all admin users (Internal + Business Owners).
     */
    public function index()
    {
        // Fetch Users who are either Internal (have inUser) OR are Business Owners
        $users = User::with([
            'inUser',
            'businesses' => function ($q) {
                // We only care if they are owners for identifying them as Business Admins
                $q->wherePivot('role', 'owner');
            }
        ])
            ->whereHas('inUser')
            ->orWhereHas('businesses', function ($q) {
                $q->where('business_users.role', 'owner');
            })
            ->latest()
            ->paginate(20);

        return view('internal.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new internal user.
     */
    public function create()
    {
        return view('internal.users.create');
    }

    /**
     * Store a newly created internal user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => ['required', new Enum(InternalRole::class)],
            'password' => 'required|min:8',
        ]);

        // 1. Create Core User
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => 'active',
        ]);

        // 2. Create InUser Profile
        InUser::create([
            'user_id' => $user->id,
            'access_level' => $request->role,
            'status' => 'active',
            'permissions' => [] // Default empty, can be enhanced
        ]);

        return redirect()->route('internal.users.index')
            ->with('success', 'Internal user created successfully.');
    }
    /**
     * Show the form for changing any user's password.
     */
    public function editPassword(User $user)
    {
        return view('internal.users.password', compact('user'));
    }

    /**
     * Update any user's password.
     */
    public function updatePassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|confirmed|min:8',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('internal.users.index')
            ->with('success', "Password for {$user->name} updated successfully.");
    }
}
