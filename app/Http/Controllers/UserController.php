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
            $query->where(fn($q) => $q->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%"));
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

        if ((int)$user->id === auth()->id()) {
            if (isset($data['role_id']) && (int)$data['role_id'] !== (int)$user->role_id) {
                return back()->with('error', 'You cannot change your own role.');
            }
            if (isset($data['is_active']) && !$data['is_active']) {
                return back()->with('error', 'You cannot deactivate your own account.');
            }
        }

        if ($user->isAdmin() && $user->is_active) {
            $isDemoting = isset($data['role_id']) && (int)$data['role_id'] !== (int)$user->role_id;
            $isDeactivating = isset($data['is_active']) && !$data['is_active'];

            if ($isDemoting || $isDeactivating) {
                $otherActiveAdmins = User::whereHas('role', fn($q) => $q->where('name', 'admin'))
                    ->where('is_active', true)
                    ->where('id', '!=', $user->id)
                    ->count();

                if ($otherActiveAdmins === 0) {
                    return back()->with('error', 'Cannot demote or deactivate the last active administrator.');
                }
            }
        }

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

        if ($user->isAdmin() && $user->is_active) {
            $otherActiveAdmins = User::whereHas('role', fn($q) => $q->where('name', 'admin'))
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->count();

            if ($otherActiveAdmins === 0) {
                return back()->with('error', 'Cannot delete the last active administrator.');
            }
        }

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

        if ($user->is_active && $user->isAdmin()) {
            $otherActiveAdmins = User::whereHas('role', fn($q) => $q->where('name', 'admin'))
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->count();

            if ($otherActiveAdmins === 0) {
                return back()->with('error', 'Cannot deactivate the last active administrator.');
            }
        }

        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'activated' : 'deactivated';
        ActivityLog::log('toggle_active', "User {$user->name} {$status}", $user);
        return back()->with('success', "User \"{$user->name}\" {$status} successfully.");
    }
}
