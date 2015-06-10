<?php
@include_once('config.php');
require_once('../includes/config.php');
require_once('../includes/configcheck.php');
require_once('../includes/helpers.php');
?>
<!doctype html>
<!--
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
-->
<?php
	// Make sure $files, $album_id, $album_hash and $remaining_time contain the data we want.

	if (!empty($_GET['album']) && is_string($_GET['album'])) {
		$album_id = strip_album_id($_GET['album']);
		if (!empty($album_id)) {
			$_a = explode(":", $album_id, 2);
			if (!empty($_a[0]))
				$album_lifetime = $_a[0];
			if (!empty($_a[1]))
				$album_hash = $_a[1];
			
			if (!empty($LIFETIMES[$album_lifetime]) && file_exists(PATH_ALBUM.'/'.$album_lifetime.'/'.$album_hash.'.txt')) {
				$time  = time ();
				$album_data = unserialize(file_get_contents(PATH_ALBUM.'/'.$album_lifetime.'/'.$album_hash.'.txt'));
				if (!empty($album_data) && !empty($album_data['files'])) {
					if (!empty($album_data['name']))
						$album_name = $album_data['name'];
					if (!empty($album_data['description']))
						$album_description = $album_data['description'];
					$files = $album_data['files'];
				}
				$remaining_time = $LIFETIMES[$album_lifetime]['time']*60 - ($time - filemtime (PATH_ALBUM.'/'.$album_lifetime.'/'.$album_hash.'.txt'));
			}
		}
	}
?>
<html>
	<head>
		<meta charset="utf-8">
		<title><?php if (!empty($album_name)) { echo htmlspecialchars($album_name, ENT_QUOTES).' - '; }
			echo INSTANCE_NAME; ?></title>

		<link rel="stylesheet" href="<?php echo URL_BASE; ?>/css/bootstrap.min.css">
		<link href="<?php echo URL_BASE; ?>/css/copyrotate.css" media="all" rel="stylesheet" type="text/css" />
		<link href="<?php echo URL_BASE; ?>/css/tempic-front.css" media="all" rel="stylesheet" type="text/css" />
		<?php if (defined ('CSS_OVERRIDE') && !empty(CSS_OVERRIDE) && file_exists("css/".CSS_OVERRIDE)) : ?>
		<link href="<?php echo URL_BASE; ?>/css/<?php echo CSS_OVERRIDE; ?>" media="all" rel="stylesheet" type="text/css" />
		<?php endif; ?>

		<script src="<?php echo URL_BASE; ?>/js/jquery-2.1.0.min.js"></script>
		<script src="<?php echo URL_BASE; ?>/js/bootstrap.min.js"></script>
		<script src="<?php echo URL_BASE; ?>/js/tempic-helpers.js"></script>
		<script src="<?php echo URL_BASE; ?>/js/modernizr-p1.js"></script>
		<script src="<?php echo URL_BASE; ?>/js/uploadmanager.js"></script>
		<script src="<?php echo URL_BASE; ?>/js/tempic-site.js"></script>
		
		<style>
			@font-face {
				font-family: 'Open Sans';
				font-style: normal;
				font-weight: 400;
				src: local('Open Sans'), local('OpenSans'), url('<?php echo URL_BASE; ?>/fonts/opensans.woff') format('woff');
			}
		</style>
		
		<script>
			<?php // Show album lifetime, if possible.
				if (!empty ($remaining_time)) : ?>
					$(function() { initRemainingLifetime (<?php echo($remaining_time); ?>); });
			<?php endif;
			if (!empty ($album_id) && is_string ($album_id)) : ?>
				var album_id = '<?php echo $album_id; ?>';
			<?php endif; ?>
			var album_url = '<?php echo get_album_url() ?>';
			<?php if (is_string (URL_BASE)) : ?>
				var url_base = '<?php echo URL_BASE; ?>';
			<?php endif;
			if (is_string (INSTANCE_NAME)) : ?>
				var instance_name = '<?php echo INSTANCE_NAME; ?>';
			<?php else : ?>
				var instance_name = 'TemPIC';
			<?php endif;
			if (is_numeric(SIZE_LIMIT)) : ?>
				var size_limit = <?php echo SIZE_LIMIT; ?>;
			<?php endif; ?>
		</script>
	</head>
	<body>
		<?php include('../includes/copyrotate.php'); ?>
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<div class="page-header">
						<h1><a href="<?php echo URL_BASE; ?>"><?php echo INSTANCE_NAME; ?></a></h1>
						<?php if (defined ('INSTANCE_DESCRIPTION') && !empty (INSTANCE_DESCRIPTION)): ?>
						<h4><?php echo INSTANCE_DESCRIPTION; ?></h4>
						<?php endif; ?>
					</div>

					<div id="div_browserwarning" class="row">
						<div class="col-md-12">
							<noscript>
								<p>This site is best viewed with JavaScript.
								If you don't want to turn on JavaScript, please use
								<a href="index_nojs.php<?php if (!empty($album_id)) echo '?album='.$album_id; ?>">the NoJS version</a>.</p>
							</noscript>
							<p id="browser_warning_text"></p>
						</div>
					</div>
					
					<div id="div_fileform" class="row">
						<div class="col-md-12">
							<form id="file-form" class="form-horizontal" method="post" action="<?php echo URL_BASE; ?>/upload.php" enctype="multipart/form-data">
								<div class="form-group">
									<label for="file" class="col-md-1 control-label">Files</label>
									<div class="col-md-8">
										<div class="input-group file-upload-form">
											<div class="form-control file-overview" id="file-overview">
												<span class="glyphicon glyphicon-file"></span>
												<span class="file-overview-text" id="file-overview-text"></span>
												<input class="file" type="file" name="file[]" id="file" multiple="multiple">
											</div>
											<div class="input-group-btn file-upload-controls">
												<button class="btn btn-default" type="button" id="button-file-wipe">
													<span class="glyphicon glyphicon-trash"></span>
													<span>Remove all</span>
												</button>
												<button class="btn btn-success" type="submit" id="button-upload">
													<span class="glyphicon glyphicon-cloud-upload"></span>
													<span>Upload</span>
												</button>
												<button class="btn btn-primary btn-file" type="button" id="button-browse"
														onclick="fileBrowse()">
													<span class="glyphicon glyphicon-folder-open btn-file-browse"></span>
													<span>Browse&nbsp;&hellip;</span>
												</button>
											</div>
										</div>
										
									</div>
									<div class="col-md-3">
										<select class="form-control" name="lifetime" id="lifetime">
										<?php foreach ($LIFETIMES as $id => $data) : ?>
											<option value="<?php echo $id; ?>"<?php
												if ($id == DEFAULT_LIFETIME)
													echo ' selected';
											?>><?php echo $data['name']; ?></option>
										<?php endforeach; ?>
										</select>
									</div>
								</div>
								<div class="row" id="div_albumname_input">
									<label for="file" class="col-md-1 control-label">Name</label>
									<div class="col-md-8">
										<input type="text" class="form-control" name="album_name"
											id="album_name" placeholder="Album Name">
									</div>
								</div>
								<div class="row" id="div_albumdescription_input">
									<label for="file" class="col-md-1 control-label">Info</label>
									<div class="col-md-8">
										<textarea class="verticalresizing noscroll form-control"
											name="album_description" id="album_description"
											onkeyup="textAreaAutoResize(this);"
											placeholder="Album Description"></textarea>
									</div>
								</div>
								<div class="row file-preview-area" id="div_filelist_preview">
									<label for="file" class="col-md-1 control-label">Files</label>
									<div class="col-md-8">
										<div id="div_filelist_preview_box"></div>
									</div>
								</div>
							</form>
						</div>
					</div>

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
					
					<div class="row" id="div_info_element">
						<div class="col-md-6 col-md-offset-3">
							<div id="info_element" class="std-hide alert alert-info alert-dismissable">
								<button type="button" class="close" data-hide="alert" aria-hidden="true">&times;</button>
								<p id="info_element_text"></p>
							</div>
						</div>
					</div>
					
					<?php if (isset($_GET['404']) || (!empty($album_id) && empty($files))) : // 404 or bad album id ?>
						<div class="row">
							<div class="col-md-6 col-md-offset-3">
								<div id="404_element" class="alert alert-danger alert-dismissable">
									<button type="button" class="close" data-hide="alert" aria-hidden="true">&times;</button>
									<p id="404_element_text">The requested file could not be found!<br />
									It may have been removed or it never existed in the first place.</p>
								</div>
							</div>
						</div>
					<?php endif; ?>
					
					<?php if (!empty($album_name)) : ?>
						<div id="div_albumname" class="row">
							<div class="col-md-12">
								<h3 class="album-name" id="albumname_text">Album: <?php echo htmlspecialchars($album_name, ENT_QUOTES); ?></h3>
							</div>
						</div>
					<?php endif; ?>
					
					<div id="div_infoarea" class="row infoarea">
						<div id="div_infoarea_left" class="col-md-6">
							<div class="row">
								<div class="col-md-12">
									<p id="lifetime_text"></p>
								</div>
							</div>
						</div>
						<div id="div_infoarea_right" class="col-md-6">
							<div class="pull-right">
								<?php if (!empty($files)) : ?><button id="checksums-toggle" class="btn btn-default">Show file checksums</button><?php endif; ?>
								<?php if (!empty($album_lifetime) && !empty($album_hash)
									&& file_exists(PATH_ALBUM.'/'.$album_lifetime.'/'.$album_hash.'.zip')) : ?>
									<a href="<?php
										echo URL_BASE.'/'.PATH_ALBUM.'/'.$album_lifetime.'/'.$album_hash.'.zip';
									?>" class="btn btn-primary btn-default"><span class="glyphicon glyphicon-download"></span> Download entire album</a>
								<?php endif; ?>
							</div>
						</div>
					</div>
					
					<?php if (!empty($files)) : ?>
						<?php if (!empty($album_description)) : ?>
							<div id="div_descriptionbox" class="row">
								<div class="col-md-12">
									<div class="panel-group" id="description_accordion" role="tablist" aria-multiselectable="true">
										<div class="panel panel-default">
											<div class="panel-heading" role="tab">
												<h4 class="panel-title">
													<a data-toggle="collapse" data-parent="#description_accordion" href="#description_collapse" aria-expanded="true" aria-controls="description_collapse">
														Description
													</a>
												</h4>
											</div>
											<div id="description_collapse" class="panel-collapse collapse in" role="tabpanel">
												<div class="panel-body">
													<?php echo nl2br(htmlspecialchars($album_description, ENT_QUOTES)); ?>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						<?php endif; ?>
						<?php $count = 0; ?>
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
												<a href="<?php echo $file['url']; ?>">
													<?php $file_ext_icon = URL_BASE . '/img/filetypes/'
															. (!empty($file['extension']) && file_exists('img/filetypes/' . $file['extension'] . '.png')
															? $file['extension'] : '_blank') . '.png';
													
													if (!empty($file['thumbnail'])) : ?>
														<img src="<?php echo $file['thumbnail']; ?>" alt="Uploaded Image" class="thumbnail img-responsive"
															onerror="onThumbnailError(this, '<?php echo $file_ext_icon; ?>');">
													<?php elseif ($file['image']) : ?>
														<img src="<?php echo $file['url']; ?>" alt="Uploaded Image" class="thumbnail img-responsive">
													<?php else: ?>
														<img src="<?php echo $file_ext_icon; ?>" alt="Uploaded File" class="img-responsive">
													<?php endif; ?>

													<p><?php echo htmlspecialchars($name); ?></p>
												</a>
												<pre class="checksum-field"><?php
													if (!empty($file['checksums']['crc']))
														echo "CRC32: " . $file['checksums']['crc'] . "\n";
													if (!empty($file['checksums']['md5']))
														echo "MD5  : " . $file['checksums']['md5'] . "\n";
													if (!empty($file['checksums']['sha1']))
														echo "SHA-1: " . $file['checksums']['sha1'] . "\n";
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
