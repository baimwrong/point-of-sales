<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone'                 => ['nullable', 'string', 'max:20'],
            'current_password'      => ['nullable', 'required_with:password', 'current_password'],
            'password'              => ['nullable', 'min:8', 'confirmed'],
        ]);

        $user->name  = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'] ?? null;

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        ActivityLog::log('profile_update', "User {$user->name} updated their profile", $user);

        return back()->with('success', 'Profile updated successfully.');
    }
}
