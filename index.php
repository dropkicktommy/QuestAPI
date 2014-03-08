<?php
/**
 * File to handle all API requests
 * Accepts GET and POST
 *
 * Each request will be identified by TAG
 * Response will be JSON data
 
  /**
 * check for POST request
 */
if (isset($_POST['tag']) && $_POST['tag'] != '') {
	// get tag
   $tag = $_POST['tag'];
   // include db handler
   require_once 'DB_Functions.php';
   $db = new DB_Functions();
   // response Array
   $response = array("tag" => $tag, "success" => 0, "error" => 0);
   // check for tag type
   if ($tag == 'login') {
   	// Request type is check Login
      $email = $_POST['email'];
      $password = $_POST['password'];
      // check for user
      $user = $db->getUserByEmailAndPassword($email, $password);
      if ($user != false) {
      	// user found
         // echo json with success = 1
         $response["success"] = 1;
         $response["uid"] = $user["unique_id"];
         $response["user"]["name"] = $user["name"];
         $response["user"]["email"] = $user["email"];
         $response["user"]["created_at"] = $user["created_at"];
         $response["user"]["updated_at"] = $user["updated_at"];
         echo json_encode($response);
      } 
      else {
      	// user not found
         // echo json with error = 1
         $response["error"] = 1;
         $response["error_msg"] = "Incorrect email or password!";
         echo json_encode($response);
      }
   } 
   else if ($tag == 'register') {
   	// Request type is Register new user
      $name = $_POST['name'];
      $email = $_POST['email'];
      $password = $_POST['password'];
      // check if user is already existed
      if ($db->isUserExisted($email)) {
      	// user is already existed - error response
         $response["error"] = 2;
         $response["error_msg"] = "User already existed";
         echo json_encode($response);
      } 
      else {
      	// store user
         $user = $db->storeUser($name, $email, $password);
         if ($user) {
         	// user stored successfully
            $response["success"] = 1;
            $response["uid"] = $user["unique_id"];
            $response["user"]["name"] = $user["name"];
            $response["user"]["email"] = $user["email"];
            $response["user"]["created_at"] = $user["created_at"];
            $response["user"]["updated_at"] = $user["updated_at"];
            echo json_encode($response);
         } 
         else {
         	// user failed to store
            $response["error"] = 1;
            $response["error_msg"] = "Error occurred in Registration";
            echo json_encode($response);
         }
     	}
   } 
   else if ($tag == 'create') {
   	// Request type is Create new challenge
      $created_by = $_POST['created_by'];
      $name = $_POST['name'];
      $longitude = $_POST['longitude'];
      $latitude = $_POST['latitude'];       
      $challenged = $_POST['challenged'];   
      $text = $_POST['text'];
      $photo = $_POST['photo'];
      $video = $_POST['video'];
      $expire = $_POST['expires'];
      $challenged_array = explode(', ', $challenged);
      // store new Challenge
//      print_r ($sql);
      $challenge = $db->createChallenge($created_by, $name, $longitude, $latitude, $challenged_array, $text, $photo, $video, $expire);
      if ($challenge) {
      	// Challenge stored successfully
      	$response["success"] = 1;
         $response["cid"] = $challenge["challenge_id"];
         $response["challenge"]["name"] = $challenge["name"];
         $response["challenge"]["created_at"] = $challenge["created_at"];
         echo json_encode($response);
      } 
      else {
      	// Challenge failed to store
         $response["error"] = 1;
         $response["error_msg"] = "Error occurred while creating new Challenge";
         echo json_encode($response);
      }   
   }
   else if ($tag == 'new_friend') {
   	// Request type is add new friend
      $user_id = $_POST['user'];
      $email = $_POST['email'];
      // store new Friend
      $friend = $db->addNewFriend($user_id, $email);
      if ($friend) {
      	// Friend stored successfully
         $response["success"] = 1;
         $response["friend"]["name"] = $friend["name"];
         $response["friend"]["email"] = $friend["email"];
         $response["friend"]["unique_id"] = $friend["unique_id"];
         echo json_encode($response);
      } 
      else {
      	// Friend failed to store
         $response["error"] = 1;
         $response["error_msg"] = "Error occurred while adding new Friend";
         echo json_encode($response);
      }   
   }
   else if ($tag == 'list_friends') {
   	// Request type is list friends
      $user_id = $_POST['user'];
      // Retrieve friend list
      $friend_list = $db->listFriends($user_id);
      if ($friend_list) {
      	// Friend list retrieved successfully
         $values = $friend_list;
         $response["success"] = 1;
         $response["friend_list"]["values"] = $values;
         echo json_encode($response);
      } 
      else {
      	// Friend failed to store
         $response["error"] = 1;
         $response["error_msg"] = "Error occurred while retrieving Friends";
         echo json_encode($response);
      }   
   }
   else if ($tag == 'list_challenges') {
   	// Request type is list challenges
      $user_id = $_POST['user'];
      // Retrieve challenge list
    	$challenge_list = $db->listChallenges($user_id);
      if ($challenge_list) {
      	// Challenge list retrieved successfully
         $values = $challenge_list;
         $response["success"] = 1;
         $response["challenge_list"]["values"] = $values;
         echo json_encode($response);
      } 
      else {
      	// Friend failed to store
         $response["error"] = 1;
         $response["error_msg"] = "Error occurred while retrieving Challenges";
         echo json_encode($response);
      }   
   }
   else if ($tag == 'accept_challenge') {
   	// Request type is list challenges
      $user_id = $_POST['user'];
      $challenge_id = $_POST['challengeID'];
      // Retrieve challenge list
      $accept_challenge = $db->acceptChallenge($user_id, $challenge_id);
      if ($accept_challenge) {
      	// Challenge list retrieved successfully
         $response["success"] = 1;
         $response["challenge"]["accepted_at"] = $accept_challenge["accepted_at"];
         $response["challenge"]["expires"] = $accept_challenge["time_to_expire"];
         echo json_encode($response);
      } 
      else {
      	// Friend failed to store
         $response["error"] = 1;
         $response["error_msg"] = "Error occurred while accepting Challenge";
         echo json_encode($response);
      }   
   }
   else if ($tag == 'syncAdd_challenges') {
   	// Request type is list challenges
      $user_id = $_POST['user'];
      $challenge_id = $_POST['ID'];
      error_log ($challenge_id);        	
      $challenge_id_array = explode(', ', $challenge_id);
     	// Retrieve challenge list
    	$syncAdd_challenges = $db->syncAddChallenges($user_id, $challenge_id_array);
      if ($syncAdd_challenges) {
      	// Challenge list retrieved successfully
         $values = $syncAdd_challenges;
         $response["success"] = 1;
         $response["challenge_list"]["values"] = $values;
         echo json_encode($response);
      } 
      else {
      	// Failed to retrieve
         $response["error"] = 1;
         $response["error_msg"] = "Error occurred while retrieving Challenges";
         echo json_encode($response);
      }   
   }
   else if ($tag == 'syncRem_challenges') {
   	// Request type is list challenges
      $user_id = $_POST['user'];
      $challenge_id = $_POST['ID'];
      $challenge_id_array = explode(', ', $challenge_id);
     	// Remove challenge
    	$syncRem_challenges = $db->syncRemChallenges($user_id, $challenge_id_array);
      if ($syncRem_challenges) {
      	// Challenge removed successfully
         $response["success"] = 1;
         echo json_encode($response);
      } 
      else {
      	// Failed to remove
         $response["error"] = 1;
         $response["error_msg"] = "Error occurred while removing Challenges";
         echo json_encode($response);
      }   
   }                  
   else {
   	echo "Invalid Request";
   }
} 
else {
	echo "Access Denied";
}
?>