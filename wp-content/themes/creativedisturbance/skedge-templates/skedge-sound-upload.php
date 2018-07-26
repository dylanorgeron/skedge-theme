<?php
  /* Template Name: Skedge-Sound-Upload */

  #connect
  require_once get_template_directory()."/skedge-api/Database.php";
  $conn = dbconn();

  #save form
  if (!empty($_FILES)) {
    $skedge_id  = $_GET['id'];
    $closing    = $bumpers_dir . $_POST['closing'];
    $opening    = $bumpers_dir . $_POST['opening'];

    #fetch podcast title and channel for naming the file
    $podcast        = new Podcast();
    $podcast        = $podcast->retrieveBySkedgeId($conn, $skedge_id);
    $podcast_id     = $podcast['id'];
    $podcast_title  = str_replace(" ", "-", $podcast['podcast_title']);
    $channel        = $podcast['channel'];

    #timestamps
    $time = time();
    $date = date('Y-m-d');

    #time-date-username-podcast_title-channel.extension
    $podcasts_target_file = str_replace(" ", "-", "$time--$date--$user_login--$podcast_title--$channel--edited.") . pathinfo($_FILES["edited_podcast"]["name"], PATHINFO_EXTENSION);
    #error check and upload edited podcast
    $allowedExts = array("wav", "aac", "mp3");
    $extension = pathinfo($_FILES["edited_podcast"]["name"], PATHINFO_EXTENSION);
    if(in_array($extension, $allowedExts)){
      move_uploaded_file($_FILES["edited_podcast"]["tmp_name"], $edited_podcasts_dir . $podcasts_target_file);
    }

    #stich podcast with bumpers
    $podcast_file = $edited_podcasts_dir . $podcasts_target_file;
    $output_filename = str_replace(" ", "-", "$time--$date--$user_login--$podcast_title--final.wav");
    $sox_cmd = addslashes("sox $opening $podcast_file $closing $output_filename");

    chdir($final_podcasts_dir);
    exec($sox_cmd);

    #update podcast record with new audio urls
    $podcast = new Podcast();
    $podcast->id = $podcast_id;
    $podcast->edited_audio_url = $podcasts_target_file;
    $podcast->final_audio_url = $output_filename;
    $podcast->saveRecord($conn, $user_login);

    #update skedge record
    $skedge = new Skedge();
    $skedge->id = $skedge_id;
    $skedge->stage = 9;
    $skedge->saveRecord($conn, $user_login);

    #make history state
    $history = new History();
    $history->skedge_id 	   = $skedge_id;
    $history->action 		     = "uploaded the edited audio file";
    $history->action_creator = $user_login;
    $history->makeHistoryState($conn);

    header( "Location: ../editing/?id=$skedge_id" );
  }

  #get skedge records
  if(!empty($_GET['id'])){
    $skedge = new Skedge();
    $forms['skedge'] = $skedge->retrieveRecordByID($conn, $_GET['id']);
    $skedge->retrieveAssociatedRecords($conn, $_GET['id'], $forms);
    $bumpers = array_diff(scandir($bumpers_dir), array('..', '.'));
  }
  get_header();

?>

<div id="content">
  <h2>Edit and Upload</h2>
  <p>Edit the podcast and then upload the final file.</p>
  <hr>

  <h4>Podcast Title: <?php echo $forms['podcast']['podcast_title'] ?></h4>
  <h4>Podcast Channel: <?php echo $forms['podcast']['channel'] ?></h4>
  <br>
  <a href="../../bumpers">Bumper Library</a>
  <br><br>
  <form method="POST" enctype="multipart/form-data">
    <div class="">
      <label for="">Opening Bumper</label>
      <select name="opening">
        <?php foreach ($bumpers as $bumper) {
          echo "<option value='$bumper'>$bumper</option>";
        } ?>
      </select>
    </div>
    <br>
    <div class="">
      <label for="">Podcast File</label>
      <input type="file" name="edited_podcast" value="">
    </div>
    <br>
    <div class="">
      <label for="">Closing Bumper</label>
      <select name="closing">
        <?php foreach ($bumpers as $bumper) {
          echo "<option value='$bumper'>$bumper</option>";
        } ?>
      </select>
    </div>
    <br>
    <br>
    <button style="float:right" type="submit">Save and Continue</button>
  </form>

</div>

<?php get_footer(); ?>
