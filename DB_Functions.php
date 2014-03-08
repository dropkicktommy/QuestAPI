<?php
 
class DB_Functions {
 
    private $db;
 
	//put your code here
   // constructor
   function __construct() {
   	require_once 'DB_Connect.php';
      // connecting to database
      $this->db = new DB_Connect();
      $this->db->connect();
    }
 
	// destructor
   function __destruct() {
         
   }
 
   /**
    * Storing new user
    * returns user details
    */
   public function storeUser($name, $email, $password) {
   	$uuid = uniqid('', true);
      $hash = $this->hashSSHA($password);
      $encrypted_password = $hash["encrypted"]; // encrypted password
      $salt = $hash["salt"]; // salt
      $result = mysql_query("INSERT INTO user_info(unique_id, name, email, encrypted_password, salt, created_at) VALUES ('$uuid', '$name', '$email', '$encrypted_password', '$salt', NOW())");
      // check for successful store
      if ($result) {
      	// get user details
         $uid = mysql_insert_id(); // last inserted id
         $result = mysql_query("SELECT * FROM user_info WHERE uid = $uid");
         // return user details
         return mysql_fetch_array($result);
		} 
		else {
	      return false;
      }
   }
 
   /**
    * Get user by email and password
    */
   public function getUserByEmailAndPassword($email, $password) {
   	$result = mysql_query("SELECT * FROM user_info WHERE email = '$email'") or die(mysql_error());
      // check for result
      $no_of_rows = mysql_num_rows($result);
      if ($no_of_rows > 0) {
      	$result = mysql_fetch_array($result);
         $salt = $result['salt'];
         $encrypted_password = $result['encrypted_password'];
         $hash = $this->checkhashSSHA($salt, $password);
         // check for password equality
         if ($encrypted_password == $hash) {
         	// user authentication details are correct
            return $result;
         }
      }
   	else {
      	// user not found
         return false;
      }
	}
 
   /**
    * Check user is existed or not
    */
   public function isUserExisted($email) {
   	$result = mysql_query("SELECT email FROM user_info WHERE email = '$email'");
      $no_of_rows = mysql_num_rows($result);
      if ($no_of_rows > 0) {
      	// user existed
         return true;
      } 
      else {
      	// user not existed
         return false;
      }
   }
 
   /**
    * Encrypting password
    * @param password
    * returns salt and encrypted password
    */
   public function hashSSHA($password) {
    	$salt = sha1(rand());
      $salt = substr($salt, 0, 10);
      $encrypted = base64_encode(sha1($password . $salt, true) . $salt);
      $hash = array("salt" => $salt, "encrypted" => $encrypted);
      return $hash;
   }
 
   /**
    * Decrypting password
    * @param salt, password
    * returns hash string
    */
   public function checkhashSSHA($salt, $password) {
 	  $hash = base64_encode(sha1($password . $salt, true) . $salt);
     return $hash;
   }
   public function createChallenge($created_by, $name, $longitude, $latitude, $challenged_array, $text, $photo, $video, $expire) {
 	  $ucid = uniqid('', true);
     $sql2 ="INSERT INTO shared_challenges(friend_unique_id, challenge_id, time_to_expire, created_at) VALUES";
     $sql2 .= "('".implode("', '$ucid', '$expire', now()), ('", $challenged_array)."', '$ucid', '$expire', now())";
     $sql1 = "INSERT INTO challenges(challenge_id, created_by_uid, name, longitude, latitude, text, photo, video, created_at) VALUES ('$ucid', '$created_by', '$name', '$longitude', '$latitude', '$text', '$photo', '$video', NOW())";
     $result = mysql_query($sql1);        
     // check for successful store
     if ($result) {
   	  // get challenge details
        $cid = mysql_insert_id(); // last inserted cid
        $result = mysql_query("SELECT * FROM challenges WHERE cid = $cid");
        mysql_query($sql2);
        // return challenge details
        return mysql_fetch_array($result);
     }
     else {
		  return false;
     }
    }
    public function addNewFriend($user_id, $email) {
   	$result = mysql_query("SELECT email FROM user_info WHERE email = '$email'");        
      $no_of_rows = mysql_num_rows($result);
      if ($no_of_rows > 0) {
      	$result = mysql_query("SELECT unique_id FROM user_info WHERE email = '$email'");
        	$result = mysql_fetch_array($result);
         $friend_id = $result['unique_id'];
         $ufid = uniqid('', true);
         $result = mysql_query("INSERT INTO friends(user_unique_id, friend_unique_id, created_at) VALUES ('$user_id', '$friend_id', NOW())");
         // check for successful store
         if ($result) {
         	// get friend details
            $fid = mysql_insert_id(); // last inserted fid
            $result = mysql_query("SELECT friend_unique_id FROM friends WHERE fid = $fid");
            $result = mysql_fetch_array($result);
            $friend = $result['friend_unique_id'];
            $result = mysql_query("SELECT name, email, unique_id FROM user_info WHERE unique_id = '$friend'");
            // return friend details
            return mysql_fetch_array($result);
         } 
         else {
         	return false;
         }
      }
      else {
      	// friend does not exist
         return false;
      }
    }
	public function listFriends($user_id) {
   	$result = mysql_query("SELECT f.user_unique_id, u.name, u.email, u.unique_id FROM user_info u JOIN friends f ON u.unique_id = f.friend_unique_id WHERE f.user_unique_id = '$user_id'");
      $no_of_rows = mysql_num_rows($result);
      if ($no_of_rows > 0) {
      	// friends exist
         $allrows = array();
         while($array = mysql_fetch_row($result)) {
         	$cs_array = "'".implode("', '", $array)."'";
            $allrows[] = $cs_array;
         }
         $final_array = "(".implode("), (", $allrows).")";
         return $final_array;
      }
      else {
      	return false;
      }     	
  	}
    public function listChallenges($user_id) {
   	$result = mysql_query("SELECT s.friend_unique_id, c.challenge_id, c.name, u.name, c.longitude, c.latitude, s.accepted_at, s.time_to_expire FROM challenges c JOIN shared_challenges s ON c.challenge_id = s.challenge_id JOIN user_info u ON c.created_by_uid = u.unique_id WHERE s.friend_unique_id = '$user_id'");
      $no_of_rows = mysql_num_rows($result);
      if ($no_of_rows > 0) {
      	// challenges exist
         $allrows = array();
         while($array = mysql_fetch_row($result)) {
         	$cs_array = "'".implode("', '", $array)."'";
            $allrows[] = $cs_array;
         }
         $final_array = "(".implode("), (", $allrows).")";
         return $final_array;
      }
      else {
      	return false;
      }
	}
    public function acceptChallenge($user_id, $challenge_id) {
   	$result = mysql_query("UPDATE shared_challenges SET accepted_at = NOW() WHERE challenge_id = '$challenge_id' AND friend_unique_id = '$user_id'");
		// check for successful store
		if ($result) {
      	// get challenge accepted details
         $result = mysql_query("SELECT accepted_at, time_to_expire FROM shared_challenges WHERE challenge_id = '$challenge_id' AND friend_unique_id = '$user_id'");
         return mysql_fetch_array($result);
      }
      else {
      	return false;
      }
    }
    public function syncAddChallenges($user_id, $challenge_id_array) {
   	$challenge_id = "".implode(", ", $challenge_id_array)."";
   	error_log ($challenge_id);
   	$result = mysql_query("SELECT s.friend_unique_id, c.challenge_id, c.name, u.name, c.longitude, c.latitude, s.accepted_at, s.time_to_expire FROM challenges c JOIN shared_challenges s ON c.challenge_id = s.challenge_id JOIN user_info u ON c.created_by_uid = u.unique_id WHERE s.friend_unique_id = '$user_id' AND s.challenge_id NOT IN ('$challenge_id')");
   	error_log ($result);      
      $no_of_rows = mysql_num_rows($result);
      if ($no_of_rows > 0) {
      	// challenges exist
         $allrows = array();
         while($array = mysql_fetch_row($result)) {
         	$cs_array = "'".implode("', '", $array)."'";
            $allrows[] = $cs_array;
         }
         $final_array = "(".implode("), (", $allrows).")";
         error_log ($final_array);
         return $final_array;
      }
      else {
      	return false;
      }
	}
	public function syncRemChallenges($user_id, $challenge_id_array) {
        $challenge_id = "".implode(", ", $challenge_id_array)."";
        mysql_query("DELETE * FROM shared_challenges WHERE friend_unique_id = '$user_id' AND challenge_id = $challenge_id");
        $result = mysql_query("SELECT * FROM shared_challenges WHERE friend_unique_id = '$user_id' AND challenge_id = $$challenge_id");
      $no_of_rows = mysql_num_rows($result);
      if ($no_of_rows == 0) {
      	// challenge successfully removed
         return true;
      }
      else {
      	return false;
      }
	}
}
// Test
 
?>