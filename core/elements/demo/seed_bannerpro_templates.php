<?php
/**
 * Preset-шаблоны баннеров для менеджера BannerPro («Создать из шаблона»).
 * Запуск из корня MODX: php core/elements/demo/seed_bannerpro_templates.php
 */

$root = __DIR__;
while ($root !== '/' && !is_file($root . '/config.core.php')) {
    $root = dirname($root);
}
if (!is_file($root . '/config.core.php')) {
    fwrite(STDERR, "config.core.php not found.\n");
    exit(1);
}

require_once $root . '/config.core.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

$modx = new MODX\Revolution\modX();
$modx->initialize('mgr');
require_once MODX_CORE_PATH . 'components/bannerpro/include/helpers.php';
require_once MODX_CORE_PATH . 'components/bannerpro/include/ad_template_presets.php';
bannerpro_add_package($modx);

$qaResourceId = 0;
$qaParentId = 0;
foreach (['bannerpro-test', 'bannerpro-test.html'] as $alias) {
    /** @var modResource|null $res */
    if ($res = $modx->getObject('modResource', ['alias' => $alias])) {
        $qaResourceId = (int) $res->get('id');
        $qaParentId = max(0, (int) $res->get('parent'));
        break;
    }
}

$categoryId = 0;
/** @var modResource|null $productResource */
if ($productResource = $modx->getObject('modResource', 25)) {
    $categoryId = max(0, (int) $productResource->get('parent'));
}

$created = bannerpro_seed_ad_templates($modx, [
    'qa_resource_id' => $qaResourceId,
    'qa_parent_id' => $qaParentId,
    'category_id' => $categoryId,
    'now' => new DateTimeImmutable(),
]);

$total = (int) $modx->getCount('byAdTemplate');

echo "BannerPro ad templates seed OK\n";
echo "Created: {$created}, total in DB: {$total}\n\n";

/** @var byAdTemplate[] $templates */
$templates = $modx->getCollection('byAdTemplate', null, false);
usort($templates, static fn (byAdTemplate $a, byAdTemplate $b) => strcmp($a->get('name'), $b->get('name')));
foreach ($templates as $template) {
    echo sprintf(
        "  [%d] %s (%s) tpl=%s\n",
        $template->get('id'),
        $template->get('name'),
        $template->get('type'),
        $template->get('tpl')
    );
}
