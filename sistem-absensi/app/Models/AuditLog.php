<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $table = 'audit_log';
    protected $primaryKey = 'log_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'log_id',
        'akun_id',
        'aktivitas',
        'waktu_log',
    ];

    public function akun(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'akun_id', 'id');
    }
}
