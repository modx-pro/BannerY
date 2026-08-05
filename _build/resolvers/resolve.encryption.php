<?php

/**
 * Encryption Resolver for MODX Revolution 3.x
 *
 * Loads EncryptedVehicle class during package installation/upgrade.
 * On UNINSTALL the file may already be removed by file resolver — do not fail.
 *
 * @var xPDO\Transport\xPDOTransport $transport
 * @var array $options
 */

use xPDO\Transport\xPDOTransport;
use xPDO\xPDO;

if (!defined('COMPONENT_NAME')) {
    define('COMPONENT_NAME', 'bannerpro');
}

$success = true;

if ($transport->xpdo) {
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            $vehiclePath = MODX_CORE_PATH . 'components/' . COMPONENT_NAME . '/Transport/EncryptedVehicle.php';
            if (!file_exists($vehiclePath)) {
                $vehiclePath = MODX_CORE_PATH . 'components/' . COMPONENT_NAME . '/src/Transport/EncryptedVehicle.php';
            }
            if (file_exists($vehiclePath)) {
                require_once $vehiclePath;
                $transport->xpdo->log(
                    xPDO::LOG_LEVEL_INFO,
                    '[' . COMPONENT_NAME . '] EncryptedVehicle class loaded'
                );
            } else {
                $transport->xpdo->log(
                    xPDO::LOG_LEVEL_ERROR,
                    '[' . COMPONENT_NAME . '] EncryptedVehicle class not found: ' . $vehiclePath
                );
                $success = false;
            }
            break;
        case xPDOTransport::ACTION_UNINSTALL:
            $vehiclePath = MODX_CORE_PATH . 'components/' . COMPONENT_NAME . '/Transport/EncryptedVehicle.php';
            if (!file_exists($vehiclePath)) {
                $vehiclePath = MODX_CORE_PATH . 'components/' . COMPONENT_NAME . '/src/Transport/EncryptedVehicle.php';
            }
            if (file_exists($vehiclePath)) {
                require_once $vehiclePath;
                $transport->xpdo->log(xPDO::LOG_LEVEL_INFO, '[' . COMPONENT_NAME . '] EncryptedVehicle loaded for uninstall');
            }
            break;
    }
}

return $success;
