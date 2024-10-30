=== Listings for Appfolio ===
Contributors: deepakkite, mrking2201
Tags: appfolio, property listings, listings, appfolio integration
Requires at least: 5.3
Tested up to: 6.6.2
Stable tag: 1.1.8
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin gets your Appfolio property listings and display them in an interactive way rather than using iframe and gives you styling freedom.

== Description ==

“Listings for Appfolio” is a light-weight property listing plugin that allows you to list your properties on your WordPress site from your Appfolio account using a shortcode.

The old iframe method of displaying appfolio listings does not allow site owners to customize the styling of the listings. This plugin will give you freedom to make any changes to the listings page.
All your appfolio listings will be shown in an interactive way which you can customize using CSS easily. Listings page have an optional google map as well. You will also get all the filters from your appfolio listings page.

[Free version Demo](https://eagle.listingsforappfolio.com)

[PRO version Demo](https://hawk.listingsforappfolio.com) (Comes with Customizer)

Shortcode: **[apfl_listings]**
where you want to show the listings. A full-width page is recommended for better styling.

Notice: This plugin depends on "allow_url_fopen" to get the content from appfolio listings page. Please add "allow_url_fopen = 1" in your php.ini file or contact your hosting to enable it for you.

You will need to enter your appfolio account page URL in the plugin settings that looks like this - https://example.appfolio.com
Optionally, enter google map JS API key in the plugin settings to enable the google map showing your listings.

If you have any feedback or new feature request, please let me know by creating a support ticket and I will add/improve it as soon as possible.

Check our other plugin - [Listings for Buildium](https://wordpress.org/plugins/listings-for-buildium/)

== Installation ==

You can install the Plugin in two ways.

= WordPress interface installation =

1. Go to plugins in the WordPress admin and click on “Add new”.
2. In the Search box enter “Listings for Appfolio” and press Enter.
3. Click on “Install” to install the plugin.
4. Activate the plugin.

= Manual installation =

1. Download and upload the plugin files to the /wp-content/plugins/listings-for-appfolio directory from the WordPress plugin repository.
2. Activate the plugin through the "Plugins" screen in WordPress admin area.

== Frequently Asked Questions ==

= How do I configure the plugin? =

Once the plugin is activated, you will find the "Listings for Appfolio" link under the Settings menu in WordPress admin area. Enter your appfolio listing page URL that should look like this - https://example.appfolio.com

= How do I enable/disable google map on listings page? =

You will need to create a google maps JS API key from your google account. Then enter the API key under the plugin settings. Leave it emtpy if you want to disable the map. 

= What is the shortcode to display the listings? =

Use "[apfl_listings]" shortcode where you want to show the listings with filters and map.

= Where is the single listing page? =

You don't need to create a separate page for showing single property listing. The plugin uses the same page where you put "apfl_listings" shortcode.

= Does the plugin use pagination for listings? =

No, the plugin does not support the pagination feature for now.

== Screenshots ==
1. Appfolio listings filters and map
2. Interactive appfolio properties listings
3. Settings for Appfolio Listings
4. Backend Customization Options

== Features ==

* Easy plugin setup for appfolio listings.

* iframe alternative method which allows you to do any customization on the layout and styling.

* SEO improvement.

* No need to manually add listing items.

* No need to manually sync listings with Appfolio account.

* Filters to search for specific properties listings.

* Google Map showing the listings that updates with the filtered results.

* Interactive 3 column design for listings.

* Single property listing opens in the same page.

* Easy to use shortcode.

* Interactive gallery to list the property images.

* Schedule a Showing button added.

[PRO version Features](https://listingsforappfolio.com/)

* Customization options in WordPress backend.

* Support for multiple Appfolio accounts to load listings from.

* Support for loading listings from different groups. E.g Commercial | Residential.

* Separate page/layout for Rental details page.

* Slider and Carousel.

* Video support in the Gallery.

* Option to change default sort order. **(NEW)**

* Option to add Search-By-Address in filters box. **(NEW)**

* Display Residential, Commercial or all listings on different pages.

* Hide/Show filters, buttons, Price, Availability, Title, Address.

* Replaceable icons for bedroom and bathroom labels. 

* 1 column and 2 columns design options.

* Gallery popup/lightbox showing full size images.

* Options to toggle different search filters.

* Option to use custom link for Apply buttons. **(NEW)**

* Option to add a Page heading.

== Changelog ==

= 1.1.8 =
* 2024-10-07
* Optimized listings.
* Tested with WP version 6.6.2.

= 1.1.7 =
* 2024-03-28
* Added new options.
* Tested with WP version 6.5.

= 1.1.6 =
* 2023-10-18
* Fixed filter box design conflict with some themes.
* Added backend options to showcase features.
* Fixed layout issues by adding listings and details page wrapper in flex.
* Tested with WP 6.3.2.

= 1.1.5 =
* 2023-09-19
* Added Available date on listings page.
* Updated required versions of php and wordpress.
* Tested with latest WP version 6.3.1.

= 1.1.4 =
* 2023-08-06
* Fixed empty label for dogs and cats filter.

= 1.1.3 =
* 2022-07-14
* Fixed warning for missing telephone.

= 1.1.2 =
* 2022-01-16
* Fixed warning for empty bed/bath array keys.

= 1.1.1 =
* 2021-07-13
* Fixed a known bug where baths display incorrectly when studio is shown.

= 1.1.0 =
* 2021-04-07
* Fixed the reported bugs.
* Changed the listings fetching method to get the whole page.

= 1.0.9 =
* 2021-04-06
* Fixed a bug for missing detail button on remote listings page.

= 1.0.8 =
* 2021-03-31
* Added shortcode and banner on plugin's settings page.

= 1.0.7 =
* 2021-03-27
* Fixed a known bug for existing simple_html_dom class.
* Updated gallery image css to use max-height instead of height to maintain responsiveness.

= 1.0.6 =
* 2021-03-24
* Added Schedule a Showing button on single listings.

== Upgrade Notice ==
