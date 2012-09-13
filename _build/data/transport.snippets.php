<?php
$snippets = array();
$properties = include $sources['build'].'properties/properties.bannerx.php';

$snippets[0] = $modx->newObject('modSnippet');
$snippets[0]->set('id', 0);
$snippets[0]->set('name', 'BannerX');
$snippets[0]->set('description', 'Show ads on your site');
$snippets[0]->set('snippet', file_get_contents($sources['source_core'].'/elements/snippets/bannerx.snippet.php'));
$snippets[0]->setProperties($properties[0]);

return $snippets;
