<?php
	$start_date = date_create($start_date);
	$end_date = date_create($end_date);

	$date_diff = date_diff($start_date, $end_date);
	if ( $date_diff->m === 1 )
	{
		// month view
		$date_header = date_format($start_date, 'F Y' );
	}
	elseif ( $date_diff->d === 1 )
	{
		// day view
		$date_header = date_format($start_date, 'F j, Y' );
	}
	else
	{
		// week view
		$day_before_end_date = $end_date->sub(new DateInterval('P1D'));

		if ( date_format($start_date, 'm' ) == date_format($day_before_end_date, 'm' ) )
		{
			$to_date_format = 'j, Y';
		}
		else
		{
			$to_date_format = 'M j, Y';
		}

		$date_header = date_format($start_date, 'M j' ) . ' - ' . date_format($day_before_end_date, $to_date_format );
	}
?>
<h1><strong><?php echo $date_header ?></strong></h1>
<?php
	if ( count($events) != 0 )
	{
		// For multi-day all day appointments, create a new event for each, so they show in the schedule each day
		foreach ( $events as $event )
		{
			if ( isset($event['allDay']) && $event['allDay'] === true )
			{
				$all_day_start_date = date_create($event['start']);
				$all_day_end_date = date_create($event['end']);

				$all_day_date_diff = date_diff($all_day_start_date, $all_day_end_date);
				if ( $all_day_date_diff->d > 1 )
				{
					for ( $x = 1; $x < $all_day_date_diff->d; ++$x )
					{
						$all_day_start_date->modify('+1 day');
						if ( $all_day_start_date > $end_date )
						{
							// If the day is after the end date of the current view, don't show events after the visible range
							break;
						}
						$new_all_day_event = $event;
						$new_all_day_event['start'] = date_format($all_day_start_date, 'Y-m-d' );
						$events[] = $new_all_day_event;
					}
				}
			}
		}
?>
<table class="calendar_schedule_table">
<?php
	usort($events, function($a, $b) {
		return strtotime($a['start']) - strtotime($b['start']);
	});

	$event_date = null;
	foreach ( $events as $event )
	{
		$previous_date = $event_date;
		$event_date = date( 'l jS M', strtotime($event['start']) );
		?>
		<tr class="calendar_schedule_row">
			<td class="calendar_schedule_date">
				<?php
					if ( $event_date !== $previous_date )
					{
						echo $event_date;
					}
					else
					{
						echo '&nbsp;';
					}
				?>
			</td>
			<td class="calendar_schedule_time">
				<?php
					if ( isset($event['allDay']) && $event['allDay'] === true )
					{
						echo "All Day";
					}
					else
					{
						echo date( 'g:ia', strtotime($event['start']) );
					}
				?>
			</td>
			<td class="calendar_schedule_info" style="width:80%;">
				<?php
					echo nl2br($event['title']);
					if ( isset( $event['contactDetails'] ) )
					{
						foreach ($event['contactDetails'] as $contact)
						{
							echo '<br>' . $contact['name'] . ' (' . ucfirst($contact['type']) . '): ';
							$contact_methods = array_filter(array($contact['phone'], $contact['email']));
							echo implode(', ', $contact_methods);
						}
					}
				?>
			</td>
		</tr>
		<?php
	}
?>
</table>
<br>
<button type="button" id="print_calendar_button" class="noprint">Print</button>
<?php
	}
	else
	{
		echo "<br>No events scheduled";
	}
?>