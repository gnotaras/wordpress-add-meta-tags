=== Add Meta Tags ===
Contributors: gnotaras
Donate link: http://bit.ly/HvUakt
Tags: amt, meta, metadata, seo, optimize, ranking, description, keywords, metatag, schema, opengraph, dublin core, schema.org, microdata, google, twitter cards, google plus, yahoo, bing, search engine optimization, rich snippets, semantic, structured, meta tags, product, woocommerce, edd, breadcrumbs, breadcrumb trail, multilingual, multilanguage, hreflang, json-ld
Requires at least: 3.1.0
Tested up to: 4.3
Stable tag: 2.9.3
License: Apache License v2
License URI: http://www.apache.org/licenses/LICENSE-2.0.txt

Add basic meta tags and also Opengraph, Schema.org Microdata, Twitter Cards and Dublin Core metadata to optimize your web site for better SEO.


== Description ==

*Add-Meta-Tags* (<abbr title="Add-Meta-Tags Wordpress plugin">AMT</abbr>) adds metadata to your content, including the basic *description* and *keywords* meta tags, [Opengraph](http://ogp.me "Opengraph specification"), [Schema.org](http://schema.org/ "Schema.org Specification"), [Twitter Cards](https://dev.twitter.com/docs/cards "Twitter Cards Specification") and [Dublin Core](http://dublincore.org "Dublin Core Metadata Initiative") metadata.

It is actively maintained since 2006 (historical [Add-Meta-Tags home](http://www.g-loaded.eu/2006/01/05/add-meta-tags-wordpress-plugin/ "Official historical Add-Meta-Tags Homepage")).

*Add-Meta-Tags* is one of the personal software projects of George Notaras. It is developed in his free time and released to the open source WordPress community as Free software. If you are looking for technical documentation, please visit the [Add-Meta-Tags development website's wiki](http://www.codetrax.org/projects/wp-add-meta-tags/wiki).


= Highlights of the latest releases =

- Since v2.9.2 advanced SEO title customization has been built into Add-Meta-Tags. ([learn more](http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Advanced_Title_Management))
- Since v2.8.9 experimental build-in support for the popular multilingual plugins WPML and Polylang has been implemented.
- Since v2.8.7 a basic template tag for the generation of a *semantic breadcrumb trail* for hierarchically structured content types, such as pages, is available for use in your themes ([more info](http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Semantic_Breadcrumbs)).
- Since v2.8.0 Add-Meta-Tags supports the generation of metadata for *product* and *product group* pages for the *WooCommerce* and *Easy-Digital-Downloads* e-commerce plugins ([more info](http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Metadata_Generators_Key_Notes#Metadata-for-products)).


= Goals =

The goals of the Add-Meta-Tags plugin are:

- be a free, yet high quality, metadata extension for the WordPress publishing platform.
- provide efficient, out-of-the-box search engine optimization (*SEO*).
- be customizable, yet simple and easy to use and configure, with minimal or no support.
- be as lightweight as possible.
- support advanced customization through the WordPress filter/action system (for developers and advanced users).


= Free License and Donations =

*Add-Meta-Tags* is released under the terms of the <a href="http://www.apache.org/licenses/LICENSE-2.0.html">Apache License version 2</a> and, therefore, is **Free software**.

However, a significant amount of **time** and **energy** has been put into developing this plugin, so, its production has not been free from cost. If you find this plugin useful and if it has helped your blog get indexed better and rank higher, you can show your appreciation by making a small <a href="http://bit.ly/HvUakt">donation</a>.

Donations in the following crypto currencies are also accepted and welcome. Send coins to the following addresses:

- BitCoin (BTC): `1KkgpmaBKqQVk643VRhFRkL19Bbci4Mwn9`

Thank you in advance for **donating**!


= What it does =

*Add-Meta-Tags* (referred to as *AMT* hereafter) adds metadata to your web site. This metadata contains information about the **content**, the **author**, the **publisher**, the **media files**, which have been attached to your content, and even about some of the **embedded media** (see the details about this feature in the relevant section below).

*Metadata* refers to information that describes the content in a machine-friendly way. Search engines and other online services use this metadata to better understand your content. Keep in mind that metadata itself neither automatically makes your blog rank better nor makes your content more useful. For this to happen the content is still required to meet various quality standards. However, the presence of accurate and adequate metadata gives search engines and other services the chance to make less guesses about your content, index and categorize it better and, eventually, deliver it to an audience that finds it useful. Good metadata facilitates this process and thus plays a significant role in achieving better rankings. This is what the *Add-Meta-Tags* plugin does.


= Features =

Add-Meta-Tags automatically generates metadata for your content.

**Main Features**

The main features of the plugin include:

* Generation of basic meta tags, such as the *description* and *keywords* meta tags. ([more info](http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Metadata_Generators_Key_Notes#Basic-Meta-Tags))
* Generation of [Opengraph](http://ogp.me "Opengraph specification") metadata. ([more info](http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Metadata_Generators_Key_Notes#Opengraph-Metadata))
* Generation of [Schema.org](http://schema.org/ "Schema.org Specification") metadata. ([more info](http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Metadata_Generators_Key_Notes#Schemaorg-Microdata))
* Generation of [Twitter Cards](https://dev.twitter.com/docs/cards "Twitter Cards Specification") metadata. ([more info](http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Metadata_Generators_Key_Notes#Twitter-Cards))
* Generation of [Dublin Core](http://dublincore.org "Dublin Core Metadata Initiative") metadata. ([more info](http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Metadata_Generators_Key_Notes#Dublin-Core-metadata))
* The various metadata types are generated for posts, pages, custom post types, attachment pages, category, tag, custom-taxonomy, author archives and the front page. Please note that not all generators produce metadata for all the aforementioned content types. In some cases, this happens because of limitations of the metadata specification. Moreover, the generators are constantly being improved so as to produce as complete metadata as possible.
* Supports both the default 'latest posts' front page or static pages, which are used as the front page and as the 'latest posts' page.
* Supports the generation of [metadata for embedded media](http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Metadata_Generators_Key_Notes#Metadata-for-embedded-media) (only a subset of the embeddable media in WordPress are supported)
* Supports the generation of [metadata for *product* and *product group* pages](http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Metadata_Generators_Key_Notes#Metadata-for-products) for the *WooCommerce* and *Easy-Digital-Downloads* (partial) e-commerce plugins.
* The metadata generators support social media profile links for each author.
* Publisher settings shared by all authors (currently only social media profile links).
* Custom locale setting, which is used in all generated metadata. (Should not be used in conjuction with a multilingual plugin.)
* Generation of a HTML link with the hreflang attribute according to the locale of the content. (Should not be used in conjuction with a multilingual plugin.)

**Additional Features**

Additional SEO and other features include:

* Web site description and keywords.
* Global keywords. These are keywords that are automatically added to the keywords of the content throughout the web site.
* Site-wide meta tags. It is possible to add full meta tags, such as verification meta tags or meta tags with extra robots rules, that appear throughout the web site.
* Supports a default image, such as your web site's logo, which is used as a fallback, if no other image can be determined.
* Supports the addition of the *copyright* meta tag.
* Supports the addition of the `NOODP,NOYDIR` options to the *robots* meta tag.
* Supports the addition of the `NOINDEX,FOLLOW` options to the *robots* meta tag on category, tag, author, time based archives and search results. The option to exclude the first page of each of the aforementioned archives from this rule is provided.
* Metadata review mode. When enabled, WordPress users with administrator privileges see a box (right above the post's content) containing the metadata exactly as it is added in the HTML head and body for easier examination. The box is displayed on posts, pages, attachments and custom post types.
* Supports reading data, such as custom descriptions, keywords, custom titles, etc from external fields and thus makes the migration from other plugins extremely easy.

**Customization through the User Interface**

The generation of metadata is automatic. However, customization by the user is possible directly from the [post editing screen](https://en.support.wordpress.com/posts/post-screen/). Add-Meta-Tags adds a metabox in this screen, the `Metadata` box, which offers the following metadata customization features:

* Custom description.
* Custom keywords.
* Custom content of the `<title>` element.
* News keywords. ([read more](http://support.google.com/news/publisher/bin/answer.py?hl=en&answer=68297))
* Full meta tags box, which can be used to enter full meta tags (`<meta>` and `<link>` elements are allowed by default), which are specific to the post.
* Global image override field, which accepts an image URL, which, if set, overrides the generation of the metadata for any other media file that has been attached to the post.
* Content locale override, which lets users override the locale on a per post basis. (Should not be used in conjuction with a multilingual plugin.)
* Express review, which adds a field that accepts review related information using special notation, which results in the generation of a [Review](http://schema.org/Review) schema.org entity instead of [Article](http://schema.org/Article). This feature should be used only by advanced users for testing.
* Referenced items. (Note: Using this very experimental feature is not recommended.)

**Advanced Customization**

Add-Meta-Tags supports advanced customization via the WordPress filter and action system. See the [list of available filter and action hooks](http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Filter_and_Action_Hooks) offered by the plugin.

Although this level of customization is mainly available for developers and power users, much sample code that *just works* can be found in the [forums](https://wordpress.org/support/plugin/add-meta-tags).

Moreover, the [Add-Meta-Tags Cookbook](http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Add-Meta-Tags_Cookbook), which is a collection of code snippets that perform specific commonly needed customizations, is also available. (Note: still work in progress -- needs much work).

**Template Tags**

* Some [metadata related template tags](http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Template_Tags) have been included for use in the themes:
* Semantic breadcrumb generator. Among other template tags, a basic template tag for the generation of a *semantic breadcrumb trail* for hierarchically structured content types, such as pages, has been included for use in your themes ([how to use](http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Semantic_Breadcrumbs))


= Migrate to Add-Meta-Tags =

Migrating to Add-Meta-Tags from any other plugin is extremely **easy**. In fact, if the 3rd party plugin stores data in [Custom Fields](https://codex.wordpress.org/Custom_Fields) provided by WordPress, no migration process needs to be done. Add-Meta-Tags can read data from multiple external fields by adding a small snippet of PHP code in the `functions.php` file of your theme.

So, if you decided to use Add-Meta-Tags as you main SEO plugin or if you just want to test how it would work with the data you have inserted in another plugin, please check the [Migrating to Add-Meta-Tags](http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Migrate_to_Add-Meta-Tags) section of the Add-Meta-Tags Cookbook.


= Multilingual Content Support =

Add-Meta-Tags can work well along with plugins that add multilingual support to WordPress. Moreover, all the features, which could assist authors with publishing content in multiple languages, have been implemented. However, these features are not suitable for publishing translations of the same content. For this purpose, using a multilingual plugin is highly recommended.

Please read the [technical notes regarding support for multilingual plugins](http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Multilingual_Content_Support)


= Translations =

Join a team at the [Add-Meta-Tags translations project](https://www.transifex.com/projects/p/add-meta-tags "Add-Meta-Tags translations project") and start translating right away from your browser.

Read more details about [how to translate Add-Meta-Tags](http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Translations).


= Support and Feedback =

Add-Meta-Tags is released as Free software and comes with absolutely no support.

However, the developer and the community of users share their knowledge in the [Add-Meta-Tags Community Support Forum](http://wordpress.org/support/plugin/add-meta-tags). Feel free to ask your questions there and provide as detailed feedback as possible about the potential problems you encounter. It might take a while before someone responds. To avoid duplicate effort, please do some [research on the forum](https://wordpress.org/support/topic/how-to-search-this-forum-2) before asking a question, just in case the same or similar question has already been answered.

Also, make sure you read the [FAQ](http://wordpress.org/plugins/add-meta-tags/faq/ "Add-Meta-Tags FAQ"). An answer to your question might already exist there.

Commercial grade support is not available.


**More**
 
Check out other [open source software](http://www.codetrax.org/projects) by George Notaras.


== Installation ==

Add-Meta-Tags can be easily installed through the plugin management interface from within the WordPress administration panel (*recommended*).

Alternatively, you may manually extract the compressed (zip) package in the `/wp-content/plugins/` directory.

After the plugin has been installed, activate it through the 'Plugins' menu in WordPress.

Finally, visit the plugin's administration panel at `Settings->Metadata` to read the detailed instructions about customizing the generated metatags.

As it has been mentioned, no configuration is required for the plugin to function. It will add meta tags automatically. Full customization is possible though.

Read more information about the [Add-Meta-Tags installation](http://www.g-loaded.eu/2006/01/05/add-meta-tags-wordpress-plugin/ "Official Add-Meta-Tags Homepage").


== Frequently Asked Questions ==

= Plugin X displays a warning about Add-Meta-Tags being incompatible! What should I do? =

Add-Meta-Tags is compatible with every plugin available for WordPress. It never affects the functionality of other plugins in any way.

Please read our <a title="Add-Meta-Tags Compatibility Notice" href="https://wordpress.org/support/topic/compatibility-notice-apr-09-2015">Compatibility Notice</a>. Also, check out our <a href="https://wordpress.org/support/topic/about-the-warnings-issued-by-3rd-party-plugins-for-add-meta-tags-apr-11-2015">message</a> about warnings issued by 3rd parties.

= I see duplicate meta tags in the HTML source! =

Add-Meta-Tags does not generate duplicate meta tags. Moreover, it does not check the HTML head area for duplicate meta tags. This is the responsibility of the user. Please make sure no meta tags are hardcoded into your theme (usually the `header.php` template). Also, if you use multiple SEO plugins, make sure that similar features, eg Twitter Cards metadata, is not enabled in both plugins.

= The blog title appears in the 'title' HTML element even if a custom title has been set! =

Most probably this issue is related to your theme. Please try to reproduce the same behavior using one of the default themes. If it is reproducible, please let me know about it in the forums.

= Metadata validation tools show the error [Error: Missing required field "updated"]! =

This is an issue related to <a title="Microformats Specification" href="http://microformats.org/">Microformats</a> metadata, which is hard-coded into the theme. It is not related to Schema.org microdata and also not related to Add-Meta-Tags.

= My meta tags do not show up! =

Please, check if your theme's `header.php` file contains the following required piece of code: `<?php wp_head(); ?>`. If this is missing, contact the theme author.

= My meta tags show up twice! =

The *description* and *keywords* meta tags are most probably already hardcoded into your theme's `header.php` file. Please contact the theme author.

= I paste HTML code in the 'Full Meta Tags' box, but it keeps disappearing! =

For security reasons, only `<meta>` HTML elements are allowed in this box.

= Where can I get support? =

You can get first class support from the [community of users](http://wordpress.org/support/plugin/add-meta-tags "Add-Meta-Tags Users"). Please post your questions, feature requests and general feedback in the forums.

Keep in mind that in order to get helpful answers and eventually solve any problem you encounter with the plugin, it is essential to provide as much information as possible about the problem and the configuration of the plugin. If you use a customized installation of WordPress, please make sure you provide the general details of your setup.

Also, my email can be found in the `add-meta-tags.php` file. If possible, I'll help. Please note that it may take a while to get back to you.

= I want to request a new feature! =

Please, feel free to post your request in the forums. Please be descriptive! Providing **detailed feedback** about the requested feature is the best way to contribute.

= Is there a bug tracker? =

You can find the bug tracker at the [Add-Meta-Tags Development web site](http://www.codetrax.org/projects/wp-add-meta-tags).

= There is no amount set in the donation form! How much should I donate? =

The amount of the donation is totally up to you. You can think of it like this: Are you happy with the plugin? Do you think it makes your life easier or adds value to your web site? If this is a yes and, if you feel like showing your appreciation, you could imagine buying me a cup of coffee at your favorite Cafe and <a href="http://bit.ly/HvUakt">make a donation</a> accordingly.

= I've added a low star rating in order to motivate you! Why don't you help me or not implement the feature I want? =

Time permitting, the developer generally tries to do his best with providing free support for this plugin.

But, if you try to force that support with a low star rating, it is guaranteed you are not going to get any help. Unfortunately, it's never going to work that way. So, please, do not do it. You are encouraged to provide detailed feedback in the forums and work closely with the dev in order to get problems fixed. Then, feel free to add your review.

= What should I always bare in mind before asking for support? =

This plugin is Free software. It is developed in the author's free time and is offered without support of any kind. However, the developer tries to do his best to offer support for free in these forums.

You are expected to *collaborate* and act as a *contributor*. Detailed feedback is almost always the key for the quick resolution of any issue. Give the developer time to respond. Acting and expressing demands as if you are the customer or as if the developer is your personal assistant or employee is not a good way to ask for support. In fact, there is very little tolerance for such kind of behavior. Please, do not do it. Fixing all potential issues is just a matter of good collaboration.

= Do you respect my privacy? =

Absolutely. Add-Meta-Tags does not collect any data about the way the plugin is used by its users. Moreover, Add-Meta-Tags does not make any HTTP requests and does not use (or attempt to use) any resources stored remotely. Any statistical information about how you use the plugin is beyond the developer's interest. The entire source code is [publicly available](https://bitbucket.org/gnotaras/wordpress-add-meta-tags/src) for review.


== Screenshots ==

Screenshots as of v2.4.0

1. Add-Meta-Tags administration interface ( `Options -> Metadata` ).
2. Enable Metadata meta box in the screen options of the post editing panel.
3. Metadata box in the post editing panel.
4. Contact info entries added by Add-Meta-Tags (AMT) in the user profile page.


== Upgrade Notice ==


== Changelog ==

Please check out the changelog of each release by following the links below. You can also check the [roadmap](http://www.codetrax.org/projects/wp-add-meta-tags/roadmap "Add-Meta-Tags Roadmap") regarding future releases of the plugin.

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

