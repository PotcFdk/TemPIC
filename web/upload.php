<?php
	include("config.php");
	
	function isImage()
	{
		return ($_FILES["file"]["type"] == "image/gif")
			|| ($_FILES["file"]["type"] == "image/jpeg")
			|| ($_FILES["file"]["type"] == "image/jpg")
			|| ($_FILES["file"]["type"] == "image/pjpeg")
			|| ($_FILES["file"]["type"] == "image/x-png")
			|| ($_FILES["file"]["type"] == "image/png");
	}

	$disallowedExts = array("php", "html", "htm");
	$temp = explode(".", $_FILES["file"]["name"]);
	$extension = end($temp);
	if ($_FILES["file"]["size"] < 2000000)
	{
		if (in_array($extension, $disallowedExts))
		{
			echo "Disallowed file type!";
		}
		elseif ($_FILES["file"]["error"] > 0)
		{
			echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
		}
		else
		{
			echo "Upload: " . $_FILES["file"]["name"] . "<br>";
			echo "Type: " . $_FILES["file"]["type"] . "<br>";
			echo "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
			//echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br>";

			if (file_exists("upload/" . $_FILES["file"]["name"]))
			{
				echo $_FILES["file"]["name"] . " already exists. ";
			}
			else
			{
				$name = time() . "_" . rand(100000000, 999999999) . "_" . $_FILES["file"]["name"];
				move_uploaded_file($_FILES["file"]["tmp_name"], $PATH_UPLOAD . "/" . $name);
				$link = $URL_BASE . "/" . $PATH_UPLOAD . "/" . $name;
				echo "Your link: <a href=\"" . $link . "\">right here</a><br><br>";
				if (isImage())
				{
					echo "<img src=\"" . $link . "\" alt=\"Uploaded Image\">";
				}
			}
		}
	}
	else
	{
		echo "File too large!";
	}
?>
