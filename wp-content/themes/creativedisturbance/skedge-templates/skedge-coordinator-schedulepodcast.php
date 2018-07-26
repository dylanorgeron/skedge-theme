<?php
    /* Template Name: Skedge-Coordinator-SchedulePodcast */

    #connect
    require_once get_template_directory()."/skedge-api/Database.php";
    $conn = dbconn();

    #save
    if ( isset( $_POST['schedule_date'] ) ) {
        $forms = array();
        #vars
        $skedge_id      = $_GET['id'];
        $assignee       = $_POST['assignee'];
        $schedule_date  = $_POST['schedule_date'];

        #instantiate
        $skedge = new Skedge();

        #assign and save
        $skedge->id             = $skedge_id;
        $skedge->assigned_to    = $assignee;
        $skedge->schedule_date  = $schedule_date;
        $skedge->saveRecord($conn, $user_login);

        #make a history state for this action
        $history = new History();
        $history->skedge_id      = $skedge_id;
        $history->action         = "scheduled this form for $schedule_date and assigned it to";
        $history->action_creator = $user_login;
        $history->action_target  = $assignee;
        $history->makeHistoryState($conn);

        header( "Location: ../" );
    }

    #retrieve
    if(!empty($_GET['id'])){
    	$skedge_id = $_GET['id'];

      #fetch main skedge record
    	$skedge = new Skedge();
    	$forms['skedge'] = $skedge->retrieveRecordById($conn, $skedge_id);

      #fetch associated records
      $skedge->retrieveAssociatedRecords($conn, $skedge_id, $forms);

      #retrieve editors
      $editors = array();
      #add current user to self assign
      $cu = array();
      $cu['user_login'] = $user_login;
      $cu['name']       = $current_user->display_name;
      array_push($editors, $cu);

      #sound designers
      $sound_designers = get_users(array('role'=>'sound_designer'));
      foreach ($sound_designers as $sd) {
          $sd_user = array();
          $sd_user['user_login']  = $sd->data->user_login;
          $sd_user['name']        = $sd->data->display_name;
          array_push($editors, $sd_user);
      }

      #coordinators
      $coordinator = get_users(array('role'=>'coordinator'));
      foreach ($coordinator as $c) {
          $c_user = array();
          $c_user['user_login']   = $c->data->user_login;
          $c_user['name']         = $c->data->display_name;
          array_push($editors, $c_user);
      }

      #add to response
      $forms['editors'] = $editors;

    }else{
    	#handle no id error
    }

    get_header();
?>

<h1>Schedule Podcast</h1>
<p>The producer has submitted their podcast details. Its time to assigne the podacst to a sound member so they can get to work.</p>

<h4>Podcast</h4>
<p>
  The podcast details have been submitted and its time for editing to begin.
  The assignee will be responsible for following each step of the editting process
  to ensure that the podcast is published appropriately.
</p>
<?php echo "<a href=".add_query_arg('id', $forms['podcast']['id'], 'pendingpodcast').">View Podcast Form</a>"; ?>
<br>
<br>

<?php if(!empty($forms['skedge']['assigned_to'])){ ?>
    <div>Currently assigned to: <b><?php echo $forms['skedge']['assigned_to'] ?></b></div>
    <div>Currently scheduled for: <b><?php echo $forms['skedge']['schedule_date'] ?></b></div>
<?php } ?>
<br><br>


<form id="assignee-form" method="POST" enctype="multipart/form-data">
    <b>Assign To</b>
    <br>
    <select name="assignee" id="assignee">
        <?php foreach ($forms['editors'] as $editor) {
            echo "<option value='".$editor['user_login']."'>";
            echo $editor['name'];
            echo "</option>";
        } ?>
    </select>
    <br>
    <br>
    <b>Schedule For</b>
    <input type="date" name="schedule_date" value="">
    <br><br>
    <button style="float:right" type="submit">Assign Form</button>
</form>
<?php get_footer(); ?>
