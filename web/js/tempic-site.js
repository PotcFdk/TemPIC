/*
	TemPIC - Copyright (c) PotcFdk, 2014 - 2016

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
		if (remaining > 0) {
			$('#lifetime_text').html('<p><span class="label label-info">Album removal</span> Remaining time: '
				+ millisecondsToAccurateStr (remaining*1000)+'</p>');
			-- remaining;
		} else {
			$('#lifetime_text').html('<p><span class="label label-danger">Removed</span> '
				+ 'This album has been removed.</p>');
			setInterval (function() { window.location = url_base; }, 1000);
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
		if (evt.target.status == 200) {
			if (evt.target.responseText) {
				var response_obj;
				try {
					response_obj = JSON.parse(evt.target.responseText);
				} catch (e) {
					return uploadFailed(evt);
				}
				if (response_obj.success)
					window.location = response_obj.location || album_url;
				else if (response_obj.error_type == 'auth')
				{
					$("#upload-deny_element").show();
					return uploadEnd();
				}
				else
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

				inner_entry.appendChild(button);
				inner_entry.appendChild(filename);
				
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
			$("#file-overview-text").text(files[0].name);
		else if(files.length > 1)
			$("#file-overview-text").text(files.length.toString().concat(" files ready to upload"));
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
});