<?php

/**
 * Properties English Lexicon Entries for BannerPro
 *
 * @package bannerpro
 * @subpackage lexicon
 */

$_lang['bannerpro_prop_positions'] = 'Comma separated list of ads positions (numeric IDs).';
$_lang['bannerpro_prop_positionName'] = 'Position name or comma-separated names (e.g. sidebar). Ignored if &position is set.';
$_lang['bannerpro_prop_positionKey'] = 'Alias for &positionName (same behavior).';
$_lang['bannerpro_prop_context'] = 'MODX context for position filter. Empty or * — current context. Positions with empty context_key show everywhere.';
$_lang['bannerpro_prop_productId'] = 'MiniShop3 product ID: global ads plus ads linked to this product_id.';
$_lang['bannerpro_prop_resource'] = 'Resource ID for page targeting context. Omit to use the current MODX resource.';
$_lang['bannerpro_prop_parent'] = 'Parent resource ID for section targeting context. Omit to use the current resource parent.';
$_lang['bannerpro_prop_tags'] = 'Comma-separated tag slugs to filter ads (untagged ads are excluded when set).';
$_lang['bannerpro_prop_tagsMode'] = 'Tag filter mode: any (OR) or all (AND). Default any.';

$_lang['bannerpro_prop_limit'] = 'Max items to return.';
$_lang['bannerpro_prop_offset'] = 'Skip this many items from the start.';
$_lang['bannerpro_prop_sortby'] = 'Sort order: any byAd field, "RAND()", "idx" (order in position), "weighted" (rotation by ad-position weight), or "ab" (A/B split 50/50 by cookie when exactly two ads in slot).';
$_lang['bannerpro_prop_sortdir'] = 'Order of the results';
$_lang['bannerpro_prop_where'] = 'A JSON-style expression of criteria to build any additional where clauses from.';
$_lang['bannerpro_prop_showInactive'] = 'Show an inactive items.';

$_lang['bannerpro_prop_showLog'] = 'Snippet debug log. Managers only (mgr context).';
$_lang['bannerpro_prop_fastMode'] = 'Fast chunk mode: strip unprocessed tags.';
$_lang['bannerpro_prop_tpl'] = 'Banner chunk. Without it, raw fields print to output.';
$_lang['bannerpro_prop_tplFirst'] = 'Name of a chunk serving as item template for the first item.';
$_lang['bannerpro_prop_tplLast'] = 'Name of a chunk serving as item template for the last item.';
$_lang['bannerpro_prop_tplOdd'] = 'Name of a chunk serving as item template for items with an odd idx value (see idx property).';
$_lang['bannerpro_prop_tplImage'] = 'Chunk for image-type banners. Overrides &tpl for type=image when set.';
$_lang['bannerpro_prop_tplImageFirst'] = 'Chunk for the first image-type banner.';
$_lang['bannerpro_prop_tplImageLast'] = 'Chunk for the last image-type banner.';
$_lang['bannerpro_prop_tplImageOdd'] = 'Chunk for image-type banners with odd idx.';
$_lang['bannerpro_prop_tplHtml'] = 'Chunk for HTML-type banners. Defaults to byHtml when not set.';
$_lang['bannerpro_prop_tplHtmlFirst'] = 'Chunk for the first HTML-type banner.';
$_lang['bannerpro_prop_tplHtmlLast'] = 'Chunk for the last HTML-type banner.';
$_lang['bannerpro_prop_tplHtmlOdd'] = 'Chunk for HTML-type banners with odd idx.';
$_lang['bannerpro_prop_tplWrapper'] = 'Name of a chunk serving as a wrapper template for the output. This does not work with toSeparatePlaceholders.';
$_lang['bannerpro_prop_wrapIfEmpty'] = 'If true, will output the wrapper specified in &tplWrapper even if the output is empty.';

$_lang['bannerpro_prop_toPlaceholder'] = 'If set, will assign the result to this placeholder instead of outputting it directly.';
$_lang['bannerpro_prop_toSeparatePlaceholders'] = 'Prefix for per-item placeholders: myPl → [[+myPl0]], [[+myPl1]], …';
$_lang['bannerpro_prop_outputSeparator'] = 'An optional string to separate each tpl instance.';

$_lang['bannerpro_prop_cache'] = 'Enable (1) or disable (0) output cache for this call. Empty = use system setting bannerpro_cache.';
$_lang['bannerpro_prop_cacheLifetime'] = 'Cache TTL in seconds for this call. Empty = bannerpro_cache_lifetime.';
