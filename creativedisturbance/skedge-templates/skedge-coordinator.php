<?php
  /* Template Name: Skedge-Coordinator */

  #connect
  require_once get_template_directory()."/skedge-api/Database.php";
  $conn = dbconn();

  #get skedge records
  $skedge = new Skedge;
  $forms = array();
  $forms = $skedge->retrieveActiveRecords($conn);

  get_header();
?>

<div id="content">
  <h2>Welcome back, <?php echo $current_user->first_name; ?>.</h2>
  <p>Here's a look at what's going on right now.</p>
  <br>
  <h4>Inbox</h4>
  <p>These forms are waiting to be assigned to a team member.</p>
  <ul>
    <?php if(!empty($forms)){foreach ($forms as $record) {
      if(empty($record['assigned_to']) && (($record['stage'] == 4) || $record['stage'] == 6)){
    ?>
    <li>
      <?php
        $id = $record['id'];
        $timestamp = $record['timestamp'];
        $created_by = $record['created_by'];
        $type = ucfirst($record['type']);
        if($record["stage"] == 4){
          echo "<a href=".add_query_arg('id', $record['id'], 'assignform').">[Processing] $type form from $created_by on $timestamp</a>";
        }else{
          echo "<a href=".add_query_arg('id', $record['id'], 'schedulepodcast').">[Editing] $type form from $created_by on $timestamp</a>";
        }
      ?>
    </li>
    <?php }}} ?>
  </ul>
  <br>
  <h4>In Progress</h4>
  <p>These forms have been assigned and are being worked on.</p>
  <ul>
    <?php if(!empty($forms)){foreach ($forms as $record) {
      if(!empty($record['assigned_to'])){
    ?>
    <li>
    <?php
        $id = $record['id'];
        $timestamp = $record['timestamp'];
        $created_by = $record['created_by'];
        $type = ucfirst($record['type']);
        if($record["stage"] == 4){
          echo "<a href=".add_query_arg('id', $record['id'], 'assignform').">$type form from $created_by on $timestamp</a>";
        }else{
          echo "<a href=".add_query_arg('id', $record['id'], 'schedulepodcast').">[Editing] $type form from $created_by on $timestamp</a>";
        }
    ?>
    </li>
    <?php }}} ?>
  </ul>
</div>
<?php get_footer(); ?>
