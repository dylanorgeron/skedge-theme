<?php
  /* Template Name: Skedge-User-NewPodcastChannel */

  #connect
  require_once get_template_directory()."/skedge-api/Database.php";
  $conn = dbconn();

  #url for displaying media
  $url_root   = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/';

  if(!empty($_POST)){
    $forms = array();

    #type 1 means we will create a podcast form with the chosen channel name as the channel
    #type 2 means a new channel was submitted and has its own form
    if($_POST['type'] == 1){

      $podcast = new Podcast();
      $skedge = new Skedge();

      #if we are updating a record, check if a channel form previously existed
      #users can change their mind after starting a new channel form, and we will need to delete their in-progress channel form
      #only need to check when type == 1 && !empty(id) because its impossible to save, not submit, a type 1
      if(!empty($_GET['id'])){
        $channel = new Channel();
        $channel->deleteRecord($conn, $skedge->retrieveRecordByID($conn, $_GET['id'])['channel_id']);
        $skedge->id = $_GET['id'];
        $skedge->channel_id = 0;
        $skedge->saveRecord($conn, $user_login);
      }

      $podcast->channel = $_POST['channel_name'];
      $podcast_id = $podcast->saveRecord($conn, $user_login);
      $skedge->podcast_id = $podcast_id;
      $skedge->stage = 2;
      $skedge->type = 'podcast';
      $skedge_id = $skedge->saveRecord($conn, $user_login);
      header("Location: ../newpodcastvoices/?id=$skedge_id");
    }else{
      $skedge = new Skedge();
      $channel = new Channel();

      #update existing form if it exists
      if(!empty($_GET['id'])){
        $forms = $skedge->retrieveRecordByID($conn, $_GET['id']);
        $skedge->id = $forms['id'];
        $channel->id = $forms['channel_id'];
      }

      #save channel data
      $channel->channel_name      = $_POST['channel_name'];
      $channel->producer_name     = $_POST['producer_name'];
      $channel->producer_email    = $_POST['producer_email'];
      $channel->description       = $_POST['description'];
      $channel->comments          = $_POST['comments'];
      $channel->organization      = $_POST['organization'];
      $channel->link              = $_POST['link'];
      $channel->podcasts_per_year = $_POST['podcasts_per_year'];
      $channel->submitted         = $_POST['submitted'];
      $channel_id = $channel->saveRecord($conn, $user_login);

      #save skedge data
      $skedge->channel_id = $channel_id;
      $skedge->stage = $channel->submitted == 0 ? 1 : 2;
      $skedge->type = 'podcast';
      $skedge_id = $skedge->saveRecord($conn, $user_login);

      if($channel->submitted){
        header("Location: ../newpodcastvoices/?id=$skedge_id");
      }else{
        header("Location: ../../");
      }
    }
  }

    #pull data
    if(!empty($_GET['id'])){
      #get skedge records
      $channel = new Channel;
      $skedge = new Skedge;
      $forms['skedge'] = $skedge->retrieveRecordByID($conn, $_GET['id']);
      $forms['channel'] = $channel->retrieveByID($conn, $forms['skedge']['channel_id']);
    }

    #fetch user's channels
    $user = wp_get_current_user();
    $params = array(
      'where'=> "producer_name.meta_value = '" . $user->first_name ." ". $user->last_name ."'"
    );
    $user_channels = pods('channel', $params);

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
    var newChannel = <?php if((!empty($forms) && !empty($forms['skedge']['channel_id']))){echo 1;}else{echo 0;} ?>;
    $('document').ready(function(){
        $('.pane-switcher').on('change', function(){
            $('.pane').hide()
            $('#'+this.id+'-pane').show()
        })
        if(<?php echo $user_channels->total_found()?>){
            $('#mine').prop('checked', true)
            $('#mine-pane').show()
        }else{
            $('#collab').prop('checked', true)
            $('#collab-pane').show()
        }
        if(newChannel){
            $("#new").prop('checked', true)
            $('#new-pane').show()
        }
    })
    var submitForm = function(submitted){
        //determine what form was submitted
        var formType = $("input:radio:checked").attr('id')
        var formElement = "#" + $("input:radio:checked").attr('id') + "-pane form"
        if(formType == 'new'){
            //set value of submit input based on button clicked
            if(submitted) $('#submit-input').val(1)
            //validate and submit
            $(formElement).submit()
        }else{
            $(formElement).submit()
        }
    }
</script>

<div id="content">
    <table>
        <tr>
            <td class="active">Channel Info -></td>
            <td>Voice Info</td>
            <td>Confirmation</td>
            <td>Podcast Info</td>
        </tr>
    </table>
    <div>
        First, we need to know what channel your podcast will be heard on. If you have a channel already, you can select it from the dropdown below. Otherwise, feel free to contribute to one of our collaborative channels or start up a brand new channel!
    </div>
    <br>
    <hr>

    <h2>Where will your podcast be heard?</h2>
    <br>

    <!--My Channel-->
    <label for="mine"><input class="pane-switcher" id="mine" name="method" type="radio"> My Channels</label>
    <br>
    <div id="mine-pane" class="pane">
        <form id="mine-form" method="POST" enctype="multipart/form-data">
            <select name="channel_name">
                <?php   while($user_channels->fetch()) {?>
                    <option value="<?php echo $user_channels->display("channel_name") ?>"><?php echo $user_channels->display("channel_name") ?></option>
                <?php   } ?>
            </select>
            <br>
            <input style="display:none" name="type" type="text" value="1">
        </form>
    </div>
    <br>

    <!--Collab Channel-->
    <label for="collab"><input class="pane-switcher" id="collab" name="method" type="radio"> Collaborative Channel</label>
    <br>
    <div id="collab-pane" class="pane">
        <form id="collab-form" method="POST" enctype="multipart/form-data">
            <select name="channel_name">
                <option value="Voices From the Crowd">Voices From the Crowd</option>
            </select>
            <input style="display:none" name="type" type="text" value="1">
        </form>
    </div>
    <br>

    <!--New Channel-->
    <label for="new"><input class="pane-switcher" id="new" name="method" type="radio"> New Channel</label>
    <br>
    <div id="new-pane" class="pane">
        <form id="new-form" method="POST" enctype="multipart/form-data">
            <fieldset>
                <label>Channel Name</label>
                <input name="channel_name" value="<?php if(!empty($forms)){echo $forms['channel']['channel_name'];} ?>"type="text" required>
                <br><br>

                <label>Producer Name</label>
                <input name="producer_name" value="<?php if(!empty($forms)){echo $forms['channel']['producer_name'];} ?>"type="text" required>
                <br><br>

                <label>Producer Email</label>
                <input name="producer_email" value="<?php if(!empty($forms)){echo $forms['channel']['producer_email'];} ?>"type="email" required>
                <br><br>

                <label>Description</label>
                <textarea name="description" value="<?php if(!empty($forms)){echo $forms['channel']['description'];} ?>" required></textarea>
                <br><br>

                <label>Comments</label>
                <input name="comments" value="<?php if(!empty($forms)){echo $forms['channel']['comments'];} ?>"type="text">
                <br><br>

                <label>Organization</label>
                <input name="organization" value="<?php if(!empty($forms)){echo $forms['channel']['organization'];} ?>"type="text">
                <br><br>

                <label>Website</label>
                <input name="link" value="<?php if(!empty($forms)){echo $forms['channel']['link'];} ?>"type="text">
                <br><br>

                <label>Podcasts Per Year</label>
                <input name="podcasts_per_year" value="<?php if(!empty($forms)){echo $forms['channel']['podcasts_per_year'];} ?>"type="text">
                <br><br>

                <label>Reference Image</label>
                <br>
                <?php
                    if(!empty($forms['channel']['photo_url'])){
                ?>
                        <img src="<?php echo "$url_root/storage/photos/".$forms['channel']['photo_url'] ?>">
                        <br>
                        You currently have a profile picture uploaded. Uploading a new file will replace the old one.
                        <br>
                <?php
                    }
                ?>
                <input name="photo" type="file">
                <br><br>

                <input id="submit-input" name="submitted" style="display:none" type="text" value="0">
                <input style="display:none" name="type" type="text" value="2">

            </fieldset>
        </form>
    </div>

    <br><br>
    <div style="float:right">
        <button onclick='submitForm(0)''>Save</button>
        <button onclick='submitForm(1)'>Submit</button>
    </div>

</div>

<?php get_footer(); ?>
