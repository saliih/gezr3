<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Support\Permissions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('username')->paginate(20);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string|max:180|unique:user,username',
            'password' => 'required|string|min:6|confirmed',
            'roles'    => 'nullable|array',
        ]);

        $attrs = [
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'roles'    => $data['roles'] ?? ['ROLE_USER'],
        ];
        if (Schema::hasColumn('user', 'permissions')) {
            $attrs['permissions'] = Permissions::sanitize($request->input('permissions'));
        }

        User::create($attrs);

        return redirect()->route('users.index')
            ->with('success', 'تم إضافة المستخدم بنجاح.');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'username' => 'required|string|max:180|unique:user,username,' . $user->id,
            'roles'    => 'nullable|array',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'string|min:6|confirmed';
        }

        $data = $request->validate($rules);

        $user->username = $data['username'];
        $user->roles    = $data['roles'] ?? ['ROLE_USER'];

        if (Schema::hasColumn('user', 'permissions')) {
            $user->permissions = Permissions::sanitize($request->input('permissions'));
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()->route('users.index')
            ->with('success', 'تم تحديث المستخدم بنجاح.');
    }

    public function destroy(User $user)
    {
        if ($user->getAuthIdentifier() === auth()->user()->getAuthIdentifier()) {
            return back()->with('error', 'لا يمكنك حذف حسابك الخاص.');
        }
        $user->delete();
        return redirect()->route('users.index')
            ->with('success', 'تم حذف المستخدم.');
    }
}
