=== Add Meta Tags ===
Contributors: gnotaras
Donate link: http://bit.ly/HvUakt
Tags: meta tags, seo, opengraph, dublin core, schema.org, json-ld, twitter cards, description, keywords, woocommerce, breadcrumbs, hreflang, metadata, optimize, ranking, metatag, schema, google, google plus, yahoo, bing, search engine optimization, rich snippets, semantic, structured, product, edd, breadcrumb trail, multilingual, multilanguage, microdata
Requires at least: 3.1.0
Tested up to: 4.4
Stable tag: 2.9.5
License: Apache License v2
License URI: http://www.apache.org/licenses/LICENSE-2.0.txt

Add basic meta tags and also Opengraph, Schema.org Microdata, Twitter Cards and Dublin Core metadata to optimize your web site for better SEO.

== Description ==

*Add-Meta-Tags* (<abbr title="Add-Meta-Tags Wordpress plugin">AMT</abbr>) adds metadata to your *content* and *WooCommerce* products, including the basic *description* and *keywords* meta tags, [Opengraph](http://ogp.me "Opengraph specification"), [Schema.org](http://schema.org/ "Schema.org Specification"), [Twitter Cards](https://dev.twitter.com/docs/cards "Twitter Cards Specification") and [Dublin Core](http://dublincore.org "Dublin Core Metadata Initiative") metadata.

It also supports advanced *SEO title* customization letting you take control of the title generation on every part of the web site. Moreover, a basic *breadcrumb trail* generator is provided for use with hierarchical post types. Last, but not least, it lets you customize the *locale* on a per post basis generating a proper `hreflang` for a signle language and, also, is out-of-the-box compatible with WPML and Polylang multilingual plugins (through the WPML language configuration file that ships with the plugin).

It is actively maintained since 2006 (historical [Add-Meta-Tags home](http://www.g-loaded.eu/2006/01/05/add-meta-tags-wordpress-plugin/ "Official historical Add-Meta-Tags Homepage")).

*Add-Meta-Tags* is one of the personal software projects of George Notaras. It is developed in his free time and released to the open source WordPress community as Free software.

= IMPORTANT NOTICE =

These forums and the reviews are no longer monitored by the developer. Please, check the support section below for your free support options.

= Official Project Homepage =

The Add-Meta-Tags project information and documentation has been moved to the [Add-Meta-Tags Development Web Site](http://www.codetrax.org/projects/wp-add-meta-tags/wiki).

The development web site contains:

- all the details about the project goals,
- the complete [feature set](http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Features) of the plugin,
- release notes,
- technical documentation,
- information about how to contribute translations and source code,
- the [Add-Meta-Tags Cookbook](http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Add-Meta-Tags_Cookbook) (the Documentation) containing:
 - customization examples with sample source code,
 - instructions about how to migrate from other SEO plugins,
 - details about the multilingual support,
 - information about how to customize the SEO titles,
 - how to use the basic semantic breadcrumb trail generator,
 - how to enhance the product metadata,
 - details about even more advanced topics!


= Support =

Add-Meta-Tags is released without support of any kind.

However, you can still get support free of charge at the following support channels:

- The [issue tracker](https://github.com/gnotaras/wordpress-add-meta-tags/issues) at Github. This tracker is monitored by the developer. Free registration is required in order to post. This is currently the **recommended support channel**.
- more to come...

NOTICE 1: Support requests via email are now silently ignored. Please post your support requests in the aforementioned support channels.

NOTICE 2: The developer no longer monitors, participates or provides support through the wordpress org forum or review system. You can still get free support there from other users of the plugin.


= Legal Notice =

Add-Meta-Tags is Copyright (c) 2006-2015 George Notaras. All rights reserved.

Permission is granted to use this software under the terms of the Apache
License version 2 and the NOTICE file that ships with the distribution package.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
CONTRIBUTORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS WITH
THE SOFTWARE.

WordPress is a registered trademark of Automattic Inc.


== Installation ==

Add-Meta-Tags can be easily installed through the plugin management interface from within the WordPress administration panel.


== Upgrade Notice ==

No special requirements when upgrading.


== Frequently Asked Questions ==

Please read the [Add-Meta-Tags FAQ](http://www.codetrax.org/projects/wp-add-meta-tags/wiki/FAQ) at the development web site.


== Screenshots ==

No screenshots are available at this time.


== Changelog ==

= IMPORTANT NOTICE ABOUT POLICY CHANGE =

For some technical, but mostly for non-technical reasons, I no longer monitor, participate or provide support in the wordpress org forums. Please post your support requests, issue reports and feature requests in the [issue tracker](https://github.com/gnotaras/wordpress-add-meta-tags/issues) at Github.

= Changelog Entries =

Please check out the changelog of each release by following the links below. You can also check the [roadmap](http://www.codetrax.org/projects/wp-add-meta-tags/roadmap "Add-Meta-Tags Roadmap") regarding future releases of the plugin.

- [2.9.5](http://www.codetrax.org/versions/302)
 - The *Advanced Titles* feature now properly supports the management of titles of custom post type archives.
 - Metadata is now generated for archives of custom post types.
 - Reverted back to the old check of the version of the plugin settings which properly upgrades the settings whever necessary without having to access the admin interface. (props to Eddie McHam for providing useful feedback about this issue)
 - Updated the [theme requirements](http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Requirements#Theme-Requirements).
 - Added extra filter that removes shortcodes which have not been properly registered using the WordPress Shortcode API.
 - Some minor modifications which should result in slightly better performance in some cases.
- [2.9.4](http://www.codetrax.org/versions/301)
 - Updated translations. The plugin now ships with a complete Greek translation. Big thanks to Michael Kotsarinis for contributing to the project!
- [2.9.3](http://www.codetrax.org/versions/300)
 - The full meta tags field is now set as translatable in the wpml-config.xml file. (props to Werner Grunberger for feedback)
 - Re-added the %title% tag expansion functionality in the custom title. (props to ndrwpvlv for feedback)
- [2.9.2](http://www.codetrax.org/versions/299)
 - Advanced SEO title management and customization has been built into Add-Meta-Tags. Needs to be enabled in the settings. Read [more info](http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Advanced_Title_Management) about how to customize the titles. This feature is currently marked as experimental. Your feedback is welcome.
 - Option to force the use of the content's custom title, if one has been set in the post editing screen, in the titles within the metadata. By default the custom title is used only for the 'title' HTML element. (Props to fatherb, bolt24, vtinath, Craig Damon and others)
 - Fixed missing schema.org properties of video schema.org objects. (Props to Dragos for reporting the issue and for useful feedback)
 - Fixed several translatable strings. (Props to Burak Yavuz for valuable feedback.)
 - Dublin Core generator follows media item limits. (Props to Eduardo Molon for feedback.)
 - Internal media limit (configurable via filter) increased from 10 to 16. (Props to Eduardo Molon for feedback.)
 - Minor improvements of the schema.org metadata generators.
- [2.9.1](http://www.codetrax.org/versions/298)
 - The Twitter Cards, Opengraph and Schema.org microdata and JSON+LD generators for WooCommerce products have been greatly improved and are ready for general testing.
 - Updated the Turkish translation. (props to BouRock for tirelessly maintaining the Turkish translation)
 - Fixed issues of the JSON-LD generator with product and product group metadata. (props to Justin Flores for valuable feedback)
 - Review mode box no longer shows message about microdata when the JSON+LD generator is enabled. (props to Eduardo Molon for providing feedback)
 - Various other minor fixes and enhancements.
- [2.9.0](http://www.codetrax.org/versions/297)
 - **IMPORTANT NOTICE 1**: All help text messages and examples of the settings page have been moved to the integrated WordPress help system. This has been done in order to make the settings page easier to navigate. While at the settings page, press the `HELP` button on the top right corner and browse through the various sections in order to get detailed information about the available settings.
 - **IMPORTANT NOTICE 2**: It is no longer possible to enter the URLs of the Publisher's social media profiles in the WordPress user profile pages. Instead, publisher information should be entered in the relevant fields of the **Publisher Settings** section of the settings page.
 - The administration interface has been reworked.
 - Removed publisher related settings from user profile pages.
 - Improved the algorithm that collects the embedded media so that it excludes media which are just linked from the content and not embedded into the content.
 - Added option that limits the generated media metadata to one media file of each media type (image, video, audio). See `Media Limit` in the settings page. (thanks all for providing feedback about this feature - too many to list here)
 - Added support for pre-defined full meta tag sets, which can be used in the 'Full Meta Tags' box ([more info](http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Plugin_Functionality_Customization#Create-Pre-Defined-Full-Meta-Tag-Sets)). (props to aferguson for ideas and feedback)
 - Re-invented the 'Express Review' feature. Admittedly, creating a review has become a little more complex, but the new way of creating reviews is as simple as it can possibly get without sacrificing flexibility. If you have an idea about how to make it even simpler, please let me know.
 - This release contains an alpha version of JSON-LD schema.org metadata generator. By enabling it in the settings, schema.org metadata is added in the head section of the web page as an `application/ld+json` script, instead of embedded microdata in the content. This feature currently exists only for testing. Your feedbackis welcome.

Changelog information for older releases can be found in the ChangeLog file or at the [roadmap](http://www.codetrax.org/projects/wp-add-meta-tags/roadmap "Add-Meta-Tags Roadmap") on the [Add-Meta-Tags development web site](http://www.codetrax.org/projects/wp-add-meta-tags).

