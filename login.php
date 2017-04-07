<?php
include('Crypt/RSA.php');
$cryptMode = CRYPT_RSA_ENCRYPTION_PKCS1;

if(isset($_POST['encrypted']))
{
	$encryptedFromPost = hex2bin($_POST['encrypted']);
	
	// decrypt
	$rsa = new Crypt_RSA();
	$rsa->setPassword('1234');
	$rsa->loadKey(file_get_contents('private.pem'));
	$rsa->setEncryptionMode($cryptMode);
	$decrypted = $rsa->decrypt($encryptedFromPost);
	
	//$ne = $rsa->getPublicKey(CRYPT_RSA_PUBLIC_FORMAT_RAW);
	//echo "<p>n:<br/>".$ne["n"]->toHex()."</p>";
	//echo "<p>e:<br/>".$ne["e"]->toHex()."</p>";
	//echo "<p>decryptedFromPost:<br/>".$decrypted."</p>";
	
	$array = explode("\n", $decrypted);
	$result = array();
	$result['user'] = $array[0];
	$result['pass'] = $array[1];
	$timeDiff = microtime(true)*1000 - $array[2];
	$result['valid'] = $timeDiff > 0 && $timeDiff < 10*1000;
	header('Content-type: application/json');
	echo json_encode($result);
}
?>