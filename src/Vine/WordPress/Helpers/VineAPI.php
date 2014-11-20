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

namespace Vine\WordPress\Helpers;

// make sure the plugin does not expose any info if called directly
if ( ! function_exists( 'wp_remote_get' ) ) {
	if ( ! headers_sent() ) {
		if ( function_exists( 'http_response_code' ) ) {
			http_response_code( 403 );
		} else {
			header( 'HTTP/1.1 403 Forbidden', true, 403 );
		}
	}
	exit( 'Hi there! I am a WordPress plugin requiring HTTP functions included with WordPress. I am not meant to be addressed directly.' );
}

/**
 * Request information from Vine
 *
 * @since 1.0.0
 */
class VineAPI {

	/**
	 * Vine API base string
	 *
	 * @since 1.0.0
	 *
	 * @type string
	 */
	const BASE_URI = 'https://vine.co/';

	/**
	 * Request JSON data from Vine. JSON decode the results
	 *
	 * @since 1.0.0
	 *
	 * @param string $relative_path API path without the response type. e.g. statuses/show
	 * @param array $parameters query parameters
	 *
	 * @return stdClass|null json decoded result or null if no JSON returned or issues with parameters
	 */
	public static function getJSON( $relative_path, $parameters ) {
		if ( ! is_string( $relative_path ) ) {
			return;
		}

		$relative_path = trim( ltrim( $relative_path, '/' ) );
		if ( ! $relative_path ) {
			return;
		}

		$request_uri = self::BASE_URI . $relative_path . '.json';
		if ( is_array( $parameters ) && ! empty( $parameters ) ) {
			$request_uri .= '?' . http_build_query( $parameters, '', '&' );
		}

		$response = wp_remote_get(
			$request_uri,
			array(
				'redirection' => 0,
			)
		);
		if ( is_wp_error( $response ) ) {
			return;
		}

		$response_body = wp_remote_retrieve_body( $response );
		if ( ! $response_body ) {
			return;
		}

		$json_response = json_decode( $response_body );

		// account for parse failures
		if ( $json_response ) {
			return $json_response;
		}
	}

	/**
	 * Get a Vine oEmbed response for the requested video ID and options
	 *
	 * @since 1.0.0
	 *
	 * @param string $id Vine video ID
	 * @param array $options oEmbed query parameter options {
	 *   @type string option name
	 *   @type string option value
	 * }
	 *
	 * @return stdClass|null json decoded oEmbed response or null if minimum requirements not met or no JSON returned
	 */
	public static function getOEmbed( $id, $options = array() ) {
		if ( ! is_string( $id ) && $id ) {
			return;
		}

		$parameters = array( 'id' => $id );
		if ( is_array( $options ) && ! empty( $options ) ) {
			$allowed_options = array( 'omit_script', 'maxwidth' );
			foreach ( $allowed_options as $allowed_option ) {
				if ( isset( $options[ $allowed_option ] ) ) {
					$parameters[ $allowed_option ] = (string) $options[ $allowed_option ];
				}
			}
		}

		return static::getJSON( 'oembed', $parameters );
	}
}