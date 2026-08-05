<?php

/**
 * Properties Russian Lexicon Entries for BannerPro
 *
 * @package bannerpro
 * @subpackage lexicon
 */

$_lang['bannerpro_prop_positions'] = 'Номера рекламных позиций для вывода баннеров, через запятую.';
$_lang['bannerpro_prop_positionName'] = 'Имя позиции или несколько через запятую (например sidebar). Игнорируется, если задан &position.';
$_lang['bannerpro_prop_positionKey'] = 'Синоним &positionName (то же поведение).';
$_lang['bannerpro_prop_context'] = 'Контекст MODX для фильтра позиций. Пусто или * — текущий контекст. Позиции с пустым context_key видны везде.';
$_lang['bannerpro_prop_productId'] = 'ID товара MiniShop3: глобальные баннеры и баннеры с тем же product_id в БД.';
$_lang['bannerpro_prop_resource'] = 'ID ресурса для таргетинга по странице. Без параметра — текущий ресурс MODX.';
$_lang['bannerpro_prop_parent'] = 'ID родителя для таргетинга по разделу. Без параметра — parent текущего ресурса.';
$_lang['bannerpro_prop_tags'] = 'Метки через запятую (баннеры без меток не попадают в выборку).';
$_lang['bannerpro_prop_tagsMode'] = 'Режим фильтра меток: any (ИЛИ) или all (И). По умолчанию any.';

$_lang['bannerpro_prop_limit'] = 'Ограничение выборки.';
$_lang['bannerpro_prop_offset'] = 'Пропуск результатов от начала.';
$_lang['bannerpro_prop_sortby'] = 'Сортировка: поле byAd, "RAND()", "idx" (порядок в позиции), "weighted" (взвешенная ротация по weight) или "ab" (A/B 50/50 по cookie при ровно двух баннерах в слоте).';
$_lang['bannerpro_prop_sortdir'] = 'Направление сортировки.';
$_lang['bannerpro_prop_where'] = 'Массив дополнительных параметров выборки, закодированный в JSON.';
$_lang['bannerpro_prop_showInactive'] = 'Показывать отключенные позиции.';

$_lang['bannerpro_prop_showLog'] = 'Лог работы сниппета. Только в контексте mgr.';
$_lang['bannerpro_prop_fastMode'] = 'Быстрый режим чанков: необработанные теги вырезаются.';
$_lang['bannerpro_prop_tpl'] = 'Чанк баннера. Без чанка — сырые поля на экран.';
$_lang['bannerpro_prop_tplFirst'] = 'Имя чанка для первого баннера в результатах.';
$_lang['bannerpro_prop_tplLast'] = 'Имя чанка для последнего баннера в результатах.';
$_lang['bannerpro_prop_tplOdd'] = 'Имя чанка для каждого второго баннера.';
$_lang['bannerpro_prop_tplImage'] = 'Чанк для баннеров типа image. Перекрывает &tpl для type=image.';
$_lang['bannerpro_prop_tplImageFirst'] = 'Чанк для первого баннера типа image.';
$_lang['bannerpro_prop_tplImageLast'] = 'Чанк для последнего баннера типа image.';
$_lang['bannerpro_prop_tplImageOdd'] = 'Чанк для баннеров типа image с нечётным idx.';
$_lang['bannerpro_prop_tplHtml'] = 'Чанк для баннеров типа html. По умолчанию byHtml.';
$_lang['bannerpro_prop_tplHtmlFirst'] = 'Чанк для первого баннера типа html.';
$_lang['bannerpro_prop_tplHtmlLast'] = 'Чанк для последнего баннера типа html.';
$_lang['bannerpro_prop_tplHtmlOdd'] = 'Чанк для баннеров типа html с нечётным idx.';
$_lang['bannerpro_prop_tplWrapper'] = 'Обёртка всех результатов. Плейсхолдер [[+output]]. Несовместимо с toSeparatePlaceholders.';
$_lang['bannerpro_prop_wrapIfEmpty'] = 'Выводить tplWrapper при пустом результате.';

$_lang['bannerpro_prop_toPlaceholder'] = 'Сохранить вывод в плейсхолдер вместо печати на экран.';
$_lang['bannerpro_prop_toSeparatePlaceholders'] = 'Префикс плейсхолдеров: myPl → [[+myPl0]], [[+myPl1]], …';
$_lang['bannerpro_prop_outputSeparator'] = 'Разделитель между баннерами в выводе.';

$_lang['bannerpro_prop_cache'] = 'Включить (1) или выключить (0) кэш для этого вызова. Пусто — настройка bannerpro_cache.';
$_lang['bannerpro_prop_cacheLifetime'] = 'TTL кэша в секундах для этого вызова. Пусто — bannerpro_cache_lifetime.';
