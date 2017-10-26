<?php

WPFBEvents_PostType::setup();

class WPFBEvents_PostType {
	static $type = 'facebook_events';

	static function setup() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'save_post_' . self::$type, array( __CLASS__, 'save_post' ) );
	}

	static function register() {
		$slug = get_option("slug") ?: 'facebook-events';

		register_post_type( self::$type, array(
			'labels' => array(
				'name' => __( 'Facebook Events' ),
				'singular_name' => __( 'Facebook Event' )
			),
			'public' => true,
			'menu_position' => 5,
			'menu_icon' =>  'dashicons-calendar',
			'rewrite' => array('slug' => $slug),
			'supports' => array('title','editor','thumbnail','comments','tags'),
			'register_meta_box_cb' => array( __CLASS__, 'metaboxes' )
		) );

	}

	static function metaboxes( $post ) {
		$fields_title = __( 'Facebook Event Fields', 'facebook_events_textdomain' );
		add_meta_box( 'facebook_events_sectionid', $fields_title, array( __CLASS__, 'metabox_fields' ), $post->post_type, 'normal', 'high' );

		$feature_title = __( 'Feature Facebook Event', 'facebook_events_textdomain' );
		add_meta_box( 'facebook_feature_eventid', $feature_title, array( __CLASS__, 'metabox_feature' ), $post->post_type, 'side', 'low' );
	}

	static function metabox_fields( $post ) {

		$facebook_event_id = get_post_meta( $post->ID, 'facebook_event_id', true );
		$location = get_post_meta( $post->ID, 'location', true );
		$ticket_uri = get_post_meta( $post->ID, 'ticket_uri', true );
		$image_url = get_post_meta( $post->ID, 'image_url', true );
		$fb_event_uri = get_post_meta( $post->ID, 'fb_event_uri', true );
		$timezone = get_post_meta( $post->ID, 'event_timezone', true );

		$venue_desc = get_post_meta( $post->ID, 'venue_desc', true );
		$event_starts_sort_field = get_post_meta( $post->ID, 'event_starts_sort_field', true );

		$start_time = get_post_meta( $post->ID, 'start_time', true );
		$end_time = get_post_meta( $post->ID, 'end_time', true );

		$start_date = get_post_meta( $post->ID, 'event_starts', true );
		$end_date = get_post_meta( $post->ID, 'event_ends', true );

		date_default_timezone_set($timezone);
		if($start_date != ''){
			$start_date  = date('m/d/Y', strtotime($start_date) );
		}else{ $start_date = '';}

		if($start_time != ''){
			$start_time = date('g:i a', strtotime($start_time));
		}else{$start_time ='';}

		if($end_date != ''){
			$end_date = date('m/d/Y', strtotime($end_date));
		}else{ $end_date = '';}

		if($end_time != ''){
			$end_time = date('g:i a', strtotime($end_time));
		}else{$end_time = '';}

		echo '<div id="facebook_event_fields">';
		echo "<h4 style='margin:10px 0px; padding:10px 0px; color:#0074A2;'>Event Information</h4>";
		echo '<label for="location">Location</label>';
		echo '<input type="text" id="location" name="location" value="' . sanitize_text_field( $location ) . '" size="25" />';
		echo '<br />';
		echo '<label for="event_starts">Event Starts</label>';
		echo '<input type="text" id="event_starts" name="event_starts" value="'. sanitize_text_field( $start_date ) .'" size="10">@<input type="text" id="start_time" name="start_time" value="'. sanitize_text_field( $start_time ) .'"" size="8" />';

		$event_starts_sort_field = strtotime($start_date);
		$event_starts_sort_field = date("Y-m-d",$event_starts_sort_field);

		echo '<input type="hidden" id="event_starts_sort_field" name="event_starts_sort_field" value="'.sanitize_text_field( $event_starts_sort_field ).'" size="10">';
		echo '<br />';
		echo '<label for="event_ends">Event Ends</label>';
		echo '<input type="text" id="event_ends" name="event_ends" value="'. sanitize_text_field( $end_date ) .'" size="10">@<input type="text" id="end_time" name="end_time" value="'. sanitize_text_field( $end_time ) .'"" size="8" />';
		echo '<br />';
		echo '<label for="ticket_uri">Ticket URL</label>';
		echo '<input type="text" id="ticket_uri" name="ticket_uri" value="' . sanitize_text_field( $ticket_uri ) . '" size="45" />';
		echo '<br />';
		echo '<label for="fb_event_uri">Facebook Event Page</label>';
		echo '<input type="text" id="fb_event_uri" name="fb_event_uri" value="' . sanitize_text_field( $fb_event_uri ) . '" size="45" />';
		echo '<input type="hidden" id="facebook_event_id" name="facebook_event_id" value="' . sanitize_text_field( $facebook_event_id ) . '" size="25" />';
		echo '<input type="hidden" id="image_url" name="image_url" value="' . sanitize_text_field( $image_url ) . '" size="25" />';
		echo '<br />';
		echo "<h4 style='margin:10px 0px; padding:10px 0px; color:#0074A2;'>Venue Information</h4>";

		$fields = array(
			'venue_name' => 'Venue Name',
			'venue_phone' => 'Phone',
			'venue_email' => 'Email',
			'venue_website' => 'Website',
			'facebook_page' => 'Facebook',
			'geo_latitude' => 'Geo Latitude',
			'geo_longitude' => 'Geo Longitude'
		);

		foreach( $fields as $key => $label ) {
			$value = get_post_meta( $post->ID, $key, true );
			self::print_field( $key, $label, $value )
		}

		echo '<label for="venue_desc">Venue About</label><br /><br />';
		echo '<textarea rows="15" cols="50" id="venue_desc" name="venue_desc" class="widefat" style="width:100%!important; max-width:540px!important; max-height:100px!important;" />'. esc_textarea($venue_desc ) .'</textarea>';
		echo '<br />';
		echo '<br />';
		echo '</div>';

	}

	static function print_field( $key, $label, $value, $size = 45 ) {
		$k = esc_attr( $key );
		$v = esc_attr( value );
		printf( '<label for="%s">%s</label>', $k, $label );
		printf( '<input type="text" id="%s" name="%s" value="%s" size="%d" /><br />', $k, $k, $v, $size );
	}

	static function metabox_feature( $post ) {
		$feature_event_value = get_post_meta($post->ID, 'feature_event', true);

		if($feature_event_value == "yes"){
			$field_id_checked = 'checked="checked"';
		}else{
			$field_id_checked = '';
		}
		echo '<label for="location">Feature this event</label>
		<input type="checkbox" name="feature_event" id="feature_event" value="yes" '.$field_id_checked.'/>';
	}

	static function save_post( $post_id ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if( isset( $_POST[ 'feature_event' ] ) ) {
			update_post_meta( $post_id, 'feature_event', 'yes' );
		} else {
			update_post_meta( $post_id, 'feature_event', 'no' );
		}

		$fields = array(
			'location',
			'ticket_uri',
			'facebook_event_id',
			'image_url',
			'start_time',
			'end_time',
			'event_starts',
			'event_starts_sort_field',
			'event_ends',
			'fb_event_uri',
			'venue_phone',
			'venue_email',
			'venue_website',
			'facebook_page',
			'facebook',
			'venue_name',
			'venue_desc',
			'geo_latitude',
			'geo_longitude'
		);

		foreach ( $fields as $meta){
			if ( isset( $_POST[$meta] ) ) {
				update_post_meta( $post_id, $meta, wp_unslash( $_POST[$meta] ) );
			}
		}
	}
}
