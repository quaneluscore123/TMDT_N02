<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditService
{
    /**
     * Ghi vết giao dịch vào audit_logs (tự điền user, IP, user agent).
     *
     * @param  array<string, mixed>  $metadata
     */
    public static function log(
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        array $metadata = [],
        ?int $userId = null
    ): void {
        $request = request();

        // login_failed chưa auth được → truyền $userId tường minh
        $resolvedUserId = $userId ?? auth()->user()?->getAuthIdentifier();

        if ($resolvedUserId === null && $entityType === 'User' && $entityId !== null) {
            $resolvedUserId = $entityId;
        }

        AuditLog::create([
            'user_id' => $resolvedUserId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'metadata' => $metadata !== [] ? $metadata : null,
        ]);
    }
}
