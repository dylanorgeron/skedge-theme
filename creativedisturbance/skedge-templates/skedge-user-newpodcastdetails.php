<?php
  /* Template Name: Skedge-User-NewPodcastDetails */

  #connect
  require_once get_template_directory()."/skedge-api/Database.php";
  $conn = dbconn();

  #will be run if the save or submit buttons were clicked
  if ( isset( $_POST['submitted'] ) ) {
    #instantiate
    $podcast = new Podcast();
    $skedge = new Skedge();

    #storage directory doesnt depend on what server code is being run on
    $raw_podcasts_dir = realpath($_SERVER["DOCUMENT_ROOT"])."/storage/raw_podcasts/";

    #fetch the skedge id from url if it exists
    if(!empty($_GET['id'])) $skedge->id = $_GET['id'];

    #post vars
    $podcast->podcast_title	= $_POST["podcast_title"];
    $podcast->podcast_desc	= $_POST["podcast_desc"];
    $podcast->language    	= $_POST["language"];
    $podcast->keywords    	= $_POST["keywords"];
    $podcast->submitted     = $_POST['submitted'];

    #check if there is an existing podcast form
    $podcast->id = $skedge->retrieveRecordByID($conn, $skedge->id)['podcast_id'];
    #if there is no existing podcast form, we need to get the channel name from
    #the channel form associated with the skedge record
    if($podcast->id == 0){
      $channel = new Channel();
      $podcast->channel = $channel->retrieveBySkedgeId($conn, $skedge->id)['channel_name'];
    }

    #save audio file
    if(basename($_FILES["raw_audio"]["name"] != "")){
      #check old file to see if we need to remove it
      if($podcast->id != 0){
        $sql = $conn->prepare("SELECT `raw_audio_url` from `podcast` WHERE `id` = $podcast->id");
        $sql->execute();
        $old_audio_file = $sql->fetch(PDO::FETCH_ASSOC);
        if($old_audio_file['raw_audio_url']) unlink($raw_podcasts_dir . $old_audio_file['raw_audio_url']);
      }

      #build filename
      $podcast_filename = time() . "--" . str_replace(" ", "--", $podcast->podcast_title) . "." . pathinfo($_FILES["raw_audio"]["name"], PATHINFO_EXTENSION);
      $podcasts_target_file = $raw_podcasts_dir . $podcast_filename;

      #grab the file extension
      $extension = pathinfo($_FILES["raw_audio"]["name"], PATHINFO_EXTENSION);

      #make sure we dont get bullshit files
      $allowedExts = array("wav", "aac", "mp3");

      #validate file and upload
      if(in_array($extension, $allowedExts)){
        move_uploaded_file($_FILES["raw_audio"]["tmp_name"], $podcasts_target_file);
      }

      #save audio url
      $podcast->raw_audio_url = $podcast_filename;
    }

    #save podcast form
    $podcast_id = $podcast->saveRecord($conn, $user_login);
    #stage is based on whether or not the form is submitted
    $stage = $podcast->submitted == 1 ? 6 : 5;

    #update skedge record
    $skedge->stage = $stage;
    $skedge->podcast_id = $podcast_id;
    $skedge->saveRecord($conn, $user_login);

    #make history state
    $history = new History();
    $history-> action = $stage == 5 ? "saved podacast details" : "submitted podcast details";
    $history->skedge_id = $_GET['id'];
    $history->action_creator = $user_login;
    $history->makeHistoryState($conn);

    #redirect
    if($submitted){
      header("Location: ../skedgerecord/?id=".$_GET['id']);
    }else{
      header("Location: ../../");
    }
  }

  #attempt to fetch existing podcast data
  if(!empty($_GET['id'])){
    $skedge = new Skedge();
    $podcast_id = $skedge->retrieveRecordByID($conn, $_GET['id'])['podcast_id'];
    $podcast = new Podcast();
    $podcast = $podcast->retrieveByID($conn, $podcast_id);
  }

  get_header();
?>
<style>
    table, tr, td{
        border:none;
        color: #aaa;
    }
    table .active{
        color: #1a1a1a;
    }
    .pane{
        display:none;
    }
</style>

<script>
	var submitForm = function(){
		$("#submit-input").val(1)
		$("#voice_form").submit()
	}
	$("document").ready(function(){
		$("#voice_form").validate()
	})
</script>

<div id="content">
  <table>
      <tr>
          <td>Channel Info</td>
          <td>Voice Info</td>
          <td>Confirmation</td>
          <td class="active">Podcast Info -></td>
      </tr>
  </table>
  <div>
    Everything looks good, now it's time to record your podcast! If you're looking
    for some tips on getting the best possible audio quality, our sound team has
    put together a handy guide on recording the perfect podcast.
    <a href="#">Check it out here.</a>
    Once you've recorded, fill out the form below and we'll take care of the rest.
  </div>
  <br>
  <hr>
  <h2>Podcast Details</h2>
  <form id="voice_form" method="POST" enctype="multipart/form-data">
  	<fieldset>
  		<label>Title</label>
  		<input id="title" name="podcast_title" value="<?php if(!empty($podcast)){echo $podcast['podcast_title'];} ?>"type="text" required>
  		<br><br>

  		<label>Description</label>
  		<textarea id="description" name="podcast_desc" required><?php if(!empty($podcast)){echo $podcast['podcast_desc'];} ?></textarea>
  		<br><br>

  		<label>Language</label>
  		<input name="language" value="<?php if(!empty($podcast)){echo $podcast['language'];} ?>"type="text">
  		<br><br>

  		<label>Keywords</label>
  		<input name="keywords" value="<?php if(!empty($podcast)){echo $podcast['keywords'];} ?>"type="text">
  		<br><br>

      <label>Audio File</label>
      <br>
      <?php if (!empty($podcast['raw_audio_url'])) {?>
        <div class="">
          You have already uploaded <a href="/storage/raw_podcasts/<?php echo $podcast['raw_audio_url']; ?>">this</a> audio file. You can upload a new file to replace the old one.
        </div>
      <?php } ?>
      <input type="file" name="raw_audio" id="audio">
      <br><br>

  		<input id="submit-input" name="submitted" style="display:none" type="text" value=0>
  		<br><br>
  		<div style="float:right;">
  			<button formnovalidate="formnovalidate">Save Form</button>
  			<button onclick="submitForm()" type="submit">Submit Form</button>
  		</div>
  	</fieldset>
  </form>
</div>
<?php get_footer(); ?>
