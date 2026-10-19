<?php
declare(strict_types=1);

/**
 * Append-only audit trail. Never records passwords, hashes or session tokens.
 * Callers performing privileged mutations should run this inside the same
 * transaction so the action and its audit record commit together.
 */
function audit_insert(?int $actorId, string $actorEmail, string $actorRole, string $action, string $entity, ?int $entityId, array $details = []): void
{
    $payload = $details === [] ? null : json_encode($details, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if (is_string($payload) && mb_strlen($payload) > 2000) {
        $payload = mb_substr($payload, 0, 2000);
    }

    $statement = db()->prepare(
        'INSERT INTO audit_log (actor_user_id, actor_email, actor_role, action, entity, entity_id, details, ip_hash, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())'
    );
    $statement->execute([
        $actorId,
        mb_substr($actorEmail, 0, 190),
        mb_substr($actorRole, 0, 20),
        mb_substr($action, 0, 60),
        mb_substr($entity, 0, 40),
        $entityId !== null ? (string) $entityId : '',
        $payload,
        request_ip_hash(),
    ]);
}

function audit_event(string $action, string $entity, ?int $entityId, array $details = []): void
{
    $actorId = isset($_SESSION['auth_user_id']) ? (int) $_SESSION['auth_user_id'] : null;
    $actorEmail = '';
    $actorRole = '';
    if ($actorId !== null) {
        $user = find_admin_by_id($actorId);
        if ($user !== null) {
            $actorEmail = (string) $user['email'];
            $actorRole = (string) $user['role'];
        }
    }
    audit_insert($actorId, $actorEmail, $actorRole, $action, $entity, $entityId, $details);
}

function audit_event_as(array $actor, string $action, string $entity, ?int $entityId, array $details = []): void
{
    audit_insert(
        (int) $actor['id'],
        (string) $actor['email'],
        (string) $actor['role'],
        $action,
        $entity,
        $entityId,
        $details
    );
}
