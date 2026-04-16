<?php
function log_activity(PDO $pdo, int $userId, string $action, ?string $entityType = null, ?int $entityId = null, ?string $description = null): void
{
    $stmt = $pdo->prepare('INSERT INTO activity_logs (user_id, action, entity_type, entity_id, description, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
    $stmt->execute([$userId, $action, $entityType, $entityId, $description]);
}
