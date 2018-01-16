<?php
class User_Functions {
	private $db;
	private $link;
	private $hasher;

	// constructor
	function __construct() {
		require_once "PasswordHash.php";
		require_once "jwt_helper.php"; 
		require_once 'DB_Connect.php';
		require_once 'strings.php';
		// connecting to database
		$this->db = new DB_Connect();
		$this->link = $this->db->connect();
		$this->hasher = new PasswordHash(8, false);
		$this->serverkey = SERVER_SECRET;
	}
 
	// destructor
	function __destruct() {
		 
	}
	
    public $falseValue = '{"success": false}';
    public $trueValue = '{"success": true}';

    //User Invitation Functions
    public function generateInvite(){
    	$id = str_replace('.','-',uniqid("",true));
    	$stmt = mysqli_prepare($this->link, "INSERT INTO `NewUserInvitations` (`Token`) VALUES (?);");
		mysqli_stmt_bind_param($stmt, 's', $id);
		/* execute prepared statement */
		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);
		return $this->successfulResult($id);
    }

    public function addNewUser($token,$username,$email,$password, $stay_signed_in){
    	if (!$this->checkInvite($token)){
    		return $this->unsuccessfulResult(ERROR_ACCESS_DENIED);
    	}
		if (strlen($email)>50){
			return $this->unsuccessfulResult(EMAIL_LENGTH_ERROR);
		}
		if (strlen($username)>50){
			return $this->unsuccessfulResult(USERNAME_LENGTH_ERROR);
		}
		if (strlen($password)>72){
			return $this->unsuccessfulResult(PASS_LENGTH_ERROR);
		}
		if (strlen($email)==0){
			return $this->unsuccessfulResult(EMAIL_PRESENCE_ERROR);
		}
		if (strlen($username)==0){
			return $this->unsuccessfulResult(USERNAME_PRESENCE_ERROR);
		}
		if (strlen($password)==0){
			return $this->unsuccessfulResult(PASS_PRESENCE_ERROR);
		}
		if (!filter_var($email, FILTER_VALIDATE_EMAIL) === true) {
			return $this->unsuccessfulResult(EMAIL_TYPE_ERROR);
		}
		$stmt = mysqli_prepare($this->link, "SELECT * FROM `Users` WHERE `Username` = ?;");
		mysqli_stmt_bind_param($stmt, 's', $username);
		/* execute prepared statement */
		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);
		if ($result){
			if (mysqli_num_rows($result)>0){
				return $this->unsuccessfulResult(REPEAT_ERROR);
			}
		} else {
			return $this->unsuccessfulResult(SERVER_ERROR);
		}
		$hash = $this->hasher->HashPassword($password);
		if (strlen($hash)<20){
			return $this->unsuccessfulResult(SERVER_ERROR);
		}
		$res = $this->addUserDetails($username,$email,$hash);
		if ($res==false){
			return $this->unsuccessfulResult(SERVER_ERROR);
		}
		$this->removeInvite($uuid);
		return $this->successfulResult($this->setJWTToken($res,$stay_signed_in));
	}

	//Tokens
	public function checkInvite($uuid){
    	$stmt = mysqli_prepare($this->link, "SELECT * FROM `NewUserInvitations` WHERE `Token` = ?;");
		mysqli_stmt_bind_param($stmt, 's', $uuid);
		/* execute prepared statement */
		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);
		if ($result){
			if (mysqli_num_rows($result)>0){
				return true;
			}
		} 
    }

    public function removeInvite($uuid){
    	$stmt = mysqli_prepare($this->link, "DELETE FROM `NewUserInvitations` WHERE `Token` = ?");
		mysqli_stmt_bind_param($stmt, 's', $uuid);
		/* execute prepared statement */
		mysqli_stmt_execute($stmt);
		//TODO: Error checking
		return true;
    }

	//User Maintenance Functions

	public function getUsername($UserID){
		$stmt = mysqli_prepare($this->link, "SELECT * FROM `Users` WHERE `UserID` = ?;");
		mysqli_stmt_bind_param($stmt, 's', $UserID);
		/* execute prepared statement */
		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);
		if (!$result){
			return false;
		}
		$temp = mysqli_fetch_array($result);
		return $temp['Username'];
	}

	public function renewCookie($UserID, $stay_signed_in){
		$cook = $this->setJWTToken($UserID,$stay_signed_in);
		error_log($cook);
		$decode = $this->getJWTExp($cook);
		error_log($decode);
		setcookie("session",$cook,intval($decode),"/");
	}

	public function checkUser($username,$password, $stay_signed_in){
		//var_dump($stay_signed_in);
		if (strlen($username)>50||strlen($password)>72||strlen($username)==0||strlen($password)==0){
			return $this->unsuccessfulResult(INVALID_ERROR);
		}
		$stmt = mysqli_prepare($this->link, "SELECT * FROM `Users` WHERE `Username` = ?;");
		mysqli_stmt_bind_param($stmt, 's', $username);
		/* execute prepared statement */
		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);
		if (!$result){
			return $this->unsuccessfulResult(INVALID_ERROR);
		}
		$temp = mysqli_fetch_array($result);
		$stored_hash = $temp['Phash'];
		$check = $this->hasher->CheckPassword($password, $stored_hash);
		if (!$check){
			return $this->unsuccessfulResult(INVALID_ERROR);
		}
		return $this->successfulResult($this->setJWTToken($temp['UserID'],$stay_signed_in));
	}

	//Ancillary Functions

	public function addUserDetails($username,$email,$phash){
		$stmt = mysqli_prepare($this->link, "INSERT INTO `Users` (`Username`, `Email`, `Phash`) VALUES (?, ?, ?);");
		mysqli_stmt_bind_param($stmt, 'sss', $username, $email, $phash);
		/* execute prepared statement */
		mysqli_stmt_execute($stmt);
		$stmt = mysqli_prepare($this->link, "SELECT * FROM `Users` WHERE `Username` = ?;");
		mysqli_stmt_bind_param($stmt, 's', $username);
		/* execute prepared statement */
		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);
		if ($result){
			$temp = mysqli_fetch_array($result);
			return $temp['UserID'];
		}
		return false;
	}

	public function setJWTToken($UserID,$stay_signed_in){
		//short login = 7hrs = 25200secs
		//long login = 7days = 604800secs
		$SHORT_LOGIN = 25200;
		$LONG_LOGIN = 604800;
		$time = time();
		//var_dump($stay_signed_in);
		if ($stay_signed_in==true){
			$time+=$LONG_LOGIN;
		} else {
			$time+=$SHORT_LOGIN;
		}
		$token = array();
		$token['id'] = $UserID;
		$token['exp'] = $time;
		$token['ssi'] = ($stay_signed_in) ? 'true' : 'false';
		return JWT::encode($token, $this->serverkey);
	}

	public function checkJWTToken($session){
		try {
			$token = JWT::decode($session,$this->serverkey);
		} catch (Exception $e){
			return false;
		}
		if ($token->exp<time()){
			return false;
		}
		$this->renewWithJWT($session);
		return $token->id;
	}

	public function getJWTExp($session){
		try {
			$token = JWT::decode($session,$this->serverkey);
		} catch (Exception $e){
			return false;
		}
		return $token->exp;
	}

	public function renewWithJWT($session){
		try {
			$token = JWT::decode($session,$this->serverkey);
		} catch (Exception $e){
			return false;
		}
		//error_log(($token->ssi)? 'true' : 'false');
		$this->renewCookie($token->id,boolval($token->ssi));
	}

	//Result functions
    public function successfulResult($data, $htmlsafe=false){
        $a = array();
        $a["success"]=true;
        $a["data"]=$data;
        if ($htmlsafe){
            return json_encode($a, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        } else {
            return json_encode($a);
        }
    }

    public function unsuccessfulResult($error){
        $a = array();
        $a["success"]=false;
        $a["error"]=$error;
        return json_encode($a);
    }
}
?>
