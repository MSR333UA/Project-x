var original_task_title_border;
var saving_task = false;

function propertyhive_load_tasks_grid()
{
	//jQuery('#propertyhive_tasks_grid').html('Loading Tasks...'); / don't do this as it might make the grid jump around when reloading it

	jQuery.post(
		ajaxurl,
		{
			action: 'propertyhive_get_tasks',
			related_to: jQuery('#propertyhive_tasks_grid').attr('data-related-to'),
			security: ajax_object.ajax_nonce
		},
		function( response )
		{
			// success
			if ( response.length == 0 )
			{
				jQuery('#propertyhive_tasks_grid').html('<p>No tasks exist.</p>');
			}
			else
			{	
				var previous_status = 'open';
				var html = '';
				html += '<div class="open-tasks">';
				for ( var i in response )
				{
					if ( previous_status != response[i].status )
					{
						html += '</div>';
						html += '<div class="completed-tasks">';
					}
					html += '<div id="propertyhive_task_' + response[i].id + '" class="task" data-original-status="' + response[i].status + '" data-original-position="' + i + '">';
						html += '<div class="task-title"><input type="checkbox" name="task_completed[]" value="' + response[i].id + '"' + ( response[i].status == 'completed' ? ' checked' : '' ) + '> <a href="" class="task-edit" data-task-id="' + response[i].id + '">' + response[i].title + '</a></div>';
						if ( response[i].details != '' ) 
						{
							html += '<div class="task-details">' + response[i].details + '</div>';
						}
						if ( response[i].due_date_formatted != '' ) 
						{
							html += '<div class="task-due-date">Due: ' + response[i].due_date_formatted + '</div>';
						}
						if ( response[i].completed_formatted != '' ) 
						{
							html += '<div class="task-due-date task-completed">Completed: ' + response[i].completed_formatted + '</div>';
						}
						if ( response[i].assigned_to_names != '' ) 
						{ 
							html += '<div class="task-assigned-to">Assigned To: ' + response[i].assigned_to_names + '</div>'; 
						}
						else
						{
							html += '<div class="task-assigned-to">Assigned To: Everyone</div>'; 
						}
					html += '</div>';

					previous_status = response[i].status;
				}
				if ( previous_status == 'open' )
				{
					html += '</div><div class="completed-tasks">';
				}
				html += '</div><div style="clear:both"></div>';
				jQuery('#propertyhive_tasks_grid').html(html);
			}
		}
	)
	.fail(function() 
	{
		jQuery('#propertyhive_tasks_grid').html('Failed to load tasks. Please refresh to try again.');
	});
}

jQuery(document).ready(function()
{
	if ( jQuery('#propertyhive_tasks_grid').length > 0 )
	{
		propertyhive_load_tasks_grid();
	}

	jQuery('#propertyhive_tasks_grid').on( 'click', 'a.task-edit', function(e)
	{
		e.preventDefault();

		var task_id = jQuery(this).attr('data-task-id');

		jQuery.post(
			ajaxurl,
			{
				action: 'propertyhive_get_task',
				task_id: task_id,
				security: ajax_object.ajax_nonce
			},
			function( response )
			{
				jQuery('#_task_id').val(task_id);
				jQuery('#_task_title').val(response.title);
				jQuery('#_task_details').val(response.details);
				jQuery('#_task_due_date').val(response.due_date);

				if ( response.assigned_to && response.assigned_to.length > 0)
				{
					jQuery.each(response.assigned_to, function(i,e)
					{
					    jQuery("#_task_assigned_to option[value='" + e + "']").prop("selected", true);
					});
				}
				else
				{
					jQuery("#_task_assigned_to option:selected").removeAttr("selected");
				}

				jQuery("#_task_assigned_to").trigger("chosen:updated");

				jQuery('a.add-new-task').hide();
				jQuery('a.delete-task').show();
				jQuery('.create-task-form').show();
			}
		);
	});

	jQuery('#create_task_form').on( 'click', 'a.delete-task', function(e)
	{
		e.preventDefault();

		var confirm_box = confirm("Are you sure you wish to delete this task?");

		if ( confirm_box == false )
		{
			return false;
		}

		jQuery.post(
			ajaxurl,
			{
				action: 'propertyhive_delete_task',
				task_id: jQuery('#_task_id').val(),
				security: ajax_object.ajax_nonce
			},
			function( response )
			{
				if ( response == 'success' )
				{
					jQuery('#_task_id').val('');
					jQuery('#_task_title').val('');
					jQuery('#_task_details').val('');
					jQuery('#_task_due_date').val('');

					jQuery('#create_task_form').hide();
					jQuery('a.delete-task').hide();
					jQuery('a.add-new-task').show();

					propertyhive_load_tasks_grid();
				}
				else
				{
					alert("An error occurred whilst trying to delete this task. Please refresh and try again");
				}
			}
		);
	});

	jQuery('#propertyhive_tasks_grid').on( 'click', 'input[name=\'task_completed[]\']', function()
	{
		var task_id = jQuery(this).val();

		if ( saving_task == true )
		{
			return false;
		}

		saving_task = true;
		jQuery('input[name=\'task_completed[]\']').attr('disabled', 'disabled');

		if ( jQuery(this).prop('checked') == true )
		{
			// changing to completed
			jQuery.post(
				ajaxurl,
				{
					action: 'propertyhive_task_complete',
					task_id: task_id,
					security: ajax_object.ajax_nonce
				},
				function( response )
				{
					// success
			
					var clone = jQuery('#propertyhive_task_' + task_id).clone();
					clone.css('display', 'none').css('opacity', 0);
					
					jQuery('#propertyhive_task_' + task_id).slideUp(400, function() 
					{ 
						jQuery('#propertyhive_task_' + task_id).remove(); 
						jQuery('#propertyhive_tasks_grid .completed-tasks').prepend(clone);
						clone.slideDown(400, function() { clone.fadeTo(400, 0.5); });

						saving_task = false;
						jQuery('input[name=\'task_completed[]\']').attr('disabled', false);
					});
				}
			).fail(function() 
			{
	    		alert( "Error updating task. Please refresh and try again" );

	    		saving_task = false;
				jQuery('input[name=\'task_completed[]\']').attr('disabled', false);
	  		});
		}
		else
		{
			// changing to open
			jQuery.post(
				ajaxurl,
				{
					action: 'propertyhive_task_open',
					task_id: task_id,
					security: ajax_object.ajax_nonce
				},
				function( response )
				{
					// success

					var clone = jQuery('#propertyhive_task_' + task_id).clone();
					clone.css('display', 'none').css('opacity', 0);

					jQuery('#propertyhive_task_' + task_id).slideUp(400, function() 
					{ 
						jQuery('#propertyhive_task_' + task_id).remove(); 
						jQuery('#propertyhive_tasks_grid .open-tasks').append(clone);
						clone.slideDown(400, function() { clone.fadeTo(400, 1); });

						saving_task = false;
						jQuery('input[name=\'task_completed[]\']').attr('disabled', false);
					});
				}
			).fail(function() 
			{
	    		alert( "Error updating task. Please refresh and try again" );

	    		saving_task = false;
				jQuery('input[name=\'task_completed[]\']').attr('disabled', false);
	  		});
		}
	});

	original_task_title_border = jQuery('#_task_title').css('border');
	jQuery('a.add-new-task').click(function(e)
	{
		e.preventDefault();

		jQuery('a.add-new-task').hide();
		jQuery('a.delete-task').hide();
		jQuery('#create_task_form').show();
	});

	jQuery('a#create_task_form_cancel').click(function(e)
	{
		e.preventDefault();

		jQuery('#_task_id').val('');
		jQuery('#_task_title').val('');
		jQuery('#_task_details').val('');
		jQuery('#_task_due_date').val('');
		jQuery('#create_task_form').hide();
		jQuery('a.delete-task').hide();
		jQuery('a.add-new-task').show();
	});

	jQuery('#_task_title').change(function()
	{
		if ( jQuery(this).val() != '' )
		{
			jQuery(this).css('border', original_task_title_border);
		}
	});

	jQuery('#create_task_form_submit').click(function(e)
	{
		e.preventDefault();

		// validate a title is present
		if ( jQuery('#_task_title').val() == '' )
		{
			jQuery('#_task_title').css('border', '1px solid #900');
			return false;
		}

		jQuery('#create_task_form_submit').attr('disabled', 'disabled');
		jQuery('#create_task_form_submit').text('Saving...');

		// make ajax request to save task
		var assigned_to = [];
        jQuery('#_task_assigned_to option').each(function(i) 
        {
            if (this.selected == true) 
            {
                assigned_to.push(this.value);
            }
        });

		jQuery.post(
			ajaxurl,
			{
				action: 'propertyhive_create_task',
				task_id: jQuery('#_task_id').val(),
				title: jQuery('#_task_title').val(),
				details: jQuery('#_task_details').val(),
				due_date: jQuery('#_task_due_date').val(),
				related_to: jQuery('#_task_related_to').val(),
				assigned_to: assigned_to,
				security: ajax_object.ajax_nonce
			},
			function( response )
			{
				// success
				//console.log(response);

				jQuery('#create_task_form_submit').attr('disabled', false);
				jQuery('#create_task_form_submit').text('Save Task');

				// reset form
				jQuery('#_task_id').val('');
				jQuery('#_task_title').val('');
				jQuery('#_task_details').val('');
				jQuery('#_task_due_date').val('');

				jQuery('#create_task_form').hide();
				jQuery('a.delete-task').hide();
				jQuery('a.add-new-task').show();

				propertyhive_load_tasks_grid();
			}
		)
		.fail(function() 
		{
    		alert( "Error saving task. Please try again" );

    		jQuery('#create_task_form_submit').attr('disabled', false);
			jQuery('#create_task_form_submit').text('Save Task');
  		});
	});
});

jQuery(window).on('load', function()
{
	if ( jQuery('#ph_dashboard_tasks').length > 0 )
	{
		// Load open tasks assigned to me/everyone
		var data = {
			action: 'propertyhive_get_tasks',
			status: 'open',
			assigned_to: ajax_object.current_user_id,
			security: ajax_object.ajax_nonce
		};

		jQuery.post(ajaxurl, data, function(response)
		{
			if ( response == '' || response.length == 0 )
			{
				jQuery('#ph_dashboard_tasks').html('No open tasks to display');
				return;
			}

			jQuery('#ph_dashboard_tasks').html('<ul></ul>')
			for ( var i in response )
			{
				if ( i > 4 )
				{
					break;
				}
				jQuery('#ph_dashboard_tasks ul').append('<li><a class="rsswidget" style="font-weight:400" href="' + response[i].edit_link + '">' + ( ( response[i].title != '' ) ? response[i].title : '(no title)' ) + '</a><br><small style="opacity:0.85">' + ( (response[i].due_date_formatted != '') ? 'Due: ' + response[i].due_date_formatted + '<br>' : '' ) + 'Assigned To: ' + ( ( response[i].assigned_to_names != '' ) ? response[i].assigned_to_names : 'Everyone' ) + '</small></li>');
			}
				
			jQuery('#ph_dashboard_tasks').append('<a href="' + ajax_object.dashboard_tasks_list_link + '">View All Open Tasks Assigned To Me</a> (' +  response.length + ')');
			
		}, 'json');
	}
});