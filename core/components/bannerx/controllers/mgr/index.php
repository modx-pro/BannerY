<?php
/**
 * Loads the home page.
 *
 * @package bannerx
 * @subpackage controllers
 */

$modx->regClientStartupScript($bannerx->config['jsUrl'].'mgr/widgets/ads.grid.js');
$modx->regClientStartupScript($bannerx->config['jsUrl'].'mgr/widgets/positions.grid.js');
$modx->regClientStartupScript($bannerx->config['jsUrl'].'mgr/widgets/referrers.grid.js');
$modx->regClientStartupScript($bannerx->config['jsUrl'].'mgr/widgets/stats.panel.js');
$modx->regClientStartupScript($bannerx->config['jsUrl'].'mgr/widgets/home.panel.js');
$modx->regClientStartupScript($bannerx->config['jsUrl'].'mgr/sections/index.js');

$output = '<div id="bannerx-panel-home-div"></div>';

return $output;