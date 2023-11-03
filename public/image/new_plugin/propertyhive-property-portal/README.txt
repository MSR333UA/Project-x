=== PropertyHive Property Portal ===
Contributors: PropertyHive,BIOSTALL
Tags: property portal, portal, property hive, propertyhive, estate agent, estate agency, rightmove, zoopla, onthemarket, property website
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=N68UHATHAEDLN&lc=GB&item_name=BIOSTALL&no_note=0&cn=Add%20special%20instructions%20to%20the%20seller%3a&no_shipping=1&currency_code=GBP&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Requires at least: 3.8
Tested up to: 5.7
Stable tag: trunk
Version: 1.0.9
Homepage: http://wp-property-hive.com/addons/property-portal/

This add on for Property Hive allows you to turn your site into a property portal by assigning and showing properties for multiple agents

== Description ==

This add on for Property Hive allows you to turn your site into a property portal by assigning and showing properties for multiple agents

== Installation ==

= Manual installation =

The manual installation method involves downloading the Property Hive Property Portal plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

Once installed and activated, you can access the agents by navigating to 'PropertyHive > Agents' from within Wordpress and assigning properties to them from within the property edit screen.

= Updating =

Updating should work like a charm; as always though, ensure you backup your site just in case.

== Changelog ==

= 1.0.9 =
* Remove occurrences where agents would be queried and then branches queried for each agent. Very inefficient and causing sites with lots of agents to hang. Instead we now index the branches when an agent is saved, plus weekly cron that updates the index to ensure it's all correct

= 1.0.8 =
* If a user is deleted that's linked to an agent then remove the relationship so that an agent login can be created again
* Correct undefined variable warning on admin property list
* Declared support for WordPress 5.7

= 1.0.7 =
* Added new option to agent dropdown filter on backend property list to filter properties by those not assigned to an agent/branch
* Declared support for WordPress 5.6.1

= 1.0.6 =
* Added ability to create a user login for an agent and for them to login. Useful if wanting to combine with our frontend proeprty submissions add on and allow agents to add properties themselves
* Declared support for WordPress 5.5.3

= 1.0.5 =
* Ensure main $post variable isn't lost after looping through agents in property meta box
* Declared support for WordPress 5.4.1

= 1.0.4 =
* Ensure agent edit screen is seen as main Hive screen so relevant JS is included
* Catered for main Property Hive plugin not being active
* Declared support for WordPress 5.2.4

= 1.0.3 =
* Added new 'Agent Directory' setting and accompanying overwritable archive-agent.php template
* Added ability to store branch contact details for commercial if commercial department is active
* Added ability to filter property search results by agent ID or branch ID
* Added 'Agent/Branch' filter and column to property list in WordPress admin
* Removed 'Month' filter from agents list in WordPress admin
* Declared support for WordPress 4.7.2

= 1.0.2 =
* Check for valid [license key](https://wp-property-hive.com/product/12-month-license-key/) before performing future updates
* Declared support for WordPress 4.7.1

= 1.0.1 =
* Three new helper classes: PH_Agent, PH_Agent_Branch and PH_Property_Agent_Branch
* Increase width of logo shown on main Agents admin screen

= 1.0.0 =
* First working release of the add on