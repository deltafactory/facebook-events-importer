<?php
/*
 * Plugin Name: Facebook Events Importer
 * Plugin URI: http://wpfbevents.com/
 * Description: A simple way to import Facebook events.
 * Version: 2.3.7
 * Author: <a href="http://volk.io/">Volk</a>
 * Author URI: http://volk.io/
  * License: GPL2
 /*  Copyright 2015  Volk  (email : media@volk.io)

	This program is free software; You can modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class WPFBEvents_Loader {
	static function setup() {

		spl_autoload_register( array( __CLASS__, 'autoload' ) );

		self::includes();

		add_action( 'init', 'import_fbe_scripts' );
		add_action( 'wp_head' , array( 'WPFBEvents_Customize' , 'wpfbe_header_output' ) );


		if ( is_admin() ) {
			self::admin_setup();
		}

		// Ajax hooks
		add_action( 'wp_ajax_facebook_events_request', 'fbe_callback' );
		add_action( 'wp_ajax_nopriv_facebook_events_request', 'fbe_callback' );

		add_action( 'wp_ajax_facebook_events_update', 'fbe_update_callback' );
		add_action( 'wp_ajax_nopriv_facebook_events_update', 'fbe_update_callback' );

		add_action( 'wp_ajax_facebook_events_remove', 'fbe_remove_callback' );
		add_action( 'wp_ajax_nopriv_facebook_events_remove', 'fbe_remove_callback' );

		add_action( 'wp_ajax_facebook_app_data', 'fbe_app_data_callback' );
		add_action( 'wp_ajax_nopriv_facebook_app_data', 'fbe_app_data_callback' );

		// Shortcode, Widgets, and Sidebars .. oh my!
		add_shortcode( 'wpfbevents', array( 'WPFBEvents_Shortcode', 'shortcode' ) );
		add_action( 'widgets_init', array( __CLASS__, 'register_widget' ) );

	}

	static function autoload( $class ) {
		$path = __DIR__ . '/include';
		$prefix = 'WPFBEvents_';

		if ( strpos( $prefix, $class ) === 0 ) {
			$name = strtolower( substr( $class, strlen( $prefix ) ) );
			$file = $path . '/' . $name . '.php';

			if ( file_exists( $file ) ) {
				include( $file );
			}
		}
	}

	static function admin_setup() {
		self::check_php_version();
		self::admin_includes();

		add_action("admin_menu", "setup_fbe_import_admin_menu");

	}

	static function check_php_version() {
		// Check PHP Version and deactivate & die if it doesn't meet minimum requirements.
		if ( strnatcmp(phpversion(),'5.4.0') < 0 ){
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( 'This plugin requires PHP Version 5.4. Your current version is '. phpversion() );
		}

	}

	static function includes() {
		require( __DIR__ . '/include/pro.php' );
		require( __DIR__ . '/include/customize.php' );
		require( __DIR__ . '/include/shortcode.php' );

		require_once(ABSPATH . 'wp-admin/includes/media.php');
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/image.php');

		require( __DIR__ . 'assets/includes/posttype.php');
		require( __DIR__ . 'assets/includes/cron.php');
		require( __DIR__ . 'assets/includes/fb_import_action.php');
	}

	static function admin_includes() {
		require( __DIR__ . '/include/admin.php' );
	}

	static function register_widget() {
	    register_widget( 'WPFBEvents_Widget' );
	}
}


function wpfb_feed_request($qv) {
	if (isset($qv['feed']))
		$qv['post_type'] = 'facebook-events';
	return $qv;
}
add_filter('request', 'wpfb_feed_request');



function custom_fbe_post_nav($current_event_date) {
	$currentdate = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-1,date("Y")));
	$args = array (
		'meta_query'=> array(
			array(
				'key' => 'event_starts_sort_field',
				'compare' => '>',
				'value' => $current_event_date,
				'type' => 'DATE',
			),
			array(
				'key' => 'event_starts_sort_field',
				'compare' => '>',
				'value' => $currentdate,
				'type' => 'DATE',
			),
		),
		'post_type' => 'facebook_events',
		'meta_key' => 'event_starts_sort_field',
		'orderby' => 'meta_value',
		'order' => 'ASC',
		'posts_per_page' => -1
	);

	$pages = array();
	$fbe_nav_query = get_posts($args);

	foreach ($fbe_nav_query as $fbe_nav_post) {
		$pages[] += $fbe_nav_post->ID;
	}

	$id = get_the_id();
	$current = array_search($id, $pages);
	$prevID = $pages[$current-1];
	$nextID = $pages[$current+1];

	$total = count($pages);

	foreach ($pages as $mykey => $myval) {
		if ($myval== $id) {
			$key = ($mykey + 1);
		}
	}

	if (!empty($prevID)) {
		echo '<a class="prev_fb_event" rel="prev" href="'.get_permalink($prevID).'" title="'.get_the_title($prevID).'"><span class="arrow-left"></span>Previous Event </a>';
	} else {
		echo '<a class="prev_fb_event" rel="prev" href="'.get_permalink(end($pages)).'" title="'.get_the_title(end($pages)).'"><span class="arrow-left"></span>Previous Event </a>';
	}

	if (!empty($nextID)) {
		echo '<a class="next_fb_event" rel="next" href="'.get_permalink($nextID).'" title="'.get_the_title($nextID).'">Next Event <span class="arrow-right"></span></a>';
	} else {
		echo '<a class="next_fb_event" rel="next" href="'.get_permalink(array_shift($pages)).'" title="'.get_the_title(array_shift($pages)).'">Next Event <span class="arrow-right"></span></a>';
	}
}

function getaddress($lat,$lng){
	$url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($lat).','.trim($lng).'&sensor=false';
	$json = @file_get_contents($url);
	$data = json_decode($json);
	$status = $data->status;
	return ($status=="OK") ? $data->results[0]->formatted_address : false;
}
