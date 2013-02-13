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

