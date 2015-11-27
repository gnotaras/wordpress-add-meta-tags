=== Add Meta Tags ===
Contributors: gnotaras
Donate link: http://bit.ly/HvUakt
Tags: meta tags, seo, opengraph, dublin core, schema.org, json-ld, twitter cards, description, keywords, woocommerce, breadcrumbs, hreflang, metadata, optimize, ranking, metatag, schema, google, google plus, yahoo, bing, search engine optimization, rich snippets, semantic, structured, product, edd, breadcrumb trail, multilingual, multilanguage, microdata
Requires at least: 3.1.0
Tested up to: 4.4
Stable tag: 2.9.7
License: Apache License v2
License URI: http://www.apache.org/licenses/LICENSE-2.0.txt

A Free yet feature rich metadata plugin that can optimize your web site for more efficient indexing and easier sharing of your content.

== Description ==

_Add-Meta-Tags_ is a Free metadata plugin for the _WordPress Publishing Platform_ that can optimize your web site for more efficient indexing and easier sharing of your content. It achieves this by generating machine friendly information about your content, called **metadata**, according to widely used standard specifications. More specifically, _Add-Meta-Tags_ can generate the _description_ and _keywords_ meta tags, _Opengraph_, _Schema.org_ microdata and JSON+LD data, _Twitter Cards_ and _Dublin Core_ metadata for your _WordPress_ content and archives, your _WooCommerce_ products and product groups, _BuddyPress_ profiles, and more.

It also supports advanced _title customization_ letting you take control of the title generation on every part of the web site. Moreover, a basic _breadcrumb trail_ generator is provided for use with hierarchical post types. Last, but not least, it lets you customize the _locale_ on a per post basis generating a proper `hreflang` link for a signle language and, also, is out-of-the-box compatible with _WPML_ and _Polylang_ multilingual plugins (through the WPML language configuration file that ships with the plugin).

Add-Meta-Tags is actively maintained since 2006. Please visit the [Add-Meta-Tags historical homepage](http://www.g-loaded.eu/2006/01/05/add-meta-tags-wordpress-plugin/).

_Add-Meta-Tags_ is one of the personal software projects of George Notaras. It is developed in his free time and released to the open source WordPress community as Free software.


= Official Project Homepage =

More information and documentation about the complete [feature set](http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Features), together with technical information regarding migration and customization, can be found at the [Add-Meta-Tags Development Web Site](http://www.codetrax.org/projects/wp-add-meta-tags/wiki).


= Support =

Add-Meta-Tags is released without support of any kind.

However, you can still get support free of charge at the [issue tracker](https://github.com/gnotaras/wordpress-add-meta-tags/issues) at Github. Feel free to post your questions, suggestions, bug reports, feature requests about the _Add-Meta-Tags_ project (free registration is required in order to post).

The issue tracker at Github is the **recommended support channel**. The developer no longer monitors, participates or provides support through the wordpress org forum or review system.


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

- [2.9.7](http://www.codetrax.org/versions/304)
 - IMPORTANT NOTICE: The file/directory layout might change in upcoming releases.
 - Added support for the generation of metadata for _BuddyPress Profiles_. Please consult the _Extended Metadata_ section in the integrated help for more information about this new feature. This feature should be considered work in progress. Title customization is not supported yet. Many thanks to HansRuedi Keller for ideas, valuable feedback and for helping me to raise my "BuddyPress IQ" at _lerngruppen.net_.
 - Added support for configurable source of local author profiles. Add-Meta-Tags, by convention, due to the lack of public profile pages in WordPress, treats the first page of the author archive as the author's profile page. This is now configurable. Please check the _Author Settings_ section in the integrated help for more information before changing this as it affects some parts of the metadata.
 - Added the `amt_local_author_profile_url()` template tag which generates a URL to the local author profile according to the relevant selection in the Add-Meta-Tags settings.
 - Added the base mechanism for bbPress support. This feature is at a very early stage of developement.
 - Various improvements of the integrated help.
 - Minor improvements of the administration interface.
 - Fixes of various minor issues.
- [2.9.6](http://www.codetrax.org/versions/303)
 - This release implements the `amt` community command of [wp-cli](http://wp-cli.org/). Downloading `wp-cli` is required. Learn more about the [Add-Meta-Tags command line interface](http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Command_Line_Interface). This feature should be considered experimental and is currently meant to be used in testing environments, even if it seems to work fine.
 - Decoupled the *WebSite* and *Organisation* Schema.org entities on the homepage, as it is unclear whether the various services interpret them correctly when nested. Furthermore, more research about how these two entities could be nested is required. The default homepage is the only page on which Add-Meta-Tags prints decoupled schema.org objects.
 - Fixed an issue of the excerpt generator, which in some cases could reinsert shortcodes (eg Visual Composer shortcodes) and HTML tags in the excerpt. Kudos to Ceslav Przywara for spotting it and for providing useful and detailed feedback.
 - Reverted back to one-argument version of the `amt_custom_title_tag()` filtering function in order to maintain backwards compatibility. Props to Cat for reporting the issue.
 - Removed the `headline` itemprop from *Product* schema.org entities.
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

