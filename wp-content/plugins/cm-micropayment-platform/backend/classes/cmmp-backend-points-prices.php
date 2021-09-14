<?php

include_once CMMP_PLUGIN_DIR . '/shared/models/points.php';

class CMMicropaymentPlatformBackendPointsPrices extends CMMicropaymentPlatformPointsPrices {

	public function render() {
		global $wpdb;
		require_once CMMP_PLUGIN_DIR . '/backend/classes/point-prices/list.php';

		$wp_list_table = new CMMicropaymentPlatformBackendPointsPricesList();
		$wp_list_table->prepare_items();

		echo '<div class="wrap">';
		$action = isset( $_GET['cmm-action'] ) ? $_GET['cmm-action'] : null;

		switch ( $action ) {
			case 'edit':
				$data = false;
				if ( isset( $_GET['cmm-id'] ) && $_GET['cmm-id'] != '' ) {
					$data = self::getOne( $_GET['cmm-id'] );
				}
				self::form( $data );
				break;
			case 'add':
				self::form();
				break;
			case 'remove':
				self::remove( $_GET['cmm-id'] );
				break;
			default:

				$page_tab = 'points-prices';

				ob_start();
				if ( file_exists( plugin_dir_path( CMMP_PLUGIN_FILE ) . 'backend/views/setting-tabs/' . $page_tab . '.phtml' ) ) {
					include_once plugin_dir_path( CMMP_PLUGIN_FILE ) . 'backend/views/setting-tabs/' . $page_tab . '.phtml';
				}
				$tab_content = ob_get_clean();

				$tab_content_filtered = apply_filters( 'cmmp_tab_content', $tab_content, $page_tab );

				ob_start();
				include CMMP_PLUGIN_DIR . '/backend/views/settings.phtml';

				$content = ob_get_clean();
				echo $content;

				break;
		}
		echo '</div>';
	}

	public static function getAll( $type = OBJECT ) {
		global $wpdb;

		$query  = "SELECT * FROM " . self::getTable();
		$result = $wpdb->get_results( $query, $type );
		if ( ! empty( $result ) ) {
			return $result;
		}

		return false;
	}

	public static function getOne( $id, $type = OBJECT ) {
		global $wpdb;

		$query  = "SELECT * FROM " . self::getTable() . " WHERE points_cost_id = " . esc_sql( $id );
		$result = $wpdb->get_row( $query, $type );
		if ( ! empty( $result ) ) {
			return $result;
		}

		return false;
	}

	public static function remove( $id ) {
		global $wpdb;
		if ( isset( $id ) && $id != '' ) {
			$wpdb->delete( self::getTable(), array( 'points_cost_id' => $id ) );
			$_SESSION['success-message'] = __( 'Point setting has been deleted' );
		}
		wp_redirect( admin_url( 'admin.php?page=cm-micropayment-platform-settings&tab=points-prices' ), 301 );
		exit;
	}

	private function form( $data = false ) {
		self::registerScripts();
		self::registerStyles();
		self::handlePost();

		if ( ob_start() ) {
			$page_tab = 'points-prices';
			include CMMP_PLUGIN_DIR . '/backend/views/points-settings-forms.phtml';
			$content = ob_get_clean();
			echo $content;
		}
	}

	public static function handlePost() {
		global $wpdb;

		if ( isset( $_POST['cost'] ) && isset( $_POST['points-value'] ) ) {
			$postData = $_POST;

			if ( ! isset( $postData['points-value'] ) ) {
				$_SESSION['error-message'] = __( 'Please type a value of points' );

				return false;
			}
			if ( ! CMMicropaymentPlatform::numericOrFloat( $postData['points-value'] ) || $postData['points-value'] <= 0 ) {

				$_SESSION['error-message'] = __( 'Points is lower than 0 or is not a number' );

				return false;
			}
			$postData['points-value']  = CMMicropaymentPlatform::convertType( $postData['points-value'] );

			if ( ! isset( $postData['cost'] ) ) {
				$_SESSION['error-message'] = __( 'Please type a price of points' );

				return false;
			}
			if ( ! is_numeric( $postData['cost'] ) || $postData['cost'] <= 0 ) {
				$_SESSION['error-message'] = __( 'Price is lower than 0 or is not a number' );

				return false;
			}

			if ( isset( $postData['points_cost_id'] ) && $postData['points_cost_id'] > 0 ) {
				$update = true;
			} else {
				$update = false;
			}

			if ( ! $update ) {
				$query  = "SELECT * FROM " . self::getTable() . " WHERE points_value = " . esc_sql( $postData['points-value'] );
				$result = $wpdb->get_results( $query );

				if ( isset( $result[0] ) ) {
					$_SESSION['error-message'] = __( 'Value of points already exists' );

					return false;
				}
			}

			$data = array(
				'points_value' => CMMicropaymentPlatform::convertType($postData['points-value']),
				'cost'         => $postData['cost']
			);

			if ( $update ) {
				$update = $wpdb->update( self::getTable(), $data, array( 'points_cost_id' => $postData['points_cost_id'] ) );
				if ( $update ) {
					$_SESSION['success-message'] = __( 'Point setting has been saved' );
				} else {
					$_SESSION['success-message'] = __( 'Error during saving points setting' );
				}
			} else {
				$result = $wpdb->insert( self::getTable(), $data );
				if ( ! is_wp_error( $result ) ) {
					$_SESSION['success-message'] = __( 'New point settings has been saved' );
				} else {
					$_SESSION['error-message'] = $result->get_error_message();
				}
			}

			return true;
		}
	}

	public static function registerScripts() {
		wp_enqueue_script( 'jquery-ui' );
		wp_enqueue_script( 'cm-micropayment-admin-scripts', CMMP_PLUGIN_URL . '/backend/assets/js/scripts.js', array('jquery-ui'), CMMicropaymentPlatform::version() );
		$jsData         = array(
			'ajaxurl' => admin_url( 'admin-ajax.php?action=cm_micropayment_platform_save_wallet_points' ),
			'l18n'    => array(
				'save'   => __( 'Save' ),
				'cancel' => __( 'Cancel' ),
				'label'  => __( 'Change button points value' ),
			)
		);
		$jsDataFiltered = apply_filters( 'cmmp_wallet_render_js_data', $jsData );
		wp_localize_script( 'cm-micropayment-admin-scripts', 'cmmp_data', $jsDataFiltered );
	}

	public static function registerStyles() {
		wp_enqueue_style( 'cm-micropayment-backend-jquery-ui', CMMP_PLUGIN_URL . '/backend/assets/css/jquery-ui/ui-lightness/jquery-ui-1.10.4.custom.min.css' );
		wp_enqueue_style( 'cm-micropayment-backend-style', CMMP_PLUGIN_URL . '/backend/assets/css/style.css' );
	}

	private static function getTable() {
		global $wpdb;
		if ( self::$_tableName == null ) {
			if ( is_multisite() && CMMPMultisite::is_shared_network() ) {
				self::$_tableName = $wpdb->base_prefix . "cm_micropayments_defined_points_cost";
			} else {
				self::$_tableName = $wpdb->prefix . "cm_micropayments_defined_points_cost";
			}
		}

		return self::$_tableName;
	}

}
