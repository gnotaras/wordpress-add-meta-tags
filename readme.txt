=== Add Meta Tags ===
License: Apache License v2
Donate link: http://www.g-loaded.eu/about/donate/
Tags: meta, metadata, seo, description, keywords, metatag, opengraph, dublin core, google, yahoo, bing, meta tags
Requires at least: 3.0.0
Tested up to: 3.5.1
Stable tag: 2.2.0

Adds metadata to your content, including the basic description and keywords meta tags, Opengraph and Dublin Core metadata.

== Description ==

*Add-Meta-Tags* adds metadata to your content, including the basic description and keywords meta tags, Opengraph and Dublin Core metadata. It is actively maintained since 2006 (historical [Add-Meta-Tags home](http://www.g-loaded.eu/2006/01/05/add-meta-tags-wordpress-plugin/ "Official Add-Meta-Tags Homepage")).

*Metadata* refers to information that describes the content in a machine-friendly way. Search engines and other online services use this metadata to better understand your content. Keep in mind that metadata itself does not automatically make your blog rank better. For this to happen the content is still required to meet various quality standards. However, the presence of accurate and adequate metadata gives search engines and other services the chance to make less guesses about your content, index and categorize it better and, eventually, deliver it to an audience that finds it useful.  Good metadata facilitates this process and thus plays a significant role in achieving better rankings. This is what the *Add-Meta-Tags* plugin does.

The following list outlines how and where metadata is added to a *WordPress* blog.

**Basic meta tags**

The <em>description</em> and <em>keywords</em> meta tags are added:

- Front Page
 - Automatically.
 - Customization is possible through the plugin's configuration panel.
- Single Posts
 - Automatically.
 - Customization of the *description* META tag:
  - either by setting an excerpt in the post's edit panel
  - or by setting a custom description in the *Metadata* meta box in the post editing panel. (overrides the custom excerpt).
 - Customization of the *keywords* META tag is possible by setting a custom list of keywords in the *Metadata* meta box in the post editing panel.
- Pages
 - Automatically (description only - no automatic keywords).
 - Customization of the *description* meta tag is possible by setting a custom description in the *Metadata* meta box in the page editing panel.
 - Setting a *keywords* meta tag is possible by setting a custom list of keywords in the *Metadata* meta box in the post editing panel.
- Attachment Pages
 - A 'description' metatag is automatically generated from the caption or, if a caption has not been set, from the description of the attachment.
- Custom Post Types
 - Automatically (description only - no automatic keywords) from the first paragraph of the content.
 - Custom description and keywords by setting a description and a keywords list in the *Metadata* meta box in the post editing panel.
- Category-based Archives
 - The description of the category, if set, is used for the description meta tag. The name of the category is always used at the keywords metatag.
- Tag-based Archives
 - The description of the tag, if set, is used for the description meta tag. The name of the tag is always used at the keywords metatag.
- Site-wide META Tags
 - It is possible to add any metatags on all content.
- Global keywords (since 2.1.0)
 - It is possible to set some keywords that are prepended/appended to the keywords of all your content.
- Head link to copyright page.
- Default image to use in metadata, if a featured image has not been set.

**Custom title tag**

It is possible to customize the <em>title</em> tag for posts, pages and any public custom post type.

**'news_keywords' meta tag**

It is possible to set a <code>news_keywords</code> meta tag for posts, pages and any public custom post type. 
For more info about the <emnews_keywords</em> meta tag, please see this <a target="_blank" href="http://support.google.com/news/publisher/bin/answer.py?hl=en&answer=68297">Google help page</a>.

**Per post full meta tags**

It is possible to assign custom full meta tags to single posts (posts, pages, custom post types).

**Opengraph metadata**

- Front page
 - Automatic addition of metadata.
 - Supports custom default image if URL is provided.
- Posts and Pages
 - Automatic addition of metadata.
 - Use featured image or alternatively the default image if URL has been set.

**Dublin Core metadata**

- Posts and Pages
 - Automatic addition of metadata.

**Extra SEO features**

- Add the `NOODP,NOYDIR` option to the robots meta tag.
- Add the `NOINDEX,FOLLOW` options to the robots meta tag on category, tag, author or time based archives and search results.

**Languages**

Add-Meta-Tags is available in the following languages:

* English (default language)
* Slovak (sk_SK) - contributed by Branco from <a href="http://webhostinggeeks.com/user-reviews/">WebHostingGeeks.com</a>

**More**
 
Check out other [open source software](http://www.codetrax.org/projects) by George Notaras.


== Installation ==

Add-Meta-Tags can be easily installed through the plugin management interface from within the WordPress administration panel (*recommended*).

Alternatively, you may manually extract the compressed (zip) package in the `/wp-content/plugins/` directory.

After the plugin has been installed, activate it through the 'Plugins' menu in WordPress.

Finally, visit the plugin's administration panel at `Options->Metadata` to read the detailed instructions about customizing the generated metatags.

As it has been mentioned, no configuration is required for the plugin to function. It will add meta tags automatically. Full customization is possible though.

Read more information about the [Add-Meta-Tags installation](http://www.g-loaded.eu/2006/01/05/add-meta-tags-wordpress-plugin/ "Official Add-Meta-Tags Homepage").


== Upgrade Notice ==

No special requirements when upgrading.


== Frequently Asked Questions ==

Troubleshooting:

= My meta tags do not show up! =

Please, check if your theme's `header.php` file contains the following required piece of code: `<?php wp_head(); ?>`. If this is missing, contact the theme author. Full WordPress functionality requires this.

= My meta tags show up twice! =

The *description* and *keywords* meta tags are most probably already hardcoded into your theme's `header.php` file. Please contact the theme author.

= Where can I get support? =

Add-Meta-Tags is released as free software without warranties. You can get first class support from the [community of users](http://wordpress.org/support/plugin/add-meta-tags "Add-Meta-Tags Users").

= I found a bug! =

Please, be kind enough to [file a bug report](http://www.codetrax.org/projects/wp-add-meta-tags/issues/new "File bug about Add-Meta-Tags") to our issue database. This is the only way to bring the issue to the plugin author's attention.

= I want to request a new feature! =

Please, use our [issue database](http://www.codetrax.org/projects/wp-add-meta-tags/issues "Add-Meta-Tags Issue Database") to submit your requests.


== Screenshots ==

Screenshots as of v2.1.0.

1. Add-Meta-Tags administration interface.
2. Enable Metadata meta box in the screen options of the post/page editing panel.
3. Metadata meta box in the post editing panel.


== Changelog ==

Please check out the changelog of each release by following the links below. You can also check the [roadmap](http://www.codetrax.org/projects/wp-add-meta-tags/roadmap "Add-Meta-Tags Roadmap") regarding future releases of the plugin.

- [2.2.0](http://www.codetrax.org/versions/167) -- New features. Please review settings.
- [2.1.4](http://www.codetrax.org/versions/171)
- [2.1.3](http://www.codetrax.org/versions/170)
- [2.1.2](http://www.codetrax.org/versions/169)
- [2.1.1](http://www.codetrax.org/versions/168)
- [2.1.0](http://www.codetrax.org/versions/126)
- [2.0.4](http://www.codetrax.org/versions/132)
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

