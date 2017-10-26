<?php
class WPFBEvents_Customize {

	public static function wpfbe_header_output() {
		$primary_color = get_option('wpfbe_primary_color');
		$second_color = get_option('wpfbe_secondary_color');
		$third_color = get_option('wpfbe_tertiary_color');
		$inverted_color = get_option('wpfbe_inverted_color');
		$css = '%s { %s: %s }';

		echo '<!-- Facebook Events CSS--><style type="text/css">';

		printf($css, '.fbe_list_date,.fbe_feat_event_link', 'background-color', $primary_color );
		printf($css, '#load_more_fbe,.fbe-facebook-css > div ', 'background-color', $second_color, '','');
		printf($css, '.fbe_col_location h4', 'color', $third_color );
		printf($css, '.fbe_list_date,.fbe_feat_event_link,#load_more_fbe', 'color', $inverted_color );
		printf($css, '.prev_fb_event,.next_fb_event,#fbe_sidebar a', 'color', $primary_color );
		printf($css, '.prev_fb_event:hover,.next_fb_event:hover', 'color', $inverted_color );
		printf($css, '.prev_fb_event:hover,.next_fb_event:hover,#event_facebook_page', 'background-color', $primary_color );
		printf($css, '#event_facebook_page', 'fill', $inverted_color );

		$prefix = array(
			'@-webkit-keyframes',
			'@-moz-keyframes',
			'@-o-keyframes',
			'@keyframes'
		);

		foreach( $prefix as $p ) {
			printf( '%s fbehover{ 0%{} 100%{background-color:%s; color:%s;} }', $p, $second_color, $inverted_color );
			printf( '%s fbehoverOut{ 0%{background-color:%s;} 100%{ background-color:rgba(255,255,255,0.9); } }', $p, $second_color );
		}

		echo '</style><!--/  Facebook Events CSS-->';
	}
}
