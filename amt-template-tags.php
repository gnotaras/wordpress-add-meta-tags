<?php
/**
 * Module containing template tags.
 */


function amt_content_description() {
    $post = amt_get_current_post_object();
    echo amt_get_content_description($post);
}

function amt_content_keywords() {
    $post = amt_get_current_post_object();
    echo amt_get_content_keywords($post);
}

function amt_content_keywords_mesh() {
    $post = amt_get_current_post_object();
    // Keywords echoed in the form: keyword1;keyword2;keyword3
    echo amt_get_content_keywords_mesh($post);
}

function amt_metadata() {
    // Prints full metadata.
    amt_add_metadata();
}

function amt_metadata_review() {
    // Prints full metadata in review mode. No user level checks here.
    echo amt_get_metadata_inspect();
}

