<?php
/**
 * Module containing the admin panel and metabox code.
 */


/**
 * Administration Panel - Add-Meta-Tags Settings
 */

function amt_add_pages() {
    add_options_page(__('Metadata Settings', 'add-meta-tags'), __('Metadata', 'add-meta-tags'), 'manage_options', 'add-meta-tags-options', 'amt_options_page');
}
add_action('admin_menu', 'amt_add_pages');


function amt_show_info_msg($msg) {
    echo '<div id="message" class="updated fade"><p>' . $msg . '</p></div>';
}




function amt_options_page() {
    // Permission Check
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }

    // Default Add-Meta-Tags Settings
    $default_options = amt_get_default_options();

    if (isset($_POST['info_update'])) {

        amt_save_settings($_POST);

    } elseif (isset($_POST["info_reset"])) {

        amt_reset_settings();

    }

    // Get the options from the DB.
    $options = get_option("add_meta_tags_opts");

    // var_dump($options);

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
        <p style="font-size: 1.2em; padding-left: 2em;">However, a significant amount of <strong>time</strong> and <strong>energy</strong> has been put into developing this plugin, so, its production has not been free from cost. If you find this plugin useful and if it has helped your blog get indexed better and rank higher, I would appreciate an <a href="http://www.g-loaded.eu/about/donate/">extra cup of coffee</a>.</p>
        <p style="font-size: 1.2em; padding-left: 2em;">Thank you in advance,<br />George Notaras</p>
        <div style="text-align: right;"><small>'.__('This message can be deactivated in the settings below.', 'add-meta-tags').'</small></div>
    </div>

    <div class="wrap">
        <h2>'.__('How it works', 'add-meta-tags').'</h2>
        
        <p>'.__('Add-Meta-Tags tries to follow the "<em>It just works</em>" principal. By default, the <em>description</em> and <em>keywords</em> meta tags are added to your blog\'s front page, posts, pages, category and tag based archives. Furthermore, it is possible to enable the insertion of <em>Opengraph</em> and <em>Dublin Core</em> metadata to your posts and pages. The plugin also supports some extra SEO related functionality that helps you fine tune your web site.', 'add-meta-tags').'</p>
        
        <p>'.__('Customization of the added metadata on a per post/page basis is possible by using the user-friendly <strong>WordPress meta boxes</strong> in the post/page editing panel. In earlier versions of the plugin (before 2.1.0) such customization was possible through custom fields. If you have already used custom fields in order to customize the posts description and keywords in the past, there is nothing to worry about, since the new meta-box functionality is internally based on those custom fields, so there is no migration procedure involved. However, you need to enable the <em>Metadata</em> meta box in the <a href="http://en.support.wordpress.com/screen-options/">Screen Options</a> of the post/page editing panel.', 'add-meta-tags').'</p>

    </div>

    <div class="wrap">
        <h2>'.__('Configuration', 'add-meta-tags').'</h2>

        <p>'.__('This section contains global configuration options for the metadata that is added to your web site.', 'add-meta-tags').'</p>

        

        <form name="formamt" method="post" action="' . $_SERVER['REQUEST_URI'] . '">

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
                '.__('It appears that you use static pages on the <em>front</em> page and the <em>posts</em> index of this web site. Please visit the editing panel of these pages and set the <code>description</code> and <code>keywords</code> meta tags in the relevant Metadata box. (since v2.2.0)', 'add-meta-tags').'
                ');
                print('<ul>');
                $front_page_id = get_option('page_on_front');
                if ( intval($front_page_id) > 0 ) {
                    print('<li>&raquo; '.__('Edit the', 'add-meta-tags').' <a href="'.get_edit_post_link(intval($front_page_id)).'">'.__('front page', 'add-meta-tags').'</a></li>');
                }
                $posts_page_id = get_option('page_for_posts');
                if ( intval($posts_page_id) > 0 ) {
                    print('<li>&raquo; '.__('Edit the', 'add-meta-tags').' <a href="'.get_edit_post_link(intval($posts_page_id)).'">'.__('posts page', 'add-meta-tags').'</a></li>');
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
                <label for="site_description">
                    <textarea name="site_description" id="site_description" cols="100" rows="2" class="code">' . stripslashes($options["site_description"]) . '</textarea>
                    <br />
                    '.__('Enter a short (150-250 characters long) description of your blog. This text will be used in the <em>description</em> meta tag and the <em>og:description</em> meta property (if Opengraph is enabled) on the <strong>front page</strong>. If this is left empty, then the blog\'s description from the <em>Tagline</em> in <a href="' . get_bloginfo('wpurl') . '/wp-admin/options-general.php">General Options</a> will be used.', 'add-meta-tags').'
                </label>
            </fieldset>
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('Front Page Keywords', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Front Page Keywords', 'add-meta-tags').'</span></legend>
                <label for="site_keywords">
                    <textarea name="site_keywords" id="site_keywords" cols="100" rows="2" class="code">' . stripslashes($options["site_keywords"]) . '</textarea>
                    <br />
                    '.__('Enter a comma-delimited list of keywords for your blog. These keywords will be used for the <em>keywords</em> meta tag on the <strong>front page</strong>. If this field is left empty, then all of your blog\'s <a href="' . get_bloginfo('wpurl') . '/wp-admin/edit-tags.php?taxonomy=category">categories</a> will be used as keywords for the <em>keywords</em> meta tag.', 'add-meta-tags').'
                    <br />
                    <strong>'.__('Example', 'add-meta-tags').'</strong>: <code>'.__('keyword1, keyword2, keyword3', 'add-meta-tags').'</code>
                </label>
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
                <label for="global_keywords">
                    <textarea name="global_keywords" id="global_keywords" cols="100" rows="2" class="code">' . stripslashes($options["global_keywords"]) . '</textarea>
                    <br />
                    '.__('Enter a comma-delimited list of global keywords which will be added before the keywords of <strong>all</strong> posts and pages.', 'add-meta-tags').'
                    <br />
                    <strong>'.__('Example', 'add-meta-tags').'</strong>: <code>'.__('keyword1, keyword2, keyword3', 'add-meta-tags').'</code>
                    <br />
                    '.__('By default, these keywords are prepended to the post/page\'s keywords. For enhanced flexibility, it is possible to use the <code>%contentkw%</code> placeholder, which will be populated with the post/page\'s autogenerated or user-defined keywords. This way you can globally both prepend and append keywords to the <em>keywords</em> of your content.', 'add-meta-tags').'
                    <br />
                    <strong>'.__('Example', 'add-meta-tags').'</strong>: <code>'.__('keyword1, keyword2, %contentkw%, keyword3', 'add-meta-tags').'</code>
                </label>
            </fieldset>
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('Site-wide META tags', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Site-wide META tags', 'add-meta-tags').'</span></legend>
                <label for="site_wide_meta">
                    <textarea name="site_wide_meta" id="site_wide_meta" cols="100" rows="10" class="code">' . stripslashes($options["site_wide_meta"]) . '</textarea>
                    <br />
                    '.__('Provide the <strong>full XHTML code</strong> of META tags you would like to be included in <strong>all</strong> of your blog pages.', 'add-meta-tags').'
                    <br />
                    <strong>'.__('Examples', 'add-meta-tags').'</strong>:
                    <br /><code>&lt;meta name="google-site-verification" content="1234567890" /&gt;</code>
                    <br /><code>&lt;meta name="msvalidate.01" content="1234567890" /&gt;</code>
                </label>
            </fieldset>
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('Automatic Basic Metadata', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Automatic Basic Metadata', 'add-meta-tags').'</span></legend>

                <input id="auto_description" type="checkbox" value="1" name="auto_description" '. (($options["auto_description"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="auto_description">
                '.__('Automatically generate the <em>description</em> meta tag for single posts, pages, category-based archives and tag-based archives. If this is unchecked, you can still set a <em>description</em> meta tag by using the <code>description</code> custom field.', 'add-meta-tags').'
                </label>
                <br />
                
                <input id="auto_keywords" type="checkbox" value="1" name="auto_keywords" '. (($options["auto_keywords"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="auto_keywords">
                '.__('Automatically generate the <em>keywords</em> meta tag for single posts, category-based archives and tag-based archives. Automatic keywords are not supported on pages. If this is unchecked, you can still set a <em>keywords</em> meta tag by using the <code>keywords</code> custom field.', 'add-meta-tags').'
                </label>
                <br />

            </fieldset>
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('Automatic Opengraph Metadata', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Automatic Opengraph Metadata', 'add-meta-tags').'</span></legend>

                <input id="auto_opengraph" type="checkbox" value="1" name="auto_opengraph" '. (($options["auto_opengraph"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="auto_opengraph">
                '.__('Automatically generate Opengraph meta tags for single posts and pages. For more information, please refer to the <a href="http://ogp.me">Opengraph specification</a>.', 'add-meta-tags').'
                </label>
                <br />
                <strong>'.__('Important Note', 'add-meta-tags').'</strong>:
                <br />
                '.__('In order to generate the <code>article:author</code> and <code>article:publisher</code> meta tags for your content, it is required to provide the respective URLs of the Facebook profiles of the author and/or publisher of the content. Update your WordPress user\'s <a href="/wp-admin/profile.php">profile page</a> and fill in the relevant URLs under the section \'Contact Info\'.', 'add-meta-tags').'
                <br />
            </fieldset>
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('Automatic Dublin Core Metadata', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Automatic Dublin Core Metadata', 'add-meta-tags').'</span></legend>

                <input id="auto_dublincore" type="checkbox" value="1" name="auto_dublincore" '. (($options["auto_dublincore"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="auto_dublincore">
                '.__('Automatically generate Dublin Core metadata for single posts and pages. For more information, please refer to <a href="http://dublincore.org">Dublin Core Metadata Initiative</a>.', 'add-meta-tags').'
                </label>
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
                <label for="noodp_description">
                '.__('Add <code>NOODP</code> and <code>NOYDIR</code> to the <em>robots</em> meta tag on the front page, posts and pages. This setting will prevent all search engines (at least those that support the meta tag) from displaying information from the <a href="http://www.dmoz.org/">Open Directory Project</a> or the <a href="http://dir.yahoo.com/">Yahoo Directory</a> instead of the description you set in the <em>description</em> meta tag.', 'add-meta-tags').'
                </label>
                <br />
                <br />

                '.__('Add <code>NOINDEX,FOLLOW</code> to the <em>robots</em> meta tag on following types of archives. This is an advanced setting that aims at reducing the amount of duplicate content that gets indexed by search engines:', 'add-meta-tags').'
                <br />

                <input id="noindex_search_results" type="checkbox" value="1" name="noindex_search_results" '. (($options["noindex_search_results"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="noindex_search_results">
                '.__('Search results (<em>Highly recommended</em>)', 'add-meta-tags').'
                </label>
                <br />

                <input id="noindex_date_archives" type="checkbox" value="1" name="noindex_date_archives" '. (($options["noindex_date_archives"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="noindex_date_archives">
                '.__('Date based archives (<em>Recommended</em>)', 'add-meta-tags').'
                </label>
                <br />

                <input id="noindex_category_archives" type="checkbox" value="1" name="noindex_category_archives" '. (($options["noindex_category_archives"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="noindex_category_archives">
                '.__('Category based archives', 'add-meta-tags').'
                </label>
                <br />

                <input id="noindex_tag_archives" type="checkbox" value="1" name="noindex_tag_archives" '. (($options["noindex_tag_archives"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="noindex_tag_archives">
                '.__('Tag based archives', 'add-meta-tags').'
                </label>
                <br />

                <input id="noindex_author_archives" type="checkbox" value="1" name="noindex_author_archives" '. (($options["noindex_author_archives"]=="1") ? 'checked="checked"' : '') .'" />
                <label for="noindex_author_archives">
                '.__('Author based archives', 'add-meta-tags').'
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
                <input name="copyright_url" type="text" id="copyright_url" class="code" value="' . $options["copyright_url"] . '" size="100" maxlength="1024" />
                <br />
                <label for="copyright_url">
                '.__('Add an absolute URL to a document containing information about copyright. The relevant meta tags will be added automatically.', 'add-meta-tags').'
                <br />
                <strong>'.__('Example', 'add-meta-tags').'</strong>: <code>http://example.org/copyright.html</code>
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
                <input name="default_image_url" type="text" id="default_image_url" class="code" value="' . $options["default_image_url"] . '" size="100" maxlength="1024" />
                <br />
                <label for="default_image_url">
                '.__('Add an absolute URL to an image that will be used in meta data in case a featured image has not been set for the content.', 'add-meta-tags').'
                <br />
                <strong>'.__('Example', 'add-meta-tags').'</strong>: <code>http://example.org/images/default.png</code>
                </label>
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
                <label for="review_mode">
                '.__('Enable <em>Metadata Review Mode</em>. When enabled, WordPress users with administrator privileges see a box containing the metadata exactly as it is added in the HTML head. The box is displayed for posts, pages, attachments and custom post types.', 'add-meta-tags').'
                </label>
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
                <label for="i_have_donated">
                '.__('By checking this, the <em>message from the author</em> above goes away. Thanks for <a href="http://www.g-loaded.eu/about/donate/">donating</a>!', 'add-meta-tags').'
                </label>
                <br />
            </fieldset>
            </td>
            </tr>

        </tbody>
        </table>

        <p class="submit">
            <input id="submit" class="button-primary" type="submit" value="'.__('Save changes', 'add-meta-tags').'" name="info_update" />
            <input id="reset" class="button-primary" type="submit" value="'.__('Reset to defaults', 'add-meta-tags').'" name="info_reset" />
        </p>

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
 * See the amt_get_supported_post_types() docstring for more info on the supported types.
 */
function amt_add_metadata_box() {
    $supported_types = amt_get_supported_post_types();

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
    // $supported_types = amt_get_supported_post_types();
    // See: #900 for details

    // Using included Jquery UI
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-widget');
    wp_enqueue_script('jquery-ui-tabs');

    //wp_register_style( 'amt-jquery-ui-core', plugins_url('css/jquery.ui.core.css', __FILE__) );
    //wp_enqueue_style( 'amt-jquery-ui-core' );
    //wp_register_style( 'amt-jquery-ui-tabs', plugins_url('css/jquery.ui.tabs.css', __FILE__) );
    //wp_enqueue_style( 'amt-jquery-ui-tabs' );
    wp_register_style( 'amt-metabox-tabs', plugins_url('css/amt-metabox-tabs.css', __FILE__) );
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
    wp_nonce_field( plugin_basename( __FILE__ ), 'amt_noncename' );

    // Get the post type. Will be used to customize the displayed notes.
    $post_type = get_post_type( $post->ID );

    // Display the meta box HTML code.

    // Custom description
    
    // Retrieve the field data from the database.
    $custom_description_value = amt_get_post_meta_description( $post->ID );

    print('
        <p>
            <label for="amt_custom_description">'.__('Description', 'add-meta-tags').':</label>
            <textarea class="code" style="width: 99%" id="amt_custom_description" name="amt_custom_description" cols="30" rows="2" >'.$custom_description_value.'</textarea>
            <br>
            ('.__('Enter a custom description of 20-40 words - based on an average word length of 5 characters', 'add-meta-tags').')
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

    // Custom keywords

    // Retrieve the field data from the database.
    $custom_keywords_value = amt_get_post_meta_keywords( $post->ID );

    // Alt input:  <input type="text" class="code" style="width: 99%" id="amt_custom_keywords" name="amt_custom_keywords" value="'.$custom_keywords_value.'" />
    print('
        <p>
            <label for="amt_custom_keywords">'.__('Keywords', 'add-meta-tags').':</label>
            <textarea class="code" style="width: 99%" id="amt_custom_keywords" name="amt_custom_keywords" cols="30" rows="2" >'.$custom_keywords_value.'</textarea>
            <br>
            ('.__('Separate keywords with commas', 'add-meta-tags').')
        </p>
    ');
    // Different notes based on post type
    if ( $post_type == 'post' ) {
        print('
            <p>
                '.__('If the <em>keywords</em> field is left blank, a <em>keywords</em> meta tag will be <strong>automatically</strong> generated from the post\'s categories and tags. In case you decide to set a custom list of keywords for this post, it is possible to easily include the post\'s categories and keywords in that list by using the special placeholders <code>%cats%</code> and <code>%tags%</code> respectively.', 'add-meta-tags').'
                <br />
                '.__('Example', 'add-meta-tags').': <code>keyword1, keyword2, %cats%, keyword3, %tags%, keyword4</code>
            </p>
        ');
    } elseif ( $post_type == 'page' ) {
        print('
            <p>
                '.__('If the <em>keywords</em> field is left blank, a <em>keywords</em> meta tag <strong>will not be automatically</strong> generated.', 'add-meta-tags').'
            </p>
        ');
    } else {    // Custom post types
        print('
            <p>
                '.__('If the <em>keywords</em> field is left blank, a <em>keywords</em> meta tag <strong>will not be automatically</strong> generated.', 'add-meta-tags').'
            </p>
        ');
    }

    // Advanced options

    // Custom title tag

    // Retrieve the field data from the database.
    $custom_title_value = amt_get_post_meta_title( $post->ID );

    print('
        <p>
            <label for="amt_custom_title">'.__('Title', 'add-meta-tags').':</label>
            <input type="text" class="code" style="width: 99%" id="amt_custom_title" name="amt_custom_title" value="'.$custom_title_value.'" />
            <br>
            '.__('Enter a custom title to be used in the <em>title</em> tag. <code>%title%</code> is expanded to the current title.', 'add-meta-tags').'
        </p>
    ');

    // 'news_keywords' meta tag
    
    // Retrieve the field data from the database.
    $custom_newskeywords_value = amt_get_post_meta_newskeywords( $post->ID );

    print('
        <p>
            <label for="amt_custom_newskeywords">'.__('News Keywords', 'add-meta-tags').':</label>
            <input type="text" class="code" style="width: 99%" id="amt_custom_newskeywords" name="amt_custom_newskeywords" value="'.$custom_newskeywords_value.'" />
            <br>
            '.__('Enter a comma-delimited list of <strong>news keywords</strong>. For more info about this meta tag, please see this <a target="_blank" href="http://support.google.com/news/publisher/bin/answer.py?hl=en&answer=68297">Google help page</a>.', 'add-meta-tags').'
        </p>
    ');

    // per post full meta tags
    
    // Retrieve the field data from the database.
    $custom_full_metatags_value = amt_get_post_meta_full_metatags( $post->ID );

    print('
        <p>
            <label for="amt_custom_full_metatags">'.__('Full meta tags', 'add-meta-tags').':</label>
            <textarea class="code" style="width: 99%" id="amt_custom_full_metatags" name="amt_custom_full_metatags" cols="30" rows="2" >'. stripslashes($custom_full_metatags_value) .'</textarea>
            <br>
            '.__('Enter full meta tags specific to this content.', 'add-meta-tags').'
        </p>
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
    if ( !isset($_POST['amt_noncename']) || !wp_verify_nonce( $_POST['amt_noncename'], plugin_basename( __FILE__ ) ) )
        return;

    /* Get the post type object. */
	$post_type_obj = get_post_type_object( $post->post_type );

    /* Check if the current user has permission to edit the post. */
	if ( !current_user_can( $post_type_obj->cap->edit_post, $post_id ) )
		return;

    // OK, we're authenticated: we need to find and save the data

    //
    // Sanitize user input
    //
    // $description_value = sanitize_text_field( $_POST['amt_custom_description'] );
    // TODO: sanitize removes '%ca' part of '%cats%'
    // $keywords_value = sanitize_text_field( $_POST['amt_custom_keywords'] );
    $description_value = $_POST['amt_custom_description'];
    $keywords_value = $_POST['amt_custom_keywords'];
    $title_value = $_POST['amt_custom_title'];
    $newskeywords_value = $_POST['amt_custom_newskeywords'];
    // Full metatags
    $full_metatags_value = esc_textarea( stripslashes( wp_kses( $_POST['amt_custom_full_metatags'], get_allowed_html_kses() ) ) );

    // If a value has not been entered we try to delete existing data from the database
    // If the user has entered data, store it in the database.

    // Add-Meta-Tags custom field names
    $amt_description_field_name = '_amt_description';
    $amt_keywords_field_name = '_amt_keywords';
    $amt_title_field_name = '_amt_title';
    $amt_newskeywords_field_name = '_amt_news_keywords';
    $amt_full_metatags_field_name = '_amt_full_metatags';

    // Description
    if ( empty($description_value) ) {
        delete_post_meta($post_id, $amt_description_field_name);
        // Also clean up old description field
        delete_post_meta($post_id, 'description');
    } else {
        update_post_meta($post_id, $amt_description_field_name, $description_value);
        // Also clean up again old description field - no need to exist any more since the new field is used.
        delete_post_meta($post_id, 'description');
    }

    // Keywords
    if ( empty($keywords_value) ) {
        delete_post_meta($post_id, $amt_keywords_field_name);
        // Also clean up old keywords field
        delete_post_meta($post_id, 'keywords');
    } else {
        update_post_meta($post_id, $amt_keywords_field_name, $keywords_value);
        // Also clean up again old keywords field - no need to exist any more since the new field is used.
        delete_post_meta($post_id, 'keywords');
    }

    // Title
    if ( empty($title_value) ) {
        delete_post_meta($post_id, $amt_title_field_name);
    } else {
        update_post_meta($post_id, $amt_title_field_name, $title_value);
    }

    // 'news_keywords'
    if ( empty($newskeywords_value) ) {
        delete_post_meta($post_id, $amt_newskeywords_field_name);
    } else {
        update_post_meta($post_id, $amt_newskeywords_field_name, $newskeywords_value);
    }

    // per post full meta tags
    if ( empty($full_metatags_value) ) {
        delete_post_meta($post_id, $amt_full_metatags_field_name);
    } else {
        update_post_meta($post_id, $amt_full_metatags_field_name, $full_metatags_value);
    }
    
}


