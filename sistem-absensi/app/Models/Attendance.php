<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $table = 'absensi';
    protected $primaryKey = 'absensi_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'absensi_id',
        'pegawai_id',
        'tanggal_absensi',
        'jam_checkin',
        'jam_checkout',
        'skema_kerja',
        'status_kehadiran',
        'lokasi_id',
        'catatan',
        'created_by',
        'source',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id', 'pegawai_id');
    }

    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(WorkLocation::class, 'lokasi_id', 'lokasi_id');
    }
}
