<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $query = \App\Models\User::with(['role', 'perwakilan'])->latest();

        // Scoping per Perwakilan (kecuali Superadmin / Rendal)
        if (!auth()->user()->role || (!in_array(auth()->user()->role->name, ['Superadmin', 'Rendal']))) {
            $query->where('perwakilan_id', auth()->user()->perwakilan_id);
        }

        $users = $query->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = \App\Models\Role::all();
        $perwakilan_query = \App\Models\Perwakilan::query();
        
        // Scope for Admin Perwakilan
        if (auth()->user()->role && auth()->user()->role->name === 'Admin Perwakilan') {
            $perwakilan_query->where('id', auth()->user()->perwakilan_id);
        }
        
        $perwakilan = $perwakilan_query->get();
        return view('users.create', compact('roles', 'perwakilan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nip' => 'nullable|string|max:25|unique:users',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users|regex:/^[a-zA-Z0-9._%+-]+@bpkp\.go\.id$/i',
            'password' => 'required|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
            'perwakilan_id' => 'nullable|exists:perwakilan,id',
        ], [
            'email.regex' => 'Wajib menggunakan email dengan domain @bpkp.go.id (Email GWS).',
        ]);

        $role = \App\Models\Role::find($request->role_id);
        $nip = $request->nip;
        if ($role && in_array($role->name, ['Superadmin', 'Admin Perwakilan', 'Rendal'])) {
            $nip = null;
        }

        \App\Models\User::create([
            'nip' => $nip,
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
        $perwakilan_query = \App\Models\Perwakilan::query();
        
        // Scope for Admin Perwakilan
        if (auth()->user()->role && auth()->user()->role->name === 'Admin Perwakilan') {
            $perwakilan_query->where('id', auth()->user()->perwakilan_id);
        }
        
        $perwakilan = $perwakilan_query->get();
        return view('users.edit', compact('user', 'roles', 'perwakilan'));
    }

    public function update(Request $request, \App\Models\User $user)
    {
        $request->validate([
            'nip' => 'nullable|string|max:25|unique:users,nip,'.$user->id,
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id.'|regex:/^[a-zA-Z0-9._%+-]+@bpkp\.go\.id$/i',
            'password' => 'nullable|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
            'perwakilan_id' => 'nullable|exists:perwakilan,id',
        ], [
            'email.regex' => 'Wajib menggunakan email dengan domain @bpkp.go.id (Email GWS).',
        ]);

        $role = \App\Models\Role::find($request->role_id);
        $nip = $request->nip;
        if ($role && in_array($role->name, ['Superadmin', 'Admin Perwakilan', 'Rendal'])) {
            $nip = null;
        }

        $data = [
            'nip' => $nip,
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
