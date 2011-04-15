=== Add Meta Tags ===
Donate link: http://www.g-loaded.eu/about/donate/
Tags: meta, metadata, seo, description, keywords, metatag, google, yahoo, bing
Requires at least: 1.5.2
Tested up to: 2.8.4
Stable tag: 1.7

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

Please read the dynamic [changelog](http://www.codetrax.org/projects/wp-add-meta-tags/changelog "Add-Meta-Tags ChangeLog")

= Wed, Nov 7 2007 - v1.6 =
* Bug Fixed: The adaptation to the new taxonomy system while maintaining
  backwards compatibility is solid in this version.
= Sat, Nov 3 2007 - v1.5 =
* The license under which the plugin is released has changed. The Add-Meta-Tags
  plugin is now released under the terms of an 'Apache License version 2'. This
  change is directly related to the development of the plugin and end users
  should not worry about anything as the plugin remains free and open-source
  software.
* The plugin packaging has changed. All information about licensing,
  installation, translations, acknowledgements has been removed from the plugin
  php file and is shipped in separate files inside the distribution package.
* Support for localization. Now translation of the plugin is very easy using
  tools like 'poedit'.
* Support for the new WordPress v2.3 feature: tags. Now, the automatic creation
  of the 'keywords' metatag includes both the post's categories amd its tags.
  This eliminates the need to create a custom field. If you need even more,
  keyword customization, the custom 'keywords' field functionality is still
  there. Note that %cats% and %tags%, if included in the custom 'keywords'
  field, will be translated to the post's categories and its tags respectively.
* Bug Fixed: The new WordPress 2.3 taxonomy system was the cause of an error
  when the post's categories were retrieved. This is now fixed, while old
  WordPress versions are fully supported.
* Minor bug fixes.
= Sun, Mar 11 2007 - v1.2 (v1.1 not released) =
* Multiword categories appeared as "word1-word2" etc in the keywords META tag.
  This has been fixed so they appear as "word1 word2". [Bug 5]
* More efficient storage of the add-meta-tags options in the wordpress database.
  All options are stored in a single field.
* A reset button has been added. The user can reset the plugin's options by
  pressing the Reset button.
* The user can now set META tags that will appear on all pages throughout the blog.
* The configuration panel has been updated.
* The "uncategorized" category is not used when all the site's categories are used
  as keywords. (in case no custom keywords have been set for the homepage)
* Added internal option to disable the keywords META tag generation.
= Tue Jan 30 2007 - v1.0 =
* Added support for writing META tags in the category archive pages.
* The plugin description was updated.
* Set a priority for the add-meta-tags function. This is because some people have
  complained about meta tags appearing too far down the page within the HTML HEAD area.
* The plugin configuration panel was updated.
= Fri Jan 26 2007 - v0.9 =
* Fixed bug with adding empty description (the post excerpt) to the description
  MetaTag WordPress 2.1, in which the get_the_excerpt() function does not work
  outside the loop any more. This was fixed in a way so that the plugin
  compatibility with older WordPress versions does not break. [Bug 4]
* In addition to the above fix, the new function that retrieves the excerpt
  directly from the the post tries to use full sentences, whenever possible, and
  also takes care of the length of the description (after it has been cleaned from
  HTML tags, so that it is not too short or too long.
* Enhanced the description clean-up filter.
= Sun Jan 14 2007 - v0.8 =
* META tags can be added to Pages from now on by adding the "description" and/or
  "keywords" custom fields.
* The administration Panel was updated.
* Implemented own function that fetches all of the blog categories. Although
  such a function will be implemented in the upcoming 2.0.7 release of WordPress
  at the time of this plugin release, this implementation was prefered in order
  to preserve backwards compatibility.
* A HTML comment is added together with the metatags, so that people who check
  the website's source code know which WordPress plugin has added the meta tags.
  Please do not remove this comment. This is not visible when viewing the website
  with a browser.
= Thu Jan 11 2007 - v0.7 =
* If the user-defined custom site description contained quotes, then wordpress
  escaped these quotes. These slashes are now stripped. (Thanks moeffju) [Bug 3]
* Added a section in the plugin code where I thank contributors or bug reporters.
= Wed Jan 10 2007 - v0.6 =
* A description filter was added. Double quotes and other special characters are
  are now transformed to HTML entities. Also, line-feed characters and multiple
  consecutive empty spaces are replaced by a single empty space. (Thanks Ronald) [Bug 2]
= Tue Nov 28 2006 - v0.5 =
* Added ability to override the keywords that derives from the post's categories
  with custom keywords from a custom field, named "keywords". (for single posts)
* When overriding keywords, %cats% is replaced with the post's categories.
* Added ability to override the description that derives from post's excerpt
  with a custom description from a custom field, named "description". (for single posts)
* Added administration panel. No more manual editing.
= Sun Nov 05 2006 - v0.4 =
* The plugin also adds "description" and "keywords" meta tags on the home page.
= Wed Oct 04 2006 - v0.3 =
* Plugin information update
= Fri Jan 6 2006 - v0.2 =
* Duh! I had forgotten to strip the tags from the excerpt. Now it's fixed. [Bug 1]
= Thu Jan 5 2006 - v0.1 =
* Initial release


