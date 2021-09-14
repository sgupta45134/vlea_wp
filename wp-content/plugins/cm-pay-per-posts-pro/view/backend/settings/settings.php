<?php

use com\cminds\payperposts\controller\SettingsController;
use com\cminds\payperposts\view\SettingsView;
use com\cminds\payperposts\App;
use com\cminds\payperposts\model\Settings;


if ( ! empty( $_GET['status'] ) and ! empty( $_GET['msg'] ) ) {
	printf( '<div id="message" class="%s"><p>%s</p></div>', ( $_GET['status'] == 'ok' ? 'updated' : 'error' ), esc_html( $_GET['msg'] ) );
}
?>

<form method="post" id="settings">
	<?php add_thickbox(); ?>
  <div id="cmppp-price-group-modal" style="display:none;">
    <p>
      Loading...
    </p>
  </div>

  <ul class="cmppp-settings-tabs"><?php

	  $tabs = apply_filters( 'cmppp_settings_pages', Settings::$categories );
	  foreach ( $tabs as $tabId => $tabLabel ) {
		  printf( '<li><a href="#tab-%s">%s</a></li>', $tabId, $tabLabel );
	  }

	  ?></ul>

  <div class="inner"><?php

	  $settingsView = new SettingsView();
	  echo $settingsView->render();

	  ?></div>

  <p class="form-finalize">

    <span class="required-text"><span>*</span> - Required fields</span>
    <br>
    <br>

	  <?php /* <a href="<?php echo esc_attr($clearCacheUrl); ?>" class="right button">Clear cache</a> */ ?>
    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce( SettingsController::getMenuSlug() ); ?>"/>
    <input type="submit" value="Save" class="button button-primary"/>
  </p>

</form>
