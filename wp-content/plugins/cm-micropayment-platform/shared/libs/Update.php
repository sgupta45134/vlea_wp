<?php
class CMMPP_Update{
    public static function run() {
        $readme = file_get_contents(CMMP_PLUGIN_DIR . '/readme.txt');
        preg_match('/Stable tag\: ([0-9\.]+)/i', $readme, $match);
        if( isset($match[1]) )
        {
            $currentVersion = $match[1];
        }
		self::update_for_1_5_0($currentVersion);
		self::update_for_1_8_10($currentVersion);
	}

	public static function update_for_1_5_0($currentVersion = null){
		global $wpdb;
        $oldVersion = '1.5.0';

        if(!empty($currentVersion) && version_compare($oldVersion, $currentVersion, '<') ) {
            $tableName = $wpdb->prefix . "cm_micropayments_defined_points_cost";
            $sql = sprintf("ALTER TABLE %s MODIFY cost decimal(10,3)", $tableName);
            $wpdb->query($sql);
        }
	}

	public static function update_for_1_8_10($currentVersion = null){
        $oldVersion = '1.8.10';

        if(!empty($currentVersion) && version_compare($oldVersion, $currentVersion, '<') ) {
			$option_name = 'cmmp_label_format_price_in_checkout';
            $option_value = get_option($option_name, '%s %s per %s %s');
			if ( !empty($option_value) ) {
				$option_value = preg_replace('/%\S+/','%s', $option_value);
			}
			update_option($option_name, $option_value);
        }
	}
}
