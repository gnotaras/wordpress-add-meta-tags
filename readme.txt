=== Add Meta Tags ===
License: Apache License v2
Donate link: http://www.g-loaded.eu/about/donate/
Tags: meta, metadata, seo, description, keywords, metatag, opengraph, dublin core, google, yahoo, bing, meta tags
Requires at least: 3.0.0
Tested up to: 3.3.2
Stable tag: 2.0.3

Adds metadata to your content, including the basic description and keywords meta tags, Opengraph and Dublin Core metadata.

== Description ==

[Add-Meta-Tags](http://www.g-loaded.eu/2006/01/05/add-meta-tags-wordpress-plugin/ "Official Add-Meta-Tags Homepage") adds metadata to your WordPress blog.

The following list outlines how and where metadata is added to a *WordPress* blog. Please note that this list does not provide all the details you need to know about how to customize the added metatags. Its purpose is to provide a general idea of what this plugin supports.


Basic <em>description</em> and <em>keywords</em> meta tags

- Front Page
 - Automatically.
 - Customization is possible through the plugin's configuration panel.
- Single Posts
 - Automatically.
 - Customization of the *description* META tag:
  - either via setting an excerpt in the post's edit panel
  - or via custom <em>description</em> field (this overrides the custom excerpt).
 - Customization of the *keywords* META tag via custom <em>keywords</em> field only.
- Pages
 - Automatic generation of description meta tag.
 - Customization of the description meta tag is possible with a custom field.
 - Setting a keywords meta tag is possible by providing a comma-delimited list of keywords in a custom field.
- Category-based Archives
 - The description of the category, if set, is used for the description meta tag. The name of the category is always used at the keywords metatag.
- Tag-based Archives
 - The description of the tag, if set, is used for the description meta tag. The name of the tag is always used at the keywords metatag.
- META Tags on all pages
 - It is possible to set any meta tag to all blog pages.
 - Head link to copyright page.


Opengraph metadata

- Front page
 - Automatic addition of metadata.
 - Supports custom default image if URL is provided.
- Posts and Pages
 - Automatic addition of metadata.
 - Use featured image or alternatively the default image if URL has been set.


Dublin Core metadata

- Posts and Pages
 - Automatic addition of metadata.


Extra SEO features

- Add the `NOODP` option to the robots meta tag.
- Add the `NOINDEX,FOLLOW` options to the robots meta tag on category, tag, author or time based archives.


More:
 
Check out other [open source software](http://www.g-loaded.eu/software/) by the same author.


== Installation ==

1. Extract the compressed (zip) package in the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Visit the plugin's administration panel at `Options->Metadata` to read the detailed instructions about customizing the generated metatags.

As it has been mentioned, no configuration is required for the plugin to function. It will add meta tags automatically. Full customization is possible though.

Read more information about the [Add-Meta-Tags installation](http://www.g-loaded.eu/2006/01/05/add-meta-tags-wordpress-plugin/ "Official Add-Meta-Tags Homepage").


== Upgrade Notice ==

No special requirements when upgrading.


== Frequently Asked Questions ==

Troubleshooting:

= My meta tags do not show up! =

Please, check if your theme's `header.php` file contains the following required piece of code: `<?php wp_head(); ?>`. If this is missing, contact the theme author. Full WordPress functionality requires this.

= My meta tags show up twice! =

The *description* and *keywords* meta tags are most probably already hardcoded into your theme's `header.php` file. Please contact the theme author, since this is not good for your website. Meta tags should be different on every page.

= Where can I get support? =

Add-Meta-Tags is released as free software without warranties or official support. You can still get first class support from the [community of users](http://wordpress.org/tags/add-meta-tags "Add-Meta-Tags Users").

= I found a bug! =

Please, be kind enough to [file a bug report](http://www.codetrax.org/projects/wp-add-meta-tags/issues/new "File bug about Add-Meta-Tags") to our issue database. This is the only way to bring the issue to the plugin author's attention.

= I want to request a new feature! =

Please, use our [issue database](http://www.codetrax.org/projects/wp-add-meta-tags/issues "Add-Meta-Tags Issue Database") to submit your requests.


== Screenshots ==

1. Add-Meta-Tags administration interface.


== Changelog ==

Please check out the dynamic [changelog](http://www.codetrax.org/versions/2 "Add-Meta-Tags 2.0.2 ChangeLog")

- [2.0.3](http://www.codetrax.org/versions/130)
- [2.0.2](http://www.codetrax.org/versions/2)
- [1.8](http://www.codetrax.org/versions/87)
- [1.7](http://www.codetrax.org/versions/3)
- [1.6](http://www.codetrax.org/versions/1)
- [1.5](http://www.codetrax.org/versions/36)
- [1.2](http://www.codetrax.org/versions/35)
- [1.0](http://www.codetrax.org/versions/34)
- [0.9](http://www.codetrax.org/versions/33)
- [0.8](http://www.codetrax.org/versions/32)
- [0.7](http://www.codetrax.org/versions/31)
- [0.6](http://www.codetrax.org/versions/30)
- [0.5](http://www.codetrax.org/versions/29)
- [0.4](http://www.codetrax.org/versions/28)
- [0.3](http://www.codetrax.org/versions/27)
- [0.2](http://www.codetrax.org/versions/26)
- [0.1](http://www.codetrax.org/versions/25)

