/*
	TemPIC - Copyright (c) PotcFdk, 2014 - 2015

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
window.onload = function() {
	if (!Modernizr.filereader || !Modernizr.partialflexbox || window.FormData === undefined)
		document.getElementById("browser_warning_text").innerHTML = 'Your browser seems to be heavily outdated. Please consider updating. As a workaround you can use <a href="index_nojs.php'
			+ (album_id ? '?album=' + album_id : '')
			+ '">the NoJS version</a>.';
	else
		document.getElementById("browser_warning_text").style.display = 'none';
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

function onThumbnailError (obj, icon) {
	obj = $(obj);
	obj.parent().parent().attr('style', 'text-align: center');
	obj.replaceWith("<img src='" + icon + "' alt='thumbnail unavailable' /><br />"
		+ "<span style='font-size: 1.7em;'>[thumbnail unavailable]</span>");
	info ('Some thumbnails have not yet been generated.');
}

var fileBrowse;

$(function() {
	$('#div_filelist_preview').hide();
	$('#div_albumname_input').hide();
	$('#div_albumdescription_input').hide();
	$('#div_info_element').hide();
	$('#info_element').hide();
	$('#div_warn_element').hide();
	$('#warn_element').hide();
	$('#div_progressbar').hide();
	$('#progressbar').hide();
	$('#div_progresstext').hide();
	$(".checksum-field").hide();
	$("#button-file-wipe").hide();
	$("#button-upload").hide();

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
		fileInput.click();
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

	function uploadComplete(evt) {
		if (evt.target.responseText) {
			window.location = album_url + evt.target.responseText;
		} else {
			window.location = url_base;
		}
	}

	function uploadFailed(evt) {
	  warn("There was an error attempting to upload the file.");
	}

	function uploadCanceled(evt) {
	  warn("The upload has been canceled by the user or the browser dropped the connection.");
	}				

	function showFilePreview(files) {
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
	
	function showAlbumForm(files) {
		if(files.length) {
			$('#div_albumname_input').show();
			$('#div_albumdescription_input').show();
		}
		else {
			$('#div_albumname_input').hide();
			$('#div_albumdescription_input').hide();
		}
	}
	
	function updateFileOverview(files) {
		if(files.length == 1)
			$("#file-overview-text").text(files[0].name);
		else if(files.length > 1)
			$("#file-overview-text").text(files.length.toString().concat(" files ready to upload"));
		else
			$("#file-overview-text").text("");
	}

	function showFileWipeButton(files) {
		if(files.length)
			$("#button-file-wipe").show();
		else
			$("#button-file-wipe").hide();
	}
	
	function showUploadButton(files) {
		if(files.length)
			$("#button-upload").show();
		else
			$("#button-upload").hide();
	}
	//UploadManager - global
	
	um = new UploadManager(size_limit, url_base, uploadProgress, uploadComplete, uploadFailed, uploadCanceled);
	um.registerFileObserver(updateFileOverview);
	um.registerFileObserver(showFileWipeButton);
	um.registerFileObserver(showUploadButton);
	um.registerFileObserver(showFilePreview)
	um.registerFileObserver(showAlbumForm);
	
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
		//reset input with js hacks (input type=file is read only)
		$(this).wrap('<form>').closest('form').get(0).reset();
		$(this).unwrap();
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
		var files = e.originalEvent.dataTransfer.files;
		for (var x = 0; x < files.length; x++)
		{
			um.addFile(files[x]);
		}
	});

	$("#button-file-wipe").on("click", function(e){
		um.wipeFiles();
	});
	
	var btn = $('button[type=submit]');
	btn.prop('type', 'button');
	btn.on('click', function() {
		um.send(um.makePOSTData());
		
		upload_started = Date.now();
		
		$('#div_progressbar').show();
		$('#progressbar').show();
		$('#div_progresstext').show();
	});
});