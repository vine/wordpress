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

namespace Vine\WordPress\JavaScriptLoaders;

// make sure the plugin does not expose any info if this file is called directly
if ( ! function_exists( 'wp_register_script' ) ) {
	if ( ! headers_sent() ) {
		if ( function_exists( 'http_response_code' ) ) {
			http_response_code( 403 );
		} else {
			header( 'HTTP/1.1 403 Forbidden', true, 403 );
		}
	}
	exit( 'Hi there! I am a WordPress plugin requiring functions included with WordPress or BackPress. I am not meant to be addressed directly.' );
}

/**
 * Load the remotely hosted Vine embed JavaScript
 *
 * @since 1.0.0
 */
class Embed {
	/**
	 * Unique identifer for the Vine Embed JS
	 *
	 * Identifies the Vine Embed JavaScript in the WordPress queue system
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const QUEUE_HANDLE = 'vine-embed-js';

	/**
	 * Proactively resolve Vine platform FQDN asynchronously before later use.
	 *
	 * @since 1.0.0
	 *
	 * @link http://dev.chromium.org/developers/design-documents/dns-prefetching Chromium prefetch behavior
	 * @link https://developer.mozilla.org/en-US/docs/Controlling_DNS_prefetching Firefox prefetch behavior
	 *
	 * @return void
	 */
	public static function dnsPrefetchPlatform() {
		echo '<link rel="dns-prefetch" href="//platform.vine.co"';
		if ( ! current_theme_supports('html5') )
			echo ' /';
		echo '>' . "\n";
	}

	/**
	 * The absolute URI of the Vine embed JavaScript file
	 *
	 * Prefer absolute URI over scheme-relative URI
	 *
	 * @since 1.0.0
	 *
	 * @return string absolute URI for the Vine embed JavaScript file
	 */
	public static function getAbsoluteURI() {
		return 'http' . ( is_ssl() ? 's' : '' ) . '://platform.vine.co/static/scripts/embed.js';
	}

	/**
	 * Register the Vine embed JavaScript for later use
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function register() {
		wp_register_script(
			self::QUEUE_HANDLE,
			static::getAbsoluteURI(), // should be overridden during queue output by asyncScriptLoaderSrc
			array(), // no dependencies
			null, // do not add extra query parameters for cache busting
			true // in footer
		);

		// replace standard script element with async script element
		add_filter( 'script_loader_src', array( __CLASS__, 'asyncScriptLoaderSrc' ), 1, 2 );
	}

	/**
	 * Enqueue the widgets JavaScript
	 *
	 * @since 1.0.0
	 *
	 * @uses wp_enqueue_script()
	 *
	 * @return void
	 */
	public static function enqueue() {
		wp_enqueue_script( self::QUEUE_HANDLE );
	}

	/**
	 * Load Vine embed JS using async deferred JavaScript properties
	 *
	 * Called from script_loader_src filter.
	 *
	 * @since 1.0.0
	 *
	 * @param string $src script URL
	 * @param string $handle WordPress registered script handle
	 *
	 * @global WP_Scripts $wp_scripts match concatenation preferences
	 *
	 * @return string empty string if Vine embed JS, else give back the src variable
	 */
	public static function asyncScriptLoaderSrc( $src, $handle ) {
		global $wp_scripts;

		if ( $handle !== self::QUEUE_HANDLE ) {
			return $src;
		}

		// type = text/javascript to match default WP_Scripts output
		// async property to unlock page load, preload scanner discoverable in modern browsers
		// defer property for IE 9 and older
		$html = '<script type="text/javascript" id="' . esc_attr( self::QUEUE_HANDLE ) . '" async defer src="' . esc_url( static::getAbsoluteURI(), array( 'http', 'https' ) ) . '"></script>' . "\n";

		if ( isset( $wp_scripts ) && $wp_scripts->do_concat ) {
			$wp_scripts->print_html .= $html;
		} else {
			echo $html;
		}

		// empty out the src response to avoid extra <script>
		return '';
	}
}