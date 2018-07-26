<?php

class Podcast{

  public $conn;
  public $id;
  public $podcast_title;
  public $podcast_desc;
  public $channel;
  public $secondary_channel;
  public $keywords;
  public $language;
  public $website;
  public $photo_url;
  public $recording_method;
  public $raw_audio_url;
  public $edited_audio_url;
  public $final_audio_url;
  public $total_voices;
  public $submitted;
  public $archived;
  public $timestamp;

  #returns a single podcast form by id
  public function retrieveByID($conn, $id){
    $response = array();
    $podcast_sql = $conn->prepare("SELECT * FROM `podcast` WHERE `id` = $id");
    $podcast_sql->execute();
    $response = $podcast_sql->fetch(PDO::FETCH_ASSOC);
    return $response;
  }

  public function retrieveBySkedgeId($conn, $skedge_id){
    #get id from skedge record
    $skedge_sql = $conn->prepare("SELECT `podcast_id` FROM `skedge` WHERE `id` = $skedge_id");
    $skedge_sql->execute();
    $podcast_id = $skedge_sql->fetch(PDO::FETCH_ASSOC)['podcast_id'];
    #call main get method
    return $this->retrieveByID($conn, $podcast_id);
  }

  #returns all podcast forms created by a specific user
  public function retrieveRecordsByUser($conn, $user){
    $response = array();
    $podcast_sql = $conn->prepare("SELECT * FROM `podcast` WHERE `username` = '$user'");
    $podcast_sql->execute();
    while($row = $podcast_sql->fetch(PDO::FETCH_ASSOC)){
        array_push($response, $row);
    }
    return $response;
  }

  #saves a form, either creating a new form or updating an existing one based on whether or not an id was set
  public function saveRecord($conn, $user){

    #store timestamps as UTC, do localization client-side
    date_default_timezone_set ('UTC');
    $this->timestamp = date_create('now')->format('Y-m-d H:i:s');

    #if id is not 0, id will be used to update a record
    #else, a new record will be created
    if(!$this->id){
      #create record
      $sql = $conn->prepare("INSERT INTO `podcast`
                              SET `username`            = '$user',
                                  `podcast_title`       = '$this->podcast_title',
                                  `podcast_desc`        = '$this->podcast_desc',
                                  `channel`             = '$this->channel',
                                  `secondary_channel`   = '$this->secondary_channel',
                                  `keywords`            = '$this->keywords',
                                  `language`            = '$this->language',
                                  `website`             = '$this->website',
                                  `photo_url`           = '$this->photo_url',
                                  `recording_method`    = '$this->recording_method',
                                  `raw_audio_url`       = '$podcast_filename',
                                  `edited_audio_url`    = '$this->edited_audio_url',
                                  `final_audio_url`     = '$this->final_audio_url',
                                  `total_voices`        = '$this->total_voices',
                                  `submitted`           = 0,
                                  `archived`            = 0,
                                  `timestamp`           = '$this->timestamp'");
      $sql->execute();
      return $conn->lastInsertId();
    }else{
      #build sql string for non null params
      $sql_string = "UPDATE `podcast` SET ";

      foreach ($this as $key => $value) {
        if(!empty($value)) $sql_string .= "`$key` = \"$value\",";
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
