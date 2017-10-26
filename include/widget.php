<?php
class WPFBEvents_Widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			'fbe_widget',
			__( 'Facebook Events widget', 'text_domain' ),
			array( 'description' => __( 'Show Facebook events in your sidebar', 'text_domain' ), )
		);
	}

	function widget( $args, $instance ) {

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}

		$disp_posts = apply_filters( 'post_count', $instance['disp_posts'] );
        $today = current_time('m/d/Y');
        $oneYear= date('m/d/Y', strtotime('+ 365 day'));
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
		    'posts_per_page' => $disp_posts,
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
			$featured = get_post_meta('feature_event', true);
			?>
			<div class="fbecol-1-1">
			<div class="fbe-sidebar-post" data-id="<?php echo $permalink; ?>">
			<div class="fbe_list_bar">
			<div class="fbe_list_date">
			<div class="fbe_list_month"><?php echo $event_starts_month; ?></div>
			<div class="fbe_list_day"><?php echo $event_starts_day; ?></div>
			</div>
			<div class="fbe_col_title"><h2><?php echo limitFBETxt( $event_title,35); ?></h2></div>
			<div class="fbe_col_location"><h4><?php echo limitFBETxt($location,100); ?></h4></div>
			</div>

			</div>
			</div>
			<?php
		endwhile;
	endif;

	wp_reset_query();

	}


	function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Upcoming Events', 'text_domain' );
		$disp_posts= ! empty( $instance['disp_posts'] ) ? $instance['disp_posts'] : __( '3', 'text_domain' );
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		<br />
		<label for="<?php echo $this->get_field_id( 'disp_posts' ); ?>"><?php _e( 'Posts to Display:' ); ?></label> <br />
		<input class="shortfat" size="8" id="<?php echo $this->get_field_id( 'disp_posts' ); ?>" name="<?php echo $this->get_field_name( 'disp_posts' ); ?>" type="text" value="<?php echo esc_attr( $disp_posts ); ?>">
		</p>
		<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['disp_posts'] = ( ! empty( $new_instance['disp_posts'] ) ) ? strip_tags( $new_instance['disp_posts'] ) : '';
		return $instance;
	}
}
