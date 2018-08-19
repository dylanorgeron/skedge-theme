<?php
  /* Template Name: Skedge-Sound-Finalize */

  #connect
  require_once get_template_directory()."/skedge-api/Database.php";
  $conn = dbconn();

  #save form
  if (!empty($_POST)) {
    $approved = $_POST['approved'];
    $skedge_id = $_GET['id'];

    if($approved){
      $history = new History();
      $skedge = new Skedge();
      $forms['skedge'] = $skedge->retrieveRecordByID($conn, $skedge_id);
      $skedge->retrieveAssociatedRecords($conn, $skedge_id, $forms);

      #save skedge record
      $skedge->stage = 12;
      $skedge->assigned_to = "";
      $skedge->active = 0;
      $skedge->saveRecord($conn, $user_login);

      #archive all forms
      $podcast = new Podcast();
      $podcast->id = $forms['podcast']['id'];
      $podcast->archived = 1;
      $podcast->saveRecord($conn, $user_login);

      foreach ($forms['guest'] as $g) {
        $guest = new Guest();
        $guest->id = $g['id'];
        $guest->archived = 1;
        $guest->saveRecord($conn, $user_login);
      }

      #make history state
      $history->skedge_id 	   = $skedge_id;
      $history->action 		     = "approved the audio quality";
      $history->action_creator = $user_login;
      $history->makeHistoryState($conn);

      header( "Location: ../../?id=$skedge_id" );

    }
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
  <h2>Finalize</h2>
  <p>Review the WordPress post for this podcast. Don't forget to set the publish date!</p>
  <hr>

  <h4>Podcast Title: <?php echo $forms['podcast']['podcast_title'] ?></h4>
  <h4>Podcast Channel: <?php echo $forms['podcast']['channel'] ?></h4>
  <br>
  <a target="_blank" href="/wp-admin/post.php?post=<?php echo $forms['skedge']['post_id']; ?>&action=edit">Podcast Post</a>
  <br>
  <br>

  <form method="POST" enctype="multipart/form-data">
    <input type="checkbox" name="approved">
    <label for="approved">Yes, this podcast has been scheduled to go live at the appropriate date.</label>
    <br>
    <br>
    <button style="float:right" type="submit">Finalize</button>
  </form>

</div>

<?php get_footer(); ?>
