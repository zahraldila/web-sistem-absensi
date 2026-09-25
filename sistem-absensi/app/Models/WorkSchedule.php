<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkSchedule extends Model
{
    protected $table = 'jadwal_kerja';
    protected $primaryKey = 'jadwal_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'tanggal_berlaku',
        'jam_masuk',
        'jam_pulang',
        'organization_id',
    ];

    public function organization(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }
}
