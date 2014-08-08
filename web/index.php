<?php
@include_once('config.php');
require_once('../includes/configcheck.php');
session_start();
?>
<!doctype html>
<!--
	TemPIC - Copyright 2014 PotcFdk, ukgamer

	Licensed under the Apache License, Version 2.0 (the "License");
	you may not use this file except in compliance with the License.
	You may obtain a copy of the License at
	
	http://www.apache.org/licenses/LICENSE-2.0
	
	Unless required by applicable law or agreed to in writing, software
	distributed under the License is distributed on an "AS IS" BASIS,
	WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
	See the License for the specific language governing permissions and
	limitations under the License.
-->
<html>
	<head>
		<meta charset="utf-8">
		<title><?php echo $INSTANCE_NAME; ?></title>

		<link rel="stylesheet" href="css/bootstrap.min.css">
		<link href="css/fileinput.min.css" media="all" rel="stylesheet" type="text/css" />
		<link href="css/copyrotate.css" media="all" rel="stylesheet" type="text/css" />
		<link href="css/tempic-front.css" media="all" rel="stylesheet" type="text/css" />

		<script src="js/jquery-2.1.0.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/fileinput.min.js"></script>

		<script>
			function warn(text) {
				var warn_element = $("#warn_element");
				var warn_element_text = $("#warn_element_text");
				warn_element_text.html(text);
				warn_element.show();
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
			
			$(function() {
				$("#warn_element").hide();
				$('#progressbar').hide();
				
				// File upload form setup.
				
			    $("[data-hide]").on("click", function(){
					$("." + $(this).attr("data-hide")).hide();
				});
				
				$("#file").fileinput({
				  "showPreview" : false
				});
				
				$('#file').bind('change', function() {
					var warning = "(At least) one of the files you added exceeds the file size limit:";
					var show = false;
					
					for (var i = 0; i < this.files.length; ++i)
					{
						var file = this.files[i];
						if (file.size > <?php echo $SIZE_LIMIT; ?>)
						{
							show = true;
							warning += "<br />" + file.name;
						}
					}
					
					if (show) warn(warning);
				});
				
				var upload_started = 0;
				var xhr;
				
				function uploadProgress(evt) {
				  if (evt.lengthComputable) {
					var percentComplete = Math.round(evt.loaded * 100 / evt.total);
					var duration = (Date.now() - upload_started);
					var speed = evt.loaded / duration;
					$('#progressbar').attr('value', percentComplete.toString());
					$('#progresstext').html("Uploading: " + percentComplete.toString() + " %<br />"
						+ humanFileSize(evt.loaded) + " / " + humanFileSize(evt.total) + " total<br />"
						+ 'ETA: ' + millisecondsToStr((evt.total - evt.loaded)/speed))
					document.title = "<?php echo $INSTANCE_NAME; ?> - uploading " +  percentComplete.toString() + " %";
				  }
				  else {
					$('#progressbar').removeAttr('value');
				  }
				}

				function uploadComplete(evt) {
					window.location.reload();
				}

				function uploadFailed(evt) {
				  warn("There was an error attempting to upload the file.");
				}

				function uploadCanceled(evt) {
				  warn("The upload has been canceled by the user or the browser dropped the connection.");
				}				
				
				var btn = $('button[type=submit]');
				btn.prop('type', 'button');
				btn.on('click', function() {
					if(xhr) xhr.abort();
					xhr = new XMLHttpRequest();
					var fd = new FormData($('#file-form')[0]);
					fd.append('ajax', 'true');
	
					xhr.upload.addEventListener("progress", uploadProgress, false);
					xhr.addEventListener("load", uploadComplete, false);
					xhr.addEventListener("error", uploadFailed, false);
					xhr.addEventListener("abort", uploadCanceled, false);
					
					xhr.open("POST", "upload.php");
					xhr.send(fd);
					upload_started = Date.now();
					
					$('#progressbar').show();
				});
			});
		</script>
	</head>
	<body>
		<?php include('../includes/copyrotate.php'); ?>
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<div class="page-header">
						<h1><a href="<?php echo $URL_BASE; ?>"><?php echo $INSTANCE_NAME; ?></a></h1>
					</div>

					<div class="row">
						<noscript>
							<p>This site is best viewed with JavaScript. If you don't want to turn on JavaScript, please use <a href="index_nojs.php">the NoJS version</a>.</p>
						</noscript>
					</div>
					
					<form id="file-form" class="form-horizontal" method="post" action="upload.php" enctype="multipart/form-data">
						<div class="form-group">
							<label for="file" class="col-md-1 control-label">Files</label>
							<div class="col-md-8">
								<input class="file" type="file" name="file[]" id="file" multiple="multiple">
							</div>
							<div class="col-md-3">
								<select class="form-control" name="lifetime">
								<?php foreach ($LIFETIMES as $id => $data) : ?>
									<option value="<?php echo $id; ?>"><?php echo $data['name']; ?></option>
								<?php endforeach; ?>
								</select>
							</div>
						</div>
					</form>

					<div class="row">
						<div class="col-md-8 col-md-offset-1">
							<progress id="progressbar" max="100" value="0"></progress>
						</div>
					</div>
					
					<div class="row">
						<div class="col-md-8 col-md-offset-1">
							<p id="progresstext"></p>
						</div>
					</div>
					
					<div class="row">
						<div class="col-md-6 col-md-offset-3">
							<div id="warn_element" class="std-hide alert alert-danger alert-dismissable">
								<button type="button" class="close" data-hide="alert" aria-hidden="true">&times;</button>
								<p id="warn_element_text"></p>
							</div>
						</div>
					</div>
					
					<?php if (!empty($_SESSION['files'])) : ?>
						<?php $count = 0; ?>
						<?php foreach ($_SESSION['files'] as $name => $file) : ?>
							<?php if ($count % 3 == 0) : ?><div class="row"><?php endif; ?>
								<div class="col-md-4">
									<?php if (!empty($file['error'])) : ?>
		                                <div class="alert alert-danger alert-dismissable">
		                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		                                    Error uploading "<?php echo $name; ?>": <?php echo $file['error']; ?>
		                                </div>
		                            <?php else: ?>
										<div class="panel panel-default">
											<div class="panel-body">
												<a href="<?php echo $file['link']; ?>">
	                                                <?php if ($file['image']) : ?>
	                                                    <img src="<?php echo $file['link']; ?>" alt="Uploaded Image" class="thumbnail img-responsive">
	                                                <?php else: ?>
	                                                	<?php $image = $URL_BASE . '/img/filetypes/' . (file_exists('img/filetypes/' . $file['extension'] . '.png') ? $file['extension'] : '_blank') . '.png'; ?>
														<img src="<?php echo $image; ?>" alt="Uploaded File" class="img-responsive">
	                                                <?php endif; ?>

	                                                <p><?php echo htmlspecialchars($name); ?></p>
												</a>
											</div>
										</div>
									<?php endif; ?>
								</div>

							<?php if ($count % 3 == 2) : ?></div><?php endif; ?>
							<?php $count++; ?>
						<?php endforeach; ?>

						<?php if ($count % 3 != 0) : ?></div><?php endif; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</body>
</html>

<?php unset($_SESSION['files']); ?>
