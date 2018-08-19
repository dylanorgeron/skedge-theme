<?php 
	//API to return channel data and a list of the channel's episodes as JSON
	header('Content-Type: text/plain; charset=UTF-8');


	/* change character set to utf8 */
	$conn->set_charset("utf8");

	class ChannelInfo{
		public $channel_id	 	= "";
		public $title 			= "";
		public $description 	= "";
		public $image_url 		= "";
		public $episodes 		= array();
	};

	class EpisodeInfo{
		public $title 	= "";
		public $guid 	= "";
	}

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

	if(!empty($_GET["channel"])){
		$channel_name = $_GET["channel"];
		$episode_list = array();
		$channel_info = new ChannelInfo();
		$channel_info->title = $channel_name;

		//get channel post id from postmeta
		$sql = "SELECT `post_id` FROM `wp_postmeta` WHERE (`meta_key` = 'channel' OR `meta_key` = 'primary_channel') AND  `meta_value`='$channel_name'";

		if($stmt=$conn->prepare($sql)) { 
			$stmt->execute(); 
			$stmt->store_result();		
			$stmt->bind_result($post_id);
		}

		while ($stmt->fetch()) {
			//get all episodes for this channel
			$sql = "SELECT DISTINCT `post_title`, `guid` FROM `wp_posts` WHERE `id` = " . $post_id;
			if($episode_stmt=$conn->prepare($sql)) { 
				$episode_stmt->execute(); 
				$episode_stmt->store_result();		
				$episode_stmt->bind_result($post_title, $guid);
			}

			while ($episode_stmt->fetch()){
				$episode_info = new EpisodeInfo();
				$post_title = htmlspecialchars(trim($post_title));
				$episode_info->title = $post_title;
				$episode_info->guid = $guid;

				array_push($episode_list, $episode_info);
			}


			$episode_stmt->close();
		}


		//make unique
		$titles = [];
		foreach ($episode_list as $episode_list_item) {
			if(!in_array($episode_list_item, $titles)){
				array_push($titles, $episode_list_item);
				array_push($channel_info->episodes, $episode_list_item);
			}
		}
		
		$stmt->close();

		//get the channel meta information
		$sql = "SELECT `post_content`, `ID` FROM `wp_posts` WHERE `post_title` = '".$channel_name."' AND `post_type` = 'channel'";
		if($stmt=$conn->prepare($sql)) { 
			$stmt->execute(); 
			$stmt->store_result();		
			$stmt->bind_result($post_content, $post_id);
			while ($stmt->fetch()) {
				$post_content = convert_smart_quotes($post_content);
				$channel_info->description = $post_content;
				$channel_info->channel_id = $post_id;
			}
		}

		//get the channel logo
		$sql = "SELECT `meta_value` FROM `wp_postmeta` WHERE `meta_key` = 'album_cover' AND `post_id` = $channel_info->channel_id";
		if($stmt=$conn->prepare($sql)) { 
			$stmt->execute(); 
			$stmt->store_result();		
			$stmt->bind_result($image_post_id);
		
			while ($stmt->fetch()) {
		
				$sql = "SELECT `guid` FROM `wp_posts` WHERE `ID` = $image_post_id";
				if($image_stmt=$conn->prepare($sql)) { 
		
					$image_stmt->execute(); 
					$image_stmt->store_result();		
					$image_stmt->bind_result($guid);
		
					while ($image_stmt->fetch()) {
						$channel_info->image_url = $guid;
					}
				}
			}
		}				


		echo json_encode($channel_info);
	}else{
		echo "invalid params";
	}


?>