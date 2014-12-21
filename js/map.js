function infoAddress(club) {
    if (club['properties']['address']) {
        addr = club['properties']['address'];
        address = [];
        if (addr['street-address']) {
            address.push(addr['street-address']);
        }
        if (addr['extended-address']) {
            if (typeof addr['extended-address'] === 'object') {
                for (i in addr['extended-address']) {
                    address.push(addr['extended-address'][i]);
                }
            } else {
                address.push(addr['extended-address']);
            }
        }
        if (addr['locality']) {
            if (addr['postal-code']) {
                address.push(addr['locality'] + " " + addr['postal-code']);
            } else {
                address.push(addr['locality']);
            }
        }
        return "<br />" + address.join(",<br />") + "<br />";
    } else {
        return "";
    }
}

function infoWindow(club_name, club) {
    if (club['properties']['url']) {
        info = "<a href=\"" + club['properties']['url'] + "\">" + club_name + "</a><br />";
    } else {
        info = club_name + "<br />";
    }
    info += infoAddress(club);
    if (club['properties']['phone']) {
        info += "<br />Phone: <a href=\"tel:" +  club['properties']['phone'] +
            "\">" + club['properties']['phone'] + "</a>"
    }
    info += "<br />GPS: " + club['geometry']['coordinates'][1].toFixed(4) + ", " + club['geometry']['coordinates'][0].toFixed(4);
    info += "<br /><a target=\"_blank\" ";
    info += "href=\"http://maps.google.com/maps?saddr=&daddr=" +
            club['geometry']['coordinates'][1] + "," +
            club['geometry']['coordinates'][0] + "\">Directions...</a>";
    return info
}

function drawClubsAndRanges(map, clubs) {
    for (club_name in clubs) {
        clubs[club_name]['point'] = new google.maps.LatLng(
            clubs[club_name]['geometry']['coordinates'][1],
            clubs[club_name]['geometry']['coordinates'][0]
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
        document.getElementById('map'),
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
