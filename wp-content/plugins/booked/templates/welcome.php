<div id="booked-welcome-screen">
	<div class="wrap about-wrap">
		<div id="welcome-panel" class="welcome-panel">

			<img src="<?php echo BOOKED_PLUGIN_URL; ?>/templates/images/welcome-banner.png" class="booked-welcome-banner">

			<div class="welcome-panel-intro">
				<h1><?php echo sprintf(esc_html__('Thanks for choosing %s.','booked'),'Booked'); ?></h1>
                <p><?php echo sprintf(esc_html__('If this is your first time using %s, you will find some helpful "Getting Started" links below. If you just updated the plugin, you can find out what\'s new in the "What\'s New" section below.','booked'),'Booked'); ?></p>
            </div>

			<div class="welcome-panel-content">
				<div class="welcome-panel-column-container">
					<div class="welcome-panel-column">
						<h3><?php esc_html_e('Getting Started','booked'); ?></h3>
						<ul>
							<li><a class="welcome-icon welcome-learn-more" href="https://boxystudio.ticksy.com/article/3239/" target="_blank"><?php esc_html_e('Installation & Setup Guide','booked'); ?>&nbsp;&nbsp;<i class="booked-icon booked-icon-sign-out"></i></a></li>
							<li><a class="welcome-icon welcome-learn-more" href="https://boxystudio.ticksy.com/article/6268/" target="_blank"><?php esc_html_e('Custom Calendars','booked'); ?>&nbsp;&nbsp;<i class="booked-icon booked-icon-sign-out"></i></a></li>
							<li><a class="welcome-icon welcome-learn-more" href="https://boxystudio.ticksy.com/article/3238/" target="_blank"><?php esc_html_e('Default Time Slots','booked'); ?>&nbsp;&nbsp;<i class="booked-icon booked-icon-sign-out"></i></a></li>
							<li><a class="welcome-icon welcome-learn-more" href="https://boxystudio.ticksy.com/article/3233/" target="_blank"><?php esc_html_e('Custom Time Slots','booked'); ?>&nbsp;&nbsp;<i class="booked-icon booked-icon-sign-out"></i></a></li>
							<li><a class="welcome-icon welcome-learn-more" href="https://boxystudio.ticksy.com/article/6267/" target="_blank"><?php esc_html_e('Custom Fields','booked'); ?>&nbsp;&nbsp;<i class="booked-icon booked-icon-sign-out"></i></a></li>
							<li><a class="welcome-icon welcome-learn-more" href="https://boxystudio.ticksy.com/article/3240/" target="_blank"><?php esc_html_e('Shortcodes','booked'); ?>&nbsp;&nbsp;<i class="booked-icon booked-icon-sign-out"></i></a></li>
						</ul>
						<a class="button" style="margin-bottom:15px; margin-top:0;" href="https://boxystudio.ticksy.com/articles/7827/" target="_blank"><?php esc_html_e('View all Guides','booked'); ?>&nbsp;&nbsp;<i class="booked-icon booked-icon-sign-out"></i></a>&nbsp;
						<a class="button button-primary" style="margin-bottom:15px; margin-top:0;" href="<?php echo get_admin_url().'admin.php?page=booked-settings'; ?>"><?php esc_html_e('Get Started','booked'); ?></a>
					</div>
					<div class="welcome-panel-column welcome-panel-last">
						<?php do_action( 'booked_welcome_before_changelog' ); ?>
						<?php echo booked_parse_readme_changelog(); ?>
						<?php do_action( 'booked_welcome_after_changelog' ); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
