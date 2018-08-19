<?php

class Channel{

  public $id;
  public $channel_name;
  public $producer_name;
  public $producer_email;
  public $description;
  public $comments;
  public $podcasts_per_year;
  public $organization;
  public $photo_url;
  public $orcid;
  public $submitted;
  public $timestamp;
  public $archived;
  public $link;

  #returns a single guest form by id
  public function retrieveByID($conn, $id){
    $response = array();
    $guest_sql = $conn->prepare("SELECT * FROM `channel` WHERE `id` = $id");
    $guest_sql->execute();
    $response = $guest_sql->fetch(PDO::FETCH_ASSOC);
    return $response;
  }

  #returns all guest forms created by a specific user
  public function retrieveRecordsByUser($conn, $user){
    $response = array();
    $guest_sql = $conn->prepare("SELECT * FROM `channel` WHERE `username` = '$user'");
    $guest_sql->execute();
    while($row = $guest_sql->fetch(PDO::FETCH_ASSOC)){
        array_push($response, $row);
    }
    return $response;
  }

  #returns records associated with a skedge record
  #used as a helper for pulling the guest for a type 2 skedge record
  public function retrieveBySkedgeId($conn, $skedge_id){
    #get id from skedge record
    $skedge_sql = $conn->prepare("SELECT `channel_id` FROM `skedge` WHERE `id` = $skedge_id");
    $skedge_sql->execute();
    $channel_id = $skedge_sql->fetch(PDO::FETCH_ASSOC)['channel_id'];
    #call main get method
    return $this->retrieveByID($conn, $channel_id);
  }

  #saves a form, either creating a new form or updating an existing one based on whether or not an id was set
  public function saveRecord($conn, $user){

    #photo directory doesnt depend on what server code is being run on
    $photos_dir = realpath($_SERVER["DOCUMENT_ROOT"])."/storage/photos/";

    #check for a file, do some uploading
    if(!empty($_FILES) && basename($_FILES["photo"]["name"] != "")){

      #build filename (timestamp-username-firstname-lastname.extension)
      $photo_filename = time() ."-". $user ."-". str_replace(" ", "-", $this->channel_name) ."-". str_replace(" ", "-", $this->producer_name) .".". pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION);
      $photo_target_file = $photos_dir . $photo_filename;

      #validate extension incase something made it past javascript
      $allowedExts = array("png", "jpg");

      #grab the file extension
      $extension = pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION);

      #validate file and upload
      if(in_array($extension, $allowedExts)){
          if(move_uploaded_file($_FILES["photo"]["tmp_name"], $photo_target_file)){
            #check old file to see if we need to remove it only if the new file was saved
            #prevents old file from being deleted and nothing taking its place
            if(!empty($this->id)){
              $photo_sql = $conn->prepare("SELECT `photo_url` FROM `guest` WHERE `id` = $this->id");
              $photo_sql->execute();
              if($old_photo['photo_url']){
                  unlink($photos_dir . $old_photo['photo_url']);
              }
            }
            $this->photo_url = $photo_filename;
          }else{
            #throw errors to be caught by client
            return "photo upload error";
          }
      }else{
        #throw errors to be caught by client
        return "photo extension error";
      }
    }

    #if id is empty, create a new record
    #else, update record with id

    #store timestamps as UTC, do localization client-side
    date_default_timezone_set ('UTC');
    $this->timestamp = date_create('now')->format('Y-m-d H:i:s');

    if(empty($this->id)){
      $sql = $conn->prepare("INSERT INTO `channel`
                              SET `username`          = '$user',
                                  `channel_name`      = '$this->channel_name',
                                  `producer_name`     = '$this->producer_name',
                                  `producer_email`    = '$this->producer_email',
                                  `description`       = '$this->description',
                                  `comments`          = '$this->comments',
                                  `podcasts_per_year` = '$this->podcasts_per_year',
                                  `organization`      = '$this->organization',
                                  `archived`          = '$this->archived',
                                  `photo_url`         = '$this->photo_url',
                                  `timestamp`         = '$this->timestamp',
                                  `submitted`         = '$this->submitted'");
      $sql->execute();
      return $conn->lastInsertId();
    }else{
      #build sql string for non null params
      $sql_string = "UPDATE `channel` SET `username` = '$user',";

      foreach ($this as $key => $value) {
        if(!empty($value)) $sql_string .= "`$key` = '$value',";
      }
      $sql_string = rtrim($sql_string, ',');
      $sql_string .= " WHERE `id` = $this->id";


      $sql = $conn->prepare($sql_string);
      $sql->execute();

      return $this->id;
    }
  }

  public function deleteRecord($conn, $id ){
    $sql = $conn->prepare("DELETE FROM `channel` WHERE `id` = $id");
    $sql->execute();
  }
}
?>
