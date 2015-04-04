<?php
/*
The MIT License (MIT)

Copyright (c) 2015 Vine Labs

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

/**
 * Communicate a lack of compatibilty between the Vine plugin for WordPress and the current site's server environment
 *
 * @since 1.0.1
 */
class Vine_CompatibilityNotice {
	/**
	 * Minimum version of PHP required to run the plugin
	 *
	 * Format: major.minor(.release)
	 *
	 * @since 1.0.1
	 *
	 * @type string
	 */
	const MIN_PHP_VERSION = '5.3';

	/**
	 * Release dates of PHP versions greater than the WordPress minimum requirement and less than the plugin minimum requirement
	 *
	 * @since 1.0.1
	 *
	 * @type array
	 */
	public static $PHP_RELEASE_DATES = array(
		'5.2.17' => '2011-01-06',
		'5.2.16' => '2010-12-16',
		'5.2.15' => '2010-12-09',
		'5.2.14' => '2010-07-22',
		'5.2.13' => '2010-02-25',
		'5.2.12' => '2009-12-17',
		'5.2.11' => '2009-09-17',
		'5.2.10' => '2009-06-18',
		'5.2.9'  => '2009-02-26',
		'5.2.8'  => '2008-12-08',
		'5.2.7'  => '2008-12-04',
		'5.2.6'  => '2008-05-01',
		'5.2.5'  => '2007-11-08',
		'5.2.4'  => '2007-08-30',
	);

	/**
	 * Admin init handler
	 *
	 * @since 1.0.1
	 *
	 * @return void
	 */
	public static function adminInit()
	{
		// no action taken for ajax request
		// extra non-formatted output could break a response format such as XML or JSON
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		// only show notice to a user of proper capability
		if ( ! Vine_CompatibilityNotice::currentUserCanManagePlugins() ) {
			return;
		}

		// display error messages in the site locale
		Vine_CompatibilityNotice::loadTranslatedText();

		// trigger an E_USER_NOTICE for the built-in error handler
		trigger_error( sprintf( __( 'The Vine plugin for WordPress requires PHP version %s or greater.', 'vine' ), Vine_CompatibilityNotice::MIN_PHP_VERSION ) );

		// deactivate the plugin
		Vine_CompatibilityNotice::deactivatePlugin();

		// display an admin notice
		add_action( 'admin_notices', array( 'Vine_CompatibilityNotice', 'adminNotice' ) );
	}

	/**
	 * Load translated text to display an error message in the site locale
	 *
	 * @since 1.0.1
	 *
	 * @uses load_plugin_textdomain()
	 * @return void
	 */
	public static function loadTranslatedText()
	{
		load_plugin_textdomain( 'vine' );
	}

	/**
	 * Get the plugin path relative to the plugins directory
	 *
	 * Used to identify the plugin in a list of installed and activated plugins
	 *
	 * @since 1.0.1
	 *
	 * @return string Plugin path. e.g. vine/vine.php
	 */
	public static function getPluginPath()
	{
		return dirname( plugin_basename( __FILE__ ) ) . '/vine.php';
	}

	/**
	 * Does the curent user have the capability to possibly fix the problem?
	 *
	 * @since 1.0.1
	 *
	 * @return bool True if the current user might be able to fix, else false
	 */
	public static function currentUserCanManagePlugins()
	{
		return current_user_can( is_plugin_active_for_network( Vine_CompatibilityNotice::getPluginPath() ) ? 'manage_network_plugins' : 'activate_plugins' );
	}

	/**
	 * Deactivate the plugin due to incompatibility
	 *
	 * @since 1.0.1
	 *
	 * @return void
	 */
	public static function deactivatePlugin()
	{
		// test for plugin management capability
		if ( ! Vine_CompatibilityNotice::currentUserCanManagePlugins() ) {
			return;
		}

		// deactivate with deactivation actions (non-silent)
		deactivate_plugins( array( Vine_CompatibilityNotice::getPluginPath() ) );

		// remove activate state to prevent a "Plugin activated" notice
		// notice located in wp-admin/plugins.php
		unset( $_GET['activate'] );
	}

	/**
	 * Display an admin notice communicating an incompatibility
	 *
	 * @since 1.0.1
	 *
	 * @return void
	 */
	public static function adminNotice()
	{
		echo '<div class="notice error is-dismissible">';
		echo '<p>' . esc_html( sprintf( __( 'The Vine plugin for WordPress requires PHP version %s or greater.', 'vine' ), Vine_CompatibilityNotice::MIN_PHP_VERSION ) ) . '</p>';

		$version = PHP_VERSION;

		$matches = array();
		// isolate major.minor(.release)
		preg_match('/^(5\.[2|3](\.[\d]{1,2})?).*/', $version, $matches );
		if ( isset( $matches[1] ) ) {
			$version = $matches[1];
		}
		unset( $matches );

		$release_date = _x( 'an unknown date', 'the day the event occurred is unknown', 'vine' );
		if ( array_key_exists( $version, Vine_CompatibilityNotice::$PHP_RELEASE_DATES ) ) {
			$release_date = date_i18n(
				get_option( 'date_format' ),
				strtotime( Vine_CompatibilityNotice::$PHP_RELEASE_DATES[ $version ] ),
				/* GMT */ true
			);
		}
		echo '<p>' . esc_html( sprintf( _x( 'This server is running PHP version %1$s released on %2$s.', 'The web server is running a version of the PHP software released on a locale-formatted date', 'vine' ), $version, esc_html( $release_date ) ) ) . '</p>';

		if ( is_plugin_inactive( Vine_CompatibilityNotice::getPluginPath() ) ) {
			echo '<p>' . __( 'Plugin <strong>deactivated</strong>.' ) . '</p>';
		}

		echo '</div>';
	}
}
