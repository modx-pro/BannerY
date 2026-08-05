<?php

/** @var modX $modx */

if (!class_exists('MiniShop3\\Model\\msProduct')) {
    return;
}

require_once MODX_CORE_PATH . 'components/bannerpro/include/helpers.php';
bannerpro_add_package($modx);

switch ($modx->event->name) {
    case 'msOnProductPrepare':
        $product = $modx->event->params['object'] ?? null;
        if (!is_array($product)) {
            break;
        }
        $productId = (int) ($product['id'] ?? 0);
        if ($productId <= 0) {
            break;
        }
        $product['bannerpro_ads'] = bannerpro_fetch_ads_for_product($modx, $productId);
        $product['bannerpro_positions'] = bannerpro_fetch_catalog_positions_for_product($modx, $productId);
        $modx->event->params['object'] = $product;
        break;

    case 'msOnProductsLoad':
        $products = $modx->event->params['products'] ?? null;
        if (!is_array($products) || $products === []) {
            break;
        }
        $productIds = [];
        foreach ($products as $product) {
            $id = (int) ($product['id'] ?? 0);
            if ($id > 0) {
                $productIds[$id] = $id;
            }
        }
        if ($productIds === []) {
            break;
        }
        $ids = array_values($productIds);
        $adsByProduct = bannerpro_fetch_ads_for_products($modx, $ids);
        $positionsByProduct = bannerpro_fetch_catalog_positions_for_products($modx, $ids);
        foreach ($products as $index => $product) {
            $id = (int) ($product['id'] ?? 0);
            $products[$index]['bannerpro_ads'] = $adsByProduct[$id] ?? [];
            $products[$index]['bannerpro_positions'] = $positionsByProduct[$id] ?? [];
        }
        $modx->event->params['products'] = $products;
        break;

    case 'msOnSubmitOrder':
        $clickId = (int) ($_COOKIE['bannerpro_click_id'] ?? 0);
        if ($clickId <= 0) {
            break;
        }
        $order = $modx->event->params['order'] ?? null;
        if (!$order || !method_exists($order, 'get')) {
            break;
        }
        $orderId = (int) $order->get('id');
        if ($orderId <= 0) {
            break;
        }
        /** @var byClick|null $click */
        $click = $modx->getObject('byClick', ['id' => $clickId, 'order_id' => 0]);
        if ($click) {
            $click->set('order_id', $orderId);
            $click->save();
        }
        break;
}
