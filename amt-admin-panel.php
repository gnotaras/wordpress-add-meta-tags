<?php
/**
 *  This file is part of the Add-Meta-Tags distribution package.
 *
 *  Add-Meta-Tags is an extension for the WordPress publishing platform.
 *
 *  Homepage:
 *  - http://wordpress.org/plugins/add-meta-tags/
 *  Documentation:
 *  - http://www.codetrax.org/projects/wp-add-meta-tags/wiki
 *  Development Web Site and Bug Tracker:
 *  - http://www.codetrax.org/projects/wp-add-meta-tags
 *  Main Source Code Repository (Mercurial):
 *  - https://bitbucket.org/gnotaras/wordpress-add-meta-tags
 *  Mirror repository (Git):
 *  - https://github.com/gnotaras/wordpress-add-meta-tags
 *  Historical plugin home:
 *  - http://www.g-loaded.eu/2006/01/05/add-meta-tags-wordpress-plugin/
 *
 *  Licensing Information
 *
 *  Copyright 2006-2016 George Notaras <gnot@g-loaded.eu>, CodeTRAX.org
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *
 *  The NOTICE file contains additional licensing and copyright information.
 */


/**
 * Module containing the admin panel and metabox code.
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
    header( 'HTTP/1.0 403 Forbidden' );
    echo 'This file should not be accessed directly!';
    exit; // Exit if accessed directly
}


// Display information message
function amt_show_info_msg($msg) {
    echo '<div id="message" class="updated fade"><p>' . esc_attr( $msg ) . '</p></div>';
}


/**
 * Administration Panel - Add-Meta-Tags Settings
 */

function amt_admin_init() {

    // Here we just add some dummy variables that contain the plugin name and
    // the description exactly as they appear in the plugin metadata, so that
    // they can be translated.
    $amt_plugin_name = __('Add Meta Tags', 'add-meta-tags');
    $amt_plugin_description = __('Add basic meta tags and also Opengraph, Schema.org Microdata, Twitter Cards and Dublin Core metadata to optimize your web site for better SEO.', 'add-meta-tags');

    // Perform automatic settings upgrade based on settings version.
    // Also creates initial default settings automatically.
    // NOTE: Reverted back to the test performed in amt-settings.php, because otherwise
    // the ``is_array($options) && array_has_key('foo', $options)`` should be performed
    // every time a setting value is retrieved from the settings array.
    //amt_plugin_upgrade();

    // Register scripts and styles

}
add_action( 'admin_init', 'amt_admin_init' );


function amt_admin_menu() {
    /* Register our plugin page */
    $amt_options_page = add_options_page( __('Metadata Settings', 'add-meta-tags'), __('Metadata', 'add-meta-tags'), 'manage_options', 'add-meta-tags-options', 'amt_options_page' );

    // Add help tabs when the Add-Meta-Tags admin page loads
    add_action( 'load-' . $amt_options_page, 'amt_admin_help_tabs' );
}
add_action( 'admin_menu', 'amt_admin_menu');


//
// Enqueue scripts and styles
// From: http://codex.wordpress.org/Plugin_API/Action_Reference/admin_enqueue_scripts#Example:_Target_a_Specific_Admin_Page
//
function amt_enqueue_admin_scripts_and_styles( $hook ) {
    //var_dump($hook);
    if ( 'settings_page_add-meta-tags-options' != $hook ) {
        return;
    }

    // Using included Jquery
    wp_enqueue_script('jquery');

    // Necessary for the media selector.
    // https://codex.wordpress.org/Javascript_Reference/wp.media
    wp_enqueue_media();

    // Register our stylesheet.
    wp_register_style( 'amt_settings', plugins_url( 'css/amt-settings.css', AMT_PLUGIN_FILE ) );

    // Enqueue style.
    wp_enqueue_style( 'amt_settings' );

    // Register Add-Meta-Tags admin scripts
    wp_register_script( 'amt_image_selector_script', plugins_url( 'js/amt-image-selector.js', AMT_PLUGIN_FILE ), array('jquery') );

    // Enqueue the Add-Meta-Tags Admin Scripts
    wp_enqueue_script( 'amt_image_selector_script' );

}
add_action( 'admin_enqueue_scripts', 'amt_enqueue_admin_scripts_and_styles' );
// Note: `admin_print_styles` should not be used to enqueue styles or scripts on the admin pages. Use `admin_enqueue_scripts` instead. 


/**
 * Function that adds the help tabs for the options.
 */
function amt_admin_help_tabs() {
    // Get current screen
    $screen = get_current_screen();

    // Starting
    $help_text = '
    <p>'.__('Welcome to the Add-Meta-Tags Documentation.', 'add-meta-tags').'</p>

    <p>'.__('This help system contains detailed information about the available configuration settings. The documentation is divided into sections. Each section contains information about the relevant configuration options.', 'add-meta-tags').'</p>

    <p>'.__('Select the <strong>Introduction</strong> section for more information about what metadata is and how Add-Meta-Tags works.', 'add-meta-tags').'</p>

    ';
    $screen->add_help_tab( array(
        'id'	=> 'amt_help_starting',
        'title'	=> __('Starting', 'add-meta-tags'),
        'content'	=> $help_text,
    ) );

    // Introduction
    $help_text = '
    <h2>'.__('Introduction to metadata', 'add-meta-tags').'</h2>

    <p>'.__('<em>Metadata</em> refers to information that describes the content in a machine-friendly way. Search engines and other online services use this metadata to better understand your content. Keep in mind that metadata itself does not automatically make your blog rank better. For this to happen the content is still required to meet various quality standards. However, the presence of accurate and adequate metadata gives search engines and other services the chance to make less guesses about your content, index and categorize it better and, eventually, deliver it to an audience that finds it useful.  Good metadata facilitates this process and thus plays a significant role in achieving better rankings. This is what the Add-Meta-Tags plugin does.', 'add-meta-tags').'</p>

    <h2>'.__('Add-Meta-Tags mission', 'add-meta-tags').'</h2>

    <p>'.__('Add-Meta-Tags is a Free metadata plugin for the WordPress Publishing Platform that can optimize your web site for more efficient indexing of your content by search engines and easier sharing on social networks. It achieves this by generating machine friendly information about your content, called <em>metadata</em>, according to widely used standard specifications.', 'add-meta-tags').'</p>

    <p>'.__('<em>Add Meta Tags</em> is a great tool to assist you with the search engine optimization (<em>SEO</em>) of your web site. However, neither the plugin nor its developer provide you with any SEO advice or rating of your content. Any decision regarding SEO is still your own responsibility and subject to your own research. This is by design.', 'add-meta-tags').'</p>

    <h2>'.__('How it works', 'add-meta-tags').'</h2>
        
    <p>'.__('Add-Meta-Tags tries to follow the "<em>It just works</em>" principal. By default, the <em>description</em> and <em>keywords</em> meta tags are added to the front page, posts, pages, public custom post types, attachment pages, category, tag, custom taxonomy and author based archives. Furthermore, it is possible to enable the generation of <em>Opengraph</em>, <em>Dublin Core</em>, <em>Twitter Cards</em> and <em>Schema.org</em> metadata. The plugin also supports some extra SEO related functionality that helps you fine tune your web site.', 'add-meta-tags').'</p>
    
    <p>'.__('The automatically generated metadata can be further customized for each individual post, page, or any public custom post type directly from the <em>Metadata</em> box inside the post editing panel. If the <em>Metadata</em> box is not visible, you probably need to enable it at the <a href="http://en.support.wordpress.com/screen-options/">Screen Options</a> of the post editing panel.', 'add-meta-tags').'</p>

    <p>'.__('Apart from the customization through the WordPress administration interface, the generated metadata and specific aspects of the plugin\'s functionality can be further customized to a great extent programmatically. Read the technical notes at the <a href="http://www.codetrax.org/projects/wp-add-meta-tags/wiki" title="Add-Meta-Tags Development web site">development web site</a> and find ready sample code snippets for commonly needed customizations in the <a href="http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Add-Meta-Tags_Cookbook" title="Add-Meta-Tags Cookbook">Add-Meta-Tags Cookbook</a>.', 'add-meta-tags').'</p>';
    $screen->add_help_tab( array(
        'id'	=> 'amt_help_introduction',
        'title'	=> __('Introduction', 'add-meta-tags'),
        'content'	=> $help_text,
    ) );

    // General Settings
    $help_text = '

    <h3>'.__('Front Page Description', 'add-meta-tags').'</h3>

    <p>'.__('Enter a short (150-250 characters long) description of your blog. This text will be used in the <em>description</em> and other similar metatags on the <strong>front page</strong>. If this is left empty, then the blog\'s <em>Tagline</em> from the <a href="options-general.php">General Options</a> will be used.', 'add-meta-tags').'</p>

    <h3>'.__('Front Page Keywords', 'add-meta-tags').'</h3>

    <p>'.__('Enter a comma-delimited list of keywords for your blog. These keywords will be used in the <em>keywords</em> meta tag on the <strong>front page</strong>. If this field is left empty, then all of your blog\'s <a href="edit-tags.php?taxonomy=category">categories</a> will be used as keywords for the <em>keywords</em> meta tag.', 'add-meta-tags').'</p>
    <p><strong>'.__('Example', 'add-meta-tags').'</strong>: <code>'.__('keyword1, keyword2, keyword3', 'add-meta-tags').'</code></p>

    <h3>'.__('Global Keywords', 'add-meta-tags').'</h3>

    <p>'.__('Enter a comma-delimited list of global keywords which will be added before the keywords of <strong>all</strong> posts and pages.', 'add-meta-tags').'</p>
    <p><strong>'.__('Example', 'add-meta-tags').'</strong>: <code>'.__('keyword1, keyword2, keyword3', 'add-meta-tags').'</code></p>
    <p>'.__('By default, these keywords are prepended to the post/page\'s keywords. For enhanced flexibility, it is possible to use the <code>%contentkw%</code> placeholder, which will be populated with the post/page\'s autogenerated or user-defined keywords. This way you can globally both prepend and append keywords to the <em>keywords</em> of your content.', 'add-meta-tags').'</p>
    <p><strong>'.__('Example', 'add-meta-tags').'</strong>: <code>'.__('keyword1, keyword2, %contentkw%, keyword3', 'add-meta-tags').'</code></p>

    <h3>'.__('Site-wide META tags', 'add-meta-tags').'</h3>

    <p>'.__('Provide the full XHTML code of extra META elements you would like to add to all the pages of your web site (read more about the <a href="http://en.wikipedia.org/wiki/Meta_element" target="_blank">META HTML element</a> on Wikipedia).', 'add-meta-tags').'</p>
    <p><strong>'.__('Examples', 'add-meta-tags').'</strong>:</p>
    <p><code>&lt;meta name="google-site-verification" content="1234567890" /&gt;</code></p>
    <p><code>&lt;meta name="msvalidate.01" content="1234567890" /&gt;</code></p>
    <p><code>&lt;meta name="robots" content="noimageindex" /&gt;</code></p>
    <p><code>&lt;meta property="fb:admins" content="1234" /&gt;</code></p>
    <p><code>&lt;meta property="fb:app_id" content="4321" /&gt;</code></p>

    <p>'.__('Moreover, the full meta tags box accepts the special notation <code>[field=Field Name]</code> which lets you use data from a Custom Field with name <em>Field Name</em>. This special notation may exist anywhere inside the meta tag. To automatically add paging information to URLs, append the <code>PAGEINFO</code> placeholder.', 'add-meta-tags').'</p>

    ';
    $screen->add_help_tab( array(
        'id'	=> 'amt_help_general',
        'title'	=> __('General settings', 'add-meta-tags'),
        'content'	=> $help_text,
    ) );

    // Publisher Settings
    $help_text = '
    <p>'.__('This section contains options related to your web site; the publisher of the content.', 'add-meta-tags').'</p>
    <p>'.__('The following publisher related settings are shared among all users. Filling in these settings is entirely optional.', 'add-meta-tags').'</p>

    <h3>'.__('Facebook publisher profile URL', 'add-meta-tags').':</h3>

    <p>'.__('Enter an absolute URL to the Facebook profile of the publisher. If this is filled in, it will be used in the <code>article:publisher</code> meta tag.', 'add-meta-tags').'</p>
    <p><strong>'.__('Example', 'add-meta-tags').'</strong>: <code>https://www.facebook.com/awesome.editors</code></p>

    <h3>'.__('Google+ publisher profile URL', 'add-meta-tags').':</h3>

    <p>'.__('Enter an absolute URL to the Google+ profile of the publisher. If this is filled in, it will be used in the link with <code>rel="publisher"</code> in the HEAD area of the web page.', 'add-meta-tags').'</p>
    <p><strong>'.__('Example', 'add-meta-tags').'</strong>: <code>https://plus.google.com/+AwesomeEditors/</code></p>

    <h3>'.__('Twitter publisher username', 'add-meta-tags').':</h3>

    <p>'.__('Enter the Twitter username of the publisher (without @).', 'add-meta-tags').'</p>
    <p><strong>'.__('Example', 'add-meta-tags').'</strong>: <code>AwesomeEditors</code></p>';
    $screen->add_help_tab( array(
        'id'	=> 'amt_help_publisher',
        'title'	=> __('Publisher Settings', 'add-meta-tags'),
        'content'	=> $help_text,
    ) );

    // Author Settings
    $help_text = '
    <p>'.__('This section contains options related to the authors of the content.', 'add-meta-tags').'</p>

    <h3>'.__('URLs of author profiles on social networks', 'add-meta-tags').':</h3>

    <p>'.__('Each author may fill in the the URLs of the profiles on external social networks in the WordPress user profile page (fields marked with <em>AMT</em>).', 'add-meta-tags').'</p>

    <h3>'.__('Main source of local author profiles', 'add-meta-tags').':</h3>

    <p>'.__('WordPress does not have a dedicated page for author or user profiles. By convention, Add-Meta-Tags considers the first page of the author archive as the page of the local profile of each author.', 'add-meta-tags').'</p>

    <p>'.__('It is possible to select alternative sources of local author profiles:', 'add-meta-tags').'</p>

    <ul>
    <li>'.__('First page of the author archive', 'add-meta-tags').' &mdash; '.__('This is the default option. The first page of the author archive is considered as the page of the local author profile on which a <em>Person</em> metadata entity is the top level metadata object. Moreover, the front page is considered the page on which the <em>Organization</em> metadata entity is the top level metadata object.', 'add-meta-tags').'</li>
    <li>'.__('Front page (with static page only)', 'add-meta-tags').' &mdash; '.__('This option is only useful on personal web sites where there is a single author. If the front page is set as the source of the local author profile, then a <em>Person</em> metadata entity is the top level metadata object on the front page, instead of the <em>Organization</em> metadata object. In fact, no <em>Organization</em> metadata object is generated on any web page. Please note that this option has an effect only if a static page has been set as the front page.', 'add-meta-tags').'</li>
    <li>'.__('BuddyPress profile page', 'add-meta-tags').' &mdash; '.__('This option is only useful if BuddyPress is used. In this case, the author\'s BuddyPress profile page is considered as the source of the local author profile, instead of the first page of the author archive. The generation of metadata on BuddyPress pages by this plugin has to be enabled in the <em>Extended Metadata</em> settings.', 'add-meta-tags').'</li>
    <li>'.__('Author web site URL', 'add-meta-tags').' &mdash; '.__('If this option is selected, then the URL of the <em>Website</em> field of the WordPress user profile is considered to be the URL of the local author profile, regardless of the fact that the URL may lead to a third party web site.', 'add-meta-tags').'</li>
    </ul>

    <p>'.__('This is considered an advanced option and should not be changed, unless you know exactly what you are doing.', 'add-meta-tags').'</p>';
    $screen->add_help_tab( array(
        'id'	=> 'amt_help_authors',
        'title'	=> __('Author Settings', 'add-meta-tags'),
        'content'	=> $help_text,
    ) );

    // Basic Meta Tags
    // Open Graph Generator
    $help_text = '
    <h3>'.__('Automatically generate the <em>description</em> meta tag.', 'add-meta-tags').'</h3>

    <p>'.__('If this option is enabled, the <em>description</em> meta tag is automatically generated for the content, attachments and archives. Customization of the <em>description</em> meta tag is possible through the <em>Metadata</em> box in the post editing screen.', 'add-meta-tags').'</p>

    <h3>'.__('Automatically generate the <em>keywords</em> meta tag.', 'add-meta-tags').'</h3>

    <p>'.__('If this option is enabled, the <em>keywords</em> meta tag is automatically generated for the content and archives. Keywords are not generated automatically on pages and attachments. Customization of the <em>keywords</em> meta tag is possible through the <em>Metadata</em> box in the post editing screen.', 'add-meta-tags').'</p>';
    $screen->add_help_tab( array(
        'id'	=> 'amt_help_metadata_basic',
        'title'	=> __('Basic meta tags', 'add-meta-tags'),
        'content'	=> $help_text,
    ) );

    // Open Graph Generator
    $help_text = '
    <h3>'.__('Automatically generate Opengraph meta tags.', 'add-meta-tags').'</h3>

    <p>'.__('If this option is enabled, Opengraph metadata is automatically generated for content, attachments and archives. For more information, please refer to the <a href="http://ogp.me">Opengraph specification</a>.', 'add-meta-tags').'</p>

    <h3>'.__('Add Facebook\'s XML namespaces.', 'add-meta-tags').'</h3>

    <p>'.__('When enabled, the Facebook\'s XML namespaces for the <code>og</code> and <code>fb</code> meta tag name prefixes are added to the <code>html</code> element of the page. If your theme already contains these namespaces, then this option should not be enabled.', 'add-meta-tags').'</p>

    <h3>'.__('Omit <code>og:video</code> meta tags.', 'add-meta-tags').'</h3>

    <p>'.__('When a post containing a video is shared on Facebook, Facebook uses its own player to embed the video in the timeline. If this setting is enabled, <code>og:video</code> meta tags are no longer generated and Facebook, instead of embedding your video, links to your actual video post.', 'add-meta-tags').'</p>

    <h3>'.__('Important Note', 'add-meta-tags').'</h3>

    <p>'.__('By default, this feature sets the URL of the front page of your web site to the <code>article:publisher</code> meta tag and the URL of the author archive to the <code>article:author</code> meta tag. In order to link to the publisher page and the author profile on Facebook, it is required to provide the respective URLs. The profile URL of the publisher can be entered in this page under the <em>Publisher Settings</em> section. The profile URL of the author can be entered in the WordPress user <a href="profile.php">profile page</a> under the section <em>Contact Info</em>. For example:', 'add-meta-tags').'</p>
    <blockquote>'.__('Facebook author profile URL', 'add-meta-tags').' (AMT): <code>https://www.facebook.com/john.smith</code></blockquote>
    <blockquote>'.__('Facebook publisher profile URL', 'add-meta-tags').' (AMT): <code>https://www.facebook.com/awesome.editors</code></blockquote>
    <p>'.__('It is possible to control the value of the <code>og:type</code> meta tag either by changing the post format or programmatically via a filter. By default, the <code>og:type</code> is set to <code>article</code> for all content, except for video attachment pages and posts whose post format has been set to <code>video</code>, on which <code>og:type</code> is set to <code>video.other</code>.', 'add-meta-tags').'</p>';
    $screen->add_help_tab( array(
        'id'	=> 'amt_help_metadata_opengraph',
        'title'	=> __('Opengraph', 'add-meta-tags'),
        'content'	=> $help_text,
    ) );

    // Twitter Cards Generator
    $help_text = '
    <h3>'.__('Automatically generate Twitter Cards meta tags.', 'add-meta-tags').'</h3>

    <p>'.__('If this option is enabled, Twitter Cards metadata is automatically generated for content and attachments. For more information, please refer to the <a href="https://dev.twitter.com/docs/cards">Twitter Cards specification</a>.', 'add-meta-tags').'</p>

    <h3>'.__('Enforce the use of <code>summary_large_image</code> as default card type.', 'add-meta-tags').'</h3>

    <p>'.__('If this setting is enabled, <code>summary_large_image</code> will be used as the default Twitter Card type instead of the <code>summary</code> card type.', 'add-meta-tags').'</p>

    <h3>'.__('Enable the generation of <em>player</em> cards for local media.', 'add-meta-tags').'</h3>

    <p>'.__('Enable the generation of <em>player</em> cards for locally hosted audio and video attachments or for posts with their post format set to audio or video. In the latter case, an audio or video is expected to be attached to the post respectively. A mandatory requirement for this feature, as outlined in the Twitter Cards specifications, is secure access to your web site over the HTTPS protocol. If secure access to your web site has not been configured properly, the player cards will not be rendered by the Twitter service. Moreover, using self-signed certificates could cause problems which might be hard to identify. This feature should be considered experimental.', 'add-meta-tags').'</p>

    <h3>'.__('Important Notes', 'add-meta-tags').'</h3>

    <p>'.__('In order to generate the <code>twitter:site</code> and <code>twitter:creator</code> meta tags, it is required to provide the respective usernames of the Twitter account of the author and/or the publisher of the content. The username of the publisher can be entered in this page under the <em>Publisher Settings</em> section. The username of the author can be entered in the WordPress user <a href="profile.php">profile page</a> under the section <em>Contact Info</em>. For example:', 'add-meta-tags').'</p>
    <blockquote>'.__('Twitter author username', 'add-meta-tags').' (AMT): <code>JohnSmith</code></blockquote>
    <blockquote>'.__('Twitter publisher username', 'add-meta-tags').' (AMT): <code>AwesomeEditors</code></blockquote>

    <p>'.__('By default, a Twitter Card of type <em>summary</em> is generated for your content. If your theme supports <a href="http://codex.wordpress.org/Post_Formats">post formats</a>, then it is possible to generate Twitter Cards of various types according to the specified post format. More specifically:', 'add-meta-tags').'</p>
    <blockquote>'.__('A <code>summary</code> card is generated for posts with one of the <code>standard</code>, <code>aside</code>, <code>link</code>, <code>quote</code>, <code>status</code> and <code>chat</code> formats.', 'add-meta-tags').'</blockquote>
    <blockquote>'.__('A <code>summary_large_image</code> card is generated for posts with the <code>image</code> format. An image is expected to be attached or embedded to the post.', 'add-meta-tags').'</blockquote>
    <blockquote>'.__('A <code>gallery</code> card is generated for posts with the <code>gallery</code> format. At least one image is expected to be attached or embedded to the post.', 'add-meta-tags').'</blockquote>
    <blockquote>'.__('A <code>photo</code> card is generated for image attachment pages.', 'add-meta-tags').'</blockquote>
    <blockquote>'.__('A <code>player</code> card is generated for posts with the <code>audio</code> or <code>video</code> format and for audio or video attachment pages. Regarding posts, an audio or video is expected to be attached or embedded to the post.', 'add-meta-tags').'</blockquote>';
    $screen->add_help_tab( array(
        'id'	=> 'amt_help_metadata_twitter_cards',
        'title'	=> __('Twitter Cards', 'add-meta-tags'),
        'content'	=> $help_text,
    ) );

    // Dublin Core Generator
    $help_text = '
        <h3>'.__('Automatically generate Dublin Core metadata.', 'add-meta-tags').'</h3>

        <p>'.__('If this option is enabled, Dublin Core metadata is automatically generated for your content and attachments. For more information, please refer to <a href="http://dublincore.org">Dublin Core Metadata Initiative</a>.', 'add-meta-tags').'</p>

        <h3>'.__('Add the Dublin Core XML namespace.', 'add-meta-tags').'</h3>

        <p>'.__('When enabled, the Dublin Core XML namespace for the <code>dcterms</code> prefix is added to the <code>html</code> element of the page. If your theme already contains this namespace, then this option should not be enabled.', 'add-meta-tags').'</p>';

    $screen->add_help_tab( array(
        'id'	=> 'amt_help_metadata_dublin_core',
        'title'	=> __('Dublin Core', 'add-meta-tags'),
        'content'	=> $help_text,
    ) );

    // Schema.org Generator
    $help_text = '
    <h3>'.__('Automatically generate and embed Schema.org Microdata.', 'add-meta-tags').'</h3>
    
    <p>'.__('Automatically generate Schema.org Microdata and embed it to your content. This feature embeds <code>meta</code> elements inside the body of the web page. This is compatible with the HTML 5 standard, so, before enabling it, make sure your theme is HTML 5 ready. For information about Microdata please refer to <a href="http://schema.org">Schema.org</a>.', 'add-meta-tags').'</p>

    <p>'.__('This metadata generator actually adds schema.org microdata around the post\'s content, because marking up the exact contents of the page in an automatic manner is technically impossible. Make no mistake, this is not the ideal way to insert microdata to your web pages. This generator should be considered as a workaround in case your theme lacks schema.org microdata markup in its templates and not as a replacement of schema.org enhanced themes.', 'add-meta-tags').'</p>

    <h3>'.__('Enforce the generation of schema.org metadata as JSON-LD data.', 'add-meta-tags').'</h3>

    <p>'.__('If this option is enabled, the schema.org metadata is generated as a JSON+LD script and added to the head section of the HTML page, instead of being added as microdata within the content.', 'add-meta-tags').'</p>

    <h3>'.__('Important Notes', 'add-meta-tags').'</h3>

    <p>'.__('By default, this feature links the author and publisher objects to the author archive and to the front page of your web site respectively. In order to link to the author\'s profile and publisher\'s page on Google+, it is required to provide the respective URLs. The profile URL of the publisher can be entered in this page under the <em>Publisher Settings</em> section. The profile URL of the author can be entered in the WordPress user <a href="profile.php">profile page</a> under the section <em>Contact Info</em>. For example:', 'add-meta-tags').'</p>
    <blockquote>'.__('Google+ author profile URL', 'add-meta-tags').' (AMT): <code>https://plus.google.com/+JohnSmith/</code></blockquote>
    <blockquote>'.__('Google+ publisher page URL', 'add-meta-tags').' (AMT): <code>https://plus.google.com/+AwesomeEditors/</code></blockquote>

    <p>'.__('Once you have filled in the URLs to the author profile and the publisher page on Google+, the relevant link elements with the attributes <code>rel="author"</code> and <code>rel="publisher"</code> are automatically added to the head area of the web page.', 'add-meta-tags').'</p>';
    $screen->add_help_tab( array(
        'id'	=> 'amt_help_metadata_schemaorg',
        'title'	=> __('Schema.org', 'add-meta-tags'),
        'content'	=> $help_text,
    ) );

    // Locale
    $help_text = '
    <h3>'.__('Enter a locale which will be used globally.', 'add-meta-tags').'</h3>
    <p>'.__('Enter a locale which will be used globally in the generated metadata overriding the default locale as returned by WordPress. Filling in this setting is only recommended if WordPress does not return a locale in the form of <code>language_TERRITORY</code>. This feature should not be used in conjunction with a multilingual plugin in order to avoid the potential generation of meta tags with invalid locale.', 'add-meta-tags').'</p>
    <p><strong>'.__('Example', 'add-meta-tags').'</strong>: <code>en_US</code></p>

    <h3>'.__('Automatically set the HTML document\'s <code>lang</code> attribute according to the locale of the content.', 'add-meta-tags').'</h3>

    <p>'.__('By enabling this feature, Add-Meta-Tags will automatically set the HTML document\'s <code>lang</code> attribute according to the content\'s locale. This feature should not be used in conjunction with a multilingual plugin in order to avoid the potential generation of an invalid <code>lang</code> attribute.', 'add-meta-tags').'</p>

    <h3>'.__('Enable the generation of a <code>link</code> element with the <code>hreflang</code> attribute.', 'add-meta-tags').'</h3>

    <p>'.__('If this feature is enabled, an HTML <code>link</code> element containing the proper hreflang attribute is added to the head section of the HTML page. The value of the hreflang attribute is determined by the locale of the content. This feature should not be used in conjunction with a multilingual plugin in order to avoid the potential generation of invalid hreflang links.', 'add-meta-tags').'</p>

    <h3>'.__('Strip region code from the hreflang attribute.', 'add-meta-tags').'</h3>

    <p>'.__('By default, Add-Meta-Tags uses the locale, which is usually required to be set in the form <code>language_TERRITORY</code> so as to comply with the various metadata specifications, as the value of the hreflang attribute. However, Google and possibly other services might interpret the regional information of the hreflang attribute as region targeting. If your content is not targeted to users in a specific region, it might be a good idea to strip regional information from this attribute by enabling this option.', 'add-meta-tags').'</p>
    <p>'.__('For instance, if your locale is <code>en_US</code>, by enabling this option you force the hreflang attribute to be <code>en</code>. In the same way, if the locale is in the form <code>language_Script_TERRITORY</code>, for example <code>zh_Hans_TW</code>, by enabling this option the hreflang attribute becomes <code>zh-Hans</code>.', 'add-meta-tags').'</p>';
    $screen->add_help_tab( array(
        'id'	=> 'amt_help_locale',
        'title'	=> __('Locale', 'add-meta-tags'),
        'content'	=> $help_text,
    ) );

    // Metabox features
    $help_text = '
    <p>'.__('It is possible to partially customize the generated metadata on a per post basis through the <em>Metadata</em> metabox which exists in the post editing screen. Below you can choose which metabox features should be enabled. Enabling or disabling these features has no effect on the custom data that has been stored for each post.', 'add-meta-tags').'</p>

    <ul>
    <li>'.__('Custom description.', 'add-meta-tags').' ('.__('Recommended', 'add-meta-tags').')</li>
    <li>'.__('Custom keywords.', 'add-meta-tags').' ('.__('Recommended', 'add-meta-tags').')</li>
    <li>'.__('Custom content of the <code>title</code> HTML element.', 'add-meta-tags').'</li>
    <li>'.__('Custom news keywords. (<a target="_blank" href="http://support.google.com/news/publisher/bin/answer.py?hl=en&answer=68297">more info</a>)', 'add-meta-tags').'</li>
    <li>'.__('Full meta tags box.', 'add-meta-tags').'</li>
    <li>'.__('Global image override.', 'add-meta-tags').'</li>
    <li>'.__('Content locale override. (Not to be used in conjunction with a multilingual plugin.) ', 'add-meta-tags').'</li>
    <li>'.__('Express review.', 'add-meta-tags').' ('.__('For advanced users only', 'add-meta-tags').')</li>
    <li>'.__('Referenced items.', 'add-meta-tags').' ('.__('Not recommended', 'add-meta-tags').')</li>
    </ul>
    <p>'.__('The metabox feature selection above affects all users. Advanced customization of the availability of these features on a per user basis or depending upon each user\'s permissions, is possible through the <code>amt_metadata_metabox_permissions</code> filter.', 'add-meta-tags').'</p>
    ';
    $screen->add_help_tab( array(
        'id'	=> 'amt_help_metabox_features',
        'title'	=> __('Metabox Features', 'add-meta-tags'),
        'content'	=> $help_text,
    ) );

    // Extra Settings
    $help_text = '
    <p>'.__('This section contains information about the general Add-Meta-Tags settings.', 'add-meta-tags').'</p>

    <h3>'.__('Add <code>NOODP</code> and <code>NOYDIR</code> to the <em>robots</em> meta tag.', 'add-meta-tags').'</h3>

    <p>'.__('If checked, <code>NOODP</code> and <code>NOYDIR</code> are added to the <em>robots</em> meta tag on the front page, content and attachments. This setting will prevent all search engines (at least those that support the meta tag) from displaying information from the <a href="http://www.dmoz.org/">Open Directory Project</a> or the <a href="http://dir.yahoo.com/">Yahoo Directory</a> instead of the description you set in the <em>description</em> meta tag.', 'add-meta-tags').'</p>

    <h3>'.__('Add <code>NOINDEX,FOLLOW</code> to the <em>robots</em> meta tag on following types of archives.', 'add-meta-tags').'</h2>

    <p>'.__('Add <code>NOINDEX,FOLLOW</code> to the <em>robots</em> meta tag on following types of archives: search results, date, tag, custom taxonomy, author archives. This is an advanced setting that aims at reducing the amount of duplicate content that gets indexed by search engines', 'add-meta-tags').'</p>

    <h3>'.__('Enforce the use of the content\'s custom title in the generated metadata.', 'add-meta-tags').'</h3>

    <p>'.__('It is possible to set a custom title for the content in the relevant field of the \'Metadata\' box in the post editing screen. By default, this custom title is used to modify the <code>title</code> HTML element only. Add-Meta-Tags still uses the default content title in the relevant generated meta tags, for example in <code>og:title</code>/<code>twitter:title</code>/<code>dcterms:title</code>. If this option is enabled, then Add-Meta-Tags uses the custom title of the content, if such a custom title has been set, in the the generated meta data, instead of the default title.', 'add-meta-tags').'</p>

    <h3>'.__('Enable advanced title management.', 'add-meta-tags').'</h3>

    <p>'.__('By enabling this option, Add-Meta-Tags generates the title that is used in the <code>title</code> HTML element and also the title that is used throughout the generated metadata according to user defined templates. The <em>Add-Meta-Tags Cookbook</em> has all the information you need about <a target="_blank"  href="http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Advanced_Title_Management">how to customize the generated titles</a>.', 'add-meta-tags').'</p>

    <p>'.__('Please make sure to check the <a href="http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Requirements#Advanced-Title-Management" target="_blank">theme requirements</a> before enabling this feature.', 'add-meta-tags').'</p>

    <h3>'.__('Copyright URL', 'add-meta-tags').'</h3>

    <p>'.__('Enter an absolute URL to a document containing copyright and licensing information about your work. If this URL is set, the relevant meta tags will be added automatically on all the pages of your web site.', 'add-meta-tags').'</p>
    <p><strong>'.__('Example', 'add-meta-tags').'</strong>: <code>http://example.org/copyright.html</code></p>

    <h3>'.__('Default Image', 'add-meta-tags').'</h3>

    <p>'.__('Enter an absolute URL to an image that represents your website, for instance the logo. This image will be used in the metadata of the front page and also in the metadata of the content, in case no featured image or other images have been attached or embedded. To specify the image dimensions you can use the special notation <code>URL,WIDTHxHEIGHT</code>.', 'add-meta-tags').'</p>
    <p><strong>'.__('Example', 'add-meta-tags').'</strong>: <code>http://example.org/images/logo.png</code></p>

    <h3>'.__('Shortcodes', 'add-meta-tags').'</h3>

    <p>'.__('Enforce the expansion of shortcodes before generating a description.', 'add-meta-tags').'</p>

    <h3>'.__('Secure Access', 'add-meta-tags').'</h3>

    <p>'.__('Media are accessible over HTTPS.', 'add-meta-tags').'</p>

    <p>'.__('Currently this option, if enabled, lets the plugin make decisions about whether to generate additional secure links even if the active connection does not use HTTPS. For instance, if the web site is accessed over HTTP and this options is enabled, additional <code>og:image:secure_url</code> meta tags will be generated for your local media. If the current connection uses HTTPS, then secure links are always generated.', 'add-meta-tags').'</p>

    <h3>'.__('Media Limit', 'add-meta-tags').'</h3>

    <p>'.__('Do not generate metadata for more than one media file of each type (image, video, audio).', 'add-meta-tags').'</p>

    <p>'.__('By default, metadata is generated for all media files that have been attached or embedded in the content. By enabling this option Add-Meta-Tags will generate metadata only for the first media file of each type (image, video, audio) it encounters. This limit does not affect the <code>gallery</code> Twitter Card, which always contains all the attached images.', 'add-meta-tags').'</p>

    <h3>'.__('Review Mode', 'add-meta-tags').'</h3>

    <p>'.__('If enabled, WordPress users with administrator privileges see a box (right above the post\'s content) containing the metadata exactly as it is added in the HTML head and body. The box is displayed for posts, pages, attachments and custom post types.', 'add-meta-tags').'</p>

    <h3>'.__('Enable keyword analysis and statistics', 'add-meta-tags').'</h3>

    <p>'.__('If enabled, a section containing <a href="http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Metadata_Overview">statistical information</a> about the metadata and the content is also displayed in the review mode panel.', 'add-meta-tags').'</p>

    <h3>'.__('Omit help messages and notices', 'add-meta-tags').'</h3>

    <p>'.__('If enabled, help messages and other notices are no longer displayed in the metadata review panel.', 'add-meta-tags').'</p>

    ';
    $screen->add_help_tab( array(
        'id'	=> 'amt_help_extra',
        'title'	=> __('Extra settings', 'add-meta-tags'),
        'content'	=> $help_text,
    ) );

    // Extended Metadata
    $help_text = '
    <p>'.__('This section contains information about the metadata Add-Meta-Tags can generate for third party content.', 'add-meta-tags').'</p>

    <h3>'.__('Metadata for WooCommerce', 'add-meta-tags').'</h3>

    <p>'.__('Add-Meta-Tags can generate metadata for WooCommerce products and product groups. The Basic, Opengraph, Twitter Cards, Schema.org microdata and Schema.org JSON+LD metadata generators have been implemented.', 'add-meta-tags').'</p>
    <p>'.__('The generated metadata is complete and supports most of the properties of products and offers, except shipping related information. Product attributes are also supported. In some cases, you will have to override the associations between the names of product attributes the plugin expects and the names of product attributes that are actually used.', 'add-meta-tags').'</p>
    <p>'.__('Products with regular and sales prices are supported. Variable products and pages of product groups are also supported.', 'add-meta-tags').'</p>
    <p>'.__('The metadata generators for WooCommerce products should be considered stable. However, your comments and suggestions are welcome.', 'add-meta-tags').'</p>

    <h3>'.__('Metadata for Easy Digital Downloads', 'add-meta-tags').'</h3>

    <p>'.__('Support for the products and product groups of the Easy Digital Downloads plugin is still work in progress. If enabled, product and product group pages will contain basic product related metadata.', 'add-meta-tags').'</p>

    <h3>'.__('Metadata for BuddyPress', 'add-meta-tags').'</h3>

    <p>'.__('Add-Meta-Tags can generate metadata for BuddyPress member profile pages. The Basic, Opengraph, Twitter Cards, Schema.org microdata and Schema.org JSON+LD metadata generators have been implemented.', 'add-meta-tags').'</p>

    <p>'.__('If the <em>Extended Profiles</em> component has not been activated in BuddyPress, then metadata is generated based on the information found in the WordPress user profile.', 'add-meta-tags').'</p>

    <p>'.__('Please consult the <a href="http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Custom_Content#Metadata-for-BuddyPress" target="_blank">wiki documentation about BuddyPress</a> for more information about how to modify or extend the generated metadata, override the default field map or prevent profile metadata from being added to the profile page.', 'add-meta-tags').'</p>

    <p>'.__('How to fully take advantage of this feature:', 'add-meta-tags').'</p>

    <ol>
    <li>'.__('Enable BuddyPress support in the Add-Meta-Tags settings.', 'add-meta-tags').'</li>
    <li>'.__('Set the source of local author profiles to <code>BuddyPress</code> in the Add-Meta-Tags settings.', 'add-meta-tags').' (<em>'.__('Optional', 'add-meta-tags').'</em>)</li>
    <li>'.__('Use the <code>amt_local_author_profile_url()</code> template tag in your theme templates in order to generate a URL to the author\'s profile. This template tag takes your selection about the source of local author profiles into account.', 'add-meta-tags').' (<em>'.__('Optional', 'add-meta-tags').'</em>)</li>
    </ol>

    <p>'.__('This is a new feature and should be considered work in progress.', 'add-meta-tags').'</p>

    <h3>'.__('Metadata for bbPress', 'add-meta-tags').'</h3>

    <p>'.__('Metadata for bbPress forums and topics should be generated out of the box. More specific forum metadata has not been implemented yet.', 'add-meta-tags').'</p>

    <p>'.__('Currently, only the base mechanism exists, which lets you <a href="http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Custom_Content#Metadata-for-BuddyPress" target="_blank">modify or extend</a> metadata for bbPress forums and topics.', 'add-meta-tags').'</p>

    <p>'.__('Please, don\'t forget to contribute your customizations to this project!', 'add-meta-tags').'</p>

    ';
    $screen->add_help_tab( array(
        'id'	=> 'amt_help_extended_metadata',
        'title'	=> __('Extended Metadata', 'add-meta-tags'),
        'content'	=> $help_text,
    ) );

    // Metadata caching
    $help_text = '
    <p>'.__('This section contains basic information about the <em>metadata caching</em> feature. For more details about how metadata caching works in Add-Meta-Tags, troubleshooting notes and also helpful information that aims to help you decide whether you need to enable this feature or not can be found in the <a target="_blank" href="http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Performance#Metadata-Caching">Performance</a> page of the wiki.', 'add-meta-tags').'</p>

    <p>'.__('Metadata caching is the last thing you should care about while configuring this plugin. You should consider enabling it only after you have finished configuring the rest of the settings.', 'add-meta-tags').'</p>

    <h3>'.__('Notice', 'add-meta-tags').'</h3>

    <p>'.__('Metadata caching is an experimental feature, which should only be used by experienced WordPress users. If you are a WordPress beginner or if you don\'t have enough knowledge about the concept of caching and, especially, if you have followed instructions without having understood how this works, please keep this feature turned off, read this section of the documentation and also the <a target="_blank" href="http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Performance#Metadata-Caching">Performance</a> page of the wiki carefully and ask any questions you might have before enabling it. Even if you do otherwise, your web site is not going to be harmed in any way, but it is always a good idea to have a good understanding about how things work.', 'add-meta-tags').'</p>

    <h3>'.__('Show timing information', 'add-meta-tags').'</h3>

    <p>'.__('If this option is enabled, information about the total time that was required to generate a block of metadata is printed. This feature, generally, should not be enabled, unless you want an indication of the metadata generation times in each request.', 'add-meta-tags').'</p>

    <h3>'.__('Enable metadata caching', 'add-meta-tags').'</h3>

    <p>'.__('Metadata caching can be enabled by setting the <em>caching timeout</em>, which is the number of seconds for which the metadata should be cached, to a number greater than zero. The cache is cleared every time you save the Add-Meta-Tags settings or from the command line using the <code>amt</code> command of <code>wp-cli</code>.', 'add-meta-tags').'</p>

    <p>'.__('The full documentation of this feature has been moved to the <a target="_blank" href="http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Performance#Metadata-Caching">Performance</a> page of the wiki. It is highly recommended to thoroughly study this document before enabling metadata caching.', 'add-meta-tags').'</p>

    ';
    if ( apply_filters('amt_enable_metadata_cache', true) ) {
        $screen->add_help_tab( array(
            'id'	=> 'amt_help_metadata_caching',
            'title'	=> __('Metadata caching', 'add-meta-tags'),
            'content'	=> $help_text,
        ) );
    }

    // Advanced Customization
    $help_text = '
    <p>'.__('Add-Meta-Tags can be customized to a great extent programmatically. Please read the <a href="http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Add-Meta-Tags_Cookbook" target="_blank">Add-Meta-Tags Cookbook</a> for more information.', 'add-meta-tags').'</p>
    ';
    $screen->add_help_tab( array(
        'id'	=> 'amt_help_advanced',
        'title'	=> __('Advanced', 'add-meta-tags'),
        'content'	=> $help_text,
    ) );

}


/**
 * Function that adds the help tabs for the options.
 */
function amt_options_page() {
    // Permission Check
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }

    // Default Add-Meta-Tags Settings
    $default_options = amt_get_default_options();

    if (isset($_POST['info_update'])) {

        // Save the Add-Meta-Tags settings
        amt_save_settings($_POST);

        // Also, since it may happen that the rewrite rules have not been flushed
        // this is a good place to do it.
        flush_rewrite_rules();

        // Also, clear the metadata cache automatically on every save.
        $result = amt_delete_all_transient_metadata_cache();
        if ( intval($result) > 0 ) {
            amt_show_info_msg( sprintf(__('Deleted %d transient metadata cache entries.', 'add-meta-tags'), $result) );
        }

    } elseif (isset($_POST["info_reset"])) {

        amt_reset_settings();

    }

    // Get the options from the DB.
    $options = get_option("add_meta_tags_opts");
    //var_dump($options);

    /*
    Configuration Page
    */

    print('
    <!-- #add-meta-tags-settings is required by the media selector -->
    <span id="add-meta-tags-settings">    
    ');

    print('
    <div class="wrap">
        <div id="icon-options-general" class="icon32"><br /></div>
        <h2>'.__('Metadata Settings', 'add-meta-tags').'</h2>

        <p>'.__('Welcome to the administration panel of the Add-Meta-Tags plugin.', 'add-meta-tags').'</p>

    </div>

    <div class="wrap amt-settings-notice">

        <!-- <h3>'.__('Notice', 'add-meta-tags').'</h3> -->

        <p>'.__('Press the <code>Help</code> button on the top right corner for an introduction to metadata and also for detailed <strong>documentation</strong> about the available settings and the metadata generators. All help texts will gradually be moved to this integrated WordPress help system in order to reduce the size of this page and make it more user-friendly.', 'add-meta-tags').'</p>

    </div>

    <!--
    <div class="wrap amt-settings-notice">

        <p>'.__('It is no longer possible to enter the URLs of the Publisher\'s social media profiles in the WordPress user profile page. Instead, this information should be entered in the relevant fields of the <strong>Publisher Settings</strong> section below.', 'add-meta-tags').'</p>

    </div>
    -->

    <div class="wrap">
        <h2>'.__('Configuration', 'add-meta-tags').'</h2>

        <p>'.__('This section contains global configuration options for the metadata that is added to your web site.', 'add-meta-tags').'</p>

        <form name="formamt" method="post" action="' . admin_url( 'options-general.php?page=add-meta-tags-options' ) . '">

        <table class="form-table">
        <tbody>
    ');


    // General Settings

    if ( amt_has_page_on_front() ) {

        /* Options:

            Example No pages
            +-----------+----------------+--------------+----------+
            | option_id | option_name    | option_value | autoload |
            +-----------+----------------+--------------+----------+
            |        58 | show_on_front  | posts        | yes      |
            |        93 | page_for_posts | 0            | yes      |
            |        94 | page_on_front  | 0            | yes      |
            +-----------+----------------+--------------+----------+

            Example pages as front page and posts page
            +-----------+----------------+--------------+----------+
            | option_id | option_name    | option_value | autoload |
            +-----------+----------------+--------------+----------+
            |        58 | show_on_front  | page         | yes      |
            |        93 | page_for_posts | 28           | yes      |
            |        94 | page_on_front  | 25           | yes      |
            +-----------+----------------+--------------+----------+

        */
        print('
            <tr valign="top">
            <th scope="row">'.__('Front Page Metadata', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Front Page Metadata', 'add-meta-tags').'</span></legend>
                '.__('It appears that you use static pages on the <em>front page</em> and the <em>latest posts page</em> of this web site. Please visit the editing panel of these pages and set the <code>description</code> and the <code>keywords</code> meta tags in the relevant Metadata box.', 'add-meta-tags').'
                ');
                print('<ul>');
                $front_page_id = get_option('page_on_front');
                if ( intval($front_page_id) > 0 ) {
                    printf( '<li>&raquo; '.__('Edit the <a href="%s">front page</a>', 'add-meta-tags').'</li>', get_edit_post_link(intval($front_page_id)) );
                }
                $posts_page_id = get_option('page_for_posts');
                if ( intval($posts_page_id) > 0 ) {
                    printf( '<li>&raquo; '.__('Edit the <a href="%s">posts page</a>', 'add-meta-tags').'</li>', get_edit_post_link(intval($posts_page_id)) );
                }
                print('</ul>');
        print('
            </fieldset>
            </td>
            </tr>
        ');

    } else {

        print('
            <tr valign="top">
            <th scope="row">'.__('Front Page Description', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Front Page Description', 'add-meta-tags').'</span></legend>
                
                <textarea name="site_description" id="site_description" cols="100" rows="2" class="code">' . esc_attr( stripslashes( amt_get_site_description($options) ) ) . '</textarea>
                <br />
                <label for="site_description">
                    '.__('Enter a short description of your web site.', 'add-meta-tags').'
                </label>
                <br />
            </fieldset>
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('Front Page Keywords', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Front Page Keywords', 'add-meta-tags').'</span></legend>
                
                <textarea name="site_keywords" id="site_keywords" cols="100" rows="2" class="code">' . esc_attr( stripslashes( amt_get_site_keywords($options) ) ) . '</textarea>
                <br />
                <label for="site_keywords">'.__('Enter a comma-delimited list of keywords for your web site.', 'add-meta-tags').'</label>
                <br />
            </fieldset>
            </td>
            </tr>
        ');
    }

    print('
            <tr valign="top">
            <th scope="row">'.__('Global Keywords', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Global Keywords', 'add-meta-tags').'</span></legend>
                
                <textarea name="global_keywords" id="global_keywords" cols="100" rows="2" class="code">' . esc_attr( stripslashes( amt_get_site_global_keywords($options) ) ) . '</textarea>
                <br />
                <label for="global_keywords">'.__('Enter a comma-delimited list of global keywords. Please consult the integrated help for more information.', 'add-meta-tags').'</label>
                <br />
            </fieldset>
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('Site-wide META tags', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Site-wide META tags', 'add-meta-tags').'</span></legend>

                <textarea name="site_wide_meta" id="site_wide_meta" cols="100" rows="10" class="code">' . stripslashes( $options["site_wide_meta"] ) . '</textarea>
                <br />
                <label for="site_wide_meta">
                '.__('Enter complete <a href="http://en.wikipedia.org/wiki/Meta_element" target="_blank">meta tags</a> which will appear on all web pages.', 'add-meta-tags').'
                '.__('Please, do not enter keywords here.', 'add-meta-tags').'
                </label>
                <br />
            </fieldset>
            </td>
            </tr>

    ');

    // Publisher Settings

    print('
            <tr valign="top">
            <th scope="row">'.__('Publisher Settings', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Publisher Settings', 'add-meta-tags').'</span></legend>

                <p>'.__('This section contains options related to your web site; the publisher of the content.', 'add-meta-tags').'</p>
                <p>'.__('The following publisher related settings are shared among all users. Filling in these settings is entirely optional.', 'add-meta-tags').'</p>

                <h4>'.__('Facebook publisher profile URL', 'add-meta-tags').':</h4>

                <input name="social_main_facebook_publisher_profile_url" type="text" id="social_main_facebook_publisher_profile_url" class="code" value="' . esc_url_raw( stripslashes( $options["social_main_facebook_publisher_profile_url"] ) ) . '" size="100" maxlength="1024" />
                <br />
                <label for="social_main_facebook_publisher_profile_url">
                '.__('Enter an absolute URL to the Facebook profile of the publisher. If this is filled in, it will be used in the <code>article:publisher</code> meta tag.', 'add-meta-tags').'
                </label>
                <p><strong>'.__('Example', 'add-meta-tags').'</strong>: <code>https://www.facebook.com/awesome.editors</code></p>
                <br />

                <!-- We currently let users add the full meta tags for fb:app_id and fb:admins in the site wide meta tags box.

                <h4>'.__('Facebook App ID', 'add-meta-tags').':</h4>

                <input name="social_main_facebook_app_id" type="text" id="social_main_facebook_app_id" class="code" value=" . esc_url_raw( stripslashes( $options["social_main_facebook_app_id"] ) ) . " size="100" maxlength="1024" />
                <br />
                <label for="social_main_facebook_app_id">
                '.__('Enter the App ID for <a target="_blank" href="https://developers.facebook.com/docs/platforminsights">Facebook Insights</a>.', 'add-meta-tags').'
                </label>
                <p><strong>'.__('Example', 'add-meta-tags').'</strong>: <code>1234567890</code></p>
                <br />

                <h4>'.__('Facebook Admin ID list', 'add-meta-tags').':</h4>

                <input name="social_main_facebook_admins" type="text" id="social_main_facebook_admins" class="code" value=" . esc_url_raw( stripslashes( $options["social_main_facebook_admins"] ) ) . " size="100" maxlength="1024" />
                <br />
                <label for="social_main_facebook_admins">
                '.__('Enter a comma delimited list of numerical Facebook user IDs which will have access to <a target="_blank" href="https://developers.facebook.com/docs/platforminsights">Facebook Insights</a>.', 'add-meta-tags').'
                </label>
                <p><strong>'.__('Example', 'add-meta-tags').'</strong>: <code>1111111111,2222222222</code></p>
                <br />

                -->

                <h4>'.__('Google+ publisher profile URL', 'add-meta-tags').':</h4>

                <input name="social_main_googleplus_publisher_profile_url" type="text" id="social_main_googleplus_publisher_profile_url" class="code" value="' . esc_url_raw( stripslashes( $options["social_main_googleplus_publisher_profile_url"] ) ) . '" size="100" maxlength="1024" />
                <br />
                <label for="social_main_googleplus_publisher_profile_url">
                '.__('Enter an absolute URL to the Google+ profile of the publisher. If this is filled in, it will be used in the link with <code>rel="publisher"</code> in the HEAD area of the web page.', 'add-meta-tags').'
                </label>
                <p><strong>'.__('Example', 'add-meta-tags').'</strong>: <code>https://plus.google.com/+AwesomeEditors/</code></p>
                <br />

                <h4>'.__('Twitter publisher username', 'add-meta-tags').':</h4>

                <input name="social_main_twitter_publisher_username" type="text" id="social_main_twitter_publisher_username" class="code" value="' . esc_attr( stripslashes( $options["social_main_twitter_publisher_username"] ) ) . '" size="100" maxlength="1024" />
                <br />
                <label for="social_main_twitter_publisher_username">
                '.__('Enter the Twitter username of the publisher (without @).', 'add-meta-tags').'
                </label>
                <p><strong>'.__('Example', 'add-meta-tags').'</strong>: <code>AwesomeEditors</code></p>
                <br />

            </fieldset>
            </td>
            </tr>
    ');

    // Author Settings


    print('
            <tr valign="top">
            <th scope="row">'.__('Author Settings', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Author Settings', 'add-meta-tags').'</span></legend>

                <p>'.__('This section contains options related to the authors of the content.', 'add-meta-tags').'</p>

                <h4>'.__('URLs of author profiles on social networks', 'add-meta-tags').':</h4>

                <p>'.__('Each author may fill in the the URLs of the profiles on external social networks in the WordPress user profile page (fields marked with <em>AMT</em>).', 'add-meta-tags').'</p>

                <h4>'.__('Main source of local author profiles', 'add-meta-tags').':</h4>

                <select name="author_profile_source" type="text" id="author_profile_source" class="code">
                    <option '. (($options["author_profile_source"]=="default") ? 'selected="selected"' : '') .' value="default">'.__('First page of the author archive', 'add-meta-tags').' '.__('(default)', 'add-meta-tags').' &mdash; '.__('Organization web site', 'add-meta-tags').'</option>
                    <option '. (($options["author_profile_source"]=="frontpage") ? 'selected="selected"' : '') .' value="frontpage">'.__('Front page (with static page only)', 'add-meta-tags').' &mdash; '.__('Personal web site', 'add-meta-tags').'</option>
                    <option '. (($options["author_profile_source"]=="buddypress") ? 'selected="selected"' : '') .' value="buddypress">'.__('BuddyPress member page', 'add-meta-tags').' &mdash; '.__('Organization web site', 'add-meta-tags').'</option>
                    <option '. (($options["author_profile_source"]=="url") ? 'selected="selected"' : '') .' value="url">'.__('Author\'s external web site URL', 'add-meta-tags').' &mdash; '.__('Organization web site', 'add-meta-tags').'</option>
                </select> 

                <br /><br />
                <p>'.__('This is an advanced option and should not be changed, unless you have consulted the integrated help for more information.', 'add-meta-tags').'</p>
                <br />

            </fieldset>
            </td>
            </tr>
    ');


    // Basic Metadata

    print('
            <tr valign="top">
            <th scope="row">'.__('Basic Metadata', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Basic Metadata', 'add-meta-tags').'</span></legend>

                <input id="auto_description" type="checkbox" value="1" name="auto_description" '. (($options["auto_description"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="auto_description">'.__('Automatically generate the <em>description</em> meta tag.', 'add-meta-tags').'</label>
                <br />

                <input id="auto_keywords" type="checkbox" value="1" name="auto_keywords" '. (($options["auto_keywords"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="auto_keywords">'.__('Automatically generate the <em>keywords</em> meta tag.', 'add-meta-tags').'</label>
                <br />

            </fieldset>
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('Opengraph Metadata', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Opengraph Metadata', 'add-meta-tags').'</span></legend>

                <input id="auto_opengraph" type="checkbox" value="1" name="auto_opengraph" '. (($options["auto_opengraph"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="auto_opengraph">'.__('Automatically generate Opengraph meta tags.', 'add-meta-tags').'</label>
                <br />

                <input id="og_add_xml_namespaces" type="checkbox" value="1" name="og_add_xml_namespaces" '. (($options["og_add_xml_namespaces"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="og_add_xml_namespaces">'.__('Add Facebook\'s XML namespaces.', 'add-meta-tags').'</label>
                <br />

                <input id="og_omit_video_metadata" type="checkbox" value="1" name="og_omit_video_metadata" '. (($options["og_omit_video_metadata"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="og_omit_video_metadata">'.__('Omit <code>og:video</code> meta tags.', 'add-meta-tags').'</label>
                <br />

            </fieldset>
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('Twitter Cards Metadata', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Twitter Cards Metadata', 'add-meta-tags').'</span></legend>

                <input id="auto_twitter" type="checkbox" value="1" name="auto_twitter" '. (($options["auto_twitter"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="auto_twitter">'.__('Automatically generate Twitter Cards meta tags.', 'add-meta-tags').'</label>
                <br />

                <input id="tc_enforce_summary_large_image" type="checkbox" value="1" name="tc_enforce_summary_large_image" '. (($options["tc_enforce_summary_large_image"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="tc_enforce_summary_large_image">'.__('Enforce the use of <code>summary_large_image</code> as default card type.', 'add-meta-tags').'</label>
                <br />

                <input id="tc_enable_player_card_local" type="checkbox" value="1" name="tc_enable_player_card_local" '. (($options["tc_enable_player_card_local"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="tc_enable_player_card_local">'.__('Enable the generation of <em>player</em> cards for local media.', 'add-meta-tags').'</label>
                <br />

            </fieldset>
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('Dublin Core Metadata', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Dublin Core Metadata', 'add-meta-tags').'</span></legend>

                <input id="auto_dublincore" type="checkbox" value="1" name="auto_dublincore" '. (($options["auto_dublincore"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="auto_dublincore">'.__('Automatically generate Dublin Core metadata.', 'add-meta-tags').'</label>
                <br />

                <input id="dc_add_xml_namespaces" type="checkbox" value="1" name="dc_add_xml_namespaces" '. (($options["dc_add_xml_namespaces"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="dc_add_xml_namespaces">'.__('Add the Dublin Core XML namespace.', 'add-meta-tags').'</label>
                <br />

            </fieldset>
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('Schema.org Metadata', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Schema.org Metadata', 'add-meta-tags').'</span></legend>

                <input id="auto_schemaorg" type="checkbox" value="1" name="auto_schemaorg" '. (($options["auto_schemaorg"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="auto_schemaorg">'.__('Automatically generate and embed Schema.org Microdata.', 'add-meta-tags').'</label>
                <br />

                <input id="schemaorg_force_jsonld" type="checkbox" value="1" name="schemaorg_force_jsonld" '. (($options["schemaorg_force_jsonld"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="schemaorg_force_jsonld">'.__('Enforce the generation of schema.org metadata as JSON-LD data.', 'add-meta-tags').'</label>
                <br />

            </fieldset>
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('Locale', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Locale', 'add-meta-tags').'</span></legend>

                <input name="global_locale" type="text" id="global_locale" class="code" value="' . esc_attr( stripslashes( $options["global_locale"] ) ) . '" size="20" maxlength="32" />
                <br />
                <label for="global_locale">
                '.__('Enter a locale, for example <code>en_US</code> or <code>zh_Hans_TW</code>, which will be used globally in the generated metadata overriding the default locale as returned by WordPress.', 'add-meta-tags').'
                '.__('The locale can be overridden on a per post basis. For this purpose, enabling the relevant metabox feature below is required.', 'add-meta-tags').'
                </label>
                <br />

                <input id="manage_html_lang_attribute" type="checkbox" value="1" name="manage_html_lang_attribute" '. (($options["manage_html_lang_attribute"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="manage_html_lang_attribute">'.__('Automatically set the HTML document\'s <code>lang</code> attribute according to the locale of the content.', 'add-meta-tags').'</label>
                <br />

                <input id="generate_hreflang_links" type="checkbox" value="1" name="generate_hreflang_links" '. (($options["generate_hreflang_links"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="generate_hreflang_links">'.__('Enable the generation of a <code>link</code> element with the <code>hreflang</code> attribute.', 'add-meta-tags').'</label>
                <br />

                <input id="hreflang_strip_region" type="checkbox" value="1" name="hreflang_strip_region" '. (($options["hreflang_strip_region"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="hreflang_strip_region">'.__('Strip region code from the hreflang attribute.', 'add-meta-tags').'</label>
                <br />

            </fieldset>
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('Metabox Features', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Metabox Features', 'add-meta-tags').'</span></legend>

                <p>'.__('It is possible to partially customize the generated metadata on a per post basis through the <em>Metadata</em> metabox which exists in the post editing screen. Below you can choose which metabox features should be enabled. Enabling or disabling these features has no effect on the custom data that has been stored for each post.', 'add-meta-tags').'</p>

                <p><input id="metabox_enable_description" type="checkbox" value="1" name="metabox_enable_description" '. (($options["metabox_enable_description"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="metabox_enable_description">
                '.__('Custom description.', 'add-meta-tags').' ('.__('Recommended', 'add-meta-tags').')
                </label></p>

                <p><input id="metabox_enable_keywords" type="checkbox" value="1" name="metabox_enable_keywords" '. (($options["metabox_enable_keywords"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="metabox_enable_keywords">
                '.__('Custom keywords.', 'add-meta-tags').' ('.__('Recommended', 'add-meta-tags').')
                </label></p>

                <p><input id="metabox_enable_title" type="checkbox" value="1" name="metabox_enable_title" '. (($options["metabox_enable_title"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="metabox_enable_title">
                '.__('Custom content of the <code>title</code> HTML element.', 'add-meta-tags').'
                </label></p>

                <p><input id="metabox_enable_news_keywords" type="checkbox" value="1" name="metabox_enable_news_keywords" '. (($options["metabox_enable_news_keywords"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="metabox_enable_news_keywords">
                '.__('Custom news keywords. (<a target="_blank" href="http://support.google.com/news/publisher/bin/answer.py?hl=en&answer=68297">more info</a>)', 'add-meta-tags').'
                </label></p>

                <p><input id="metabox_enable_full_metatags" type="checkbox" value="1" name="metabox_enable_full_metatags" '. (($options["metabox_enable_full_metatags"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="metabox_enable_full_metatags">
                '.__('Full meta tags box.', 'add-meta-tags').'
                </label></p>

                <p><input id="metabox_enable_image_url" type="checkbox" value="1" name="metabox_enable_image_url" '. (($options["metabox_enable_image_url"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="metabox_enable_image_url">
                '.__('Global image override.', 'add-meta-tags').'
                </label></p>

                <p><input id="metabox_enable_content_locale" type="checkbox" value="1" name="metabox_enable_content_locale" '. (($options["metabox_enable_content_locale"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="metabox_enable_content_locale">
                '.__('Content locale override. (Not to be used in conjunction with a multilingual plugin.) ', 'add-meta-tags').'
                </label></p>

                <p><input id="metabox_enable_express_review" type="checkbox" value="1" name="metabox_enable_express_review" '. (($options["metabox_enable_express_review"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="metabox_enable_express_review">
                '.__('Express review.', 'add-meta-tags').' ('.__('For advanced users only', 'add-meta-tags').')
                </label></p>

                <p><input id="metabox_enable_referenced_list" type="checkbox" value="1" name="metabox_enable_referenced_list" '. (($options["metabox_enable_referenced_list"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="metabox_enable_referenced_list">
                '.__('Referenced items.', 'add-meta-tags').' ('.__('Not recommended', 'add-meta-tags').')
                </label></p>

            ');

            // The term meta API was implemented in 4.4
            if ( version_compare( get_bloginfo('version'), '4.4', '>=' ) ) {
            
                print('
                    <h4>'.__('Taxonomy terms', 'add-meta-tags').':</h4>

                    <p><input id="metabox_term_enable_full_metatags" type="checkbox" value="1" name="metabox_term_enable_full_metatags" '. (($options["metabox_term_enable_full_metatags"]=="1") ? 'checked="checked"' : '') .'" />
                    <label for="metabox_term_enable_full_metatags">
                    '.__('Full meta tags box.', 'add-meta-tags').'
                    </label></p>

                    <p><input id="metabox_term_enable_image_url" type="checkbox" value="1" name="metabox_term_enable_image_url" '. (($options["metabox_term_enable_image_url"]=="1") ? 'checked="checked"' : '') .'" />
                    <label for="metabox_term_enable_image_url">
                    '.__('Global image override.', 'add-meta-tags').'
                    </label></p>

                ');

            }

            print('

                <h4>'.__('WordPress user profiles', 'add-meta-tags').':</h4>

                <p><input id="metabox_user_enable_full_metatags" type="checkbox" value="1" name="metabox_user_enable_full_metatags" '. (($options["metabox_user_enable_full_metatags"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="metabox_user_enable_full_metatags">
                '.__('Full meta tags box.', 'add-meta-tags').'
                </label></p>

                <p><input id="metabox_user_enable_image_url" type="checkbox" value="1" name="metabox_user_enable_image_url" '. (($options["metabox_user_enable_image_url"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="metabox_user_enable_image_url">
                '.__('Global image override.', 'add-meta-tags').'
                </label></p>

                <br />

            </fieldset>
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('Extra SEO Options', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Extra SEO Options', 'add-meta-tags').'</span></legend>

                <input id="noodp_description" type="checkbox" value="1" name="noodp_description" '. (($options["noodp_description"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="noodp_description">'.__('Add <code>NOODP</code> and <code>NOYDIR</code> to the <em>robots</em> meta tag.', 'add-meta-tags').'</label>
                <br />
                <br />

                <p>'.__('Add <code>NOINDEX,FOLLOW</code> to the <em>robots</em> meta tag on following types of archives:', 'add-meta-tags').'</p>

                <p><input id="noindex_search_results" type="checkbox" value="1" name="noindex_search_results" '. (($options["noindex_search_results"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="noindex_search_results">
                '.__('Search results.', 'add-meta-tags').' ('.__('Highly recommended', 'add-meta-tags').')
                </label></p>

                <p><input id="noindex_date_archives" type="checkbox" value="1" name="noindex_date_archives" '. (($options["noindex_date_archives"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="noindex_date_archives">
                '.__('Date based archives.', 'add-meta-tags').' ('.__('Recommended', 'add-meta-tags').')
                </label></p>

                <p><input id="noindex_category_archives" type="checkbox" value="1" name="noindex_category_archives" '. (($options["noindex_category_archives"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="noindex_category_archives">
                '.__('Category based archives.', 'add-meta-tags').' ('.__('Even if checked, the first page of this type of archive is always indexed.', 'add-meta-tags').')
                </label></p>

                <p><input id="noindex_tag_archives" type="checkbox" value="1" name="noindex_tag_archives" '. (($options["noindex_tag_archives"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="noindex_tag_archives">
                '.__('Tag based archives.', 'add-meta-tags').' ('.__('Even if checked, the first page of this type of archive is always indexed.', 'add-meta-tags').')
                </label></p>

                <p><input id="noindex_taxonomy_archives" type="checkbox" value="1" name="noindex_taxonomy_archives" '. (($options["noindex_taxonomy_archives"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="noindex_taxonomy_archives">
                '.__('Custom taxonomy based archives.', 'add-meta-tags').' ('.__('Even if checked, the first page of this type of archive is always indexed.', 'add-meta-tags').')
                </label></p>

                <p><input id="noindex_author_archives" type="checkbox" value="1" name="noindex_author_archives" '. (($options["noindex_author_archives"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="noindex_author_archives">
                '.__('Author based archives.', 'add-meta-tags').' ('.__('Even if checked, the first page of this type of archive is always indexed.', 'add-meta-tags').')
                </label></p>

                <br />

                <p><input id="enforce_custom_title_in_metadata" type="checkbox" value="1" name="enforce_custom_title_in_metadata" '. (($options["enforce_custom_title_in_metadata"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="enforce_custom_title_in_metadata">
                '.__('Enforce the use of the content\'s custom title in the generated metadata.', 'add-meta-tags').'
                </label></p>

                <br />

                <p><input id="enable_advanced_title_management" type="checkbox" value="1" name="enable_advanced_title_management" '. (($options["enable_advanced_title_management"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="enable_advanced_title_management">
                '.__('Enable advanced title management.', 'add-meta-tags').'
                (<a target="_blank" href="http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Advanced_Title_Management">'.__('Learn more', 'add-meta-tags').'</a>)
                </label>

                <br />
            </fieldset>
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('Copyright URL', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Copyright URL', 'add-meta-tags').'</span></legend>

                <input name="copyright_url" type="text" id="copyright_url" class="code" value="' . esc_url( stripslashes( amt_get_site_copyright_url($options) ) ) . '" size="100" maxlength="1024" />
                <br />
                <label for="copyright_url">
                '.__('Enter an absolute URL to a document containing copyright and licensing information about your work.', 'add-meta-tags').'
                </label>
                <br />
            </fieldset>
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('Default Image', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Default Image', 'add-meta-tags').'</span></legend>

                <input name="default_image_url" type="text" id="default_image_url" class="code" value="' . amt_esc_id_or_url_notation( stripslashes( $options["default_image_url"] ) ) . '" size="100" maxlength="1024" />
                <br />

                <span id="amt-default-image-selector-button" class="amt-default-image-selector-button wp-media-buttons-icon loadmediawindow button updatemeta button-small">Select image</span>
                <br />

                <label for="default_image_url">
                '.__('Enter an absolute URL to an image that represents your website, for instance the logo. To specify the image dimensions you can use the special notation <code>URL,WIDTHxHEIGHT</code>.', 'add-meta-tags').'
                </label>
                <br />
        ');

            // Default image preview
            $image_data = amt_get_default_image_data();
            $img_html = '';
            if ( is_numeric($image_data['id']) ) {
                $main_size_meta = wp_get_attachment_image_src( $image_data['id'], 'medium' );
                $img_html = '<img src="' . esc_url($main_size_meta[0]) . '" width="' . esc_attr($main_size_meta[1]) . '" height="' . esc_attr($main_size_meta[2]) . '" />';
            } elseif ( ! is_null($image_data['url']) ) {
                $img_html = '<img src="' . esc_url($image_data['url']) . '" width="' . esc_attr($image_data['width']) . '" height="' . esc_attr($image_data['height']) . '" />';
            }
            if ( ! empty($img_html) ) {
                print('
                <p>'.__('Image preview', 'add-meta-tags').':</p>
                <br />
                <div id="amt-image-preview" class="amt-image-preview">' . $img_html . '</div>
                ');
            }

        print('
            </fieldset>
            </td>
            </tr>


            <tr valign="top">
            <th scope="row">'.__('Shortcodes', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Shortcodes', 'add-meta-tags').'</span></legend>

                <input id="expand_shortcodes" type="checkbox" value="1" name="expand_shortcodes" '. (($options["expand_shortcodes"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="expand_shortcodes">'.__('Enforce the expansion of shortcodes before generating a description.', 'add-meta-tags').'</label>
                <br />

            </fieldset>
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('Secure Access', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Secure Access', 'add-meta-tags').'</span></legend>

                <input id="has_https_access" type="checkbox" value="1" name="has_https_access" '. (($options["has_https_access"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="has_https_access">'.__('Media are accessible over HTTPS.', 'add-meta-tags').'</label>
                <br />
            </fieldset>
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('Media Limit', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Media Limit', 'add-meta-tags').'</span></legend>

                <input id="force_media_limit" type="checkbox" value="1" name="force_media_limit" '. (($options["force_media_limit"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="force_media_limit">'.__('Do not generate metadata for more than one media file of each type (image, video, audio).', 'add-meta-tags').'</label>
                <br />
            </fieldset>
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('Review Mode', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Review Mode', 'add-meta-tags').'</span></legend>

                <input id="review_mode" type="checkbox" value="1" name="review_mode" '. (($options["review_mode"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="review_mode">'.__('Enable <em>Metadata Review Mode</em>.', 'add-meta-tags').'</label>
                <br />

                <input id="review_mode_metadata_report" type="checkbox" value="1" name="review_mode_metadata_report" '. (($options["review_mode_metadata_report"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="review_mode_metadata_report">'.__('Enable keyword analysis and statistics.', 'add-meta-tags').'</label>
                (<a target="_blank" href="http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Metadata_Overview">'.__('Learn more', 'add-meta-tags').'</a>)
                (<span style="color:red;">'.__('Experimental', 'add-meta-tags').'</span>)
                <br />

                <input id="review_mode_omit_notices" type="checkbox" value="1" name="review_mode_omit_notices" '. (($options["review_mode_omit_notices"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="review_mode_omit_notices">'.__('Omit help messages and notices. I have read, understood and agreed to them.', 'add-meta-tags').'</label>
                <br />

            </fieldset>
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('Extended Metadata Support', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Extended Metadata Support', 'add-meta-tags').'</span></legend>

                <p>'.__('Add-Meta-Tags supports the generation of metadata for products and other post types. Please enable any of the following generators of extended metadata.', 'add-meta-tags').'</p>

                <br />

                <p><input id="extended_support_woocommerce" type="checkbox" value="1" name="extended_support_woocommerce" '. (($options["extended_support_woocommerce"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="extended_support_woocommerce">
                '.__('Metadata for WooCommerce products and product groups.', 'add-meta-tags').'
                </label></p>

                <p><input id="extended_support_edd" type="checkbox" value="1" name="extended_support_edd" '. (($options["extended_support_edd"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="extended_support_edd">
                '.__('Metadata for Easy-Digital-Downloads products and product groups.', 'add-meta-tags').' (<span style="color:red;">'.__('Work in progress', 'add-meta-tags').'</span>)
                </label></p>

                <p><input id="extended_support_buddypress" type="checkbox" value="1" name="extended_support_buddypress" '. (($options["extended_support_buddypress"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="extended_support_buddypress">
                '.__('Metadata for BuddyPress profiles.', 'add-meta-tags').'
                </label></p>

                <p><input id="extended_support_bbpress" type="checkbox" value="1" name="extended_support_bbpress" '. (($options["extended_support_bbpress"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="extended_support_bbpress">
                '.__('Metadata for bbPress.', 'add-meta-tags').' (<span style="color:red;">'.__('Work in progress', 'add-meta-tags').'</span>)
                </label></p>

                <br />

                <p>'.__('Please consult the integrated help for more information about these features.', 'add-meta-tags').'</p>

                <br />

            </fieldset>
            </td>
            </tr>
    ');


    // Metadata Caching

    if ( apply_filters('amt_enable_metadata_cache', true) ) {
        print('
            <tr valign="top">
            <th scope="row">'.__('Metadata Caching', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Metadata Caching', 'add-meta-tags').'</span></legend>

                <input id="enable_timings" type="checkbox" value="1" name="enable_timings" '. (($options["enable_timings"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="enable_timings">'.__('Show timing information.', 'add-meta-tags').'</label>
                <br /><br />

                <label for="transient_cache_expiration">'.__('Cache metadata for this number of seconds:', 'add-meta-tags').'</label>
                <input id="transient_cache_expiration" type="text" name="transient_cache_expiration" value="' . esc_attr( $options["transient_cache_expiration"] ) . '" size="10" maxlength="16" class="code" />
                <br /><br />

                <p>'.__('Metadata caching is an advanced feature and should only be used after you have read and fully understood the <em>Metadata caching</em> section of the integrated help.', 'add-meta-tags').'</p>
                <br />
        ');

        // Metadata cache status

        if ( absint($options["transient_cache_expiration"]) > 0 ) {
            // Get the number of cached entries
            // This only works when the database is used as the Transient Data storage backend.
            // If external storage backends are used for the transient data, this is always going to be zeo.
            $nr_cached_blocks = amt_count_transient_metadata_cache_entries();
            if ( absint($nr_cached_blocks) > 0 ) {
                print('
                    <p>'.__('<strong>Metadata cache status</strong>: <em>Activated</em>, <strong style="color: green;">' . esc_attr( $nr_cached_blocks ) . '</strong> metadata blocks have been cached. All cached metadata blocks will be automatically purged the next time these settings are saved.', 'add-meta-tags').'</p>
                    <br />
                ');
            } else {
                print('
                    <p>'.__('<strong>Metadata cache status</strong>: <em>Activated</em>, but no cached metadata blocks have been detected. This means that either no metadata blocks have been cached yet in the database or your WordPress installation uses an external storage backend for the transient data.', 'add-meta-tags').'</p>
                    <br />
                ');
            }
        } else {
            print('
                <p>'.__('<strong>Metadata cache status</strong>: <em>Deactivated</em>. The caching timeout is set to zero, so metadata blocks are not cached.', 'add-meta-tags').'</p>
                <br />
            ');
        }

        print('
            </fieldset>
            </td>
            </tr>
        ');
    }

    print('
            <tr valign="top">
            <th scope="row">'.__('Vendor HTML comments', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Vendor HTML comments', 'add-meta-tags').'</span></legend>

                <p>
                    '.__('Add-Meta-Tags has always been enclosing its output in <a target="_blank" href="http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Screenshots#Vendor-HTML-comments">HTML comments</a>, which contain the plugin\'s name. This is common practice among WordPress plugin developers as it makes it easier for users to identify the output of a specific plugin and also lets those who check the HTML source code of the page know which plugin has generated this specific output. By checking the following option you can remove those comments.', 'add-meta-tags').'
                </p>
                <br />

                <input id="omit_vendor_html_comments" type="checkbox" value="1" name="omit_vendor_html_comments" '. (($options["omit_vendor_html_comments"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="omit_vendor_html_comments">'.sprintf( __('Omit the vendor HTML comments.', 'add-meta-tags'), 'http://bit.ly/HvUakt').'</label> *
                <br />

                <p>
                    '.__('<em>* HTML comments that are generated as part of the Schema.org microdata are always retained.</em>', 'add-meta-tags').'
                </p>
                <br />

            </fieldset>
            </td>
            </tr>
    ');

    print('

        </tbody>
        </table>

        <!-- Submit Buttons -->
        <table class="form-table">
        <tbody>
            <tr valign="top">
                <th scope="row">
                    <input id="submit" class="button-primary" type="submit" value="'.__('Save Changes', 'add-meta-tags').'" name="info_update" />
                </th>

<!--
                <th scope="row">
                    <input id="reset" class="button-primary" type="submit" value="'.__('Reset to defaults', 'add-meta-tags').'" name="info_reset" />
                </th>
-->

                <th></th><th></th><th></th><th></th>
            </tr>
        </tbody>
        </table>

        <br /><br /><br />

        <h2 class="title">'.__('Notes', 'add-meta-tags').'</h2>

        <p>'.__('More management tools are available via the <code>amt</code> command of <code>wp-cli</code>. Learn more about the <a target="_blank" href="http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Command_Line_Interface">Add-Meta-Tags command line interface</a>.', 'add-meta-tags').'</p>


        </form>
        
    </div>

    ');

    print('
    </span> <!-- #add-meta-tags-settings -->
    ');

}



//
//
// Metadata metabox in post/page editing panel.
//
//

function amt_post_edit_metabox_enqueue_scripts($hook) {
    // Enqueue only on profile page.
    if ( ! in_array($hook, array('post.php', 'post-new.php')) ) {
        return;
    }
    
    // $supported_types = amt_get_post_types_for_metabox();
    // See: #900 for details

    // Using included Jquery
    wp_enqueue_script('jquery');

// For tabs
//    wp_enqueue_script('jquery-ui-core');
//    wp_enqueue_script('jquery-ui-widget');
//    wp_enqueue_script('jquery-ui-tabs');

    // Necessary for the media selector.
    // https://codex.wordpress.org/Javascript_Reference/wp.media
    wp_enqueue_media();

// For tabs
    //wp_register_style( 'amt-jquery-ui-core', plugins_url('css/jquery.ui.core.css', AMT_PLUGIN_FILE) );
    //wp_enqueue_style( 'amt-jquery-ui-core' );
    //wp_register_style( 'amt-jquery-ui-tabs', plugins_url('css/jquery.ui.tabs.css', AMT_PLUGIN_FILE) );
    //wp_enqueue_style( 'amt-jquery-ui-tabs' );
//    wp_register_style( 'amt-metabox-tabs', plugins_url('css/amt-metabox-tabs.css', AMT_PLUGIN_FILE) );
//    wp_enqueue_style( 'amt-metabox-tabs' );

    // Register Add-Meta-Tags admin scripts
    wp_register_script( 'amt_image_selector_script', plugins_url( 'js/amt-image-selector.js', AMT_PLUGIN_FILE ), array('jquery') );
    // Enqueue the Add-Meta-Tags Admin Scripts
    wp_enqueue_script( 'amt_image_selector_script' );
}
add_action( 'admin_enqueue_scripts', 'amt_post_edit_metabox_enqueue_scripts' );


/* Define the custom box */
add_action( 'add_meta_boxes', 'amt_add_metadata_box' );

/**
 * Adds a box to the main column of the editing panel of the supported post types.
 * See the amt_get_post_types_for_metabox() docstring for more info on the supported types.
 */
function amt_add_metadata_box() {

    // Get the Metadata metabox permissions (filtered)
    $metabox_permissions = amt_get_metadata_metabox_permissions();

    // Global Metadata metabox permission check (can be user customized via filter).
    if ( ! current_user_can( $metabox_permissions['global_metabox_capability'] ) ) {
        return;
    }

    // Global Metadata metabox permission check (internal - `edit_posts` is the minimum capability).
    if ( ! current_user_can( 'edit_posts' ) ) {
        return;
    }

    // Get an array of post types that support the addition of the metabox.
    $supported_types = amt_get_post_types_for_metabox();

    // Add an Add-Meta-Tags meta box to all supported types
    foreach ($supported_types as $supported_type) {
        add_meta_box( 
            'amt-metadata-box',
            __( 'Metadata', 'add-meta-tags' ) . ' ' . __('(by <em>Add-Meta-Tags</em>)', 'add-meta-tags'),
            'amt_inner_metadata_box',
            $supported_type,
            'advanced',
            'high'
        );
    }

}


// For future reference - Add data to the HEAD area of post editing panel
//
// add_action('admin_head-post.php', 'amt_metabox_script_caller');
// add_action('admin_head-post-new.php', 'amt_metabox_script_caller');
// OR
// add_action('admin_footer-post.php', 'amt_metabox_script_caller');
// add_action('admin_footer-post-new.php', 'amt_metabox_script_caller');
function amt_metabox_script_caller() {
    print('
    <script>
        jQuery(document).ready(function($) {
        $("#amt-metabox-tabs .hidden").removeClass(\'hidden\');
        $("#amt-metabox-tabs").tabs();
        });
    </script>
    ');
}



/* Prints the box content */
function amt_inner_metadata_box( $post ) {

    /* For future implementation. Basic code for tabs. */
    /*
    print('<br /><br />
        <div id="amt-metabox-tabs">
            <ul id="amt-metabox-tabs-list" class="category-tabs">
                <li><a href="#metadata-basic">Basic</a></li>
                <li><a href="#metadata-advanced">Advanced</a></li>
                <li><a href="#metadata-extra">Extra</a></li>
            </ul>

            <br class="clear" />
            <div id="metadata-basic">
                <p>#1 - basic</p>
            </div>
            <div class="hidden" id="metadata-advanced">
                <p>#2 - advanced</p>
            </div>
            <div class="hidden" id="metadata-extra">
                <p>#3 - extra</p>
            </div>
        </div>
        <br /><br />
    ');
    */

    // Use a nonce field for verification
    wp_nonce_field( plugin_basename( AMT_PLUGIN_FILE ), 'amt_noncename' );

    // Get the Metadata metabox permissions (filtered)
    $metabox_permissions = amt_get_metadata_metabox_permissions();

    // Get the post type. Will be used to customize the displayed notes.
    $post_type = get_post_type( $post->ID );

    // Get the Add-Meta-Tags options.
    $options = get_option("add_meta_tags_opts");

    // Display the meta box HTML code.

    $metabox_has_features = false;

    print('
    <!-- #add-meta-tags-settings is required by the media selector -->
    <span id="add-meta-tags-settings">
    ');

    // Custom description
    
    // Description box permission check (can be user customized via filter).
    if ( $options['metabox_enable_description'] == '1' && current_user_can( $metabox_permissions['description_box_capability'] ) ) {
        $metabox_has_features = true;

        // Retrieve the field data from the database.
        $custom_description_value = amt_get_post_meta_description( $post->ID );

        print('
            <p>
                <label for="amt_custom_description"><strong>'.__('Description', 'add-meta-tags').'</strong>:</label>
                <textarea class="code" style="width: 99%" id="amt_custom_description" name="amt_custom_description" cols="30" rows="2" >' . esc_attr( stripslashes( $custom_description_value ) ) . '</textarea>
                <br>
                '.__('Enter a custom description of 30-50 words (based on an average word length of 5 characters).', 'add-meta-tags').'
            </p>
        ');
        // Different notes based on post type
        if ( $post_type == 'post' ) {
            print('
                <p>
                    '.__('If the <em>description</em> field is left blank, a <em>description</em> meta tag will be <strong>automatically</strong> generated from the excerpt or, if an excerpt has not been set, directly from the first paragraph of the content.', 'add-meta-tags').'
                </p>
            ');
        } elseif ( $post_type == 'page' ) {
            print('
                <p>
                    '.__('If the <em>description</em> field is left blank, a <em>description</em> meta tag will be <strong>automatically</strong> generated from the first paragraph of the content.', 'add-meta-tags').'
                </p>
            ');
        } else {    // Custom post types
            print('
                <p>
                    '.__('If the <em>description</em> field is left blank, a <em>description</em> meta tag will be <strong>automatically</strong> generated from the first paragraph of the content.', 'add-meta-tags').'
                </p>
            ');
        }

        // Add warning about the automatic descriptions being turned off.
        if ( ! apply_filters( 'amt_generate_description_if_no_manual_data', true ) ) {
            print('
                <p>
                    <em>'.__('Warning: the automatic generation of descriptions has been turned off.', 'add-meta-tags').'</em>
                </p>
            ');
        }
    }


    // Custom keywords

    // Keywords box permission check (can be user customized via filter).
    if ( $options['metabox_enable_keywords'] == '1' && current_user_can( $metabox_permissions['keywords_box_capability'] ) ) {
        $metabox_has_features = true;

        // Retrieve the field data from the database.
        $custom_keywords_value = amt_get_post_meta_keywords( $post->ID );

        // Alt input:  <input type="text" class="code" style="width: 99%" id="amt_custom_keywords" name="amt_custom_keywords" value="'.$custom_keywords_value.'" />
        print('
            <p>
                <label for="amt_custom_keywords"><strong>'.__('Keywords', 'add-meta-tags').'</strong>:</label>
                <textarea class="code" style="width: 99%" id="amt_custom_keywords" name="amt_custom_keywords" cols="30" rows="2" >' . esc_attr( stripslashes( $custom_keywords_value ) ) . '</textarea>
                <br>
                '.__('Enter keywords separated with commas.', 'add-meta-tags').'
            </p>
        ');
        // Different notes based on post type
        if ( $post_type == 'post' ) {
            print('
                <p>
                    '.__('If the <em>keywords</em> field is left blank, a <em>keywords</em> meta tag will be <strong>automatically</strong> generated from the post\'s categories, tags, custom taxonomy terms and from the global keywords, if any such global keywords have been set in the plugin settings. In case you decide to set a custom list of keywords for this post, it is possible to easily include the post\'s categories, tags and custom taxonomy terms in that list by using the special placeholders <code>%cats%</code>, <code>%tags%</code> and <code>%terms%</code> respectively.', 'add-meta-tags').'
                    <br />
                    '.__('Example', 'add-meta-tags').': <code>keyword1, keyword2, %cats%, keyword3, %tags%, keyword4</code>
                </p>
            ');
        } elseif ( $post_type == 'page' ) {
            print('
                <p>
                    '.__('If the <em>keywords</em> field is left blank, a <em>keywords</em> meta tag will only be automatically generated from global keywords, if any such global keywords have been set in the plugin settings.', 'add-meta-tags').'
                </p>
            ');
        } else {    // Custom post types
            print('
                <p>
                    '.__('If the <em>keywords</em> field is left blank, a <em>keywords</em> meta tag will only be automatically generated from global keywords, if any such global keywords have been set in the plugin settings.', 'add-meta-tags').'
                </p>
            ');
        }

        // Add warning about the automatic keywords being turned off.
        if ( ! apply_filters( 'amt_generate_keywords_if_no_manual_data', true ) ) {
            print('
                <p>
                    <em>'.__('Warning: the automatic generation of keywords has been turned off.', 'add-meta-tags').'</em>
                </p>
            ');
        }

    }


    // Advanced options

    // Custom title tag

    // Custom title box permission check (can be user customized via filter).
    if ( $options['metabox_enable_title'] == '1' && current_user_can( $metabox_permissions['title_box_capability'] ) ) {
        $metabox_has_features = true;

        // Retrieve the field data from the database.
        $custom_title_value = amt_get_post_meta_title( $post->ID );

        print('
            <p>
                <label for="amt_custom_title"><strong>'.__('Title', 'add-meta-tags').'</strong>:</label>
                <input type="text" class="code" style="width: 99%" id="amt_custom_title" name="amt_custom_title" value="' . esc_attr( stripslashes( $custom_title_value ) ) . '" />
                <br>
                '.__('Enter a custom title to be used in the <em>title</em> HTML element of the page.', 'add-meta-tags').'
            </p>
            <p>
                '.__('The <code>%title%</code> placeholder is automatically expanded to the current title of the content.', 'add-meta-tags').'
            </p>
        ');

    }


    // 'news_keywords' meta tag
    
    // 'news_keywords' box permission check (can be user customized via filter).
    if ( $options['metabox_enable_news_keywords'] == '1' && current_user_can( $metabox_permissions['news_keywords_box_capability'] ) ) {
        $metabox_has_features = true;

        // Retrieve the field data from the database.
        $custom_newskeywords_value = amt_get_post_meta_newskeywords( $post->ID );

        print('
            <p>
                <label for="amt_custom_newskeywords"><strong>'.__('News Keywords', 'add-meta-tags').'</strong>:</label>
                <input type="text" class="code" style="width: 99%" id="amt_custom_newskeywords" name="amt_custom_newskeywords" value="' . esc_attr( stripslashes( $custom_newskeywords_value ) ) . '" />
                <br>
                '.__('Enter a comma-delimited list of <strong>news keywords</strong>. For more info about this meta tag, please see this <a target="_blank" href="http://support.google.com/news/publisher/bin/answer.py?hl=en&answer=68297">Google help page</a>.', 'add-meta-tags').'
            </p>
        ');

    }


    // per post full meta tags
    
    // Full meta tags box permission check (can be user customized via filter).
    if ( $options['metabox_enable_full_metatags'] == '1' && current_user_can( $metabox_permissions['full_metatags_box_capability'] ) ) {
        $metabox_has_features = true;

        // Retrieve the field data from the database.
        $custom_full_metatags_value = amt_get_post_meta_full_metatags( $post->ID );

        print('
            <p>
                <label for="amt_custom_full_metatags"><strong>'.__('Full meta tags', 'add-meta-tags').'</strong>:</label>
                ' . amt_get_full_meta_tag_sets( $custom_full_metatags_value ) . '
                <textarea class="code" style="width: 99%" id="amt_custom_full_metatags" name="amt_custom_full_metatags" cols="30" rows="6" >'. stripslashes( $custom_full_metatags_value ) .'</textarea>
                <br />
                '.__('Provide the full XHTML code of extra <a target="_blank" href="http://en.wikipedia.org/wiki/Meta_element" target="_blank"><code>meta</code></a> and <code>link</code> <a target="_blank" href="https://en.wikipedia.org/wiki/HTML_element">HTML elements</a> you would like to add to this content.', 'add-meta-tags').'
                '.__('For example, to prevent a cached copy of this content from being available in search engine results, you can add the following metatag:', 'add-meta-tags').'
                <br /><br />
                <code>&lt;meta name="robots" content="noarchive" /&gt;</code>
            </p>

            <p>'.__('Moreover, the full meta tags box accepts the special notation <code>[field=Field Name]</code> which lets you use data from a Custom Field with name <em>Field Name</em>. This special notation may exist anywhere inside the meta tag. To automatically add paging information to URLs, append the <code>PAGEINFO</code> placeholder.', 'add-meta-tags').'</p>

        ');

    }


    // Image URL (global override)
    
    // 'image_url' box permission check (can be user customized via filter).
    if ( $options['metabox_enable_image_url'] == '1' && current_user_can( $metabox_permissions['image_url_box_capability'] ) ) {
        $metabox_has_features = true;

        // Retrieve the field data from the database.
        $custom_image_url_value = amt_get_post_meta_image_url( $post->ID );

        print('
            <p>
                <label for="amt_custom_image_url"><strong>'.__('Image URL', 'add-meta-tags').'</strong>:</label>
                <input type="text" class="code" style="width: 99%" id="amt_custom_image_url" name="amt_custom_image_url" value="' . amt_esc_id_or_url_notation( stripslashes( $custom_image_url_value ) ) . '" />

                <span id="amt-image-selector-button" class="amt-image-selector-button wp-media-buttons-icon loadmediawindow button updatemeta button-small">'.__('Select image', 'add-meta-tags').'</span>
                <br />

                '.__('Enter an absolute image URL in order to enforce the use of this image in the metadata. To specify the image dimensions you can use the special notation <code>URL,WIDTHxHEIGHT</code>.', 'add-meta-tags').'
                '.__('Alternatively, you can select an image by pressing the <em>Select image</em> button.', 'add-meta-tags').'
                '.__('If this image is set, the plugin will not generate metadata for other media.', 'add-meta-tags').'
            </p>

        ');

            // Image preview
            $image_data = amt_get_image_data( amt_esc_id_or_url_notation( stripslashes( $custom_image_url_value ) ) );
            $img_html = '';
            if ( is_numeric($image_data['id']) ) {
                $main_size_meta = wp_get_attachment_image_src( $image_data['id'], 'medium' );
                $img_html = '<img src="' . esc_url($main_size_meta[0]) . '" width="' . esc_attr($main_size_meta[1]) . '" height="' . esc_attr($main_size_meta[2]) . '" />';
            } elseif ( ! is_null($image_data['url']) ) {
                $img_html = '<img src="' . esc_url($image_data['url']) . '" width="' . esc_attr($image_data['width']) . '" height="' . esc_attr($image_data['height']) . '" />';
            }
            if ( ! empty($img_html) ) {
                print('
                <p>'.__('Image preview', 'add-meta-tags').':</p>
                <br />
                <div id="amt-image-preview" class="amt-image-preview">' . $img_html . '</div>
                ');
            }

    }


    // Content locale override
    
    // 'content_locale' box permission check (can be user customized via filter).
    if ( $options['metabox_enable_content_locale'] == '1' && current_user_can( $metabox_permissions['content_locale_box_capability'] ) ) {
        $metabox_has_features = true;

        // Retrieve the field data from the database.
        $custom_content_locale_value = amt_get_post_meta_content_locale( $post->ID );

        print('
            <p>
                <label for="amt_custom_content_locale"><strong>'.__('Content locale', 'add-meta-tags').'</strong>:</label>
                <input type="text" class="code" style="width: 99%" id="amt_custom_content_locale" name="amt_custom_content_locale" value="' . esc_attr( stripslashes( $custom_content_locale_value ) ) . '" />
                <br>
                '.__('Override the default locale setting by entering a custom locale for this content in the form <code>language_TERRITORY</code>, for example: <code>en_US</code>.', 'add-meta-tags').'
            </p>
        ');

    }


    // Express review

    // Express review box permission check (can be user customized via filter).
    if ( $options['metabox_enable_express_review'] == '1' && current_user_can( $metabox_permissions['express_review_box_capability'] ) ) {
        $metabox_has_features = true;

        // Retrieve the field data from the database.
        $custom_express_review_value = amt_get_post_meta_express_review( $post->ID );

        print('
            <p>
                <label for="amt_custom_express_review"><strong>'.__('Express review', 'add-meta-tags').'</strong>:</label>
                ' . amt_get_sample_review_sets() . '
                <textarea class="code" style="width: 99%" id="amt_custom_express_review" name="amt_custom_express_review" cols="30" rows="12" >'. stripslashes( $custom_express_review_value ) .'</textarea>
                <br />
                '.__('This field accepts review related information using INI file syntax. If this info is provided in the correct form, then Add-Meta-Tags treats your content as being a review of an item and generates proper Schema.org metadata. <a href="http://www.codetrax.org/projects/wp-add-meta-tags/wiki/Metadata_Generators_Key_Notes#Metadata-for-reviews" target="_blank">Read more</a> about the correct syntax of the review information.', 'add-meta-tags').'
                <br />
            </p>
        ');

        // Add javascipt to fill textarea with sample review data.
        // CURRENTLY NOT USED
        print('
<script>
// To click on a link like:
// <a id="amt_fill_sample_review" href="#">Click here</a>
// and replace the textarea data.
jQuery(document).ready(function() {
    jQuery("#amt_fill_sample_review").click(function(event) {
        event.preventDefault();
        //jQuery("#amt_fill_sample_review").live(\'click\',function(e){
        var samplereview = "; Review rating (required)\n\
ratingValue = 4.2\n\
; Mandatory reviewed item properties (required)\n\
object = Book\n\
name = On the Origin of Species\n\
sameAs = http://en.wikipedia.org/wiki/On_the_Origin_of_Species\n\
; Extra reviewed item properties (optional)\n\
;isbn = 123456\n\
;[author]\n\
;object = Person\n\
;name = Charles Darwin\n\
;sameAs = https://en.wikipedia.org/wiki/Charles_Darwin";
        jQuery("#amt_custom_express_review").val(samplereview);
    });
});
</script>
        ');

    }

    // List of URLs of items referenced in the post.

    // Referenced items box permission check (can be user customized via filter).
    if ( $options['metabox_enable_referenced_list'] == '1' && current_user_can( $metabox_permissions['referenced_list_box_capability'] ) ) {
        $metabox_has_features = true;

        // Retrieve the field data from the database.
        $custom_referenced_list_value = amt_get_post_meta_referenced_list( $post->ID );

        print('
            <p>
                <label for="amt_custom_referenced_list"><strong>'.__('URLs of referenced items', 'add-meta-tags').'</strong>:</label>
                <textarea class="code" style="width: 99%" id="amt_custom_referenced_list" name="amt_custom_referenced_list" cols="30" rows="4" >'. stripslashes( $custom_referenced_list_value ) .'</textarea>
                <br>
                '.__('Enter a list of canonical URLs (one per line) of items referenced in the content. The page referenced need not be on the same domain as the content. For example, you might reference a page where a product can be purchased or a page that further describes a place. If such references are provided and if OpenGraph/Schema.org metadata is enabled, then the relevant <code>og:referenced</code> and <code>referencedItem</code> meta tags will be generated.', 'add-meta-tags').' (<span style="color:red;">'.__('Experimental feature', 'add-meta-tags').'</span>)
            </p>
        ');

    }


    // If no features have been enabled, print an informative message
    if ( $metabox_has_features === false ) {
        print('
            <p>'.__(sprintf( 'No features have been enabled for this metabox in the Add-Meta-Tags <a href="%s">settings</a> or you do not have enough permissions to access the available features.', admin_url( 'options-general.php?page=add-meta-tags-options' ) ), 'add-meta-tags').'</p>
        ');
    } else {
        print('
            <p style="font-size: 85%; text-align: right; margin-top: 10px;">'.__(sprintf( 'Note: more features for this metabox might be available in the Add-Meta-Tags <a href="%s">settings</a>.', admin_url( 'options-general.php?page=add-meta-tags-options' ) ), 'add-meta-tags').'</p>
        ');
    }

    print('
    </span> <!-- #add-meta-tags-settings -->
    ');

}




/* Manage the entered data */
add_action( 'save_post', 'amt_save_postdata', 10, 2 );

/* When the post is saved, saves our custom description and keywords */
function amt_save_postdata( $post_id, $post ) {

    // Verify if this is an auto save routine. 
    // If it is our form has not been submitted, so we dont want to do anything
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
        return;

    /* Verify the nonce before proceeding. */
    // Verify this came from the our screen and with proper authorization,
    // because save_post can be triggered at other times
    if ( !isset($_POST['amt_noncename']) || !wp_verify_nonce( $_POST['amt_noncename'], plugin_basename( AMT_PLUGIN_FILE ) ) )
        return;

    // Get the Metadata metabox permissions (filtered)
    $metabox_permissions = amt_get_metadata_metabox_permissions();

    // Global Metadata metabox permission check (can be user customized via filter).
    if ( ! current_user_can( $metabox_permissions['global_metabox_capability'] ) ) {
        return;
    }

    // Get the Add-Meta-Tags options.
    $options = get_option("add_meta_tags_opts");

    /* Get the post type object. */
	$post_type_obj = get_post_type_object( $post->post_type );

    /* Check if the current user has permission to edit the post. */
	if ( !current_user_can( $post_type_obj->cap->edit_post, $post_id ) )
		return;

    // OK, we're authenticated: we need to find and save the data

    //
    // Sanitize user input
    //

    //
    // Description
    if ( isset( $_POST['amt_custom_description'] ) ) {
        $description_value = sanitize_text_field( amt_sanitize_description( stripslashes( $_POST['amt_custom_description'] ) ) );
    }
    // Keywords - sanitize_text_field() removes '%ca' part of '%cats%', so we enclose 'sanitize_text_field()' in amt_(convert|revert)_placeholders()
    if ( isset( $_POST['amt_custom_keywords'] ) ) {
        $keywords_value = amt_sanitize_keywords(amt_revert_placeholders( sanitize_text_field( amt_convert_placeholders( stripslashes( $_POST['amt_custom_keywords'] ) ) ) ) );
    }
    // Title
    if ( isset( $_POST['amt_custom_title'] ) ) {
        $title_value = amt_revert_placeholders( sanitize_text_field( amt_convert_placeholders( stripslashes( $_POST['amt_custom_title'] ) ) ) );
    }
    // News keywords
    if ( isset( $_POST['amt_custom_newskeywords'] ) ) {
        $newskeywords_value = sanitize_text_field( amt_sanitize_keywords( stripslashes( $_POST['amt_custom_newskeywords'] ) ) );
    }
    // Full metatags - We allow only <meta> elements.
    if ( isset( $_POST['amt_custom_full_metatags'] ) ) {
        $full_metatags_value = esc_textarea( wp_kses( stripslashes( $_POST['amt_custom_full_metatags'] ), amt_get_allowed_html_kses() ) );
    }
    // Image URL
    if ( isset( $_POST['amt_custom_image_url'] ) ) {
        $image_url_value = amt_esc_id_or_url_notation( stripslashes( $_POST['amt_custom_image_url'] ) );
    }
    // Content locale
    if ( isset( $_POST['amt_custom_content_locale'] ) ) {
        $content_locale_value = esc_attr( stripslashes( $_POST['amt_custom_content_locale'] ) );
    }
    // Express review
    if ( isset( $_POST['amt_custom_express_review'] ) ) {
        $express_review_value = esc_textarea( wp_kses( stripslashes( $_POST['amt_custom_express_review'] ), array() ) );
        //
        // REVIEW_AMPERSAND_NOTE: We replace &amp; to & for (see also REVIEW_AMPERSAND_NOTE in amt-utils.php)
        //
        $express_review_value = str_replace('&amp;', '&', $express_review_value);
    }
    // List of referenced items - We allow no HTML elements.
    if ( isset( $_POST['amt_custom_referenced_list'] ) ) {
        $referenced_list_value = esc_textarea( wp_kses( stripslashes( $_POST['amt_custom_referenced_list'] ), array() ) );
    }

    // If a value has not been entered we try to delete existing data from the database
    // If the user has entered data, store it in the database.

    // Add-Meta-Tags custom field names
    $amt_description_field_name = '_amt_description';
    $amt_keywords_field_name = '_amt_keywords';
    $amt_title_field_name = '_amt_title';
    $amt_newskeywords_field_name = '_amt_news_keywords';
    $amt_full_metatags_field_name = '_amt_full_metatags';
    $amt_image_url_field_name = '_amt_image_url';
    $amt_content_locale_field_name = '_amt_content_locale';
    $amt_express_review_field_name = '_amt_express_review';
    $amt_referenced_list_field_name = '_amt_referenced_list';

    // As an extra security measure, here we also check the user-defined per box
    // permissions before we save any data in the database.

    // Description
    if ( $options['metabox_enable_description'] == '1' && current_user_can( $metabox_permissions['description_box_capability'] ) ) {
        if ( empty($description_value) ) {
            delete_post_meta($post_id, $amt_description_field_name);
            // Also clean up old description field
            delete_post_meta($post_id, 'description');
        } else {
            update_post_meta($post_id, $amt_description_field_name, $description_value);
            // Also clean up again old description field - no need to exist any more since the new field is used.
            delete_post_meta($post_id, 'description');
        }
    }

    // Keywords
    if ( $options['metabox_enable_keywords'] == '1' && current_user_can( $metabox_permissions['keywords_box_capability'] ) ) {
        if ( empty($keywords_value) ) {
            delete_post_meta($post_id, $amt_keywords_field_name);
            // Also clean up old keywords field
            delete_post_meta($post_id, 'keywords');
        } else {
            update_post_meta($post_id, $amt_keywords_field_name, $keywords_value);
            // Also clean up again old keywords field - no need to exist any more since the new field is used.
            delete_post_meta($post_id, 'keywords');
        }
    }

    // Title
    if ( $options['metabox_enable_title'] == '1' && current_user_can( $metabox_permissions['title_box_capability'] ) ) {
        if ( empty($title_value) ) {
            delete_post_meta($post_id, $amt_title_field_name);
        } else {
            update_post_meta($post_id, $amt_title_field_name, $title_value);
        }
    }

    // 'news_keywords'
    if ( $options['metabox_enable_news_keywords'] == '1' && current_user_can( $metabox_permissions['news_keywords_box_capability'] ) ) {
        if ( empty($newskeywords_value) ) {
            delete_post_meta($post_id, $amt_newskeywords_field_name);
        } else {
            update_post_meta($post_id, $amt_newskeywords_field_name, $newskeywords_value);
        }
    }

    // per post full meta tags
    if ( $options['metabox_enable_full_metatags'] == '1' && current_user_can( $metabox_permissions['full_metatags_box_capability'] ) ) {
        if ( empty($full_metatags_value) ) {
            delete_post_meta($post_id, $amt_full_metatags_field_name);
        } else {
            update_post_meta($post_id, $amt_full_metatags_field_name, $full_metatags_value);
        }
    }

    // Image URL
    if ( $options['metabox_enable_image_url'] == '1' && current_user_can( $metabox_permissions['image_url_box_capability'] ) ) {
        if ( empty($image_url_value) ) {
            delete_post_meta($post_id, $amt_image_url_field_name);
        } else {
            update_post_meta($post_id, $amt_image_url_field_name, $image_url_value);
        }
    }

    // Content locale
    if ( $options['metabox_enable_content_locale'] == '1' && current_user_can( $metabox_permissions['content_locale_box_capability'] ) ) {
        if ( empty($content_locale_value) ) {
            delete_post_meta($post_id, $amt_content_locale_field_name);
        } else {
            update_post_meta($post_id, $amt_content_locale_field_name, $content_locale_value);
        }
    }

    // Express review
    if ( $options['metabox_enable_express_review'] == '1' && current_user_can( $metabox_permissions['express_review_box_capability'] ) ) {
        if ( empty($express_review_value) ) {
            delete_post_meta($post_id, $amt_express_review_field_name);
        } else {
            update_post_meta($post_id, $amt_express_review_field_name, $express_review_value);
        }
    }

    // Referenced list
    if ( $options['metabox_enable_referenced_list'] == '1' && current_user_can( $metabox_permissions['referenced_list_box_capability'] ) ) {
        if ( empty($referenced_list_value) ) {
            delete_post_meta($post_id, $amt_referenced_list_field_name);
        } else {
            update_post_meta($post_id, $amt_referenced_list_field_name, $referenced_list_value);
        }
    }
    
}



//
//
// Extra fields to taxonomy terms
//
//


function amt_terms_enqueue_scripts($hook) {
    // Enqueue only on profile page.
    if ( 'term.php' != $hook ) {
        return;
    }
    
    // Using included Jquery UI
    wp_enqueue_script('jquery');

    // Necessary for the media selector.
    // https://codex.wordpress.org/Javascript_Reference/wp.media
    wp_enqueue_media();

    // Register Add-Meta-Tags admin scripts
    wp_register_script( 'amt_image_selector_script', plugins_url( 'js/amt-image-selector.js', AMT_PLUGIN_FILE ), array('jquery') );
    // Enqueue the Add-Meta-Tags Admin Scripts
    wp_enqueue_script( 'amt_image_selector_script' );
}
add_action( 'admin_enqueue_scripts', 'amt_terms_enqueue_scripts' );


function amt_add_extra_section_fields_terms() {

    // The term meta API was implemented in 4.4
    if ( version_compare( get_bloginfo('version'), '4.4', '<' ) ) {
        return;
    }

    $taxonomies = get_taxonomies();
    if ( ! empty($taxonomies) ) {
        foreach ( $taxonomies as $key => $taxonomy_slug ) {
            // For new term additions.
            //add_action( $taxonomy_slug . '_add_form_fields', 'amt_taxonomy_extra_fields_show', 9999, 2 );
            // For editing current terms.
            add_action( $taxonomy_slug . '_edit_form_fields', 'amt_taxonomy_extra_fields_show', 9999, 2 );

            // Store data when created
            //add_action( 'created_' . $taxonomy_slug, 'amt_taxonomy_extra_fields_save', 10, 2 );
            // Store data when edited
            add_action( 'edited_' . $taxonomy_slug, 'amt_taxonomy_extra_fields_save', 10, 2 );
        }
    }
}
add_action('admin_init', 'amt_add_extra_section_fields_terms');


function amt_taxonomy_extra_fields_show( $term, $taxonomy_slug ) {

    // Use a nonce field for verification
    wp_nonce_field( plugin_basename( AMT_PLUGIN_FILE ), 'amt_noncename' );

    // Get the Metadata metabox permissions (filtered)
    $metabox_permissions = amt_get_metadata_metabox_permissions();

    // Get the Add-Meta-Tags options.
    $options = amt_get_options();

    // Store term ID
    $term_id = $term->term_id;

    // Display the meta box HTML code.

    $metabox_has_features = false;

    print('
        <tr class="form-field">
        <th scope="row"><h3>'.__('Add-Meta-Tags', 'add-meta-tags').'</h3></th>
        <td></td>
        </tr>
    ');

    // per post full meta tags
    
    // Full meta tags box permission check (can be user customized via filter).
    if ( $options['metabox_term_enable_full_metatags'] == '1' && current_user_can( $metabox_permissions['term_full_metatags_box_capability'] ) ) {
        $metabox_has_features = true;

        // Retrieve the field data from the database.
        $custom_full_metatags_value = amt_get_term_meta_full_metatags( $term_id );

        print('
            <!-- .add-meta-tags-setting is required by the media selector -->
            <tr class="add-meta-tags-setting form-field term-amt_custom_full_metatags-wrap">
            <th scope="row"><label for="amt_custom_full_metatags">'.__('Full meta tags', 'add-meta-tags').'</label></th>
            <td>
                <textarea class="large-text code" style="width: 99%" id="amt_custom_full_metatags" name="amt_custom_full_metatags" cols="50" rows="6" >'. stripslashes( $custom_full_metatags_value ) .'</textarea>
                <p class="description">
                    '.__('Provide the full XHTML code of extra <a target="_blank" href="http://en.wikipedia.org/wiki/Meta_element" target="_blank"><code>meta</code></a> and <code>link</code> <a target="_blank" href="https://en.wikipedia.org/wiki/HTML_element">HTML elements</a>. To automatically add paging information to URLs, append the <code>PAGEINFO</code> placeholder. For example:', 'add-meta-tags').'
                    <br /><br />
                    <code>&lt;meta name="robots" content="noodp,noarchive,notranslate,noimageindex" /&gt;</code>
                    <br />
                    <code>&lt;link rel="prefetch" href="http://www.example.org/landing-page.html" /&gt;</code>
                    <br />
                    <code>&lt;link rel="alternate" hreflang="el" href="http://example.org/section/multimedia/PAGEINFO" /&gt;</code>
                    <br />
                </p>
            </td>
            </tr>

        ');

    }


    // Image URL (global override)
    
    // 'image_url' box permission check (can be user customized via filter).
    if ( $options['metabox_term_enable_image_url'] == '1' && current_user_can( $metabox_permissions['term_image_url_box_capability'] ) ) {
        $metabox_has_features = true;

        // Retrieve the field data from the database.
        $custom_image_url_value = amt_get_term_meta_image_url( $term_id );

        print('
            <!-- .add-meta-tags-setting is required by the media selector -->
            <tr class="add-meta-tags-setting form-field term-amt_custom_image_url-wrap">
            <th scope="row"><label for="amt_custom_image_url">'.__('Image URL', 'add-meta-tags').'</label></th>
            <td>
            <input type="text" class="code" style="width: 99%" size="40" id="amt_custom_image_url" name="amt_custom_image_url" value="' . amt_esc_id_or_url_notation( stripslashes( $custom_image_url_value ) ) . '" />

            <span id="amt-image-selector-button" class="amt-image-selector-button wp-media-buttons-icon loadmediawindow button updatemeta button-small">'.__('Select image', 'add-meta-tags').'</span>
            <br />

            <p class="description">
                '.__('Enter an absolute image URL in order to enforce the use of this image in the metadata. To specify the image dimensions you can use the special notation <code>URL,WIDTHxHEIGHT</code>.', 'add-meta-tags').'
                '.__('Alternatively, you can select an image by pressing the <em>Select image</em> button.', 'add-meta-tags').'
                <br />
            </p>

        ');

            // Image preview
            $image_data = amt_get_image_data( amt_esc_id_or_url_notation( stripslashes( $custom_image_url_value ) ) );
            $img_html = '';
            if ( is_numeric($image_data['id']) ) {
                $main_size_meta = wp_get_attachment_image_src( $image_data['id'], 'medium' );
                $img_html = '<img src="' . esc_url($main_size_meta[0]) . '" width="' . esc_attr($main_size_meta[1]) . '" height="' . esc_attr($main_size_meta[2]) . '" />';
            } elseif ( ! is_null($image_data['url']) ) {
                $img_html = '<img src="' . esc_url($image_data['url']) . '" width="' . esc_attr($image_data['width']) . '" height="' . esc_attr($image_data['height']) . '" />';
            }
            if ( ! empty($img_html) ) {
                print('
                <p>'.__('Image preview', 'add-meta-tags').':</p>
                <br />
                <div id="amt-image-preview" class="amt-image-preview">' . $img_html . '</div>
                ');
            }

        print('

            </td>
            </tr>

        ');

    }

    // If no features have been enabled, print an informative message
    if ( $metabox_has_features === false ) {
        print('

        <tr class="form-field">
        <th scope="row">'.__('Notice', 'add-meta-tags').'</th>
        <td>
            <p>'.__(sprintf( 'No features have been enabled for this metabox in the Add-Meta-Tags <a href="%s">settings</a> or you do not have enough permissions to access the available features.', admin_url( 'options-general.php?page=add-meta-tags-options' ) ), 'add-meta-tags').'</p>
        </td>
        </tr>
        ');
    }

}


// When the term is saved
function amt_taxonomy_extra_fields_save( $term_id, $taxonomy_id ) {

    /* Verify the nonce before proceeding. */
    // Verify this came from the our screen and with proper authorization,
    // because save_post can be triggered at other times
    if ( ! isset($_POST['amt_noncename']) || ! wp_verify_nonce( $_POST['amt_noncename'], plugin_basename( AMT_PLUGIN_FILE ) ) )
        return;

    // Get the Metadata metabox permissions (filtered)
    $metabox_permissions = amt_get_metadata_metabox_permissions();

    // Global Metadata metabox permission check (can be user customized via filter).
    if ( ! current_user_can( $metabox_permissions['global_metabox_capability'] ) ) {
        return;
    }

    // Get the Add-Meta-Tags options.
    $options = amt_get_options();

    // Check if the current user has permission to edit the post.
	if ( ! current_user_can( 'edit_published_posts' ) ) {
		return;
    }

    // OK, we're authenticated: we need to find and save the data

    //
    // Sanitize user input
    //

    // Full metatags - We allow only <meta> elements.
    if ( isset( $_POST['amt_custom_full_metatags'] ) ) {
        $full_metatags_value = esc_textarea( wp_kses( stripslashes( $_POST['amt_custom_full_metatags'] ), amt_get_allowed_html_kses() ) );
    }
    // Image URL
    if ( isset( $_POST['amt_custom_image_url'] ) ) {
        $image_url_value = amt_esc_id_or_url_notation( stripslashes( $_POST['amt_custom_image_url'] ) );
    }

    // If a value has not been entered we try to delete existing data from the database
    // If the user has entered data, store it in the database.

    // Add-Meta-Tags custom field names
    $amt_full_metatags_field_name = '_amt_term_full_metatags';
    $amt_image_url_field_name = '_amt_term_image_url';

    // As an extra security measure, here we also check the user-defined per box
    // permissions before we save any data in the database.

    // per term full meta tags
    if ( $options['metabox_term_enable_full_metatags'] == '1' && current_user_can( $metabox_permissions['term_full_metatags_box_capability'] ) ) {
        if ( empty($full_metatags_value) ) {
            delete_term_meta($term_id, $amt_full_metatags_field_name);
        } else {
            update_term_meta($term_id, $amt_full_metatags_field_name, $full_metatags_value);
        }
    }

    // Image URL
    if ( $options['metabox_term_enable_image_url'] == '1' && current_user_can( $metabox_permissions['term_image_url_box_capability'] ) ) {
        if ( empty($image_url_value) ) {
            delete_term_meta($term_id, $amt_image_url_field_name);
        } else {
            update_term_meta($term_id, $amt_image_url_field_name, $image_url_value);
        }
    }

}


//
//
// Extra fields to WordPress user profiles
//
//

function amt_user_profile_enqueue_scripts($hook) {
    // Enqueue only on profile page.
    if ( 'profile.php' != $hook ) {
        return;
    }
    
    // Using included Jquery UI
    wp_enqueue_script('jquery');

    // Necessary for the media selector.
    // https://codex.wordpress.org/Javascript_Reference/wp.media
    wp_enqueue_media();

    // Register Add-Meta-Tags admin scripts
    wp_register_script( 'amt_image_selector_script', plugins_url( 'js/amt-image-selector.js', AMT_PLUGIN_FILE ), array('jquery') );
    // Enqueue the Add-Meta-Tags Admin Scripts
    wp_enqueue_script( 'amt_image_selector_script' );
}
add_action( 'admin_enqueue_scripts', 'amt_user_profile_enqueue_scripts' );


function amt_add_extra_section_fields_users() {
    // Show/edit
    add_action( 'show_user_profile', 'amt_user_extra_fields_show' );
    add_action( 'edit_user_profile', 'amt_user_extra_fields_show' );

    // Store data when created
    add_action( 'personal_options_update', 'amt_user_extra_fields_save' );
    add_action( 'edit_user_profile_update', 'amt_user_extra_fields_save' );
}
add_action('admin_init', 'amt_add_extra_section_fields_users');


function amt_user_extra_fields_show( $user ) {

    // Use a nonce field for verification
    wp_nonce_field( plugin_basename( AMT_PLUGIN_FILE ), 'amt_noncename' );

    // Get the Metadata metabox permissions (filtered)
    $metabox_permissions = amt_get_metadata_metabox_permissions();

    // Get the Add-Meta-Tags options.
    $options = amt_get_options();

    // Display the meta box HTML code.

    $metabox_has_features = false;

    print('
    <!-- #add-meta-tags-settings is required by the media selector -->
    <span id="add-meta-tags-settings">

        <h3>'.__('Add-Meta-Tags', 'add-meta-tags').'</h3>

        <table class="form-table">
            <tbody>
    ');

    // per post full meta tags
    // Full meta tags box permission check (can be user customized via filter).
    if ( $options['metabox_user_enable_full_metatags'] == '1' && current_user_can( $metabox_permissions['user_full_metatags_box_capability'] ) ) {
        $metabox_has_features = true;

        // Retrieve the field data from the database.
        $custom_full_metatags_value = amt_get_user_meta_full_metatags( $user->ID );

        print('

            <tr class="form-field user-amt_custom_full_metatags-wrap">
            <th scope="row"><label for="amt_custom_full_metatags">'.__('Full meta tags', 'add-meta-tags').'</label></th>
            <td>
                <textarea class="large-text code" style="width: 99%" id="amt_custom_full_metatags" name="amt_custom_full_metatags" cols="50" rows="6" >'. stripslashes( $custom_full_metatags_value ) .'</textarea>
                <p class="description">
                    '.__('Provide the full XHTML code of extra <a target="_blank" href="http://en.wikipedia.org/wiki/Meta_element" target="_blank"><code>meta</code></a> and <code>link</code> <a target="_blank" href="https://en.wikipedia.org/wiki/HTML_element">HTML elements</a>. To automatically add paging information to URLs, append the <code>PAGEINFO</code> placeholder. For example:', 'add-meta-tags').'
                    <br /><br />
                    <code>&lt;meta name="robots" content="noodp,noarchive,notranslate,noimageindex" /&gt;</code>
                    <br />
                    <code>&lt;link rel="prefetch" href="http://www.example.org/landing-page.html" /&gt;</code>
                    <br />
                    <code>&lt;link rel="alternate" hreflang="el" href="http://example.org/section/multimedia/PAGEINFO" /&gt;</code>
                    <br />
                </p>
            </td>
            </tr>

        ');

    }


    // Image URL (global override)
    
    // 'image_url' box permission check (can be user customized via filter).
    if ( $options['metabox_user_enable_image_url'] == '1' && current_user_can( $metabox_permissions['user_image_url_box_capability'] ) ) {
        $metabox_has_features = true;

        // Retrieve the field data from the database.
        $custom_image_url_value = amt_get_user_meta_image_url( $user->ID );

        print('
            <tr class="form-field user-amt_custom_image_url-wrap">
            <th scope="row"><label for="amt_custom_image_url">'.__('Image URL', 'add-meta-tags').'</label></th>
            <td>
            <input type="text" class="code" style="width: 99%" size="40" id="amt_custom_image_url" name="amt_custom_image_url" value="' . amt_esc_id_or_url_notation( stripslashes( $custom_image_url_value ) ) . '" />

            <span id="amt-image-selector-button" class="amt-image-selector-button wp-media-buttons-icon loadmediawindow button updatemeta button-small">'.__('Select image', 'add-meta-tags').'</span>
            <br />

            <p class="description">
                '.__('Enter an absolute image URL in order to enforce the use of this image in the metadata. To specify the image dimensions you can use the special notation <code>URL,WIDTHxHEIGHT</code>.', 'add-meta-tags').'
                '.__('Alternatively, you can select an image by pressing the <em>Select image</em> button.', 'add-meta-tags').'
                <br />
            </p>

        ');

            // Image preview
            $image_data = amt_get_image_data( amt_esc_id_or_url_notation( stripslashes( $custom_image_url_value ) ) );
            $img_html = '';
            if ( is_numeric($image_data['id']) ) {
                $main_size_meta = wp_get_attachment_image_src( $image_data['id'], 'medium' );
                $img_html = '<img src="' . esc_url($main_size_meta[0]) . '" width="' . esc_attr($main_size_meta[1]) . '" height="' . esc_attr($main_size_meta[2]) . '" />';
            } elseif ( ! is_null($image_data['url']) ) {
                $img_html = '<img src="' . esc_url($image_data['url']) . '" width="' . esc_attr($image_data['width']) . '" height="' . esc_attr($image_data['height']) . '" />';
            }
            if ( ! empty($img_html) ) {
                print('
                <p>'.__('Image preview', 'add-meta-tags').':</p>
                <br />
                <div id="amt-image-preview" class="amt-image-preview">' . $img_html . '</div>
                ');
            }

        print('

            </td>
            </tr>

        ');

    }

    print('
            </tbody>
        </table>
    ');

    // If no features have been enabled, print an informative message
    if ( $metabox_has_features === false ) {
        print('
            <p>'.__(sprintf( 'No features have been enabled for this metabox in the Add-Meta-Tags <a href="%s">settings</a> or you do not have enough permissions to access the available features.', admin_url( 'options-general.php?page=add-meta-tags-options' ) ), 'add-meta-tags').'</p>
        ');
    }

    print('
    </span> <!-- #add-meta-tags-settings -->
    ');

}


// When the user profile is saved
function amt_user_extra_fields_save( $user_id ) {

    /* Verify the nonce before proceeding. */
    // Verify this came from the our screen and with proper authorization,
    // because save_post can be triggered at other times
    if ( ! isset($_POST['amt_noncename']) || ! wp_verify_nonce( $_POST['amt_noncename'], plugin_basename( AMT_PLUGIN_FILE ) ) )
        return;

    // Get the Metadata metabox permissions (filtered)
    $metabox_permissions = amt_get_metadata_metabox_permissions();

    // Global Metadata metabox permission check (can be user customized via filter).
    if ( ! current_user_can( $metabox_permissions['global_metabox_capability'] ) ) {
        return;
    }

    // Get the Add-Meta-Tags options.
    $options = amt_get_options();

    // Check if the current user has permission to edit the post.
	if ( ! current_user_can( 'edit_published_posts' ) ) {
		return;
    }

    // OK, we're authenticated: we need to find and save the data

    //
    // Sanitize user input
    //

    // Full metatags - We allow only <meta> elements.
    if ( isset( $_POST['amt_custom_full_metatags'] ) ) {
        $full_metatags_value = esc_textarea( wp_kses( stripslashes( $_POST['amt_custom_full_metatags'] ), amt_get_allowed_html_kses() ) );
    }
    // Image URL
    if ( isset( $_POST['amt_custom_image_url'] ) ) {
        $image_url_value = amt_esc_id_or_url_notation( stripslashes( $_POST['amt_custom_image_url'] ) );
    }

    // If a value has not been entered we try to delete existing data from the database
    // If the user has entered data, store it in the database.

    // Add-Meta-Tags custom field names
    $amt_full_metatags_field_name = '_amt_user_full_metatags';
    $amt_image_url_field_name = '_amt_user_image_url';

    // As an extra security measure, here we also check the user-defined per box
    // permissions before we save any data in the database.

    // per user profile full meta tags
    if ( $options['metabox_user_enable_full_metatags'] == '1' && current_user_can( $metabox_permissions['user_full_metatags_box_capability'] ) ) {
        if ( empty($full_metatags_value) ) {
            delete_user_meta($user_id, $amt_full_metatags_field_name);
        } else {
            update_user_meta($user_id, $amt_full_metatags_field_name, $full_metatags_value);
        }
    }

    // Image URL
    if ( $options['metabox_user_enable_image_url'] == '1' && current_user_can( $metabox_permissions['user_image_url_box_capability'] ) ) {
        if ( empty($image_url_value) ) {
            delete_user_meta($user_id, $amt_image_url_field_name);
        } else {
            update_user_meta($user_id, $amt_image_url_field_name, $image_url_value);
        }
    }

}



