<?php

class History{

  public $skedge_id;
  public $action;
  public $action_creator;
  public $action_target;
  public $timestamp;


  public function retrieveHistoryByRecord($conn){
    $response = array();
    $sql = $conn->prepare("SELECT * FROM `history` WHERE `skedge_id` = $this->skedge_id");
    $sql->execute();
    $response = $sql->fetchAll(PDO::FETCH_ASSOC);
    return $response;
  }

  public function makeHistoryState($conn){
      #store timestamps as UTC, do localization client-side
      date_default_timezone_set ('UTC');
      $this->timestamp = date_create('now')->format('Y-m-d H:i:s');
      $sql = $conn->prepare("INSERT INTO `history`
                              SET
                                  `skedge_id`       = '$this->skedge_id',
                                  `action`          = '$this->action',
                                  `action_creator`  = '$this->action_creator',
                                  `action_target`   = '$this->action_target',
                                  `timestamp`       = '$this->timestamp'");
      $sql->execute();
      return $conn->lastInsertId();
    }
  }
?>
