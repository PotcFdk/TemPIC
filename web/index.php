<?php
@include_once('config.php');
require_once('../includes/configcheck.php');
require_once('../includes/baseconfig.php');
require_once('../includes/helpers.php');

// Set the httpOnly flag.
session_set_cookie_params(0, null, null, null, true);
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
<?php
	// Make sure $files, $album_id, $album_hash and $remaining_time contain the data we want.

	if (!empty($_SESSION['files'])) {
		$files = $_SESSION['files'];
		
		if (!empty($_SESSION['album_name']))
			$album_name = $_SESSION['album_name'];

		if (!empty($_SESSION['album_description']))
			$album_description = $_SESSION['album_description'];

		if (!empty($_SESSION['album_id'])) {
			$album_id = $_SESSION['album_id'];
			$_a = explode(":", $album_id, 2);
			$album_hash = $_a[1];
		}
		
		if (!empty($_SESSION['album_lifetime'])) {
			$album_lifetime = $_SESSION['album_lifetime'];
			if (!empty($LIFETIMES[$album_lifetime]))
				$remaining_time = $LIFETIMES[$_SESSION['album_lifetime']]['time']*60;
		}
		
		if (!empty($album_lifetime) && !empty($LIFETIMES[$album_lifetime]))
			$remaining_time = $LIFETIMES[$_SESSION['album_lifetime']]['time']*60;
	}

	if (empty($files) && isset($_GET['album'])) {
		$album_id = strip_album_id($_GET['album']);
		if (!empty($album_id)) {
			$_a = explode(":", $album_id, 2);
			if (!empty($_a[0]))
				$album_lifetime = $_a[0];
			if (!empty($_a[1]))
				$album_hash = $_a[1];
			
			if (!empty($LIFETIMES[$album_lifetime]) && file_exists($PATH_ALBUM.'/'.$album_lifetime.'/'.$album_hash.'.txt')) {
				$time  = time ();
				$album_data = unserialize(file_get_contents($PATH_ALBUM.'/'.$album_lifetime.'/'.$album_hash.'.txt'));
				if (!empty($album_data) && !empty($album_data['files'])) {
					if (!empty($album_data['name']))
						$album_name = $album_data['name'];
					if (!empty($album_data['description']))
						$album_description = $album_data['description'];
					$files = $album_data['files'];
				}
				$remaining_time = $LIFETIMES[$album_lifetime]['time']*60 - ($time - filemtime ($PATH_ALBUM.'/'.$album_lifetime.'/'.$album_hash.'.txt'));
			}
		}
	}
?>
<html>
	<head>
		<meta charset="utf-8">
		<title><?php if (!empty($album_name)) { echo htmlspecialchars($album_name, ENT_QUOTES).' - '; }
			echo $INSTANCE_NAME; ?></title>

		<link rel="stylesheet" href="<?php echo $URL_BASE; ?>/css/bootstrap.min.css">
		<link href="<?php echo $URL_BASE; ?>/css/fileinput.min.css" media="all" rel="stylesheet" type="text/css" />
		<link href="<?php echo $URL_BASE; ?>/css/copyrotate.css" media="all" rel="stylesheet" type="text/css" />
		<link href="<?php echo $URL_BASE; ?>/css/tempic-front.css" media="all" rel="stylesheet" type="text/css" />

		<script src="<?php echo $URL_BASE; ?>/js/jquery-2.1.0.min.js"></script>
		<script src="<?php echo $URL_BASE; ?>/js/bootstrap.min.js"></script>
		<script src="<?php echo $URL_BASE; ?>/js/fileinput.min.js"></script>
		<script src="<?php echo $URL_BASE; ?>/js/tempic-helpers.js"></script>
		<script src="<?php echo $URL_BASE; ?>/js/modernizr-p1.js"></script>
		
		<style>
			@font-face {
				font-family: 'Open Sans';
				font-style: normal;
				font-weight: 400;
				src: local('Open Sans'), local('OpenSans'), url('<?php echo $URL_BASE; ?>/fonts/opensans.woff') format('woff');
			}
		</style>

		<script>	
			// Detect browser capabilities
			window.onload = function() {
				if (!Modernizr.filereader || !Modernizr.partialflexbox || window.FormData === undefined)
					document.getElementById("browser_warning_text").innerHTML = 'Your browser seems to be heavily outdated. Please consider updating. As a workaround you can use <a href="index_nojs.php<?php if (!empty($album_id)) echo '?album='.$album_id; ?>">the NoJS version</a>.';
				else
					document.getElementById("browser_warning_text").style.display = 'none';
			}
			
			$(function() {
				$('#div_albumname_input').hide();
				$('#div_albumdescription_input').hide();
				$('#div_warn_element').hide();
				$('#warn_element').hide();
				$('#div_progressbar').hide();
				$('#progressbar').hide();
				$('#div_progresstext').hide();
				$(".checksum-field").hide();
				
				<?php // Show album lifetime, if possible.
					if (!empty($remaining_time)) : ?>									
						var remaining = <?php echo($remaining_time); ?>;
						function updateRemainingLifetime () {
							if (remaining > 0) {
								$('#lifetime_text').html('<p><span class="label label-info">Album removal</span> Remaining time: '
									+ millisecondsToAccurateStr(remaining*1000)+'</p>');
								-- remaining;
							} else {
								$('#lifetime_text').html('<p><span class="label label-danger">Removed</span> '
									+ 'This album has been removed.</p>');
								setInterval(function() { window.location = "<?php echo $URL_BASE; ?>"; }, 1000);
							}
						}
						updateRemainingLifetime();
						setInterval(updateRemainingLifetime, 1000);
				<?php endif; ?>

				$('#checksums-toggle').change(function() {
					if ($(this).is(':checked')) {
						$(".checksum-field").show(300);
					} else {
						$(".checksum-field").hide(300);
					}
				});

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
					else if (this.files.length > 0)	{
						$('#div_albumname_input').show();
						$('#div_albumdescription_input').show();
					}
					else {
						$('#div_albumname_input').hide();
						$('#div_albumdescription_input').hide();
					}
				});
				
				var upload_started = 0;
				var xhr;
				
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
					document.title = "<?php echo $INSTANCE_NAME; ?> - " + percentCompleteStr + " (uploading)";
				  }
				  else {
					$('#progressbar').removeAttr('value');
				  }
				}

				function uploadComplete(evt) {
					if (evt.target.responseText) {
						window.location = "<?php echo get_album_url(); ?>" + evt.target.responseText;
					} else {
						window.location = "<?php echo $URL_BASE; ?>";
					}
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
					
					xhr.open("POST", "<?php echo $URL_BASE; ?>/upload.php");
					xhr.send(fd);
					upload_started = Date.now();
					
					$('#div_progressbar').show();
					$('#progressbar').show();
					$('#div_progresstext').show();
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
							<p>This site is best viewed with JavaScript. If you don't want to turn on JavaScript, please use <a href="index_nojs.php<?php if (!empty($album_id)) echo '?album='.$album_id; ?>">the NoJS version</a>.</p>
						</noscript>
						<p id="browser_warning_text"></p>
					</div>
					
					<form id="file-form" class="form-horizontal" method="post" action="<?php echo $URL_BASE; ?>/upload.php" enctype="multipart/form-data">
						<div class="form-group">
							<label for="file" class="col-md-1 control-label">Files</label>
							<div class="col-md-8">
								<input class="file" type="file" name="file[]" id="file" multiple="multiple">
							</div>
							<div class="col-md-3">
								<select class="form-control" name="lifetime">
								<?php foreach ($LIFETIMES as $id => $data) : ?>
									<option value="<?php echo $id; ?>"<?php
										if (isset($DEFAULT_LIFETIME) && $DEFAULT_LIFETIME == $id)
											echo ' selected';
									?>><?php echo $data['name']; ?></option>
								<?php endforeach; ?>
								</select>
							</div>
						</div>
						<div class="row" id="div_albumname_input">
							<label for="file" class="col-md-1 control-label">Name</label>
							<div class="col-md-8">
								<input type="text" class="form-control" name="album_name" id="album_name">
							</div>
						</div>
						<div class="row" id="div_albumdescription_input">
							<label for="file" class="col-md-1 control-label">Info</label>
							<div class="col-md-8">
								<textarea class="form-control"
									name="album_description" id="album_description"
									placeholder="Album Description">
								</textarea>
							</div>
						</div>
					</form>

					<div class="row" id="div_progressbar">
						<div class="col-md-8 col-md-offset-1">
							<progress id="progressbar" max="100" value="0"></progress>
						</div>
					</div>
					
					<div class="row" id="div_progresstext">
						<div class="col-md-8 col-md-offset-1">
							<p id="progresstext"></p>
						</div>
					</div>
					
					<div class="row" id="div_warn_element">
						<div class="col-md-6 col-md-offset-3">
							<div id="warn_element" class="std-hide alert alert-danger alert-dismissable">
								<button type="button" class="close" data-hide="alert" aria-hidden="true">&times;</button>
								<p id="warn_element_text"></p>
							</div>
						</div>
					</div>
					
					<?php if (isset($_GET['404']) || (!empty($album_id) && empty($files))) : // 404 or bad album id ?>
						<div class="row">
							<div class="col-md-6 col-md-offset-3">
								<div id="404_element" class="alert alert-danger alert-dismissable">
									<button type="button" class="close" data-hide="alert" aria-hidden="true">&times;</button>
									<p id="404_element_text">The requested file could not be found!<br />
									It may have been removed or it never existed in first place.</p>
								</div>
							</div>
						</div>
					<?php endif; ?>

					<?php if (!empty($album_name)) : ?>
						<div class="row">
							<div class="col-md-12">
								<h3 class="album-name" id="albumname_text">Album: <?php echo htmlspecialchars($album_name, ENT_QUOTES); ?></h3>
							</div>
						</div>
					<?php endif; ?>
					
					<?php if (!empty($album_lifetime) && !empty($album_hash)
						&& file_exists($PATH_ALBUM.'/'.$album_lifetime.'/'.$album_hash.'.zip')) : ?>
						<div class="row">
							<div class="col-md-12">
								<p><span class="label label-info">Zip File</span> You can <a href="<?php
									echo $URL_BASE.'/'.$PATH_ALBUM.'/'.$album_lifetime.'/'.$album_hash.'.zip';
								?>">download the entire album as a zip file</a>!</p>
							</div>
						</div>
					<?php endif; ?>
					
					<div class="row">
						<div class="col-md-12">
							<p id="lifetime_text"></p>
						</div>
					</div>
					
					<?php if (!empty($files)) : ?>
					<div class="row">
						<div class="col-md-12">
							<p id="checksum_toggle_text"><span class="label label-info">Checksums</span> <input type="checkbox" id="checksums-toggle"> Show file checksums</p>
						</div>
					</div>
					<?php if (!empty($album_description)) : ?>
						<div class="row">
							<div class="col-md-12">
								<h3 class="album-description" id="albumdescription_text"><?php echo htmlspecialchars($album_description, ENT_QUOTES); ?></h3>
							</div>
						</div>
					<?php endif;
						$count = 0; ?>
						<?php foreach ($files as $name => $file) : ?>
							<?php if ($count % 3 == 0) : ?><div class="row"><?php endif; ?>
								<div class="col-md-4">
									<?php if (!empty($file['error'])) : ?>
		                                <div class="alert alert-danger alert-dismissable">
		                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		                                    Error uploading "<?php echo htmlspecialchars($name, ENT_QUOTES); ?>": <?php echo $file['error']; ?>
		                                </div>
		                            <?php else: ?>
										<div class="panel panel-default">
											<div class="panel-body">
												<a href="<?php echo $file['link']; ?>">
	                                                <?php if ($file['image']) : ?>
	                                                    <img src="<?php echo $file['link']; ?>" alt="Uploaded Image" class="thumbnail img-responsive">
	                                                <?php else: ?>
	                                                	<?php $image = $URL_BASE . '/img/filetypes/'
															. (!empty($file['extension']) && file_exists('img/filetypes/' . $file['extension'] . '.png')
															? $file['extension'] : '_blank') . '.png'; ?>
														<img src="<?php echo $image; ?>" alt="Uploaded File" class="img-responsive">
	                                                <?php endif; ?>

	                                                <p><?php echo htmlspecialchars($name); ?></p>
												</a>
												<pre class="checksum-field"><?php
													if (!empty($file['crc']))
														echo "CRC32: " . $file['crc'] . "\n";
													if (!empty($file['md5']))
														echo "MD5  : " . $file['md5'] . "\n";
													if (!empty($file['sha1']))
														echo "SHA-1: " . $file['sha1'] . "\n";
												?></pre>
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

<?php
	unset($_SESSION['files']);
	unset($_SESSION['album_name']);
	unset($_SESSION['album_id']);
	unset($_SESSION['album_lifetime']);
?>
