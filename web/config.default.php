<?php // TemPIC / configuration template: 2015-05-29

/// Basic configuration

	// The name of your instance.
	define ('INSTANCE_NAME', 'TemPIC');

	// The instance description. (optional)
	// This will be placed below the instance name and can contain HTML code.
	//define ('INSTANCE_DESCRIPTION', 'Powered by <a href="https://github.com/PotcFdk/TemPIC">TemPIC</a>!');

	// The URL of your instance.
	// This will be used for many link-generation related things.
	define ('URL_BASE', 'http://example.com');

	// Here you can disallow files with the specified extensions.
	// SECURITY WARNING: You should take additional steps to prevent accidents,
	// like running PHP files uploaded by users!
	$DISALLOWED_EXTS = array('php', 'html', 'htm', 'xhtml', 'htaccess', 'htpasswd');

	// The maximum album size allowed, used in serverside post-upload and clientside pre-upload checks.
	// This is 20e6 (20000000) bytes / 20 megabytes by default.
	// Note that this might be limited by other things as well, so if you experience
	// any odd behaviour related to filesize, check your webserver configuration and your php.ini!
	define ('SIZE_LIMIT', 20e6);

	// Here you can set up the available album lifetime options.
	// The indices (30m, 1h etc.) are directory names and are going to appear in the generated links.
	// `name` specifies actual entries in the album lifetime selection drop-down box.
	// `time` is the time, in minutes, that the album will be available after uploading.
	$LIFETIMES = array(
		'30m' => array('name' => '30 Minutes', 'time' => 30),
		'1h'  => array('name' => '1 Hour',     'time' => 60),
		'2h'  => array('name' => '2 Hours',    'time' => 2*60),
		'4h'  => array('name' => '4 Hours',    'time' => 4*60),
		'10h' => array('name' => '10 Hours',   'time' => 10*60),
		'1d'  => array('name' => '1 Day',      'time' => 24*60),
		'3d'  => array('name' => '3 Days',     'time' => 3*24*60),
	);

	// This setting specifies the default lifetime.
	// Must be a key in $LIFETIMES.
	// The chosen lifetime will be the default selection in the dropdown-box on the web page
	// and an alias in upload requests.
	define ('DEFAULT_LIFETIME', '30m');

	// Allows users to download the entire album as a ZIP file.
	// The ZIP file is generated when the album is created.
	// Keep in mind, that this can double the disk usage.
	define ('ENABLE_ALBUM_ZIP', false);

/// Advanced options

	// Enable album thumbnails.
	// This requires running the `thumbnailer.php` script in short intervals.
	// Keep in mind, that this will significantly increase the total CPU usage
	// if you expect a lot of images to be uploaded.
	define ('ENABLE_THUMBNAILS', false);

	// Use ImageMagick for the thumbnail generation.
	// Make sure that the Imagick extenstion is installed on the server.
	// Make sure that you also enable ENABLE_THUMBNAILS.
	define ('THUMBNAIL_USE_IMAGICK', false);

	// When generating thumbnails for animated GIFs, keep the animation.
	// Warning: This can significantly increase the CPU usage.
	// Requires THUMBNAIL_USE_IMAGICK to be enabled.
	define ('THUMBNAIL_ENABLE_ANIMATED', false);

	/// Thumbnail settings

	// This is the maximum number of pixels the longest side of the thumbnail will have.
	define ('THUMBNAIL_MAX_RES', 400);

	// Same as `THUMBNAIL_MAX_RES`, but for animated GIFs.
	define ('THUMBNAIL_MAX_ANIMATED_RES', 180);

/// Extras

	// Here you can specify the name of a CSS file that will be loaded after all other
	// CSS files. The file is expected to be in the [WEB-DIRECTORY]/css directory.
	//define ('CSS_OVERRIDE', 'my-stylesheet.css');

	// If you set this option, this string will be used to generate file URLs.
	// Normally, this is done by taking URL_BASE
	// and appending PATH_UPLOAD and the relative file path.
	// If you prefer to use a subdomain or an entirely different domain
	// for user-generated content, this is the setting you should be using.
	// The file path (relative to the upload path) will be appended to this string.
	// This requires your web-server to be configured to use the domain correctly.
	// SECURITY WARNING: If you want to prevent possible attacks caused by user-generated
	// content being accessible through your normal domain (e.g. stealing cookies using
	// javascript code that the user uploaded to your domain), make sure to disallow
	// access to the upload directory on your main domain and only allow access through
	// your usercontent (sub-)domain (the one that you set up here).
	// Don't enable this, if you don't know what you are doing,
	// or you might break file URL generation.
	//define ('URL_UPLOAD', 'http://tempicusercontent.example.com/');

	// If you set this option, this string will be used to generate album URLs.
	// Normally, this is done by taking URL_BASE and appending `/?album=[ALBUM_ID]`.
	// You can, for example, set this to `URL_BASE.'/'`,
	// so album URLs would look like this: `http://example.com/1d:1234567890`.
	// This requires a rewriting rule to be added to your web-server configuration.
	// Don't enable this, if you don't know what you are doing,
	// or you might break album URL generation.
	//define ('URL_ALBUM', URL_BASE.'/');

/// Custom
	// Here you can append unsupported configuration options,
	// for example when overriding the internal configuration defaults.
?>