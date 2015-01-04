<?php
/**
 * This file is part of the Add-Meta-Tags WordPress plugin.
 *
 * Template for audio embeds, used by the Add-Meta-Tags Twitter Card generator.
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

    </head>
    <body marginwidth="0" marginheight="0">

        <?php
        $attrs = array(
            'src'      => amt_embed_make_https( amt_embed_get_stream_url( amt_embed_get_id() ) ),
            'loop'     => '',
            'autoplay' => '',
            'preload'  => 'none'
            );
        echo do_shortcode( wp_audio_shortcode( $attrs ) );
        ?>

    </body>
</html>
