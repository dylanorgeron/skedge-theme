<?php
  /* Template Name: Skedge-Sound-SoundCheck */

  #connect
  require_once get_template_directory()."/skedge-api/Database.php";
  $conn = dbconn();

  #save form
  if (!empty($_POST)) {
    $usable = $_POST['usable'];
    $skedge_id = $_GET['id'];

    $skedge = new Skedge();
    $history = new History();
    if($usable){
      #save skedge record
      $skedge->id = $skedge_id;
      $skedge->stage = 7;
      $skedge->saveRecord($conn, $user_login);

      #make history state
      $history->skedge_id 	   = $skedge_id;
      $history->action 		     = "approved the audio quality";
      $history->action_creator = $user_login;
      $history->makeHistoryState($conn);
    }
    header( "Location: ../editing/?id=$skedge_id" );
  }

  #get skedge records
  if(!empty($_GET['id'])){
    $skedge = new Skedge();
    $forms['skedge'] = $skedge->retrieveRecordByID($conn, $_GET['id']);
    $skedge->retrieveAssociatedRecords($conn, $_GET['id'], $forms);
  }
  get_header();

?>

<div id="content">
  <h2>Sound Check</h2>
  <p>Listen to the uploaded audio file and make sure the audio sounds usable.</p>
  <hr>

  <h4>Podcast Title: <?php echo $forms['podcast']['podcast_title'] ?></h4>
  <h4>Podcast Channel: <?php echo $forms['podcast']['channel'] ?></h4>
  <br>
  <audio controls src="/storage/raw_podcasts/<?php echo $forms['podcast']['raw_audio_url'] ?>"></audio>

  <form method="POST" enctype="multipart/form-data">
    <input type="checkbox" name="usable">
    <label for="usable">Yes, this audio is usable</label>
    <br>
    <br>
    <button style="float:right" type="submit">Save and Continue</button>
  </form>

</div>

<?php get_footer(); ?>
