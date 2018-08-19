<?php
    $root_path = "../resources/inc/"; 
    require('../wp-blog-header.php');
	
	$servername = "";
	$username = "";
	$password = "";
	$dbname = "";
	
	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	
	// Check connection      
	if (mysqli_connect_error()) {
		$logMessage = 'MySQL Error: ' . mysqli_connect_error();
		die('Could not connect to the database');
	}
	/* change character set to utf8 */
	if (!$conn->set_charset("utf8")) {
		printf("Error loading character set utf8: %s\n", $conn->error);
	}

?>
<html lang="en">
    <head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Creative Disturbance Forms</title>
		<?php require($root_path.'head.php'); ?>
		<link href="../resources/css/jquery.scrollbar.css" rel="stylesheet">
		<script src="../resources/js/channel.js"></script>
        <link rel="stylesheet" href="../resources/css/channel.css">
        <style>
	        span.badge.badge-success{
				background-color: #66BB66;
				display: inline;
				margin-left: 15px;
			}
			#channel-update{
				font-style: italic;
			}
			#table-downloads, #table-title{
				cursor: pointer;
			}
			.table .glyphicon{
				margin-left: 15px;
			}
		</style>
    </head>
    <body>
        <?php include $root_path.'header.php' ?>
	    <div class="container reverse-text" style="padding-top: 40px;">
	        <div class="container">
                <h2><strong>Channel Stats</strong></h2>
                <p class="lead">Check out the top podcasts from each of our channels.</p>
	        </div>
	    </div>
	    <article>
			<div class="grey">
				<div class="container">				
			        <?php 
						function convert_smart_quotes($string) 
						{ 
						    $search = array(chr(145), 
						                    chr(146), 
						                    chr(147), 
						                    chr(148), 
						                    chr(151)); 
						    $replace = array("'", 
						                     "'", 
						                     '"', 
						                     '"', 
						                     '-'); 
						    return str_replace($search, $replace, $string); 
						}
			        	$sql = "SELECT DISTINCT `post_title` FROM `wp_posts` WHERE `post_type` = 'channel' ORDER BY `post_title` ASC";
				        if($stmt=$conn->prepare($sql)) { 
				            $stmt->execute(); 
			                $stmt->store_result();		
			                $stmt->bind_result($channel);
				        }
				        echo "<select id='channel-select' class='form-control'><option value=''>Select a Channel</option>";
				        while ($stmt->fetch()) {
				    		$channel = convert_smart_quotes($channel);
				        	echo "<option value='".$channel."'>" . $channel . "</option>";
						}
				        
				        echo "</select>";
			        ?>
				</div>
			</div>
			<div class="container" id="channel-container">
				<div class="row">
					<div class="col-xs-4">
						<img id="album" src="" />						
					</div>
					
					<div class="col-xs-8">
		        		<h2 id="channel-header"></h2>
						<div id="channel-downloads"></div>
						<div id="channel-podcasts"></div>
						<div id="channel-update"></div>
						<hr>
		        		<p id="channel-description"></p>
					</div>
				</div>
				<br><br><br>
				<table id="episode-list" class="table table-hover"></table>		
			</div>
        
        <?php include $root_path.'footer.php' ?>
        
        </article>
    </body>
</html>