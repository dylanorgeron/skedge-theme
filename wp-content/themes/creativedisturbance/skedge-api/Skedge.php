<?php

class Skedge{

  public $id;
  public $type;
  public $guest_id_csv;
  public $channel_id;
  public $podcast_id;
  public $post_id;
  public $created_by;
  public $assigned_to;
  public $schedule_date;
  public $active;
  public $stage;
  public $timestamp;

  #retrieves all active records
  public function retrieveActiveRecords($conn){
    $sql = $conn->prepare("SELECT * FROM `skedge` WHERE `active` = 1");
    $sql->execute();
    return $sql->fetchAll(PDO::FETCH_ASSOC);
  }

  #retrieves a single skedge record by id
  public function retrieveRecordByID($conn, $id){
    $sql = $conn->prepare("SELECT * FROM `skedge` WHERE `id` = $id");
    $sql->execute();
    $response = $sql->fetch(PDO::FETCH_ASSOC);
    #get status if it exists

    #status messages
    switch ($response['type']) {
      case 'guest':
        $response['status_title'] = "Processing";
        $response['status_desc'] = "We've recieved your voice information and are working on processing it. You will be notified in the activity feed below when a team member has been assigned to your form. You can then track their progress as they make sure everything is in order with your form. We'll send you an email once they've added your voice to Creative Disturbance.";
        break;
      case 'channel':
        $response['status_title'] = "Processing";
        $response['status_desc'] = "We've recieved your channel information and are working on processing it. You will be notified in the activity feed below when a team member has been assigned to your form. You can then track their progress as they make sure everything is in order with your channel. We'll send you an email once they've added your voice to Creative Disturbance.";
        break;
      default:
        switch ($response['stage']) {
          case "4":
              $response['status_title'] = "Processing";
              $response['status_desc'] = "We've recieved your channel and voice information and are working on processing it. You will be notified in the activity feed below when a team member has been assigned to your podcast. You can then track their progress as they make sure everything is in order with your channel and voices. Once they've confirmed everything is in order, we will send you an email letting you know it's time to record your podcast.";
              break;
          case "6":
          case "7":
          case "8":
          case "9":
              $response['status_title'] = "Editing";
              $response['status_desc'] = "Your podcast has been successfully submitted and is now going through the editing process. We will be assigning it to a sound designer, who will then follow a multi-step process to ensure your podcast is just right before being scheduled. Feel free to watch their progress in the activity feed below, or leave them a comment if theres something you think they should know.";
              break;


          case "10":
              $response['status_title'] = "Drafting";
              $response['status_desc'] = "Your podcast has been edited and the pages required for publishing are now being drafted.";
              break;

          case "12":
              $response['status_title'] = "Awaiting Publication";
              $response['status_desc'] = "Your podcast has been edited and the necessary pages have been drafted. All that's left now is to wait for the publication date. Thanks for contributing to Creative Disturbance!";
              break;

          default:
              break;
        }
        break;
    }

    return $response;
  }

  #retrieves a single skedge record by id
  public function retrieveAssignedRecords($conn, $user){
    $sql = $conn->prepare("SELECT * FROM `skedge` WHERE `assigned_to` = '$user'");
    $sql->execute();
    return $sql->fetchAll(PDO::FETCH_ASSOC);
  }

  #returns all records for a user
  public function retrieveRecordsByUser($conn, $user){
    $response = array();
    $skedge_sql = $conn->prepare("SELECT * FROM `skedge` WHERE `created_by` = '$user' AND `active` = TRUE");
    $skedge_sql->execute();
    while($row = $skedge_sql->fetch(PDO::FETCH_ASSOC)){
        array_push($response, $row);
    }
    return $response;
  }

  #fetches all forms associated with this record
  #not done by default because we dont always want everything in the response
  public function retrieveAssociatedRecords($conn, $id, &$response){
    #pull all guest forms
    if(!empty($response['skedge']['guest_id_csv'])){
      $guest = new Guest();
      $response['guests'] = $guest->retrieveRecordsBySkedgeID($conn, $id);
    }
    #pull all channel forms
    if(!empty($response['skedge']['channel_id'])){
      $channel = new Channel();
      $response['channel'] = $channel->retrieveBySkedgeId($conn, $id);
    }

    if(!empty($response['skedge']['podcast_id'])){
      $podcast = new Podcast();
      $response['podcast'] = $podcast->retrieveByID($conn, $response['skedge']['podcast_id']);
    }

    $history = new history();
    $history->skedge_id = $id;
    $response['history'] = $history->retrieveHistoryByRecord($conn);

    $comments = new Comment();
    $comments->skedge_id = $id;
    $comments_arr = $comments->retrieveRecordsBySkedgeId($conn, $id);
    foreach ($comments_arr as $comment) {
      array_push($response['history'], $comment);
    }
    usort($response['history'], function($a, $b){
      if($a['timestamp'] == $b['timestamp']) return 0;
      return ($a['timestamp'] > $b['timestamp']) ? -1 : 1;
    });
  }

  public function saveRecord($conn, $user){

    #store timestamps as UTC, do localization client-side
    date_default_timezone_set ('UTC');
    $this->timestamp = date_create('now')->format('Y-m-d H:i:s');

    #if id is not 0, id will be used to update a record
    #else, a new record will be created
    if(!$this->id){
      #create record
      $sql = $conn->prepare("INSERT INTO `skedge`
                              SET `created_by`    = '$user',
                                  `type`          = '$this->type',
                                  `guest_id_csv`  = '$this->guest_id_csv',
                                  `channel_id`    = '$this->channel_id',
                                  `podcast_id`    = '$this->podcast_id',
                                  `post_id`       = '$this->post_id',
                                  `assigned_to`   = '$this->assigned_to',
                                  `schedule_date` = '',
                                  `active`        = 1,
                                  `stage`         = '$this->stage',
                                  `timestamp`     = '$this->timestamp'");
      $sql->execute();
      return $conn->lastInsertId();
    }else{
      #build sql string for non null params
      $sql_string = "UPDATE `skedge` SET ";

      foreach ($this as $key => $value) {
        if(isset($value)) $sql_string .= "`$key` = '$value',";
      }
      $sql_string = rtrim($sql_string, ',');
      $sql_string .= " WHERE `id` = $this->id";

      $sql = $conn->prepare($sql_string);
      $sql->execute();
      return $this->id;
    }
  }

  #check number of pending forms on this skedge record
  public function checkFormCompletion($conn, $id){

    #fetch ids
    $sql = $conn->prepare("SELECT `guest_id_csv`, `channel_id` FROM `skedge` WHERE `id`=$id");
    $sql->execute();
    $skedge_record  = $sql->fetch(PDO::FETCH_ASSOC);
    $guest_id_csv   = $skedge_record['guest_id_csv'];
    $channel_id     = $skedge_record['channel_id'];

    #get guest ids
    $guest_id_array = explode(",", $skedge_record['guest_id_csv']);

    #check each id
    foreach ($guest_id_array as $guest_id) {
      #guest sql
      $guest_sql = $conn->prepare("SELECT `archived` FROM `guest` WHERE `id`=$guest_id");
      $guest_sql->execute();
      #mark as complete
      if($guest_sql->fetch(PDO::FETCH_ASSOC)['archived']=="0"){
        return false;
      }
    }

    #check if channel form is complete
    if(!empty($channel_id)){
      $channel_sql = $conn->prepare("SELECT `archived` FROM `channel` WHERE `id` = $channel_id");
      $channel_sql->execute();
      $channel_response = $channel_sql->fetch(PDO::FETCH_ASSOC);
      return $channel_response['archived'];
    }else{
      return true;
    }
  }

  #complete skedge record
  public function completeRecord($conn, $id, $user){
    #get creator
    $created_by_sql = $conn->prepare("SELECT `created_by` FROM `skedge` WHERE `id` = $id");
    $created_by_sql->execute();
    $created_by = $created_by_sql->fetch(PDO::FETCH_ASSOC)['created_by'];

    #skedge sql
    $skedge_sql = $conn->prepare("SELECT `type`, `stage` FROM `skedge` WHERE `id` = $id");
    $skedge_sql->execute();
    $skedge_completion = $skedge_sql->fetch(PDO::FETCH_ASSOC);

    #mark as complete
    if($skedge_completion['type'] == 'guest' || $skedge_completion['type'] == 'channel'){
      $skedge_sql = $conn->prepare("UPDATE `skedge` SET `assigned_to` = '', `stage` = 0, `active` = 0 WHERE `id` = $id");
      $history = new History();
      $history->skedge_id = $id;
      $history->action = "completed this record";
      $history->action_creator = $user;
      $history->makeHistoryState($conn);
    }else{
      $skedge_sql = $conn->prepare("UPDATE `skedge` SET `assigned_to` = '', `stage`=5 WHERE `id` = $id");
    }
    $skedge_sql->execute();
  }
}

?>
