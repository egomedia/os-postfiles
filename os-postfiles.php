<?php
/*
Plugin Name: OS Post Files
Description: Automatically lists all the attached files for a particular post in a user-friendly manner.
Version: 0.2
Author: Oli Salisbury
*/

//Config
$os_postfiles_post_types_in = array(
	'post' => array(), 
	'page' => array(),
	'event' => array(), 
	'casestudy' => array()
);

/*
DO NOT EDIT BELOW HERE
*/

//hooks
if (WP_ADMIN) {
	add_action('admin_menu', 'os_postfiles_init');
	add_action('admin_head', 'os_postfiles_css');
	add_action('admin_head', 'os_postfiles_init_js');
	add_action('admin_footer', 'os_postfiles_init_footer');
}

//function to get all file attachments for a post
function get_attached_files($post_id=NULL) {
	global $post;
	//set default
	if (!$post_id) { $post_id = $post->ID; }
	//make query
	$q['post_type'] = 'attachment';
	$q['post_mime_type'] = 'application';
	$q['numberposts'] = -1;
	$q['post_parent'] = $post->ID;
	$q['orderby'] = 'menu_order';
	$q['order'] = 'ASC';
	//return query
	return get_posts($q);
}

//Inserts HTML for meta box, including all existing attachments
function os_postfiles_add() { 
	global $post;
	echo '<div id="os_postfiles">';
	echo '<div id="os_postfiles_buttons">';
	echo '<a href="media-upload.php?post_id='.$post->ID.'&TB_iframe=1" class="button button-highlighted thickbox">Upload File</a>';
	echo ' <a href="#" id="os_postfiles_ajaxclick" class="button">Refresh</a>';
	echo ' <span id="os_postfiles_loading" style="display:none;"><img src="images/loading.gif" alt="Loading..." style="width:auto; height:auto; margin:0; vertical-align:middle" /></span>';
	echo '</div>';
	echo '<div id="os_postfiles_alert"><em>Don\'t forget to click "Refresh" once you\'ve uploaded or edited your files</em></div>';
	echo '<ul id="os_postfiles_ajax">';
	$attachments = get_attached_files();
	foreach ($attachments as $obj) {
		$file_type = str_replace("application/", "", $obj->post_mime_type);
		echo '<li class="widget">';
		echo '<b class="';
		echo $file_type=='pdf' || $file_type=='msword' ? $file_type : '';
		echo '"></b> ';
		echo '<strong>'.$obj->post_title.'</strong>';
		echo '<a href="media-upload.php?post_id='.$post->ID.'&tab=gallery&TB_iframe=1" class="thickbox edit-post-attachment" rel="'.$post->ID.'">Edit</a>';
		echo '</li>';
	}
	echo '</ul>';
	echo '<div style="clear:both;"></div>';
	echo '</div>';
}

//Creates meta box on all defined post types
function os_postfiles_metabox() {
	global $_GET, $os_postfiles_post_types_in;
	//for each post type
	foreach ($os_postfiles_post_types_in as $posttype => $inouts) {
		//get lowest inout val first
		sort($inouts);
		//if lowest val of inout is above zero (include)
		if ($inouts[0]>0 && $inouts) {
			if (!in_array($_GET['post'], $inouts)) { continue; }
		//if inout is negative (disclude), or inout not set at all
		} else {
			if (in_array($_GET['post']*-1, $inouts)) { continue; }
		}
		//add meta box
		add_meta_box('os_postfiles_list', 'Attachments', 'os_postfiles_add', $posttype);
	}
}

//javascript in header
function os_postfiles_init_js() {
	echo '
	<script type="text/javascript" charset="utf-8">
	jQuery(document).ready(function(){
		//hide redundant boxes
		jQuery("#gallery-settings").hide();
		jQuery("tr.url").hide();
		jQuery("tr.align").hide();
		jQuery("tr.image-size").hide();
		jQuery("td.savesend input").hide();
		//ajax update
		jQuery("#os_postfiles_ajaxclick").click(function() {
			jQuery("#os_postfiles_alert").hide();
			jQuery("#os_postfiles_ajax").slideUp("fast", function() { jQuery("#os_postfiles_loading").show(); }).load("'.$_SERVER['REQUEST_URI'].' #os_postfiles_ajax", function() { 
				jQuery(this).slideDown("fast");
				jQuery("#os_postfiles_loading").hide();
			});
			return false;
		});
		//alert
		jQuery(".thickbox").live("click", function(){ jQuery("#os_postfiles_alert").show(); });
	});
	</script>';
}

//javascript in footer
function os_postfiles_init_footer() {
}

//plugin css
function os_postfiles_css() {
	echo '
	<style type="text/css">
	#os_postfiles { padding:10px 10px 0 10px; }
	#os_postfiles_buttons { margin-bottom:20px; }
	#os_postfiles ul { margin:0; padding:0; list-style:none; }
	#os_postfiles li { margin:0 10px 6px 0; padding:10px; clear:both; }
	#os_postfiles li b { display:inline-block; width:30px; height:25px; background:url(http://horizontestserver.co.uk/assets/ico-sprite.png) no-repeat 0 0; vertical-align:middle; }
	#os_postfiles li b.pdf { background-position:-30px 0; }
	#os_postfiles li b.msword { background-position:-60px 0; }
	#os_postfiles li a { float:right; line-height:29px; }
	#os_postfiles_alert { display:none; position:absolute; top:5px; left:130px; z-index:100; }
	#os_postfiles_alert em { display:block; background:#fff59b; padding:10px; position:relative; }
	#os_postfiles_alert em:after { 
		border-color: #fff59b transparent;
		border-style: solid;
		border-width: 10px 10px 0;
		bottom: -10px;
		content: "";
		display: block;
		left: 10px;
		position: absolute;
		width: 0;
	}
	</style>';
}

//initialise plugin
function os_postfiles_init() {
	wp_enqueue_script('jquery');
	os_postfiles_metabox();
}
?>