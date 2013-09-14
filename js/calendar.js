function loadCalendar() {
	$.getJSON('/feed/calendars/').done(function (data) {
		renderCalendar(data);
	});
}

function formatDate(epochTime) {
	days = [
		'Sunday',
		'Monday',
		'Tuesday',
		'Wednesday',
		'Thursday',
		'Friday',
		'Saturday',
	]
	months = [
		'January',
		'February',
		'March',
		'April',
		'May',
		'June',
		'July',
		'August',
		'September',
		'October',
		'November',
		'December'
	];
	date = new Date();
	date.setTime(epochTime * 1000);
	return  days[date.getDay()] + ' ' +
			date.getDate() + ' ' +
			months[date.getMonth()] + ' ' +
			date.getFullYear();
}

function newElement(element, attrs, text) {
	if (arguments.length < 3) { text = null; }
	if (arguments.length < 2) { attrs = {}; }

	ele = document.createElement(element);
	for (attr in attrs) {
		ele.setAttribute(attr, attrs[attr]);
	}
	if (text != null) {
		ele.appendChild(document.createTextNode(text));
	}
	return ele;
}

function buildEventDetailsTable(obj, entries) {
	table  = '<table>';
	for (entry in entries) {
		if (entry in obj && obj[entry]) {
			table += '<tr>';
			table += '<th class="event-details">' + entries[entry] + '</th>';
			table += '<td class="event-details">' + obj[entry] + '</td>';
			table += '</tr>';
		}
	}
	table += '</table>';
	return table;
}

function renderCalendar(events) {
	all_events = newElement('ol', {'class': 'events'});
	for (event_time in events) {
		day = newElement('li', {'class': 'day'});
		day.appendChild(newElement('h5', {}, formatDate(event_time)));
		day_events = newElement('ol');
		for (idx in events[event_time]) {
			day_event = newElement(
				'li',
				{
					'class': 'event',
					'data-content': buildEventDetailsTable(
						events[event_time][idx],
						{
							'calendar': 'Calendar',
							'location': 'Location',
							'details': 'Details'
						}
					),
					'data-html': true,
					'data-toggle': 'popover',
					'data-placement': 'auto top',
					'data-trigger': 'hover',
					'title': events[event_time][idx]['title_clean']
				},
				events[event_time][idx]['title_clean']
			);
			$(day_event).popover();
			day_events.appendChild(day_event);
		}
		day.appendChild(day_events);
		all_events.appendChild(day);
	}

	$('#calendar').append(all_events).css('height', $(window).height());
}
