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

function amt_content_keywords_mesh() {
    $post = get_queried_object();
    // Keywords echoed in the form: keyword1;keyword2;keyword3
    echo amt_get_content_keywords_mesh($post);
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

