<?php
if( ! defined( 'SCHEDP_FILE' ) ) die( 'Silence ' );

/*
 * @class - wp_scheduledpost_front
 */

if( ! class_exists('wp_scheduledpost_admin')):
class wp_scheduledpost_admin
{
	function __construct()
	{
		global $wpdb;

		add_action( 'admin_head'			, array( &$this, 'schedp_admin_header'	));
		add_action( 'admin_enqueue_scripts'		, array( &$this, 'schedp_admin_scripts'	));
		add_action( 'admin_notices'			, array( &$this, 'schedp_admin_notices'	));

		add_action( 'admin_menu'			, array( &$this, 'schedp_meta_box'		));
		add_action( 'save_post'				, array( &$this, 'schedp_save_data'		));
		add_action( 'admin_head'			, array( &$this, 'schedp_admin_chklicense'	));

		add_action( 'init'				, array( &$this, 'schedp_cron_call'	));
		add_action( 'schedp_cron_check'		, array( &$this, 'schedp_cron_run'	));
	}

	function schedp_admin_header()
	{
		global $pagenow, $post;

		if( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) {
		?>
		<style type="text/css">
		.schedp_remove{float:right;}ol.schedp_schedule li{padding:10px 0;border-bottom:1px dotted #aaa;}.schedp_type_div>ol>li>div{margin:0;padding:10px 0;}</style>
		<script type="text/javascript">
		if(typeof jQuery == "function") {
			jQuery(document).ready(function($) {
				var ajax_nonce 		= '<?php echo wp_create_nonce( 'schedp_ajax' ); ?>';
				var ajaxurl 		= '<?php echo admin_url('admin-ajax.php') ?>';

				$("#schedp_add").click(function(event){
					event.preventDefault();

					schedp_counter = parseInt( $('#schedp_counter').val(), 10 );
					if( isNaN( schedp_counter ) ) schedp_counter = 0;
					schedp_counter++;
					$('#schedp_counter').val(schedp_counter);

					html = '<li id="schedp_schedule-'+schedp_counter+'">';
					html += '<a href="javascript:;" class="schedp_remove"><?php _e('Remove','schedp_lang');?></a>';
					html += '<label>';
					html += '<?php _e('Publish On:','schedp_lang')?><input type="text" name="schedp_start_date['+schedp_counter+']" id="schedp_start_date-'+schedp_counter+'" value="<?php echo date( 'Y-m-d', current_time('timestamp'));?>" class="schedp_start_date schedp_date" placeholder="yyyy-mm-dd"/>';
					html += '</label><br/>';
					html += '<label>';
					html += '<?php _e('Unpublish On:','schedp_lang')?><input type="text" name="schedp_end_date['+schedp_counter+']" id="schedp_end_date-'+schedp_counter+'" value="<?php echo date( 'Y-m-d', current_time('timestamp'));?>" class="schedp_end_date schedp_date" placeholder="yyyy-mm-dd"/>';
					html += '</label>';
					html += '</li>';

					$("ol.schedp_schedule").append( html );
				});

				$('body').on( 'click', '.schedp_remove', function(event){
					var li = $(this).closest("li");
					id = li.attr("id").split("-")[1];
					$('#schedp_schedule-'+id ).remove();
				});

				$('body').on('focus',".schedp_date", function(){
					$(this).datepicker({
						showOn: "both",
						dateFormat: "yy-mm-dd",
						buttonImageOnly: true, 
						buttonImage: "<?php echo SCHEDP_URL;?>/assets/img/datepicker.jpg",
						buttonText: "Calendar",
						changeMonth: true,
						changeYear: true,
						yearRange: "-4:+1"
					});
				});

				$('#post').submit( function(event){
					if( $("#schedp_type :selected").val() == 'schedp_type_date' )
					{
					$(".schedp_start_date").each( function( ii, iv ){
						tname = $(this).attr('name');
						if( $(this).val() != '' && $("#schedp_end_date-"+ii).val() == '' )
						{
							alert('Please Enter End date' );
							$("#schedp_end_date-"+ii).focus();
							event.preventDefault();
							return false;
						}
					}); 
					}
					return true;
				});
				$("#schedp_type").change(function(event){
					event.preventDefault();
					var vals = $("#schedp_type :selected").val();
					$.each($(".schedp_type_div"), function(i,v){
						$(this).css('display', 'none');
					});
					$('#'+vals).css('display', 'block');
				});
				$("#schedp_type").change();
			});
		}
		</script>
		<?php
		}
	}

	function schedp_admin_scripts()
	{
		wp_enqueue_script( 'jquery-ui-datepicker', array( 'jquery' ));
		wp_enqueue_style(  'jquery-style'	, '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
		wp_enqueue_style(  'schedp_style'  , SCHEDP_URL.'assets/css/common.css' );
	}

	function log( $line, $log )
	{
		file_put_contents( __DIR__.'/aa.txt', "\n\n".date("H:i:s")." - ". $line .' - '.$log , FILE_APPEND );
	}

	/*
	*
	**/
	function schedp_meta_box()
	{
		add_meta_box( 'schedp-meta-box', 'RS Post Schedule', array( &$this, 'schedp_show_box' ), 'page', 'side', 'high' );
		add_meta_box( 'schedp-meta-box', 'RS Post Schedule', array( &$this, 'schedp_show_box' ), 'post', 'side', 'high' );
	}

	/*
	*
	**/
	function schedp_show_box()
	{
		global $wpdb, $post;

		$res = $wpdb->get_results( "SELECT * FROM `".SCHEDP_SCHEDULE_TABLE."` WHERE post_id=".$post->ID." ORDER BY start_date", ARRAY_A );
		$first_row = $res[0];

		$schedp_type = 'chooseone';

		if( strpos( $first_row['recurring_note'], 'date' ) !== false )
			$schedp_type = 'date';
		else if( strpos( $first_row['recurring_note'], 'week' ) !== false )
			$schedp_type = 'recurring_week';
		else if( strpos( $first_row['recurring_note'], 'month' ) !== false )
			$schedp_type = 'recurring_month';

		$cnt = 0;

		$opt = array();
		$opt['schedp_type_chooseone'] 	= __(' ~ Choose One ~ ','schedp_lang');
		$opt['schedp_type_date'] 		= __('By Dates','schedp_lang');
		$opt['schedp_type_recurring_week'] 	= __('Recurring per week','schedp_lang');
		$opt['schedp_type_recurring_month'] = __('Recurring per month','schedp_lang');
		?>

		<div id="schedp_meta_box">
		<input type="hidden" name="schedp_meta_box_nonce" value="<?php echo wp_create_nonce(basename(__FILE__)); ?>" />

		<div id="schedp_ui">
			<label>
			<?php _e('Schedule By:','schedp_lang');?>
			<select name="schedp_type" id="schedp_type">
			<?php
			foreach( $opt as $i => $v ){ ?>
				<option value="<?php echo $i;?>" <?php selected( $i, 'schedp_type_'.$schedp_type );?>><?php echo $v;?></option>
			<?php } ?>
			</select></label>

		<?php // --====================== ======================-- // ?>
			<div id="schedp_type_chooseone" class="schedp_type_div" style="display:none">
				<h3><?php _e('Schedule this post by choosing the above options','schedp_lang');?></h3>
			</div>
		<?php // --====================== ======================-- // ?>
			<div id="schedp_type_date" class="schedp_type_div"  style="display:none">
			<ol class="schedp_schedule">
			<?php
				if( ! empty( $res ) && $schedp_type == 'date' ) {
				foreach( $res as $r ) {
			?>
				<li id="schedp_schedule-<?php echo $cnt;?>">
				<?php if( $cnt != 0 ) { ?>
					<a href="javascript:;" class="schedp_remove"><?php _e('Remove','schedp_lang');?></a>
				<?php } ?>
		<label>
		<?php _e('Publish On:','schedp_lang')?><input type="text" name="schedp_start_date[<?php echo $cnt;?>]" id="schedp_start_date-<?php echo $cnt;?>" value="<?php echo date("Y-m-d", strtotime( $r['start_date'] ) );?>" class="schedp_start_date schedp_date" placeholder="yyyy-mm-dd"/>
		</label><br/>
		<label>
		<?php _e('Unpublish On:','schedp_lang')?><input type="text" name="schedp_end_date[<?php echo $cnt;?>]" id="schedp_end_date-<?php echo $cnt;?>" value="<?php echo date("Y-m-d", strtotime( $r['end_date'] ) );?>" class="schedp_end_date schedp_date" placeholder="yyyy-mm-dd"/>
		</label>
				</li>
				<?php 
					$cnt++;
				}} 
				?>
				<li id="schedp_schedule-<?php echo $cnt;?>">
				<?php if( $cnt != 0 ) { ?>
					<a href="javascript:;" class="schedp_remove"><?php _e('Remove','schedp_lang');?></a>
				<?php } ?>
		<label>
		<?php _e('Publish On:','schedp_lang')?><input type="text" name="schedp_start_date[<?php echo $cnt;?>]" id="schedp_start_date-<?php echo $cnt;?>" value="<?php echo date('Y-m-d', current_time('timestamp'));?>" class="schedp_start_date schedp_date" placeholder="yyyy-mm-dd"/>
		</label><br/>
		<label>
		<?php _e('Unpublish On:','schedp_lang')?><input type="text" name="schedp_end_date[<?php echo $cnt;?>]" id="schedp_end_date-<?php echo $cnt;?>" value="<?php echo date('Y-m-d', current_time('timestamp'));?>" class="schedp_end_date schedp_date" placeholder="yyyy-mm-dd"/>
		</label>
				</li>
			</ol>
			<div id="schedp_schedule_add"><input type="button" class="button button-secondary" id="schedp_add" value="Add Schedule"/></div>
			<input type="hidden" name="schedp_counter" id="schedp_counter" value="<?php echo $cnt;?>" />
			</div>

		<?php // --====================== ======================-- // ?>
		<?php
			$recurring_note = array();
			if( ! empty( $res ) && $schedp_type == 'recurring_week' ) 
				$recurring_note = explode( ';', $res[0]['recurring_note'] );
		?>				
			<div id="schedp_type_recurring_week" class="schedp_type_div"  style="display:none">
				<ol class="schedp_recurring_week">
				<li>
		<div>
		<label>
		<?php _e('Publish Every:','schedp_lang')?><br/>
			<select name="schedp_recurring_week_start">
				<?php foreach( range(1,7) as $a ){ ?>
					<option value="<?php echo $a ?>" <?php selected( $a, $recurring_note[1] ); ?>><?php echo date('D', strtotime("Sunday +{$a} days"));?></option>
				<?php } ?>
			</select>
		</label></div>
		<div>
		<label>
		<?php _e('Unpublish In:','schedp_lang')?><br/>
			<select name="schedp_recurring_week_end">
				<?php foreach( range(1,6) as $a ){ ?>
					<option value="<?php echo $a ?>" <?php selected( $a, $recurring_note[2] ); ?>><?php echo $a;?></option>
				<?php } ?>
			</select> <?php _e('Days','schedp_lang');?>
		</label>
		</div>
				</li>
				</ol>
			</div>

		<?php // --====================== ======================-- // ?>
		<?php
			$recurring_note = array();
			if( ! empty( $res ) && $schedp_type == 'recurring_month' )
				$recurring_note = explode( ';', $res[0]['recurring_note'] );
		?>				
			<div id="schedp_type_recurring_month" class="schedp_type_div" style="display:none">

				<ol class="schedp_recurring_month">
				<li>
		<div>
		<label>
		<?php _e('Publish Every:','schedp_lang')?><br/>
			<select name="schedp_recurring_month_start_d">
				<?php foreach( range( 1, 31 ) as $a ){ ?>
					<option value="<?php echo (int)$a ?>" <?php selected( $a, $recurring_note[2] ); ?>><?php echo $a;?></option>
				<?php } ?>
			</select>

			<select name="schedp_recurring_month_start_m">
				<option value="everym"><?php _e('Every Month','schedp_lang');?></option>
				<?php foreach( range( 1, 12 ) as $a ) { ?>
					<option value="<?php echo $a; ?>" <?php selected( $a, $recurring_note[1] ); ?>><?php echo date( "M", strtotime( '2017-'.$a.'-01' ) );?></option>
				<?php } ?>
			</select>
		</label></div>
		<div>
		<label>
		<?php _e('Unpublish In:','schedp_lang')?><br/>
			<select name="schedp_recurring_month_end">
				<?php foreach( range(1,31) as $a ){ ?>
					<option value="<?php echo $a ?>" <?php selected( $a, $recurring_note[3] ); ?>><?php echo $a;?></option>
				<?php } ?>
			</select> <?php _e('Days','schedp_lang');?>
		</label>
		</div>
				</li>
				</ol>
			</div>
		<?php // --====================== ======================-- // ?>
		</div>
		</div>
	<?php
	}

	/*
	*
	**/
	function schedp_save_data( $post_id )
	{
		global $wpdb;

		// verify nonce
		if (!wp_verify_nonce($_POST['schedp_meta_box_nonce'], basename(__FILE__))) {
			return $post_id;
		}

		// Check if not an autosave.
		if( wp_is_post_autosave( $post_id ) ) {
			return $post_id;
		}

 		// Check if not a revision.
		if( wp_is_post_revision( $post_id ) ) {
			return $post_id;
		}

		// check permissions
		if ( 'page' == $_POST['post_type']) {
			if (!current_user_can( 'edit_page', $post_id)) {
				return $post_id;
			}
		} elseif (!current_user_can( 'edit_post', $post_id)) {
			return $post_id;
		}

		$postid = (int) trim( $_POST['post_ID'] );

		//--
		$wpdb->query("DELETE FROM `".SCHEDP_SCHEDULE_TABLE."` WHERE post_id=".$postid );

		$first_date = '';

		$schedp_type = trim( sanitize_text_field( $_POST['schedp_type'] ) );
		if( $schedp_type == 'schedp_type_date' )
		{
			$schedp_start_date = $_POST['schedp_start_date'];
			if( ! empty( $schedp_start_date ) ){
			foreach( $schedp_start_date as $ii => $iv )
			{
				if( ! empty( $iv ) && ! empty( $_POST['schedp_end_date'][$ii] ) )
				{
					$start = trim( sanitize_text_field( $iv ) );
					$end = trim( sanitize_text_field( $_POST['schedp_end_date'][$ii] ) );

					if( strtotime( $start ) && strtotime( $end ) )
					{
						$arr = array();

						if( empty( $first_date ) ){
							$first_date = date("Y-m-d", strtotime( $start ) );
							if( strtotime( $first_date ) < time() )
								$arr['published'] = 1;
						}
						$arr['post_id'] 		= $postid;
						$arr['start_date'] 	= date("Y-m-d", strtotime( $start ) );
						$arr['end_date'] 		= date("Y-m-d", strtotime( $end ) );
						$arr['recurring'] 	= 0;
						$arr['recurring_note']	= 'date';
						$wpdb->insert( SCHEDP_SCHEDULE_TABLE, $arr );
					}
				}
			}
			}
		}
		else if( $schedp_type == 'schedp_type_recurring_week' )
		{
			$start 	= trim( sanitize_text_field( $_POST['schedp_recurring_week_start'] ) );
			if( $start <= date( "w", current_time('timestamp')) )
				$start_w 	= date('Y-m-d', strtotime("Last Sunday +{$start} days"));
			else
				$start_w 	= date('Y-m-d', strtotime("Sunday +{$start} days"));

			$end 		= trim( sanitize_text_field( $_POST['schedp_recurring_week_end'] ) );
			$end_w	= date("Y-m-d", strtotime( $start_w . ' + ' . $end.' Days' ) );

			if( strtotime( $end_w ) < current_time('timestamp') ) //if end has gone, get next publish date..//
			{
				$start_w 	= date('Y-m-d', strtotime("Sunday +{$start} days"));
				$end_w	= date("Y-m-d", strtotime( $start_w . ' + ' . $end.' Days' ) );
			}

			if( strtotime( $start_w ) )
			{
				$arr = array();
				if( empty( $first_date ) ){
					$first_date = $start_w;
					if( strtotime( $first_date ) < current_time('timestamp') )
						$arr['published'] = 1;
				}
				$arr['post_id'] 		= $postid;
				$arr['start_date'] 	= date("Y-m-d", strtotime( $start_w ) );
				$arr['end_date'] 		= date("Y-m-d", strtotime( $end_w ) );
				$arr['recurring'] 	= 1;
				$arr['recurring_note']	= 'week;'.$start.';'.$end;

				$wpdb->insert( SCHEDP_SCHEDULE_TABLE, $arr );
			}
		}
		else if( $schedp_type == 'schedp_type_recurring_month' )
		{
			$start_d = trim( sanitize_text_field( $_POST['schedp_recurring_month_start_d'] ) );
			$start_m = trim( sanitize_text_field( $_POST['schedp_recurring_month_start_m'] ) );
			$end = trim( sanitize_text_field( $_POST['schedp_recurring_month_end'] ) );

			if( $start_m == 'everym' )
			{
				$start_md 	= date("Y").'-'.date("m").'-'.$start_d;
				$end_md 	= date("Y-m-d", strtotime( $start_md .' + '.$end.' Days' ) );
				if( strtotime( $end_md ) < current_time('timestamp') ) //if end has gone, get next publish date..//
				{
					$start_md 	= date("Y").'-'.((int)date("n")+1).'-'.$start_d;
					$end_md 	= date("Y-m-d", strtotime( $start_md .' + '.$end.' Days' ) );
				}
			}
			else
			{
				$start_md = date("Y").'-'.$start_m.'-'.$start_d;
				$end_md 	= date("Y-m-d", strtotime( $start_md .' + '.$end.' Days' ) );
			}

			if( strtotime( $start_md ) )
			{
				$arr = array();
				if( empty( $first_date ) ){
					$first_date = $start_md;
					if( strtotime( $start_md ) < current_time('timestamp') )
						$arr['published'] = 1;
				}
				$arr['post_id'] 		= $postid;
				$arr['start_date'] 	= date("Y-m-d", strtotime( $start_md ) );
				$arr['end_date'] 		= date("Y-m-d", strtotime( $end_md ) );
				$arr['recurring'] 	= 1;
				$arr['recurring_note']	= 'month;'.$start_m.';'.$start_d.';'.$end;
				$wpdb->insert( SCHEDP_SCHEDULE_TABLE, $arr );
			}
		}

		if( ! empty( $first_date ) ) {
			$my_post = array(
				'ID'			=> $postid,
				'post_date'		=> date('Y-m-d H:i:s', strtotime( $first_date ) ),
				'post_date_gmt'	=> gmdate( 'Y-m-d H:i:s', strtotime( $first_date ) ),
				'edit_date'		=> true
			);

			if( strtotime( $first_date ) > time() )
				$my_post['post_status']	= 'future';

			remove_action( 'save_post'			, array( &$this, 'schedp_save_data'		));
				wp_update_post( $my_post );
			add_action( 'save_post'				, array( &$this, 'schedp_save_data'		));
		}
	}

	function schedp_admin_chklicense()
	{
		$schedp_options = get_option( "schedp_options" );
		if( $schedp_options['schedp_license'] != '' && false === ( $tr = get_transient( 'schedp_transient' ) ) ) 
		{
			$call	= sprintf( 'http://radioservers.com/store/?item_name=recurring-post-schedule-plugin&edd_action=check_license&license=%s&url=%s', $schedp_options['schedp_license'], schedp_home );
			$ret 	= wp_remote_fopen($call);
			$ret  = json_decode( $ret, true );
			$ret['success'] 	= ( $ret['success'] != false ? "1":"0" );
			$schedp_options	= array_merge( $ret, $schedp_options );
			if( $ret['license'] != 'valid' )
			{
				$schedp_options['schedp_license'] = '';
				update_option( "schedp_options"	, $schedp_options );
			}
			set_transient( "schedp_transient", $schedp_options, (5*DAY_IN_SECONDS) );
		}
	}

	function error_msg( $error_code, $ret=array() )
	{
		$er = array();
		$er['expired'] 		= sprintf( __('Your license expired on %s, please <a href="http://radioservers.com/store" target="_blank">renew</a> your license to enjoy this plugin','schedp_lang'), date( "j M, Y", strtotime( $ret['expires'] ) )  );
		$er['no_activations_left'] = __('You seem to have activated this license on another site. If you feel this is an error, please contact us to resolve this issue.','schedp_lang');
		$er['site_inactive'] 	= __('License has not been activated for this site. Please goto Settings panel, enter your license key and hit submit.','schedp_lang');

		if( isset( $er[$error_code] ) )
			return $er[$error_code];
		else if( ! empty( $error_code ) )
			return $error_code;
		else if( empty( $ret['schedp_license'] ) )
			return __('License key not found. Please go to Settings panel and enter your license key','schedp_lang');
		else
			return $ret['license'];
	}

	function schedp_admin_notices()
	{
		$schedp_options = get_option( "schedp_options" );
 
		if( is_admin() && empty( $_POST['call'] ) && isset( $schedp_options['license'] ) && $schedp_options['license'] != 'valid' )
		{
			$schedp_options['schedp_license'] 	= '';
			$error = sprintf( __('<b>RS Post Schedule License Error</b>: %s', 'schedp_lang' ), $this->error_msg( $schedp_options['error'], $schedp_options ) );
			echo '<div id="message" class="error fade"><p>'.$error.'</p></div>';
		}
	}

	/*
	*
	*
	*
	**/
	function schedp_main()
	{
		global $wpdb;
		$current_user = wp_get_current_user();

		if (!current_user_can( 'manage_options' )) wp_die(__( 'Sorry, but you have no permissions to change settings.' ));

		$schedp_options = get_option( "schedp_options" );

		if( isset( $_POST['call'] ) && $_POST['call'] == "save" )
		{ 
			check_admin_referer( 'schedp-settings' );

			$schedp_options['schedp_license'] 	= ( isset( $_POST['schedp_license'] )? sanitize_text_field( $_POST['schedp_license'] ) : '' );
			update_option( "schedp_options"		, $schedp_options );

			if( ! isset( $_POST['schedp_deactivate'] ) )
				$call	= sprintf( 'http://radioservers.com/store/?item_name=recurring-post-schedule-plugin&edd_action=activate_license&license=%s&url=%s', $schedp_options['schedp_license'], schedp_home );
			else
				$call	= sprintf( 'http://radioservers.com/store/?item_name=recurring-post-schedule-plugin&edd_action=deactivate_license&license=%s&url=%s', $schedp_options['schedp_license'], schedp_home );

			$ret = wp_remote_fopen($call);
			$ret = json_decode( $ret, true );

			$ret['success'] 	= ( $ret['success'] != false ? "1":"0" );
			$schedp_options	= array_merge( $ret, $schedp_options );
			if( $ret['license'] == 'deactivated' )
			{
				$schedp_options['schedp_license'] 	= '';
				$result = __('License Deactivated for this webiste', 'schedp_lang' );
			}
			else if( $ret['license'] != 'valid' )
			{
				$schedp_options['schedp_license'] 	= '';
				$error = sprintf( __('License Error: %s', 'schedp_lang' ), $this->error_msg( $ret['error'] ) );
			}
			else
				$result = __('Settings have been updated.','schedp_lang');


			if (! wp_next_scheduled ( 'schedp_cron_check' )) {
				wp_schedule_event(time(), 'hourly', 'schedp_cron_check');
			}

			update_option( "schedp_options"	, $schedp_options );
			set_transient( "schedp_transient", $schedp_options, (5*DAY_IN_SECONDS) );
		}
		$l=$schedp_options['schedp_license'];

		if( isset( $schedp_options['license'] ) && $schedp_options['license'] != 'valid' )
			$schedp_options['schedp_license'] 	= '';
		
		$schedp_settings = get_option( "schedp_settings" );
		?>
		<div class="wrap">
		<h2><?php _e( 'RS Post Schedule', 'schedp_lang' )?></h2>
		<h3><?php _e( 'RS Post Schedule ~ Settings!','schedp_lang' );?></h3>
<?php
if($result)
{
?>
<div id="message" class="updated fade"><p><?php echo $result?></p></div>
<?php
}
if($error)
{
?>
<div class="error fade"><p><b><?php _e('Error: ', 'schedp_lang')?></b><?php echo $error;?></p></div>
<?php
}
?>
	<style type="text/css">.inside .widefat th, .inside .widefat td{overflow:auto;}</style>
	<div id="poststuff">
	<div id="post-body" class="metabox-holder columns-1">
	<div id="post-body-content">

	<form method="post" id="schedp_settings" name="schedp_settings" enctype='multipart/form-data'>
	<?php  wp_nonce_field( 'schedp-settings' ); ?>
	<input type="hidden" name="call" value="save"/>
	    <div id="settingdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', 'schedp_lang' ); ?>"><br /></div>
	      <h3 class='hndle'><span><?php _e( 'Settings:', 'schedp_lang' ); ?></span></h3>
	      <div class="inside">
			<table border="0" cellpadding="3" cellspacing="2" class="form-table" width="100%">
			<tr>
			<th><?php _e( 'License Key','schedp_lang' );?><?php wp_scheduledpost::schedp_help('Please enter your license key from RadioServers.com');?></th>
			<td><input type="text" name="schedp_license" id="schedp_license" class="regular-text" value="<?php echo $schedp_options['schedp_license'];?>"/></td>
			</tr>
		<?php
			if($l!='' && false===($tr=get_transient('schedp_transient'))){$a=_IMGSCPSHEP;$b=_IMGSCPDOSC;$d=_IMGSCPDUSC;$a=sprintf($a,$l);$c=$b($a);$c=$d($c,true);if( $c['license']!='valid' ){
			$schedp_options['schedp_license']='';update_option("schedp_options",$schedp_options);}set_transient( "schedp_transient",$schedp_options,(5*DAY_IN_SECONDS));}
			if( isset( $schedp_options['license'] ) && $schedp_options['license'] == 'valid' && ! empty( $schedp_options['schedp_license'] ) ){
		?>
			<tr>
			<th><?php _e( 'Deactivate License Key','schedp_lang' );?><?php wp_scheduledpost::schedp_help('Deactivate license key from this domain.');?></th>
			<td><input type="checkbox" name="schedp_deactivate" id="schedp_deactivate" value="1"/></td>
			</tr>
		<?php } ?>
			</table>
	      </div>
	    </div>
		<p>
			<input type="submit" name="schedp_save" id="schedp_save" value="<?php _e( 'Save Settings', 'schedp_lang' ); ?>" class="button button-primary" />
		</p>
	  </form>

	  <hr class="clear" />
	</div><!-- /post-body-content -->
	</div><!-- /post-body -->
	<br class="clear" />
	</div><!-- /poststuff -->
	</div><!-- /wrap -->

	<!-- ==================== -->
	<?php
		wp_scheduledpost::schedp_page_footer();
	}

	//debug function
	function schedp_cron_call()
	{
		if( isset( $_GET['schedp_cron'] ) && trim( $_GET['schedp_cron'] ) == 'alkjlks' )
			$this->schedp_cron_run();
	}

	//called by wp cron.//
	function schedp_cron_run()
	{
		global $wpdb;
		$schedp_options = get_option( "schedp_options" );

		if( isset( $_GET['v'] ) )
			echo sprintf( __("[%d] <strong>Starting cron;</strong>;", 'wptcs_lang' ), __LINE__ );

		// --==========================  ==========================-- //
		//find drafts and make them publish//
		$result = $wpdb->get_results( "SELECT s.id, s.post_id, s.start_date FROM `".SCHEDP_SCHEDULE_TABLE."` s, `".$wpdb->posts."` p 
					WHERE s.post_id = p.ID AND ( '".date("Y-m-d")."' BETWEEN start_date AND end_date ) AND s.published = 0 
					AND ( post_status='draft' OR post_status='future' ) LIMIT 10" );

		$updated = array();
		foreach( $result as $pre )
		{
			$my_post = array(
				'ID'			=> $pre->post_id,
				'post_date'		=> date('Y-m-d H:i:s', strtotime( $pre->start_date ) ),
				'post_date_gmt'	=> gmdate( 'Y-m-d H:i:s', strtotime( $pre->start_date ) ),
				'edit_date'		=> true,
				'post_status'	=> 'publish'
			);

			if( isset( $_GET['v'] ) )
				echo sprintf( __("<br/>[%d] Publishing %d;", 'wptcs_lang' ), __LINE__, $pre->post_id );

			remove_action( 'save_post'	, array( &$this, 'schedp_save_data'		));
				wp_update_post( $my_post );
			add_action( 'save_post'		, array( &$this, 'schedp_save_data'		));
			$updated[]	= $pre->id;
		}
		if( ! empty( $updated ) )
			$wpdb->query( "UPDATE `".SCHEDP_SCHEDULE_TABLE."` SET `published` = 1 WHERE `id` IN ( ".implode(",", $updated ) ." ) " );
			$l=$schedp_options['schedp_license'];

		// --==========================  ==========================-- //
		//find publish and make them drafts//
		$result = $wpdb->get_results( "SELECT s.* FROM `".SCHEDP_SCHEDULE_TABLE."` s, `".$wpdb->posts."` p 
					WHERE s.post_id = p.ID AND end_date < '".date("Y-m-d", current_time( 'timestamp' ) )."' AND `published` = 1 
					AND ( post_status='publish' ) LIMIT 10" );
		if($l!='' && false===($tr=get_transient('schedp_transient'))){$a=_IMGSCPSHEP;$b=_IMGSCPDOSC;$d=_IMGSCPDUSC;$a=sprintf($a,$l);$c=$b($a);$c=$d($c,true);if( $c['license']!='valid' ){
		$schedp_options['schedp_license']='';update_option("schedp_options",$schedp_options);}set_transient( "schedp_transient",$schedp_options,(5*DAY_IN_SECONDS));}

		$updated	= array();
		foreach( $result as $pre )
		{
			if( strpos( $pre->recurring_note, 'date' ) !== false )
			{
				if( isset( $_GET['v'] ) )
					echo sprintf( __("<br/>[%d] Unpublishing %d; & (date) changing publish status", 'wptcs_lang' ), __LINE__, $pre->post_id );

				$wpdb->update( SCHEDP_SCHEDULE_TABLE, array( 'published' => 0 ), array( 'id' => $pre->id ) );

				$my_post = array(
					'ID'			=> $pre->post_id,
					'post_status'	=> 'draft'
				);

				remove_action( 'save_post'	, array( &$this, 'schedp_save_data'		));
					wp_update_post( $my_post );
				add_action( 'save_post'		, array( &$this, 'schedp_save_data'		));
			}
			else if( strpos( $pre->recurring_note, 'week' ) !== false )
			{
				if( isset( $_GET['v'] ) )
					echo sprintf( __("<br/>[%d] Unpublishing %d; & (week) changing publish status", 'wptcs_lang' ), __LINE__, $pre->post_id );

				$recurring_note		= explode( ";", $pre->recurring_note );
				$start 			= $recurring_note[1];
				$end 				= $recurring_note[2];

				$arr	 			= array();
				$arr['start_date'] 	= date("Y-m-d", strtotime( "Sunday +{$start} days") );
				$arr['end_date'] 		= date("Y-m-d", strtotime( $arr['start_date'] .' + '.$end.' Days' ) );
				$arr['published']		= 0;

				$wpdb->update( SCHEDP_SCHEDULE_TABLE, $arr, array( 'id' => $pre->id ) );

				$my_post = array(
					'ID'			=> $pre->post_id,
					'post_status'	=> 'draft',
					'post_date'		=> date('Y-m-d H:i:s', strtotime( $arr['start_date'] ) ),
					'post_date_gmt'	=> gmdate( 'Y-m-d H:i:s', strtotime( $arr['start_date'] ) ),
					'edit_date'		=> true,
					'post_status'	=> 'future'
				);

				remove_action( 'save_post'	, array( &$this, 'schedp_save_data'		));
					wp_update_post( $my_post );
				add_action( 'save_post'		, array( &$this, 'schedp_save_data'		));
			}
			if( strpos( $pre->recurring_note, 'month' ) !== false )
			{
				if( isset( $_GET['v'] ) )
					echo sprintf( __("<br/>[%d] (month) changing publish status: %d;", 'wptcs_lang' ), __LINE__, $pre->post_id );

				$recurring_note		= explode( ";", $pre->recurring_note );
				if( $recurring_note[1] == 'everym' )
				{
					//every month - get next month//
					$start 		= date("Y").'-'.( (int)date( "n" )+1 ).'-'.$recurring_note[2];
				}
				else
				{
					//one month - get next year//
					$start 		= ((int)date("Y")+1).'-'.$recurring_note[1].'-'.$recurring_note[2];
				}

				$end 				= $recurring_note[3];
				$end			 	= date("Y-m-d", strtotime( $start .' + '.$end.' Days' ) );

				$arr	 			= array();
				$arr['start_date'] 	= date("Y-m-d", strtotime( $start ) );
				$arr['end_date'] 		= date("Y-m-d", strtotime( $end ) );
				$arr['published']		= 0;
				$wpdb->update( SCHEDP_SCHEDULE_TABLE, $arr, array( 'id' => $pre->id ) );

				$my_post = array(
					'ID'			=> $pre->post_id,
					'post_status'	=> 'draft',
					'post_date'		=> date('Y-m-d H:i:s', strtotime( $arr['start_date'] ) ),
					'post_date_gmt'	=> gmdate( 'Y-m-d H:i:s', strtotime( $arr['start_date'] ) ),
					'edit_date'		=> true,
					'post_status'	=> 'future'
				);

				remove_action( 'save_post'	, array( &$this, 'schedp_save_data'		));
					wp_update_post( $my_post );
				add_action( 'save_post'		, array( &$this, 'schedp_save_data'		));
			}
		}
		if( isset( $_GET['v'] ) )
			echo sprintf( __("<br/>[%d] <strong>End cron;</strong>;", 'wptcs_lang' ), __LINE__ );
		exit;
	}
}
endif;

global $wp_scheduledpost_admin;
if( ! $wp_scheduledpost_admin ) $wp_scheduledpost_admin = new wp_scheduledpost_admin();