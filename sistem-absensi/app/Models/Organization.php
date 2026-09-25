<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    protected $table = 'organizations';
    protected $primaryKey = 'organization_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'nama_organisasi',
        'kode_organisasi',
        'alamat',
        'status',
        'display_token',
    ];

    public function pegawais(): HasMany
    {
        return $this->hasMany(Pegawai::class, 'organization_id', 'organization_id');
    }

    public function masterDivisis(): HasMany
    {
        return $this->hasMany(MasterDivisi::class, 'organization_id', 'organization_id');
    }

    public function masterJabatans(): HasMany
    {
        return $this->hasMany(MasterJabatan::class, 'organization_id', 'organization_id');
    }

    public function jadwalKerjas(): HasMany
    {
        return $this->hasMany(WorkSchedule::class, 'organization_id', 'organization_id');
    }

    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class, 'organization_id', 'organization_id');
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class, 'organization_id', 'organization_id');
    }
}
