<?php

use MODX\Revolution\modX;
use MODX\Revolution\modCategory;
use MODX\Revolution\Transport\modPackageBuilder;
use MODX\Revolution\Transport\modTransportPackage;
use MODX\Revolution\modSystemSetting;
use MODX\Revolution\modMenu;
use MODX\Revolution\modSnippet;
use MODX\Revolution\modPlugin;
use MODX\Revolution\modPluginEvent;
use MODX\Revolution\modChunk;
use xPDO\Transport\xPDOTransport;

class BannerProPackage
{
    private $modx;
    private $config = [];
    private $category;
    private $category_attributes = [];

    public $builder;

    public function __construct(modX $modx, array $config = [])
    {
        $this->modx = $modx;
        $this->modx->initialize('mgr');

        $root = dirname(__FILE__, 2) . '/';
        $core = $root . 'core/components/' . $config['name_lower'] . '/';
        $assets = $root . 'assets/components/' . $config['name_lower'] . '/';

        $this->config = array_merge([
            'log_level' => modX::LOG_LEVEL_INFO,
            'log_target' => XPDO_CLI_MODE ? 'ECHO' : 'HTML',
            'root' => $root,
            'build' => $root . '_build/',
            'elements' => $root . '_build/elements/',
            'resolvers' => $root . '_build/resolvers/',
            'source' => $core . 'elements/',
            'core' => $core,
            'assets' => $assets,
            'properties_lexicon' => $config['name_lower'] . ':properties',
        ], $config);
        $this->modx->setLogLevel($this->config['log_level']);
        $this->modx->setLogTarget($this->config['log_target']);

        $this->initialize();
    }

    public function process()
    {
        $this->assets();
        $this->encryptionSetup();

        $elements = scandir($this->config['elements']);
        foreach ($elements as $element) {
            if (in_array($element[0], ['_', '.'], true)) {
                continue;
            }
            $name = preg_replace('#\.php$#', '', $element);
            if (method_exists($this, $name)) {
                $this->{$name}();
            }
        }

        $vehicle = $this->builder->createVehicle($this->category, $this->category_attributes);

        $vehicle->resolve('file', [
            'source' => $this->config['core'],
            'target' => "return MODX_CORE_PATH . 'components/';",
        ]);
        $vehicle->resolve('file', [
            'source' => $this->config['assets'],
            'target' => "return MODX_ASSETS_PATH . 'components/';",
        ]);

        $resolvers = array_filter(
            scandir($this->config['resolvers']),
            static fn ($r) => !in_array($r[0], ['_', '.'], true) && substr($r, -4) === '.php'
        );
        sort($resolvers);
        foreach ($resolvers as $resolver) {
            if ($resolver === 'resolve.encryption.php') {
                continue;
            }
            if ($vehicle->resolve('php', ['source' => $this->config['resolvers'] . $resolver])) {
                $this->modx->log(modX::LOG_LEVEL_INFO, 'Added resolver ' . preg_replace('#\.php$#', '', $resolver));
            }
        }

        $this->builder->putVehicle($vehicle);

        $this->builder->setPackageAttributes([
            'changelog' => $this->readDocFile('changelog.txt'),
            'license' => $this->readDocFile('license.txt'),
            'readme' => $this->readDocFile('readme.txt'),
            'requires' => [
                'php' => '>=8.2.0',
                'modx' => '>=3.0.0',
                'pdoTools' => '>=2.1.0',
            ],
        ]);
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Added package attributes.');

        $this->modx->log(modX::LOG_LEVEL_INFO, 'Packing up transport package zip...');
        $this->builder->pack();

        if (!empty($this->config['install'])) {
            $this->install();
        }

        return $this->builder;
    }

    private function initialize()
    {
        $this->builder = new modPackageBuilder($this->modx);
        $this->builder->createPackage($this->config['name_lower'], $this->config['version'], $this->config['release']);
        $this->builder->registerNamespace($this->config['name_lower'], false, true, '{core_path}components/' . $this->config['name_lower'] . '/');
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Created Transport Package and Namespace.');

        $this->category = $this->modx->newObject(modCategory::class);
        $this->category->set('category', $this->config['name']);
        $this->category_attributes = [
            xPDOTransport::UNIQUE_KEY => 'category',
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::RELATED_OBJECTS => true,
            xPDOTransport::RELATED_OBJECT_ATTRIBUTES => [],
        ];
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Created main Category.');
    }

    /**
     * Шифрование категории (сниппеты, чанки и т.д.): EncryptedVehicle + resolve.encryption.php.
     * Ключ при сборке — API modstore.pro (`encrypt` => true и провайдер в менеджере).
     */
    private function encryptionSetup(): void
    {
        if (empty($this->config['encrypt'])) {
            return;
        }

        $this->modx->log(modX::LOG_LEVEL_INFO, 'Encryption enabled.');

        require_once $this->config['core'] . 'src/Transport/EncryptedVehicle.php';

        $this->builder->package->put(
            [
                'source' => $this->config['core'] . 'src/Transport/EncryptedVehicle.php',
                'target' => "return MODX_CORE_PATH . 'components/" . $this->config['name_lower'] . "/Transport/';",
            ],
            [
                'vehicle_class' => \xPDO\Transport\xPDOFileVehicle::class,
                xPDOTransport::UNINSTALL_FILES => false,
            ]
        );
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Added EncryptedVehicle class to package');

        $this->builder->package->put(
            [
                'source' => $this->config['core'] . 'src/Transport/encryptedvehicle.class.php',
                'target' => "return MODX_CORE_PATH . 'model/" . $this->config['name_lower'] . "/transport/';",
            ],
            [
                'vehicle_class' => \xPDO\Transport\xPDOFileVehicle::class,
                xPDOTransport::UNINSTALL_FILES => false,
            ]
        );
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Added EncryptedVehicle compatibility class to core/model');

        $this->builder->putVehicle($this->builder->createVehicle(
            ['source' => $this->config['resolvers'] . 'resolve.encryption.php'],
            ['vehicle_class' => \xPDO\Transport\xPDOScriptVehicle::class]
        ));
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Added encryption resolver');

        $this->category_attributes['vehicle_class'] = 'BannerPro\\Transport\\EncryptedVehicle';
        $this->category_attributes[xPDOTransport::ABORT_INSTALL_ON_VEHICLE_FAIL] = true;
    }

    protected function assets()
    {
        $root = $this->config['root'];
        $corePath = $this->config['core'];

        if (file_exists($corePath . 'composer.json')) {
            $this->modx->log(modX::LOG_LEVEL_INFO, 'Running composer install --no-dev in component...');
            $output = shell_exec('cd ' . escapeshellarg($corePath) . ' && composer install --no-dev --optimize-autoloader 2>&1');
            if ($output) {
                $this->modx->log(modX::LOG_LEVEL_INFO, trim($output));
            }
        }

        if (file_exists($root . 'package.json') && file_exists($root . 'vite.mgr.config.js')) {
            $this->modx->log(modX::LOG_LEVEL_INFO, 'Running npm run build...');
            $output = shell_exec('cd ' . escapeshellarg($root) . ' && npm run build 2>&1');
            if ($output) {
                $this->modx->log(modX::LOG_LEVEL_INFO, trim($output));
            }
        }
    }

    private function install()
    {
        $signature = $this->builder->getSignature();
        $sig = explode('-', $signature);
        $versionSignature = explode('.', $sig[1] ?? '1.0.0');

        /** @var modTransportPackage $package */
        $package = $this->modx->getObject(modTransportPackage::class, ['signature' => $signature]);
        if (!$package) {
            $package = $this->modx->newObject(modTransportPackage::class);
            $package->set('signature', $signature);
            $package->fromArray([
                'created' => date('Y-m-d H:i:s'),
                'updated' => null,
                'state' => 1,
                'workspace' => 1,
                'provider' => 0,
                'source' => $signature . '.transport.zip',
                'package_name' => $this->config['name'],
                'version_major' => $versionSignature[0] ?? 1,
                'version_minor' => $versionSignature[1] ?? 0,
                'version_patch' => $versionSignature[2] ?? 0,
            ], '', true, true);
            if (!empty($sig[2])) {
                $r = preg_split('#([0-9]+)#', $sig[2], -1, PREG_SPLIT_DELIM_CAPTURE);
                if (is_array($r) && !empty($r)) {
                    $package->set('release', $r[0]);
                    $package->set('release_index', $r[1] ?? '0');
                } else {
                    $package->set('release', $sig[2]);
                }
            }
            $package->save();
        }
        if (isset($package->xpdo->packages['Revolution'])) {
            $package->xpdo->packages['MODX\Revolution\\'] = $package->xpdo->packages['Revolution'];
        }
        if ($package->install()) {
            $this->modx->runProcessor('System/ClearCache');
        }
    }

    private function settings()
    {
        /** @noinspection PhpIncludeInspection */
        $settings = include $this->config['elements'] . 'settings.php';
        if (!is_array($settings)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not package System Settings');
            return;
        }
        $preserveOnUpgrade = [];
        $attributes = [
            xPDOTransport::UNIQUE_KEY => 'key',
            xPDOTransport::PRESERVE_KEYS => true,
            xPDOTransport::UPDATE_OBJECT => !empty($this->config['update']['settings']),
            xPDOTransport::RELATED_OBJECTS => false,
        ];
        $count = 0;
        foreach ($settings as $name => $data) {
            if (in_array($name, $preserveOnUpgrade, true)) {
                continue;
            }
            /** @var modSystemSetting $setting */
            $setting = $this->modx->newObject(modSystemSetting::class);
            $setting->fromArray(array_merge([
                'key' => $this->config['name_lower'] . '_' . $name,
                'namespace' => $this->config['name_lower'],
            ], $data), '', true, true);
            $vehicle = $this->builder->createVehicle($setting, $attributes);
            $this->builder->putVehicle($vehicle);
            $count++;
        }
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . $count . ' System Settings (preserve on upgrade: ' . implode(', ', $preserveOnUpgrade) . ')');
    }

    private function menus()
    {
        /** @noinspection PhpIncludeInspection */
        $menus = include $this->config['elements'] . 'menus.php';
        if (!is_array($menus)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not package Menus');
            return;
        }
        $attributes = [
            xPDOTransport::PRESERVE_KEYS => true,
            xPDOTransport::UPDATE_OBJECT => !empty($this->config['update']['menus']),
            xPDOTransport::UNIQUE_KEY => 'text',
            xPDOTransport::RELATED_OBJECTS => true,
        ];
        foreach ($menus as $name => $data) {
            /** @var modMenu $menu */
            $menu = $this->modx->newObject(modMenu::class);
            $menu->fromArray(array_merge([
                'text' => $name,
                'parent' => 'components',
                'namespace' => $this->config['name_lower'],
                'icon' => '',
                'menuindex' => 0,
                'params' => '',
                'handler' => '',
            ], $data), '', true, true);
            $vehicle = $this->builder->createVehicle($menu, $attributes);
            $this->builder->putVehicle($vehicle);
        }
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($menus) . ' Menus');
    }

    private function snippets()
    {
        /** @noinspection PhpIncludeInspection */
        $snippets = include $this->config['elements'] . 'snippets.php';
        if (!is_array($snippets)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not package Snippets');
            return;
        }
        $this->category_attributes[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['Snippets'] = [
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => !empty($this->config['update']['snippets']),
            xPDOTransport::UNIQUE_KEY => 'name',
        ];
        $lex = $this->config['properties_lexicon'];
        $objects = [];
        foreach ($snippets as $name => $data) {
            /** @var modSnippet $snippet */
            $objects[$name] = $this->modx->newObject(modSnippet::class);
            $objects[$name]->fromArray(array_merge([
                'id' => 0,
                'name' => $name,
                'description' => @$data['description'],
                'snippet' => $this->getFileContent($this->config['source'] . 'snippets/' . $data['file'] . '.php'),
                'static' => !empty($this->config['static']['snippets']),
                'source' => 1,
                'static_file' => 'core/components/' . $this->config['name_lower'] . '/elements/snippets/' . $data['file'] . '.php',
            ], $data), '', true, true);
            $properties = [];
            foreach (@$data['properties'] ?: [] as $k => $v) {
                $properties[] = array_merge([
                    'name' => $k,
                    'desc' => $this->config['name_lower'] . '_prop_' . $k,
                    'lexicon' => $lex,
                ], is_array($v) ? $v : ['type' => 'textfield', 'value' => $v]);
            }
            $objects[$name]->setProperties($properties);
        }
        $this->category->addMany($objects);
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($objects) . ' Snippets');
    }

    private function chunks()
    {
        /** @noinspection PhpIncludeInspection */
        $chunks = include $this->config['elements'] . 'chunks.php';
        if (!is_array($chunks)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not package Chunks');
            return;
        }
        $this->category_attributes[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['Chunks'] = [
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => !empty($this->config['update']['chunks']),
            xPDOTransport::UNIQUE_KEY => 'name',
        ];
        $objects = [];
        foreach ($chunks as $name => $data) {
            /** @var modChunk $chunk */
            $objects[$name] = $this->modx->newObject(modChunk::class);
            $objects[$name]->fromArray(array_merge([
                'id' => 0,
                'name' => $name,
                'description' => @$data['description'],
                'snippet' => $this->getFileContent($this->config['source'] . 'chunks/' . $data['file'] . '.tpl'),
                'static' => !empty($this->config['static']['chunks']),
                'source' => 1,
                'static_file' => 'core/components/' . $this->config['name_lower'] . '/elements/chunks/' . $data['file'] . '.tpl',
            ], $data), '', true, true);
        }
        $this->category->addMany($objects);
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($objects) . ' Chunks');
    }

    private function plugins()
    {
        /** @noinspection PhpIncludeInspection */
        $plugins = include $this->config['elements'] . 'plugins.php';
        if (!is_array($plugins)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not package Plugins');
            return;
        }
        $this->category_attributes[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['Plugins'] = [
            xPDOTransport::UNIQUE_KEY => 'name',
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => !empty($this->config['update']['plugins']),
            xPDOTransport::RELATED_OBJECTS => true,
            xPDOTransport::RELATED_OBJECT_ATTRIBUTES => [
                'PluginEvents' => [
                    xPDOTransport::PRESERVE_KEYS => true,
                    xPDOTransport::UPDATE_OBJECT => true,
                    xPDOTransport::UNIQUE_KEY => ['pluginid', 'event'],
                ],
            ],
        ];
        $objects = [];
        foreach ($plugins as $name => $data) {
            $pluginData = $data;
            unset($pluginData['events'], $pluginData['file']);
            /** @var modPlugin $plugin */
            $plugin = $this->modx->newObject(modPlugin::class);
            $plugin->fromArray(array_merge([
                'name' => $name,
                'category' => 0,
                'description' => @$data['description'],
                'plugincode' => $this->getFileContent($this->config['source'] . 'plugins/' . $data['file'] . '.php'),
                'static' => !empty($this->config['static']['plugins']),
                'source' => 1,
                'static_file' => 'core/components/' . $this->config['name_lower'] . '/elements/plugins/' . $data['file'] . '.php',
            ], $pluginData), '', true, true);

            $events = [];
            if (!empty($data['events'])) {
                foreach ($data['events'] as $eventName => $eventData) {
                    /** @var modPluginEvent $event */
                    $event = $this->modx->newObject(modPluginEvent::class);
                    $event->fromArray(array_merge([
                        'event' => $eventName,
                        'priority' => 0,
                        'propertyset' => 0,
                    ], is_array($eventData) ? $eventData : []), '', true, true);
                    $events[] = $event;
                }
            }
            if (!empty($events)) {
                $plugin->addMany($events);
            }
            $objects[] = $plugin;
        }
        $this->category->addMany($objects);
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($objects) . ' Plugins');
    }

    private function getFileContent($filename)
    {
        if (file_exists($filename)) {
            $file = trim(file_get_contents($filename));
            return preg_match('#\<\?php(.*)#is', $file, $data)
                ? rtrim(rtrim(trim(@$data[1]), '?>'))
                : $file;
        }
        return '';
    }

    private function readDocFile($filename)
    {
        $filepath = $this->config['core'] . 'docs/' . $filename;
        if (!file_exists($filepath)) {
            if ($filename === 'readme.txt') {
                $filepath = $this->config['root'] . 'README.md';
            }
            if (!file_exists($filepath)) {
                return '';
            }
        }
        $content = file_get_contents($filepath);
        return $content !== false ? $content : '';
    }
}

if (php_sapi_name() === 'cli') {
    if (!isset($_SESSION)) {
        $_SESSION = [];
    }
    $buildErrorReporting = error_reporting(E_ALL & ~E_DEPRECATED);
}
if (!file_exists(dirname(__FILE__) . '/config.inc.php')) {
    exit('Could not load config. Please specify MODX_CORE_PATH in config or place the component in Extras/ of your MODX site.');
}
$config = require dirname(__FILE__) . '/config.inc.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
$modx = new modX();
$install = new BannerProPackage($modx, $config);
$builder = $install->process();

if (php_sapi_name() === 'cli' && isset($buildErrorReporting)) {
    error_reporting($buildErrorReporting);
}

if (!empty($config['download'])) {
    $name = $builder->getSignature() . '.transport.zip';
    $path = $modx->getOption('core_path', null, MODX_CORE_PATH) . 'packages/';
    if ($content = @file_get_contents($path . $name)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $name);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($content));
        exit($content);
    }
}
