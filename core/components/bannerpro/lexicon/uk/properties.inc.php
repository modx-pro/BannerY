<?php

/**
 * Properties Ukrainian Lexicon Entries for BannerPro
 *
 * @package bannerpro
 * @subpackage lexicon
 */

$_lang['bannerpro_prop_positions'] = 'Номери рекламних позицій для виводу банерів, через кому.';
$_lang['bannerpro_prop_positionName'] = 'Ім\'я позиції або кілька через кому (наприклад sidebar). Ігнорується, якщо задано &position.';
$_lang['bannerpro_prop_positionKey'] = 'Синонім &positionName (та сама поведінка).';
$_lang['bannerpro_prop_context'] = 'Контекст MODX для фільтра позицій. Порожньо або * — поточний контекст. Позиції з порожнім context_key видно скрізь.';
$_lang['bannerpro_prop_productId'] = 'ID товару MiniShop3: глобальні банери та банери з тим самим product_id у БД.';
$_lang['bannerpro_prop_resource'] = 'ID ресурсу для таргетингу за сторінкою. Без параметра — поточний ресурс MODX.';
$_lang['bannerpro_prop_parent'] = 'ID батька для таргетингу за розділом. Без параметра — parent поточного ресурсу.';
$_lang['bannerpro_prop_tags'] = 'Мітки через кому (банери без міток не потрапляють у вибірку).';
$_lang['bannerpro_prop_tagsMode'] = 'Режим фільтра міток: any (АБО) або all (І). За замовчуванням any.';

$_lang['bannerpro_prop_limit'] = 'Обмеження вибірки.';
$_lang['bannerpro_prop_offset'] = 'Пропуск результатів від початку.';
$_lang['bannerpro_prop_sortby'] = 'Сортування: поле byAd, «RAND()», «idx» (порядок у позиції), «weighted» (зважена ротація за weight) або «ab» (A/B 50/50 по cookie при рівно двох банерах у слоті).';
$_lang['bannerpro_prop_sortdir'] = 'Напрямок сортування.';
$_lang['bannerpro_prop_where'] = 'Масив додаткових параметрів вибірки в JSON.';
$_lang['bannerpro_prop_showInactive'] = 'Показувати вимкнені банери.';

$_lang['bannerpro_prop_showLog'] = 'Показувати додаткову інформацію про роботу сніпета. Лише для авторизованих у контексті «mgr».';
$_lang['bannerpro_prop_fastMode'] = 'Швидкий режим обробки чанків. Усі необроблені теги будуть вирізані.';
$_lang['bannerpro_prop_tpl'] = 'Ім\'я чанка для оформлення банера. Якщо не вказано — поля банера виводяться на екран.';
$_lang['bannerpro_prop_tplFirst'] = 'Чанк для першого банера у результатах.';
$_lang['bannerpro_prop_tplLast'] = 'Чанк для останнього банера у результатах.';
$_lang['bannerpro_prop_tplOdd'] = 'Чанк для кожного другого банера.';
$_lang['bannerpro_prop_tplImage'] = 'Чанк для банерів типу image. Перекриває &tpl для type=image.';
$_lang['bannerpro_prop_tplImageFirst'] = 'Чанк для першого банера типу image.';
$_lang['bannerpro_prop_tplImageLast'] = 'Чанк для останнього банера типу image.';
$_lang['bannerpro_prop_tplImageOdd'] = 'Чанк для банерів типу image з непарним idx.';
$_lang['bannerpro_prop_tplHtml'] = 'Чанк для банерів типу html. За замовчуванням byHtml.';
$_lang['bannerpro_prop_tplHtmlFirst'] = 'Чанк для першого банера типу html.';
$_lang['bannerpro_prop_tplHtmlLast'] = 'Чанк для останнього банера типу html.';
$_lang['bannerpro_prop_tplHtmlOdd'] = 'Чанк для банерів типу html з непарним idx.';
$_lang['bannerpro_prop_tplWrapper'] = 'Чанк-обгортка для всіх результатів. Плейсхолдер [[+output]]. Не працює з toSeparatePlaceholders.';
$_lang['bannerpro_prop_wrapIfEmpty'] = 'Виводити обгортку (tplWrapper) навіть якщо результатів немає.';

$_lang['bannerpro_prop_toPlaceholder'] = 'Якщо не порожньо — результат зберігається в плейсхолдер замість виводу на екран.';
$_lang['bannerpro_prop_toSeparatePlaceholders'] = 'Кожен результат у окремий плейсхолдер: ім\'я параметра + порядковий номер (з 0), наприклад myPl0, myPl1.';
$_lang['bannerpro_prop_outputSeparator'] = 'Необов\'язковий рядок-роздільник між результатами.';

$_lang['bannerpro_prop_cache'] = 'Увімкнути (1) або вимкнути (0) кеш для цього виклику. Порожньо — налаштування bannerpro_cache.';
$_lang['bannerpro_prop_cacheLifetime'] = 'TTL кешу в секундах для цього виклику. Порожньо — bannerpro_cache_lifetime.';
