<?php

namespace App\Services;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send a notification to a specific user.
     */
    public static function send(
        User $user,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?array $metadata = null
    ): ?Notification {
        try {
            $metadata = self::sanitizeMetadata($metadata ?? []);

            return Notification::create([
                'user_id' => $user->id,
                'organization_id' => $user->organization_id, // Context of the notification based on recipient
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'action_url' => $actionUrl,
                'metadata' => empty($metadata) ? null : $metadata,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create notification: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Broadcast a notification to all Super Admins.
     */
    public static function sendToSuperAdmins(
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?array $metadata = null
    ): void {
        $superAdmins = User::role('Super Admin')->where('status', 'active')->get();
        foreach ($superAdmins as $admin) {
            self::send($admin, $type, $title, $message, $actionUrl, $metadata);
        }
    }

    /**
     * Broadcast a notification to all Admins of a specific organization.
     */
    public static function sendToOrganizationAdmins(
        int $organizationId,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?array $metadata = null
    ): void {
        $orgAdmins = User::role('Admin Organisasi')
            ->where('organization_id', $organizationId)
            ->where('status', 'active')
            ->get();
            
        foreach ($orgAdmins as $admin) {
            self::send($admin, $type, $title, $message, $actionUrl, $metadata);
        }
    }

    /**
     * Sanitize metadata to remove sensitive credentials.
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
        
        foreach ($metadata as $k => $v) {
            if (is_array($v)) {
                $metadata[$k] = self::sanitizeMetadata($v);
            }
        }

        return $metadata;
    }
}
