/*
 * Copyright (c) 2012, Conor McDermottroe
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *   this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

(function($){})(window.jQuery);
$(document).ready(function (){
	// Calendar stuff
	$('.calendar .event h3').click(function() {
		$(this).parent().find(".event_details").slideToggle();
	});

	// Map stuff
	if ($('#map_canvas').length > 0) {
		var map = new google.maps.Map(
			document.getElementById('map_canvas'),
			{
				zoom: 7,
				center: new google.maps.LatLng(
					53.5,
					-8.0
				),
				mapTypeId: google.maps.MapTypeId.ROADMAP
			}
		);
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
				info = "<a href=\"" + clubs_and_ranges[cnr]['url'] + "\">" +
					info + "</a>";
			}
			info += "<br />";
			if (clubs_and_ranges[cnr]['phone']) {
				info += "<br /><span class=\"tel\">Phone: <a clas=\"value\" "; 
				info += "href=\"tel:" + clubs_and_ranges[cnr]['phone'] + "\">";
				info +=	clubs_and_ranges[cnr]['phone'] + "</a>";
			}
			info += "<br /><a target=\"_blank\" ";
			info += "href=\"http://maps.google.com/maps?saddr=&daddr=" +
				clubs_and_ranges[cnr]['latitude'] + "," +
				clubs_and_ranges[cnr]['longitude'] + "\">Directions...</a>";
			clubs_and_ranges[cnr]['info_window'] = new google.maps.InfoWindow({
				content: info
			});
			google.maps.event.addListener(clubs_and_ranges[cnr]['marker'], 'click', function() {
				document.selectClub(this.title);
			});
		}
		$('#club_list div').each(function (index) {
			$(this).hover(function() {
				$(this).toggleClass('hover');
			});
			clubs_and_ranges[$(this).text()]['node'] = $(this)
		});
		
		document.selectClub = function (club_name) {
			map.panTo(clubs_and_ranges[club_name]['point']);
			map.setZoom(12);
			for (cnr in clubs_and_ranges) {
				clubs_and_ranges[cnr]['info_window'].close();
				clubs_and_ranges[cnr]['node'].removeClass('active');
			}
			clubs_and_ranges[club_name]['info_window'].open(map, clubs_and_ranges[club_name]['marker']);
			clubs_and_ranges[club_name]['node'].addClass('active');
		}

		// Resize the map to the same height as the list of clubs.
		$("#map_canvas").height($("#club_list").height());
	}
});
