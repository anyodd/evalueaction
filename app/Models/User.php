<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'nip',
        'email',
        'password',
        'role_id',
        'perwakilan_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function perwakilan()
    {
        return $this->belongsTo(Perwakilan::class);
    }

    public function adminlte_image()
    {
        // Using DiceBear Notionists style for a modern and premium look
        return 'https://api.dicebear.com/9.x/notionists/svg?seed=' . urlencode($this->email) . '&size=128';
    }

    public function adminlte_desc()
    {
        return $this->role->name . ' - ' . ($this->perwakilan->nama_perwakilan ?? 'Pusat');
    }

    public function adminlte_profile_url()
    {
        return 'profile';
    }

    /**
     * Check if user has a specific role.
     *
     * @param string $roleName
     * @return bool
     */
    public function hasRole($roleName)
    {
        return $this->role && $this->role->name === $roleName;
    }
}
