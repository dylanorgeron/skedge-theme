<?php

class Guest{

  public $additional;
  public $archived;
  public $bio;
  public $email;
  public $first_name;
  public $id;
  public $last_name;
  public $link;
  public $username;
  public $orcid;
  public $photo_url;
  public $submitted;
  public $timestamp;
  public $url_guid;
  public $website;

  #returns a single guest form by id
  public function retrieveByID($conn, $id){
    $response = array();
    $guest_sql = $conn->prepare("SELECT * FROM `guest` WHERE `id` = $id");
    $guest_sql->execute();
    $response = $guest_sql->fetch(PDO::FETCH_ASSOC);
    return $response;
  }

  #returns all guest forms created by a specific user
  public function retrieveRecordsByUser($conn, $user){
    $response = array();
    $guest_sql = $conn->prepare("SELECT * FROM `guest` WHERE `username` = '$user'");
    $guest_sql->execute();
    while($row = $guest_sql->fetch(PDO::FETCH_ASSOC)){
        array_push($response, $row);
    }
    return $response;
  }

  #returns records associated with a skedge record
  #used as a helper for pulling the guest for a type 2 skedge record
  public function retrieveRecordsBySkedgeID($conn, $skedge_id){
    #get id from skedge record
    $response = array();
    $skedge_sql = $conn->prepare("SELECT `guest_id_csv` FROM `skedge` WHERE `id` = $skedge_id");
    $skedge_sql->execute();
    $guest_id_csv = $skedge_sql->fetch(PDO::FETCH_ASSOC)['guest_id_csv'];

    #id comes back as csv string, parse into array
    #works even if only one id is saved
    $guest_id_array = str_getcsv($guest_id_csv);
    foreach ($guest_id_array as $guest_id) {
      #call retrieve for this id and push into response
      array_push($response, $this->retrieveByID($conn, $guest_id));
    }
    #return array to be assigned in skedge object
    return $response;
  }

  #saves a form, either creating a new form or updating an existing one based on whether or not an id was set
  public function saveRecord($conn, $user){

    #photo directory doesnt depend on what server code is being run on
    $photos_dir = realpath($_SERVER["DOCUMENT_ROOT"])."/storage/photos/";

    #check for a file, do some uploading
    if(!empty($_FILES) && basename($_FILES["photo"]["name"] != "")){

      #build filename (timestamp-username-firstname-lastname.extension)
      $photo_filename = time() ."-". $user ."-". str_replace(" ", "-", $this->first_name) ."-". str_replace(" ", "-", $this->last_name) .".". pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION);
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
      $sql = $conn->prepare("INSERT INTO `guest`
                              SET `username`    = '$user',
                                  `first_name`  = '$this->first_name',
                                  `last_name`   = '$this->last_name',
                                  `email`       = '$this->email',
                                  `bio`         = '$this->bio',
                                  `website`     = '$this->website',
                                  `orcid`       = '$this->orcid',
                                  `additional`  = '$this->additional',
                                  `url_guid`    = '$this->url_guid',
                                  `photo_url`   = '$this->photo_url',
                                  `timestamp`   = '$this->timestamp',
                                  `submitted`   = '$this->submitted'");
      $sql->execute();
      return $conn->lastInsertId();
    }else{
      #build sql string for non null params
      $sql_string = "UPDATE `guest` SET `username` = '$user',";

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
}
?>
