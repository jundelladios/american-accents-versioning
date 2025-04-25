<?php
/**
 * Plugin Name: American Accents Site Versioning
 * Plugin URI: mailto:jundell@ad-ios.com
 * Description: Website Versioning for Site Updates
 * Version: 1.0
 * Author: Jun Dell
 * Author URI: mailto:jundell@ad-ios.com
 */

function american_accent_versioning_dbprefixes_generated() {
    return array(
        'table' => 'site_versions',
        'wp' => 'wpdy_',
        'inv' => 'invt_',
        'limit' => defined('_APP_SITE_VERSIONING_LIMIT') ? _APP_SITE_VERSIONING_LIMIT : 1000
    );
}

function american_accent_versioning_admin_url($version=null) {

    $adminslug = defined('_APP_WP_ADMIN_SLUG') ? _APP_WP_ADMIN_SLUG : 'wp-admin';

    $url = home_url();

    if($version) {

        $url.="/version/$version";
    }

    return "$url/$adminslug";
}

function american_accent_versioning_is_version_abspath() {

    return (strpos(ABSPATH, 'version') !== false);
}


/**
 * Database instances for sites versioning
 */
function american_accent_versioning_website_versioning_database_init() {
    global $aasiteversioning;
    if(american_accent_versioning_website_versioning_required_constants()) {
        $aasiteversioning = new wpdb(_APP_DB_SITE_VER_USER, _APP_DB_SITE_VER_PASSWORD, _APP_DB_SITE_VER_NAME, _APP_DB_SITE_VER_HOST);
    }
    
}
add_action('init', 'american_accent_versioning_website_versioning_database_init');


// Check if config found.
function american_accent_versioning_website_versioning_required_constants() {
    return defined('_APP_DB_SITE_VER_USER') && 
    defined('_APP_DB_SITE_VER_PASSWORD') && 
    defined('_APP_DB_SITE_VER_NAME') && 
    defined('_APP_DB_SITE_VER_HOST') &&
    defined('_APP_EXEC_MYSQL_BIN') &&
    defined('_APP_EXEC_WPCLI');
}

// send notice to admin if constants not found
function american_accent_versioning_website_versioning_required_constants_notice() {
    if( !american_accent_versioning_website_versioning_required_constants() && !american_accent_versioning_is_version_abspath() ):
    ?>
    <div class="notice notice-error ml-0 mr-2">
        <p><?php _e( 'American Accents Site Versioning requires these Constant Variables (_APP_DB_SITE_VER_USER, _APP_DB_SITE_VER_PASSWORD, _APP_DB_SITE_VER_NAME, _APP_DB_SITE_VER_HOST, _APP_EXEC_MYSQL_BIN, _APP_EXEC_WPCLI).', 'american-accents' ); ?></p>
    </div>
    <?php
    endif;

    if(!file_exists(ABSPATH.'version/wordpress') && !american_accent_versioning_is_version_abspath()) {
        ?>
        <div class="notice notice-info is-dismissible ml-0 mr-2">
            <p><?php _e( 'Basic WP Installation for version is missing, please create a new one without wp-content folder or <a href="mailto:jundell@ad-ios.com">contact developer</a>', 'american-accents' ); ?></p>
        </div>
        <?php
    }
};
add_action( 'admin_notices', 'american_accent_versioning_website_versioning_required_constants_notice' );


/**
 * American Accents Site Versioning Admin Menu
 */
function american_accent_versioning_website_versioning_admin_menu() {

    // allow this page only for main site.
    if(!american_accent_versioning_is_version_abspath()) {

        $suffix = "aa-site-version";

        $parent =  $suffix.'general';
        
        $type = "manage_options";

        add_menu_page( 'Site Versioning', 'Site Versioning', $type, $parent, 'american_accent_versioning_website_versioning_admin_page', 'dashicons-admin-site');
    }

}
add_action( 'admin_menu', 'american_accent_versioning_website_versioning_admin_menu' );


function american_accent_versioning_website_versioning_admin_page() {

    require_once( plugin_dir_path(__FILE__) . 'site-versions.php' );

}


add_action( 'wp_footer', 'american_accents_siteversioning_footer_banner' );
function american_accents_siteversioning_footer_banner() {
    if(american_accent_versioning_is_version_abspath() && american_accents_siteversioning_get_site_version())
    {
        require_once( plugin_dir_path(__FILE__) . 'site-versions-footer.php' );
    }
}


function american_accents_siteversioning_get_site_version() {

    if(american_accent_versioning_is_version_abspath()) {
        $homeurl = home_url();
        $protocols = array( 'http://', 'https://' );
        $extract = str_replace($protocols,"",$homeurl);
        $v = explode('/', $extract);
        return isset($v[2]) ? $v[2] : null;
    }

    return null;
}


function american_accents_siteversioning_hasher($salt) {
    if(function_exists('wp_salt')) {
        return wp_salt($salt);
    }
    return hash('sha256', $salt);
}

function american_accents_siteversioning_notice( $message, $type="success" ) {
    echo "<div class='notice notice-$type ml-0 mr-2'><p>$message</p></div>";
}


function american_accents_siteversioning_reqfiles() {
    $files = array(
        'ajax.php',
        'api.php',
        'assets.php',
        'routes.php'
    );

    foreach($files as $file) {
        
        require_once( plugin_dir_path(__FILE__) . $file );
    }
}

american_accents_siteversioning_reqfiles();