<?php

defined('MODX_CORE_PATH') || exit;

function bannerpro_request_is_bot(modX $modx): bool
{
    if (!(bool) (int) $modx->getOption('bannerpro_filter_bots', null, 0)) {
        return false;
    }

    static $warned = false;

    if ($modx->getObject('modSnippet', ['name' => 'isCrawler'])) {
        return (bool) $modx->runSnippet('isCrawler');
    }

    $crawlerPath = (string) $modx->getOption('crawlerdetect.core_path', null, '');
    if ($crawlerPath !== '') {
        $autoload = rtrim($crawlerPath, '/') . '/vendor/autoload.php';
        if (is_file($autoload)) {
            require_once $autoload;
            if (class_exists('Jaybizzle\\CrawlerDetect\\CrawlerDetect')) {
                $detector = new Jaybizzle\CrawlerDetect\CrawlerDetect(
                    null,
                    $_SERVER['HTTP_USER_AGENT'] ?? null
                );

                return $detector->isCrawler();
            }
        }
    }

    if (!$warned) {
        $modx->log(
            modX::LOG_LEVEL_WARN,
            '[BannerPro] bannerpro_filter_bots is enabled but CrawlerDetect is not available; bot filtering skipped.'
        );
        $warned = true;
    }

    return false;
}
