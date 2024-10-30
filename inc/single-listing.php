<?php

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
   exit;
}

if (!function_exists('apfl_display_single_listing')) {
	function apfl_display_single_listing(){
		global $apfl_plugin_url;
		global $client_listings_url;
		
		if(!$client_listings_url){ return '<p>The Appfolio URL is blank. Please contact site owner.</p>'; }
		
		$schshowing_btn_link = $apply_btn_link = $contact_btn_link = '';
		
		$licensed = get_option('apfl_free_licensed');
		if(!$licensed){
			return '<p>Unable to display Listings. Please contact site owner.</p>';
		}
		
		$sl_html = '<div class="apfl-sl-wrapper" style="width: 100%; max-width: 100%;">';
		if(isset($_GET['lid'])){
			
			$place_area = $availability = $rent_price = $address = $bed_std = $baths = $ttl = $dsc = '';
			
			$list_id = sanitize_text_field($_GET['lid']); // sanitize target listing ID number
			$url = $client_listings_url.'/listings/detail/'.$list_id;
			$html = file_get_html($url);
			
			$listing_images = array();
			$i = 0;
			$main_gallery = $html->find('main .gallery', 0);
			
			if($main_gallery){
				$main_imgs = $main_gallery->find('a.swipebox');
				if($main_imgs){
					foreach($main_imgs as $main_img){
						$listing_images[$i]['href'] = $main_img->{'href'};	
						$src = $main_img->{'style'};
						if($src){
							$ini = strpos($src, '(');
							$ini += strlen('(');
							$len = strpos($src, ')', $ini) - $ini;
							$listing_images[$i]['img_url'] = substr($src, $ini, $len);
						} else{
							$img_src_obj = $main_img->find('img', 0);
							$img_src = $img_src_obj->{'src'};
							$listing_images[$i]['img_url'] = $img_src;
						}
						$i++;
					}
				}
				$extra_imgs = $html->find('div[style="display:none"] a.swipebox');
				if($extra_imgs){
					foreach($extra_imgs as $extra_img){
						$listing_images[$i]['href'] = $extra_img->{'href'};
						$ext_img_obj = $extra_img->find('img', 0);
						$listing_images[$i]['img_url'] = $ext_img_obj->{'src'};
						$i++;
					}
				}
			}
			
			$listing_details = $html->find('.listing-detail', 0);
			if($listing_details){
				$ld_body = $listing_details->find('.listing-detail__body', 0);
				if($ld_body){
					$address_obj = $ld_body->find('.header .js-show-title', 0);
					if($address_obj){
						$address = $address_obj->innertext;
					}
					$bb_obj = $ld_body->find('.header .header__summary', 0);
					if($bb_obj){
						$bed_bath_avail = $bb_obj->innertext;
						$bed_bath_avail = explode("| ", $bed_bath_avail);
						if($bed_bath_avail){
							if(strpos($bed_bath_avail[0], 'Sq.') !== false){
								$reversedParts = explode(' ,', strrev($bed_bath_avail[0]));
								$place_area = strrev($reversedParts[0]); // get last part
							}
							if(count($bed_bath_avail) > 1){
								$availability = $bed_bath_avail[1];
							}
						}
					}
					$ttl_obj = $ld_body->find('.listing-detail__title', 0);
					if($ttl_obj){
						$ttl = $ttl_obj->innertext;
					}
					$dsc_obj = $ld_body->find('.listing-detail__description', 0);
					if($dsc_obj){
						$dsc = $dsc_obj->innertext;
					}
					$extra_fields = $ld_body->find('.grid div');
				}
				
				$ld_sidebar = $listing_details->find('.sidebar', 0);
				if($ld_sidebar){
					$rent_cap = $ld_sidebar->firstChild();
					if($rent_cap){
						$rent_price_obj = $rent_cap->find('h2', 0);
						if($rent_price_obj){ $rent_price = $rent_price_obj->innertext; }
						
						$cap_bed_baths_obj = $rent_cap->find('h3', 0);
						if($cap_bed_baths_obj){
							$cap_bed_baths = $cap_bed_baths_obj->innertext;
							$cap_bed_baths = explode("/", $cap_bed_baths);
							if($cap_bed_baths){
								$bed_std = $cap_bed_baths[0];
								if(strpos($bed_std, 'bd') !== false){ $bed_std = str_replace("bd","Bed",$bed_std); }
								$baths = $cap_bed_baths[1];
								if(strpos($baths, 'ba') !== false){ $baths = str_replace("ba","Baths",$baths); }
							}
						}
					}
					$btns = $ld_sidebar->find('.foot-button', 0);
					if($btns){
						$schshowing_btn_link_obj = $btns->find('.js-schedule-showing', 0);
						if($schshowing_btn_link_obj){ $schshowing_btn_link = $schshowing_btn_link_obj->{'href'}; }
						
						$apply_btn_link_obj = $btns->find('.btn-warning', 0);
						if($apply_btn_link_obj){ $apply_btn_link = $apply_btn_link_obj->{'href'}; }
						
						$contact_btn_link_obj = $btns->find('.btn-secondary', 0);
						if($contact_btn_link_obj){ $contact_btn_link = $contact_btn_link_obj->{'href'}; }
					}
					
					$logo_link_obj = $ld_sidebar->find('.sidebar__portfolio-logo', 0);
					if($logo_link_obj){ $logo_link = $logo_link_obj->{'src'}; }
					
					$phn_ctc = '';
					$phn_ctc_obj = $ld_sidebar->find('.u-pad-bl', 0);
					if($phn_ctc_obj){
						$phn_ctc = $phn_ctc_obj->innertext;
						$phn_ctc = preg_replace('#<(a)(?:[^>]+)?>.*?</\1>#s', '', $phn_ctc);
					}
				}
			}
			
			$all_lstng_url = strtok($_SERVER["REQUEST_URI"], '?');
			if($all_lstng_url){
				$sl_html .= '<div style="margin-bottom: 2rem;"><a class="apfl-prmry-btn" href="'.$all_lstng_url.'" style="margin-left: 2%;"> << All Listings</a></div>';
			}
			
			$sl_html .='<div class="listing-sec section_wrapper mcb-section-inner"><div class="apfl-column apfl-two-fifth">';
					if($listing_images){
						$sl_html .='<div class="apfl-gallery">';
							$j = 1; foreach($listing_images as $list_img){
								$sl_html .='<div class="mySlides">
									<div class="numbertext">'.$j.' / '.count($listing_images).'</div>
									<img src="'.$list_img["img_url"].'" data-href="'.$list_img["href"].'" data-id="apfl_gal_img_'.$j.'">
								</div>';
								$j++;
							}
							$sl_html .='<a class="prev" onclick="plusSlides(-1)">&#10094;</a>
							<a class="next" onclick="plusSlides(1)">&#10095;</a>
							<div class="row" style="margin-top: 7px;">';
								$k = 1; foreach($listing_images as $list_img){
									$sl_html .='<div class="imgcolumn">
										<img class="demo cursor" src="'.$list_img["img_url"].'" onclick="currentSlide('.$k.')">
									</div>';
								$k++; }
						$sl_html .='</div></div>';
					}
				$sl_html .='</div>';
				$sl_html .='<div class="apfl-column apfl-three-fifth">';
					if($listing_details){
						$sl_html .='<div class="lst-dtls">
							<div class="details-left">
								<h3 class="address-hdng">'.$address.'</h3>
								<p class="bed-bath-std">
									<img class="bedimg" src="'.$apfl_plugin_url.'images/sleep.png"><span>'.$bed_std.'</span>
									<img class="bathimg" src="'.$apfl_plugin_url.'images/bathtub.png"><span>'.$baths.'</span>';
									if($place_area){
										$sl_html .='<span> | '.$place_area.'</span>';
									}
								$sl_html .='</p>';
							$sl_html .='</div>';
							$sl_html .='<div class="details-right">
									<p class="rent-hdng"><img class="price-tag" src="'.$apfl_plugin_url.'images/dollar-tag.png">'.$rent_price.'</p>';
									if($availability){
										$sl_html .='<p style="margin-bottom: 1rem;">';
										if(preg_replace('/\s+/', '', $availability) == 'AvailableNow'){
											$sl_html .='<img class="avail-now" src="'.$apfl_plugin_url.'images/check.png">';
										}
										$sl_html .='<span id="avail-txt">'.$availability.'</span>';
										$sl_html .='</p>';
									}
									
									$phn_nmbr = explode('<br>', $phn_ctc);
									if(count($phn_nmbr) > 1){
										$phn_nmbr = $phn_nmbr[1];
										$sl_html .='<a class="call-top" href="tel:'.$phn_nmbr.'"><img class="call-now" src="'.$apfl_plugin_url.'images/phone-call.png"><strong>'.$phn_nmbr.'</strong></a>';
									}
									
								$sl_html .='</div>
							</div>';
						$sl_html .='<p class="desctitle">'.$ttl.'</p>
							<p class="desc">'.$dsc.'</p>
							<div class="apfl-half">';
								if($extra_fields){
									$sl_html .='<div class="extra">';
									foreach($extra_fields as $field){
										$sl_html .='<div class="extra-half">';
										
										$extra_fld_obj = $field->find("h3", 0);
										if($extra_fld_obj){ $sl_html .='<h4>'.$extra_fld_obj->innertext.'</h4>'; }
										
										$extra_fld_ul_obj = $field->find("ul", 0);
										if($extra_fld_ul_obj){ $sl_html .='<ul>'.$extra_fld_ul_obj->innertext.'</ul>'; }
										
										$sl_html .='</div>';
									}
									$sl_html .='</div>';
								}
						$sl_html .='</div>';
						$sl_html .='<div class="apfl-half" style="text-align: right; margin: 25px 0;">';
						
						if($schshowing_btn_link) {
							$schshowing_btn_link = $client_listings_url.$schshowing_btn_link;
							$schshowing_btn_link = apply_filters( "apfl_schshowing_btn_link", $schshowing_btn_link, $schshowing_btn_link );
							$sl_html .='<a id="schshowingBtn" class="sl-btns" target="_blank" href="'.$schshowing_btn_link.'">Schedule Showing</a>';
						}
						
						if($apply_btn_link) {
							$apply_btn_link = $client_listings_url.$apply_btn_link;
							$apply_btn_link = apply_filters( "apfl_apply_btn_link", $apply_btn_link, $apply_btn_link );
							$sl_html .='<a id="applyBtn" class="sl-btns" target="_blank" href="'.$apply_btn_link.'">Apply Now</a>';
						}
						
						if($contact_btn_link) {
							$contact_btn_link = $client_listings_url.$contact_btn_link;
							$contact_btn_link = apply_filters( "apfl_contact_btn_link", $contact_btn_link, $contact_btn_link );
							$sl_html .='<a id="contactBtn" class="sl-btns" target="_blank" href="'.$contact_btn_link.'">Contact Us</a>';
						}
						
						$sl_html .='</br><br><p>'.$phn_ctc.'</p></div>';
					}
				$sl_html .='</div></div>';

			if($main_gallery){
				$sl_html .='<script>
					var slideIndex = 1;
					showSlides(slideIndex);
					// Next/previous controls
					function plusSlides(n) {
					  showSlides(slideIndex += n);
					}
					// Thumbnail image controls
					function currentSlide(n) {
					  showSlides(slideIndex = n);
					}
					function showSlides(n) {
					  var i;
					  var slides = document.getElementsByClassName("mySlides");
					  var dots = document.getElementsByClassName("demo");
					  if (n > slides.length) {slideIndex = 1}
					  if (n < 1) {slideIndex = slides.length}
					  for (i = 0; i < slides.length; i++) {
						slides[i].style.display = "none";
					  }
					  for (i = 0; i < dots.length; i++) {
						dots[i].className = dots[i].className.replace(" active", "");
					  }
					  slides[slideIndex-1].style.display = "block";
					  dots[slideIndex-1].className += " active";
					  
					  if(dots.length > 5){
						  for (i = 0; i < dots.length; i++) {
							dots[i].style.display = "none";
						  }
						  if(slideIndex > 2 && slideIndex < dots.length-1){
							for(i=0; i<5; i++){
								dots[slideIndex-3+i].style.display = "block";
							}
						  } else if(slideIndex < 3){
							for(i=0; i<5; i++){
								dots[i].style.display = "block";
							}
						  } else if(slideIndex > dots.length-2){
							 for(i=dots.length-1; i>dots.length-6; i--){
								dots[i].style.display = "block";
							}
						  }
					  }
					}
				</script>';
			}
			
		}
		
	$sl_html .='</div>';

	return $sl_html;

	}
}
