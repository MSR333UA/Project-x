=== PropertyHive Shortlist ===
Contributors: PropertyHive,BIOSTALL
Tags: propertyhive, property hive, property, real estate, software, estate agents, estate agent, property management, shortlist, basket
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=N68UHATHAEDLN&lc=GB&item_name=BIOSTALL&no_note=0&cn=Add%20special%20instructions%20to%20the%20seller%3a&no_shipping=1&currency_code=GBP&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Requires at least: 3.8
Tested up to: 5.9.2
Stable tag: trunk
Version: 1.0.15
Homepage: http://wp-property-hive.com/addons/property-shortlist/

This add on for Property Hive adds the ability for your users to save properties to a shortlist

== Description ==

This add on for Property Hive adds the ability for your users to save properties to a shortlist, then view their shortlisted properties at a later date.

Once installed and activated you will automatically get an 'Add To Shortlist' button added to the property actions of property pages. The following shortcodes are available:

[shortlist_button] - Display the 'Add To Shortlist' / 'Remove From Shortlist' button
[shortlisted_properties] - Display a list of the properties previously shortcoded.

Shortlisted properties are stored in a cookie that has a 7 day expiry.

== Installation ==

= Special Requirements =

If wanting to output the details in PDF format you will need the wkhtmltopdf package installed on the server. We recommend you speak to your server providers about getting this setup. Note that on shared servers this might not always be possible.

= Manual installation =

The manual installation method involves downloading the Property Hive Shortlist Add-on plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Updating should work like a charm; as always though, ensure you backup your site just in case.

== Changelog ==

= 1.0.15 =
* Pass in property ID to custom JS events triggered on add/remove
* Ensure shortlisted page works when SEO friendly URLs being used
* Declared support for WordPress 5.9.2

= 1.0.14 =
* Make AJAX request to ensure shortlist button is correct to get around issue with cache plugins showing a property as already shortlisted, and vice versa
* Prevent caching on page used to display shortlisted properties
* Declared support for WordPress 5.8.2

= 1.0.13 =
* Added new 'propertyhive_shortlist_my_account_shortlisted_properties' to customise properties returned
* Display Off Market properties in red in My Account shortlisted properties
* Declared support for WordPress 5.8.1

= 1.0.12 =
* Added new 'no_results_output' attribute to [shortlisted_properties] shortcode to display message when no properties shortlisted
* Declared support for WordPress 5.5.1

= 1.0.11 =
* Prevent two shortlist requests from ever being made at the same time
* Make new 'Loading' text translatable

= 1.0.10 =
* Don't set href of button to 'add-shortlist'. Bad practice. Instead use data-add-to-shortlist attribute as identifier
* Declared support for WordPress 5.4.2

= 1.0.9 =
* Set button/link text to 'Loading...' as AJAX request is being made to give indication something is happening

= 1.0.8 =
* Changed button click listener to live event so works on buttons loaded via AJAX
* Declared support for WordPress 5.4.1

= 1.0.7 =
* Added rel="nofollow" to Add To Shortlist link
* Improved response provided when adding/removing properties so it includes number of properties in shortlist
* Changed get_shortlisted_properties() method to public so it can be access elsewhere
* Added 'propertyhive-shortlist-enquiry-button' class to 'Enquire All' button
* Trigger custom events now when properties are added or removed
* Declared support for WordPress 5.2.2

= 1.0.6 =
* Added ability to utilise existing archive layout to show shortlisted properties by passing '?shortlisted=1' in query string. This will show shortlisted properties, remove search form and change page title.
* Added new 'Enquire About All Shortlisted Properties' button when archive being used to displayt shortlisted properties. This will open existing enquiry form and send one enquiry for all properties
* Declared support for WordPress 5.0.3

= 1.0.5 =
* If user logs in or registers, save properties to their account as opposed to a cookie for users not logged in
* Display shortlisted properties as new section/tab within users account area
* Log note on contact record in WordPress everytime a property is added or removed from shortlisted.
* Declared support for WordPress 4.9.8

= 1.0.4 =
* Cater for when multiple shortlist buttons exist on the full details page, ensuring text changes on all of them when shortlist status changed

= 1.0.3 =
* Tweak so shortlist button can be added and works within posts loop
* Declared support for WordPress 4.9.1

= 1.0.2 =
* Check for valid [license key](https://wp-property-hive.com/product/12-month-license-key/) before performing future updates
* Declared support for WordPress 4.7.1

= 1.0.1 =
* Tweaked order of cookie being saved vs output being made to prevent 'Headers already sent' issue
* Declared support for WordPress 4.7

= 1.0.0 =
* First working release of the add on