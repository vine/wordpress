<?php
/*
Copyright (c) 2014 Vine Labs

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

namespace Vine\WordPress;

// make sure the plugin does not expose any info if this file is called directly
if ( ! function_exists( 'add_action' ) ) {
	if ( ! headers_sent() ) {
		if ( function_exists( 'http_response_code' ) ) {
			http_response_code( 403 );
		} else {
			header( 'HTTP/1.1 403 Forbidden', true, 403 );
		}
	}
	exit( 'Hi there! I am a WordPress plugin requiring functions included with WordPress. I am not meant to be addressed directly.' );
}

/**
 * Hook the WordPress plugin into the appropriate WordPress actions and filters
 *
 * @since 1.0.0
 */
class PluginLoader {
	/**
	 * Plugin version
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const VERSION = '1.0.0';

	/**
	 * Bind plugin functionality to WordPress hooks and filters
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function init() {
		load_plugin_textdomain( 'vine' );
		static::registerShortcodeHandlers();
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueueScripts' ), 5, 0 );
	}

	/**
	 * Register and/or enqueue JavaScript during the enqueue scripts action
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function enqueueScripts() {
		\Vine\WordPress\JavaScriptLoaders\Embed::register();
	}

	/**
	 * Register shortcodes handlers and callbacks
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function registerShortcodeHandlers() {
		add_action(
			'plugins_loaded',
			array( '\Vine\WordPress\Shortcodes\Vine', 'init' ),
			5,
			0
		);
	}
}