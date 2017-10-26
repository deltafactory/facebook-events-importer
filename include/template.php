<?php

add_image_size( 'fb_event_cover', '784', '295', true );
add_image_size( 'fb_event_list', '288', '192', true );
add_image_size( 'fb_event_ad', '288', '295', true );
add_theme_support( 'post-thumbnails' );

function get_fbe_field($meta){
	return get_post_meta( get_the_ID(), $meta, true );
}

function fbe_field($meta){
	echo get_fbe_field( $meta );
}

function get_fbe_date($meta,$format){
	$event_date = get_post_meta( get_the_ID(), $meta, true );
	$timezone = get_post_meta( get_the_ID(),'event_timezone', true );
	if($event_date){
		if($timezone){
			date_default_timezone_set($timezone);
		}
		$fbdate = strtotime($event_date);
		return date($format, $fbdate);
	}
}

function get_fbe_image($size){
	$image = wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID()), $size );
	return $image['0'];
}

function fbe_image($size){
	echo get_fbe_image( $size );
}
