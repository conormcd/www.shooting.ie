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

function renderCalendar(events) {
	contents = '<ol class="events">';
	for (event_time in events) {
		contents += '<li class="day">';
		contents += '<h5>' + formatDate(event_time) + '</h5>';
		contents += '<ol>';
		for (idx in events[event_time]) {
			title = events[event_time][idx]['title_clean'];

			popover_contents = 'Here are the popover contents.';

			contents += '<li class="event" data-trigger="hover" data-content="here is some content" data-toggle="popover" data-original-title="' + title + '">' +
						title + 
						'</li>';
		}
		contents += '</ol>';
		contents += '</li>';
	}
	contents += '</ol>';
	$('#calendar').append(contents).css('height', $(window).height());
}
