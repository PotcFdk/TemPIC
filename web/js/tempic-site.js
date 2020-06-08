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

// Detect browser capabilities
function isUpToDate () {
	return Modernizr.filereader && Modernizr.flexbox && window.FormData !== undefined
}

function canUseFormData () {
	return window.FormData !== undefined
}

function tempicOnLoad () {
	if (isUpToDate ())
		document.getElementById("browser_warning_text").style.display = 'none';
}

function resetTitle () {
	if ($.type(album_name) == 'string')
		document.title = album_name + " - " + instance_name;
	else
		document.title = instance_name;
}

function initRemainingLifetime (remaining)
{
	function updateRemainingLifetime () {
		let remaining = album_expires - Date.now()/1000;
		if (remaining > 0) {
			$('#lifetime_text').html('<p><span class="label label-info">Album removal</span> Remaining time: '
				+ millisecondsToAccurateStr (remaining*1000)+'</p>');
		} else {
			$('#lifetime_text').html('<p><span class="label label-danger">Removed</span> '
				+ 'This album has been removed.</p>');

			// if we're not currentry about to upload, or uploading, new files, leave the album page
			if (um.files.length == 0) {
				setInterval (function() {
					window.location = url_base;
				}, 1000);
			}
		}
	}
	updateRemainingLifetime();
	setInterval (updateRemainingLifetime, 1000);
}

function retryThumbnail (obj) {
	var src = obj.data('src');

	if (!src)
		throw new Error('Cannot retry thumbnail: Object has no src!');

	obj.data('failure', false);
	obj.attr('src', src);
}

function onThumbnailLoad (obj) {
	obj = $(obj);
	if (!obj.data('failure')) {
		obj.removeClass('no-thumbnail');
		obj.attr('alt', 'Uploaded Image');
	}
}

function onThumbnailError (obj) {
	obj = $(obj);

	if (!obj.data('src'))
		obj.data('src', obj.attr('src'));

	if (!obj.data('failure')) {
		obj.data('failure', true);
		obj.attr('alt', 'Thumbnail currently unavailable.');
		obj.addClass('no-thumbnail');
		obj.attr('src', 'img/ico_loading.gif');
		setTimeout(retryThumbnail, 30000, obj);
	}

	info('Some thumbnails have not yet been generated.');
}

var fileBrowse;

$(function() {
	$('#div_filelist_preview').hide();
	$('#div_albumname_input').hide();
	$('#div_albumdescription_input').hide();
	$('#div_warn_element').hide();
	$('#warn_element').hide();
	$('#div_progressbar').hide();
	$('#progressbar').hide();
	$('#div_progresstext').hide();
	$(".checksum-field").hide();
	$("#button-file-wipe").hide();
	$("#button-upload").hide();
	$("#button-abort").hide();

	var upload_in_progress = false;

	var base_text = $('#checksums-toggle').text();
	var is_showing_checksums = false;
	$('#checksums-toggle').on('click', function() {
		if (is_showing_checksums) {
			is_showing_checksums = false;
			$(".checksum-field").hide(300);
			$('#checksums-toggle').text(base_text);
		} else {
			is_showing_checksums = true;
			$(".checksum-field").show(300);
			$('#checksums-toggle').text(base_text.replace("Show","Hide"));
		}
	});

	var fileInput = $('#file');
	fileBrowse = function () {
		// fileInput show()/hide():
		//  compatibility hack (e.g. older android devices)

		if (!isUpToDate ())
			fileInput.show ();
		fileInput.click();
		if (!isUpToDate ())
			fileInput.hide ();
	}

	var upload_started = 0;

	function uploadProgress(evt) {
	  if (evt.lengthComputable) {
		var percentComplete = evt.loaded * 100 / evt.total;
		var percentCompleteStr = Math.round(percentComplete).toString() + " %";
		var duration = (Date.now() - upload_started);
		var speed = evt.loaded / duration;
		$('#progressbar').attr('value', percentComplete.toString());
		$('#progresstext').html("Uploading: " + percentCompleteStr + "<br />"
			+ humanFileSize(evt.loaded) + " / " + humanFileSize(evt.total) + " total"
				+ " @ " + humanFileSize((speed*1000).toFixed(2)) + " per second<br />"
			+ "Elapsed: " + millisecondsToStr(duration) + "<br />"
			+ 'ETA: ' + millisecondsToStr((evt.total - evt.loaded)/speed));
		document.title = percentCompleteStr + " - " + instance_name + " (uploading)";
	  }
	  else {
		$('#progressbar').removeAttr('value');
	  }
	}

	function uploadBegin() {
		upload_in_progress = true;
		window.onbeforeunload = function (e) {
			e = e || window.event;

			// For IE and Firefox prior to version 4
			if (e) {
				e.returnValue = 'Sure?';
			}

			// For Safari
			return 'Sure?';
		}

		$("#button-file-wipe").hide();
		$("#button-upload").hide();
		$("#button-abort").show();
		$('#button-browse').prop('disabled', true);

		$('#lifetime').prop('disabled', true);

		$('#div_progress').hide();

		$('#div_progressbar').show();
		$('#progressbar').show();
		$('#div_progresstext').show();

		$('#div_upload_data').slideUp(400, 'swing', function() {
			$('#div_progress').slideDown(400, 'swing');
		});
	}

	function uploadEnd() {
		upload_in_progress = false;

		resetTitle();

		$("#button-file-wipe").show();
		$("#button-upload").show();
		$("#button-abort").hide();
		$('#button-browse').prop('disabled', false);

		$('#lifetime').prop('disabled', false);

		$('#div_progress').slideUp(200, 'swing', function() {
			$('#div_progressbar').hide();
			$('#progressbar').hide();
			$('#div_progresstext').hide();
		});

		$('#div_upload_data').slideDown(200, 'swing');
	}

	function uploadComplete(evt) {
		window.onbeforeunload = undefined;

		if (evt.target.status == 200) {
			if (evt.target.responseText) {
				var response_obj;
				try {
					response_obj = JSON.parse(evt.target.responseText);
				} catch (e) {
					return uploadFailed(evt);
				}
				if (response_obj.success) {
					if (response_obj.album_id)
						storageUpdateAlbum(response_obj.album_id, {
							name: um.album_name,
							file_count: um.files.length,
							own_upload: true
						});
					window.location = response_obj.location || album_url;
				} else if (response_obj.error_type == 'auth') {
					$("#upload-deny_element").show();
					return uploadEnd();
				} else
					return uploadFailed(evt);
			} else {
				window.location = url_base;
			}
		} else if (evt.target.status == 401) {
			$("#upload-deny_element").show();

			var response_obj;
			try {
				response_obj = JSON.parse(evt.target.responseText);
			} catch (e) {
				return uploadEnd();
			}
			if (response_obj.location)
				window.location = response_obj.location;

			return uploadEnd();
		} else {
			return uploadFailed(evt);
		}
	}

	function uploadFailed(evt) {
		warn("There was an error attempting to upload the file.");
		uploadEnd();
	}

	function uploadCanceled(evt) {
		warn("The upload has been canceled by the user or the browser dropped the connection.");
		uploadEnd();
	}

	// UploadManager observers

	function observerFilePreview(files) {
		if (files.length) {
			$("#div_filelist_preview_box").empty();

			for (var x = 0; x < files.length; x++)
			{
				var entry = document.createElement("div");
					entry.setAttribute("class", "row");
				var col = document.createElement("div");
					col.setAttribute("class", "col-md-12");

				var inner_entry = document.createElement("div")
					inner_entry.setAttribute("class", "file-preview-entry");
					inner_entry.setAttribute("onclick", "um.delFile(".concat(x,")"));

				var button = document.createElement("button");
					button.setAttribute("class", "btn-file-remove-base btn-file-remove");
					button.setAttribute("type", "button");
				var txt = document.createTextNode("\u00D7");
				button.appendChild(txt);

				var filename = document.createTextNode(files[x].name);
				var filesize = document.createElement("span");
				filesize.setAttribute("class", "text-muted");
				filesize.innerHTML = " (" + humanFileSize(files[x].size, true) + ")";

				inner_entry.appendChild(button);
				inner_entry.appendChild(filename);
				inner_entry.appendChild(filesize);

				col.appendChild(inner_entry)

				entry.appendChild(col);

				document.getElementById("div_filelist_preview_box").appendChild(entry);
			}

			$("#div_filelist_preview").show();
		}
		else
			$("#div_filelist_preview").hide();
	}

	function observerAlbumForm(files) {
		if(files.length) {
			$('#div_albumname_input').show();
			$('#div_albumdescription_input').show();
		}
		else {
			$('#div_albumname_input').hide();
			$('#div_albumdescription_input').hide();
		}
	}

	function observerFileOverview(files) {
		if(files.length == 1)
			$("#file-overview-text").text(files[0].name.toString().concat(" (", humanFileSize(um.getUploadSize(), true), ")"));
		else if(files.length > 1)
			$("#file-overview-text").text(files.length.toString().concat(" files ready to upload (", humanFileSize(um.getUploadSize(), true),")"));
		else
			$("#file-overview-text").text("");
	}

	function observerFileWipeButton(files) {
		if(files.length)
			$("#button-file-wipe").show();
		else
			$("#button-file-wipe").hide();
	}

	function observerUploadButton(files) {
		if(files.length)
			$("#button-upload").show();
		else
			$("#button-upload").hide();
	}

	// UploadManager - global

	um = new UploadManager(size_limit, url_base, uploadProgress, uploadComplete, uploadFailed, uploadCanceled);
	um.registerFileObserver(observerFileOverview);
	um.registerFileObserver(observerFileWipeButton);
	um.registerFileObserver(observerUploadButton);
	um.registerFileObserver(observerFilePreview)
	um.registerFileObserver(observerAlbumForm);

	// File upload form setup.

	$("[data-hide]").on("click", function(){
		$("." + $(this).attr("data-hide")).hide();
	});

	//Add eventListeners to input -> add values to UploadManager on change

	$("#file").on("change", function(e){
		var files = this.files;
		for(var x = 0; x < files.length; x++)
		{
			um.addFile(files[x]);
		}

		// reset input, as input type=file is read only
		// (!) compatibility hack: don't do this when FormData doesn't work
		if (canUseFormData ()) {
			$(this).wrap('<form>').closest('form').get(0).reset();
			$(this).unwrap();
		}
	});

	$("#lifetime").on("change", function(e){
		um.setLifetime(this.value);
	});

	$("#album_name").on("change", function(e){
		um.setAlbumName(this.value);
	});

	$("#album_description").on("change", function(e){
		um.setAlbumDescription(this.value);
	});

	// Drag&Drop feature setup

	dragCounter = 0; //necessary for entering/leaving child elements of html while dragging stuff

	$("html").on("dragenter", function(e){
		dragCounter++;
		e.preventDefault();
		if (upload_in_progress) return;
		$(this).addClass("draghover");
	});

	$("html").on("dragover", function(e){
		e.preventDefault();
	});

	$("html").on("dragend", function(e){
		e.preventDefault();
		dragCounter = 0;
		$(this).removeClass("draghover");
	});

	$("html").on("dragleave", function(e){
		dragCounter--;
		e.preventDefault();
		if(dragCounter == 0)
			$(this).removeClass("draghover");
	});

	$("html").on("drop", function(e){
		e.preventDefault();
		dragCounter = 0;
		$(this).removeClass("draghover");
		if (upload_in_progress) return;
		var files = e.originalEvent.dataTransfer.files;
		for (var x = 0; x < files.length; x++)
		{
			um.addFile(files[x]);
		}
	});

	$("#button-file-wipe").on("click", function(e){
		um.wipeFiles();
	});

	$("#button-abort").on("click", function(e){
		um.abort();
	});

	var btn = $('button[type=submit]');
	btn.prop('type', 'button');
	btn.on('click', function() {
		warn(); // Close the current warning, if applicable.

		um.send(um.makePOSTData());
		upload_started = Date.now();
		uploadBegin();
	});

	const albumlist = $('#albumlist');
	let albums = storageLoadAlbums();

	// check if the current album needs to be added to the storage
	if (typeof album_id !== 'undefined') { // do we even have an album opened?
		// update the stored data just in case it's wrong for some reason
		// e.g. the expiry time might have been updated
		// or if it's incomplete (e.g. if it was our own upload)
		albums = storageUpdateAlbum(album_id, {
			name: typeof album_name === 'string' ? album_name : undefined,
			file_count: $('.panel-file').length,
			expires: album_expires * 1000
		});
	}

	// create all list entries for the previously seen albums
	// don't consider the currently open album, though
	if (albums.filter(album => typeof album_id === 'undefined' || album.id != album_id).length == 0) {
		$('.footer').hide();
	} else
		albums.forEach(album => {
			let li = $('<li/>')
				.addClass('row')
				.attr('data-albumid', album.id)
				.appendTo(albumlist);

			if (album.own_upload)
				li.attr('data-own-upload', 'true');
			if (typeof album_id !== 'undefined' && album.id == album_id)
				li.attr('data-current-album', 'true');

			let item = $('<a/>')
				.attr('href', album_url + album.id)
				.appendTo(li);

			let label = $('<label/>')
				.addClass('col-md-7')
				.appendTo(item);

			if (album.name)
				$('<span/>')
					.addClass('albumname')
					.text(album.name)
					.appendTo(label);

			let id = $('<span/>')
				.addClass('albumid')
				.text(album.id)
				.appendTo(label);

			let file_count = $('<span/>')
				.addClass('albumfilecnt')
				.text(album.file_count)
				.attr('data-value', album.file_count)
				.appendTo(label);

			let expires = album.expires;

			let remaining = () => {
				let album_entry = albums.find(a => a.id == album.id);
				if (album_entry)
					expires = album_entry.expires;
				return expires && expires - Date.now();
			};
			let span = $('<span/>')
				.addClass('albumexpires')
				.addClass('col-md-4')
				.appendTo(item);
			const update = () => {
				const r = remaining();
				if (typeof r === 'undefined') return;
				span.text(millisecondsToAccurateStr(Math.abs(r)));
				if (r > 0)
					span.removeAttr('data-expired');
				else
					span.attr('data-expired', 'expired');
				if (typeof albums.find(a => a.id == album.id) === 'undefined')
					li.attr('data-gone', 'gone');
			};
			update();
			setInterval(update, 1000);
		});

	// periodically check all expired albums for existence
	// and remove if gone
	const check_for_expired_albums = () => {
		albums
			.filter(album => album.expires < Date.now() || typeof album.expires === 'undefined')
			.forEach(album => {
				fetch(url_base + '/api.php?v1/albums/' + album.id.replace(/[^\w:]/g,'') + '/info')
					.then(response => {
						if (response.status === 404) {
							albums = storageDeleteAlbum(album.id);
						} else if (response.status === 200) {
							response.json().then(function(data) {
								if (data && data.status == 'success' && data.data && data.data.albums && data.data.albums[album.id]) {
									albums = storageUpdateAlbum(album.id, {
										name: data.data.albums[album.id].name,
										file_count: Object.keys(data.data.albums[album.id].files).length,
										expires: data.data.albums[album.id].expires*1000
									});
								}
							});
						}
					});
			})
	}
	setTimeout(check_for_expired_albums, 5*1000);
	setInterval(check_for_expired_albums, 60*1000);

	// remove invalid albums
	setTimeout(() => {
		albums
			.filter(album => typeof album.id !== 'string')
			.map(album => album.id)
			.forEach(storageDeleteAlbum);
	}, 5*1000);
});