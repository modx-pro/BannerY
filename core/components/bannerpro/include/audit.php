<?php

defined('MODX_CORE_PATH') || exit;

/**
 * @param array<string, mixed> $payload
 */
function bannerpro_audit_log(modX $modx, string $action, string $entity, int $entityId, array $payload = []): void
{
    $user = $modx->getUser();
    if (!$user) {
        return;
    }

    /** @var byAudit|null $record */
    $record = $modx->newObject('byAudit');
    if (!$record) {
        return;
    }

    $record->fromArray([
        'user' => (int) $user->get('id'),
        'username' => (string) $user->get('username'),
        'action' => $action,
        'entity' => $entity,
        'entity_id' => $entityId,
        'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
        'created_at' => date('Y-m-d H:i:s'),
    ], '', true, true);
    $record->save();
}

/**
 * @return array{data: list<array<string, mixed>>, total: int}
 */
function bannerpro_audit_get_list(
    modX $modx,
    int $start,
    int $limit,
    string $sort,
    string $dir,
    ?string $entityFilter = null,
    ?string $actionFilter = null
): array {
    $criteria = $modx->newQuery('byAudit');

    if ($entityFilter !== null && $entityFilter !== '') {
        $criteria->where(['entity' => $entityFilter]);
    }
    if ($actionFilter !== null && $actionFilter !== '') {
        $criteria->where(['action' => $actionFilter]);
    }

    $total = (int) $modx->getCount('byAudit', $criteria);
    bannerpro_apply_list_query($criteria, $sort, $dir, ['id', 'created_at', 'action', 'entity', 'username'], 'id', $start, $limit);

    $rows = [];
    foreach ($modx->getCollection('byAudit', $criteria) as $entry) {
        $row = $entry->toArray();
        $payload = json_decode((string) ($row['payload'] ?? ''), true);
        $row['payload_data'] = is_array($payload) ? $payload : [];
        $row['payload_summary'] = bannerpro_audit_payload_summary($modx, $row);
        $rows[] = $row;
    }

    return ['data' => $rows, 'total' => $total];
}

/**
 * @param array<string, mixed> $row
 */
function bannerpro_audit_payload_summary(modX $modx, array $row): string
{
    $payload = $row['payload_data'] ?? [];
    if (!is_array($payload)) {
        return '';
    }

    if (!empty($payload['name'])) {
        return (string) $payload['name'];
    }
    if (!empty($payload['affected'])) {
        return $modx->lexicon('bannerpro.audit.summary_affected', ['count' => (int) $payload['affected']])
            ?: 'Affected: ' . (int) $payload['affected'];
    }
    if (!empty($payload['removed']) && is_array($payload['removed'])) {
        $count = count($payload['removed']);

        return $modx->lexicon('bannerpro.audit.summary_removed', ['count' => $count])
            ?: (string) $count;
    }

    return '';
}
