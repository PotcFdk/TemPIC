<?php
	if (   !defined ('URL_BASE')
		|| !defined ('PATH_UPLOAD')
		|| !defined ('DISALLOWED_EXTS')
		|| !defined ('LIFETIMES')
	) {
		include('error.php');
		exit();
	}
?>