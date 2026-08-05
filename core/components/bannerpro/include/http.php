<?php

defined('MODX_CORE_PATH') || exit;

/**
 * @param mixed $default
 * @return mixed
 */
function bannerpro_post(string $key, $default = null)
{
    if (isset($_POST[$key])) {
        $value = $_POST[$key];
        return is_array($value) ? $value : trim((string) $value);
    }

    return $default;
}

/**
 * @param array<string, mixed> $payload
 */
function bannerpro_json_response(array $payload, int $status = 200): void
{
    if (!headers_sent()) {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * @param array<string, mixed>|list<mixed> $data
 */
function bannerpro_json_success($data = [], ?int $total = null): void
{
    $payload = ['success' => true, 'data' => $data];
    if ($total !== null) {
        $payload['total'] = $total;
    }
    bannerpro_json_response($payload);
}

function bannerpro_json_error(string $message, int $status = 200): void
{
    bannerpro_json_response(['success' => false, 'message' => $message], $status);
}

function bannerpro_int_post(string $key, int $default = 0): int
{
    return (int) bannerpro_post($key, $default);
}

function bannerpro_require_permission(modX $modx, string $perm): void
{
    if (!$modx->hasPermission($perm)) {
        bannerpro_json_error('permission_denied', 403);
    }
}

function bannerpro_require_mgr_auth(modX $modx): void
{
    $modx->initialize('mgr');
    $modx->getRequest();
    if (!$modx->user || !$modx->user->isAuthenticated('mgr')) {
        bannerpro_json_error('unauthorized', 401);
    }
    bannerpro_load_lexicon($modx, 'default');
    bannerpro_add_package($modx);
}

/**
 * @param array{success: bool, message?: string, data?: mixed} $result
 */
function bannerpro_dispatch_result(array $result, bool $emptyOnSuccess = false): void
{
    if (!$result['success']) {
        bannerpro_json_error($result['message'] ?? 'Error');
    }
    bannerpro_json_success($emptyOnSuccess ? [] : ($result['data'] ?? []));
}
