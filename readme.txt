=== Add Meta Tags ===
Donate link: http://www.g-loaded.eu/about/donate/
Tags: meta, metadata, seo, description, keywords, metatag, google, yahoo, bing
Requires at least: 1.5.2
Tested up to: 3.1.1
Stable tag: 1.8

Adds XHTML META tags to your posts, static and result pages. The addition of the META tags is automatic, but every metatag can be fully customized.


== Description ==

[Add-Meta-Tags](http://www.g-loaded.eu/2006/01/05/add-meta-tags-wordpress-plugin/ "Official Add-Meta-Tags Homepage") had been initially released in early 2006, but still works for all WordPress releases from v1.5.2 up to the current stable version. The code is **actively maintained**.

This plugin adds **XHTML META** tags to your WordPress blog. The addition of the META tags is fully automatic, but it also includes all those features a **SEO** concerned publisher would need in order to have total control over those meta tags.

The following list outlines how and where *META* tags are added to a *WordPress* blog by this plugin. Please note that this list does not provide all the details you need to know about how to customize the added metatags. Its purpose is to provide a general idea of what this plugin supports. For detailed info, please visit the plugin's homepage.

- Front Page
 - Automatically.
 - Customization is possible from the plugin’s configuration panel.
- Single Posts
 - Automatically. (On WordPress v2.3 or newer, *tags* are also used in addition to the post’s categories)
 - Customization of the *description* META tag:
  - either via custom excerpt
  - or via custom field (note that this overrides the custom excerpt).
 - Customization of the *keywords* META tag via custom field only.
- Static Pages
 - No automatic generation of meta tags.
 - Customization is possible with custom fields like it can be done in posts.
- Category Archive Pages
 - The description of the category, if set, is used for the description META tag. The name of the category is always used at the keywords metatag.
- META Tags on all pages
 - It is now possible to set any other META tag, which does not require any computation, to be added to all blog pages.

More:
 
Check out other [open source software](http://www.g-loaded.eu/software/) by the same author.


== Installation ==

1. Extract the compressed (zip) package in the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Visit the plugin's administration panel at `Options->Meta Tags` to read the detailed instructions about customizing the generated metatags.

As it has been mentioned, no configuration is required for the plugin to function. It will add meta tags automatically. Full customization is possible though.

Read more information about the [Add-Meta-Tags installation](http://www.g-loaded.eu/2006/01/05/add-meta-tags-wordpress-plugin/ "Official Add-Meta-Tags Homepage").


== Frequently Asked Questions ==

Troubleshooting:

= My meta tags do not show up! =

Please, check if your theme's `header.php` file contains the following required piece of code: `<?php wp_head(); ?>`. If this is missing, contact the theme author. Full WordPress functionality requires this.

= My meta tags show up twice! =

The *description* and *keywords* meta tags are already hardcoded into your theme's `header.php` file. Please contact the theme author, since this is not good for your website. Meta tags should be different on every page. They are the page's metadata after all.

= Where can I get support? =

Add-Meta-Tags is released as free software without warranties or official support. You can still get first class support from the [community of users](http://www.codetrax.org/projects/wp-add-meta-tags/boards "Add-Meta-Tags Users").

= I found a bug! =

Please, be kind enough to [file a bug report](http://www.codetrax.org/projects/wp-add-meta-tags/issues/new "File bug about Add-Meta-Tags") to our issue database. This is the only way to bring the issue to the plugin author's attention.

= I want to request a new feature! =

Please, use our [issue database](http://www.codetrax.org/projects/wp-add-meta-tags/issues "Add-Meta-Tags Issue Database") to submit your requests.

= How can I thank you? =

This plugin is released as free software. On the other hand, it requires time and effort to develop and maintain. I would either appreciate:

- a small [donation](http://www.g-loaded.eu/about/donate/ "Donate here") as a sign of appreciation of the effort and energy put into this project, or
- a blog post that describes why you like or dislike Add-Meta-Tags.

Thanks in advance!


== Screenshots ==

No screenshots have been uploaded.


== Changelog ==

Please read the dynamic [changelog](http://www.codetrax.org/projects/wp-add-meta-tags/versions/87 "Add-Meta-Tags ChangeLog")

