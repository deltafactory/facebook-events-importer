<?php

class WPFBEvents_Shortcode {
	static function shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'show' => -1,
		), $atts );

		$a = $atts['show'];

		echo '<div class="fbegrid fbegrid-pad">';

		global $post;
		$currentdate = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-1,date("Y")));
		$args = array (
			'meta_query'=> array(
				array(
					'key' => 'event_starts_sort_field',
					'compare' => '>',
					'value' => $currentdate,
					'type' => 'DATE',
				)
			),
			'post_type' => 'facebook_events',
			'posts_per_page' => $a,
			'meta_key' => 'event_starts_sort_field',
			'orderby' => 'meta_value',
			'order' => 'ASC'
		);


		$fbe_query = new WP_Query( $args );
		if( $fbe_query->have_posts() ):
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

		?>
	</div>
	<?php
}
