<?php
/**
 * This file is part of the Add-Meta-Tags WordPress plugin.
 *
 * Template for video embeds, used by the Add-Meta-Tags Twitter Card generator.
 *
 */
?><!doctype html>
<html>
    <head>
        <meta name="viewport" content="width=device-width; height=device-height;">

        <title><?php bloginfo('name') ?></title>

        <script src="<?php echo amt_embed_make_https( amt_embed_get_includes_url( 'js/jquery/jquery.js' ) ); ?>"></script>

        <script type='text/javascript'>
            /* <![CDATA[ */
            var mejsL10n = {"language":"en-US","strings":{"Close":"Close","Fullscreen":"Fullscreen","Download File":"Download File","Download Video":"Download Video","Play\/Pause":"Play\/Pause","Mute Toggle":"Mute Toggle","None":"None","Turn off Fullscreen":"Turn off Fullscreen","Go Fullscreen":"Go Fullscreen","Unmute":"Unmute","Mute":"Mute","Captions\/Subtitles":"Captions\/Subtitles"}};
            var _wpmejsSettings = {"pluginPath":"\/wp-includes\/js\/mediaelement\/"};
            /* ]]> */
        </script>
        <script src="<?php echo amt_embed_make_https( amt_embed_get_includes_url( 'js/mediaelement/mediaelement-and-player.min.js' ) ); ?>"></script>
        <script src="<?php echo amt_embed_make_https( amt_embed_get_includes_url( 'js/mediaelement/wp-mediaelement.js' ) ); ?>"></script>

        <link rel="stylesheet" href="<?php echo amt_embed_make_https( amt_embed_get_includes_url( 'js/mediaelement/mediaelementplayer.min.css' ) ); ?>" />
        <link rel="stylesheet" href="<?php echo amt_embed_make_https( amt_embed_get_includes_url( 'js/mediaelement/wp-mediaelement.css' ) ); ?>" />

        <style>
            video { width: 100%; height: 100%; max-width: 100%; }
            .wp-video, .wp-video-shortcode, .mejs-overlay, .mejs-poster { width: 100% !important; }
        </style>

    </head>
    <body marginwidth="0" marginheight="0">

        <?php
        $attrs = array(
            'src'      => amt_embed_make_https( amt_embed_get_stream_url( amt_embed_get_id() ) ),
            'poster'   => amt_embed_make_https( amt_embed_get_preview_image( amt_embed_get_id() ) ),
            'loop'     => '',
            'autoplay' => '',
            'preload'  => 'none',
            //'width'    => '',
            //'height'   => ''
            );
        echo do_shortcode( wp_video_shortcode( $attrs ) );
        ?>

    </body>
</html>
