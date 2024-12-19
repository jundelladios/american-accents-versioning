<?php

add_action( 'wp_ajax_american_accents_siteversioning_migration_query_generator', 'american_accents_siteversioning_migration_query_generator' );
function american_accents_siteversioning_migration_query_generator() {
    american_accents_siteversioning_migration_query_generator_call($_POST);
    wp_die();
}


add_action( 'wp_ajax_american_accents_siteversioning_add', 'american_accents_siteversioning_add' );
function american_accents_siteversioning_add()
{
    american_accents_siteversioning_add_call($_POST);
    wp_die();
}



add_action( 'wp_ajax_american_accents_siteversioning_remove', 'american_accents_siteversioning_remove' );
function american_accents_siteversioning_remove() {
    american_accents_siteversioning_remove_call($_POST);
    wp_die();
}



add_action( 'wp_ajax_american_accents_siteversioning_production', 'american_accents_siteversioning_production' );
function american_accents_siteversioning_production() {
    american_accents_siteversioning_production_call($_POST);
    wp_die();
}



add_action( 'wp_ajax_american_accents_siteversioning_update', 'american_accents_siteversioning_update' );
function american_accents_siteversioning_update() {
    american_accents_siteversioning_update_call($_POST);
    wp_die();
}