<?php

function american_accent_versioning_assets() {

    $version = get_plugin_data(__FILE__, array())['Version'];

    // enqueue only on these functionality.
    if( !isset($_GET['page']) || !preg_match("/^aa-site-version/", $_GET['page'])) {
        return;
    }
    
    // slick css
    wp_enqueue_style( 'american-accents-site-version-flatpickr-css', plugin_dir_url(__FILE__) . '/assets/flatpickr.css', array(), $version, null );

    // papa parse csv
    wp_enqueue_script( 'american-accents-site-version-flatpickr-js', plugin_dir_url(__FILE__) . 'assets/flatpickr.js', array(), $version, false );

    // style css
    wp_enqueue_style( 'american-accents-site-version-style-css', plugin_dir_url(__FILE__) . '/assets/style.css', array(), $version, null );


}
add_action( 'admin_enqueue_scripts', 'american_accent_versioning_assets' );