/*
	TemPIC - Copyright (c) PotcFdk, 2014 - 2017

	TemPIC UploadManager (c) PotcFdk, 2015
	TemPIC UploadManager (c) KeyLimePie, 2015

	This file is based on uploadmanager.js ('UploadManager for TemPIC')
	by KeyLimePie, with some later modifications by PotcFdk.

	Original copyright notice:
	 UploadManager for TemPIC - Copyright  2015; KeyLimePie (GitHub: TheKeyLimePie); based on work by PotcFdk

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

function UploadManager(fileSizeLimit, baseurl, uploadProgress, uploadComplete, uploadFailed, uploadCanceled)
{
	this.UPLOADPATH = baseurl.concat("/upload.php");
	this.SIZELIMIT = fileSizeLimit;
	
	this.files = new Array(); //contains objects of type "File"
	this.lifetime = "";
	this.album_name = "";
	this.album_description = "";
	
	this.xhr;
	this.uploadProgress = uploadProgress;
	this.uploadComplete = uploadComplete;
	this.uploadFailed = uploadFailed;
	this.uploadCanceled = uploadCanceled;
	this.fileObserver = new Array(); //Observer pattern sucks in javascript
}

UploadManager.prototype.setAlbumName = function(name)
{
	this.album_name = name;
}

UploadManager.prototype.setAlbumDescription = function(description)
{
	this.album_description = description;
}

UploadManager.prototype.setLifetime = function(lifetime)
{
	this.lifetime = lifetime;
}

UploadManager.prototype.addFile = function(file)
{
	if(file.size > this.SIZELIMIT)
	{
		var warning = "The file you added exceeds the file size limit:<br />";
		warn(warning.concat(file.name));
	}
	else
	{
		var duplicate = false;
		for(var x = 0; x < this.getFiles().length; x++)
		{
			if(file.name == this.getFiles()[x].name)
			{
				duplicate = this.getFiles()[x].name;
				break;
			}
		}
		if(duplicate)
			warn("Duplicate filename: " + duplicate);
		else
			this.files.push(file);
	}	
	
	this.notifyFileObserver();
}

UploadManager.prototype.delFile = function(i)
{
	this.files.splice(i,1);
	this.notifyFileObserver();
}

UploadManager.prototype.wipeFiles = function()
{
	this.files = new Array();
	this.notifyFileObserver();
}

UploadManager.prototype.getFiles = function()
{
	return this.files;
}

UploadManager.prototype.getNumberOfFiles = function()
{
	return this.files.length;
}

UploadManager.prototype.reset = function()
{
	this.files = new Array();
	this.notifyFileObserver();
	this.lifetime = "";
	this.album_name = "";
	this.album_description = "";	
}

UploadManager.prototype.makePOSTData = function()
{
	var fd = new FormData();
	
	fd.append("ajax", "true");
	fd.append("lifetime", this.lifetime);
	fd.append("album_description", this.album_description);
	fd.append("album_name", this.album_name);
	
	for(var x = 0; x < this.files.length; x++)
	{
		fd.append("file[]", this.files[x]);
	}
	
	return fd;
}

UploadManager.prototype.send = function(data)
{
	this.abort();
	this.xhr = new XMLHttpRequest();
	
	this.xhr.upload.addEventListener("progress", this.uploadProgress, false);
	this.xhr.addEventListener("load", this.uploadComplete, false);
	this.xhr.addEventListener("error", this.uploadFailed, false);
	this.xhr.addEventListener("abort", this.uploadCanceled, false);
	
	this.xhr.open("POST", this.UPLOADPATH);
	this.xhr.send(data);
}

UploadManager.prototype.abort = function()
{
	if(this.xhr) this.xhr.abort();
}

UploadManager.prototype.registerFileObserver = function(obs)
{
	this.fileObserver.push(obs);
}

UploadManager.prototype.removeFileObserver = function(obs)
{
	for(var x = 0; x < this.fileObserver.length; x++)
	{
		if(this.fileObserver[x] === obs)
			this.fileObserver.splice(x,1);
	}
}

UploadManager.prototype.notifyFileObserver = function()
{
	for(var x = 0; x < this.fileObserver.length; x++)
	{
		this.fileObserver[x](this.files);
	}
}