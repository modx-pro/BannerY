<?php

/**
 * Resolver: установка зависимостей (pdoTools) при установке/обновлении BannerPro.
 *
 * @package bannerpro
 */

use MODX\Revolution\modAccessPermission;
use MODX\Revolution\modAccessPolicy;
use MODX\Revolution\modAccessPolicyTemplate;
use xPDO\Transport\xPDOTransport;

/** @var xPDOTransport $transport */
/** @var array $options */
if (!$transport->xpdo || !($transport instanceof xPDOTransport)) {
    return true;
}

$modx = $transport->xpdo;

if ($options[xPDOTransport::PACKAGE_ACTION] !== xPDOTransport::ACTION_INSTALL
    && $options[xPDOTransport::PACKAGE_ACTION] !== xPDOTransport::ACTION_UPGRADE
) {
    return true;
}

if (!function_exists('bannerpro_installPackage')) {
    function bannerpro_installPackage(modX $modx, string $packageName): array
    {
        $provider = $modx->getObject('transport.modTransportProvider', [
            'service_url:LIKE' => '%simpledream.ru%',
            'OR:service_url:LIKE' => '%modstore.pro%',
        ]);
        if (!$provider) {
            $provider = $modx->getObject('transport.modTransportProvider', 1);
        }
        if (!$provider) {
            return ['success' => 0, 'message' => 'Transport provider not found.'];
        }

        $modx->getVersionData();
        $productVersion = $modx->version['code_name'] . '-' . $modx->version['full_version'];
        $response = $provider->request('package', 'GET', [
            'supports' => $productVersion,
            'query' => $packageName,
        ]);

        if (empty($response)) {
            return ['success' => 0, 'message' => "Could not find <b>{$packageName}</b> in MODX repository."];
        }

        $foundPackages = simplexml_load_string($response->response);
        foreach ($foundPackages as $foundPackage) {
            if ((string) $foundPackage->name !== $packageName) {
                continue;
            }
            $sig = explode('-', (string) $foundPackage->signature);
            $versionSignature = explode('.', $sig[1] ?? '1.0.0');
            $url = (string) $foundPackage->location;
            $dst = $modx->getOption('core_path') . 'packages/' . $foundPackage->signature . '.transport.zip';

            if (!bannerpro_downloadPackage($url, $dst)) {
                return ['success' => 0, 'message' => "Could not download package <b>{$packageName}</b>."];
            }

            $package = $modx->newObject('transport.modTransportPackage');
            $package->set('signature', (string) $foundPackage->signature);
            $package->fromArray([
                'created' => date('Y-m-d H:i:s'),
                'updated' => null,
                'state' => 1,
                'workspace' => 1,
                'provider' => $provider->get('id'),
                'source' => $foundPackage->signature . '.transport.zip',
                'package_name' => $packageName,
                'version_major' => $versionSignature[0] ?? 1,
                'version_minor' => $versionSignature[1] ?? 0,
                'version_patch' => $versionSignature[2] ?? 0,
            ], '', true, true);

            if (!empty($sig[2])) {
                $r = preg_split('/([0-9]+)/', $sig[2], -1, PREG_SPLIT_DELIM_CAPTURE);
                if (is_array($r) && $r !== []) {
                    $package->set('release', $r[0]);
                    $package->set('release_index', $r[1] ?? '0');
                } else {
                    $package->set('release', $sig[2]);
                }
            }

            if ($package->save() && $package->install()) {
                return ['success' => 1, 'message' => "<b>{$packageName}</b> was successfully installed."];
            }

            return ['success' => 0, 'message' => "Could not save package <b>{$packageName}</b>."];
        }

        return ['success' => 0, 'message' => "Could not find <b>{$packageName}</b> in MODX repository."];
    }
}

if (!function_exists('bannerpro_downloadPackage')) {
    function bannerpro_downloadPackage(string $src, string $dst): bool
    {
        $file = false;
        if (ini_get('allow_url_fopen')) {
            $file = @file_get_contents($src);
        } elseif (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $src);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 180);
            if (empty(@ini_get('safe_mode')) && empty(@ini_get('open_basedir'))) {
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            }
            $file = curl_exec($ch);
            curl_close($ch);
        }
        if ($file === false) {
            return false;
        }
        file_put_contents($dst, $file);

        return is_file($dst);
    }
}

$packages = ['pdoTools' => '2.1.0-pl'];
foreach ($packages as $packageName => $minVersion) {
    $installed = $modx->getIterator('transport.modTransportPackage', ['package_name' => $packageName]);
    $satisfied = false;
    foreach ($installed as $package) {
        if ($package->compareVersion($minVersion, '<=')) {
            $satisfied = true;
            break;
        }
    }
    if ($satisfied) {
        continue;
    }
    $modx->log(modX::LOG_LEVEL_INFO, "[bannerpro] Trying to install <b>{$packageName}</b>. Please wait...");
    $response = bannerpro_installPackage($modx, $packageName);
    $modx->log(
        $response['success'] ? modX::LOG_LEVEL_INFO : modX::LOG_LEVEL_ERROR,
        $response['message']
    );
}

$perms = ['bannerpro_save', 'bannerpro_remove', 'bannerpro_stats'];

$templateNames = [modAccessPolicyTemplate::TEMPLATE_ADMINISTRATOR];
$managerPolicy = $modx->getObject(modAccessPolicy::class, ['name' => 'Manager']);
if ($managerPolicy) {
    $managerTemplate = $managerPolicy->getOne('Template');
    if ($managerTemplate) {
        $managerTemplateName = (string) $managerTemplate->get('name');
        if ($managerTemplateName !== '' && !in_array($managerTemplateName, $templateNames, true)) {
            $templateNames[] = $managerTemplateName;
        }
    }
}

foreach ($templateNames as $templateName) {
    $template = $modx->getObject(modAccessPolicyTemplate::class, ['name' => $templateName]);
    if (!$template) {
        $modx->log(modX::LOG_LEVEL_WARN, "[bannerpro] Access policy template not found: {$templateName}");
        continue;
    }
    $templateId = (int) $template->get('id');
    foreach ($perms as $permName) {
        $permission = $modx->getObject(modAccessPermission::class, [
            'template' => $templateId,
            'name' => $permName,
        ]);
        if ($permission) {
            continue;
        }
        $permission = $modx->newObject(modAccessPermission::class);
        $permission->fromArray([
            'name' => $permName,
            'description' => 'area.bannerpro.' . $permName,
            'template' => $templateId,
            'value' => true,
        ]);
        $permission->save();
    }
}

/** @var modAccessPolicy[] $policies */
$policies = $modx->getCollection(modAccessPolicy::class, ['name:IN' => ['Administrator', 'Manager']]);
foreach ($policies as $policy) {
    $data = $policy->get('data');
    if (is_string($data)) {
        $decoded = json_decode($data, true);
        $data = is_array($decoded) ? $decoded : (unserialize($data, ['allowed_classes' => false]) ?: []);
    }
    if (!is_array($data)) {
        $data = [];
    }
    foreach ($perms as $permName) {
        $data[$permName] = true;
    }
    $policy->set('data', $data);
    $policy->save();
}

$apiKeySetting = $modx->getObject('modSystemSetting', ['key' => 'bannerpro_api_key']);
if ($apiKeySetting && trim((string) $apiKeySetting->get('value')) === '') {
    $apiKeySetting->set('value', bin2hex(random_bytes(16)));
    $apiKeySetting->save();
}

return true;
