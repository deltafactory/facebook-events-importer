<?php

   function fbe_app_data_callback(){
		 $app_id = sanitize_text_field($_POST["app_id"]);
		 $app_secret = sanitize_text_field( $_POST["app_secret"]);
		 update_option("app_id", $app_id);
         update_option("app_secret", $app_secret);
         fbe_validate_session(get_option("app_id"),get_option("app_secret"));
         die();
	}

  	function setup_fbe_import_admin_menu() {
		add_submenu_page('options-general.php','Facebook Events Setup', 'Facebook Events', 'manage_options','facebook_events_import', 'fbe_import_settings');
	}

    add_filter("plugin_action_links_facebook_events_import/facebook_events_importer.php", 'fbe_settings' );

   function fbe_settings($links) {
	   $settings_link = admin_url('options-general.php');
	   array_unshift($links, $settings_link);
	   return $links;
   }



  function import_fbe_scripts() {
	  if ( is_admin() ) {
	  wp_enqueue_style( 'fbe_style', plugins_url( '/assets/css/fb_import.css', __FILE__ ) );
	  wp_register_script('fbe_import', plugins_url( '/assets/js/facebook_import.js', __FILE__ ), array('jquery'), '1.0.0');
	  wp_localize_script('fbe_import', 'fbeAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
	  wp_enqueue_script('jquery-ui-datepicker');
      wp_enqueue_style('jquery-style-ui', plugins_url( '/assets/css/jquery-ui.css', __FILE__) );
      wp_enqueue_script( 'fbe_import' );
 		}
    }

  function fbe_callback(){

        $facebook_page = sanitize_text_field($_POST["page"]);
        $facebook_pages = get_option("facebook_pages");
        if ($facebook_page == "") {
        echo '<div class="error" style="color:#222222; font-weight:700; font-size:1em; padding:10px">You have to enter something.</div>';
        die();
        }
        if (strpos($facebook_pages,$facebook_page) !== false) {
         echo '<div class="error" style="color:#222222; font-weight:700; font-size:1em; padding:10px">'.$facebook_page.' already exists.</div>';
        }else{
        fbe_facebook_sdk($facebook_page,get_option("app_id"),get_option("app_secret"));
		$facebook_pages = sanitize_text_field($_POST["pages"].','.$facebook_page);
    	update_option("facebook_page", $facebook_page);
        update_option("facebook_pages", $facebook_pages);
		}


		die();
   }


  function fbe_remove_callback(){
        $facebook_page = sanitize_text_field($_POST["page"]);
		$facebook_pages = str_replace($facebook_page,"",get_option("facebook_pages"));
        update_option("facebook_pages", $facebook_pages);
		die();
  }

  function fbe_update_callback(){
        $facebook_page = sanitize_text_field($_POST["page"]);
        fbe_facebook_sdk($facebook_page,get_option("app_id"),get_option("app_secret"));
        update_option("facebook_page", $facebook_page);
		die();
  }


function fbe_import_settings(){
?>


	<?php echo '<div id="wfei_plugin_head"><img class="full-width" src="' . plugins_url( 'assets/images/wfei.svg', __FILE__ ) . '" ></div>'; ?>
	<h3>Facebook App Settings</h3>
	<p>You will need a Facebook App ID and App Secret to import events. <a href="https://developers.facebook.com/apps/">Get App ID &amp; App Secret</a></p>
	 <form id="facebook_app" method="post" >
		 <label for="app_id">App ID</label>
		 <input type="text" id="app_id" name="app_id" value="<?php echo get_option("app_id"); ?>">
		 <label for="app_secret">App Secret</label>
		 <input id="app_secret" name="app_secret" type="text" value="<?php echo get_option("app_secret"); ?>" />
		 <input type="submit" value="Save App Settings" class="button-secondary"/>
	 </form>
	 <div id="appdata_results"></div>
	 <br/>
	 <hr />

	<!-- PAID FEATURES -->
	 <h3><span class="pro">PRO</span> Settings</h3>
	 <?php $fbe800 = get_option("facebook_events_pro_version");  if($fbe800){

  	$slug = get_option("slug");
	if($slug == ""){$slug = 'facebook-events';}

   	$fbe_per_page = get_option("fbe_posts_per_page");
   	if($fbe_per_page == ""){$fbe_per_page = 10;}

   	$fbe_geo_map = get_option("fbe_geo_map");
   	$fbe_venue = get_option("fbe_venue");

	$wpfbe_primary_color = get_option('wpfbe_primary_color');
				if ($wpfbe_primary_color == '') {
				$wpfbe_primary_color = '#0075A2';
	}
	$wpfbe_secondary_color = get_option('wpfbe_secondary_color');
				if ($wpfbe_secondary_color == '') {
				$wpfbe_secondary_color = '#222222';
	}
	$wpfbe_tertiary_color = get_option('wpfbe_tertiary_color');
				if ($wpfbe_tertiary_color == '') {
				$wpfbe_tertiary_color = '#939597';
	}
	$wpfbe_inverted_color = get_option('wpfbe_inverted_color');
				if ($wpfbe_inverted_color == '') {
				$wpfbe_inverted_color = '#fff';
	}

   ?>
   <div id="plugin_settings"></div>
   <form id="wfei_plugin_settings" method="post" >
	<b> Set your events page slug: </b> <a href="<?php echo site_url(); ?>/<?php echo $slug; ?>"><?php echo site_url(); ?>/<?php echo $slug; ?></a></br/>
	<label for="slug"><span style="color:#99999E;"><i><?php echo site_url(); ?>/</i></span></label>                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          </label>
	<input type="text" id="slug" name="slug" value="<?php echo $slug; ?>">


	<p>
	<input type="checkbox" id="fbe_geo_map" name="fbe_geo_map" <?php if($fbe_geo_map != 'false'){echo 'checked';} ?> />

	<label for="fbe_geo_map"><b>Display Google Map</b></label>
	<br /><span style="color:#99999E;"><i>Displays Google Map on single events page</i></span>
	</p>
	<p>
	<input type="checkbox" id="fbe_venue" name="fbe_venue" <?php if($fbe_venue != 'false'){echo 'checked';} ?> />

	<label for="fbe_venue"><b>Display Venue Details</b></label>
	<br /><span style="color:#99999E;"><i>Displays onsingle events page</i></span>
	</p>
	<p>
	<label for="fbe_posts_per_page"><b>Posts per page</b></label>                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          </label>
		<input type="text" size="5" id="fbe_posts_per_page" name="fbe_posts_per_page" value="<?php echo $fbe_per_page; ?>">
		<br /> <span style="color:#99999E;"><i>Enter <b>"all"</b> to display all.</i></span>
	</p>

<div class="group"></div>

		<h3>Style Editor</h3>
		<div id="wpfbe_style_editor">

		<div class="wpfbe_style_picker">
		<label for="wpfbe_primary_color"><b>Primary Color</b><br/> Dates, links, feat event button</label> <br/>
		<input type="text" value="<?php echo $wpfbe_primary_color; ?>" name="wpfbe_primary_color"  id="wpfbe_primary_color" class="wpfbe-color-field" />
		</div>

		<div class="wpfbe_style_picker">
		<label for="wpfbe_secondary_color"><b>Secondary Color</b> <br/>Text color, event highlight</label> <br/>
		<input type="text" value="<?php echo $wpfbe_secondary_color; ?>" name="wpfbe_secondary_color"  id="wpfbe_secondary_color" class="wpfbe-color-field" />
		</div>

		<div class="wpfbe_style_picker">
		<label for="wpfbe_tertiary_color"><b>Tertiary Color</b> <br/>Event location</label> <br/>
		<input type="text" value="<?php echo $wpfbe_tertiary_color; ?>" name="wpfbe_tertiary_color"  id="wpfbe_tertiary_color" class="wpfbe-color-field" />
		</div>

		<div class="wpfbe_style_picker">
		<label for="wpfbe_inverted_color"><b>Inverted Color</b> <br/> Inverted text </label> <br/>
		<input type="text" value="<?php echo $wpfbe_inverted_color; ?>" name="wpfbe_inverted_color"  id="wpfbe_inverted_color" class="wpfbe-color-field" />
		</div>

	<div class="group"></div>
	</div>
	<div class="group"></div>
	<br />
	<input type="submit" value="Save Settings" class="button-primary"/>
	<img class="loader" src="<?php echo plugins_url( 'assets/images/X-loader.gif', __FILE__ ); ?>">
    </form>


	 <?php }else{ ?>
	   <div class="updated"  style="padding:10px; margin:0px 0px 10px 0px; border-color:#28a9e1;">
		<span style="font-weight:900;"><img src="http://wpfbevents.com/wp-content/themes/wpfbevents/assets/images/twitter_bird.png" style="margin-right:10px; position:relative; top:5px; " /><a style="color:#28a9e1;" href="http://wpfbevents.com/" target="_blank">Pay with a tweet </a>to upgrade to PRO version!</span>
      </div>
	 More settings and options available when you <b><a href="http://wpfbevents.com/">upgrade to the <i>PRO</i> version</a></b>.
	 <?php } ?>
	 <br />
	 You can also create your own templates. <b><a href="http://wpfbevents.com/code-examples/">View code examples</a></b>.
     <hr />
	<!-- END PAID FEATURES -->

	 <div id="wfei_events_wrap">
	 <h3>Import Events</h3>
	<p> Enter a Facebook page id or page name that you want to import events from.</p>
	<form id="facebook_event_import" method="post" >
	        <textarea id="facebook_pages" class="hidden" name="facebook_pages" rows="24" cols="50"><?php echo get_option("facebook_pages"); ?></textarea>
	        <input type="text" id="facebook_page" name="facebook_page">
	        <input type="hidden" id="facebook_page_updated" name="facebook_page_updated">
	        <input type="hidden" name="update_settings" value="Y" />
	        <input type="submit" value="Import Page Events" class="button-primary"/>
	        <img class="loader" src="<?php echo plugins_url( 'assets/images/X-loader.gif', __FILE__ ); ?>" width="30" heigh="30">
	</form>
	        <div id="event_results_loading"></div>
	        <div id="event_results"></div>
	        <br />
	   	 <hr />
	<h3>Imported Pages</h3>
	<p>We'll fetch events for you automaticly but you can reload at anytime. Deleting does <b>not</b> remove events previously imported but will remove them from update queue.</p>

	 <?php
	 $pages = get_option("facebook_pages");
	 $location = array_filter(explode(",",$pages));
     $liq = 0;
	foreach ($location as $loc){
		$liq++;
	  }

	if($liq >= 1){
	if(get_option('fbe_cron_date') == ''){
	    $fbe_last = current_time('timestamp');
	}else{
		$fbe_last = get_option('fbe_cron_date');
	}

    $fbe_current_time = current_time('timestamp');

	echo '<i style="color:#222222; font-size:12px; ">Automaticly updated: <b style="color:#0075A2;">'. human_time_diff( $fbe_last, $fbe_current_time ) . ' ago</b></i>';
	}

	 ?>
	<ul>
	<?php
	$pages = get_option("facebook_pages");
	$location = array_filter(explode(",",$pages));

	foreach ($location as $loc){
	if(preg_match('/^[a-zA-Z]+[a-zA-Z0-9.]+$/', $loc))
			{

			 echo '<li class="fb_event_page"><a href="https://facebook.com/'.$loc.'">' .$loc. '</a>
			 <span class="fetch" data-id="'.$loc.'" style="background-image: url('. plugins_url( '/assets/images/reload_events.svg', __FILE__ ).');">Fetch</span>
			 <span class="remove" data-id="'.$loc.'" style="background-image:url('.plugins_url( '/assets/images/delete_this.svg', __FILE__ ).')">remove</span>
			 </li>';
			}
			else
			{
			    //invalid
			}
	}

	?>
	</ul>
	</div>
<?php }
