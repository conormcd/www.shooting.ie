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
		});
	}
}

$(function() {
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
		},
		'json'
	);
});
