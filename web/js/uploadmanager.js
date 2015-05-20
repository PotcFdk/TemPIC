/*
	UploadManager for TemPIC - Copyright  2015; KeyLimePie (GitHub: TheKeyLimePie)

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

function UploadManager(baseurl, uploadProgress, uploadComplete, uploadFailed, uploadCanceled)
{
	this.UPLOADPATH = baseurl.concat("/upload.php");
	
	this.files = new Array(); //contains objects of type "File"
	this.lifetime = "";
	this.album_name = "";
	this.album_description = "";
	
	this.xhr;
	this.uploadProgress = uploadProgress;
	this.uploadComplete = uploadComplete;
	this.uploadFailed = uploadFailed;
	this.uploadCanceled = uploadCanceled;
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
	this.files.push(file);
}

UploadManager.prototype.delFile = function(i)
{
	this.files.splice(i,1);
}

UploadManager.prototype.wipeFiles = function()
{
	this.files = new Array();
}

UploadManager.prototype.reset = function()
{
	this.files = new Array();
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
	if(xhr) xhr.abort();
	var xhr = new XMLHttpRequest();
	
	xhr.upload.addEventListener("progress", this.uploadProgress, false);
	xhr.addEventListener("load", this.uploadComplete, false);
	xhr.addEventListener("error", this.uploadFailed, false);
	xhr.addEventListener("abort", this.uploadCanceled, false);
	
	xhr.open("POST", this.UPLOADPATH);
	xhr.send(data);
}