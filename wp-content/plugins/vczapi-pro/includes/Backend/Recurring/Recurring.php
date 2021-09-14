<?php

namespace Codemanas\ZoomPro\Backend\Recurring;

use Codemanas\ZoomPro\Backend\MetaHandler;
use Codemanas\ZoomPro\Core\API;
use Codemanas\ZoomPro\Core\Fields;

/**
 * Class RecurringMetaBox
 *
 * Handler for meta box in zoom meeting section
 *
 * @author  Deepen Bajracharya, CodeManas, 2020. All Rights reserved.
 * @since   1.0.0
 * @package Codemanas\ZoomPro\Admin
 */
class Recurring {

	/**
	 * @var null
	 */
	private $zoom_api = null;

	/**
	 * Build the instance
	 */
	public function __construct() {
		add_action( 'vczapi_admin_before_additional_fields', array( $this, 'rendor_recurring_box' ), 20 );
		add_action( 'vczapi_meeting_details_admin', array( $this, 'meeting_details_side' ) );

		$this->zoom_api = API::get_instance();
	}

	/**
	 * Render Meta box html fields
	 */
	public function rendor_recurring_box() {
		global $post;

		$details              = Fields::get_meta( $post->ID, 'meeting_details' );
		$vczapi_field_details = get_post_meta( $post->ID, '_meeting_fields', true );
		$details              = ! empty( $details ) ? $details : array();
		$vczapi_field_details = ! empty( $vczapi_field_details ) ? $vczapi_field_details : array();
		if ( empty( $details['monthly_occurence_type'] ) ) {
			$details['monthly_occurence_type'] = '1';
		}

		if ( empty( $details['end_type'] ) ) {
			$details['end_type'] = 'by_occurrence';
		}

		if ( empty( $details['weekly_occurence'] ) ) {
			$details['weekly_occurence'] = array( '1' );
		}

		if ( empty( $vczapi_field_details['meeting_type'] ) ) {
			$vczapi_field_details['meeting_type'] = 1;
		}

		include_once VZAPI_ZOOM_PRO_ADDON_DIR_PATH . 'includes/Backend/Recurring/tpl-recurrings-meta-fields.php';
	}

	/**
	 * Add Extra information to side box in meeting details section
	 *
	 * @param $meeting
	 */
	public function meeting_details_side( $meeting ) {
		if ( ! empty( $meeting ) && ! empty( $meeting->pmi ) ) {
			?>
            <p><strong>PMI:</strong> <?php echo $meeting->pmi; ?></p>
			<?php
		}
	}
}