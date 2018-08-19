<?php

class Alert{
  public $id;
  public $skedge_id;
  public $created_by;
  public $issue;
  public $timestamp;

  public function retrieveOne($id){
    return true;
  }

  public function retrieveRecordsByUser($conn, $user, $skedge_records){
    $response = array();
    foreach ($skedge_records as $record) {
        $skedge_id = $record['id'];
        $alerts_sql = $conn->prepare("SELECT * FROM $dbname.`alerts` WHERE `skedge_id` = $skedge_id");
        $alerts_sql->execute();
        $alerts_response = $alerts_sql->fetch(PDO::FETCH_ASSOC);
        if($alerts_response){
            array_push($response, $alerts_response);
        }
    }
    return $response;
  }

}

?>
