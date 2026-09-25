<?php

namespace App\Models;

use App\Models\Akun;
use App\Models\Nfc;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pegawai extends Model
{
    protected $table = 'pegawai';
    protected $primaryKey = 'pegawai_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'pegawai_id',
        'nip',
        'nama_pegawai',
        'email',
        'no_handphone',
        'divisi_id',
        'jabatan_id',
        'status',
        'foto_profile',
        'organization_id',
    ];

    public function akun(): HasOne
    {
        return $this->hasOne(Akun::class, 'pegawai_id', 'pegawai_id');
    }

    public function masterDivisi(): BelongsTo
    {
        return $this->belongsTo(MasterDivisi::class, 'divisi_id', 'divisi_id');
    }

    public function masterJabatan(): BelongsTo
    {
        return $this->belongsTo(MasterJabatan::class, 'jabatan_id', 'jabatan_id');
    }

    public function nfc(): HasOne
    {
        return $this->hasOne(Nfc::class, 'pegawai_id', 'pegawai_id');
    }

    public function absensi(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Attendance::class, 'pegawai_id', 'pegawai_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }
}
