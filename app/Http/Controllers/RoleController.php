<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = \App\Models\Role::all();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        return view('roles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles',
        ]);

        \App\Models\Role::create($request->all());

        return redirect()->route('roles.index')
            ->with('success', 'Role berhasil ditambahkan!');
    }

    public function edit(\App\Models\Role $role)
    {
        return view('roles.edit', compact('role'));
    }

    public function update(Request $request, \App\Models\Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,'.$role->id,
        ]);

        $role->update($request->all());

        return redirect()->route('roles.index')
            ->with('success', 'Role berhasil diperbarui!');
    }

    public function destroy(\App\Models\Role $role)
    {
        // Prevent deleting if role has users
        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')
                ->with('error', 'Role tidak dapat dihapus karena masih memiliki user aktif!');
        }

        $role->delete();
        return redirect()->route('roles.index')
            ->with('success', 'Role berhasil dihapus!');
    }
}
