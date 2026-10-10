<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\ActivityLog;
use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('role')->latest();
        if ($s = $request->get('search')) {
            $query->where(fn($q) => $q->where('name','ilike',"%$s%")->orWhere('email','ilike',"%$s%"));
        }
        $users = $query->paginate(15)->withQueryString();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::orderBy('display_name')->get();
        return view('users.create', compact('roles'));
    }

    public function store(UserRequest $request)
    {
        $data = $request->validated();
        $user = User::create($data);
        ActivityLog::log('create', "Created user: {$user->name} ({$user->email})", $user);
        return redirect()->route('users.index')->with('success', "User \"{$user->name}\" created.");
    }

    public function edit($id)
    {
        $user  = User::findOrFail($id);
        $roles = Role::orderBy('display_name')->get();
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(UserRequest $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->validated();
        if (empty($data['password'])) unset($data['password']);
        $user->update($data);
        ActivityLog::log('update', "Updated user: {$user->name}", $user);
        return redirect()->route('users.index')->with('success', "User \"{$user->name}\" updated.");
    }

    public function destroy($id)
    {
        if ((int)$id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        $user = User::findOrFail($id);
        $name = $user->name;
        $user->delete();
        ActivityLog::log('delete', "Deleted user: {$name}");
        return redirect()->route('users.index')->with('success', "User \"{$name}\" deleted.");
    }

    public function toggleActive($id)
    {
        if ((int)$id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }
        $user = User::findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'activated' : 'deactivated';
        ActivityLog::log('toggle_active', "User {$user->name} {$status}", $user);
        return back()->with('success', "User \"{$user->name}\" {$status} successfully.");
    }
}
