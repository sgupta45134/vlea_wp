<?php

/*
 * Version: 2.0.8
 */

if (!defined('ABSPATH'))
  exit;

if (!class_exists('QLWDD_Updater')) {

  class QLWDD_Updater
  {

    protected static $instance;
    public $plugin;
    public $activation;

    function validate_success($response)
    {

      if (isset($response->success)) {
        return true;
      }

      return false;
    }

    function validate_response($json = null, $request = null)
    {

      if (200 !== wp_remote_retrieve_response_code($json) || !$response = json_decode(wp_remote_retrieve_body($json))) {

        if (is_wp_error($json)) {
          $response = array(
            'error' => 1,
            'message' => $json->get_error_message()
          );
        } else {
          $response = array(
            'error' => 1,
            'message' => __('Unknow error occurred, please try again')
          );
        }
      }

      do_action("qlwdd_updater_{$request}", (object) $response);

      return $response;
    }

    function validate_request($args = array(), $required = array())
    {

      if (count($missing = array_diff_key(array_flip($required), array_filter($args)))) {

        $message = array();

        foreach ($missing as $key => $value) {
          $missing_keys[$key] = sprintf(__('The %s parameter is undefined'), $key);
        }

        return $message;
      }

      return false;
    }

    function remote_get($args = array(), $required = array())
    {

      if (!$response = self::validate_request($args, $required)) {

        $url = add_query_arg($args, trailingslashit($this->plugin->api_url));

        $response = self::validate_response(wp_remote_get($url, array('timeout' => 29)), $args['request']);
      }

      return $response;
    }

    function request_activation($license_key = null, $license_email = null, $license_market = null)
    {

      $args = array(
        'request' => 'activation',
        'license_market' => $license_market,
        'license_key' => $license_key,
        'license_email' => $license_email,
        'activation_site' => home_url(),
      );

      $args = wp_parse_args($args, (array) $this->plugin);

      return self::remote_get($args, array(
        'request',
        'license_key',
        'activation_site'
      ));
    }

    function request_deactivation($license_key = null, $activation_instance = null)
    {

      $args = array(
        'request' => 'deactivation',
        'license_key' => $license_key,
        'activation_instance' => $activation_instance,
      );

      return self::remote_get($args, array(
        'request',
        'activation_instance'
      ));
    }

    function request_downloads($license_key = null, $activation_instance = null)
    {

      $args = array(
        'request' => 'downloads',
        'license_key' => $license_key,
        'activation_instance' => $activation_instance,
      );

      return self::remote_get($args, array(
        'request',
        'license_key',
        'activation_instance'
      ));
    }

    function request_key($product_key = null, $secret_key = null)
    {

      $args = array(
        'request' => 'key',
        'product_key' => $product_key,
        'secret_key' => $secret_key,
      );

      return self::remote_get($args, array(
        'request',
        'product_key',
        'secret_key'
      ));
    }

    function request_reset($license_key = null, $activation_instance = null)
    {

      $args = array(
        'request' => 'reset',
        'license_key' => $license_key,
        'activation_instance' => $activation_instance,
      );

      return self::remote_get($args, array(
        'request',
        'license_key',
        'activation_instance'
      ));
    }

    function request_status($license_key = null, $activation_instance = null)
    {

      $args = array(
        'request' => 'status',
        'license_key' => $license_key,
        'activation_instance' => $activation_instance,
      );

      return self::remote_get($args, array(
        'request',
        'license_key',
        'activation_instance'
      ));
    }

    function request_version(
      $product_key = null,
      $license_key = null,
      $activation_instance = null,
      $license_market = null
    ) {

      $args = array(
        'request' => 'version',
        'product_key' => $product_key,
        'license_key' => $license_key,
        'license_market' => $license_market,
        'activation_instance' => $activation_instance,
      );

      return self::remote_get($args, array(
        'request',
        'product_key',
      ));
    }

    function plugin_screenshots($screenshots = array())
    {
      ob_start();
?>
      <ol>
        <?php foreach ($screenshots as $key => $image) : ?>
          <li><a href="<?php echo esc_url($image->src); ?>"><img src="<?php echo esc_url($image->src); ?>" alt="<?php echo esc_html($image->caption); ?>"></a></li>
        <?php endforeach; ?>
      </ol>
<?php
      return ob_get_clean();
    }

    function plugin_version()
    {

      $plugin_data = get_plugin_data($this->plugin->plugin_file, false);

      return $plugin_data['Version'];
    }

    function plugin_notification($plugin_data, $response)
    {

      if (empty($response->package)) {
        printf('</p></div><span class="notice notice-error notice-alt" style="display:block; padding: 10px;"><b>%s</b> %s</span>', __('Activate your license.'), sprintf(__('Please visit %s to activate the license or %s in our website.'), sprintf('<a href="%s" target="_blank">%s</a>', esc_url($this->plugin->license_url), __('settings')), sprintf('<a href="%s" target="_blank">%s</a>', esc_url($this->plugin->plugin_url), __('purchase'))));
      }
    }

    function plugin_information($return, $action, $args)
    {

      if ('plugin_information' != $action) {
        return $return;
      }

      if ($args->slug != $this->plugin->plugin_slug) {
        return $return;
      }

      if ($plugin = get_site_transient('update_plugins')->no_update[$this->plugin->plugin_base]) {

        if (isset($plugin->sections['screenshots'])) {
          $plugin->sections['screenshots'] = $this->plugin_screenshots($plugin->sections['screenshots']);
        }

        return $plugin;
      }

      return $return;
    }

    function plugin_update($transient)
    {

      if (empty($transient->checked)) {
        return $transient;
      }

      if ($this->validate_success(
        $response = $this->request_version(
          $this->plugin->product_key,
          $this->activation->license_key,
          $this->activation->activation_instance,
          $this->plugin->license_market
        )
      )) {

        $plugin = new stdClass();
        $plugin->id = $this->plugin->plugin_slug;
        $plugin->slug = $this->plugin->plugin_slug;
        $plugin->plugin = $this->plugin->plugin_base;
        $plugin->new_version = $response->version;
        $plugin->url = $response->homepage;
        $plugin->tested = $response->tested;
        $plugin->upgrade_notice = $response->upgrade_notice;
        $plugin->icons = array('default' => $response->icon);

        // Fields for plugin info
        $plugin->version = $response->version;
        $plugin->homepage = $response->homepage;
        $plugin->name = $response->name;
        $plugin->author = $response->author;
        $plugin->requires = $response->requires;
        $plugin->rating = 100;
        $plugin->num_ratings = 5;
        $plugin->active_installs = 10000;
        $plugin->last_updated = $response->last_updated;
        $plugin->added = $response->added;
        $plugin->sections = array(
          'description' => preg_replace('/<h2(.*?)<\/h2>/si', '<h3"$1</h3>', $response->description),
          'changelog' => wpautop($response->changelog),
          'screenshots' => $response->screenshots,
        );
        $plugin->donate_link = $this->plugin->plugin_url;
        $plugin->banners = array(
          'low' => $response->banner_low,
          'high' => $response->banner_high,
        );
        $plugin->package = null;

        if (version_compare($response->version, $this->plugin_version(), '>')) {

          if (current_user_can('update_plugins') && filter_var($response->download_link, FILTER_VALIDATE_URL) !== false) {
            $plugin->package = $plugin->download_link = $response->download_link;
          }

          $transient->response[$this->plugin->plugin_base] = $plugin;
        }

        $transient->no_update[$this->plugin->plugin_base] = $plugin;
      }

      return $transient;
    }

    function request_user_agent($request, $ua)
    {

      $php_version = preg_replace('@^(\d\.\d+).*@', '\1', phpversion());

      return sprintf('%s;QLWDD|%s|%s|%s;', $ua, $request->request, $this->plugin_version(), $php_version);
    }

    function plugin_user_agent($args, $url)
    {

      if (strpos($url, $this->plugin->api_url) !== false) {

        parse_str(parse_url($url, PHP_URL_QUERY), $request);

        $args['user-agent'] = $this->request_user_agent((object) $request, $args['user-agent']);
      }

      return $args;
    }

    function do_activation($response)
    {

      update_option(sanitize_key("{$this->plugin->plugin_slug}_activation"), $response);
      wp_clean_plugins_cache();
    }

    function do_deactivation($response)
    {
      if (isset($response->success)) {
        delete_option(sanitize_key("{$this->plugin->plugin_slug}_activation"));
      }
    }

    function plugin_settings($args = array())
    {

      $defaults = array(
        'api_url' => null,
        'plugin_url' => null,
        'plugin_file' => null,
        'license_market' => null,
        'license_key' => null,
        'license_email' => null,
        'license_url' => null,
        'product_key' => null,
      );

      if (is_file($args['plugin_file'])) {
        $args['plugin_slug'] = basename($args['plugin_file'], '.php');
        $args['plugin_base'] = plugin_basename($args['plugin_file']);
      }

      $this->plugin = (object) wp_parse_args($args, $defaults);

      return $this->plugin;
    }

    function get_activation()
    {
      return get_option(sanitize_key("{$this->plugin->plugin_slug}_activation"), array());
    }

    function plugin_activation()
    {

      $defaults = array(
        'license_key' => null,
        'license_market' => null,
        'license_email' => null,
        'activation_site' => null,
        'activation_instance' => null,
      );

      $this->activation = (object) wp_parse_args((array) $this->get_activation(), $defaults);

      return $this->activation;
    }

    function init()
    {
      add_filter('plugins_api', array($this, 'plugin_information'), 10, 3);
      add_filter('pre_set_site_transient_update_plugins', array($this, 'plugin_update'));
      add_filter('http_request_args', array($this, 'plugin_user_agent'), 10, 2);
      add_action('in_plugin_update_message-' . $this->plugin->plugin_base, array($this, 'plugin_notification'), 10, 2);
      add_action('qlwdd_updater_activation', array($this, 'do_activation'));
      add_action('qlwdd_updater_deactivation', array($this, 'do_deactivation'));
      add_action('qlwdd_updater_reset', array($this, 'do_deactivation'));
    }

    public static function instance($args = array())
    {
      self::$instance = new self();
      self::$instance->plugin_settings($args);
      self::$instance->plugin_activation();
      self::$instance->init();
      return self::$instance;
    }
  }

  if (!function_exists('qlwdd_updater')) {

    function qlwdd_updater($args = array())
    {
      return QLWDD_Updater::instance($args);
    }
  }
}