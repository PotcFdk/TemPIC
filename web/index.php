<?php
require_once('config.php');
session_start();
?>
<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>TemPIC</title>

        <link rel="stylesheet" href="css/bootstrap.min.css">

        <script src="js/jquery-2.1.0.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="page-header">
                        <h1>TemPIC</h1>
                    </div>

                    <form class="form-horizontal" method="post" action="upload.php" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="file" class="col-md-1 control-label">Files</label>
                            <div class="col-md-3">
                                <input class="form-control" type="file" name="file[]" id="file" multiple="true">
                            </div>
                        </div>

                        <div class="col-md-offset-1">
                            <input type="submit" name="submit" value="Upload" class="btn btn-primary">
                        </div>
                    </form>

                    <?php if (!empty($_SESSION['files'])) : ?>
                        <?php $count = 0; ?>
                        <?php foreach ($_SESSION['files'] as $name => $file) : ?>
                            <?php if (!empty($file['error'])) : ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    Error uploading "<?php echo $name; ?>": <?php echo $file['error']; ?>
                                </div>
                            <?php else: ?>
                                <?php if ($count % 3 == 0) : ?><div class="row"><?php endif; ?>
                                    <div class="col-md-4">
                                        <div class="panel panel-default">
                                            <div class="panel-body">
                                            	<a href="<?php echo $file['link']; ?>">
	                                                <?php if ($file['image']) : ?>
	                                                    <img src="<?php echo $file['link']; ?>" alt="Uploaded Image" class="thumbnail img-responsive">
	                                                <?php else: ?>
	                                                	<?php $image = $URL_BASE . '/img/filetypes/' . (file_exists('img/filetypes/' . $file['extension'] . '.png') ? $file['extension'] : '_blank') . '.png'; ?>
                                                		<img src="<?php echo $image; ?>" alt="Uploaded File" class="img-responsive">
	                                                <?php endif; ?>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php if ($count % 3 == 2) : ?></div><?php endif; ?>
                                <?php $count++; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <?php if ($count % 3 != 0) : ?></div><?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </body>
</html>

<?php unset($_SESSION['files']); ?>