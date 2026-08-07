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
        'is_super_admin',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Cerminan nilai bawaan kolom di basis data.
     *
     * Tanpa ini, `Admin::create()` mengembalikan instance yang belum memuat
     * kolom bawaan — `is_active` bernilai null, bukan true — sampai model
     * dibaca ulang. Pemeriksaan keamanan yang membaca instance itu langsung
     * akan salah menyimpulkan bahwa akunnya nonaktif.
     */
    protected $attributes = [
        'is_super_admin' => false,
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
            'is_active' => 'boolean',
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

    /**
     * Relationship ke Admin yang membuat akun ini.
     */
    public function kreatorAdmin()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    /**
     * Relationship ke Admin yang terakhir menyunting akun ini.
     */
    public function penyuntingAdmin()
    {
        return $this->belongsTo(Admin::class, 'updated_by');
    }

    /**
     * Scope untuk filter super admin.
     */
    public function scopeSuperAdmin($query)
    {
        return $query->where('is_super_admin', true);
    }

    /**
     * Scope untuk filter admin yang aktif.
     */
    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Cek apakah admin ini super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin;
    }

    /**
     * Cek apakah admin ini aktif.
     */
    public function isAktif(): bool
    {
        return $this->is_active;
    }

    /**
     * Boot the model dan daftarkan event listeners.
     */
    protected static function booted(): void
    {
        static::creating(function (Admin $admin) {
            if (auth('admin')->check()) {
                $admin->created_by = auth('admin')->id();
            }
        });

        static::updating(function (Admin $admin) {
            if (auth('admin')->check()) {
                $admin->updated_by = auth('admin')->id();
            }
        });
    }
}
