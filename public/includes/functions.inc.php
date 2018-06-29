<?php

session_start();
require 'dbh.inc.php';
require 'credentials.inc.php';

//This function will check if the email is used
//This function can be used at the signup and login
function CheckIfEmailUsed($email)
{
	global $conn;
	$sql = "SELECT email FROM user WHERE email = '$email';";
	$result = $conn->query($sql);
	$resultCheck = $result->num_rows;

	//If the email has been used
	if ($resultCheck > 0) {
		return true;

		//If the email does not exist
	} else {
		return false;
	}
}

//This function will check if the user input at the Signup is not empty
function CheckIfEmptySignup($firstname, $lastname, $email, $password)
{
	if (empty($firstname) || empty($lastname) || empty($email) || empty($password)) {
		return false;
	} else {
		return true;
	}
}

//This function will check if the user input at the Login is not empty
function CheckIfEmptyLogin($email, $password)
{
	if (empty($email) || empty($password)) {
		return true;
	} else {
		return false;
	}
}

//This function will check if the users name is real
function CheckIfRealName($firstname, $lastname)
{
	if (preg_match('/^[A-Za-z \'-]+$/i', $firstname) || preg_match('/^[A-Za-z \'-]+$/i', $lastname)) {
		return true;
	} else {
		return false;
	}
}

//This function will check if the users email is real
function CheckIfRealEmail($email)
{
	if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return true;
	} else {
		return false;
	}
}

//This function will check if the users password has the minimum length
function CheckIfPasswordLongEnough($password)
{
	if (strlen($password) >= 4) {
		return true;
	} else {
		return false;
	}
}

// This function wil check if the user is an admin or not.
function CheckIfAdmin($userRank)
{
	if (isset($userRank) && !empty($userRank)) {
		if ($userRank == 1) {
			return true;
		} else {
			return false;
		}
	}
}

//This function will generate a random userid, example: F98F13DE-BABA5AFD-5AFDB4F9
function GenerateUID()
{
	$s = strtoupper(md5(uniqid(rand(), true)));
	$id =
		substr($s, 0, 8) . '-' .
		substr($s, 8, 8) . '-' .
		substr($s, 12, 8);
	return $id;
}

//This function will hash the user his password
function HashPassword($nothashedPWD)
{
	$hashedPWD = password_hash($nothashedPWD, PASSWORD_DEFAULT);
	return $hashedPWD;
}

// This function will un-hash the user password.
function UnHashPassword($UserTypedPassword, $hashedPassword)
{
	if (password_verify($UserTypedPassword, $hashedPassword)) {
		return true;
	} else {
		return false;
	}
}

// This function wil check if the user is already logged in, ifso then redirect to feed page automaticly.
function CheckIfLoggedIn()
{
	if (isset($_SESSION['user']['id']) && !empty($_SESSION['user']['id'])) {
		//header("Location: ../feed.php");
		return true;
	} else {
		return false;
	}
}

//This function will generate the current date and put it inside a variable.
function GetCurrentDate()
{
	date_default_timezone_set('Europe/Amsterdam');
	$date = date('Y-m-d H:i:s');
	return $date;
}

// This function will get the users ip.
function GetUserIP()
{
	if (isset($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED'];
	} else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_FORWARDED_FOR'];
	} else if (isset($_SERVER['HTTP_FORWARDED'])) {
		$ip = $_SERVER['HTTP_FORWARDED'];
	} else if (isset($_SERVER['REMOTE_ADDR'])) {
		$ip = $_SERVER['REMOTE_ADDR'];
	} else {
		$ip = '0.0.0.0';
	}
	return $ip;
}

//This function will generate a token that will be send to the user
function GenerateToken()
{
	$string = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$string = str_shuffle($string);
	$token = substr($string, 40);
	$tokenfinal = str_shuffle($token);
	return $tokenfinal;
}

// This function will check if the activation token provided is same as in activationtoken table.
function CheckbeforeActivation($email, $token)
{
	global $conn;
	$sql = "SELECT * FROM activationtoken WHERE activationtoken.email = '$email' AND activationtoken.value = '$token';";
	$result = $conn->query($sql);
	$resultCheck = $result->num_rows;

	//Everything is good proceed further
	if ($resultCheck > 0) {
		return true;

		//If the token is not the same as in db.
	} else {
		return false;
	}
}

// This function will activate the account.
function ActivateAccount($email)
{
	global $conn;
	// Update token used to 1 (true)
	$sql = "UPDATE `activationtoken` SET `used` = 1 WHERE `activationtoken`.`email` = '$email';";

	$conn->query($sql);

	$sql2 = "UPDATE `user` SET `activated` = 1 WHERE `user`.`email` = '$email';";
	$conn->query($sql2);

	if ($conn->query($sql2) === TRUE) {
		return true;
	} else {
		return false;
	}

}

function CheckIfActivated($email)
{
	global $conn;
	$sql = "SELECT * FROM user WHERE user.email = '$email' AND user.activated = 1;";
	$result = $conn->query($sql);
	$rows = $result->num_rows;

	if ($rows > 0) {
		return true;
	} else {
		return false;
	}
}

//This function will logout the user
function LogoutUser()
{
	session_start();
	session_unset();
	session_destroy();
}

//This function will echo the copyright year in the footer
function CopyrightYear()
{
	if (date('Y') == 2018) {
		echo "2018";
	} elseif (date('Y') != 2018) {
		echo "2018 - " . date('Y');
	}
}

// This function is needed for Google Recaptcha to work.
function RecaptchaCheck($responseKey, $ip)
{
	//Import secret recaptcha key
	global $secretRecaptchakey;

	//Get response from Google URL
	$url = "https://www.google.com/recaptcha/api/siteverify?secret=$secretRecaptchakey&response=$responseKey&remoteip=$ip";
	$response = file_get_contents($url);
	$response = json_decode($response);

	//If the response is success return true
	if ($response->success)
		return true;
	else
		return false;
}

// This function will setup the sessions for auto-filling.
function RefillAtErrorSignup($firstname, $lastname, $email)
{
	$_SESSION['user']['firstname'] = $firstname;
	$_SESSION['user']['lastname'] = $lastname;
	$_SESSION['user']['email'] = $email;
}

// This function will automaticly fill in the firstname when there is an error in the signup form.
function FirstnameFillIn()
{
	if (isset($_SESSION['user']['firstname'])) {
		echo $_SESSION['user']['firstname'];
		unset($_SESSION['user']['firstname']);
	}
}

// This function will automaticly fill in the lastname when there is an error in the signup form.
function LastnameFillIn()
{
	if (isset($_SESSION['user']['lastname'])) {
		echo $_SESSION['user']['lastname'];
		unset($_SESSION['user']['lastname']);
	}
}

// This function will automaticly fill in the email when there is an error in the signup form.
function EmailFillIn()
{
	if (isset($_SESSION['user']['email'])) {
		echo $_SESSION['user']['email'];
		unset($_SESSION['user']['email']);
	}
}

// This function will get the current Level (Precentage) of the user.
function GetUserLevel($userid)
{
	if (isset($userid) && !empty($userid)) {

		$currentLevel = $_SESSION['level']['current_level'];
		$currentXP = $_SESSION['level']['current_xp'];
		$amountToLevelUp = $_SESSION['level']['amount_to_level_up'];


		$maxPrecentage = 100;
		$scale = 1.0;

		// Get precentage out of 100.
		if (!empty($maxPrecentage)) {
			// Outputs 0 to 100.
			$percent = ($currentXP * 100) / $maxPrecentage;
		} else {
			$percent = 0;
		}

		if ($percent >= 100) {
			$percent = 100;
		}

	}
}

// This function will add +1 level to the user.
function LevelUserAdd($userid)
{
	global $conn;

	if (isset($userid) && !empty($userid)) {

		// Update the current level of the user with +1.
		$sqlCurrentLevel = "UPDATE `level` SET `current_level` = current_level + 1 WHERE `level`.`user_id` = '$userid';";
		$conn->query($sqlCurrentLevel);

		if ($conn->query($sqlCurrentLevel) === TRUE) {

			if (LevelXPAdd($userid, 0) === TRUE) {

				if (AmountToLevelUp($userid, 100) === TRUE) {
					return true;
				} else {
					return false;
				}

			} else {
				return false;
			}


		} else {
			return false;
		}

	}

}

// This function will -1 level to the user.
function LevelUserMinus($userid)
{
	global $conn;

	if (isset($userid) && !empty($userid)) {

		$sql = "UPDATE `level` SET `current_level` = -1 WHERE `level`.`user_id` = '$userid';";
		$conn->query($sql);

		if ($conn->query($sql) === TRUE) {
			return true;
		} else {
			return false;
		}

	}
}

// This function will add the specified amount of XP to the user.
function LevelXPAdd($userid, $xp = 0)
{
	global $conn;

	if (isset($userid) && !empty($userid)) {

		$sql = "UPDATE `level` SET `current_xp` = $xp WHERE `level`.`user_id` = '$userid';";
		$conn->query($sql);

		if ($conn->query($sql) === TRUE) {
			return true;
		} else {
			return false;
		}

	}
}

// This function will reduce the specified amount of XP to the user.
function LevelXPMinus($userid, $xp = 0)
{
	global $conn;

	if (isset($userid) && !empty($userid)) {
		$sql = "UPDATE `level` SET `current_xp` = $xp WHERE `level`.`user_id` = '$userid';";
		$conn->query($sql);

		if ($conn->query($sql) === TRUE) {
			return true;
		} else {
			return false;
		}
	}

}


// This function will check the amount to rankup level of the user.
function AmountToLevelUp($userid, $amountToLevelUp = 0)
{
	global $conn;

	if (isset($userid) && !empty($userid)) {

		$sql = "UPDATE `level` SET `amount_to_level_up` = $amountToLevelUp WHERE `level`.`user_id` = '$userid';";
		$conn->query($sql);

		if ($conn->query($sql) === TRUE) {
			return true;
		} else {
			return false;
		}

	}
}

// This function will check of the user has passed the amount to level up limit ifso it will add +1 to level.
function CheckAmountToLevelUp($userid)
{
	if (isset($userid) && !empty($userid)) {

		global $conn;
		$sql = "SELECT * FROM level WHERE level.user_id = '$userid' AND current_xp >= amount_to_level_up;";
		$result = $conn->query($sql);
		$rows = $result->num_rows;

		if ($rows > 0) {
			// If the query is true then add +1 level to user.
			LevelUserAdd($userid);
			return true;
		} else {
			return false;
		}

	}
}

if (CheckAmountToLevelUp('7820E2E0-F08FB4D4-B4D4877C') == true) {
	echo "gg boyyyyyy";
} else {
	echo "nope.";
}