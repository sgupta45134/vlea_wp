<?php
if ( !class_exists( 'CMMPEddGrantPoints' ) ) {

	class CMMPEddGrantPoints {

		public static function init() {
			add_action( 'add_meta_boxes', array( __CLASS__, 'addEddGrantedPointsMetabox' ), 120 );
			add_action( 'edd_download_price_table_head', array( __CLASS__, 'addEddGrantedPointsHeader' ), 900 );
			add_action( 'edd_download_price_table_row', array( __CLASS__, 'addEddGrantedPointsRow' ), 900, 3 );
			add_action( 'save_post', array( __CLASS__, 'eddPointsGrantedMetaboxSave' ) );

			// grants the points during purchase for EDD 1.6+
			add_action( 'edd_complete_download_purchase', array( __CLASS__, 'eddGrantPointsPerPurchase' ), 2, 5 );

			add_filter( 'edd_currencies', array( __CLASS__, 'registerCurrency' ) );
		}

		public static function registerCurrency( $currencies ) {
			$unitName	 = CMMicropaymentPlatform::get_option( 'cm_micropayment_singular_name' );
			$unitId		 = CMMicropaymentPlatformLabel::getLabel( 'external_currency_id' );
			if ( $unitName && $unitId ) {
				$currencies[ $unitId ] = ucfirst( $unitName );
			}
			return $currencies;
		}

		public static function addEddGrantedPointsMetabox() {
			if ( !CMMicropaymentPlatform::isEddIntegrationActive() ) {
				return;
			}

			global $post;

			$featureEnabled = CMMicropaymentPlatform::get_option( 'cm_micropayment_grant_points_per_purchase', 0 );
			if ( !$featureEnabled ) {
				return;
			}

			if ( !function_exists( 'edd_get_download_type' ) ) {
				return;
			}

			if ( 'bundle' != edd_get_download_type( get_the_ID() ) ) {
				add_meta_box( 'edd_mp_box', __( 'Grant MicroPayments Points' ), array( __CLASS__, 'addEddRenderGrantedPointsMetabox' ), 'download', 'normal', 'core' );
			}
		}

		/**
		 * Render the granted MicroPayments points metabox
		 *
		 * @since 1.0
		 */
		public static function addEddRenderGrantedPointsMetabox() {
			$featureEnabled = CMMicropaymentPlatform::get_option( 'cm_micropayment_grant_points_per_purchase', 0 );
			if ( !$featureEnabled ) {
				return;
			}

			global $post;
			// Use nonce for verification
			echo '<input type="hidden" name="edd_mp_meta_box_nonce" value="', wp_create_nonce( basename( __FILE__ ) ), '" />';

			echo '<table class="form-table">';

			$enabled = get_post_meta( $post->ID, '_edd_cmmp_enabled', true ) ? true : false;
			$points	 = get_post_meta( $post->ID, '_edd_cmmp_points', true ) ? get_post_meta( $post->ID, '_edd_cmmp_points', true ) : '0';

			$display = $enabled ? '' : ' style="display:none;"';

			echo '<script type="text/javascript">jQuery( document ).ready( function($) {$( "#edd_cmmp_enabled" ).on( "click",function() {$( ".edd_mp_toggled_row" ).toggle();} )} );</script>';

			echo '<tr>';
			echo '<td class="edd_field_type_text" colspan="2">';
			echo '<input type="checkbox" name="edd_cmmp_enabled" id="edd_cmmp_enabled" value="1" ' . checked( true, $enabled, false ) . '/>&nbsp;';
			echo '<label for="edd_cmmp_enabled">' . __( 'Check if you want to grant MicroPayments points per each purchase.' ) . '</label>';
			echo '<td>';
			echo '</tr>';

			echo '<tr' . $display . ' class="edd_mp_toggled_row">';
			echo '<td class="edd_field_type_text" colspan="2">';
			echo '<input type="number" class="small-text" name="edd_cmmp_points" id="edd_cmmp_points" value="' . esc_attr( $points ) . '"/>&nbsp;';
			echo __( 'Set the overall number of points granted. If using variable pricing this can be overriden per each download option.' );
			echo '<td>';
			echo '</tr>';

			echo '</table>';
		}

		public static function addEddGrantedPointsHeader( $download_id ) {
			$featureEnabled = CMMicropaymentPlatform::get_option( 'cm_micropayment_grant_points_per_purchase', 0 );
			if ( !$featureEnabled ) {
				return;
			}

			if ( !function_exists( 'edd_get_download_type' ) ) {
				return;
			}

			if ( 'bundle' == edd_get_download_type( $download_id ) ) {
				return;
			}
			?>
			<th><?php _e( 'MicroPayment Points Granted' ); ?></th>
			<?php
		}

		public static function addEddGrantedPointsRow( $download_id, $price_id, $args ) {
			$featureEnabled = CMMicropaymentPlatform::get_option( 'cm_micropayment_grant_points_per_purchase', 0 );
			if ( !$featureEnabled ) {
				return;
			}

			if ( !function_exists( 'edd_get_download_type' ) ) {
				return;
			}

			if ( 'bundle' == edd_get_download_type( $download_id ) ) {
				return;
			}

			$points = self::eddGetPointsGrantedForDownloadPrice( $download_id, $price_id );
			?>
			<td class="sl-limit">
				<input type="number" min="0" step="1" name="edd_variable_prices[<?php echo $price_id; ?>][cmmp_points_granted]" id="edd_variable_prices[<?php echo $price_id; ?>][cmmp_points_granted]" size="4" style="width: 70px" value="<?php echo absint( $points ); ?>" />
			</td>
			<?php
		}

		public static function eddGetPointsGrantedForDownloadPrice( $download_id = 0, $price_id = null ) {
			$prices = edd_get_variable_prices( $download_id );

			if ( isset( $prices[ $price_id ][ 'cmmp_points_granted' ] ) )
				return absint( $prices[ $price_id ][ 'cmmp_points_granted' ] );

			return false;
		}

		public static function eddPointsGrantedMetaboxSave( $post_id ) {
			global $post;

			// verify nonce
			if ( !isset( $_POST[ 'edd_mp_meta_box_nonce' ] ) || !wp_verify_nonce( $_POST[ 'edd_mp_meta_box_nonce' ], basename( __FILE__ ) ) ) {
				return $post_id;
			}

			// Check for auto save / bulk edit
			if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || isset( $_REQUEST[ 'bulk_edit' ] ) ) {
				return $post_id;
			}

			if ( isset( $_POST[ 'post_type' ] ) && 'download' != $_POST[ 'post_type' ] ) {
				return $post_id;
			}

			if ( !current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}

			if ( isset( $_POST[ 'edd_cmmp_enabled' ] ) ) {
				update_post_meta( $post_id, '_edd_cmmp_enabled', true );
			} else {
				delete_post_meta( $post_id, '_edd_cmmp_enabled' );
			}

			if ( isset( $_POST[ 'edd_cmmp_points' ] ) ) {
				update_post_meta( $post_id, '_edd_cmmp_points', (int) $_POST[ 'edd_cmmp_points' ] );
			} else {
				delete_post_meta( $post_id, '_edd_cmmp_points' );
			}
		}

		public static function eddGrantPointsPerPurchase( $download_id = 0, $payment_id = 0, $type = 'default',
													$cart_item = array(), $cart_index = 0 ) {
			$featureEnabled = CMMicropaymentPlatform::get_option( 'cm_micropayment_grant_points_per_purchase', 0 );
			if ( !$featureEnabled ) {
				return;
			}

			$granted = 0;

			$user_info = edd_get_payment_meta_user_info( $payment_id );
			if ( $type == 'bundle' ) {
				$downloads = edd_get_bundled_products( $download_id );
			} else {
				$downloads	 = array();
				$downloads[] = $download_id;
			}

			if ( !is_array( $downloads ) ) {
				return;
			}

			foreach ( $downloads as $d_id ) {
				if ( !get_post_meta( $d_id, '_edd_cmmp_enabled', true ) ) {
					continue;
				}

				$user_id	 = $user_info[ 'id' ];
				$price_id	 = isset( $cart_item[ 'item_number' ][ 'options' ][ 'price_id' ] ) ? (int) $cart_item[ 'item_number' ][ 'options' ][ 'price_id' ] : false;
				$amount		 = false;

				$hasVariablePrices = edd_has_variable_prices( $d_id );
				if ( $hasVariablePrices ) {
					$amount = self::eddGetPointsGrantedForDownloadPrice( $d_id, $price_id );
				}

				if ( $amount === false ) {
					$amount = (int) get_post_meta( $d_id, '_edd_cmmp_points', true );
				}

				if ( $user_id && $amount ) {
					$args	 = array( 'user_id' => $user_id, 'points' => $amount );
					$granted = apply_filters( 'cmmt_grant_for_purchase', $args );
				}
			}

			return $granted;
		}

	}

}

CMMPEddGrantPoints::init();
