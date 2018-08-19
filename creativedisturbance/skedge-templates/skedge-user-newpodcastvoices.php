<?php
  /* Template Name: Skedge-User-NewPodcastVoices */

  #connect
  require_once get_template_directory()."/skedge-api/Database.php";
  $conn = dbconn();

  #save form if necessary
  if(isset( $_POST["numVoices"])){
    $submitted = $_POST["submitted"];
    #save guest forms, adding each new id to an array
    $numVoices = $_POST["numVoices"];
    $guestIDs = "";
    #iterate over the submitted forms
    for($i = 1; $i <= $numVoices; $i++){
      #we dont renumber voices when a user starts a voice then deletes it because
      #that would be a nightmare to handle. instead, we loop up the the hightest
      #possible voiceNum and do a check for each number to see if its really there
      if(array_key_exists("first_name_$i", $_POST)){

        #save guest
        $guest = new Guest();

        #update existing record if it exists
        if($_POST["id_$i"] != "0") $guest->id = $_POST["id_$i"];

        #save other fields as normal
        $guest->first_name      = $_POST["first_name_$i"];
        $guest->last_name       = $_POST["last_name_$i"];
        $guest->email           = $_POST["email_$i"];
        $guest->bio             = $_POST["bio_$i"];
        $guest->website         = $_POST["website_$i"];
        $guest->orcid           = $_POST["orcid_$i"];
        $guest->additional_info = $_POST["additional_info_$i"];
        $guest->submitted       = $submitted;
        $guestID = $guest->saveRecord($conn, $user_login);
        $guestIDs = "$guestIDs, $guestID";

        #make a history state
        $history = new History();
    		$verb = $submitted == 1 ? 'submitted' : 'saved';
    		$history->skedge_id = $_GET['id'];
    		$history->action = "$verb a voice form";
    		$history->action_creator = $user_login;
    		$history->makeHistoryState($conn);

      }
    }
    #update skedge record with new guests
    $skedge = new Skedge();
    $skedge->id = $_GET['id'];
    $skedge->guest_id_csv = substr($guestIDs, 2);
    if($submitted) $skedge->stage = 4;
    $skedge->saveRecord($conn, $user_login);

    if($submitted){
        header("Location: ../skedgerecord/?id=".$_GET['id']);
    }else{
        header("Location: ../../");
    }
  }


  #pull saved data
  if(!empty($_GET['id'])){
    $guest = new Guest();
    $guests = $guest->retrieveRecordsBySkedgeID($conn, $_GET['id']);
  }

  #always fetch existing voice data
  #user != voices
  $params = array(
    'limit' => -1
  );
  $voice_pods = pods('voice', $params);
  #format for use in js
  $voices = array();
  while($voice_pods->fetch()){
    $v = array();
    $v['id']               = $voice_pods->display("id");
    $v['first_name']       = $voice_pods->display("first_name");
    $v['last_name']        = $voice_pods->display("last_name");
    $v['email']            = $voice_pods->display("email");
    $v['bio']              = $voice_pods->display("bio");
    $v['website']          = $voice_pods->display("website");
    $v['orcid']            = $voice_pods->display("orcid");
    $v['additional_info']  = $voice_pods->display("additional_info");
    array_push($voices, $v);
  }
  get_header();
?>

<!-- styles -->
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
  form{
    border: none;
    padding: 0;
  }
  .voice{
    border-left: 2px solid #007acc;
    padding: 10px 15px;
  }
</style>

<!-- js -->
<script>
  var savedVoices = <?php echo json_encode($guests); ?>;
  var existingVoices = <?php echo json_encode($voices); ?>;
  var voiceCount = 0
  var addVoiceItem = function(voice, isNew){
    voiceCount++
    $("#numVoices").val(voiceCount)
    // create new form
    $("#voices").append(
      "<div class='voice' id='voice_"+voiceCount+"'>" +
        "<select id='select_"+voiceCount+"' data-id='"+voiceCount+"' class='voice-select'>" +
          "<option value='-1'>Select a Voice</option>" +
          "<option value='0'>New Voice</option>" +
          <?php foreach ($voices as $v ) { ?>
            "<option value='<?php echo $v["id"] ?>'><?php echo $v["first_name"] . " " . $v["last_name"] ?></option>" +
          <?php } ?>
        "</select>" +
        "<div id='fields_"+voiceCount+"' style='display: none'>" +
          "<input style='display:none' class='id' name='id_"+voiceCount+"' type='text' value='"+voice.id+"'>" +

          "<label>First Name</label>" +
          "<input class='first_name' name='first_name_"+voiceCount+"' type='text' value='"+voice.first_name+"'>" +
          "<br><br>" +

          "<label>Last Name</label>" +
          "<input class='last_name' name='last_name_"+voiceCount+"' type='text' value='"+voice.last_name+"'>" +
          "<br><br>" +

          "<label>Email</label>" +
          "<input class='email' name='email_"+voiceCount+"' type='text' value='"+voice.email+"'>" +
          "<br><br>" +

          "<label>Bio</label>" +
          "<input class='bio' name='bio_"+voiceCount+"' type='text' value='"+voice.bio+"'>" +
          "<br><br>" +

          "<label>Website</label>" +
          "<input class='website' name='website_"+voiceCount+"' type='text' value='"+voice.website+"'>" +
          "<br><br>" +

          "<label>Additional Information</label>" +
          "<input class='additional' name='additional_info_"+voiceCount+"' type='text' value='"+voice.additional+"'>" +
          "<br><br>" +

          "<label>Voice Image</label>" +
          "<input class='image' name='image_"+voiceCount+"' type='text'>" +
          "<br><br>" +

          "<label>ORCID Number</label>" +
          "<input class='orcid' name='orcid_"+voiceCount+"' type='text' value='"+voice.orcid+"'>" +
          "<br><br>" +

          "<h4>Terms of Agreement</h4>" +
          "<p>" +
          "  I authorize Creative Disturbance, an initiative of the ArtSciLab of the University of Texas at Dallas to: 1. Record my participation in Creative Disturbance podcasts; 2. Exhibit or distribute those podcasts in accordance with Creative Disturbance's purposes; 3. Include my name, likeness, voice, and biographical material in connection with those podcasts. This release shall remain in effect unless revoked by both parties in writing. This online form is not for use by minors. Minors must complete a physical release form. By clicking submitting this form I am certifying that I am not a minor and agree to these terms." +
          "</p>" +
          "<label><input type='checkbox' name='agree_"+voiceCount+"'> I agree</label>" +
        "</div>" +
      "</div>")

    if(voice && !isNew){
      $("#fields_"+voiceCount).show()
      $("#select_"+voiceCount).hide()
    }

    //set change event
    $("#select_"+voiceCount).change(function(){
      var id = $(this).attr('data-id')
      var voiceID = this.value
      if(voiceID == '-1'){
        $("#fields_"+id).hide()
      }else if (voiceID == 0){
        $("#fields_"+id).show()
        $("#fields_"+id+" input").val("")
      }else{
        $("#fields_"+id).show()
        $("#fields_"+id).show()
        $.each(existingVoices, function(i, v){
          if(v.id == voiceID){
            $("#fields_"+id+" .first_name").val(v.first_name)
            $("#fields_"+id+" .last_name").val(v.last_name)
            $("#fields_"+id+" .email").val(v.email)
            $("#fields_"+id+" .bio").val(v.bio)
            $("#fields_"+id+" .website").val(v.website)
            $("#fields_"+id+" .orcid").val(v.orcid)
            $("#fields_"+id+" .additional_info").val(v.additional_info)
            $("#fields_"+id+" .profile_image").val(v.profile_image)
          }
        })
      }
    })
  }
  var submitForm = function(submitted){
    if(submitted) $('#submit-input').val(1)
    $("#voices").submit()
  }

  var newVoice = {
    id: '',
    first_name: '',
    last_name: '',
    email: '',
    bio: '',
    additional: '',
    orcid: '',
  }

  $(document).ready(function(){
    if(savedVoices[0]){
      $.each(savedVoices, function(i, v){
        addVoiceItem(v)
      })
    }else{
      addVoiceItem(newVoice, 1)
    }
  })
</script>

<div id="content">
  <table>
    <tr>
      <td>Channel Info</td>
      <td class="active">Voice Info -></td>
      <td>Confirmation</td>
      <td>Podcast Info</td>
    </tr>
  </table>
  <div>
    Now that you've selected a channel, it's time to tell us who we will be hearing on your podcast. If one of your voices has been on Creative Disturbance before, try searching for them in the dropdown below. If they are lending us their voice for the first time, go ahead and select "New Voice" from the top of the dropdown and fill out there information. We're excited to have them join our team!
  </div>
  <br>
  <hr>

  <h2>Who will we be hearing?</h2>
  <br>

  <form id="voices" method="POST" enctype="multipart/form-data">
    <input id="numVoices" name="numVoices" style="display:none;" type="text" />
    <input id="submit-input" name="submitted" style="display:none" type="text" value="0">
  </form>
  <br>
  <button type="button" name="button" onclick="addVoiceItem(newVoice, 1)">Add Voice</button>

  <div style="float:right">
    <button onclick="submitForm(0)">Save</button>
    <button onclick="submitForm(1)">Submit</button>
  </div>

</div>

<?php get_footer(); ?>
