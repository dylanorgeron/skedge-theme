<?php
  /* Template Name: Skedge-Sound-PublishPrep */

  #connect
  require_once get_template_directory()."/skedge-api/Database.php";
  $conn = dbconn();

  #create a wordpress post using the submitted form data and the libsyn audio url
  if (!empty($_POST)) {
    $skedge_id = $_GET['id'];
    $audio_url = $_POST['audio_url'];

    $skedge = new Skedge();
    $forms['skedge'] = $skedge->retrieveRecordByID($conn, $skedge_id);
    $skedge->retrieveAssociatedRecords($conn, $skedge_id, $forms);

    #http://pods.io/docs/code/pods/fetch/
    #fetch pods ID for channel
    $params = array(
      'where' => 'channel_name.meta_value LIKE "%'.$forms['podcast']['channel'].'%"',
      'limit' => 1
    );
    $pods_channel = pods('channel', $params);
    if($pods_channel->total() > 0){
      $pods_channel->fetch();
      $pods_channel_id = $pods_channel->id();
    }

    #fetch pods ID for voices
    $pods_voices = array();
    foreach ($forms['guests'] as $guest) {
      $params = array(
        'where' => 'first_name.meta_value LIKE "%'.$guest['first_name'].'%" AND last_name.meta_value LIKE "%'.$guest['last_name'].'%"',
        'limit' => 1
      );
      $pods_voice = pods('voice', $params);
      if($pods_voice->total() > 0){
        $pods_voice->fetch();
        array_push($pods_voices, $pods_voice->id());
      }
    }

    #PODS podcast fields
    $pods_podcast = array(
      'title'         => $forms['podcast']['podcast_title'],
      'podcast_title' => $forms['podcast']['podcast_title'],
      'description'   => $forms['podcast']['podcast_desc'],
      'language'      => $forms['podcast']['language'],
      'keywords'      => $forms['podcast']['keywords'],
      'channel'       => $pods_channel_id,
      'voices'        => $pods_voices,
      'audio_url'     => $audio_url
    );

    #add podcast
    #http://pods.io/docs/code/pods/add/
    $skedge->id = $skedge_id;
    $skedge->post_id = pods('podcast')->add($pods_podcast);
    $skedge->stage = 11;
    $skedge->saveRecord($conn, $user_login);

    #make history state
    $history = new History();
    $history->skedge_id 	   = $skedge_id;
    $history->action 		     = "drafted a post";
    $history->action_creator = $user_login;
    $history->makeHistoryState($conn);

    header( "Location: ../editing/?id=$skedge_id" );

  }
  #get skedge records
  if(!empty($_GET['id'])){
    $skedge = new Skedge();
    $response['skedge'] = $skedge->retrieveRecordByID($conn, $_GET['id']);
    $skedge->retrieveAssociatedRecords($conn, $_GET['id'], $response);
  }
  get_header();

?>


<div id="content">
  <h2>Upload to Libysn</h2>
  <p>
    Now that editing is complete, let's get this podcast uploaded to Libysn.
    Once you sign off that the podcast has been uploaded properly, a WordPress
    post draft will be created for this podcast for you to review.
  </p>
  <hr>

  <h4>Podcast Title: <?php echo $response['podcast']['podcast_title'] ?></h4>
  <h4>Podcast Channel: <?php echo $response['podcast']['channel'] ?></h4>
  <br>
  <b>Final Audio File</b>
  <br>
  <audio controls src="/storage/final_podcasts/<?php echo $response['podcast']['final_audio_url'] ?>"></audio>
  <br>
  <?php echo "<a href=".add_query_arg('id', $response['podcast']['id'], '../../pendingpodcast').">Podcast Form Details</a>"; ?>
  <br><br>
  <a target="_blank" href="https://four.libsyn.com/content_edit/index/mode/episode">Libsyn: New Draft</a></li>
  <br><br>
  <form method="POST" enctype="multipart/form-data">
    <label for="usable">Libsyn Audio URL</label>
    <br>
    <input type="text" name="audio_url">
    <br>
    <br>
    <button style="float:right" type="submit">Save and Continue</button>
  </form>

</div>

<?php get_footer(); ?>
