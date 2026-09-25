<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Approval extends Model
{
    protected $table = 'pengajuan';
    protected $primaryKey = 'pengajuan_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'pengajuan_id',
        'pegawai_id',
        'jenis_pengajuan',
        'lampiran',
        'tanggal_pengajuan',
        'keterangan',
        'status_pengajuan',
        'created_by',
        'source',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id', 'pegawai_id');
    }
}
