<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Akun extends Authenticatable
{
    use Notifiable;

    protected $table = 'akun';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'username',
        'password',
        'role',
        'role_id',
        'pegawai_id',
    ];

    protected $hidden = [
        'password',
    ];

    // Note: password hashing is handled in service/controller using Hash::make().
    // Keep casts empty to avoid automatic double-hashing.
    protected $casts = [];

    /**
     * Disable remember-token functionality karena kolom `remember_token`
     * tidak ada di tabel `akun`. Fitur "Ingat Saya" ditangani via session.
     */
    public function getRememberToken(): string
    {
        return '';
    }

    public function setRememberToken($value): void
    {
        // Sengaja dikosongkan — tidak ada kolom remember_token di tabel akun.
    }

    public function getRememberTokenName(): string
    {
        return '';
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id', 'pegawai_id');
    }

    public function roleAkses(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    /**
     * Accessor untuk $akun->role agar tetap kompatibel dengan pemanggilan string role lama.
     */
    public function getRoleAttribute($value): ?string
    {
        return $this->roleAkses?->nama_role ?? $value;
    }

    /**
     * Accessor untuk backward compatibility $akun->akun_id
     */
    public function getAkunIdAttribute()
    {
        return $this->attributes['id'] ?? null;
    }
}
