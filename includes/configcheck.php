<?php
	if (   !isset ($INSTANCE_NAME)
		|| !isset ($URL_BASE)
		|| !isset ($DISALLOWED_EXTS)
		|| !isset ($SIZE_LIMIT)
		|| !isset ($LIFETIMES)
	) {
		include('error.php');
		exit();
	}
?>