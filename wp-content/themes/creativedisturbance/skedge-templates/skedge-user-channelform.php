<?php
  /* Template Name: Skedge-User-ChannelForm */

  #connect
  require_once get_template_directory()."/skedge-api/Database.php";
  $conn = dbconn();

  $url_root 	= (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/';

  #will be run if the save or submit buttons were clicked
	if(!empty($_POST)){
    $skedge = new Skedge();
    $channel = new Channel();

		#fetch the skedge id from url if it exists
    if(!empty($_GET['id'])) $skedge->id = $_GET['id'];

    #post vars
    $channel->channel_name		= $_POST["channel_name"];
    $channel->producer_name		= $_POST["producer_name"];
    $channel->producer_email	= $_POST["producer_email"];
    $channel->description		= $_POST["description"];
    $channel->comments			= $_POST["comments"];
    $channel->podcasts_per_year	= $_POST["podcasts_per_year"];
    $channel->organization		= $_POST["organization"];
    $channel->submitted			= $_POST["submitted"];

    #stage is based on whether or not the form is submitted
		$stage = $channel->submitted == 1 ? 4 : 1;

		#if this form was saved and is being edited, fetch the guest id from the skedge record
		if(!empty($skedge->id)){
			$channel->id = $skedge->retrieveRecordByID($conn, $skedge->id)['channel_id'];
		}

    #save channel data
		$channel_id = $channel->saveRecord($conn, $user_login);

		#update the skedge record for this form
		$skedge->type 		= 'channel';
		$skedge->stage 		= $stage;
		$skedge->channel_id = $channel_id;

		#save skedge record
		$skedge_id = $skedge->saveRecord($conn, $user_login);

		#make a history state for this action
		$history = new History();
		$verb = $channel->submitted == 1 ? 'submitted' : 'saved';
		$history->skedge_id 	= $skedge_id;
		$history->action 		= "$verb a channel form";
		$history->action_creator = $user_login;
		$history->makeHistoryState($conn);

		#redirect
		if($_POST["submitted"]){
			header( "Location: ../skedgerecord/?id=$skedge_id" );
		}else{
			header( "Location: ../" );
		}
	}

  #will be run if the id is not empty
  if(!empty($_GET["id"])){
    #get id
    $id = $_GET["id"];
  	$response = array();
  	$channel = new Channel();
  	$response['channel'] = $channel->retrieveBySkedgeID($conn, $id);
  }

  get_header();
?>

<script>
	var submitForm = function(){
		jQuery("#submit-input").val(1)
		jQuery("#channel_form").submit()
	}
	$("document").ready(function(){
		$("#channel_form").validate()
	})
</script>

<h1>Channel Form</h1>
<hr>
<br>
<form id="channel-form" method="POST" enctype="multipart/form-data">
	<fieldset>
		<label>Channel Name</label>
		<input name="channel_name" value="<?php if(!empty($response)){echo $response['channel']['channel_name'];} ?>"type="text" required>
		<br><br>

		<label>Producer Name</label>
		<input name="producer_name" value="<?php if(!empty($response)){echo $response['channel']['producer_name'];} ?>"type="text" required>
		<br><br>

		<label>Producer Email</label>
		<input name="producer_email" value="<?php if(!empty($response)){echo $response['channel']['producer_email'];} ?>"type="email" required>
		<br><br>

		<label>Description</label>
		<textarea name="description" value="<?php if(!empty($response)){echo $response['channel']['description'];} ?>" required></textarea>
		<br><br>

		<label>Comments</label>
		<input name="comments" value="<?php if(!empty($response)){echo $response['channel']['comments'];} ?>"type="text">
		<br><br>

		<label>Organization</label>
		<input name="organization" value="<?php if(!empty($response)){echo $response['channel']['organization'];} ?>"type="text">
		<br><br>

		<label>Website</label>
		<input name="link" value="<?php if(!empty($response)){echo $response['channel']['link'];} ?>"type="text">
		<br><br>

		<label>Podcasts Per Year</label>
		<input name="podcasts_per_year" value="<?php if(!empty($response)){echo $response['channel']['podcasts_per_year'];} ?>"type="text">
		<br><br>

		<label>Reference Image</label>
		<br>
		<?php if(!empty($response['channel']['photo_url'])){ ?>
			<img src="<?php echo "$url_root/storage/photos/".$response['channel']['photo_url'] ?>">
			<br>
			You currently have a profile picture uploaded. Uploading a new file will replace the old one.
			<br>
		<?php }	?>
		<input name="photo" type="file">
		<br><br>

		<input id="submit-input" name="submitted" style="display:none" type="text" value="0">

		<div style="float:right;">
			<button formnovalidate="formnovalidate">Save Form</button>
			<button onclick="submitForm()" type="submit">Submit Form</button>
		</div>
	</fieldset>
</form>

<?php get_footer(); ?>
