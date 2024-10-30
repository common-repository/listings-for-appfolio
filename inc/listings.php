<?php

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
	exit;
}

if (!function_exists('apfl_display_all_listings')) {
	function apfl_display_all_listings($atts){
		
		if( !ini_get('allow_url_fopen') ) {
			return '<p>Please enable "allow_url_fopen" from server to make the plugin work correctly.</p>';
		}
		
		$render_html = '';
		if(isset($_GET['lid'])){
			$render_html = apfl_display_single_listing();
			return $render_html;
		}
		else{
			global $apfl_plugin_url;
			global $client_listings_url;
			global $client_gmap_api;
			
			if(!$client_listings_url){ return '<p>The Appfolio URL is blank. Please contact site owner.</p>'; }
			
			$licensed = get_option('apfl_free_licensed');
			
			if(!$licensed){
				return '<p>Unable to display Listings. Please contact site owner.</p>';
			}
			
			$render_html .= '<div class="main-listings-page" style="width: 100%; max-width: 100%;">';
			
			$url = $client_listings_url.'/listings';
			
			if(isset($_POST['fltr-submt'])){
				$set = 0;
				$params = '';
				$params_before = '';
				if(isset($_POST['filters'])){
					foreach($_POST['filters'] as $fltr_key=>$fltr_val){
						$fltr_key = sanitize_text_field($fltr_key);
						if($fltr_key == 'cities'){
							if($fltr_val){
								$set = 1;
								foreach($fltr_val as $val){
									$val = sanitize_text_field($val);
									$params .= '&filters[' . $fltr_key . '][]=' . urlencode($val);
								}
							}
						}
						else{
							$fltr_val = sanitize_text_field($fltr_val);
							if($fltr_val){
								$set = 1;
								$params .= '&filters[' . $fltr_key . ']=' . urlencode($fltr_val);
							}
						}
					}
				}
				if($set){
					$params_before = '?';
				}
				
				$url = $client_listings_url.'/listings'.$params_before.$params;		
				
			}
			$html = new simple_html_dom();
			$html->load_file($url);
			$listings = array();
			$db = array();
			
			$listing_page_hdng = apply_filters( "apfl_page_hdng", '' );
			
			$render_html .= '<div class="listing-filters"><div class="apfl_page_hdng">' . $listing_page_hdng . '</div>';
			$listing_filters = $html->find('.filter-menu', 0);
			if($listing_filters){
				$rent_min = $listing_filters->find('#filters_market_rent_from', 0);
				$rent_max = $listing_filters->find('#filters_market_rent_to', 0);
				$filters_bedrooms = $listing_filters->find('#filters_bedrooms', 0);
				$filters_bathrooms = $listing_filters->find('#filters_bathrooms', 0);
				$filters_cities = $listing_filters->find('#filters_cities', 0);
				$filters_cats = $listing_filters->find('#filters_cats', 0);
				$filters_dogs = $listing_filters->find('#filters_dogs', 0);
			}
			$render_html .= '<form method="post">';
			
			// Filters
			if($rent_min){
				if(isset($_POST['orig_min_rent'])){
					$correct_min_rent = stripslashes($_POST['orig_min_rent']);
					$render_html .= '<input type="hidden" value=\'' . htmlentities( $correct_min_rent ) . '\' name="orig_min_rent">';
					$correct_min_rent = str_replace('No Min.','Min Rent',$correct_min_rent);
					$selected = sanitize_text_field($_POST['filters']['market_rent_from']);
					$str_to_replace = 'value="'.$selected.'"';
					$str_to_replace_by = 'value="'.$selected.'" selected="selected"';
					$render_html .= '<select name="filters[market_rent_from]">'.str_replace($str_to_replace,$str_to_replace_by,$correct_min_rent).'</select>';
				} else{
					$correct_min_rent = stripslashes($rent_min->innertext);
					$correct_min_rent = str_replace('No Min.','Min Rent',$correct_min_rent);
					$render_html .= '<select name="filters[market_rent_from]">'.$correct_min_rent.'</select><input type="hidden" value=\'' . htmlentities( $correct_min_rent ) . '\' name="orig_min_rent">';
				}
			}
			
			if($rent_max){
				if(isset($_POST['orig_max_rent'])){
					$correct_max_rent = stripslashes($_POST['orig_max_rent']);
					$render_html .= '<input type="hidden" value=\'' . htmlentities( $correct_max_rent ) . '\' name="orig_max_rent">';
					$correct_max_rent = str_replace("No Max.","Max Rent",$correct_max_rent);
					$selected = sanitize_text_field($_POST['filters']['market_rent_to']);
					$str_to_replace = 'value="'.$selected.'"';
					$str_to_replace_by = 'value="'.$selected.'" selected="selected"';
					$render_html .= '<select name="filters[market_rent_to]">'.str_replace($str_to_replace,$str_to_replace_by,$correct_max_rent).'</select>';
				} else{
					$correct_max_rent = stripslashes($rent_max->innertext);
					$correct_max_rent = str_replace("No Max.","Max Rent",$correct_max_rent);
					$render_html .= '<select name="filters[market_rent_to]">'.$correct_max_rent.'</select><input type="hidden" value=\'' . htmlentities( $correct_max_rent ) . '\' name="orig_max_rent">';
				}
			}
			
			if($filters_bedrooms){
				if(isset($_POST['orig_beds'])){
					$correct_beds = str_replace("0+", "Beds", stripslashes($_POST['orig_beds']));
					$render_html .= '<input type="hidden" value=\'' . htmlentities( $correct_beds ) . '\' name="orig_beds">';
					$selected = sanitize_text_field($_POST['filters']['bedrooms']);
					$str_to_replace = 'value="'.$selected.'"';
					$str_to_replace_by = 'value="'.$selected.'" selected="selected"';
					$render_html .= '<select name="filters[bedrooms]">'.str_replace($str_to_replace,$str_to_replace_by,$correct_beds).'</select>';
				} else{
					$correct_beds = str_replace("0+", "Beds", stripslashes($filters_bedrooms->innertext));
					$render_html .= '<select name="filters[bedrooms]">'.$correct_beds.'</select><input type="hidden" value=\'' . htmlentities( $correct_beds ) . '\' name="orig_beds">';
				}
			}
			
			if($filters_bathrooms){
				if(isset($_POST['orig_baths'])){
					$correct_baths = str_replace("0+", "Baths", stripslashes($_POST['orig_baths']));
					$render_html .= '<input type="hidden" value=\'' . htmlentities( $correct_baths ) . '\' name="orig_baths">';
					$selected = sanitize_text_field($_POST['filters']['bathrooms']);
					$str_to_replace = 'value="'.$selected.'"';
					$str_to_replace_by = 'value="'.$selected.'" selected="selected"';
					$render_html .= '<select name="filters[bathrooms]">'.str_replace($str_to_replace,$str_to_replace_by,$correct_baths).'</select>';
				} else{
					$correct_baths = str_replace("0+", "Baths", stripslashes($filters_bathrooms->innertext));
					$render_html .= '<select name="filters[bathrooms]">'.$correct_baths.'</select><input type="hidden" value=\'' . htmlentities( $correct_baths ) . '\' name="orig_baths">';
				}
			}
			
			if($filters_cities){
				if(isset($_POST['orig_cities'])){
					$correct_cities = stripslashes($_POST['orig_cities']);
					$render_html .= '<input type="hidden" value=\'' . htmlentities( $correct_cities ) . '\' name="orig_cities">';
					$selected = sanitize_text_field($_POST['filters']['cities'][0]);
					$str_to_replace = 'value="'.$selected.'"';
					$str_to_replace_by = 'value="'.$selected.'" selected="selected"';
					$render_html .= '<select name="filters[cities][]">'.str_replace($str_to_replace,$str_to_replace_by,$correct_cities).'</select>';
				} else{
					$correct_cities = stripslashes($filters_cities->innertext);
					$render_html .= '<select name="filters[cities][]">'.$correct_cities.'</select><input type="hidden" value=\'' . htmlentities( $correct_cities ) . '\' name="orig_cities">';
				}
			}
			
			if($filters_cats){
				if(isset($_POST['orig_cats'])){
					$correct_cats = preg_replace('/<option[^>]*>.*?<\/option>/s', '<option value="">Cats Allowed</option>', stripslashes($_POST['orig_cats']), 1);
					$render_html .= '<input type="hidden" value=\'' . htmlentities( $correct_cats ) . '\' name="orig_cats">';
					$selected = sanitize_text_field($_POST['filters']['cats']);
					$str_to_replace = 'value="'.$selected.'"';
					$str_to_replace_by = 'value="'.$selected.'" selected="selected"';
					$render_html .= '<select name="filters[cats]">'.str_replace($str_to_replace,$str_to_replace_by,$correct_cats).'</select>';
				} else{
					$correct_cats = preg_replace('/<option[^>]*>.*?<\/option>/s','<option value="">Cats Allowed</option>', stripslashes($filters_cats->innertext), 1);
					$render_html .= '<select name="filters[cats]">'.$correct_cats.'</select><input type="hidden" value=\'' . htmlentities( $correct_cats ) . '\' name="orig_cats">';
				}
			}
			
			if($filters_dogs){
				if(isset($_POST['orig_dogs'])){
					$correct_dogs = preg_replace('/<option[^>]*>.*?<\/option>/s', '<option value="">Dogs Allowed</option>', stripslashes($_POST['orig_dogs']), 1);
					$render_html .= '<input type="hidden" value=\'' . htmlentities( $correct_dogs ) . '\' name="orig_dogs">';
					$selected = sanitize_text_field($_POST['filters']['dogs']);
					$str_to_replace = 'value="'.$selected.'"';
					$str_to_replace_by = 'value="'.$selected.'" selected="selected"';
					$render_html .= '<select name="filters[dogs]">'.str_replace($str_to_replace,$str_to_replace_by,$correct_dogs).'</select>';
				} else{
					$correct_dogs = preg_replace('/<option[^>]*>.*?<\/option>/s','<option value="">Dogs Allowed</option>', stripslashes($filters_dogs->innertext), 1);
					$render_html .= '<select name="filters[dogs]">'.$correct_dogs.'</select><input type="hidden" value=\'' . htmlentities( $correct_dogs ) . '\' name="orig_dogs">';
				}
			}
			
			$render_html .= '<input type="submit" value="SEARCH" name="fltr-submt">';
			
			$render_html .= '</form></div>';
			
			// Google map for listings
			if($client_gmap_api){
				$render_html .= '<div id="googlemap"></div>';
			}
			
			// All listings in columns
			$render_html .= '<div class="all-listings section_wrapper mcb-section-inner">';
			$listing_items = $html->find('#result_container .listing-item');
			if($listing_items){
				foreach ($listing_items as $listing) {
					$db['bed'] = 'N/A';
					$db['bath'] = 'N/A';
					$listingItemBody = $listing->find('.listing-item__body', 0);
					$listingItemAction = $listing->find('.listing-item__actions', 0);
					$listing_Img_obj = $listing->find('img.listing-item__image', 0);
					if($listing_Img_obj){
						$listing_Img = $listing_Img_obj->{'data-original'};
					}
					if($listingItemBody){
						foreach($listingItemBody->find('.detail-box__item') as $db_itm){
							$label_obj = $db_itm->find('.detail-box__label', 0);
							if($label_obj){ $label = $label_obj->innertext; }
							$val_obj = $db_itm->find('.detail-box__value', 0);
							if($val_obj){ $val = $val_obj->innertext; }
							if($label == 'Bed / Bath'){
								$db['bed'] = '';
								$db['bath'] = '';
								if(strpos($val, 'bd') !== false){ $beds = explode(' bd / ', $val); $db['bed'] = $beds[0] . ' Beds'; }
								if(strpos($val, 'Studio') !== false){ $beds = explode('Studio / ', $val); $db['bed'] = 'Studio'; }
								if(strpos($val, 'ba') !== false){ $baths = explode(' ba', $beds[1]); $db['bath'] = $baths[0]; }
							} else{
								$db[$label] = $val;
							}
						}
						$listing_title_obj = $listingItemBody->find('.js-listing-title a', 0);
						if($listing_title_obj){ $listing_title = $listing_title_obj->plaintext; }
						
						$listing_Address_obj = $listingItemBody->find('.js-listing-address', 0);
						if($listing_Address_obj){ $listing_Address = $listing_Address_obj->plaintext; }
						
						$listing_Description_obj = $listingItemBody->find('.js-listing-description', 0);
						if($listing_Description_obj){ $listing_Description = $listing_Description_obj->plaintext; }
						
						$listing_Pet_policy_obj = $listingItemBody->find('.js-listing-pet-policy', 0);
						if($listing_Pet_policy_obj){ $listing_Pet_policy = $listing_Pet_policy_obj->plaintext; }
					}
					$listing_ID = '';
					$listing_Apply_Link = '';
					if($listingItemAction){
						$listing_Details_Link = $listingItemAction->find('.js-link-to-detail', 0)->href;
						$listing_ID = basename($listing_Details_Link);
						$listing_Apply_Link = $listingItemAction->find('.js-listing-apply', 0);
						if($listing_Apply_Link){
							$listing_Apply_Link = $listing_Apply_Link->href;
							$listing_Apply_Link = $client_listings_url . $listing_Apply_Link;
						}
					}

					$listing_Apply_Link = apply_filters( "apfl_apply_btn_link", $listing_Apply_Link, $listing_Apply_Link );
					
					$render_html .= '<div class="listing-item column mcb-column one-third">
						<a href="?lid='.$listing_ID.'">
						<div class="list-img">
							<img src="'.$listing_Img.'">
							<span class="rent-price">'.$db["RENT"].'</span>
						</div></a>
						<div class="details">
							<h3 class="address">'.$listing_Address.'</h3>
							<span class="lstng-avail">Available '.$db["Available"].'</span> 
							<p><img class="bedimg" src="'.$apfl_plugin_url.'images/sleep.png"><span class="beds">'.$db["bed"].'</span> <img class="bathimg" src="'.$apfl_plugin_url.'images/bathtub.png"><span class="baths">'.$db["bath"].' Baths</span></p>
							<div class="btns">
								<a href="?lid='.$listing_ID.'">Details</a>
								<a href="'.$listing_Apply_Link.'" target="_blank">Apply</a>
							</div>
						</div>
					</div>';
				}
				
			} else{
				$render_html .= '<div class="no-listings"><p>No vacancies found matching your search criteria. Please select other filters.</p></div>';
			}
			$render_html .= '</div></div>';

			// Loading Map
			if($client_gmap_api){
				$lat_longs = '';
				$markers_obj = $html->find('script', -2);
				if($markers_obj){
					$markers = $markers_obj->innertext;
				}
				$markers = explode('markers:', $markers);
				if(is_array($markers) && array_key_exists(1, $markers)){
					$markers = explode('infoWindowTemplate', $markers[1]);
					$lat_longs = json_decode(str_replace('],',']',$markers[0]), true);
				}
				
				if($lat_longs){
					$init_lat = $lat_longs[0]["latitude"];
					$init_lng = $lat_longs[0]["longitude"];
					
					$render_html .= '<script type="text/javascript">
						function initMap(){
							
							const initCity = {lat: '.$init_lat.', lng: '.$init_lng.'};
							
							//var springfield = {lat: 42.08717, lng: -72.561889};
							var map = new google.maps.Map(
								document.getElementById("googlemap"), {zoom: 12, center: initCity}
							);';
							
							
							$i = 1;
							foreach($lat_longs as $pos){
								$lstng_id = '';
								$lid_url = $pos['detail_page_url'];
								$lid_url_arr = explode('/', $lid_url);
								if($lid_url_arr){
									$lstng_id = end($lid_url_arr);
								}
								
								$render_html .= "var infowindow_".$i." = new google.maps.InfoWindow({
										content: '<div class=\"mm-prop-popup\">'+
											'<div class=\"map-popup-thumbnail\"><a href=\"?lid=".$lstng_id."\" target=\"_blank\"><img src=\"".$pos['default_photo_url']."\" width=\"144\"></a></div>'+
											'<div class=\"map-popup-info\">'+
												'<h3 class=\"map-popup-rent\">".$pos['market_rent']."</h3>'+
												'<p class=\"map-popup-specs\">".$pos['unit_specs']."</p>'+
												'<p class=\"map-popup-address\">".$pos['address']."</p>'+
												'<p><a href=\"?lid=".$lstng_id."\" target=\"_blank\" class=\"btn btn-secondary btn-sm pt-1 pb-1\">Details</a>'+
													'<a href=\"https://maps.google.com/maps?daddr=".$pos['address']."\" target=\"_blank\" class=\"btn btn-secondary btn-sm pt-1 pb-1 directions-link\">Directions</a>'+
												'</p></div></div>'
									});";
								$render_html .= 'marker_'.$i.' = new google.maps.Marker({
										map: map,
										position: new google.maps.LatLng('.$pos["latitude"].', '.$pos["longitude"].')
									});
									marker_'.$i.'.addListener("click", function() {
										infowindow_'.$i.'.open(map, marker_'.$i.');
									});';
								$i++;
							}
							
					$render_html .= '} </script>';
					$render_html .= '<script type="text/javascript" async defer charset="utf-8" src="https://maps.googleapis.com/maps/api/js?key='.$client_gmap_api.'&callback=initMap"></script>';
				}
			}
			
			return $render_html;
		
		}
		
	}
}
