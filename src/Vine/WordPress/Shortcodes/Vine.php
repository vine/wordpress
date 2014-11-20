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

namespace Vine\WordPress\Shortcodes;

// make sure the plugin does not expose any info if this file is called directly
if ( ! function_exists( 'add_shortcode' ) ) {
	if ( ! headers_sent() ) {
		if ( function_exists( 'http_response_code' ) ) {
			http_response_code( 403 );
		} else {
			header( 'HTTP/1.1 403 Forbidden', true, 403 );
		}
	}
	exit( 'Hi there! I am a WordPress plugin requiring shortcode functions included with WordPress. I am not meant to be addressed directly.' );
}

/**
 * Display a Vine video
 *
 * @since 1.0.0
 */
class Vine {

	/**
	 * Shortcode tag to be matched
	 *
	 * @since 1.0.0
	 *
	 * @type string
	 */
	const SHORTCODE_TAG = 'vine';

	/**
	 * Find a Vine video URL
	 *
	 * @since 1.0.0
	 *
	 * @type string
	 */
	const URL_PATTERN = '#https?://vine\.co/v/([a-z0-9]+)\/?#i';

	/**
	 * Attach handlers for Vine embeds
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function init() {
		// register our shortcode and its handler
		add_shortcode( self::SHORTCODE_TAG, array( __CLASS__, 'shortcodeHandler' ) );

		// register a preferred handler for the Vine URL pattern
		// matched URLs execute before the oEmbed handlers
		wp_embed_register_handler(
			self::SHORTCODE_TAG,
			self::URL_PATTERN,
			array( __CLASS__, 'linkHandler' ),
			1
		);

		// add Vine to the list of allowed oEmbed providers
		// fallback for functions that go straight to oEmbed
		wp_oembed_add_provider(
			self::URL_PATTERN,                 // URL format to match
			'https://vine.co/oembed.{format}', // oEmbed provider URL
			true                               // regex URL format
		);
	}

	/**
	 * Handle a URL matched by an embed handler
	 *
	 * @since 1.0.0
	 *
	 * @param array $matches The regex matches from the provided regex when calling {@link wp_embed_register_handler()}.
	 * @param array $attr Embed attributes. Not used.
	 * @param string $url The original URL that was matched by the regex. Not used.
	 * @param array $rawattr The original unmodified attributes. Not used.
	 *
	 * @return string HTML markup for the Vine video or an empty string if requirements not met
	 */
	public static function linkHandler( $matches, $attr, $url, $rawattr ) {
		if ( ! ( is_array( $matches ) && isset( $matches[1] ) && $matches[1] ) ) {
			return '';
		}

		return static::shortcodeHandler( array( 'id' => $matches[1] ) );
	}

	/**
	 * Handle shortcode macro
	 *
	 * @since 1.0.0
	 *
	 * @param array $attributes shortcode attributes
	 * @param string $content shortcode content. no effect
	 *
	 * @return string HTML markup
	 */
	public static function shortcodeHandler( $attributes, $content = null ) {
		global $content_width;

		$options = shortcode_atts(
			array(
				'id' => '',
				'width' => 0
			),
			$attributes,
			self::SHORTCODE_TAG
		);

		$vine_id = trim( $options['id'] );
		if ( ! $vine_id ) {
			return '';
		}

		$oembed_options = array();
		$width = absint( $options['width'] );
		if ( $width < 100 && isset( $content_width ) ) {
			$width = absint( $content_width );
		}
		if ( $width > 100 ) {
			// reset max_width to max value supported by Vine
			// collapses cache hits
			if ( $width > 600 ) {
				$width = 600;
			}
			$oembed_options['maxwidth'] = $width;
		}

		$html = trim( static::getOEmbedMarkup( $vine_id, $oembed_options ) );
		if ( $html ) {
			$html = '<div class="vine-embed">' . $html . '</div>';
			\Vine\WordPress\JavaScriptLoaders\Embed::enqueue();
		}

		return $html;
	}

	/**
	 * Generate a unique cache key for the oEmbed response
	 *
	 * @param string $id Vine video ID
	 * @param array $options oEmbed options (future expansion)
	 *
	 * @return string oEmbed cache key
	 */
	public static function oEmbedCacheKey( $id, $options = array() ) {
		if ( ! ( is_string( $id ) && $id ) ) {
			return '';
		}
		if ( ! is_array( $options ) ) {
			$options = array();
		}

		$key_pieces = array( self::SHORTCODE_TAG, $id );
		if ( isset( $options['maxwidth'] ) ) {
			$key_pieces[] = 'w' . $options['maxwidth'];
		}

		return implode( '_', $key_pieces );
	}

	/**
	 * Request and parse oEmbed markup from Vine servers
	 *
	 * @since 1.0.0
	 *
	 * @param array $query_parameters request parameters
	 *
	 * @return string HTML markup returned by the oEmbed endpoint or cached value
	 */
	public static function getOEmbedMarkup( $id, $options = array() ) {
		if ( ! is_array( $options ) ) {
			$options = array();
		}

		$cache_key = static::oEmbedCacheKey( $id, $options );
		if ( ! $cache_key ) {
			return '';
		}

		// check for cached result
		$html = get_transient( $cache_key );
		if ( $html ) {
			return $html;
		}

		if ( ! isset( $options['omit_script'] ) ) {
			$options['omit_script'] = '1';
		}

		$ttl = 60 * 60 * 24;

		$oembed_response = \Vine\WordPress\Helpers\VineAPI::getOEmbed( $id, $options );
		if ( ! $oembed_response || ! isset( $oembed_response->type ) || $oembed_response->type !== 'video' || ! ( isset( $oembed_response->html ) && $oembed_response->html ) ) {
			// do not rerequest errors with every page request
			set_transient( $cache_key, ' ', $ttl );
			return '';
		}

		$html = $oembed_response->html;

		if ( isset( $oembed_response->cache_age ) ) {
			$ttl = absint( $oembed_response->cache_age );
		}
		set_transient( $cache_key, $html, $ttl );

		return $html;
	}
}