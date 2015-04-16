=== Plugin Name ===
Contributors: vine,niallkennedy
Tags: vine, video, embed
Requires at least: 3.7
Tested up to: 4.2
Stable tag: 1.0.1
License: MIT
License URI: http://opensource.org/licenses/MIT

Official Vine plugin for WordPress. Easily embed Vine videos by pasting a URL into your post editor. Requires PHP 5.3 or greater.

== Description ==

Embed a Vine video by pasting a URL or Vine shortcode into your post editor. Built on top of the [Vine oEmbed API](https://dev.twitter.com/web/vine/oembed), delivering the most recent [Vine embed HTML](https://dev.twitter.com/web/vine/embed) without requiring any post edits.

The plugin automatically adjusts displayed Vine embeds to the content width of your theme. Vine's embed JavaScript is asynchronously loaded through WordPress' JavaScript resource manager for improved performance and extensibility.

**Requires PHP 5.3** or greater to take advantage of namespaces and late static bindings.

Developers can fork [our code repository on GitHub](https://github.com/vine/wordpress) and submit pull requests.

== Changelog ==

= 1.0.1 =
* Display incompatibility notice if activated on a PHP 5.2 site

= 1.0.0 =
* Initial release

== Frequently Asked Questions ==

= Can I use a shortcode? =

Display a Vine embed using a WordPress shortcode:

`[vine id="Ml16lZVTTxe" width="300"]`

= How can I display a postcard-formatted embed or autoplay audio? =

The Vine oEmbed API does not currently support customization of embed templates or other options. We will add new features to the plugin as they become available.

== Screenshots ==

1. Paste a URL into the WordPress visual post editor in WordPress 4.0 or newer to see a preview of your embedded Vine.
2. Use the `vine` shortcode tag to specify custom parameters such as a fixed width.
3. A Vine embed displayed in the Twenty Fifteen theme.

== Installation ==

1. Add the Vine plugin to your WordPress installation
1. Activate the plugin through the 'Plugins' menu in WordPress
