<?php

defined('MODX_CORE_PATH') || exit;

/**
 * @param \xPDOQuery $criteria
 */
function bannerpro_apply_resource_class_key_filter(\xPDOQuery $criteria, string $classKey): void
{
    if ($classKey === '') {
        return;
    }

    // MiniShop2/3: class_key может быть msProduct или MiniShop3\Model\msProduct (как в ms3ProductSets)
    if (stripos($classKey, 'msProduct') !== false) {
        $criteria->where(['class_key:LIKE' => '%msProduct']);
        $criteria->where(['deleted' => 0, 'published' => 1]);

        return;
    }

    if (stripos($classKey, 'msCategory') !== false) {
        $criteria->where(['class_key:LIKE' => '%msCategory']);
        $criteria->where(['deleted' => 0, 'published' => 1]);

        return;
    }

    $criteria->where(['class_key' => $classKey]);
}

/**
 * @return array{data: list<array<string, mixed>>, total: int}
 */
function bannerpro_resource_get_list(modX $modx, string $query, int $start, int $limit, string $classKey = ''): array
{
    $criteria = $modx->newQuery('modResource');
    $criteria->select('id,parent,pagetitle,context_key,class_key');
    bannerpro_apply_resource_class_key_filter($criteria, $classKey);
    if ($query !== '') {
        $criteria->where(['pagetitle:LIKE' => '%' . $query . '%']);
    }

    $total = (int) $modx->getCount('modResource', $criteria);
    bannerpro_apply_list_query($criteria, 'pagetitle', 'ASC', ['pagetitle', 'id'], 'pagetitle', $start, $limit);

    $rows = [];
    foreach ($modx->getCollection('modResource', $criteria) as $resource) {
        $rows[] = bannerpro_prepare_resource_row($modx, $resource->toArray());
    }

    if ($query !== '' && (strpos($query, '/') === 0 || strpos($query, '://') !== false)) {
        $rows[] = ['pagetitle' => $query, 'url' => $query];
        $total++;
    }

    return ['data' => $rows, 'total' => $total];
}

/**
 * @param array<string, mixed> $resourceArray
 * @return array<string, mixed>
 */
function bannerpro_prepare_resource_row(modX $modx, array $resourceArray): array
{
    $parents = $modx->getParentIds((int) $resourceArray['id'], 2, ['context' => $resourceArray['context_key']]);
    if (!empty($parents) && $parents[count($parents) - 1] == 0) {
        unset($parents[count($parents) - 1]);
    }

    $resourceArray['parents'] = [];
    if (!empty($parents) && is_array($parents)) {
        $parentQuery = $modx->newQuery('modResource', ['id:IN' => $parents]);
        $parentQuery->select('id,pagetitle');
        if ($parentQuery->prepare() && $parentQuery->stmt->execute()) {
            while ($row = $parentQuery->stmt->fetch(PDO::FETCH_ASSOC)) {
                $key = array_search($row['id'], $parents, true);
                if ($key !== false) {
                    $parents[$key] = $row;
                }
            }
        }
        $resourceArray['parents'] = array_reverse($parents);
    }

    $resourceArray['url'] = '[[~' . $resourceArray['id'] . ']]';
    return $resourceArray;
}

function bannerpro_resource_pagetitle(modX $modx, int $resourceId): string
{
    if ($resourceId <= 0) {
        return '';
    }

    /** @var modResource|null $resource */
    $resource = $modx->getObject('modResource', $resourceId);

    return $resource ? (string) $resource->get('pagetitle') : '';
}
