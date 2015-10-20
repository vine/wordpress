# Vine plugin for WordPress

This WordPress plugin has been deprecated. The [Twitter plugin for WordPress](https://github.com/twitter/wordpress) includes support for Vine embeds with helper JavaScript; webmasters can choose to enable or disable specific product features by acting on a filter.

Vine embeds have been available as an [oEmbed provider default](https://codex.wordpress.org/Embeds) since [WordPress version 4.1](https://codex.wordpress.org/Version_4.1), released in December 2014.

The Vine plugin for WordPress replaces a Vine video URL or Vine macro with Vine embed HTML retrieved from [Vine's oEmbed API endpoint](https://dev.twitter.com/web/vine/oembed).

* `https://vine.co/v/Ml16lZVTTxe`
* `[vine id="Ml16lZVTTxe"]`

The Vine plugin adjusts Vine embed HTML to match the [$content_width](http://codex.wordpress.org/Content_Width) defined by your theme.

Vine JavaScript loads asynchronously using the `vine-embed-js` registered script handle: an improvement over the standard oEmbed response combining HTML markup and script loading.

see [readme.txt](readme.txt) for readme documentation compatible with the [WordPress plugin readme standard](https://wordpress.org/plugins/about/readme.txt). Release versions of this plugin are distributed through [the Vine listing in the WordPress plugin repository](https://wordpress.org/plugins/vine/).

## Authors
* Niall Kennedy <https://twitter.com/niall>

A full list of [contributors](https://github.com/vine/wordpress/graphs/contributors) can be found on GitHub.

## License
Copyright 2014 Vine Labs, Inc.

Licensed under the MIT License: http://opensource.org/licenses/MIT
