<?php

use com\cminds\payperposts\App;

use com\cminds\payperposts\controller\SettingsController;

use com\cminds\payperposts\model\Labels;

?>
<p>
	<?php printf(Labels::__('This plugin was developed by %s'), '<a href="http://plugins.cminds.com/" target="new">CreativeMinds</a>'); ?>.
	<?php printf(Labels::__('You can find more information about %s on website: %s'),
			App::getPluginName(),
			sprintf('<a href="%s" target="new">%s</a>', App::PLUGIN_WEBSITE, App::PLUGIN_WEBSITE)); ?>
</p>

<?php /*
<br /><br />
<h3><strong>Video Demo</strong></h3>
<iframe width="560" height="315" src="//www.youtube.com/embed/px1IOEKOcr4" frameborder="0" allowfullscreen></iframe>
*/

?>

<h3><?php echo Labels::__('Premium Plugins by CreativeMinds'); ?></h3>
<div><iframe src="<?php echo esc_attr($iframeURL) ?>" height="700" style="width: 100%" ></iframe></div>
