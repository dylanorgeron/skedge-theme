<?php

class Comment{
  public $id;
  public $skedge_id;
  public $username;
  public $text;
  public $timestamp;

  public function retrieveOne($id){
    return true;
  }

  public function retrieveRecordsBySkedgeID($conn, $skedge_id){
    $sql = $conn->prepare("SELECT * FROM `comments` WHERE `skedge_id` = $skedge_id");
    $sql->execute();
    return $sql->fetchAll(PDO::FETCH_ASSOC);
  }

  public function saveRecord($conn, $user_login){
    date_default_timezone_set ('UTC');
    $this->timestamp = date_create('now')->format('Y-m-d H:i:s');
    $this->username = $user_login;
    $sql = $conn->prepare("INSERT INTO `comments`
                            SET
                                `skedge_id` = '$this->skedge_id',
                                `username`  = '$this->username',
                                `text`      = '$this->text',
                                `timestamp` = '$this->timestamp'");
    $sql->execute();
    return $conn->lastInsertId();
  }
}
?>
