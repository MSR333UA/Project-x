=== PropertyHive Calendar ===
Contributors: PropertyHive,BIOSTALL
Tags: blm, propertyhive, property hive, calendar
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=N68UHATHAEDLN&lc=GB&item_name=BIOSTALL&no_note=0&cn=Add%20special%20instructions%20to%20the%20seller%3a&no_shipping=1&currency_code=GBP&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Requires at least: 3.8
Tested up to: 5.8.2
Stable tag: trunk
Version: 1.0.21
Homepage: http://wp-property-hive.com/addons/calendar/

This add on for Property Hive offers a calendar showing viewings and other time based events.

== Description ==

This add on for Property Hive offers a calendar showing viewings and other time based events.

== Installation ==

= Manual installation =

The manual installation method involves downloading the Property Hive Calendar Add-on plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Updating should work like a charm; as always though, ensure you backup your site just in case.

== Changelog ==

= 1.0.21 =
* Improved display of involved parties contact details in popup shown on hover
* Added support for new 'No Show' viewing status
* Use HTML date field instead of jQuery datepicker when adding/editing appointments
* Declared support for WordPress 5.8.2

= 1.0.20 =
* Correct position of popup dialogue that appears when clicking to add event
* Hovering over an event now shows more details
* Declared support for WordPress 5.8.1

= 1.0.19 =
* Change naming convention of main function to match other add ons and so new Property Hive-Only Mode works
* Declared support for WordPress 5.7.2

= 1.0.18 =
* Correct issue with negotiator filter not working for appointments

= 1.0.17 =
* Optimisations to loading of events: Remove ordering, reduce number of meta queries, remove unnecessary joins to recurrence table, more intelligent obtaining of appointments, and only get appointment meta data when needed
* Declared support for WordPress 5.7

= 1.0.16 =
* Added ability to assign general appointments to a property or contact
* First pass at new 'Schedule' view with print option
* Corrected issue with ampersand encoding
* Declared support for WordPress 5.6.2

= 1.0.15 =
* Support for jQuery changes in WordPress 5.6
* Show all applicant names if multiple applicants assigned to a viewing
* Declared support for WordPress 5.5.3

= 1.0.14 =
* Call save_post hooks when even is dragged or resized
* Declared support for WordPress 5.5

= 1.0.13 =
* Corrected use of 'continue' inside a switch when doing recurrence

= 1.0.12 =
* Hide users of role 'Subscriber' from negotiator related lists
* Declared support for WordPress 5.4.2

= 1.0.11 =
* Set reduced width on resource column
* Ensure appointments are pulled into email schedule add on

= 1.0.10 =
* Various corrections regarding recent recurrence and timeline changes

= 1.0.9 =
* Added new 'timeline' view listing each staff members events
* Added new filter so third party plugins can load events into calendar
* Removed tasks when loading events as should be loaded by tasks add on using filter mentioned above
* Changed default scroll time to 7am instead of 6am
* Improved height calculations and resizing

= 1.0.8 =
* Added basic recurrence to appointments (daily, weekly, monthly and yearly)
* Declared support for WordPress 5.4.1

= 1.0.7 =
* Added new settings area allowing you to choose first day of week and 'Assigned To' output format
* Used new multiselect control introduced in 1.4.55 of Property Hive for Negotiator dropdown
* Declared support for WordPress 5.3.2

= 1.0.6 =
* Remember last selected view and default back to this when next reloading the calendar
* Cancelled and completed items will now appear translucent
* Show items assigned to everyone even when negotiator filter is set. For example, unaccompanied viewings will show on all negotiators calendars, as will tasks assigned to everyone

= 1.0.5 =
* Remember choice in negotiator filter and default back to this when next reloading the calendar
* Declared support for WordPress 5.3

= 1.0.4 =
* Include general appointments in 'My Upcoming Appointments' dashboard widget
* Declared support for WordPress 5.2.3

= 1.0.3 =
* Added ability to choose multiple negotiators when filtering calendar

= 1.0.2 =
* Added support for general appointments (holidays, office meetings etc)
* Updated column header format of week view to be non-US format
* Updated FullCalendar JS plugin from 4.0.0-beta to 4.2.0

= 1.0.1 =
* Order negotiators alphabetically in dropdown
* Corrected issue with calendar not loading when White Label add on is installed
* Improved debugging on load, resize and drag events
* Added better loading message
* Declared support for WordPress 5.2.2

= 1.0.0 =
* First working release of the add on