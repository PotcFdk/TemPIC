<?php
	if (   !isset ($URL_BASE)
		|| !isset ($PATH_UPLOAD)
		|| !isset ($DISALLOWED_EXTS)
		|| !isset ($SIZE_LIMIT)
		|| !isset ($LIFETIMES)
	) {
		include('error.php');
		exit();
	}
?>