<?php
/**
* Plugin Name: Listings for Appfolio
* Description: This plugin gets your Appfolio property listings and display them in an interactive way rather than using iframe and gives you styling freedom.
* Version: 1.1.8
* Author: Listings for Appfolio
* Author URI: http://listingsforappfolio.com/
* License: GPL+2
* Text Domain: listings-for-appfolio
* Domain Path: /languages
*/

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
   exit;
}

add_action( 'init', 'apfl_init_plugin', 1 );
if (!function_exists('apfl_init_plugin')) {
	function apfl_init_plugin(){
		global $apfl_plugin_url;
		global $client_listings_url;
		global $client_gmap_api;
		$apfl_plugin_url = plugin_dir_url( __FILE__ );
		$client_listings_url = get_option('apfl_url');
		$client_gmap_api = get_option('apfl_gmap_api');
		
		add_action( 'wp_enqueue_scripts', 'apfl_styles_scripts' );
		
		// Including main functions
		if(!class_exists ('simple_html_dom')){
			require(plugin_dir_path(__FILE__ ) . 'inc/simple_html_dom.php');
		}
		include(plugin_dir_path(__FILE__ ) . 'inc/single-listing.php');
		include(plugin_dir_path(__FILE__ ) . 'inc/listings.php');
		
		// Shortcodes
		add_shortcode('apfl_listings', 'apfl_display_all_listings');
		
		if ( is_admin() ){
			add_action( 'admin_enqueue_scripts', 'apfl_admin_styles_scripts' );
		}
		
	}
}

if (!function_exists('apfl_styles_scripts')) {
	function apfl_styles_scripts(){
		
		$licensed = get_option('apfl_free_licensed');
		if(!$licensed){
			return;
		}
		else{
			wp_enqueue_style(
				'apfl-style',
				plugin_dir_url( __FILE__ ) . 'css/style.css'
			);
		}
		
	}
}

if (!function_exists('apfl_admin_styles_scripts')) {
	function apfl_admin_styles_scripts(){
		wp_enqueue_style(
			'apfl-admin-style',
			plugin_dir_url( __FILE__ ) . 'css/admin-style.css'
		);
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script(
			'apfl-admin-script',
			plugins_url('js/admin-main.js',__FILE__ ),
			array('jquery', 'wp-color-picker')
		);
		// wp_enqueue_script( 'my-script-handle', plugins_url('my-script.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
	}
}

function apfl_admin_key_check() {
	
	$item_reference     = 'appfolio-listings';
	$license_server_url = 'https://listingsforappfolio.com';
	$special_secretkey  = '6055f16d4e4e60.82251702';

	$domain_name = esc_url( home_url() );
	if ( is_ssl() ) {
		$domain_name = str_replace( 'https://', '', $domain_name );
	} else {
		$domain_name = str_replace( 'http://', '', $domain_name );
	}
	
	$is_sent = get_option('apfl_free_key_sent');
	
	if(!$is_sent){
		
		$api_params = array(
			'slm_action'        => 'slm_activate',
			'secret_key'        => $special_secretkey,
			'license_key'       => '670382dcc48b6',
			'registered_domain' => $domain_name,
			'item_reference'    => urlencode( $item_reference ),
		);
		$query    = esc_url_raw( add_query_arg( $api_params, $license_server_url ) );
		$response = wp_remote_get(
			$query,
			array(
				'timeout' => 20,
				'sslverify' => false,
			)
		);
		if ( is_wp_error( $response ) ) {
			// echo 'Unexpected Error! The query returned with an error.';
		}
		
		update_option('apfl_free_key_sent', true);
		
	}
	
	$licensed = get_option('apfl_free_licensed');
	if (!$licensed || is_admin()) {
		
		$lic_checked = get_transient('apfl_free_lic_checked');
		if (false == $lic_checked) {
			
			$api_params = array(
				'slm_action'        => 'slm_check',
				'secret_key'        => $special_secretkey,
				'license_key'       => '670382dcc48b6',
				'registered_domain' => $domain_name,
				'item_reference'    => urlencode( $item_reference ),
			);
			$query    = esc_url_raw( add_query_arg( $api_params, $license_server_url ) );
			$response = wp_remote_get(
				$query,
				array(
					'timeout' => 20,
					'sslverify' => false,
				)
			);
			if ( is_wp_error( $response ) ) {
				// echo 'Unexpected Error! The query returned with an error.';
			}
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			
			if ( is_object( $license_data ) ) {
				if ( property_exists( $license_data, 'result' ) && property_exists( $license_data, 'status' ) ) {
					if ( 'success' == $license_data->result && 'active' == $license_data->status ) {
						update_option('apfl_free_licensed', true);
						set_transient('apfl_free_lic_checked', true, 43200); // 12 hours cache if license key is true
					}
					else{
						update_option('apfl_free_licensed', false);
					}
				}
			} else if ( 'no_response' === $license_data ) {
				
			}
			
		}
		
	}

}
add_action( 'admin_init', 'apfl_admin_key_check' );

// Plugin Configuration Page
if(is_admin()){
	add_action('admin_menu', 'apfl_admin_config');
	if (!function_exists('apfl_admin_config')) {
		function apfl_admin_config() {
			
			// add_options_page('Listings for Appfolio', 'Listings for Appfolio', 'manage_options', 'apfl', 'apfl_config_callback');
			
			add_menu_page('Listings for Appfolio', 'Appfolio', 'manage_options', 'apfl', 'apfl_config_callback', 'dashicons-admin-home');
			add_submenu_page('apfl', 'Settings', 'Settings', 'manage_options', 'apfl', 'apfl_config_callback', 1);
			add_submenu_page('apfl', 'Appfolio Slider', 'Slider', 'manage_options', 'apfl-slider', 'apfl_slider_callback', 2);
			add_submenu_page('apfl', 'Appfolio Carousel', 'Carousel', 'manage_options', 'apfl-carousel', 'apfl_carousel_callback', 2);
			add_submenu_page('apfl', 'Appfolio Customizer', 'Customizer', 'manage_options', 'apfl-builder', 'apfl_builder_callback', 3);
			
		}
	}
	if (!function_exists('apfl_config_callback')) {
		function apfl_config_callback(){
			if (!current_user_can('manage_options')){
				wp_die( __('You do not have sufficient permissions to access this page.') );
			}
			
			if($_POST){
				if(isset($_POST['apfl_config_submit'])){
					if(isset($_POST['apfl_config_url'])){
						$apfl_url = sanitize_text_field($_POST['apfl_config_url']);
						$apfl_url_updated = update_option('apfl_url', $apfl_url);
					}
					if(isset($_POST['apfl_config_gmap_api'])){
						$apfl_gmap_api = sanitize_text_field($_POST['apfl_config_gmap_api']);
						$apfl_gmap_api_updated = update_option('apfl_gmap_api', $apfl_gmap_api);
					}
					
					// Saved message
					if($apfl_url_updated || $apfl_gmap_api_updated){
						echo '<div class="notice notice-success is-dismissible"><p>Settings Saved!</p></div>';
					}
				}
			}
			?>

			<div class="wrap">
				<div id="apfl_settings">
					<form method='POST' action="">
						<br>
						<p style=" margin: 0; padding: 12px 15px; background: rgb(89, 143, 205); font-size: 16px; color: #fff; display: inline-block; margin-bottom: 10px;">If you like this plugin, please <a href="https://wordpress.org/support/plugin/listings-for-appfolio/reviews/#new-post" style="font-weight: bold; color: #fff;" target="_blank">Add Your Review.</a> Looking for more features and customization options? See the PRO version <a target="_blank" href="https://listingsforappfolio.com/" style="font-weight: bold; color: #fff;">here</a></p>
						<h1>Listings for Appfolio Settings <span style="font-size: 15px;background: lightgrey;padding: 4px 10px;">shortcode - [apfl_listings]</span></h1>
						<table class="form-table">
							<tr>
								<th>
									<?php $apfl_listing_url = get_option('apfl_url'); ?>
									<label for="apfl_config_url">Appfolio URL to fetch listings: </label>
								</th>
								<td>
									<input type="text" name="apfl_config_url" id="apfl_config_url" style="min-width: 350px;" placeholder="For Example - https://example.appfolio.com" value="<?php echo $apfl_listing_url; ?>">
								</td>
							</tr>
							
							<tr>
								<th>
									<?php $apfl_gmap_api = get_option('apfl_gmap_api'); ?>
									<label for="apfl_config_gmap_api">Google Map JS API Key</label>
								</th>
								<td>
									<input type="text" name="apfl_config_gmap_api" id="apfl_config_gmap_api" style="min-width: 350px;" placeholder="Leave Blank to disable Google Map" value="<?php echo $apfl_gmap_api; ?>">
								</td>
							</tr>
						</table>
						
						<p class="submit">
							<input type="submit" name="apfl_config_submit" id="apfl_config_submit" class="button-primary" value="Save"/>
						</p>
					</form>
				</div>
			</div>
		<?php
		}
	}
	
	// Slider Settings
	if (!function_exists('apfl_slider_callback')) {
		function apfl_slider_callback(){
		?>
			<div class="wrap">
				<div id="apfl_pp_slider">
					<form method='POST' action="">
						<br>
						<h1>Appfolio Listings Slider &nbsp;<span style=" font-size: 14px; font-weight: normal; background: lightgrey; padding: 3px 15px; ">Shortcode - [apfl_slider]</span> (PRO Feature)</h1>
						<table class="form-table">
							<tr>
								<th>
									<label for="apfl_slider_cnt">Number of Slides</label>
								</th>
								<td>
									<input type="number" id="apfl_slider_cnt" value="">
								</td>
							</tr>
							
							<tr>
								<th>
									<label for="apfl_slider_recent">Use Recent Listings</label>
								</th>
								<td>
									<input type="checkbox" id="apfl_slider_recent">
								</td>
							</tr>
							
						</table>
						
						<p class="submit"><input type="submit" value="Save" class="button-primary"></p>
						
					</form>
				</div>
			</div>
	<?php }
	}
	
	// Carousel Settings
	if (!function_exists('apfl_carousel_callback')) {
		function apfl_carousel_callback(){
		?>
			<div class="wrap">
				<div id="apfl_pp_crsl">
					<form method='POST' action="">
						<br>
						<h1>Appfolio Listings Carousel &nbsp;<span style=" font-size: 14px; font-weight: normal; background: lightgrey; padding: 3px 15px; ">Shortcode - [apfl_carousel]</span> (PRO Feature)</h1>
						<table class="form-table">
							<tr>
								<th>
									<label for="apfl_crsl_cnt">Number of Slides</label>
								</th>
								<td>
									<input type="number" id="apfl_crsl_cnt">
								</td>
							</tr>
							
							<tr>
								<th>
									<label for="apfl_crsl_recent">Use Recent Listings</label>
								</th>
								<td>
									<input type="checkbox" id="apfl_crsl_recent">
								</td>
							</tr>
							
						</table>
						
						<p class="submit"><input type="submit" id="apfl_crsl_sbmt" value="Save" class="button-primary"></p>
							
					</form>
				</div>
			</div>
	<?php }
	}
	
	// Content Builder
	if (!function_exists('apfl_builder_callback')) {
		function apfl_builder_callback(){
			
			// Customizer tabs
			$active_menu = 'listings_page';
			if(isset($_GET['tab']) && $_GET['tab']){
				$active_menu = sanitize_text_field( $_GET['tab'] );
			}

			?>

			<!-- Navbar -->
			<nav class="apfl_tab-container">
				<a href="<?php echo get_admin_url(); ?>admin.php?page=apfl-builder&tab=listings_page" class="tabs <?php echo ($active_menu == 'listings_page' ? 'apfl_active' : ''); ?>">Listings</a>
				<a href="<?php echo get_admin_url(); ?>admin.php?page=apfl-builder&tab=details_page" class="tabs <?php echo ($active_menu == 'details_page' ? 'apfl_active' : ''); ?>">Details</a>
			</nav>

			<div class="wrap">
				<?php if($active_menu == 'listings_page') { ?>
				<!-- Start of listings page customizer -->
				<div id="apfl-pro-customizer">
					<br>
					<h1>Appfolio Listings Customizer (PRO Feature)</h1>
					
					<form id="apfl_tmplt_frm" method='POST' action="">
						<table class="form-table">
							<tr>
								<th>
									<label for="apfl_template">Template</label>
								</th>
								<td>
									<label style="margin-right: 15px;"><input type="radio" value="1"> Hawk Template</label>
									<label style="margin-right: 15px;"><input type="radio" value="2"> Eagle Template</label>
									<label><input type="radio" value="9"> Custom Template</label>
								</td>
							</tr>
						</table>
					</form>
					
					<form method='POST' action="">
						<table class="form-table">
							<tr>
								<th>
									<label for="apfl_page_hdng">Listings Page Heading<br>(You can use html)</label>
								</th>
								<td>
									<input type="text" id="apfl_page_hdng" style="min-width: 350px;" placeholder="e.g. <h2>Find a Property for Rent</h2>">
								</td>
							</tr>
							
							<tr>
								<th>
									<label>Heading banner</label>
								</th>
								<td>
									<span>Background color: <input type="text" value="#232532" class="apfl-listings-color" />
									</span>
								</td>
							</tr>
							
							<tr>
								<th>
									<label>Heading Banner Image<br>(Suggested dimentions 1300x250px)</label>
								</th>
								<td class="custom_option">
									<span><input type="checkbox">Use image</span>
								</td>
							</tr>
							
							<tr>
								<th>
									<label>Listings Page Sub Heading</label>
								</th>
								<td>
									<input type="text" style="min-width: 350px;" placeholder="e.g. Find a Property for Rent">
								</td>
							</tr>
							
							<tr>
								<th>
									<label>Sub Heading</label>
								</th>
								<td class="custom_option sub-heading-option">
									<span>Font Size: <input type="text" value="22px"></span>
									<span>Font weight: 
										<select>
											<option value="100">100</option>
											<option value="200">200</option>
											<option value="300">300</option>
											<option value="400">400</option>
											<option value="500">500</option>
											<option value="600" selected="">600</option>
											<option value="700">700</option>
											<option value="800">800</option>
											<option value="900">900</option>
											<option value="bold">bold</option>
											<option value="bolder">bolder</option>
											<option value="lighter">lighter</option>
											<option value="normal">normal</option>
										</select>
									</span>

									<span>Font color: <input type="text" value="<?php echo $apfl_listings_banner_heading_color; ?>" class="apfl-listings-color" /></span>

									<span>Line height: <input type="text" value="1"></span>

									<span>Text transform: 
										<select>
											<option value="capitalize" selected="">capitalize</option>
											<option value="lowercase">lowercase</option>
											<option value="uppercase">uppercase</option>
											<option value="none">none</option>
											<option value="math-auto">math-auto</option>
										</select>
									</span>
								</td>
							</tr>
							
							<tr>
								<th></th>
								<td class="custom_option sub-heading-option">
									<span>Text align: <input type="text" value="center"></span>
									<span>Padding top: <input type="text" value="0"></span>
									<span>Padding bottom: <input type="text" value="20px"></span>
									<span>Padding left: <input type="text" value="0"></span>
									<span>Padding right: <input type="text" value="0"></span>
								</td>
							</tr>
							
							<tr>
								<th>
									<label>Display filters</label>
								</th>
								<td>Enable searching: <input type="checkbox" checked></td>
							</tr>
							<tr>
								<th></th>
								<td class="admin-fltrs">
									<span><input type="checkbox" id="apfl_pro_textarea_input">Search </span>
									<span><input type="checkbox" id="apfl_pro_cat_filter" checked>Cats </span>
									<span><input type="checkbox" id="apfl_pro_dog_filter" checked>Dogs </span>
									<span><input type="checkbox" id="apfl_pro_minrent_filter" checked>Min Rent </span>
									<span><input type="checkbox" id="apfl_pro_maxrent_filter" checked>Max Rent </span>
									<span><input type="checkbox" id="apfl_pro_bed_filter" checked>Beds </span>
									<span><input type="checkbox" id="apfl_pro_bath_filter" checked>Baths </span>
									<span><input type="checkbox" id="apfl_pro_cities_filter" checked>Cities </span>
									<span><input type="checkbox" id="apfl_pro_zip_filter" checked>Zip </span>
									<span><input type="checkbox" id="apfl_pro_movein_filter" checked>Desired Move In </span>
									<span><input type="checkbox" id="apfl_pro_sorting_filter" checked>Sorting </span>
								</td>
							</tr>
							
							<tr>
								<th>
									<label>Default Sort Order</label>
								</th>
								<td>
									<select id="def_sort" name="def_sort">
										<option value="date_posted" selected>Most Recent</option>
										<option>Rent (Low to High)</option>
										<option>Rent (High to Low)</option>
										<option>Bedrooms</option>
										<option>Availability</option>
									</select>
								</td>
							</tr>
							
							<tr>
								<th>
									<label>Search Button</label>
								</th>
								<td class="custom_option">
									<span>
										Text Color:
										<input type="text" value="#FFFFFF" class="apfl-listings-color" />
									</span>
									<span>
										Background: 
										<input type="text" value="#ff6600" class="apfl-listings-color" />
									</span>
								</td>
							</tr>
							
							<tr>
								<th>
									<label>Pagination</label>
								</th>
								<td class="custom_option">
									<span><input type="checkbox"></span>
									<span class="more-options">
										Per Page: 
										<input type="number" value="10" class="regular-text" min="1" max="100" style="width:60px" />
									</span>
								</td>
							</tr>
							
							<tr>
								<th>
									<label for="apfl_columns_cnt">Listings Page Layout</label>
								</th>
								<td>
									<select id="apfl_columns_cnt">
										<option>1 Column</option>
										<option>2 Columns</option>
										<option selected>3 Columns</option>
										<option>4 Columns</option>
										<option>5 Columns</option>
									</select>
								</td>
							</tr>

							<!-- Rent text -->
							<tr>
								<th>
									<label for="apfl_rent_text">Rent Text</label>
								</th>
								<td class="custom_option">
									<input type="text" id="apfl_rent_text" value="Rent" />
								</td>
							</tr>
							
							<tr>
								<th>
									<label>Display Rent Price</label>
								</th>
								<td class="custom_option">
									<span><input type="checkbox" id="apfl_listings_display_price" checked></span>
									
									<span class="more-options">
										Position: 
										<select>
											<option selected>On Image</option>
											<option>Below Image</option>
										</select>
									</span>
									
									<span class="more-options">
										Text Color:
										<input type="text" value="#ffffff" class="apfl-listings-color" />
									</span>
									<span class="more-options">
										Background: 
										<input type="text" value="#ff6600" class="apfl-listings-color" />
									</span>
									
								</td>
							</tr>
							
							<tr>
								<th>
									<label>Display Availability</label>
								</th>
								<td class="custom_option">
								
									<span><input type="checkbox" id="apfl_listings_display_avail" checked></span>
									
									<span class="more-options">
										Position: 
										<select>
											<option selected>On Image</option>
											<option>Below Image</option>
										</select>
									</span>
									
									<span class="more-options">
										Text Color:
										<input type="text" value="#ffffff" class="apfl-listings-color" />
									</span>
									<span class="more-options">
										Background: 
										<input type="text" value="#ff6600" class="apfl-listings-color" />
									</span>
									
								</td>
							</tr>
							
							<tr>
								<th>
									<label>Display Listing Title</label>
								</th>
								<td class="custom_option">
									<span><input type="checkbox" id="apfl_listings_display_ttl" checked></span>
									<span id="ttl_tag" class="more-options">
										Tag: 
										<select>
											<option selected>h1</option>
											<option>h2</option>
											<option>h3</option>
											<option>h4</option>
											<option>h5</option>
											<option>h6</option>
											<option>p</option>
										</select>
									</span>
								</td>
							</tr>
							
							<tr>
								<th>
									<label>Display Listing Address</label>
								</th>
								<td class="custom_option">
									<span><input type="checkbox" id="apfl_listings_display_address" checked></span>
									<span id="address_tag" class="more-options">
										Tag: 
										<select>
											<option>h1</option>
											<option>h2</option>
											<option>h3</option>
											<option selected>h4</option>
											<option>h5</option>
											<option>h6</option>
											<option>p</option>
										</select>
									</span>
								</td>
							</tr>
							
							<tr>
								<th>
									<label>Display Beds</label>
								</th>
								<td class="custom_option">
									<span><input type="checkbox" id="apfl_listings_display_beds" checked></span>
									<span id="bed_img" class="more-options">
										Image URL: 
										<input type="text" id="apfl_listings_bed_img" style="width: 450px" placeholder="Leave blank to hide image">
									</span>
								</td>
							</tr>
							
							<tr>
								<th>
									<label>Display Baths</label>
								</th>
								<td class="custom_option">
									<span><input type="checkbox" id="apfl_listings_display_baths" checked></span>
									<span id="bath_img" class="more-options">
										Image URL: 
										<input type="text" id="apfl_listings_bath_img" style="width: 450px" placeholder="Leave blank to hide image">
									</span>
								</td>
							</tr>
							
							<tr>
								<th>
									<label>Display Details Button</label>
								</th>
								<td class="custom_option">
									
									<span><input type="checkbox" id="apfl_listings_display_detail" checked></span>
									
									<span class="more-options">
										Text Color:
										<input type="text" value="#ffffff" class="apfl-listings-color" />
									</span>
									<span class="more-options">
										Background: 
										<input type="text" value="#598fcd" class="apfl-listings-color" />
									</span>
									<span class="more-options">
										Hover Text Color:
										<input type="text" value="#ffffff" class="apfl-listings-color" />
									</span>
									<span class="more-options">
										Hover Background: 
										<input type="text" value="#000000" class="apfl-listings-color" />
									</span>
									
								</td>
							</tr>
							
							<tr>
								<th>
									<label>Display Apply Button</label>
								</th>
								<td class="custom_option">
									
									<span><input type="checkbox" id="apfl_listings_display_apply" checked></span>
									
									<span class="more-options">
										Text Color:
										<input type="text" value="#ffffff" class="apfl-listings-color" />
									</span>
									<span class="more-options">
										Background: 
										<input type="text" value="#598fcd" class="apfl-listings-color" />
									</span>
									<span class="more-options">
										Hover Text Color:
										<input type="text" value="#ffffff" class="apfl-listings-color" />
									</span>
									<span class="more-options">
										Hover Background: 
										<input type="text" value="#000000" class="apfl-listings-color" />
									</span>
									
								</td>
							</tr>
							
							<tr>
								<th>
									<label>Display Schedule Button</label>
								</th>
								<td class="custom_option">
									<span><input type="checkbox"></span>
									<span class="more-options"> Text Color: <input type="text" value="#ffffff" class="apfl-listings-color" /></span>
									<span class="more-options"> Background: <input type="text" value="#ffffff" class="apfl-listings-color" /></span>
									<span class="more-options"> Hover Text Color: <input type="text" value="#ffffff" class="apfl-listings-color" /></span>
									<span class="more-options"> Hover Background: <input type="text" value="#ffffff" class="apfl-listings-color" /></span>
								</td>
							</tr>
							
							<!-- Change google map default zoom -->
							<tr>
								<th>
									<label for="apfl_def_map_zoom">Default Map Zoom</label>
								</th>
								<td class="custom_option">
									<input type="number" value="8" class="regular-text" min="1" max="100" style="width:60px"/>
								</td>
							</tr>
							
							<tr>
								<th>
									<label for="apfl_custom_apply_lnk">Custom Apply Link<br>(Leave blank for default link)</label>
								</th>
								<td>
									<input type="text" style="min-width: 350px;" placeholder="please use complete URL including http or https">
								</td>
							</tr>
							
							<tr>
								<th>
									<label for="apfl_custom_css">Custom CSS</label>
								</th>
								<td>
									<textarea id="apfl_custom_css" style="min-width: 350px;"></textarea>
								</td>
							</tr>
							
						</table>
						
						<p class="submit"><input type="submit" value="Save" class="button-primary"></p>
						
					</form>
				</div>
				<!-- End of listings page  -->
				<?php } 
					if($active_menu == 'details_page') {
				?>
				<!-- Start of details page -->

				<div id="apfl-pro-customizer-details">
					<br>
					<h1>Appfolio Details Customizer (PRO Feature)</h1>
					
					<form method='POST' action="">
						<table class="form-table">

							<!-- Rent Price -->
							<tr>
								<th>
									<label>Display Rent Price</label>
								</th>
								<td class="custom_option">
									
									<span><input type="checkbox" checked></span>
									
									<span class="more-options">
										Text Color:
										<input type="text" value="#ff6600" class="apfl-details-color" />
									</span>

									<span class="more-options">
										Show /mo:
										<select>
											<option selected>Yes</option>
											<option>No</option>
										</select>
									</span>

								</td>
							</tr>

							<!-- Details title -->
							<tr>
								<th>
									<label>Display Details Title</label>
								</th>
								<td class="custom_option">
									<span><input type="checkbox" checked></span>
									<span id="ttl_tag" class="more-options">
										Tag: 
										<select>
											<option value="h1">h1</option>
											<option value="h2">h2</option>
											<option value="h3" selected>h3</option>
											<option value="h4">h4</option>
											<option value="h5">h5</option>
											<option value="h6">h6</option>
											<option value="p">p</option>
										</select>
									</span>
								</td>
							</tr>

						</table>
						
						<p class="submit"><input type="submit" name="apfl_details_cstmzr_sbmt" value="Save" class="button-primary"></p>
						
					</form>
				</div>
				<!-- End of details page -->
				<?php } ?>
			</div>
	<?php
		}
	}
	
}
