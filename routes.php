<?php


function american_accents_siteversioning_rest_api_init() {
    register_rest_route( 'site-version', 'live', array(
        array(
            'methods' => 'POST',
            'callback' => function( \WP_REST_Request $request ) {
                american_accents_siteversioning_production_call( $request );
            },
            'permission_callback' => function( $request ) {
                return is_user_logged_in();
            }
        )
    ));

    register_rest_route( 'site-version', 'get', array(
        array(
            'methods' => 'GET',
            'callback' => function( \WP_REST_Request $request ) {
                $wheres = isset($request['wheres']) ? $request['wheres'] : [];
                return american_accent_versioning_data_lists( $wheres );
            },
            'permission_callback' => function( $request ) {
                return is_user_logged_in();
            }
        )
    ));

    register_rest_route( 'site-version', 'remove', array(
        array(
            'methods' => 'DELETE',
            'callback' => function( \WP_REST_Request $request ) {
                american_accents_siteversioning_remove_call($request);
            },
            'permission_callback' => function( $request ) {
                return is_user_logged_in();
            }
        )
    ));

    register_rest_route( 'site-version', 'update', array(
        array(
            'methods' => 'PUT',
            'callback' => function( \WP_REST_Request $request ) {
                american_accents_siteversioning_update_call( $request );
            },
            'permission_callback' => function( $request ) {
                return is_user_logged_in();
            }
        )
    ));

    register_rest_route( 'site-version', 'new', array(
        array(
            'methods' => 'POST',
            'callback' => function( \WP_REST_Request $request ) {
                american_accents_siteversioning_add_call( $request );
            },
            'permission_callback' => function( $request ) {
                return is_user_logged_in();
            }
        )
    ));
}
add_action( 'rest_api_init', 'american_accents_siteversioning_rest_api_init' );