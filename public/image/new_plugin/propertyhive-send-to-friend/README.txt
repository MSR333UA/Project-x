=== PropertyHive Send To Friend ===
Contributors: PropertyHive,BIOSTALL
Tags: propertyhive, property hive, property, real estate, software, estate agents, estate agent, property management
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=N68UHATHAEDLN&lc=GB&item_name=BIOSTALL&no_note=0&cn=Add%20special%20instructions%20to%20the%20seller%3a&no_shipping=1&currency_code=GBP&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Requires at least: 3.8
Tested up to: 5.8.1
Stable tag: trunk
Version: 1.0.7
Homepage: http://wp-property-hive.com/addons/send-to-friend/

This add on for Property Hive adds the ability for your users to email properties to others

== Description ==

This add on for Property Hive adds the ability for your users to email properties to others.

Once installed and activated you will automatically get a 'Send To Friend' button added to the property actions of property pages. The following shortcodes are available:

[send_to_friend_form] - Display the form allowing them to enter their friends details

== Installation ==

= Manual installation =

The manual installation method involves downloading the Property Hive Send To Friend Add-on plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Updating should work like a charm; as always though, ensure you backup your site just in case.

== Changelog ==

= 1.0.7 =
* Added support for reCAPTCHA v3
* Added support for hCaptcha
* Declared support for WordPress 5.8.1

= 1.0.6 =
* Added JS triggers ph:success, ph:validation and ph:nosend so custom JavaScript can be executed in these instances
* Declared support for WordPress 5.7

= 1.0.5 =
* Added ability for 'redirect_url' to be passed to the JavaScript through new 'propertyhive_send_to_friend_script_params' filter. This means you can redirect to another page upon successful submission of the form
* Declared support for WordPress 5.3

= 1.0.4 =
* Added ability to add ReCAPTCHA to form
* Declared support for WordPress 5.2.2

= 1.0.3 =
* Don't spoof 'From' email address header
* Declared support for WordPress 4.9.8

= 1.0.2 =
* Updated action button to support new lightbox plugin
* Declared support for WordPress 4.9.7

= 1.0.1 =
* Corrected filter names used to modify email body, subject and headers
* Corrected the email address field defaulting incorrectly to the logged in users display name
* Declared support for WordPress 4.9.5

= 1.0.0 =
* First working release of the add on