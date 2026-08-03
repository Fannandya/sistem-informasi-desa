<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\AdminFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'nama',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Inisial nama untuk avatar teks di panel admin.
     */
    public function inisial(): string
    {
        $bagian = preg_split('/\s+/', trim($this->nama)) ?: [];

        return strtoupper(mb_substr($bagian[0] ?? '?', 0, 1).mb_substr($bagian[1] ?? '', 0, 1));
    }
}
