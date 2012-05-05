<?php
/*
Plugin Name: Add Meta Tags
Plugin URI: http://www.g-loaded.eu/2006/01/05/add-meta-tags-wordpress-plugin/
Description: Adds the <em>Description</em> and <em>Keywords</em> XHTML META tags to your blog's <em>front page</em> and to each one of the <em>posts</em>, <em>static pages</em> and <em>category archives</em>. This operation is automatic, but the generated META tags can be fully customized. Also, the inclusion of other META tags, which do not need any computation, is possible. Please read the tips and all other info provided at the <a href="options-general.php?page=add-meta-tags/add-meta-tags.php">configuration panel</a>.
Version: 1.8.0
Author: George Notaras
Author URI: http://www.g-loaded.eu/
*/

/*
  Copyright 2007 George Notaras <gnot@g-loaded.eu>, CodeTRAX.org

  Licensed under the Apache License, Version 2.0 (the "License");
  you may not use this file except in compliance with the License.
  You may obtain a copy of the License at

      http://www.apache.org/licenses/LICENSE-2.0

  Unless required by applicable law or agreed to in writing, software
  distributed under the License is distributed on an "AS IS" BASIS,
  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
  See the License for the specific language governing permissions and
  limitations under the License.
*/

/*

INTERNAL Configuration Options

1 - Include/Exclude the "keywords" metatag.

    The following option exists ONLY for those who do not want a "keywords"
    metatag META tag to be generated in "Single-Post-View", but still want the
    "description" META tag.
    
    Possible values: TRUE, FALSE
    Default: TRUE
*/
$include_keywords_in_single_posts = TRUE;

/*
Translation Domain

Translation files are searched in: wp-content/plugins
*/
load_plugin_textdomain('add-meta-tags', 'wp-content/plugins');


/**
 * Settings Link in the ``Installed Plugins`` page
 */
function amt_plugin_actions( $links, $file ) {
 	if( $file == 'add-meta-tags/add-meta-tags.php' && function_exists( "admin_url" ) ) {
		$settings_link = '<a href="' . admin_url( 'options-general.php?page=add-meta-tags-options' ) . '">' . __('Settings') . '</a>';
        // Add the settings link before other links
		array_unshift( $links, $settings_link );
	}
	return $links;
}
add_filter( 'plugin_action_links', 'amt_plugin_actions', 10, 2 );


/**
 * Admin Panel - Options Page
 */

function amt_add_pages() {
	add_options_page(__('Meta Tags Options', 'add-meta-tags'), __('Meta Tags', 'add-meta-tags'), 'manage_options', 'add-meta-tags-options', 'amt_options_page');
}

function amt_show_info_msg($msg) {
	echo '<div id="message" class="updated fade"><p>' . $msg . '</p></div>';
}

function amt_options_page() {
    // Permission Check
    if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	if (isset($_POST['info_update'])) {
		/*
		For a little bit more security and easier maintenance, a separate options array is used.
		*/

		//var_dump($_POST);
		$options = array(
			"site_description"	=> $_POST["site_description"],
			"site_keywords"		=> $_POST["site_keywords"],
			"site_wide_meta"	=> $_POST["site_wide_meta"],
			);
		update_option("add_meta_tags_opts", $options);
		amt_show_info_msg(__('Add-Meta-Tags options saved.', 'add-meta-tags'));

	} else {

		$options = get_option("add_meta_tags_opts");

	}

	/*
	Configuration Page
	*/
	
	print('
	<div class="wrap">
		<h2>'.__('Add-Meta-Tags', 'add-meta-tags').'</h2>
		<p>'.__('This is where you can configure the Add-Meta-Tags plugin and read about how the plugin adds META tags in the WordPress pages.', 'add-meta-tags').'</p>
		<p>'.__('Modifying any of the settings in this page is completely <strong>optional</strong>, as the plugin will add META tags automatically.', 'add-meta-tags').'</p>
		<p>'.__("For more information about the plugin's default behaviour and how you could customize the metatag generation can be found in detail in the sections that follow.", "add-meta-tags").'</p>
	</div>

	<div class="wrap">
		<h2>'.__('Configuration', 'add-meta-tags').'</h2>

		<form name="formamt" method="post" action="' . $_SERVER['REQUEST_URI'] . '">

        <table class="form-table">
        <tbody>

            <tr valign="top">
            <th scope="row">'.__('Site Description', 'add-meta-tags').'</th>
            <td>
            <fieldset>
                <legend class="screen-reader-text"><span>'.__('Site Description', 'add-meta-tags').'</span></legend>
                <label for="site_description">
                    <textarea name="site_description" id="site_description" cols="40" rows="3" style="width: 80%; font-size: 14px;" class="code">' . stripslashes($options["site_description"]) . '</textarea>
                    <br />
                    '.__('The following text will be used in the "description" meta tag on the <strong>homepage only</strong>. If this is left <strong>empty</strong>, then the blog\'s description from the <em>General Options</em> (Tagline) will be used.', 'add-meta-tags').'
                </label>
            </fieldset>
            </td>
            </tr>

            <tr valign="top">
            <th scope="row">'.__('Site Keywords', 'add-meta-tags').'</th>
            <td>
			<fieldset>
                <legend class="screen-reader-text"><span>'.__('Site Keywords', 'add-meta-tags').'</span></legend>
                <label for="site_keywords">
                    <textarea name="site_keywords" id="site_keywords" cols="40" rows="3" style="width: 80%; font-size: 14px;" class="code">' . stripslashes($options["site_keywords"]) . '</textarea>
                    <br />
					'.__('The following keywords will be used for the "keywords" meta tag on the <strong>homepage only</strong>. Provide a comma-delimited list of keywords for your blog. If this field is left <strong>empty</strong>, then all of your blog\'s categories will be used as keywords for the "keywords" meta tag.', 'add-meta-tags').'
                    <br />
					<strong>'.__('Example', 'add-meta-tags').'</strong>: <code>'.__('keyword1, keyword2, keyword3', 'add-meta-tags').'</code>
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
                    <textarea name="site_wide_meta" id="site_wide_meta" cols="40" rows="10" style="width: 80%; font-size: 14px;" class="code">' . stripslashes($options["site_wide_meta"]) . '</textarea>
                    <br />
					'.__('Provide the <strong>full XHTML code</strong> of META tags you would like to be included in <strong>all</strong> of your blog pages.', 'add-meta-tags').'
					<br />
					<strong>'.__('Example', 'add-meta-tags').'</strong>: <code>&lt;meta name="robots" content="index,follow" /&gt;</code>
				</label>
			</fieldset>
            </td>
            </tr>

        </tbody>
        </table>

        <p class="submit">
            <input id="submit" class="button-primary" type="submit" value="'.__('Update Options', 'add-meta-tags').'" name="info_update" />
        </p>

		</form>
        
	</div>

	<div class="wrap"> 

        <h2>'.__('Documentation', 'add-meta-tags').'</h2>
		<h3>'.__('Meta Tags on the Front Page', 'add-meta-tags').'</h3>
		<p>'.__('If a site description and/or keywords have been set in the Add-Meta-Tags options above, then those will be used in the "<em>description</em>" and "<em>keywords</em>" META tags respectively.', 'add-meta-tags').'</p>
		<p>'.__('Alternatively, if the above options are not set, then the blog\'s description from the <em>General</em> WordPress options will be used in the "<em>description</em>" META tag, while all of the blog\'s categories, except for the "Uncategorized" category, will be used in the "<em>keywords</em>" META tag.', 'add-meta-tags').'</p>

		<h3>'.__('Meta Tags on Single Posts', 'add-meta-tags').'</h3>
		<p>'.__('Although no configuration is needed in order to put meta tags on single posts, the following information will help you customize them.', 'add-meta-tags').'</p>
		<p>'.__('By default, when a single post is displayed, the post\'s excerpt and the post\'s categories and tags are used in the "description" and the "keywords" meta tags respectively.', 'add-meta-tags').'</p>
		<p>'.__('It is possible to override them by providing a custom description in a custom field named "<strong>description</strong>" and a custom comma-delimited list of keywords by providing it in a custom field named "<strong>keywords</strong>".', 'add-meta-tags').'</p>
		<p>'.__("Furthermore, when overriding the post's keywords, but you need to include the post's categories too, you don't need to type them, but the tag <code>%cats%</code> can be used. In the same manner you can also include your tags in this custom field by adding the word <code>%tags%</code>, which will be replaced by your post's tags.", "add-meta-tags").'</p>
		<p><strong>'.__('Example', 'add-meta-tags').':</strong> <code>'.__('keyword1, keyword2, %cats%, keyword3, %tags%, keyword4', 'add-meta-tags').'</code></p>

		<h3>'.__('Meta Tags on Pages', 'add-meta-tags').'</h3>
		<p>'.__('By default, meta tags are not added automatically when viewing Pages. However, it is possible to define a description and a comma-delimited list of keywords for the Page, by using custom fields named "<strong>description</strong>" and/or "<strong>keywords</strong>" as described for single posts.', 'add-meta-tags').'</p>
		<p>'.__('<strong>WARNING</strong>: Pages do not belong to categories in WordPress. Therefore, the tag <code>%cats%</code> will not be replaced by any categories if it is included in the comma-delimited list of keywords for the Page, so <strong>do not use it for Pages</strong>.', 'add-meta-tags').'</p>

		<h3>'.__('Meta Tags on Category Archives', 'add-meta-tags').'</h3>
		<p>'.__('META tags are automatically added to Category Archives, for example when viewing all posts that belong to a specific category. In this case, if you have set a description for that category, then this description is added to a "description" META tag.', 'add-meta-tags').'</p>
		<p>'.__('Furthermore, a "keywords" META tag - containing only the category\'s name - is always added to Category Archives.', 'add-meta-tags').'</p>

	</div>

	');

}



function amt_strtolower($text) {
    /*
    Helper function that converts $text to lowercase.
    If the mbstring php plugin exists, then the string functions provided by that
    plugin are used.
    */
    if (function_exists('mb_strtolower')) {
        return mb_strtolower($text, get_bloginfo('charset'));
    } else {
        return strtolower($text);
    }
}



function amt_clean_desc($desc) {
	/*
	This is a filter for the description metatag text.
	*/
	$desc = stripslashes($desc);
	$desc = strip_tags($desc);
	$desc = htmlspecialchars($desc);
	//$desc = preg_replace('/(\n+)/', ' ', $desc);
	$desc = preg_replace('/([\n \t\r]+)/', ' ', $desc); 
	$desc = preg_replace('/( +)/', ' ', $desc);
	return trim($desc);
}


function amt_get_the_excerpt($excerpt_max_len = 300, $desc_avg_length = 250, $desc_min_length = 150) {
	/*
	Returns the post's excerpt.
	This was written in order to get the excerpt *outside* the loop
	because the get_the_excerpt() function does not work there any more.
	This function makes the retrieval of the excerpt independent from the
	WordPress function in order not to break compatibility with older WP versions.
	
	Also, this is even better as the algorithm tries to get text of average
	length 250 characters, which is more SEO friendly. The algorithm is not
	perfect, but will do for now.
	*/
	global $posts;

	if ( empty($posts[0]->post_excerpt) ) {

		/*
		Get the initial data for the excerpt
		*/
		$amt_excerpt = strip_tags(substr($posts[0]->post_content, 0, $excerpt_max_len));

		/*
		If this was not enough, try to get some more clean data for the description (nasty hack)
		*/
		if ( strlen($amt_excerpt) < $desc_avg_length ) {
			$amt_excerpt = strip_tags(substr($posts[0]->post_content, 0, (int) ($excerpt_max_len * 1.5)));
			if ( strlen($amt_excerpt) < $desc_avg_length ) {
				$amt_excerpt = strip_tags(substr($posts[0]->post_content, 0, (int) ($excerpt_max_len * 2)));
			}
		}

		$end_of_excerpt = strrpos($amt_excerpt, ".");

		if ($end_of_excerpt) {
			/*
			if there are sentences, end the description at the end of a sentence.
			*/
			$amt_excerpt_test = substr($amt_excerpt, 0, $end_of_excerpt + 1);

			if ( strlen($amt_excerpt_test) < $desc_min_length ) {
				/*
				don't end at the end of the sentence because the description would be too small
				*/
				$amt_excerpt .= "...";
			} else {
				/*
				If after ending at the end of a sentence the description has an acceptable length, use this
				*/
				$amt_excerpt = $amt_excerpt_test;
			}
		} else {
			/*
			otherwise (no end-of-sentence in the excerpt) add this stuff at the end of the description.
			*/
			$amt_excerpt .= "...";
		}

	} else {
		/*
		When the post excerpt has been set explicitly, then it has priority.
		*/
		$amt_excerpt = $posts[0]->post_excerpt;
	}

	return $amt_excerpt;
}


function amt_get_keywords_from_post_cats() {
	/*
	Returns a comma-delimited list of a post's categories.
	*/
	global $posts;

	$postcats = "";
	foreach((get_the_category($posts[0]->ID)) as $cat) {
		$postcats .= $cat->cat_name . ', ';
	}
	$postcats = substr($postcats, 0, -2);

	return $postcats;
}


function amt_get_first_category() {
    // Helper function. Returns the first category the post belongs to.
    $cats = amt_strtolower(amt_get_keywords_from_post_cats());
    $bits = explode(',', $cats);
    if (!empty($bits)) {
        return $bits[0];
    }
    return '';
}


function amt_get_post_tags() {
	/*
	Retrieves the post's user-defined tags.
	
	This will only work in WordPress 2.3 or newer. On older versions it will
	return an empty string.
	*/
	global $posts;
	
	if ( version_compare( get_bloginfo('version'), '2.3', '>=' ) ) {
		$tags = get_the_tags($posts[0]->ID);
		if ( empty( $tags ) ) {
			return false;
		} else {
			$tag_list = "";
			foreach ( $tags as $tag ) {
				$tag_list .= $tag->name . ', ';
			}
			$tag_list = amt_strtolower(rtrim($tag_list, " ,"));
			return $tag_list;
		}
	} else {
		return "";
	}
}


function amt_get_all_categories($no_uncategorized = TRUE) {
	/*
	Returns a comma-delimited list of all the blog's categories.
	The built-in category "Uncategorized" is excluded.
	*/
	global $wpdb;

	if ( version_compare( get_bloginfo('version'), '2.3', '>=' ) ) {
		$cat_field = "name";
		$sql = "SELECT name FROM $wpdb->terms LEFT OUTER JOIN $wpdb->term_taxonomy ON ($wpdb->terms.term_id = $wpdb->term_taxonomy.term_id) WHERE $wpdb->term_taxonomy.taxonomy = 'category' ORDER BY name ASC";
	} else {
		$cat_field = "cat_name";
		$sql = "SELECT cat_name FROM $wpdb->categories ORDER BY cat_name ASC";
	}
	$categories = $wpdb->get_results($sql);
	if ( empty( $categories ) ) {
		return "";
	} else {
		$all_cats = "";
		foreach ( $categories as $cat ) {
			if ($no_uncategorized && $cat->$cat_field != "Uncategorized") {
				$all_cats .= $cat->$cat_field . ', ';
			}
		}
		$all_cats = amt_strtolower(rtrim($all_cats, " ,"));
		return $all_cats;
	}
}


function amt_get_site_wide_metatags($site_wide_meta) {
	/*
	This is a filter for the site-wide meta tags.
	*/
	$site_wide_meta = stripslashes($site_wide_meta);
	$site_wide_meta = trim($site_wide_meta);
	return $site_wide_meta;
}


function amt_get_content_description() {
	/*
	This is a helper function that returns the post's or page's description.
	*/
	global $posts;

    $content_description = '';

	if ( is_single() || is_page() ) {

		/* Custom description field name */
		$desc_fld = "description";

		/*
		Description
		Custom post field "description" overrides post's excerpt in Single Post View.
		*/
		$desc_fld_content = get_post_meta($posts[0]->ID, $desc_fld, true);
		if ( !empty($desc_fld_content) ) {
			/*
			If there is a custom field, use it
			*/
			$content_description = amt_clean_desc($desc_fld_content);
		} elseif ( is_single() ) {
			/*
			Else, use the post's excerpt. Only for Single Post View (not valid for Pages)
			*/
			$content_description = amt_clean_desc(amt_get_the_excerpt());
		}
    }
    return $content_description;
}


function amt_get_content_keywords() {
	/*
	This is a helper function that returns the post's or page's keywords.
	*/
    global $posts, $include_keywords_in_single_posts;

    $keyw_fld = "keywords";

    $content_keywords = '';

    /*
     * Custom post field "keywords" overrides post's categories and tags (tags exist in WordPress 2.3 or newer).
     * %cats% is replaced by the post's categories.
     * %tags% us replaced by the post's tags.
     * NOTE: if $include_keywords_in_single_posts is FALSE, then keywords
     * metatag is not added to single posts.
     */
    if ( ($include_keywords_in_single_posts && is_single()) || is_page() ) {
        $keyw_fld_content = get_post_meta($posts[0]->ID, $keyw_fld, true);
        if ( !empty($keyw_fld_content) ) {
            /*
            If there is a custom field, use it
            */
            if ( is_single() ) {
                /*
                For single posts, the %cat% tag is replaced by the post's categories
                */
                $keyw_fld_content = str_replace("%cats%", amt_get_keywords_from_post_cats(), $keyw_fld_content);
                /*
                Also, the %tags% tag is replaced by the post's tags (WordPress 2.3 or newer)
                */
                if ( version_compare( get_bloginfo('version'), '2.3', '>=' ) ) {
                    $keyw_fld_content = str_replace("%tags%", amt_get_post_tags(), $keyw_fld_content);
                }
            }
            $content_keywords .= amt_strtolower($keyw_fld_content);
        } elseif ( is_single() ) {
            /*
            Add keywords automatically.
            Keywords consist of the post's categories and the post's tags (tags exist in WordPress 2.3 or newer).
            Only for Single Post View (not valid for Pages)
            */
            $content_keywords .= amt_strtolower(amt_get_keywords_from_post_cats());
            $post_tags = amt_strtolower(amt_get_post_tags());
            if (!empty($post_tags)) {
                $content_keywords .= ", " . $post_tags;
            }
        }
    }
    return $content_keywords;
}


function amt_get_content_keywords_mesh() {
    // Keywords returned in the form: keyword1;keyword2;keyword3
    $keywords = explode(', ', amt_get_content_keywords());
    return implode(';', $keywords);
}


function amt_add_meta_tags() {
	/*
	This is the main function that actually writes the meta tags to the
	appropriate page.
	*/
	global $posts, $include_keywords_in_single_posts;
    global $paged;

	/*
	Get the options the DB
	*/
	$options = get_option("add_meta_tags_opts");
	$site_wide_meta = $options["site_wide_meta"];

	$my_metatags = "";

	if ( is_single() || is_page() ) {
		/*
		Add META tags to Single Page View or Page
		*/

		/*
		Auto Description
		*/
        $my_metatags .= "\n<meta name=\"description\" content=\"" . amt_get_content_description() . "\" />";

		/*
		Auto Keywords
		*/
		if ( ($include_keywords_in_single_posts && is_single()) || is_page() ) {
            $my_metatags .= "\n<meta name=\"keywords\" content=\"" . amt_strtolower(amt_get_content_keywords()) . "\" />";
		}


	} elseif ( is_front_page() ) {
		/*
		Add META tags to Home Page
		*/
		
		/*
		Description and Keywords from the options override default behaviour
		*/
		$site_description = $options["site_description"];
		$site_keywords = $options["site_keywords"];

		/*
		Description
		*/
		if ( empty($site_description) ) {
			/*
			If $site_description is empty, then use the blog description from the options
			*/
			$my_metatags .= "\n<meta name=\"description\" content=\"" . amt_clean_desc(get_bloginfo('description')) . "\" />";
		} else {
			/*
			If $site_description has been set, then use it in the description meta-tag
			*/
			$my_metatags .= "\n<meta name=\"description\" content=\"" . amt_clean_desc($site_description) . "\" />";
		}
		/*
		Keywords
		*/
		if ( empty($site_keywords) ) {
			/*
			If $site_keywords is empty, then all the blog's categories are added as keywords
			*/
			$my_metatags .= "\n<meta name=\"keywords\" content=\"" . amt_get_all_categories() . "\" />";
		} else {
			/*
			If $site_keywords has been set, then these keywords are used.
			*/
			$my_metatags .= "\n<meta name=\"keywords\" content=\"" . $site_keywords . "\" />";
		}


	} elseif ( is_category() ) {
		/*
		Writes a description META tag only if a description for the current category has been set.
		*/

		$cur_cat_desc = category_description();
		if ( $cur_cat_desc ) {
            $description_content = amt_clean_desc($cur_cat_desc);
            if ( $paged ) {
                $description_content .= ' (page ' . $paged . ')';
            }
			$my_metatags .= "\n<meta name=\"description\" content=\"" . $description_content . "\" />";
		}
		
		/*
		Write a keyword metatag if there is a category name (always)
		*/
		$cur_cat_name = single_cat_title($prefix = '', $display = false );
		if ( $cur_cat_name ) {
			$my_metatags .= "\n<meta name=\"keywords\" content=\"" . amt_strtolower($cur_cat_name) . "\" />";
		}
	}

	if ($my_metatags) {
		echo "\n<!-- META Tags added by Add-Meta-Tags WordPress plugin. Get it at: http://www.g-loaded.eu/ -->" . $my_metatags . "\n" . amt_get_site_wide_metatags($site_wide_meta) . "\n\n";
	}
}



/*
Template Tags
*/
function amt_content_description() {
    echo amt_get_content_description();
}

function amt_content_keywords() {
    echo amt_get_content_keywords();
}

function amt_content_keywords_mesh() {
    // Keywords echoed in the form: keyword1;keyword2;keyword3
    echo amt_get_content_keywords_mesh();
}


/**
 * Opengraph helper functions
 */

function amt_get_video_url() {
    global $post;

    // Youtube
    $pattern = '#youtube.com/watch\?v=([-|~_0-9A-Za-z]+)#';
    preg_match($pattern, $post->post_content, $matches);
    $youtube_video_id = $matches[1];
    if (!empty($youtube_video_id)) {
        return 'http://youtube.com/v/' . $youtube_video_id;
    }

    // Vimeo
    $pattern = '#vimeo.com/([-|~_0-9A-Za-z]+)#';
    preg_match($pattern, $post->post_content, $matches);
    $vimeo_video_id = $matches[1];
    if (!empty($vimeo_video_id)) {
        return 'http://vimeo.com/couchmode/' . $vimeo_video_id;
    }

    return '';
}


/**
 * Opengraph metadata on posts and pages
 * Opengraph Specification: http://ogp.me
 */

function amt_add_opengraph_metadata() {

    global $post;

    if ( is_front_page() ) {

        $options = get_option("add_meta_tags_opts");
        $site_description = $options["site_description"];

        $metadata_arr = array();
        $metadata_arr[] = '<meta property="og:type" content="website" />';
        $metadata_arr[] = '<meta property="og:locale" content="' . str_replace('-', '_', get_bloginfo('language')) . '" />';
        $metadata_arr[] = '<meta property="og:site_name" content="' . get_bloginfo('name') . '" />';
        if (!empty($site_description)) {
            $metadata_arr[] = '<meta property="og:description" content="' . $site_description . '" />';
        }
        // TODO: add default image?
        // $metadata_arr[] = '<meta property="og:image" content="' . $thumbnail_info[0] . '" />';
        
        echo "\n" . implode("\n", $metadata_arr) . "\n";

    } elseif ( is_single() || is_page()) {

        $metadata_arr = array();
        $metadata_arr[] = '<meta property="og:title" content="' . single_post_title('', FALSE) . '" />';
        $metadata_arr[] = '<meta property="og:url" content="' . get_permalink() . '" />';
        // We use the description defined by Add-Meta-Tags
        $content_desc = amt_get_content_description();
        if ( !empty($content_desc) ) {
            $metadata_arr[] = '<meta property="og:description" content="' . $content_desc . '" />';
        }
        $metadata_arr[] = '<meta property="og:locale" content="' . str_replace('-', '_', get_bloginfo('language')) . '" />';
        $metadata_arr[] = '<meta property="og:site_name" content="' . get_bloginfo('name') . '" />';
        
        // Image
        if (has_post_thumbnail()) {
            $thumbnail_info = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID) );
            $metadata_arr[] = '<meta property="og:image" content="' . $thumbnail_info[0] . '" />';
            //$metadata_arr[] = '<meta property="og:image:secure_url" content="' . str_replace('http:', 'https:', $thumbnail_info[0]) . '" />';
            $metadata_arr[] = '<meta property="og:image:width" content="' . $thumbnail_info[1] . '" />';
            $metadata_arr[] = '<meta property="og:image:height" content="' . $thumbnail_info[2] . '" />';
        }

        // Video
        $video_url = amt_get_video_url();
        if (!empty($video_url)) {
            $metadata_arr[] = '<meta property="og:video" content="' . $video_url . '" />';
        }

        /**
         * We treat all post formats as articles.
         */

        $metadata_arr[] = '<meta property="og:type" content="article" />';
        $metadata_arr[] = '<meta property="article:published_time" content="' . get_the_time('c') . '" />';
        $metadata_arr[] = '<meta property="article:modified_time" content="' . get_the_modified_time('c') . '" />';
        // We use the first category as the section
        $first_cat = amt_get_first_category();
        if (!empty($first_cat)) {
            $metadata_arr[] = '<meta property="article:section" content="' . $first_cat . '" />';
        }
        $metadata_arr[] = '<meta property="article:author" content="' . get_the_author_meta('display_name', $post->post_author) . '" />';
        // Keywords are listed as post tags
        $keywords = explode(', ', amt_get_content_keywords());
        foreach ($keywords as $tag) {
            if (!empty($tag)) {
                $metadata_arr[] = '<meta property="article:tag" content="' . $tag . '" />';
            }
        }

        echo "\n" . implode("\n", $metadata_arr) . "\n";
    }
}


/**
 * Dublin Core metadata on posts and pages
 */

function amt_add_dublin_core_metadata() {
    global $post;

    if ( !is_single() && !is_page()) {
        // Dublin Core metadata has a meaning for content only.
        return;
    }

    $metadata_arr = array();
    $metadata_arr[] = '<meta name="dcterms.identifier" scheme="dcterms.uri" content="' . get_permalink() . '" />';
    $metadata_arr[] = '<meta name="dc.title" content="' . single_post_title('', FALSE) . '" />';
    $metadata_arr[] = '<meta name="dc.creator" content="' . get_the_author_meta('last_name', $post->post_author) . ', ' . get_the_author_meta('first_name', $post->post_author) . '" />';
    $metadata_arr[] = '<meta name="dc.date" scheme="dc.w3cdtf" content="' . get_the_time('c') . '" />';
    // We use the same description as the ``description`` meta tag.
    $content_desc = amt_get_content_description();
    if ( !empty($content_desc) ) {
        $metadata_arr[] = '<meta name="dc.description" content="' . $content_desc . '" />';
    }
    // Keywords are in the form: keyword1;keyword2;keyword3
    $metadata_arr[] = '<meta name="dc.subject" content="' . amt_get_content_keywords_mesh() . '" />';
    $metadata_arr[] = '<meta name="dc.language" scheme="dcterms.rfc4646" content="' . get_bloginfo('language') . '" />';
    $metadata_arr[] = '<meta name="dc.publisher" scheme="dcterms.uri" content="' . get_bloginfo('url') . '" />';
    // TODO: Coipyright page from setting in the admin panel
    // <meta name="dcterms.rights" scheme="dcterms.uri" content=" bloginfo('url') /about/disclaimer-and-license/" />

    // The following requires creative commons configurator
    if (function_exists('bccl_get_license_url')) {
        $metadata_arr[] = '<meta name="dcterms.license" scheme="dcterms.uri" content="' . bccl_get_license_url() . '" />';
    }

    $metadata_arr[] = '<meta name="dc.coverage" content="World" />';

    /**
     * WordPress Post Formats: http://codex.wordpress.org/Post_Formats
     * Dublin Core Format: http://dublincore.org/documents/dcmi-terms/#terms-format
     * Dublin Core DCMIType: http://dublincore.org/documents/dcmi-type-vocabulary/
     */

    /**
     * TREAT ALL POST FORMATS AS TEXT (for now)
     */
    $metadata_arr[] = '<meta name="dc.type" scheme="DCMIType" content="Text" />';
    $metadata_arr[] = '<meta name="dc.format" scheme="dcterms.imt" content="text/html" />';

    /**
    $format = get_post_format( $post->id );
    if ( empty($format) || $format=="aside" || $format=="link" || $format=="quote" || $format=="status" || $format=="chat") {
        // Default format
        $metadata_arr[] = '<meta name="dc.type" scheme="DCMIType" content="Text" />';
        $metadata_arr[] = '<meta name="dc.format" scheme="dcterms.imt" content="text/html" />';
    } elseif ($format=="gallery") {
        $metadata_arr[] = '<meta name="dc.type" scheme="DCMIType" content="Collection" />';
        // $metadata_arr[] = '<meta name="dc.format" scheme="dcterms.imt" content="image" />';
    } elseif ($format=="image") {
        $metadata_arr[] = '<meta name="dc.type" scheme="DCMIType" content="Image" />';
        // $metadata_arr[] = '<meta name="dc.format" scheme="dcterms.imt" content="image/png" />';
    } elseif ($format=="video") {
        $metadata_arr[] = '<meta name="dc.type" scheme="DCMIType" content="Moving Image" />';
        $metadata_arr[] = '<meta name="dc.format" scheme="dcterms.imt" content="application/x-shockwave-flash" />';
    } elseif ($format=="audio") {
        $metadata_arr[] = '<meta name="dc.type" scheme="DCMIType" content="Sound" />';
        $metadata_arr[] = '<meta name="dc.format" scheme="dcterms.imt" content="audio/mpeg" />';
    }
    */

    echo "\n" . implode("\n", $metadata_arr) . "\n";
}


/*
Actions
*/

add_action('admin_menu', 'amt_add_pages');

add_action('wp_head', 'amt_add_meta_tags', 0);
add_action('wp_head', 'amt_add_opengraph_metadata', 0);
add_action('wp_head', 'amt_add_dublin_core_metadata', 0);

?>