<?php
// TODO: auslagern in ht {
include('Crypt/RSA.php');
$cryptMode = CRYPT_RSA_ENCRYPTION_PKCS1;
$privateKeyPassword = '1234';
$privateKeyFile = 'private.pem';
// }

try
{
	if(isset($_POST['encrypted']))
	{	
		$decrypted = decrypt(hex2bin($_POST['encrypted']));
		
		$array = explode("\n", $decrypted);
		$result = array();
		$user = $array[0];
		$pass = $array[1];
		$timeDiff = microtime(true)*1000 - $array[2];
		$valid = $timeDiff > 0 && $timeDiff < 10*1000; // 10s
		
		if(!$valid)
		{
			http_response_code(408); // Request Time-out
			die();
		}
		else if(!isLoginValid($user, $pass))
		{
			http_response_code(401); // Unauthorized
			die();
		}
		else
		{
			// nur hier gibts Erfolg
			header('Content-type: application/json');
			$result['user'] = $user;
			$result['pass'] = $pass;
			$result['valid'] = $valid;
			echo json_encode($result);
			die();
		}
	}
	else
	{
		// header('Parameter-missing: encrypted');
		http_response_code(406); // Not Acceptable
		die();
	}
}
catch(Exception $e)
{
	http_response_code(500); // Internal Server Error
	var_dump($e->getMessage());
	die();
}
http_response_code(400); // Bad Request
die();

function decrypt($encrypted)
{
	$rsa = new Crypt_RSA();
	$rsa->setPassword($privateKeyPassword);
	$rsa->loadKey(file_get_contents($privateKeyFile));
	$rsa->setEncryptionMode($cryptMode);
	$decrypted = $rsa->decrypt($encrypted);
	
	//$ne = $rsa->getPublicKey(CRYPT_RSA_PUBLIC_FORMAT_RAW);
	//echo "<p>n:<br/>".$ne["n"]->toHex()."</p>";
	//echo "<p>e:<br/>".$ne["e"]->toHex()."</p>";
	//echo "<p>decryptedFromPost:<br/>".$decrypted."</p>";
	
	return $decrypted;
}
	
function isLoginValid($user, $pass)
{
	session_start();
	$userDb = "Krämer";
	$passDb = "123";
	if (strcmp($user, $userDb) == 0 && strcmp($pass, $passDb) == 0) // TODO
	{
		// login is valid
		$_SESSION['user'] = $user;
		return true; // TODO: return jwt?
	}
	// login is invalid
	$_SESSION['user'] = null;
	closeSession();
	return false;
}
?>