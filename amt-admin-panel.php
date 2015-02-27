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
 *  Copyright 2006-2013 George Notaras <gnot@g-loaded.eu>, CodeTRAX.org
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
    amt_plugin_upgrade();

    // Register scripts and styles

    /* Register our script for the color picker. */
    // wp_register_script( 'myPluginScript', plugins_url( 'script.js', AMT_PLUGIN_FILE ) );
    /* Register our stylesheet. */
    // wp_register_style( 'myPluginStylesheet', plugins_url( 'stylesheet.css', AMT_PLUGIN_FILE ) );

}
add_action( 'admin_init', 'amt_admin_init' );


function amt_admin_menu() {
    /* Register our plugin page */
    add_options_page( __('Metadata Settings', 'add-meta-tags'), __('Metadata', 'add-meta-tags'), 'manage_options', 'add-meta-tags-options', 'amt_options_page' );
}
add_action( 'admin_menu', 'amt_admin_menu');


/** Enqueue scripts and styles
 *  From: http://codex.wordpress.org/Plugin_API/Action_Reference/admin_enqueue_scripts#Example:_Target_a_Specific_Admin_Page
 */
function amt_enqueue_admin_scripts_and_styles( $hook ) {
    //var_dump($hook);
    if ( 'settings_page_add-meta-tags-options' != $hook ) {
        return;
    }
    // Enqueue script and style for the color picker.
    //wp_enqueue_script( 'myPluginScript' );
    //wp_enqueue_style( 'myPluginStylesheet' );
}
add_action( 'admin_enqueue_scripts', 'amt_enqueue_admin_scripts_and_styles' );
// Note: `admin_print_styles` should not be used to enqueue styles or scripts on the admin pages. Use `admin_enqueue_scripts` instead. 


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
    <div class="wrap">
        <div id="icon-options-general" class="icon32"><br /></div>
        <h2>'.__('Metadata Settings', 'add-meta-tags').'</h2>
        <p>'.__('Welcome to the administration panel of the Add-Meta-Tags plugin.', 'add-meta-tags').'</p>
        <p>'.__('<em>Metadata</em> refers to information that describes the content in a machine-friendly way. Search engines and other online services use this metadata to better understand your content. Keep in mind that metadata itself does not automatically make your blog rank better. For this to happen the content is still required to meet various quality standards. However, the presence of accurate and adequate metadata gives search engines and other services the chance to make less guesses about your content, index and categorize it better and, eventually, deliver it to an audience that finds it useful.  Good metadata facilitates this process and thus plays a significant role in achieving better rankings. This is what the Add-Meta-Tags plugin does.', 'add-meta-tags').'</p>
    </div>

    <div class="wrap" style="background: #EEF6E6; padding: 1em 2em; border: 1px solid #E4E4E4;' . (($options["i_have_donated"]=="1") ? ' display: none;' : '') . '">
        <h2>'.__('Message from the author', 'add-meta-tags').'</h2>
        <p style="font-size: 1.2em; padding-left: 2em;"><em>Add-Meta-Tags</em> is released under the terms of the <a href="http://www.apache.org/licenses/LICENSE-2.0.html">Apache License version 2</a> and, therefore, is <strong>free software</strong>.</p>
        <p style="font-size: 1.2em; padding-left: 2em;">However, a significant amount of <strong>time</strong> and <strong>energy</strong> has been put into developing this plugin, so, its production has not been free from cost. If you find this plugin useful and if it has helped your blog get indexed better and rank higher, I would appreciate an <a href="http://bit.ly/HvUakt">extra cup of coffee</a>.</p>
        <!--
        <p style="font-size: 1.2em; padding-left: 2em;">Donations in the following crypto currencies are also accepted and welcome. Send coins to the following addresses:</p>
        <ul style="font-size: 1.2em; padding-left: 2em;">
            <li style="padding-left: 1em;">- BitCoin (BTC): <code style="font-size: 1.2em;">1KkgpmaBKqQVk643VRhFRkL19Bbci4Mwn9</code></li>
            <li style="padding-left: 1em;">- LiteCoin (LTC): <code style="font-size: 1.2em;">LS8UF39LfLahzGo49y736ooRYBVT1zZ2Fa</code></li>
        </ul>
        -->
        <p style="font-size: 1.2em; padding-left: 2em;">Thank you in advance,<br />George Notaras</p>
        <div style="text-align: right;"><small>'.__('This message can be deactivated in the settings below.', 'add-meta-tags').'</small></div>
    </div>

    <div class="wrap">
        <h2>'.__('How it works', 'add-meta-tags').'</h2>
        
        <p>'.__('Add-Meta-Tags tries to follow the "<em>It just works</em>" principal. By default, the <em>description</em> and <em>keywords</em> meta tags are added to the front page, posts, pages, public custom post types, attachment pages, category, tag, custom taxonomy and author based archives. Furthermore, it is possible to enable the generation of <em>Opengraph</em>, <em>Dublin Core</em>, <em>Twitter Cards</em> and <em>Schema.org</em> metadata. The plugin also supports some extra SEO related functionality that helps you fine tune your web site.', 'add-meta-tags').'</p>
        
        <p>'.__('The automatically generated metadata can be further customized for each individual post, page, or any public custom post type directly from the <em>Metadata</em> box inside the post editing panel. If the <em>Metadata</em> box is not visible, you probably need to enable it at the <a href="http://en.support.wordpress.com/screen-options/">Screen Options</a> of the post editing panel.', 'add-meta-tags').'</p>

    </div>

    <div class="wrap">
        <h2>'.__('Configuration', 'add-meta-tags').'</h2>

        <p>'.__('This section contains global configuration options for the metadata that is added to your web site.', 'add-meta-tags').'</p>

        

        <form name="formamt" method="post" action="' . admin_url( 'options-general.php?page=add-meta-tags-options' ) . '">

        <table class="form-table">
        <tbody>
    ');

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
                
                <textarea name="site_description" id="site_description" cols="100" rows="2" class="code">' . esc_attr( stripslashes( $options["site_description"] ) ) . '</textarea>
                <br />
                <label for="site_description">
                    '.__('Enter a short (150-250 characters long) description of your blog. This text will be used in the <em>description</em> and other similar metatags on the <strong>front page</strong>. If this is left empty, then the blog\'s <em>Tagline</em> from the <a href="options-general.php">General Options</a> will be used.', 'add-meta-tags').'
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
                
                <textarea name="site_keywords" id="site_keywords" cols="100" rows="2" class="code">' . esc_attr( stripslashes( $options["site_keywords"] ) ) . '</textarea>
                <br />
                <label for="site_keywords">'.__('Enter a comma-delimited list of keywords for your blog. These keywords will be used in the <em>keywords</em> meta tag on the <strong>front page</strong>. If this field is left empty, then all of your blog\'s <a href="edit-tags.php?taxonomy=category">categories</a> will be used as keywords for the <em>keywords</em> meta tag.', 'add-meta-tags').'</label>
                <p><strong>'.__('Example', 'add-meta-tags').'</strong>: <code>'.__('keyword1, keyword2, keyword3', 'add-meta-tags').'</code></p>
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
                
                <textarea name="global_keywords" id="global_keywords" cols="100" rows="2" class="code">' . esc_attr( stripslashes( $options["global_keywords"] ) ) . '</textarea>
                <br />
                <label for="global_keywords">'.__('Enter a comma-delimited list of global keywords which will be added before the keywords of <strong>all</strong> posts and pages.', 'add-meta-tags').'</label>
                <p><strong>'.__('Example', 'add-meta-tags').'</strong>: <code>'.__('keyword1, keyword2, keyword3', 'add-meta-tags').'</code></p>
                <br />
                <p>'.__('By default, these keywords are prepended to the post/page\'s keywords. For enhanced flexibility, it is possible to use the <code>%contentkw%</code> placeholder, which will be populated with the post/page\'s autogenerated or user-defined keywords. This way you can globally both prepend and append keywords to the <em>keywords</em> of your content.', 'add-meta-tags').'</p>
                <p><strong>'.__('Example', 'add-meta-tags').'</strong>: <code>'.__('keyword1, keyword2, %contentkw%, keyword3', 'add-meta-tags').'</code></p>
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
                '.__('Provide the full XHTML code of extra META elements you would like to add to all the pages of your web site (read more about the <a href="http://en.wikipedia.org/wiki/Meta_element" target="_blank">META HTML element</a> on Wikipedia).', 'add-meta-tags').'
                </label>
                <p><strong>'.__('Examples', 'add-meta-tags').'</strong>:</p>
                <p><code>&lt;meta name="google-site-verification" content="1234567890" /&gt;</code></p>
                <p><code>&lt;meta name="msvalidate.01" content="1234567890" /&gt;</code></p>
                <p><code>&lt;meta name="robots" content="noimageindex" /&gt;</code></p>
                <p><code>&lt;meta property="fb:admins" content="1234" /&gt;</code></p>
                <p><code>&lt;meta property="fb:app_id" content="4321" /&gt;</code></p>
                <br />
            </fieldset>
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('Basic Metadata', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Basic Metadata', 'add-meta-tags').'</span></legend>

                <input id="auto_description" type="checkbox" value="1" name="auto_description" '. (($options["auto_description"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="auto_description">'.__('Automatically generate the <em>description</em> meta tag.', 'add-meta-tags').'</label>
                <p>'.__('If this option is enabled, the <em>description</em> meta tag is automatically generated for the content, attachments and archives. Customization of the <em>description</em> meta tag is possible through the <em>Metadata</em> box in the post editing screen.', 'add-meta-tags').'</p>
                <br />

                <input id="auto_keywords" type="checkbox" value="1" name="auto_keywords" '. (($options["auto_keywords"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="auto_keywords">'.__('Automatically generate the <em>keywords</em> meta tag.', 'add-meta-tags').'</label>
                <p>'.__('If this option is enabled, the <em>keywords</em> meta tag is automatically generated for the content and archives. Keywords are not generated automatically on pages and attachments. Customization of the <em>keywords</em> meta tag is possible through the <em>Metadata</em> box in the post editing screen.', 'add-meta-tags').'</p>
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
                <p>'.__('If this option is enabled, Opengraph metadata is automatically generated for content, attachments and archives. For more information, please refer to the <a href="http://ogp.me">Opengraph specification</a>.', 'add-meta-tags').'</p>

                <h4>'.__('Important Note', 'add-meta-tags').'</h4>

                <p>'.__('By default, this feature sets the URL of the front page of your web site to the <code>article:publisher</code> meta tag and the URL of the author archive to the <code>article:author</code> meta tag. In order to link to the publisher page and the author profile on Facebook, it is required to provide the respective URLs. Enter these settings can be added to your WordPress user <a href="profile.php">profile page</a> under the section <em>Contact Info</em>. For example:', 'add-meta-tags').'</p>
                <blockquote>Facebook author profile URL (AMT): <code>https://www.facebook.com/john.smith</code></blockquote>
                <blockquote>Facebook publisher profile URL (AMT): <code>https://www.facebook.com/awesome.editors</code></blockquote>
                <p>'.__('It is possible to control the value of the <code>og:type</code> meta tag either by changing the post format or programmatically via a filter. By default, the <code>og:type</code> is set to <code>article</code> for all content, except for video attachment pages and posts whose post format has been set to <code>video</code>, on which <code>og:type</code> is set to <code>video.other</code>.', 'add-meta-tags').'</p>
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
                <p>'.__('If this option is enabled, Twitter Cards metadata is automatically generated for content and attachments. For more information, please refer to the <a href="https://dev.twitter.com/docs/cards">Twitter Cards specification</a>.', 'add-meta-tags').'</p>
                <br />

                <input id="tc_enable_player_card_local" type="checkbox" value="1" name="tc_enable_player_card_local" '. (($options["tc_enable_player_card_local"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="tc_enable_player_card_local">'.__('Enable the generation of <em>player</em> cards for local media.', 'add-meta-tags').'</label>
                <p>'.__('Enable the generation of <em>player</em> cards for locally hosted audio and video attachments or for posts with their post format set to audio or video. In the latter case, an audio or video is expected to be attached to the post respectively. A mandatory requirement for this feature, as outlined in the Twitter Cards specifications, is secure access to your web site over the HTTPS protocol. If secure access to your web site has not been configured properly, the player cards will not be rendered by the Twitter service. Moreover, using self-signed certificates could cause problems which might be hard to identify. This feature should be considered experimental.', 'add-meta-tags').'</p>

                <h4>'.__('Important Notes', 'add-meta-tags').'</h4>

                <p>'.__('In order to generate the <code>twitter:site</code> and <code>twitter:creator</code> meta tags, it is required to provide the respective usernames of the Twitter account of the author and/or the publisher of the content. Update your WordPress user\'s <a href="profile.php">profile page</a> and fill in the relevant usernames under the section <em>Contact Info</em>.', 'add-meta-tags').'</p>
                <blockquote>Twitter author username (AMT): <code>JohnSmith</code></blockquote>
                <blockquote>Twitter publisher username (AMT): <code>AwesomeEditors</code></blockquote>

                <p>'.__('By default, a Twitter Card of type <em>summary</em> is generated for your content. If your theme supports <a href="http://codex.wordpress.org/Post_Formats">post formats</a>, then it is possible to generate Twitter Cards of various types according to the specified post format. More specifically:', 'add-meta-tags').'</p>
                <blockquote>A <code>summary</code> card is generated for posts with one of the <code>standard</code>, <code>aside</code>, <code>link</code>, <code>quote</code>, <code>status</code> and <code>chat</code> formats.</blockquote>
                <blockquote>A <code>summary_large_image</code> card is generated for posts with the <code>image</code> format. An image is expected to be attached or embedded to the post.</blockquote>
                <blockquote>A <code>gallery</code> card is generated for posts with the <code>gallery</code> format. At least one image is expected to be attached or embedded to the post.</blockquote>
                <blockquote>A <code>photo</code> card is generated for image attachment pages.</blockquote>
                <blockquote>A <code>player</code> card is generated for posts with the <code>audio</code> or <code>video</code> format and for audio or video attachment pages. Regarding posts, an audio or video is expected to be attached or embedded to the post.</blockquote>
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
                <p>'.__('If this option is enabled, Dublin Core metadata is automatically generated for your content and attachments. For more information, please refer to <a href="http://dublincore.org">Dublin Core Metadata Initiative</a>.', 'add-meta-tags').'</p>
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
                <p>'.__('Automatically generate Schema.org Microdata and embed it to your content. This feature embeds <code>meta</code> elements inside the body of the web page. This is compatible with the HTML 5 standard, so, before enabling it, make sure your theme is HTML 5 ready. For information about Microdata please refer to <a href="http://schema.org">Schema.org</a>.', 'add-meta-tags').'</p>

                <h4>'.__('Important Notes', 'add-meta-tags').'</h4>

                <p>'.__('By default, this feature links the author and publisher objects to the author archive and to the front page of your web site respectively. In order to link to the author\'s profile and publisher\'s page on Google+, it is required to provide the respective URLs. These settings can be added to your WordPress user <a href="profile.php">profile page</a> under the section <em>Contact Info</em>.', 'add-meta-tags').'</p>
                <blockquote>Google+ author profile URL (AMT): <code>https://plus.google.com/+JohnSmith/</code></blockquote>
                <blockquote>Google+ publisher page URL (AMT): <code>https://plus.google.com/+AwesomeEditors/</code></blockquote>

                <p>'.__('Once you have filled in the URLs to the author profile and the publisher page on Google+, the relevant link elements with the attributes <code>rel="author"</code> and <code>rel="publisher"</code> are automatically added to the head area of the web page.', 'add-meta-tags').'</p>
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
                '.__('Custom description (<em>Recommended</em>).', 'add-meta-tags').'
                </label></p>

                <p><input id="metabox_enable_keywords" type="checkbox" value="1" name="metabox_enable_keywords" '. (($options["metabox_enable_keywords"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="metabox_enable_keywords">
                '.__('Custom keywords (<em>Recommended</em>).', 'add-meta-tags').'
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

                <p><input id="metabox_enable_referenced_list" type="checkbox" value="1" name="metabox_enable_referenced_list" '. (($options["metabox_enable_referenced_list"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="metabox_enable_referenced_list">
                '.__('Referenced items. (Experimental feature. Not recommended.)', 'add-meta-tags').'
                </label></p>

                <p>'.__('The metabox feature selection above affects all users. Advanced customization of the availability of these features on a per user basis or depending upon each user\'s permissions, is possible through the <code>amt_metadata_metabox_permissions</code> filter.', 'add-meta-tags').'</p>

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
                <p>'.__('If checked, <code>NOODP</code> and <code>NOYDIR</code> are added to the <em>robots</em> meta tag on the front page, content and attachments. This setting will prevent all search engines (at least those that support the meta tag) from displaying information from the <a href="http://www.dmoz.org/">Open Directory Project</a> or the <a href="http://dir.yahoo.com/">Yahoo Directory</a> instead of the description you set in the <em>description</em> meta tag.', 'add-meta-tags').'</p>
                <br />

                <p>'.__('Add <code>NOINDEX,FOLLOW</code> to the <em>robots</em> meta tag on following types of archives. This is an advanced setting that aims at reducing the amount of duplicate content that gets indexed by search engines:', 'add-meta-tags').'</p>

                <p><input id="noindex_search_results" type="checkbox" value="1" name="noindex_search_results" '. (($options["noindex_search_results"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="noindex_search_results">
                '.__('Search results. (<em>Highly recommended</em>)', 'add-meta-tags').'
                </label></p>

                <p><input id="noindex_date_archives" type="checkbox" value="1" name="noindex_date_archives" '. (($options["noindex_date_archives"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="noindex_date_archives">
                '.__('Date based archives. (<em>Recommended</em>)', 'add-meta-tags').'
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
            </fieldset>
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('Copyright URL', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Copyright URL', 'add-meta-tags').'</span></legend>

                <input name="copyright_url" type="text" id="copyright_url" class="code" value="' . esc_url_raw( stripslashes( $options["copyright_url"] ) ) . '" size="100" maxlength="1024" />
                <br />
                <label for="copyright_url">
                '.__('Enter an absolute URL to a document containing copyright and licensing information about your work. If this URL is set, the relevant meta tags will be added automatically on all the pages of your web site.', 'add-meta-tags').'
                </label>
                <p><strong>'.__('Example', 'add-meta-tags').'</strong>: <code>http://example.org/copyright.html</code></p>
                <br />
            </fieldset>
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('Default Image', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Default Image', 'add-meta-tags').'</span></legend>

                <input name="default_image_url" type="text" id="default_image_url" class="code" value="' . esc_url_raw( stripslashes( $options["default_image_url"] ) ) . '" size="100" maxlength="1024" />
                <br />
                <label for="default_image_url">
                '.__('Enter an absolute URL to an image that represents your website, for instance the logo. This image will be used in the metadata of the front page and also in the metadata of the content, in case no featured image or other images have been attached or embedded.', 'add-meta-tags').'
                </label>
                <p><strong>'.__('Example', 'add-meta-tags').'</strong>: <code>http://example.org/images/logo.png</code></p>
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
                <label for="review_mode">'.__('Enable <em>Metadata Review Mode</em>.').'</label>
                <p>'.__('If enabled, WordPress users with administrator privileges see a box (right above the post\'s content) containing the metadata exactly as it is added in the HTML head and body. The box is displayed for posts, pages, attachments and custom post types.', 'add-meta-tags').'</p>
                <br />
            </fieldset>
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('Donations', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Donations', 'add-meta-tags').'</span></legend>

                <input id="i_have_donated" type="checkbox" value="1" name="i_have_donated" '. (($options["i_have_donated"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="i_have_donated">'.sprintf( __('If checked, the <em>message from the author</em> above goes away. Thanks for <a href="%s">donating</a>!', 'add-meta-tags'), 'http://bit.ly/HvUakt').'</label>
                <br />
            </fieldset>
            </td>
            </tr>

        </tbody>
        </table>

        <!-- Submit Buttons -->
        <table class="form-table">
        <tbody>
            <tr valign="top">
                <th scope="row">
                    <input id="submit" class="button-primary" type="submit" value="'.__('Save Changes', 'add-meta-tags').'" name="info_update" />
                </th>
                <th scope="row">
                    <input id="reset" class="button-primary" type="submit" value="'.__('Reset to defaults', 'add-meta-tags').'" name="info_reset" />
                </th>
                <th></th><th></th><th></th><th></th>
            </tr>
        </tbody>
        </table>

        </form>
        
    </div>

    ');

}



/**
 * Meta box in post/page editing panel.
 */

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
            __( 'Metadata', 'add-meta-tags' ),
            'amt_inner_metadata_box',
            $supported_type,
            'advanced',
            'high'
        );
    }

}


/**
 * Load CSS and JS for metadata box.
 * The editing pages are post.php and post-new.php
 */
// add_action('admin_print_styles-post.php', 'amt_metadata_box_css_js');
// add_action('admin_print_styles-post-new.php', 'amt_metadata_box_css_js');

function amt_metadata_box_css_js () {
    // $supported_types = amt_get_post_types_for_metabox();
    // See: #900 for details

    // Using included Jquery UI
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-widget');
    wp_enqueue_script('jquery-ui-tabs');

    //wp_register_style( 'amt-jquery-ui-core', plugins_url('css/jquery.ui.core.css', AMT_PLUGIN_FILE) );
    //wp_enqueue_style( 'amt-jquery-ui-core' );
    //wp_register_style( 'amt-jquery-ui-tabs', plugins_url('css/jquery.ui.tabs.css', AMT_PLUGIN_FILE) );
    //wp_enqueue_style( 'amt-jquery-ui-tabs' );
    wp_register_style( 'amt-metabox-tabs', plugins_url('css/amt-metabox-tabs.css', AMT_PLUGIN_FILE) );
    wp_enqueue_style( 'amt-metabox-tabs' );

}


/* For future reference - Add data to the HEAD area of post editing panel */

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
                '.__('Enter a custom title to be used in the <em>title</em> tag.', 'add-meta-tags').'
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
                <textarea class="code" style="width: 99%" id="amt_custom_full_metatags" name="amt_custom_full_metatags" cols="30" rows="4" >'. stripslashes( $custom_full_metatags_value ) .'</textarea>
                <br>
                '.__('Provide the full XHTML code of extra META elements you would like to add to this content (read more about the <a href="http://en.wikipedia.org/wiki/Meta_element" target="_blank">META HTML element</a> on Wikipedia).', 'add-meta-tags').'
            </p>
            <p>
                '.__('For example, to prevent a cached copy of this content from being available in search engine results, you can add the following metatag:', 'add-meta-tags').'
                <br /><code>&lt;meta name="robots" content="noarchive" /&gt;</code>
            </p>

            <p>
                '.__('Important note: for security reasons only <code>meta</code> elements are allowed in this box. All other HTML elements are automatically removed.', 'add-meta-tags').'
            </p>
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
                '.__('Enter a list of canonical URLs (one per line) of items referenced in the content. The page referenced need not be on the same domain as the content. For example, you might reference a page where a product can be purchased or a page that further describes a place. If such references are provided and if OpenGraph/Schema.org metadata is enabled, then the relevant <code>og:referenced</code> and <code>referencedItem</code> meta tags will be generated.', 'add-meta-tags').' (<span style="color:red;">EXPERIMENTAL</span>)
            </p>
        ');

    }


    // If no features have been enabled, print an informative message
    if ( $metabox_has_features === false ) {
        print('
            <p>'.__(sprintf( 'No features have been enabled for this metabox in the Add-Meta-Tags plugin <a href="%s">settings</a> or you do not have enough permissions to access the available features.', admin_url( 'options-general.php?page=add-meta-tags-options' ) ), 'add-meta-tags').'</p>
        ');
    }

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

    // Referenced list
    if ( $options['metabox_enable_referenced_list'] == '1' && current_user_can( $metabox_permissions['referenced_list_box_capability'] ) ) {
        if ( empty($referenced_list_value) ) {
            delete_post_meta($post_id, $amt_referenced_list_field_name);
        } else {
            update_post_meta($post_id, $amt_referenced_list_field_name, $referenced_list_value);
        }
    }
    
}


