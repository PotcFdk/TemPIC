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
					$files = $album_data['files'];
				}
				$remaining_time = $LIFETIMES[$album_lifetime]['time']*60 - ($time - filemtime ($PATH_ALBUM.'/'.$album_lifetime.'/'.$album_hash.'.txt'));
			}
		}
	}
	
	$display_checksums = !empty($_POST['checksums']);
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
	</head>
	<body>
		<?php include('../includes/copyrotate.php'); ?>
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<div class="page-header">
						<h1><a href="<?php echo $URL_BASE.'/index_nojs.php'; ?>"><?php echo $INSTANCE_NAME; ?></a></h1>
						<h4>NoJS version - <a href="<?php if (!empty($album_id)) echo get_album_url($album_id); else echo '/'; ?>">click here</a> to access the normal version.</h4>
					</div>

					<form class="form-horizontal" method="post" action="<?php echo $URL_BASE; ?>/upload.php" enctype="multipart/form-data">
						<div class="form-group">
							<input type="hidden" name="nojs" value="true">
							<label for="file" class="col-md-1 control-label">Files</label>
							<div class="col-md-6">
								<input class="file" type="file" name="file[]" id="file" multiple="multiple">
							</div>
							<div class="col-md-2">
								<input class="submit" type="submit" name="submit" id="submit" value="Upload">
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
					</form>

					<div class="row">
						<div class="col-md-6 col-md-offset-3">
							<div id="exceeding_limit" class="std-hide alert alert-danger alert-dismissable">
								<button type="button" class="close" data-hide="alert" aria-hidden="true">&times;</button>
								<p id="exceeding_limit_text"></p>
							</div>
						</div>
					</div>
					
					<?php if (!empty($album_id) && empty($files)) : // bad album id ?>
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
					
					<?php if (!empty($files)) : ?>
					<div class="row">
						<div class="col-md-12">
							<form action="" method="post">
								<p id="checksum_toggle_text"><span class="label label-info">Checksums</span>
								<input type="hidden" name="checksums" value="<?php echo $display_checksums ? "" : "true"; ?>">
								<input type="submit" value="<?php echo $display_checksums ? "Hide" : "Show"; ?> file checksums"></p>
							</form>
						</div>
					</div>
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
												<?php if ($display_checksums): ?>
												<pre class="checksum-field"><?php
													if (!empty($file['crc']))
														echo "CRC32: " . $file['crc'] . "\n";
													if (!empty($file['md5']))
														echo "MD5  : " . $file['md5'] . "\n";
													if (!empty($file['sha1']))
														echo "SHA-1: " . $file['sha1'] . "\n";
												?></pre><?php endif; ?>
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
