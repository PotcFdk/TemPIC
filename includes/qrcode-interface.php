<?php
	require_once ("qrcode.php");

	function getQRCode ($data)
	{
		$qr = new QRCode();
		$qr->setErrorCorrectLevel (QR_ERROR_CORRECT_LEVEL_L);
		$qr->setTypeNumber (3);
		$qr->addData ($data);
		$qr->make();

		$im = $qr->createImage (20, 4);

		ob_start();
		imagepng ($im);
		$im_c =  ob_get_contents();
		ob_end_clean();
		
		imagedestroy ($im);

		$im_b64 = base64_encode ($im_c);
				
		return $im_b64;
	}
?>
