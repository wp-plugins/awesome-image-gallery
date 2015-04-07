<?php 
/*
Plugin Name: Awesome image gallery wordpress.
Plugin URI: http://plugime.com/demo/awesome-gallery-wordpress
Description: This plugin will enable gallery in your wordpress site. You can use regular wordpress shortcode, just you have to add "easy" before the shortcode. Easy, right?
Author: Lazy Persons
Version: 1.0
Author URI: http://plugime.com/demo
*/

// Adding latest jQuery
function awesome_gallery_wp_latest_jquery() {
	wp_enqueue_script('jquery');
}
add_action('init', 'awesome_gallery_wp_latest_jquery');


// Adding necessary scripts & CSS
function awesome_gallery_plugin_main_files() {
    wp_enqueue_script( 'awesome-gallery-js', plugins_url( '/js/lightGallery.min.js', __FILE__ ), array('jquery'), 1.0, false);
    wp_enqueue_style( 'awesome-gallery-css', plugins_url( '/css/lightGallery.css', __FILE__ ));
    wp_enqueue_style( 'awesome-fontello-css', plugins_url( '/includes/fontello/fontello.css', __FILE__ ));
}

add_action('init','awesome_gallery_plugin_main_files');


// Registering shortcode
function awesome_gallery_shortcode( $attr ) {
	$post = get_post();

	static $instance = 0;
	$instance++;

	if ( ! empty( $attr['ids'] ) ) {
		if ( empty( $attr['orderby'] ) )
			$attr['orderby'] = 'post__in';
		$attr['include'] = $attr['ids'];
	}

	$output = apply_filters( 'post_gallery', '', $attr );
	if ( $output != '' )
		return $output;

	if ( isset( $attr['orderby'] ) ) {
		$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
		if ( !$attr['orderby'] )
			unset( $attr['orderby'] );
	}

	$html5 = current_theme_supports( 'html5', 'gallery' );
	extract(shortcode_atts(array(
		'id'         => '',
		'size'       => 'thumbnail',
		'include'    => '',
		'exclude'    => '',
		'margin'    => '10px',
		'link'       => '',
		'radius'       => '0px',
		'shadow'       => '0px',
		'shadow_color'       => '#666',
		'desc'       => 'true',
		'caption'       => 'true',
		'controls'       => 'true',
		'theme'       => '#666',
		'effect'       => 'slide'
	), $attr, 'gallery'));

	$id = intval($id);
	if ( 'RAND' == $order )
		$orderby = 'none';

	if ( !empty($include) ) {
		$_attachments = get_posts( array('include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );

		$attachments = array();
		foreach ( $_attachments as $key => $val ) {
			$attachments[$val->ID] = $_attachments[$key];
		}
	} elseif ( !empty($exclude) ) {
		$attachments = get_children( array('post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
	} else {
		
	}

	if ( empty($attachments) )
		return '';

	if ( is_feed() ) {
		$output = "\n";
		foreach ( $attachments as $att_id => $attachment )
			$output .= wp_get_attachment_link($att_id, $size, true) . "\n";
		return $output;
	}


	$gallery_style = $gallery_div = '';



	$size_class = sanitize_html_class( $size );
	$gallery_div = "
	
	<style type='text/css'>
		html body ul#lightGallery$id {margin-left:-$margin}
		html body ul#lightGallery$id li {margin-left:$margin;margin-bottom:$margin}
		html body ul#lightGallery$id li img{border-radius:$radius;box-shadow:$shadow $shadow $shadow $shadow_color}
		html body ul#lightGallery$id li .overlay_easy{background-color:$theme;}
	</style>
	
    <script type='text/javascript'>
    jQuery(document).ready(function() {
		jQuery('#lightGallery$id').lightGallery({
			desc        : $desc,
			caption     : $caption,
			mode   : '$effect'
		});
    });
    </script>
	
	<ul id='lightGallery$id' class='easy_gallery_wp'>";

	$output = apply_filters( 'gallery_style', $gallery_style . $gallery_div );

	$i = 0;
	foreach ( $attachments as $id => $attachment ) {
		if ( ! empty( $link ) && 'file' === $link )
			$image_output = wp_get_attachment_link( $id, $size, false, false );
		elseif ( ! empty( $link ) && 'none' === $link )
			$image_output = wp_get_attachment_image( $id, $size, false );
		else
			$image_output = wp_get_attachment_link( $id, $size, true, false );

		$image_meta  = wp_get_attachment_metadata( $id );
		
		$easy_gallery_big_image  = wp_get_attachment_image_src( $id, 'large', false);
		$easy_gallery_medium_image  = wp_get_attachment_image_src( $id, 'medium', false);
		
		$easy_gallery_title = $attachment->post_title;
		$easy_gallery_description = $attachment->post_excerpt;
		
		$easy_gallery_caption = $attachment->post_content;

		$orientation = '';
		if ( isset( $image_meta['height'], $image_meta['width'] ) )
			$orientation = ( $image_meta['height'] > $image_meta['width'] ) ? 'portrait' : 'landscape';

			
		if ($easy_gallery_caption) {
		
		$output .= "
			<li data-title='$easy_gallery_title' data-desc='$easy_gallery_description' data-responsive-src='$easy_gallery_caption' data-src='$easy_gallery_caption'>
				<div class='overlay_easy'></div>
				<div class='easy_icon_holder'>
					<span class='icon icon-awesome-play'></span>
				</div>				
				$image_output
			</li>";	
			
		}
		
		else {
		
		$output .= "
			<li data-title='$easy_gallery_title' data-desc='$easy_gallery_description' data-responsive-src='$easy_gallery_medium_image[0]' data-src='$easy_gallery_big_image[0]'>
				<div class='overlay_easy'></div>
				<div class='easy_icon_holder'>
					<span class='icon icon-awesome-plus'></span>
				</div>
				$image_output
			</li>";	
			
		}


	}

	$output .= "
		</ul>\n";

	return $output;
}


add_shortcode('gallery', 'awesome_gallery_shortcode');



?>