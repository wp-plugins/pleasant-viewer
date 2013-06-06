<?php
/*
Plugin Name: Pleasant Viewer
Plugin URI: http://wordpress.org/plugins/pleasant-viewer/
Description: Easily share Wednesday Readings and other Bible and Science and Health citations online.  Uses cskit-rb to retrieve citations.
Donate URI: 
Author: Gabriel Serafini (ShareThePractice.org)
Author URI: http://sharethepractice.org/
Version: 1.0

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

Credits:
This plugin was conceived of at the first "Hack Your Church" hackathon held at the U.C. Berkeley CSO
in May of 2013.  Gabriel wrote the plugin, Cameron wrote cskit-rb, and James wrote a Javascript layer.

*/ 


// Strings
$pleasant_viewer_strings['title_placeholder'] = "New Inspiration";
$pleasant_viewer_strings['description_placeholder'] = "Add an introduction or description for these citations...";
$default_category_if_exists = "Inspiration";


/**
 * Shortcode functionality
 *
 * @param mixed $atts Optional. Attributes to use in shortcode
 * @return $content if not expired or valid interval
 */
function pleasantviewer_display_entry_form_shortcode($atts = array(), $content = '') {

	global $pleasant_viewer_strings;

// 	extract(shortcode_atts(
// 		array(
// 			'start_day_time' => 'now',
// 			'end_day_time' => '',
// 			'timezone_location' => $timezone_string,
// 			'message' => '',
// 		),
// 		$atts
// 	));

	$categories = get_categories(array('hide_empty'=>0));
	$category_options = "";
	foreach ($categories as $category) {

		if ($category->name != "News") {
			// Don't show "News" category in list

			$category_options .= '<option value="' . $category->cat_ID . '"';
			if (isset($_POST['post_category_id'])) {
				if ($category->cat_ID == strip_tags(stripslashes($_POST['post_category_id'])) ) {
					$category_options .= ' selected="selected" ';
				}
			} else if ($category->name == $default_category_if_exists ) {
				$category_options .= ' selected="selected" ';
			}
			$category_options .= '>' . $category->name . '</option>';

		}

	}

	// Set some functions
	$message = array();

	//Build the form
	
	ob_start();
	include('template_entry_form.php');
	$whq_form = ob_get_clean(); 

	if( isset($_POST['submit']) ) {

		// Set the required fields
		$required = array(
			'post_citations' => 'Citations'
			);

		// Loop through all the required $_POSTed data and display the errors
		foreach ( $required as $field => $value ) {
			if ( empty($_POST[$field]) ) {
			$message[] = $value . ' is empty';
			}
		}

		// See what to display on the page
		// Show error messages
		if ( $message ) {

			echo '<strong>Please fill in the required fields below.</strong>';
			echo '<ul>';

			foreach ($message as $error) {
				echo '<li><span style="color: #f00;">'. $error . '</span></li>';
			}
			echo '</ul>';

			return $whq_form;

		} else {
		
			if ($_POST['rendered_citations'] == "") {

				// We didn't get text in form, supplied by Javascript.  Use built-in PHP library
				// to call to the API

				$citation_list_raw = strip_tags(stripslashes($_POST['post_citations']));
				$citation_list_array = explode ("\n", $citation_list_raw);

				$post_category = strip_tags(stripslashes($_POST['post_category_id']));

				// jSON URL which should be requested
				$json_url = 'http://cskit-server.herokuapp.com/v1/text.json';

				$citation_list_retrieved = array();
				$citations_formatted = "";
				$readings = "";

				foreach ($citation_list_array as $citation) {

					if ($citation != "") {

						$volume = 'bible_kjv';
						if (is_numeric(substr($citation, 0, 1))) {
							$volume = 'science_health';
						}

						$readings .= $volume . " " . $citation . "|";

					}
				}

				$json_string = 'citations='. urlencode($readings) . '&format=plain_text';
				$curlopt_url = $json_url . '?' .  $json_string;
				$theBody = wp_remote_retrieve_body( wp_remote_get($curlopt_url) );
				$body = json_decode($theBody, true);

				foreach ($body as $citation) {

					$citation_list_retrieved[] = array(

						'citation' => $citation["citation"],
						'volume' => $volume,
						'text' => $citation["text"],
						'api_url' => $curlopt_url
		
						);

				}

				$have_citation_text = false;

				foreach ($citation_list_retrieved as $passage) {
					if ($passage['text'] != "") {
						$citations_formatted .= '<dl>';
						$citations_formatted .= '<dt>';
						if ($passage['volume'] == 'science_health') {
							$citations_formatted .= 'SH ';
						}
						$citations_formatted .= $passage['citation'] . '</dt>';
						$citations_formatted .= '<dd>';
						$citations_formatted .= nl2br($passage['text']);
						//$citations_formatted .= "<br />" . $passage['api_url'];
						$citations_formatted .= '</dd></dl>' . "\n\n";
						$have_citation_text = true;
					}
				}

			}
			else {
				// Javascript library provided rendered text, don't need to call API via PHP
				$citation_list_raw = strip_tags(stripslashes($_POST['post_citations']));
				$citations_formatted = $_POST['rendered_citations'];
				$have_citation_text = true;				
			}

			$allowed_html = array(
				'a' => array(
					'href' => array(),
					'title' => array()
				),
				'br' => array(),
				'em' => array(),
				'strong' => array(),
				'hr' => array(),
				'span' => array(
					'style' => array(),
					'class' => array(),
					'id' => array()),
				'div' => array(
					'style' => array(),
					'class' => array(),
					'id' => array()),
				'p' => array(
					'style' => array(),
					'class' => array(),
					'id' => array()),
				'dl' => array(
					'id' => array(),
					'class' => array()),
				'dt' => array(
					'id' => array(),
					'class' => array()),
				'dd' => array(
					'id' => array(),
					'class' => array()),
				);

			$post_title = "";
			if ($pleasant_viewer_strings['title_placeholder'] != strip_tags(stripslashes($_POST['post_topic']))) {
				$post_title = strip_tags(stripslashes($_POST['post_topic']));
			}

			$post_introduction = "";
			if ($pleasant_viewer_strings['description_placeholder'] != strip_tags(stripslashes($_POST['post_introduction']))) {
				$post_introduction = strip_tags(stripslashes($_POST['post_introduction']));
			}

			$formatted_post_body = "";
			$formatted_post_body .= '<div class="pleasant-viewer-post-introduction">' . "\n";
			$formatted_post_body .= wp_kses($post_introduction, $allowed_html) . "\n";
			$formatted_post_body .= '</div>' . "\n";
			$formatted_post_body .= '<div class="pleasant-viewer-citations-formatted">' . "\n";
			$formatted_post_body .= wp_kses($citations_formatted, $allowed_html);
			$formatted_post_body .= '</div>' . "\n";
			$formatted_post_body .= '<div class="pleasant-viewer-citations-list">' . "\n";
			$formatted_post_body .= wp_kses($citation_list_raw, $allowed_html) . "\n";
			$formatted_post_body .= '</div>';

			$options = get_option('pleasant_viewer_plugin_options');


			if (is_user_logged_in() && current_user_can("publish_posts")) {
				$post_status = 'publish';
			}
			else if ($options['pleasant_viewer_plugin_anonymous_user_id'] != "") {
				$post_author = $options['pleasant_viewer_plugin_anonymous_user_id'];
				$post_status = 'publish';
			}
			else {
				$post_author = 1;
				$post_status = 'publish';
			}			

			$whq_post_properties = array(
				'post_title' => $post_title,
				'post_content' => $formatted_post_body,
				'post_status' => $post_status,
				'post_author' => $post_author,
				'post_category' => array($post_category)
				);

			if (!$have_citation_text) {
				echo "<span style='color: #f00;'><strong>NOTE: Please supply at least one valid citation.</strong></span>";
				return $whq_form;		
			}

			$post_id = wp_insert_post( $whq_post_properties );

			if ( $post_id ) {
				// Add our custom fields
				add_post_meta($post_id, 'pleasantviewer_citations_list', strip_tags(stripslashes($_POST['post_citations'])));
				add_post_meta($post_id, 'pleasantviewer_introduction', $post_introduction);

				//wp_redirect( get_permalink($post_id) ); exit;

				echo '<p>Thank you for your submission.</p>';
				
				echo '<a href="' . get_permalink($post_id) . '">Click here to view and share this with others</a>';
				
				$hide_form = true;
			}
		}
	}
  
  if ( !$hide_form ) {
    return $whq_form;
  }

}

// Register shortcode
add_shortcode('pleasantviewer', 'pleasantviewer_display_entry_form_shortcode');


add_action('admin_menu', 'plugin_admin_add_page');

function plugin_admin_add_page() {
	add_options_page('Pleasant Viewer', 'Pleasant Viewer', 'manage_options', 'pleasant_viewer', 'pleasant_viewer_plugin_options_page');
}

function pleasant_viewer_plugin_options_page() {
?>
<div>
<h2>Pleasant Viewer Options</h2>

<p>Share inspiring texts - readings, lessons, benedictions and more!</p>

<p>To enable anonymous posting, include the <code>[pleasantviewer]</code> shortcode into the page where you want the form to appear.</p>

<form action="options.php" method="post">
<?php settings_fields('pleasant_viewer_plugin_options'); ?>
<?php do_settings_sections('pleasant_viewer'); ?>
 
<input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
</form></div>
 
<?php
}

add_action('admin_init', 'pleasant_viewer_plugin_admin_init');

function pleasant_viewer_plugin_admin_init(){
	register_setting( 'pleasant_viewer_plugin_options', 'pleasant_viewer_plugin_options', 'pleasant_viewer_plugin_options_validate' );
	add_settings_section('pleasant_viewer_plugin_main', 'Main Settings', 'pleasant_viewer_plugin_section_text', 'pleasant_viewer');
	add_settings_field('pleasant_viewer_plugin_anonymous_user_id', 'User to use for anonymous posts:', 'pleasant_viewer_plugin_anonymous_user_id', 'pleasant_viewer', 'pleasant_viewer_plugin_main');
}

function pleasant_viewer_plugin_section_text() {
	//echo '<p>Select user to use for anonymous posting.</p>';
}

function pleasant_viewer_plugin_anonymous_user_id() {

	$options = get_option('pleasant_viewer_plugin_options');

	$users = get_users();
	echo '<select id="pleasant_viewer_plugin_anonymous_user_id" name="pleasant_viewer_plugin_options[pleasant_viewer_plugin_anonymous_user_id]">';
	foreach ($users as $user) {
		echo '<option value="' . $user->data->ID . '"';
		if ($options['pleasant_viewer_plugin_anonymous_user_id'] == $user->data->ID) {
			echo ' selected="selected" ';
		}
		echo '>' . $user->data->display_name . '</option>';
	}
	echo "</select>";

}

function pleasant_viewer_plugin_options_validate($input) {
	$newinput['pleasant_viewer_plugin_anonymous_user_id'] = trim($input['pleasant_viewer_plugin_anonymous_user_id']);
	//if(!preg_match('/^[a-z0-9]{32}$/i', $newinput['pleasant_viewer_text_string'])) {
	//	$newinput['pleasant_viewer_text_string'] = '';
	//}
	return $newinput;
}


function pleasant_viewer_the_content_footer($content) {
	global $post;
	if (is_single() || is_archive() || is_home()) {
		$permalink = get_permalink();
		$content .= '<div class="pleasant-viewer-permalink">';
		$content .= 'Link to share this: <a href="' . $permalink . '">' . $permalink . '</a>';
		$content .= '</div>';
	}
	return $content;
}

function pleasant_viewer_styles() {
	?>

<style type="text/css">

.pleasant-viewer-permalink {
	margin: 12px 0;
	}

.pleasant-viewer-permalink a {
	background: #fdfac4;
	padding: 6px;
	}

.pleasant-viewer-citations-list {
	border: 1px solid #eee;
	padding: 6px;
	}

</style>

<!--<script src="https://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>-->
<!--<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>-->
<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>

	<?php
}


function pleasant_viewer_js() {
	global $pleasant_viewer_strings;
?>
	<script type="text/javascript">
		var title_placeholder = <?php echo json_encode($pleasant_viewer_strings['title_placeholder']); ?>;
		var description_placeholder = <?php echo json_encode($pleasant_viewer_strings['description_placeholder']); ?>;
	</script>

<?php
	wp_enqueue_script('the_js', plugins_url('/index.js',__FILE__) );

}

add_filter( 'the_content', 'pleasant_viewer_the_content_footer' );
add_action( 'wp_head', 'pleasant_viewer_styles');
add_action( 'wp_head', 'pleasant_viewer_js');


?>