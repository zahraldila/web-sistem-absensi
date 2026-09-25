<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterJabatan extends Model
{
    protected $table = 'master_jabatan';
    protected $primaryKey = 'jabatan_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nama_jabatan',
        'organization_id',
    ];

    public function pegawais(): HasMany
    {
        return $this->hasMany(Pegawai::class, 'jabatan_id', 'jabatan_id');
    }

    public function organization(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }
}
