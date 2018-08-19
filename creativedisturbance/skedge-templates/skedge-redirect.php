<?php
  /* Template Name: Skedge-Redirect */

  #Checks the user's role and directs them to the proper page

  #user check
  get_currentuserinfo();
  if(!$user_ID) header("Location: /wp-login.php");

  #TODO: refactor this
  if(is_user_logged_in()) {
    $user=new WP_User($user_ID);
    if(!empty($user->roles) && is_array($user->roles)){
      foreach($user->roles as $role){
      }
    }
  }
  switch ($role) {
    case 'coordinator':
      header("Location: /skedge/coordinator/");
      break;
    case 'sound_designer':
        header("Location: /skedge/sound/");
      break;
    default:
        header("Location: /index.php/skedge/user/");
      break;
  }
?>
