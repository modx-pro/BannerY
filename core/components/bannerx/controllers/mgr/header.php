<?php
/**
 * Loads the header for mgr pages.
 *
 * @package bannerx
 * @subpackage controllers
 */
$modx->regClientStartupScript($bannerx->config['jsUrl'].'mgr/bannerx.js');
$modx->regClientStartupHTMLBlock('<script type="text/javascript">
Ext.onReady(function() {
    Bannerx.config = '.$modx->toJSON($bannerx->config).';
});
</script>');

return '';