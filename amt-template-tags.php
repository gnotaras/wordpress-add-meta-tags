<?php
/**
 * Module containing template tags.
 */


function amt_content_description() {
    $post = get_queried_object();
    echo amt_get_content_description($post);
}

function amt_content_keywords() {
    $post = get_queried_object();
    echo amt_get_content_keywords($post);
}

function amt_metadata_head() {
    // Prints full metadata for head area.
    amt_add_metadata_head();
}

function amt_metadata_footer() {
    // Prints full metadata for footer area.
    amt_add_metadata_footer();
}

function amt_metadata_review() {
    // Prints full metadata in review mode. No user level checks here.
    echo amt_get_metadata_inspect();
}

