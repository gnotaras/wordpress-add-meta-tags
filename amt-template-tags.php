<?php
/**
 * Module containing template tags.
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

function amt_metadata() {
    // Prints full metadata.
    echo implode("\n", amt_get_metadata());
}

function amt_metadata_review() {
    // Prints full metadata in review mode. No user level checks here.
    echo amt_get_metadata_inspect();
}

