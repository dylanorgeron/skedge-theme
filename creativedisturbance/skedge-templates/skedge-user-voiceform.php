<?php
  /* Template Name: Skedge-User-VoiceForm */

  #connect
  require_once get_template_directory()."/skedge-api/Database.php";
  $conn = dbconn();

  #for displaying images
  $url_root = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/';

  #will be run if the save or submit buttons were clicked
	if ( isset( $_POST['submitted'] ) ) {
    $guest = new Guest();
		$skedge = new Skedge();

		#fetch the skedge id from url if it exists
		if(!empty($_GET['id'])) $skedge->id = $_GET['id'];

		#post vars
    $guest->first_name	= $_POST["first_name"];
    $guest->last_name 	= $_POST["last_name"];
    $guest->email 			= $_POST["email"];
    $guest->bio  			  = $_POST["bio"];
    $guest->website 		= $_POST["website"];
    $guest->additional 	= $_POST["additional"];
    $guest->submitted		= $_POST["submitted"];

		#stage is based on whether or not the form is submitted
		$stage = $guest->submitted == 1 ? 4 : 1;

		#if this form was saved and is being edited, fetch the guest id from the skedge record
		if(!empty($skedge->id)) $guest->id = $skedge->retrieveRecordByID($conn, $skedge->id)['guest_id_csv'];

    #save the guest record
		$guest_id = $guest->saveRecord($conn, $user_login);

		#finish skedge properties
		$skedge->type 			= 'guest';
		$skedge->stage 			= $stage;
		$skedge->guest_id_csv 	= $guest_id;

		#save skedge record
		$skedge_id = $skedge->saveRecord($conn, $user_login);

		#save a history message
		$history = new History();
		$verb = $guest->submitted == 1 ? 'submitted' : 'saved';
		$history->skedge_id = $skedge_id;
		$history->action = "$verb a voice form";
		$history->action_creator = $user_login;
		$history->makeHistoryState($conn);

		#redirect
		if($_POST["submitted"]) header( "Location: ../skedgerecord/?id=$skedge_id" );
		header( "Location: ../" );
	}

    #populate form with saved data if an id is present
    if(!empty($_GET["id"])){
        #get id
        $skedge_id = $_GET["id"];
    	$response = array();
    	$guest = new Guest();
    	$response['guest'] = $guest->retrieveRecordsBySkedgeID($conn, $skedge_id)[0];
    }

    #load wp header
    get_header();

?>

<script>
	var submitForm = function(){
		jQuery("#submit-input").val(1)
		jQuery("#voice_form").submit()
	}
	$("document").ready(function(){
		$("#voice_form").validate()
	})
</script>

<h1>Voice Form</h1>
<hr>
<br>
<form id="voice_form" method="POST" enctype="multipart/form-data">
	<fieldset>
		<label>First Name</label>
		<input id="first_name" name="first_name" value="<?php if(!empty($response)){echo $response['guest']['first_name'];} ?>"type="text" required>
		<br><br>

		<label>Last Name</label>
		<input id="last_name" name="last_name" value="<?php if(!empty($response)){echo $response['guest']['last_name'];} ?>"type="text" required>
		<br><br>

		<label>Email</label>
		<input id="email" name="email" value="<?php if(!empty($response)){echo $response['guest']['email'];} ?>"type="email" required>
		<br><br>

		<label>Bio</label>
		<textarea name="bio" value="<?php if(!empty($response)){echo $response['guest']['bio'];} ?>"></textarea>
		<br><br>

		<label>Website</label>
		<input name="website" value="<?php if(!empty($response)){echo $response['guest']['website'];} ?>"type="text">
		<br><br>

		<label>Additional Info</label>
		<input name="additional" value="<?php if(!empty($response)){echo $response['guest']['additional'];} ?>"type="text">
		<br><br>

		<label>Profile Picture</label>
		<br>
		<?php
			if(!empty($response['guest']['photo_url'])){
		?>
				<img src="<?php echo "$url_root/storage/photos/".$response['guest']['photo_url'] ?>">
				<br>
				You currently have a profile picture uploaded. Uploading a new file will replace the old one.
				<br>
		<?php
			}
		?>
		<input name="photo" type="file">
		<br><br>

		<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Alias libero dolorem nobis dignissimos obcaecati adipisci eligendi, impedit assumenda voluptatibus tempora minima nisi cupiditate aliquam id dolore hic reprehenderit ratione sapiente.</p>
		<label><input id="agree" name="agreement" type="checkbox" required>I agree</label>


		<input id="submit-input" name="submitted" style="display:none" type="text" value=0>
		<br><br>
		<div style="float:right;">
			<button formnovalidate="formnovalidate">Save Form</button>
			<button onclick="submitForm()" type="submit">Submit Form</button>
		</div>
	</fieldset>
</form>

<br>
<br>
<br>
<br>
<br>

<?php get_footer(); ?>
