<?php
/**
 * @package bannerx
 * @subpackage controllers
 */
require_once dirname(dirname(__FILE__)) . '/model/bannerx/bannerx.class.php';
$bannerx = new BannerX($modx);

return $bannerx->initialize('mgr');