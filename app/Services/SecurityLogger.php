<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SecurityLogger
{
    public static function authSuccess($user, $ip)
    {
        Log::channel('security')->info('LOGIN_SUCCESS', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $ip,
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString(),
        ]);
    }

    public static function authFailed($email, $ip)
    {
        Log::channel('security')->warning('LOGIN_FAILED', [
            'email' => $email,
            'ip' => $ip,
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString(),
        ]);
    }

    public static function unauthorizedAccess($user, $resource, $ip)
    {
        Log::channel('security')->warning('UNAUTHORIZED_ACCESS', [
            'user_id' => $user?->id,
            'email' => $user?->email,
            'resource' => $resource,
            'ip' => $ip,
            'timestamp' => now()->toISOString(),
        ]);
    }

    public static function suspiciousInput($type, $input, $ip)
    {
        Log::channel('security')->alert('SUSPICIOUS_INPUT', [
            'type' => $type,
            'input_preview' => substr($input, 0, 200),
            'ip' => $ip,
            'timestamp' => now()->toISOString(),
        ]);
    }

    public static function dataExport($userId, $targetUserId, $ip)
    {
        Log::channel('security')->info('DATA_EXPORT', [
            'user_id' => $userId,
            'target_user_id' => $targetUserId,
            'ip' => $ip,
            'timestamp' => now()->toISOString(),
        ]);
    }

    public static function privilegeChange($adminId, $targetUserId, $oldRole, $newRole)
    {
        Log::channel('security')->warning('PRIVILEGE_CHANGE', [
            'admin_id' => $adminId,
            'target_user_id' => $targetUserId,
            'old_role' => $oldRole,
            'new_role' => $newRole,
            'ip' => request()->ip(),
            'timestamp' => now()->toISOString(),
        ]);
    }

    public static function userDeleted($adminId, $deletedUserId, $deletedEmail)
    {
        Log::channel('security')->warning('USER_DELETED', [
            'admin_id' => $adminId,
            'deleted_user_id' => $deletedUserId,
            'deleted_email' => $deletedEmail,
            'ip' => request()->ip(),
            'timestamp' => now()->toISOString(),
        ]);
    }
}
