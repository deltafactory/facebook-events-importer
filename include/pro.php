<?php

//if(get_option("facebook_events_pro_version")){
add_action( 'widgets_init', 'fbe_widgets_init' );
add_action( 'widgets_init', 'register_fbe_widget' );
add_action( 'init', 'import_fbe_PRO_scripts' );
add_filter( 'single_template', 'get_fbe_custom_post_type_template' );
add_action( 'wp_ajax_load_facebook_events', 'load_fbe_callback' );
add_action( 'wp_ajax_nopriv_load_facebook_events', 'load_fbe_callback' );
add_filter( 'page_template', 'fbe_pro_template' );
add_action( 'wp_ajax_wfei_plugin_settings', 'wfei_plugin_settings_callback' );
add_action( 'wp_ajax_nopriv_wfei_plugin_settings', 'wfei_plugin_settings_callback' );
//}

/* PRO Features */
function fbe_pro_page_id(){
    return get_option('fbe_pro_page_id');
}

function fbe_pro_template( $page_template ){
    $id = fbe_pro_page_id();

    if ( is_page( $id ) ) {
        $page_template = dirname( __FILE__ ) . '/templates/facebook-events-template.php';
    }

    return $page_template;
}

function get_fbe_custom_post_type_template($single_template) {
    global $post;

    if ($post->post_type == 'facebook_events') {
        $single_template = dirname( __FILE__ ) . '/templates/single-facebook_events.php';
    }
    return $single_template;
}

function load_fbe_callback(){
    $page = sanitize_text_field($_POST["page"]);
    $max = get_option("fbe_posts_per_page");
    if($max=='all'){$max = -1;}
    get_fbe_events($max,$page);
    die();
}

function get_fbe_events($max,$page){
    global $post;

$paged = (get_query_var('paged')) ? get_query_var('paged') : $page;


		$currentdate = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-1,date("Y")));

		$args = array (
                  	'meta_query'=> array(
	                    array(
	                      'key' => 'event_starts_sort_field',
	                      'compare' => '>',
	                      'value' => $currentdate,
	                      'type' => 'DATE',
	                    )),
		    'post_type' => 'facebook_events',
			'posts_per_page' => $max,
			'paged' => $paged,
	        'meta_key' => 'event_starts_sort_field',
            'orderby' => 'meta_value',
			'order' => 'ASC'

		);


		$fbe_query = new WP_Query( $args );
		 if( $fbe_query->have_posts() ):
		$maxPages = $fbe_query->max_num_pages;
	     echo '<div id="maxPages" data-id='.$maxPages.'></div>';
		while ( $fbe_query->have_posts() ) : $fbe_query->the_post();
		  $event_title = get_the_title();
		  $event_desc =  get_the_content();
		  $event_image = get_fbe_image('cover');
		  $event_starts_month = get_fbe_date('event_starts','M');
		  $event_starts_day = get_fbe_date('event_starts','j');
		  $location = get_fbe_field('location');
		  $permalink = get_permalink();
		  $featured = get_post_meta($post->ID, 'feature_event', true);
	?>


	  <div class="fbecol-1-3">
	  <div class="fbecol" data-id="<?php echo $permalink; ?>">
	  <div class="fbe_list_image" style="background-image:url(<?php echo get_fbe_image('cover'); ?>);" >
	  <div class="fbe_list_bar">
	  <div class="fbe_list_date">
	  	<div class="fbe_list_month"><?php echo $event_starts_month; ?></div>
		<div class="fbe_list_day"><?php echo $event_starts_day; ?></div>
	  </div>
	  <div class="fbe_col_title"><h2><?php echo limitFBETxt( $event_title,30); ?></h2></div>
	  <div class="fbe_col_location"><h4><?php echo limitFBETxt($location,40); ?></h4></div>
	  </div>
	  </div>
	  </div>
	  </div>
	<?php
	     endwhile;
	wp_reset_postdata();
		 endif;

}



function wfei_plugin_settings_callback(){

     $slug = str_replace(" ","-", sanitize_text_field($_POST["slug"]));
     $fbe_geo_map = sanitize_text_field($_POST["fbe_geo_map"]);
     $fbe_venue  = sanitize_text_field($_POST["fbe_venue"]);
	 $fbe_per_page = sanitize_text_field($_POST["fbe_posts_per_page"]);
     update_option("fbe_posts_per_page", $fbe_per_page );
   	 update_option("fbe_geo_map", $fbe_geo_map);
     update_option("fbe_venue", $fbe_venue  );

     update_option("wpfbe_primary_color", sanitize_text_field($_POST["wpfbe_primary_color"]));
     update_option("wpfbe_secondary_color", sanitize_text_field($_POST["wpfbe_secondary_color"]));
     update_option("wpfbe_tertiary_color", sanitize_text_field($_POST["wpfbe_tertiary_color"]));
     update_option("wpfbe_inverted_color", sanitize_text_field($_POST["wpfbe_inverted_color"]));


     $page_id = fbe_pro_page_id();

		     if($slug != ''){
			 update_option("slug", $slug);
		     }else{
		     echo'<div class="error" style="color:#222222; font-weight:700; font-size:1em; padding:10px">Page slug required</div>';
		     }
		     echo '<div class="updated" style="color:#222222; font-weight:700; font-size:1em; padding:10px">Settings Saved</div>';

		die();
}


   function limitFBETxt($content,$limit){

	$content = preg_replace("/<img[^>]+\>/i", "", $content);
	$content = strip_tags($content);
	$content = strip_shortcodes( $content );

	if (strlen($content) > $limit) {
	$stringCut = substr($content, 0, $limit);
	$string = substr($stringCut, 0, strrpos($stringCut, ' ')).'... ';
	return $string;
	}else{
		return $content;
		}
   }

/* FACEBOOK EVENTS WIDGET  & SIDEBAR */



function fbe_widgets_init() {
    register_sidebar( array(
        'name' => __( 'Facebook Events Sidebar', 'facebook-events' ),
        'id' => 'facebook-events',
        'description' => __( 'Widgets in this area will be shown on facebook events.', 'facebook-events' ),
        'before_widget' => '<div class="fbe-widget">',
		'after_widget'  => '</div>',
		'before_title'  => '<h2>',
		'after_title'   => '</h2><hr />',
    ) );
}

function register_fbe_widget() {
    register_widget( 'WPFBEvents_Widget' );
}

function import_fbe_PRO_scripts() {
    wp_enqueue_script('fbe_pro_import', plugins_url( '/assets/js/facebook_events_pro.js', __FILE__ ), array('jquery'), '1.0.0');
    wp_localize_script('fbe_pro_import', 'fbeAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
    wp_enqueue_style( 'fbe_pro_style', plugins_url( '/assets/css/facebook_events_pro.css', __FILE__ ) );
    wp_register_script('fbe_map', 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false');
    wp_enqueue_script( 'fbe_map','','',false  );
    if( is_admin() ) {
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker');
    }
}

function fbe_event_map() {
if(is_singular( 'facebook_events' )) {
	if(get_option('fbe_geo_map') == 'true'){
	$LatLng = get_fbe_field('geo_latitude').','.get_fbe_field('geo_longitude');
    echo '
	<script>
	var event_loc = new google.maps.LatLng('.$LatLng.');
	var marker;
    var fbe_map;

	function initialize(){
	 var mapOptions = {
     zoom: 16,
     scrollwheel: false,
     navigationControl: false,
     scaleControl: false,
     draggable: false,
     center: event_loc,
     panControl: false,
     mapTypeControl: false,
     zoomControl: true,
     zoomControlOptions: {
     	style: google.maps.ZoomControlStyle.SMALL,
     	position: google.maps.ControlPosition.RIGHT_BOTTOM
    	}
	  };

	fbe_map = new google.maps.Map(document.getElementById("fbe_map_canvas"),mapOptions);

	marker = new google.maps.Marker({
    map:fbe_map,
    animation: google.maps.Animation.DROP,
    position: event_loc
 	});

	}


	google.maps.event.addDomListener(window,"load",initialize);
	</script>';
		}
	}
}

add_action('wp_head', 'fbe_event_map');



/* END PRO */
