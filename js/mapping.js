function infoAddress(club) {
	if (club['address']) {
		address = [];
		if (club['address']['street-address']) {
			address.push(club['address']['street-address']);
		}
		if (club['address']['extended-address']) {
			if (typeof club['address']['extended-address'] === 'object') {
				for (i in club['address']['extended-address']) {
					address.push(club['address']['extended-address'][i]);
				}
			} else {
				address.push(club['address']['extended-address']);
			}
		}
		if (club['address']['locality']) {
			if (club['address']['postal-code']) {
				address.push(club['address']['locality'] + " " + club['address']['postal-code']);
			} else {
				address.push(club['address']['locality']);
			}
		}
		return "<br />" + address.join(",<br />") + "<br />";
	} else {
		return "";
	}
}

function infoWindow(club_name, club) {
	if (club['url']) {
		info = "<a href=\"" + club['url'] + "\">" + club_name + "</a><br />";
	} else {
		info = club_name + "<br />";
	}
	info += infoAddress(club);
	info += "<br />GPS: " + club['latitude'].toFixed(4) + ", " + club['longitude'].toFixed(4);
	info += "<br /><a target=\"_blank\" ";
	info += "href=\"http://maps.google.com/maps?saddr=&daddr=" +
			club['latitude'] + "," +
			club['longitude'] + "\">Directions...</a>";
	return info
}

function drawClubsAndRanges(map, clubs) {
	for (club_name in clubs) {
		clubs[club_name]['point'] = new google.maps.LatLng(
			clubs[club_name]['latitude'],
			clubs[club_name]['longitude']
		);
		clubs[club_name]['marker'] = new google.maps.Marker({
			position: clubs[club_name]['point'],
			map: map,
			title: club_name
		});

		clubs[club_name]['info_window'] = new google.maps.InfoWindow({
			content: infoWindow(club_name, clubs[club_name])
		});

		google.maps.event.addListener(clubs[club_name]['marker'], 'click', function() {
			map.panTo(clubs[this.title]['point']);
			for (club_name in clubs) {
				clubs[club_name]['info_window'].close();
			}
			clubs[this.title]['info_window'].open(map, clubs[this.title]['marker']);

			// Change the URL. Use pushstate if possible.
			if (typeof history.pushstate == 'function') {
				history.pushstate({}, this.title, '/#' + this.title);
			} else {
				document.location.hash = this.title;
			}
		});
	}
}

function loadMap() {
	var map = new google.maps.Map(
		document.getElementById('map_canvas'),
		{
			zoom: 7,
			center: new google.maps.LatLng(53.5, -8.0),
			mapTypeId: google.maps.MapTypeId.ROADMAP
		}
	);
	$.get(
		'/feed/clubs_and_ranges/',
		function (clubs_and_ranges) {
			drawClubsAndRanges(map, clubs_and_ranges);
			goToClubInHash = function() {
				selectedClub = decodeURIComponent(
					document.location.hash.substring(1).replace(/\+/g, '%20')
				);
				if (selectedClub in clubs_and_ranges) {
					google.maps.event.trigger(
						clubs_and_ranges[selectedClub]['marker'],
						'click',
						{
							latLng: clubs_and_ranges[selectedClub]['point']
						}
					);
				} else {
					for (club_name in clubs_and_ranges) {
						clubs_and_ranges[club_name]['info_window'].close();
					}
					map.panTo(new google.maps.LatLng(53.5, -8.0));
				}
			}
			if (document.location.hash) {
				goToClubInHash();
			}
			if ('onhashchange' in window) {
				window.onhashchange = goToClubInHash;
			}
		},
		'json'
	);
}

function loadCalendar(onSuccess, nextAction) {
	$.getJSON('/feed/calendars/').done(function (data) {
		onSuccess(data)
		nextAction();
	}).fail(function () {
		nextAction();
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
		contents += '<div class="date">' + formatDate(event_time) + '</div>';
		contents += '<ol>';
		for (idx in events[event_time]) {
			title = events[event_time][idx]['title_clean'];
			details = events[event_time][idx]['details'];
			loc = events[event_time][idx]['location'];

			contents += '<li class="event">' + title;
			contents += '<div class="details">';
			if (details) {
				contents += '<div>Details: ' + details + '</div>';
			}
			if (loc) {
				contents += '<div>Location: ' + loc + '</div>';
			}
			contents += '</div>';
			contents += '</li>';
		}
		contents += '</ol>';
		contents += '</li>';
	}
	contents += '</ol>';
	$('#calendar').append(contents);
	$('#calendar .event').click(function() {
		$(this).find('.details').toggle();
	});
}

$(function() {
	loadCalendar(
		function(events) {
			renderCalendar(events);

			// Resize the containers to show both the map and sidebar.
			var sidebarWidth = 400;
			var width = $(window).width();
			var height = $(window).height();
			$('#map_canvas').css(
				'width', (width - sidebarWidth) + 'px'
			).css(
				'height', height
			);
			$('#calendar').css(
				'width', sidebarWidth
			).css(
				'height', height
			).show();
		},
		function() {
			loadMap();
		}
	);
});
