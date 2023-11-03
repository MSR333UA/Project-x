var ph_calendar_day = ph_calendar.day;
var ph_calendar_month = ph_calendar.month;
var ph_calendar_year = ph_calendar.year;

var calendar;
var fully_rendered = false;
var hoverTimeout = false;

jQuery(document).ready(function()
{
	jQuery('body').on('click', '#print_calendar_button', function() {

		var printWindow = window.open('', 'PRINT', 'height=600,width=1000');

		printWindow.document.write('<html><head><style>');
		printWindow.document.write('@media print { .noprint { visibility: hidden; } } ');
		printWindow.document.write('body { font-family: "Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif; } ');
		printWindow.document.write('table { border-collapse: collapse; border-spacing: 0; font-size: 1em; } ');
		printWindow.document.write('td { border: 1px solid #ddd;  padding: 5px; vertical-align: top; } ');
		printWindow.document.write('</style><title>Print Calendar</title></head><body>');
		printWindow.document.write(jQuery(".calendar_schedule_container").html());
		printWindow.document.write('</body></html>');

		printWindow.document.close(); // necessary for IE >= 10
		printWindow.focus(); // necessary for IE >= 10*/

		printWindow.print();
		printWindow.close();

		return true;
	});
});

jQuery(window).on('load', function()
{
	var View = FullCalendar.View;
	var createPlugin = FullCalendar.createPlugin;

	class ScheduleView extends View {

		initialize() {
			// called once when the view is instantiated, when the user switches to the view.
			// initialize member variables or do other setup tasks.
			this.el.innerHTML = '<div class="calendar_schedule_container">Loading...</div>';
			jQuery.ajax({
				url: ph_calendar.ajax_url,
				dataType: 'html',
				data: {
					action: 'propertyhive_load_events',
					start: calendar.view.currentStart.toISOString().split('T')[0],
					end: calendar.view.currentEnd.toISOString().split('T')[0],
					negotiator_id: jQuery('#negotiator_id').val(),
					all_negotiators_id: all_negotiator_ids,
					schedule_mode: true,
				},
				success: function(response)
				{
					jQuery('div.calendar_schedule_container').html(response);
				}
			});
		}
	}

	var ScheduleViewPlugin = createPlugin({
		views: {
			schedule: ScheduleView
		}
	});

	jQuery('body').on('change', '#negotiator_id', function()
	{
		calendar.refetchResources();
		calendar.refetchEvents();
	});

	var calendarEl = document.getElementById('ph_calendar');

	var defaultView = 'timeGridWeek';
	if ( ph_calendar.view == 'day' )
	{
		defaultView = 'timeGridDay';
	}
	if ( ph_calendar.view == 'month' )
	{
		defaultView = 'dayGridMonth';
	}
	if ( ph_calendar.view == 'timeline' )
	{
		defaultView = 'resourceTimeline';
	}

	var resources = new Array();
	var all_negotiator_ids = new Array();
	for ( var i in ph_calendar.negotiators )
	{
		resources.push({
			"id": ph_calendar.negotiators[i].id,
			"title": ph_calendar.negotiators[i].name
		});
		all_negotiator_ids.push(ph_calendar.negotiators[i].id);
	}

	calendar = new FullCalendar.Calendar(calendarEl, {
		schedulerLicenseKey: '0071312890-fcs-1589799283',
		timeZone: 'Europe/London',
  		plugins: [ 'dayGrid', 'timeGrid', 'interaction', 'moment', 'resourceTimeline', ScheduleViewPlugin ],
  		header: {
	        left: 'prev,next today',
	        center: 'title',
	        right: 'timeGridDay,timeGridWeek,dayGridMonth,resourceTimeline,schedule'
	    },
	    navLinks: true, // Determines if day names and week names are clickable.
	    defaultView: defaultView,
	    nowIndicator: true,
	    scrollTime: '07:00:00',
	    firstDay: parseInt(ph_calendar.week_start_day),
	    contentHeight: get_calendar_target_height(),
	    editable: true,
	    selectable:  true,
	    allDaySlot: true,
	    allDayText: 'All Day',
	    slotDuration: '00:15:00',
	    views: {
	    	/*timeGridDay: {

	    	},*/
		    timeGridWeek: {
		      	//titleFormat: 'YYYY, MM, DD'
		      	columnHeaderFormat: 'Do MMM'
		      	// other view-specific options here
		    },/*,
		    dayGridMonth: {

		    }*/
		    resourceTimeline: {
		    	buttonText: 'timeline'
		    }
		},
		customButtons: {
			schedule: {
				text: 'schedule',
				click: function() {
					calendar.changeView('schedule', {
						start: calendar.view.currentStart.toISOString().split('T')[0],
						end: calendar.view.currentEnd.toISOString().split('T')[0]
					});
				}
			}
		},
		resourceLabelText: 'Name',
		resourceOrder: 'title',
		resourceAreaWidth: 170,
		resources: function(info, successCallback, failureCallback) 
		{
		    if ( !fully_rendered )
	    	{
	    		console.log('not fully rendered');
	    		return false;
	    	}

		    console.log('loading resources');
	    	console.log('selected negotiators:');
	    	console.log(jQuery('#negotiator_id').val());

        	jQuery.ajax({
		    	url: ph_calendar.ajax_url,
		      	dataType: 'json',
		      	data: {
		        	action: 'propertyhive_load_resources',
        			negotiator_id: jQuery('#negotiator_id').val(),
		      	},
		      	success: function(response) 
		      	{
		      		console.log('Resource Load Success: ');
      				console.log(response);

      				successCallback(response);
		      	}
		    });
	  	},
		loading: function(isLoading)
		{
			/*if (!isLoading && calendar.view.type != 'resourceTimeline')
			{
				jQuery('.fc-scroller').height( get_calendar_target_height() );
			}*/
		},
		datesRender: function(info)
		{
			console.log('rendering dates');

			jQuery('.ph-calendar-new-event-popup').hide();
			calendar.unselect();

			jQuery.ajax({
		    	url: ph_calendar.ajax_url,
		      	dataType: 'json',
		      	method: 'POST',
		      	data: {
		        	action: 'propertyhive_update_view',
		        	view: info.view.type
		      	}
		    });
		},	
	    events: function(info, successCallback, failureCallback) 
	    {
	    	if ( !fully_rendered )
	    	{
	    		console.log('not fully rendered');
	    		return false;
	    	}

	    	console.log('loading events');
	    	console.log('selected negotiators:');
	    	console.log(jQuery('#negotiator_id').val());

		    jQuery.ajax({
		    	url: ph_calendar.ajax_url,
		      	dataType: 'json',
		      	data: {
		        	action: 'propertyhive_load_events',
		        	start: info.startStr,
        			end: info.endStr,
        			negotiator_id: jQuery('#negotiator_id').val(),
        			all_negotiators_id: all_negotiator_ids
		      	},
		      	success: function(response) 
		      	{
		      		console.log('Event Load Success: ');
		      		console.log(response);
		        	var events = [];
		        	jQuery(response).each(function() {

		        		var start = jQuery(this).attr('start');
		        		var end = jQuery(this).attr('end');

		        		if ( jQuery(this).attr('allDay') == true )
		        		{
		        			start = start.split("T");
		        			start = start[0];
		        			if (typeof end !== typeof undefined && end !== false) 
		        			{
			        			end = end.split("T");
			        			end = end[0];
			        		}
			        		else
			        		{
			        			end = start;
			        		}
		        		}

		          		events.push({
		          			id: jQuery(this).attr('id'),
		          			//groupId: jQuery(this).attr('groupId'),
		          			type: jQuery(this).attr('type'),
		            		title: ph_html_entity_decode_js(jQuery(this).attr('title')),
		            		start: start,
		            		end: end,
		            		borderColor: jQuery(this).attr('backgroundColor'),
		            		backgroundColor: jQuery(this).attr('backgroundColor'),
		            		url: jQuery(this).attr('url'),
		            		classNames: jQuery(this).attr('classNames'),
		            		resourceIds: jQuery(this).attr('resourceIds'),
		            		propertyAddress: jQuery(this).attr('propertyAddress'),
		            		contactDetails: jQuery(this).attr('contactDetails'),
		            		//rrule: {
						    //    freq: 'daily',
						    //    interval: 1,
						    //    dtstart: jQuery(this).attr('start'),
						    //    until: '2020-07-01'
						    //},
						    //duration: "02:00"
		          		});
		        	});
		        	successCallback(events);
		        	jQuery('.ph-calendar-new-event-popup').hide();
					calendar.unselect();
		      	}
		    });
	  	},
	  	eventResize: function(info) 
	  	{
	    	show_notification( true, 'Saving...' );

	    	jQuery.ajax({
		    	url: ph_calendar.ajax_url,
		      	dataType: 'json',
		      	data: {
		        	action: 'propertyhive_resized_event',
		        	id: info.event.id,
        			end: info.event.end.toISOString()
		      	},
		      	success: function(response) 
		      	{
		      		console.log('Event Resize Success: ');
		      		console.log(response);
		      		if ( response.success != true )
		      		{
		      			show_notification( false, 'Failed to save event. Please try again' );
						info.revert();
		      		}
		      		else
		      		{
		      			show_notification( true, 'Event saved' );
		      			calendar.refetchEvents();
		      		}
		      	}
		    });
	  	},
	  	eventDrop: function(info) 
	  	{
    		show_notification( true, 'Saving...' );

    		jQuery.ajax({
		    	url: ph_calendar.ajax_url,
		      	dataType: 'json',
		      	data: {
		        	action: 'propertyhive_dragged_event',
		        	id: info.event.id,
        			start: info.event.start.toISOString()
		      	},
		      	success: function(response) 
		      	{
		      		console.log('Event Drag Success: ');
		      		console.log(response);
		      		if ( response.success != true )
		      		{
		      			show_notification( false, 'Failed to save event. Please try again' );
						info.revert();
		      		}
		      		else
		      		{
		      			show_notification( true, 'Event saved' );
		      			calendar.refetchEvents();
		      		}
		      	}
		    });
  		},
		eventMouseEnter: function(info) {
			// Only display details popup 0.5 seconds after initial hover
			hoverTimeout = setTimeout(function(){
				// Get calendar appointment times and append appointment details on the end
				var html = moment(info.event.start).utc().format('h:mm') + ' - ' + moment(info.event.end).utc().format('h:mm');

				var eventType = info.event.extendedProps.type;

				// Add event type with first letter capitalized
				html += '<br>' + eventType.charAt(0).toUpperCase() + eventType.slice(1);

				switch (eventType)
				{
					case 'viewing':
					case 'appraisal':
						html += '<br>Property: ' + info.event.extendedProps.propertyAddress;
						break;
					default:
						// For other appointment types, just display the full title
						html += '<br>' + info.event.title.replace(/(?:\r\n|\r|\n)/g, '<br>');
						break;
				}

				// If the event has contact details, add them to the popup
				var contactDetails = info.event.extendedProps.contactDetails;
				if (typeof contactDetails !== 'undefined' && contactDetails !== false)
				{
					contactDetails.forEach(function (item, index) {
						html += '<br>' + item['name'] + ' (' + item['type'].charAt(0).toUpperCase() + item['type'].slice(1) + '): ';

						// Filter blank values from array of contact methods
						var contactMethods = [item['phone'], item['email']].filter(e => e);

						html += contactMethods.join(', ');
					});
				}

				jQuery('.ph-calendar-event-details-popup').html(html);

				var popup_top = info.jsEvent.pageY - jQuery('#wpbody-content').offset().top;
				var popup_left = info.jsEvent.pageX - jQuery('#wpbody-content').offset().left
				jQuery('.ph-calendar-event-details-popup').css({ top: popup_top, left: popup_left }).fadeIn('fast');
			}, 500);
		},
		eventMouseLeave: function(info) {
			// If cursor leaves appointment before details popup displays, don't show the popup
			clearTimeout(hoverTimeout);

			// If cursor leaves the event and isn't over the details popup, close the details popup
			// This also means if the popup opens slightly away from the cursor, it will remain open if the cursor moves to it
			if( jQuery(".ph-calendar-event-details-popup:hover").length == 0 ) {
				jQuery('.ph-calendar-event-details-popup').hide();
			}
		},
  		select: function(info)
  		{
			var popup_top = info.jsEvent.pageY - jQuery('#wpbody-content').offset().top;
			var popup_left = info.jsEvent.pageX - jQuery('#wpbody-content').offset().left
  			if ( info.allDay && calendar.view.type != 'dayGridMonth' )
  			{
  				var html = '<h3>Create New Event</h3>';
  				
  				if ( ph_calendar.tasks_enabled )
  				{
	  				html += '<a href="' + ph_calendar.admin_url + 'post-new.php?post_type=task&start=' + (info.start.getTime() / 1000) + '" class="button button-primary">Add Task</a>';
	  			}

	  			html += '<a href="' + ph_calendar.admin_url + 'post-new.php?post_type=appointment&start=' + (info.start.getTime() / 1000) + '&end=' + (info.end.getTime() / 1000) + '&all_day=yes" class="button button-primary">Add General Appointment</a>';

	  			html += '<a href="" class="cancel-create-new-event button">Cancel</a>';
				jQuery('.ph-calendar-new-event-popup').html(html);
				jQuery('.ph-calendar-new-event-popup').css({ top: popup_top, left: popup_left }).fadeIn('fast');
  			}
  			else
  			{
				var html = '<h3>Create New Event</h3>';
				if ( ph_calendar.appraisals_enabled ) { html += '<a href="' + ph_calendar.admin_url + 'post-new.php?post_type=appraisal&start=' + (info.start.getTime() / 1000) + '&end=' + (info.end.getTime() / 1000) + '" class="button button-primary">Add Appraisal</a>'; }
				if ( ph_calendar.viewings_enabled ) { html += '<a href="' + ph_calendar.admin_url + 'post-new.php?post_type=viewing&start=' + (info.start.getTime() / 1000) + '&end=' + (info.end.getTime() / 1000) + '" class="button button-primary">Add Viewing</a>'; }
				if ( calendar.view.type == 'dayGridMonth' && ph_calendar.tasks_enabled )  { html += '<a href="' + ph_calendar.admin_url + 'post-new.php?post_type=task&start=' + (info.start.getTime() / 1000) + '" class="button button-primary">Add Task</a>'; }
				html += '<a href="' + ph_calendar.admin_url + 'post-new.php?post_type=appointment&start=' + (info.start.getTime() / 1000) + '&end=' + (info.end.getTime() / 1000) + '" class="button button-primary">Add General Appointment</a>';
				html += '<a href="" class="cancel-create-new-event button">Cancel</a>';
				jQuery('.ph-calendar-new-event-popup').html(html);
				jQuery('.ph-calendar-new-event-popup').css({ top: popup_top, left: popup_left }).fadeIn('fast');
  			}
  		}
	});

	calendar.render();
	
	var options_html = '';
	for ( var i in ph_calendar.negotiators )
	{
		options_html += '<option value="' + ph_calendar.negotiators[i].id + '"';
		for ( var j in ph_calendar.selected_negotiators )
		{
			if ( ph_calendar.negotiators[i].id == ph_calendar.selected_negotiators[j] )
			{
				options_html += ' selected';
			}
		}
		options_html += '>' + ph_calendar.negotiators[i].name + '</option>';
	}
	jQuery('.fc-left').append('<select name="negotiator_id[]" id="negotiator_id" multiple="multiple" data-placeholder="All Negotiators" class="multiselect">' + options_html + '</select>');

	jQuery("#ph_calendar select.multiselect").multiselect({
		texts: {
        	placeholder: 'Select negotiators'
    	},
    	minHeight: 0
    });

	// Close the details popup when the cursor leaves it
	jQuery('.ph-calendar-event-details-popup').mouseleave(function(){
		jQuery('.ph-calendar-event-details-popup').hide();
	});

	fully_rendered = true;
	calendar.refetchResources();
    calendar.refetchEvents();

	jQuery('body').on('click', '.cancel-create-new-event', function(e) { e.preventDefault(); jQuery('.ph-calendar-new-event-popup').hide(); calendar.unselect(); });

});

jQuery(window).resize(function()
{
	calendar.setOption( 'contentHeight', get_calendar_target_height() );
});

function get_calendar_target_height()
{
	return jQuery(window).height() - jQuery('#ph_calendar').offset().top - 70;
}

var ph_calendar_notification_timeout;
function show_notification( success, message )
{
	clearTimeout(ph_calendar_notification_timeout);

	jQuery('.ph-calendar-notification .inner').html(message);
	jQuery('.ph-calendar-notification').stop().animate({
		bottom:0
	}, 'fast');
	ph_calendar_notification_timeout = setTimeout(function() { jQuery('.ph-calendar-notification').stop().animate({ bottom:"-40px" }, 'fast'); }, 3000);
}

function load_events() 
{
	var data = {
		action: 'propertyhive_load_events',
		start: dp.visibleStart().toString(),
      	end: dp.visibleEnd().toString()
	};

	jQuery.post(ph_calendar.ajax_url, data, function(response) 
	{
		dp.events.list = response;
		dp.update();	  	
	}, 'json');  
}

function ph_html_entity_decode_js(html)
{
    var txt = document.createElement("textarea");
    txt.innerHTML = html;
    return txt.value;
}