/*
	TemPIC - Copyright (c) PotcFdk, 2014 - 2020

	Licensed under the Apache License, Version 2.0 (the "License");
	you may not use this file except in compliance with the License.
	You may obtain a copy of the License at

	http://www.apache.org/licenses/LICENSE-2.0

	Unless required by applicable law or agreed to in writing, software
	distributed under the License is distributed on an "AS IS" BASIS,
	WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
	See the License for the specific language governing permissions and
	limitations under the License.
*/

function info(text) {
	$("#div_info_element").show();
	var info_element = $("#info_element");
	var info_element_text = $("#info_element_text");
	info_element_text.html(text);
	info_element.show();
}

function warn(text) {
	if (text)
	{
		$("#div_warn_element").show();
		var warn_element = $("#warn_element");
		var warn_element_text = $("#warn_element_text");
		warn_element_text.html(text);
		warn_element.show();
	}
	else
	{
		$('#div_warn_element').slideUp(1000, "swing");
	}
}

// humanFileSize by Mark - http://stackoverflow.com/a/14919494
function humanFileSize(bytes, si) {
	var thresh = si ? 1000 : 1024;
	if(bytes < thresh) return bytes + ' B';
	var units = si ? ['kB','MB','GB','TB','PB','EB','ZB','YB'] : ['KiB','MiB','GiB','TiB','PiB','EiB','ZiB','YiB'];
	var u = -1;
	do {
		bytes /= thresh;
		++u;
	} while(bytes >= thresh);
	return bytes.toFixed(1)+' '+units[u];
};

// millisecondsToStr by Dan - http://stackoverflow.com/a/8212878
function millisecondsToStr (milliseconds) {
	function numberEnding (number) {
		return (number > 1) ? 's' : '';
	}

	var temp = Math.floor(milliseconds / 1000);

	var days = Math.floor((temp %= 31536000) / 86400);
	if (days) {
		return days + ' day' + numberEnding(days);
	}
	var hours = Math.floor((temp %= 86400) / 3600);
	if (hours) {
		return hours + ' hour' + numberEnding(hours);
	}
	var minutes = Math.floor((temp %= 3600) / 60);
	if (minutes) {
		return minutes + ' minute' + numberEnding(minutes);
	}
	var seconds = temp % 60;
	if (seconds) {
		return seconds + ' second' + numberEnding(seconds);
	}

	return 'now';
}

// modified version for increased accuracy
function millisecondsToAccurateStr (milliseconds) {
	function numberEnding (number) {
		return (number > 1) ? 's' : '';
	}

	var temp = Math.floor(milliseconds / 1000);
	var res = new Array();

	var days = Math.floor((temp %= 31536000) / 86400);
	if (days) {
		res.push(days + ' day' + numberEnding(days));
	}
	var hours = Math.floor((temp %= 86400) / 3600);
	if (hours) {
		res.push(hours + ' hour' + numberEnding(hours));
	}
	var minutes = Math.floor((temp %= 3600) / 60);
	if (minutes) {
		res.push(minutes + ' minute' + numberEnding(minutes));
	}
	var seconds = temp % 60;
	if (seconds) {
		res.push(seconds + ' second' + numberEnding(seconds));
	}

	return res.length > 0 ? res.join(' ') : 'now';
}

// textarea auto height
function textAreaAutoResize (obj) {
  if (obj.scrollHeight > obj.clientHeight) {
    obj.style.height = obj.scrollHeight + "px";
  }
}

const storageLoadAlbums = () => JSON.parse(localStorage.albums || '[]');

const storageSaveAlbums = albums => {
	albums = albums.sort((a, b) => a.expires - b.expires);
	localStorage.albums = JSON.stringify(albums);
	return albums;
};

function storageUpdateAlbum (album_id, data) {
	let albums = storageLoadAlbums();
	let album = albums.find(album => album.id == album_id);
	if (typeof album === 'undefined') {
		if (data) data.id = album_id;
		albums.push(data || {id: album_id});
	} else if (data) {
		Object.assign(album, data); // merge new data
	}
	storageSaveAlbums(albums);
	return albums;
}

const storageDeleteAlbum = album_id => storageSaveAlbums(storageLoadAlbums().filter(album => album.id != album_id));
