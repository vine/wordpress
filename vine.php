<?php
/**
 * @package vine
 * @version 1.0.0
 */
/*
Plugin Name: Vine
Plugin URI: https://vine.co/
Description: Official Vine plugin for WordPress. Easily embed Vine videos by pasting a URL into your post editor.
Version: 1.0.0
Author: Vine
Author URI: https://vine.co/
License: MIT
Text Domain: vine
*/

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

// make sure the plugin does not expose any info if called directly
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

// stop loading if PHP functions may fail
if ( ! version_compare( phpversion(), '5.3.0', '>=' ) ) {
	trigger_error( 'The Vine plugin for WordPress requires PHP 5.3 or newer' );
	return;
}

require_once( dirname( __FILE__ ) . '/autoload.php' );

add_action(
	'plugins_loaded',
	array( '\\Vine\\WordPress\\PluginLoader', 'init' ),
	1, // priority
	0 // expected arguments
);
