=== Add Meta Tags ===
Contributors: gnotaras
Donate link: http://bit.ly/HvUakt
Tags: amt, meta, metadata, seo, optimize, ranking, description, keywords, metatag, schema, opengraph, dublin core, schema.org, microdata, google, twitter cards, google plus, yahoo, bing, search engine optimization, rich snippets, semantic, structured, meta tags
Requires at least: 3.1.0
Tested up to: 3.8.1
Stable tag: 2.4.3
License: Apache License v2
License URI: http://www.apache.org/licenses/LICENSE-2.0.txt

Add basic meta tags and also Opengraph, Schema.org Microdata, Twitter Cards and Dublin Core metadata to optimize your web site for better SEO.


== Description ==

*Add-Meta-Tags* (<abbr title="Add-Meta-Tags Wordpress plugin">AMT</abbr>) adds metadata to your content, including the basic *description* and *keywords* meta tags, [Opengraph](http://ogp.me "Opengraph specification"), [Schema.org](http://schema.org/ "Schema.org Specification"), [Twitter Cards](https://dev.twitter.com/docs/cards "Twitter Cards Specification") and [Dublin Core](http://dublincore.org "Dublin Core Metadata Initiative") metadata. It is actively maintained since 2006 (historical [Add-Meta-Tags home](http://www.g-loaded.eu/2006/01/05/add-meta-tags-wordpress-plugin/ "Official historical Add-Meta-Tags Homepage")).


= Goals =

The goals of the Add-Meta-Tags plugin are:

- provide efficient, out-of-the-box search engine optimization (*SEO*) on a web site powered by WordPress.
- be customizable, yet simple and easy to use and configure with minimal or no support.
- be as lightweight as possible.
- support advanced customization through the WordPress filter/action system (for developers and advanced users).


= Free License and Donations =

*Add-Meta-Tags* is released under the terms of the <a href="http://www.apache.org/licenses/LICENSE-2.0.html">Apache License version 2</a> and, therefore, is **Free software**.

However, a significant amount of **time** and **energy** has been put into developing this plugin, so, its production has not been free from cost. If you find this plugin useful and if it has helped your blog get indexed better and rank higher, you can show your appreciation by making a small <a href="http://bit.ly/HvUakt">donation</a>.

Thank you in advance for **donating**!


= What it does =

*Add-Meta-Tags* (*AMT*) adds metadata to your web site. This metadata contains information about the **content**, the **author**, the **media files**, which have been attached to your content, and even about some of the **embedded media** (see the details about this feature in the relevant section below).

*Metadata* refers to information that describes the content in a machine-friendly way. Search engines and other online services use this metadata to better understand your content. Keep in mind that metadata itself does not automatically make your blog rank better. For this to happen the content is still required to meet various quality standards. However, the presence of accurate and adequate metadata gives search engines and other services the chance to make less guesses about your content, index and categorize it better and, eventually, deliver it to an audience that finds it useful. Good metadata facilitates this process and thus plays a significant role in achieving better rankings. This is what the *Add-Meta-Tags* plugin does.

The following list outlines how and where metadata is added to a *WordPress* blog.


= Basic meta tags =

The *description* and *keywords* meta tags are added:

**Front Page**

- Automatic addition of the blog's tagline in the *description* metatag.
- Automatic addition of the blog's categories in the *keywords* metatag.
- Customization is possible through the plugin's administration panel.
- If a static page is used as the front page, customization is possible at the page's editing panel (*Metadata* box).

**Posts & Pages**

- Automatic addition of an auto-generated excerpt of the post's or page's content in the *description* metatag. In case a post has a user-defined excerpt, then this is what is used.
- Automatic addition of the post's categories and tags in the *keywords* meta tag. Pages do not support categories and tags, so there is no automatic addition of the *keywords* metatag.
- Customization is possible by adding a custom description and keywords in the post's or page's editing panel (*Metadata* box).

**Attachment Pages**

- A *description* metatag is automatically generated from the caption or, if a caption has not been set, from the description of the attachment.

**Custom Post Types**

- A description is automatically generated from the first paragraph of the content. Keywords are not generated automatically.
- Customization of the *description* and *keywords* meta tags is possible at the post type's editing panel (*Metadata* box).

**Category-based Archives**

- The description of the category, if set, is used in the *description* meta tag. If a description does not exist, then a generic one is used.
- The name of the category is always used in the *keywords* metatag.

**Tag-based Archives**

- The description of the tag, if set, is used in the *description* meta tag. If a description does not exist, then a generic one is used.
- The name of the tag is always used in the *keywords* metatag.

**Author-based Archives**

- The bio of the WordPress user, if set, is used in the *description* meta tag on the first page of the author archive. All other author archive pages use a generic description.
- The categories of the posts that are currently being displayed in the page are used in the keywords meta tag.


= Extended Meta Tag Support =

The following advanced features are also available:

**Global keywords**

- It is possible to set some keywords that are prepended/appended to the keywords of all your content.

**Site-wide META Tags**

- It is possible to force any metatags site-wide.

**Custom title tag**

It is possible to customize the *title* element on posts, pages and public custom post types.

**'news_keywords' meta tag**

It is possible to set a *news_keywords* meta tag for posts, pages and any public custom post type. 
For more info about the *news_keywords* metatag, please read this <a target="_blank" href="http://support.google.com/news/publisher/bin/answer.py?hl=en&answer=68297">Google help page</a>.

**Per post full meta tags**

It is possible to assign custom full meta tags to single posts (posts, pages, custom post types).

**Copyright Metatag**

It is possible to add a head link to a user-defined copyright page.

**Default Image**

A path to an image, for instance the we site's logo, can be set in order to be used in autogenerated metadata if a *featured image* has not been set for the content.

**Opengraph metadata**

Opengraph meta tags can be automatically added to the front page, posts, pages, attachment pages and author archive.

**Schema.org Microdata**

Schema.org Microdata can be automatically added to the front page, posts, pages, image attachment pages and author archive.

The plugin automatically marks up posts, pages and custom post types as `Article` objects and also images, videos and audio as `Image`, `Video` and `Audio` MediaObjects respectively. It also considers the web site as an `Organization` object and the author as a `Person` object.

**Twitter Cards**

Twitter Cards can be automatically added to content pages and image attachment pages.

**Dublin Core metadata**

Dublin Core metatags can be automatically added to posts and pages and attachment pages.


= Other Features =

**Extra SEO features**

- Add the `NOODP,NOYDIR` option to the robots meta tag.
- Add the `NOINDEX,FOLLOW` options to the robots meta tag on category, tag, author or time based archives and search results.

**Metadata Review Mode**

When enabled, WordPress users with administrator privileges see a box that contains the full metadata (exactly as it is added in the HTML head) above the content for easier examination.


= Metadata for embedded media =

Add-Meta-Tags generates detailed metadata for the media that have been attached to the content. This happens for all the media you manage in the WordPress media library.

Apart from attaching local media to the content, WordPress lets authors also <a href="http://codex.wordpress.org/Embeds">embed external media</a> by simply adding a link to those media inside the content or by using the `[embed]` shortcode. Several external services are supported.

Add-Meta-Tags can detect some of those media and generate metadata for them. Currently, only links to Youtube and Vimeo videos, Soundcloud tracks and Flickr images are supported. So, even if you host your media externally on those services, this plugin can still generate metadata for them. This metadata is by no means as detailed as the metadata that is generated for local media, but it gives search engines a good idea about what external media are associated with your content.

This feature relies entirely on the data WordPress has already cached for the embedded media. The plugin will never send any requests to external services attempting to get more detailed information about the embedded media as this would involve unacceptable overhead.

Here is what is supported:

- Links to Youtube videos of the form: `http://www.youtube.com/watch?v=VIDEO_ID`
- Links to Vimeo videos of the form: `http://vimeo.com/VIDEO_ID`
- Links to Soundcloud tracks of the form: `https://soundcloud.com/USER_ID/TRACK_ID`
- Links to Flickr images of the form: `http://www.flickr.com/photos/USER_ID/IMAGE_ID/`

This feature should be considered experimental. This information might be changed in future versions of the plugin.


= Translations =

There is an ongoing effort to translate Add-Meta-Tags to as many languages as possible. The easiest way to contribute translations is to register to the [translations project](https://www.transifex.com/projects/p/add-meta-tags "Add-Meta-Tags translations project") at the Transifex service.

Once registered, join the team of the language translation you wish to contribute to. If a team does not exist for your language, be the first to create a translation team by requesting the language and start translating.


= Code Contributions =

If you are interested in contributing code to this project, please make sure you read the [special section](http://wordpress.org/plugins/add-meta-tags/other_notes/#How-to-contribute-code "How to contribute code") for this purpose, which contains all the details.


= Support and Feedback =

Please post your questions and provide general feedback and requests at the [Add-Meta-Tags Community Support Forum](http://wordpress.org/support/plugin/add-meta-tags).

To avoid duplicate effort, please do some research on the forum before asking a question, just in case the same or similar question has already been answered.

Also, make sure you read the [FAQ](http://wordpress.org/plugins/add-meta-tags/faq/ "Add-Meta-Tags FAQ").


= Advanced Customization =

Add-Meta-Tags allows filtering of the generated metatags and also of some core functionality through filters. This way advanced customization of the plugin is possible.

Add-Meta-Tags generates metadata that is used in the head area of the HTML page or embedded in the body (wrapped around the content or in the footer area).

The available filters are:

1. `amt_metadata_head` - applied to all metatags that have been generated by Add-Meta-Tags for the head area of the HTML page. The hooked function should accept and return 1 argument: an array of meta tags.
1. `amt_metadata_footer` - applied to all metatags that have been generated by Add-Meta-Tags for the footer area of the HTML page. The hooked function should accept and return 1 argument: an array of meta tags.
1. `amt_basic_metadata_head` - applied to the basic metatags (description, keywords, etc) that have been generated by Add-Meta-Tags for the head area of the HTML page. The hooked function should accept and return 1 argument: an array of meta tags.
1. `amt_opengraph_metadata_head` - applied to the OpenGraph metatags that have been generated by Add-Meta-Tags for the head area of the HTML page. The hooked function should accept and return 1 argument: an array of meta tags.
1. `amt_twitter_cards_metadata_head` - applied to the Twitter Cards metatags that have been generated by Add-Meta-Tags for the head area of the HTML page. The hooked function should accept and return 1 argument: an array of meta tags.
1. `amt_dublin_core_metadata_head` - applied to the Dublin Core metatags that have been generated by Add-Meta-Tags for the head area of the HTML page. The hooked function should accept and return 1 argument: an array of meta tags.
1. `amt_dublin_core_license` - applied to the URL of the license that is used by the Dublin Core metadata generator. The hooked function should accept and return 1 argument: a string. The hooked function can also accept the post ID as a second optional argument.
1. `amt_schemaorg_metadata_head` - applied to the 'author' and 'publisher' links in the head area of HTML page that have been generated by Add-Meta-Tags for Google+. The hooked function should accept and return 1 argument: an array of links.
1. `amt_schemaorg_metadata_footer` - applied to the Schema.org metatags that have been generated by Add-Meta-Tags for the footer area. The hooked function should accept and return 1 argument: an array of meta tags.
1. `amt_schemaorg_metadata_content` - applied to the Schema.org Microdata that has been generated by Add-Meta-Tags and is embedded around the content. The hooked function should accept and return 1 argument: an array of microdata. Note that `articleBody` is added to the array after filtering.
1. `amt_get_the_excerpt` - applied to the description that Add-Meta-Tags generates from the first paragraph of the content if no other description has been defined by the user. The hooked function should accept and return 1 argument: a string.
1. `amt_paged_append_data` - applied to the data that should be appended when paginated content is encountered and a page number greater than 1 is displayed. The hooked function should accept and return 1 argument: a string.
1. `amt_supported_post_types` - applied to the list of post types Add-Meta-Tags should add metadata to. By default, this list includes posts, pages, attachments and all available public post types. The hooked function should accept and return 1 argument: an array of post types.
1. `amt_external_description_fields` - applied to the list of external custom fields from which Add-Meta-Tags can read data for the description metatag. The hooked function should accept and return 1 argument: an array of field names. The hooked function can also accept the post ID as a second optional argument. Keep in mind that Add-Meta-Tags always saves description data to its default field, regardless of the field the data was read from.
1. `amt_external_keywords_fields` - applied to the list of external custom fields from which Add-Meta-Tags can read data for the keywords metatag. The hooked function should accept and return 1 argument: an array of field names. The hooked function can also accept the post ID as a second optional argument. Keep in mind that Add-Meta-Tags always saves keywords data to its default field, regardless of the field the data was read from.
1. `amt_external_title_fields` - applied to the list of external custom fields from which Add-Meta-Tags can read data for the title metatag. The hooked function should accept and return 1 argument: an array of field names. The hooked function can also accept the post ID as a second optional argument. Keep in mind that Add-Meta-Tags always saves title data to its default field, regardless of the field the data was read from.
1. `amt_external_news_keywords_fields` - applied to the list of external custom fields from which Add-Meta-Tags can read data for the news_keywords metatag. The hooked function should accept and return 1 argument: an array of field names. The hooked function can also accept the post ID as a second optional argument. Keep in mind that Add-Meta-Tags always saves news_keywords data to its default field, regardless of the field the data was read from.
1. `amt_external_full_metatags_fields` - applied to the list of external custom fields from which Add-Meta-Tags can read full meta tag HTML code. The hooked function should accept and return 1 argument: an array of field names. The hooked function can also accept the post ID as a second optional argument. Keep in mind that Add-Meta-Tags always saves full meta tag data to its default field, regardless of the field the data was read from.
1. `amt_embedded_media` - applied to the array in which Add-Meta-Tags stores information about the embedded media. The hooked function should accept and return 1 argument: an array of post types. The hooked function can also accept the post ID as a second optional argument.
1. `amt_valid_full_metatag_html` - applied to all list of valid HTML elements and attributes that can be used in the 'Full Meta Tags' boxes in the general settings and in the metabox. The hooked function should accept and return 1 argument: an array of valid elements and their attributes. The provided array has the same format as the `$allowed_html` array of the <a href="http://codex.wordpress.org/Function_Reference/wp_kses">wp_kses function</a>.

**Example 1**: you want to replace the autogenerated `og:site_name` Opengraph metatag with a custom one.

This can easily be done by hooking a custom function to the `amt_opengraph_metadata_head` filter:

`
function customize_og_sitename_metatag( $metatags ) {
    // ... replace 'og:site_name' here
    return $metatags;
}
add_filter( 'amt_opengraph_metadata_head', 'customize_og_sitename_metatag', 10, 1 );
`
This code can be placed inside your theme's `functions.php` file.

**Example 2**: you want to limit the generation of metadata by Add-Meta-Tags to specific post types.

This can easily be done by hooking a custom function to the `amt_supported_post_types` filter:

`
function limit_metadata_to_post_types( $post_types ) {
    // ... process and return the $post_types array
    // ... or just return a custom array of post types
    return array( 'post', 'book', 'project');
}
add_filter( 'amt_supported_post_types', 'limit_metadata_to_post_types', 10, 1 );
`
This code can be placed inside your theme's `functions.php` file.

**Example 3**: you use plugin X that saves the descriptions, keywords, etc in its custom fields. You want to migrate to Add-Meta-Tags and need to read the fields of your old plugin.

This can easily be done by hooking custom functions to the `amt_external_description_fields` and `amt_external_keywords_fields` filters:

`
function read_old_plugin_description_field( $extfields ) {
    // Although $extfields is currently empty, it's a good practice to
    // append your old plugins description field to the $extfields array.
    array_unshift( $extfields, 'my_old_plugin_description_field' );
    return $extfields;
}
add_filter( 'amt_external_description_fields', 'read_old_plugin_description_field', 10, 1 );

function read_old_plugin_keywords_field( $extfields, $post_id ) {
    // This function also demonstrates how to get and possibly use the post's ID
    // Append your old plugins keywords field to the $extfields array
    if ( in_array( $post_id, array( 1, 2, 5, 8 ) ) ) {
        array_unshift( $extfields, 'my_old_plugin_keywords_field' );
    }
    return $extfields;
}
add_filter( 'amt_external_keywords_fields', 'read_old_plugin_keywords_field', 10, 2 );
`
This code can be placed inside your theme's `functions.php` file.

Keep in mind that:

1. AMT internal fields have priority over the external fields. If both the internal field and an external field contain data, then the data of the internal field is used.
1. AMT uses external fields to only read data. It never writes to external fields. Whenever the content is saved, every piece of information, which may have been read from an external field, is stored to the relevant AMT internal field. Consequently, when the content is saved, information from external fields is migrated to the AMT internal fields, and external fields have no effect on this specific content any more.

**Example 4**: Add the `title` element to the valid html elements for use in the full meta tags box.

This can easily be done by hooking custom functions to the `amt_valid_full_metatag_html` filter:

`
function extend_full_metatag_valid_elements( $valid_elements ) {
    // Construct the title element array (key: element name, value: array of valid attributes)
    $title_element = array( 'title' => array() );
    // Append the 'title' element to the valid elements
    $valid_elements = array_merge( $valid_elements, $title_element );
    return $valid_elements;
}
add_filter( 'amt_valid_full_metatag_html', 'extend_full_metatag_valid_elements', 10, 1 );
`


= Custom Fields =

Add-Meta-Tags uses the following internal custom fields to store data related to the content:

* `_amt_description` - the content's custom description (the `description` field is also read as a fallback for backwards compatibility).
* `_amt_keywords` - the content's custom keywords (the `keywords` field is also read as a fallback for backwards compatibility).
* `_amt_title` - the content's custom title.
* `_amt_news_keywords` - the content's custom news keywords.
* `_amt_full_metatags` - the content's full meta tag code.

The following internal custom fields are also used to store user contact info, which is required for full Add-Meta-Tags functionality:

TODO: add contactinfo fields here

= Template Tags =

The following *template tags* are available for use in your theme:

1. `amt_content_description()` : prints the content's description as generated by Add-Meta-Tags.
1. `amt_content_keywords()` : prints a comma-delimited list of the content's keywords as generated by Add-Meta-Tags.
1. `amt_metadata_head()` : prints the full metadata for the head area as generated by Add-Meta-Tags.
1. `amt_metadata_footer()` : prints the full metadata for the head area as generated by Add-Meta-Tags.

= Theme Requirements =

Add-Meta-Tags uses the `wp_head` and `wp_footer` action hooks to embed metadata to the HTML HEAD and the HTML BODY. Therefore, it is essential that your theme includes these two action hooks in its templates.

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

= There is no amount set in the donation form! How much should I donate? =

The amount of the donation is totally up to you. You can think of it like this: Are you happy with the plugin? Do you think it makes your life easier or adds value to your web site? If this is a yes and, if you feel like showing your appreciation, you could imagine buying me a cup of coffee at your favorite Cafe and <a href="http://bit.ly/HvUakt">make a donation</a> accordingly.

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

= Is there a bug tracker? =

You can find the bug tracker at the [Add-Meta-Tags Development web site](http://www.codetrax.org/projects/wp-add-meta-tags).


== Screenshots ==

Screenshots as of v2.4.0

1. Add-Meta-Tags administration interface ( `Options -> Metadata` ).
2. Enable Metadata meta box in the screen options of the post editing panel.
3. Metadata box in the post editing panel.
4. Contact info entries added by Add-Meta-Tags (AMT) in the user profile page.


== Changelog ==

Please check out the changelog of each release by following the links below. You can also check the [roadmap](http://www.codetrax.org/projects/wp-add-meta-tags/roadmap "Add-Meta-Tags Roadmap") regarding future releases of the plugin.

- [2.4.3](http://www.codetrax.org/versions/216)
 - Updated translations.
- [2.4.2](http://www.codetrax.org/versions/215)
 - Minor bug fixes contributed by users during the last months. (Thanks Andy, raidnet, bhoogterp)
 - Updated translations.
- [2.4.1](http://www.codetrax.org/versions/198)
 - Resolved almost all HTML validation issues.
 - The first page of the category/tag/author archives is always indexed.
- [2.4.0](http://www.codetrax.org/versions/190)
 - Improved metadata for content and attachments (Opengraph, Dublin Core, Twitter Cards, Schema.org).
 - Metadata generated for all attached local media.
 - Experimental metadata for embedded media. Currently supported: Youtube, Vimeo, Soundcloud, Flickr. See description page for details.
 - Support for all Twitter Card types. Takes post format into account.
 - Added Serbian translation (thanks Borisa Djuraskovic)
 - Added German translation (thanks BodoKaDe)
 - Added Greek translation (George Notaras)
- [2.3.7](http://www.codetrax.org/versions/189)
- [2.3.6](http://www.codetrax.org/versions/188)
 - Updated translations. Temporarily removed Slovak translation (until it is refreshed). Added Turkish translation (thanks Burak).
- [2.3.5](http://www.codetrax.org/versions/187)
 - Improved metadata on image attachment pages.
 - Support for reading data from external custom fields. Useful when migrating from other plugins or from custom hacks. Please read the *Advanced Customization* section at the plugin homepage for more information and examples.
 - Improved basic metadata for category/tag/author based archives (thanks Bidstall Admin).
 - Improved Dublin Core metadata.
- [2.3.4](http://www.codetrax.org/versions/186)
- [2.3.3](http://www.codetrax.org/versions/183)
 - Available features in this release: Twitter Cards, Schema.org Microdata.
 - Make sure you read the notes under the relevant options. Please report any issues in the WordPress Forums.
 - Better metadata in author archives.
- [2.3.2](http://www.codetrax.org/versions/182)
 - Solid sanitization of user input. Please report any issues in the WordPress forums.
 - Improved core functionality.
 - Customization through filters. Developers and advanced users please read the *Advanced Customization* section at the plugin homepage.
- [2.3.1](http://www.codetrax.org/versions/180)
 - Please read note under Opengraph settings regarding the generation of the 'article:author' and 'article:publisher' Opengraph tags.
- [2.3.0](http://www.codetrax.org/versions/179)
- [2.2.0](http://www.codetrax.org/versions/167)
 - New features. Please review settings.
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


== How to contribute code ==

This section contains information about how to contribute code to this project.

Add-Meta-Tags is released under the Apache License v2.0 and is free open-source software. Therefore, code contributions are more than welcome!

But, please, note that not all code contributions will finally make it to the main branch. Patches which fix bugs or improve the current features are very likely to be included. On the contrary, patches which add too complicated or sophisticated features, extra administration options or transform the general character of the plugin are unlikely to be included.

= Source Code =

The repository with the most up-to-date source code can be found on Bitbucket (Mercurial). This is where development takes place:

`https://bitbucket.org/gnotaras/wordpress-add-meta-tags`

The main repository is very frequently mirrored to Github (Git):

`https://github.com/gnotaras/wordpress-add-meta-tags`

The Subversion repository on WordPress.org is only used for releases. The trunk contains the latest stable release:

`http://plugins.svn.wordpress.org/add-meta-tags/`

= Creating a patch =

Using Mercurial:

`
hg clone https://bitbucket.org/gnotaras/wordpress-add-meta-tags
cd wordpress-add-meta-tags
# ... make changes ...
hg commit -m "fix for bug"
# create a patch for the last commit
hg export --git tip > bug-fix.patch
`

Using Git:

`
git clone https://github.com/gnotaras/wordpress-add-meta-tags
cd wordpress-add-meta-tags
# ... make changes to add-meta-tags.php or other file ...
git add add-meta-tags.php
git commit -m "my fix"
git show > bug-fix.patch
`

Using SVN:

`
svn co http://plugins.svn.wordpress.org/add-meta-tags/trunk/ add-meta-tags-trunk
cd add-meta-tags-trunk
# ... make changes ...
svn diff > bug-fix.patch
`

= Patch Submission =

Here are some ways in which you can submit a patch:

* submit to the [bug tracker](http://www.codetrax.org/projects/wp-add-meta-tags/issues) of the development website.
* create a pull request on Bitbucket or Github.
* email it to me directly (my email address can be found in `add-meta-tags.php`).
* post it in the WordPress forums.

Please note that it may take a while before I get back to you regarding the patch.

Last, but not least, all code contributions are governed by the terms of the Apache License v2.0.

