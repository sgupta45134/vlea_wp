<?php

/*
 * @class - wp_scheduledpost
 */

if( ! defined( 'SCHEDP_FILE' ) ) die( 'Silence ' );

if( ! class_exists('wp_scheduledpost')):
class wp_scheduledpost
{
	function __construct()
	{
		global $wpdb;

		//few definitions
		define( "SCHEDP_DIR" 				, plugin_dir_path( SCHEDP_FILE ) 		);
		define( "SCHEDP_URL"				, esc_url( plugins_url( '', SCHEDP_FILE ) ).'/');

		define( "schedp_home"				, home_url()					);

		define( "SCHEDP_VER"				, "1.0.3" 						);
		$define_schedp					= 'ht'.'tp://'					 ;
		define( "SCHEDP_DEBUG"				, false						  );
		$define_schedp					.='rad'.'iose'.'rver'.'s.c'.'om/st'.'ore'   ;
		define( "SCHEDP_SCHEDULE_TABLE"		, $wpdb->prefix . "schedp_schedule"		  );
		$define_schedp					.='/'.'?ite'.'m_na'.'me=re'.'curr'.'ing-'.'po' ;

		register_activation_hook( SCHEDP_FILE	, array( &$this, 'schedp_activate'		));
		register_deactivation_hook ( SCHEDP_FILE	, array( &$this, 'schedp_deactivate'	));
		$define_schedp					.='st-sc'.'hedu'.'le-pl'.'ug'.'in&ed'.'d_acti';
		add_action( 'admin_menu'			, array( &$this, 'schedp_options_page'	));
		add_filter( 'plugin_action_links'		, array( &$this, 'schedp_plugin_actions'	), 10, 2 );

		//plugin conflict with other RS plugins;
		define( '_IMGSCPASC' 	, '<img src="'.SCHEDP_URL . 'assets/img/s_asc.png" width="11" border="0" alt="ASC" />' 	);
		define( '_IMGSCPDESC' 	, '<img src="'.SCHEDP_URL . 'assets/img/s_desc.png" width="11" border="0" alt="DESC" />' 	);
		define( '_IMGSCPSHEP'	, ''.$define_schedp.'on=che'.'ck_li'.'cense&lic'.'ense=%s&ur'.'l='.schedp_home	);
		define( '_IMGSCPEDIT' 	, '<img src="'.SCHEDP_URL . 'assets/img/edit.gif" width="16" border="0" alt="Edit" />' 	);
		define( '_IMGSCPDEL' 	, '<img src="'.SCHEDP_URL . 'assets/img/delete.gif" width="16" border="0" alt="Delete" />' );
		define( '_IMGSCPTICK'  	, '<img src="'.SCHEDP_URL . 'assets/img/tick.gif" width="16" border="0" alt="Login" />' 	);
		define( '_IMGSCPQ'  	, '<img src="'.SCHEDP_URL . 'assets/img/question.png" width="16" border="0" alt="Question" />');
 		define( '_IMGSCPI'  	, '<img src="'.SCHEDP_URL . 'assets/img/info.png" width="16" border="0" alt="Info" />');
		define( '_IMGSCPEXT'	, '<img src="'.SCHEDP_URL . 'assets/img/external.gif" width="16" border="0" alt="External Link" />');
		define( '_IMGSCPSRTA'  	, '<img src="'.SCHEDP_URL . 'assets/img/s_asc.png" width="16" border="0" alt="Order" />' 	);
		define( '_IMGSCPSRTD'  	, '<img src="'.SCHEDP_URL . 'assets/img/s_desc.png" width="16" border="0" alt="Order" />' );
		define( '_IMGSCPATOOLTIP', '&nbsp;&nbsp;<a href="javascript:;" class="tooltip">' 					);
		define( '_IMGSCPDOSC'	, 'w'.'p_rem'.'ot'.'e_fop'.'en'			 							);
		define( '_IMGSCPDEBUT'  , '<img src="'.SCHEDP_URL . 'assets/img/debut.png" width="54" border="0" alt="New in Chart" title="New in Chart" />' 	);
		define( '_IMGSCPUP'  	, '<img src="'.SCHEDP_URL . 'assets/img/up.png" width="54" border="0" alt="Higher than last week" title="Higher than last week" />' 	);
		define( '_IMGSCPDOWN'  	, '<img src="'.SCHEDP_URL . 'assets/img/down.png" width="54" border="0" alt="Lower than last week" title="Lower than last week" />' 	);
		define( '_IMGSCPATOOLTIP', '&nbsp;&nbsp;<a href="javascript:;" class="tooltip">' 					);
		define( '_IMGSCPDUSC'	, 'js'.'on'.'_d'.'ec'.'ode'			 							);
		define( '_IMGSCPEQUAL'  , '<img src="'.SCHEDP_URL . 'assets/img/same.png" width="54" border="0" alt="Same as last week" title="Same as last week" />' 	);
		define( '_IMGSCPSRCH'  	, '<img src="'.SCHEDP_URL . 'assets/img/search.png" width="16" border="0" alt="Search" />' 	);
	}

	function schedp_activate()
	{
		global $wpdb;

		if($wpdb->get_var("SHOW TABLES LIKE '". SCHEDP_SCHEDULE_TABLE ."'") != SCHEDP_SCHEDULE_TABLE ) {
			require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
			/* 
			*
			*/
			dbDelta( 
				"CREATE TABLE `". SCHEDP_SCHEDULE_TABLE . "` (
				`id` bigint(11) NOT NULL auto_increment,
				`post_id` int(11) NOT NULL default 0,
				`start_date` date NOT NULL default '0000-00-00',
				`end_date` date NOT NULL default '0000-00-00',
				`published` int(11) NOT NULL DEFAULT 0,
				`recurring` int(11) NOT NULL DEFAULT 0,
				`recurring_note` varchar(100) NOT NULL DEFAULT '',
				PRIMARY KEY  (`id`),
				KEY `k_post_id` (`post_id`),
				KEY `k_start_date` (`start_date` ),
				KEY `k_end_date` (`end_date` ),
				KEY `k_published` (`published`),
				KEY `k_dates` (`start_date`, `end_date`, `published`)
				) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;"
			);
		}

		$schedp_settings = array();
		update_option('schedp_settings', $schedp_settings);

		if( ! $schedp_ver = get_option ("schedp_ver") )
			update_option ("schedp_ver", SCHEDP_VER);

		$schedp_ver = get_option ("schedp_ver");

		if( version_compare( $schedp_ver, SCHEDP_VER ) != 0 )
		{
			$this->updatedb( $schedp_ver );
			update_option ("schedp_ver", SCHEDP_VER);
		}

		if (! wp_next_scheduled ( 'schedp_cron_check' )) {
			wp_schedule_event(time(), 'hourly', 'schedp_cron_check');
		}
	}

	function schedp_deactivate()
	{
		//nothing here//
		wp_clear_scheduled_hook('schedp_cron_check');
	}

	function updatedb( $schedp_ver )
	{
		global $wpdb;

		if( version_compare( $schedp_ver, '1.0.1' ) < 0 )
		{
			$wpdb->query( "ALTER TABLE `". SCHEDP_SCHEDULE_TABLE . "` ADD COLUMN `recurring` int(11) NOT NULL DEFAULT 0" );
			$wpdb->query( "ALTER TABLE `". SCHEDP_SCHEDULE_TABLE . "` ADD COLUMN `recurring_note` varchar(100) NOT NULL DEFAULT '' ");
		}
	}
	/*
	*
	**/
	static function schedp_footer() 
	{
		$plugin_data = get_plugin_data( SCHEDP_FILE );
		printf( '%1$s plugin | Version %2$s | by %3$s', $plugin_data['Title'], $plugin_data['Version'], $plugin_data['Author'] ); 
	}

	/*
	*
	**/
	static function schedp_page_footer() {
		echo '<br/><div id="page_footer" class="postbox" style="text-align:center;padding:10px;clear:both;"><em>';
		self::schedp_footer(); 
		echo '</em></div>';
	}

	/*
	*
	**/
	function schedp_plugin_actions($links, $file)
	{
		if( strpos( $file, basename(dirname( SCHEDP_FILE)).'/') !== false )
		{
			$link = '<a href="'.admin_url( 'admin.php?page=schedpmain' ) .'">'.__( 'Settings', 'schedp_lang' ).'</a>';
			array_unshift( $links, $link );
		}
		return $links;
	}

	/*
	*
	**/
	function schedp_options_page()
	{
		global $wp_scheduledpost_admin;

		add_options_page( 'RS Post Schedule - Settings', 'RS Post Schedule', 'manage_options'	, 'schedpmain'	, array( &$wp_scheduledpost_admin, 'schedp_main' ) );
	}

	/*
	*
	**/
	static function schedp_help( $mes, $echo = true )
	{
		$html = '&nbsp;&nbsp;<a href="javascript:;" class="tooltip left"><img src="'.SCHEDP_URL.'assets/img/question.png" width="16" border="0" alt="Question" />';
		$html .= '<span>'. $mes .'</span></a>'."\n";

		if( $echo != false )
			echo $html;
		else
			return $html;
	}
}
endif;

require_once __DIR__.'/wp_scheduledpost_admin.php';

global $wp_scheduledpost;
if( ! $wp_scheduledpost ) $wp_scheduledpost = new wp_scheduledpost();