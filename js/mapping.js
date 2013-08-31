var map = new google.maps.Map(
	document.getElementById('map_canvas'),
	{
		zoom: 7,
		center: new google.maps.LatLng(53.5, -8.0),
		mapTypeId: google.maps.MapTypeId.ROADMAP
	}
);
document.address = function(club) {
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
};
document.gps_coordinates = function(club) {
	return "<br />GPS: " + club['latitude'].toFixed(4) + ", " + club['longitude'].toFixed(4);
};
for (cnr in clubs_and_ranges) {
	clubs_and_ranges[cnr]['point'] = new google.maps.LatLng(
		clubs_and_ranges[cnr]['latitude'],
		clubs_and_ranges[cnr]['longitude']
	);
	clubs_and_ranges[cnr]['marker'] = new google.maps.Marker({
		position: clubs_and_ranges[cnr]['point'],
		map: map,
		title: cnr
	});
	info = cnr;
	if (clubs_and_ranges[cnr]['url']) {
		info = "<a href=\"" + clubs_and_ranges[cnr]['url'] + "\">" + info + "</a>";
	}
	info += "<br />";
	info += document.address(clubs_and_ranges[cnr]);
	info += document.gps_coordinates(clubs_and_ranges[cnr]);
	info += "<br /><a target=\"_blank\" ";
	info += "href=\"http://maps.google.com/maps?saddr=&daddr=" +
		clubs_and_ranges[cnr]['latitude'] + "," +
		clubs_and_ranges[cnr]['longitude'] + "\">Directions...</a>";
	clubs_and_ranges[cnr]['info_window'] = new google.maps.InfoWindow({content: info});
	google.maps.event.addListener(clubs_and_ranges[cnr]['marker'], 'click', function() {
		map.panTo(clubs_and_ranges[this.title]['point']);
		for (cnr in clubs_and_ranges) {
			clubs_and_ranges[cnr]['info_window'].close();
		}
		clubs_and_ranges[this.title]['info_window'].open(map, clubs_and_ranges[this.title]['marker']);
	});
}
