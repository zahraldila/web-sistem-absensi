<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'organization_id'];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public static function getActiveOrganizationId()
    {
        return \App\Helpers\OrganizationHelper::getActiveOrganizationId();
    }

    public static function get($key, $default = null)
    {
        try {
            $orgId = self::getActiveOrganizationId();
            if (!$orgId) {
                return $default;
            }

            $setting = self::where('key', $key)
                           ->where('organization_id', $orgId)
                           ->first();
            
            return $setting ? $setting->value : $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    public static function set($key, $value)
    {
        $orgId = self::getActiveOrganizationId();
        
        if (!$orgId) {
            throw new \Exception("Cannot set organization-scoped setting '{$key}' without an active organization context.");
        }

        return self::updateOrCreate(
            ['key' => $key, 'organization_id' => $orgId],
            ['value' => $value]
        );
    }
}
