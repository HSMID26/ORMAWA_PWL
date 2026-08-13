<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    /**
     * Log an activity.
     *
     * @param string $action Action name (e.g. create, update, delete, login, publish)
     * @param string $module Module name (e.g. posts, activities, users, authentication)
     * @param string $description Human readable description of the activity
     * @param Model|null $subject The eloquent model that was affected
     * @param array|null $metadata Additional non-sensitive data
     * @param User|null $user Explicit user (overrides auth()->user())
     * @return ActivityLog
     */
    public static function log(string $action, string $module, string $description, ?Model $subject = null, ?array $metadata = null, ?\App\Models\User $user = null): ActivityLog
    {
        $user = $user ?? auth()->user();
        
        $log = new ActivityLog();
        $log->user_id = $user ? $user->id : null;
        $log->organization_id = $user ? $user->organization_id : null;
        $log->action = $action;
        $log->module = $module;
        $log->description = $description;
        
        if ($subject) {
            $log->subject_type = get_class($subject);
            $log->subject_id = $subject->id;
        }

        // Ensure no sensitive data is present in metadata
        if ($metadata) {
            $metadata = self::sanitizeMetadata($metadata);
            $log->metadata = $metadata;
        }

        $log->ip_address = Request::ip();
        $log->user_agent = Request::userAgent();
        
        $log->save();

        return $log;
    }

    /**
     * Remove sensitive information from metadata before saving.
     */
    private static function sanitizeMetadata(array $metadata): array
    {
        $sensitiveKeys = [
            'password', 
            'password_confirmation', 
            'token', 
            'secret', 
            'authorization',
            'api_key'
        ];

        foreach ($sensitiveKeys as $key) {
            if (array_key_exists($key, $metadata)) {
                unset($metadata[$key]);
            }
        }
        
        // Handle recursive sanitization if needed for nested arrays
        foreach ($metadata as $k => $v) {
            if (is_array($v)) {
                $metadata[$k] = self::sanitizeMetadata($v);
            }
        }

        return $metadata;
    }
}
