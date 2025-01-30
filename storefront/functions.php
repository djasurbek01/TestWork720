<?php
/**
 * Storefront engine room
 *
 * @package storefront
 */

/**
 * Assign the Storefront version to a var
 */
$theme              = wp_get_theme( 'storefront' );
$storefront_version = $theme['Version'];

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 980; /* pixels */
}

$storefront = (object) array(
	'version'    => $storefront_version,

	/**
	 * Initialize all the things.
	 */
	'main'       => require 'inc/class-storefront.php',
	'customizer' => require 'inc/customizer/class-storefront-customizer.php',
);

require 'inc/storefront-functions.php';
require 'inc/storefront-template-hooks.php';
require 'inc/storefront-template-functions.php';
require 'inc/wordpress-shims.php';

if ( class_exists( 'Jetpack' ) ) {
	$storefront->jetpack = require 'inc/jetpack/class-storefront-jetpack.php';
}

if ( storefront_is_woocommerce_activated() ) {
	$storefront->woocommerce            = require 'inc/woocommerce/class-storefront-woocommerce.php';
	$storefront->woocommerce_customizer = require 'inc/woocommerce/class-storefront-woocommerce-customizer.php';

	require 'inc/woocommerce/class-storefront-woocommerce-adjacent-products.php';

	require 'inc/woocommerce/storefront-woocommerce-template-hooks.php';
	require 'inc/woocommerce/storefront-woocommerce-template-functions.php';
	require 'inc/woocommerce/storefront-woocommerce-functions.php';
}

if ( is_admin() ) {
	$storefront->admin = require 'inc/admin/class-storefront-admin.php';

	require 'inc/admin/class-storefront-plugin-install.php';
}

/**
 * NUX
 * Only load if wp version is 4.7.3 or above because of this issue;
 * https://core.trac.wordpress.org/ticket/39610?cversion=1&cnum_hist=2
 */
if ( version_compare( get_bloginfo( 'version' ), '4.7.3', '>=' ) && ( is_admin() || is_customize_preview() ) ) {
	require 'inc/nux/class-storefront-nux-admin.php';
	require 'inc/nux/class-storefront-nux-guided-tour.php';
	require 'inc/nux/class-storefront-nux-starter-content.php';
}

/**
 * Note: Do not add any custom code here. Please use a custom plugin so that your customizations aren't lost during updates.
 * https://github.com/woocommerce/theme-customisations
 */

// Custom Post Type "Cities"
function register_cities_post_type() {
    $labels = array(
        'name'                  => 'Cities',
        'singular_name'         => 'City',
        'menu_name'             => 'Cities',
        'name_admin_bar'        => 'City',
        'add_new'               => 'Add New',
        'add_new_item'          => 'Add New City',
        'new_item'              => 'New City',
        'edit_item'             => 'Edit City',
        'view_item'             => 'View City',
        'all_items'             => 'All Cities',
        'search_items'          => 'Search Cities',
        'not_found'             => 'No cities found',
        'not_found_in_trash'    => 'No cities found in Trash',
        'featured_image'        => 'City Image',
        'set_featured_image'    => 'Set city image',
        'remove_featured_image' => 'Remove city image',
        'use_featured_image'    => 'Use as city image',
        'archives'              => 'City archives',
        'insert_into_item'      => 'Insert into city',
        'uploaded_to_this_item' => 'Uploaded to this city',
        'filter_items_list'     => 'Filter cities list',
        'items_list_navigation' => 'Cities list navigation',
        'items_list'            => 'Cities list',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'menu_icon'          => 'dashicons-location',
        'supports'           => array( 'title', 'editor', 'thumbnail' ),
        'rewrite'            => array( 'slug' => 'cities' ),
        'show_in_rest'       => true,
        'register_meta_box_cb' => 'add_cities_metaboxes',
    );

    register_post_type( 'cities', $args );
}
add_action( 'init', 'register_cities_post_type' );

//  метабоксов
function add_cities_metaboxes() {
    add_meta_box(
        'cities_location',
        'City Location',
        'cities_location_metabox',
        'cities',
        'normal',
        'default'
    );
}

function cities_location_metabox( $post ) {
    $latitude = get_post_meta( $post->ID, 'latitude', true );
    $longitude = get_post_meta( $post->ID, 'longitude', true );

    wp_nonce_field( 'save_cities_location', 'cities_location_nonce' );

    echo '<label for="latitude">Latitude</label>';
    echo '<input type="text" id="latitude" name="latitude" value="' . esc_attr( $latitude ) . '" class="widefat" />';

    echo '<label for="longitude">Longitude</label>';
    echo '<input type="text" id="longitude" name="longitude" value="' . esc_attr( $longitude ) . '" class="widefat" />';
}


function save_cities_location( $post_id ) {
    if ( !isset( $_POST['cities_location_nonce'] ) || !wp_verify_nonce( $_POST['cities_location_nonce'], 'save_cities_location' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

    if ( isset( $_POST['latitude'] ) ) {
        update_post_meta( $post_id, 'latitude', sanitize_text_field( $_POST['latitude'] ) );
    }

    if ( isset( $_POST['longitude'] ) ) {
        update_post_meta( $post_id, 'longitude', sanitize_text_field( $_POST['longitude'] ) );
    }
}
add_action( 'save_post', 'save_cities_location' );

// "Countries"
function create_countries_taxonomy() {
    $labels = array(
        'name'                       => 'Countries',
        'singular_name'              => 'Country',
        'search_items'               => 'Search Countries',
        'all_items'                  => 'All Countries',
        'parent_item'                => 'Parent Country',
        'parent_item_colon'          => 'Parent Country:',
        'edit_item'                  => 'Edit Country',
        'update_item'                => 'Update Country',
        'add_new_item'               => 'Add New Country',
        'new_item_name'              => 'New Country Name',
        'menu_name'                  => 'Countries',
    );

    $args = array(
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'query_var'                  => true,
        'rewrite'                    => array( 'slug' => 'country' ),
        'show_in_rest'               => true,
    );


    register_taxonomy( 'countries', 'cities', $args );
}
add_action( 'init', 'create_countries_taxonomy' );


// AJAX
function handle_city_search() {
    if ( !isset( $_POST['search_term'] ) || empty( $_POST['search_term'] ) ) {
        wp_send_json_error( 'No search term provided.' );
    }

    $search_term = sanitize_text_field( $_POST['search_term'] );

    $cities = get_posts( array(
        'post_type' => 'cities',
        's'         => $search_term,
        'posts_per_page' => -1,
    ) );

    if ( empty( $cities ) ) {
        wp_send_json_error( 'No cities found.' );
    }

    $city_data = array();

    foreach ( $cities as $city ) {
        $latitude = get_post_meta( $city->ID, 'latitude', true );
        $longitude = get_post_meta( $city->ID, 'longitude', true );
        $weather = get_weather( $latitude, $longitude );

        $city_data[] = array(
            'country'   => get_the_terms( $city->ID, 'countries' )[0]->name,
            'city'      => $city->post_title,
            'temperature' => $weather ? $weather['temperature'] . '°C' : 'N/A',
        );
    }

    wp_send_json_success( $city_data );
}
add_action( 'wp_ajax_city_search', 'handle_city_search' );
add_action( 'wp_ajax_nopriv_city_search', 'handle_city_search' );

// API Open Meteo
function get_weather( $latitude, $longitude ) {
    $url = 'https://api.open-meteo.com/v1/forecast?latitude=' . $latitude . '&longitude=' . $longitude . '&current_weather=true';
    $response = wp_remote_get( $url );
    if ( is_wp_error( $response ) ) {
        return false;
    }

    $data = json_decode( wp_remote_retrieve_body( $response ), true );
    if ( isset( $data['current_weather'] ) ) {
        return $data['current_weather'];
    }

    return false;
}

function enqueue_city_search_scripts() {
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'city-search', get_stylesheet_directory_uri() . '/assets/js/city.js', array('jquery'), null, true );
    wp_localize_script( 'city-search', 'ajaxurl', admin_url( 'admin-ajax.php', 'admin' ) );
}
add_action( 'wp_enqueue_scripts', 'enqueue_city_search_scripts' );
