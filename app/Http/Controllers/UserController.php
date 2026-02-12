<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = \App\Models\User::with(['role', 'perwakilan'])->latest()->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = \App\Models\Role::all();
        $perwakilan = \App\Models\Perwakilan::all();
        return view('users.create', compact('roles', 'perwakilan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nip' => 'nullable|string|max:20|unique:users',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
            'perwakilan_id' => 'nullable|exists:perwakilan,id',
        ]);

        \App\Models\User::create([
            'nip' => $request->nip,
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role_id' => $request->role_id,
            'perwakilan_id' => $request->perwakilan_id,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan!');
    }

    public function edit(\App\Models\User $user)
    {
        $roles = \App\Models\Role::all();
        $perwakilan = \App\Models\Perwakilan::all();
        return view('users.edit', compact('user', 'roles', 'perwakilan'));
    }

    public function update(Request $request, \App\Models\User $user)
    {
        $request->validate([
            'nip' => 'nullable|string|max:20|unique:users,nip,'.$user->id,
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
            'perwakilan_id' => 'nullable|exists:perwakilan,id',
        ]);

        $data = [
            'nip' => $request->nip,
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'perwakilan_id' => $request->perwakilan_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', 'Data user berhasil diperbarui!');
    }

    public function destroy(\App\Models\User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri!');
        }

        $user->delete();
        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus!');
    }

    public function profile()
    {
        return view('users.profile');
    }
}
