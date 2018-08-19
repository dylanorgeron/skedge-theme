<?php
  /* Template Name: Skedge-Coordinator-Assignform */

  #connect
  require_once get_template_directory()."/skedge-api/Database.php";
  $conn = dbconn();

  #save
  if ( isset( $_POST['assignee'] ) ) {
    #post vars
    $skedge_id  = $_GET['id'];
    $assignee   = $_POST['assignee'];

    #assign and save
    $skedge = new Skedge();
    $skedge->id             = $skedge_id;
    $skedge->assigned_to    = $assignee;
    $skedge->saveRecord($conn);

    #make a history state for this action
    $history = new History();
    $history->skedge_id      = $skedge_id;
    $history->action         = "assigned this form to";
    $history->action_creator = $user_login;
    $history->action_target  = $assignee;
    $history->makeHistoryState($conn);

    header( "Location: ../" );
  }

  #retrieve
  if(!empty($_GET['id'])){
    $forms = array();
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
  }

    get_header();
?>

<h1>Assign Forms</h1>
<p>A producer has submitted one or more forms that require processing. Review the required actions and assign this record to a team member.</p>

<h4><b>Assignee's Responsibilities</b></h4>
<br>

<!-- voices and channel for stage 4  -->
<?php if ($forms['skedge']['stage'] == 4) { ?>
  <?php if(!empty($forms['guests'])){
      echo "<b>New Guest Info</b><br><ul>";
      foreach ($forms['guests'] as $guest) {
          echo "<li>";
          echo "<a href=".add_query_arg('id', $guest['id'], 'pendingvoice').">".$guest['first_name']." ".$guest['last_name']."</a>";
          echo "</li></ul>";
      }
  }?>
<?php } ?>

<!-- podcast for stage 6 -->
<?php if ($forms['skedge']['stage'] == 6) { ?>
  <h4>Podcast</h4>
  <p>
    The podcast details have been submitted and its time for editing to begin.
    The assignee will be responsible for following each step of the editting process
    to ensure that the podcast is published appropriately.
  </p>
  <?php echo "<a href=".add_query_arg('id', $forms['podcast']['id'], 'pendingpodcast').">View Podcast Form</a>"; ?>
<?php } ?>

<?php if(!empty($forms['skedge']['assigned_to'])){ ?>
    <div>Currently assigned to: <b><?php echo $forms['skedge']['assigned_to'] ?></b></div>
    <br>
<?php } ?>
<br><br>


<h4>Assign To</h4>
<form id="assignee-form" method="POST" enctype="multipart/form-data">
    <select name="assignee" id="assignee">
        <?php foreach ($forms['editors'] as $editor) {
            echo "<option value='".$editor['user_login']."'>";
            echo $editor['name'];
            echo "</option>";
        } ?>
    </select>
    <br><br>
    <button style="float:right">Assign Form</button>
</form>
<?php get_footer(); ?>
