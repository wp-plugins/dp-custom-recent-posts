<?php
/*
Plugin Name: dp Custom Recent Posts

Description: Create your custom, recent posts list by choosing certain post from a drop down list. This plugin is similar to the default "recent posts" plugin of Wordpress, but you can choose specific posts you want to show up in the widget.
Version: 0.99
Author: DanP.
Author URI: http://www.danielp.eu
*/

/*  Copyright 2010  DanP.  (email : mail@danielp.eu)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

	 
	 function my_init_method() {
		
		$url = plugins_url( 'dp-custom-recent-posts-functions.js' , __FILE__ ).
	
		wp_deregister_script( 'dp_functions' );
		wp_register_script( 'dp_functions', $url);
		wp_enqueue_script( 'dp_functions' );
	}
	 
	// Widget activation
	add_action('widgets_init', create_function('', 'return register_widget("dp_custom_recent_posts_widget");'));
	
	//load javascript
	add_action('init', 'my_init_method');

class dp_custom_recent_posts_widget extends WP_Widget {

	function dp_custom_recent_posts_widget() {
		$widget_ops = array('classname' => 'dp-custom-recent-posts', 'description' => __( "Create a custom list of your (recent) posts on your blog") );
		$control_ops = array('width' => 400);
		$this->WP_Widget('dp-custom-recent-posts', __('dp Custom Recent Posts'), $widget_ops, $control_ops);

	}

	function widget($args, $instance) {
		$cache = wp_cache_get('dp-custom-recent-posts-widget', 'widget');

		if ( !is_array($cache) )
			$cache = array();

		if ( isset($cache[$args['widget_id']]) ) {
			echo $cache[$args['widget_id']];
			return;
		}

		ob_start();
		extract($args);

		$title = apply_filters('widget_title', empty($instance['title']) ? __('dp-custom-recent-posts') : $instance['title']);
		if ( !$number = (int) $instance['number'] )
			$number = 10;
		else if ( $number < 1 )
			$number = 1;
		else if ( $number > 30 )
			$number = 30;

		//$id = $args['widget_id'];

?>
		<?php echo $before_widget; ?>
		<?php if ( $title ) echo $before_title . $title . $after_title; ?>
		<ul>
		<?php 
		
		$dp_widget_id = isset($instance['dp_widget_id']) ? esc_attr($instance['dp_widget_id']) : '';
		$number_posts = isset($instance['number_posts']) ? esc_attr($instance['number_posts']) : '';
		for ($i=1;$i<=$number_posts;$i++){
			//creates i variables, depending on setting "Number Posts"
			//$custom_post1 = isset($instance['custom_post']) ? esc_attr($instance['custom_post']) : '';
			${custom_post.$i} = isset($instance['custom_post'.$i]) ? esc_attr($instance['custom_post'.$i]) : '';


			$id = ${custom_post.$i};
			$title = get_the_title($id);
			$permalink = get_permalink( $id );
		?><li><a href="<?php echo $permalink ?>" title="<?php echo $title ?>"><?php echo $title ?></a></li>
		<?php } ?>
		</ul>
		
		<?php echo $after_widget; ?>
<?php
		wp_reset_query();  // Restore global post data stomped by the_post().

		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_add('dp-custom-recent-posts-widget', $cache, 'widget');
	}

	function flush_widget_cache() {
		wp_cache_delete('dp-custom-recent-posts-widget', 'widget');
	}

	function form( $instance ) {
	
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		
		if ( !isset($instance['number_posts']) || !$number_posts = (int) $instance['number_posts'] )
			$number_posts = 5;
		if ( !isset($instance['number']) || !$number = (int) $instance['number'] )
			$number = 15;
		
		$number_posts = isset($instance['number_posts']) ? esc_attr($instance['number_posts']) : '';
		for ($i=1;$i<=$number_posts;$i++){
			//creates i variables, depending on setting "Number Posts"
			//$custom_post = isset($instance['custom_post']) ? esc_attr($instance['custom_post']) : '';
			${custom_post.$i} = isset($instance['custom_post'.$i]) ? esc_attr($instance['custom_post'.$i]) : '';
		}
?>

		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p>
		<label for="<?php echo $this->get_field_id('number_posts'); ?>"><?php _e('Number of Custom Posts:'); ?></label>
		<input id="<?php echo $this->get_field_id('number_posts'); ?>" name="<?php echo $this->get_field_name('number_posts'); ?>" type="text" value="<?php echo $number_posts; ?>" size="3" /><br />
		
		
		<label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of elements in dropdown list'); ?></label>
		<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" />
		</p>
		
		<?php
		$args = array(
			'numberposts' => $number,
			'order' => 'DESC',
			'orderby' => 'date'
			); 
		
		$get_posts = get_posts($args);
		$tot_posts = count($get_posts);
		?>
		
		<div id="dp_input_container" >
		<?php if ($number_posts){ ?>
			<select id="selected_post" style="width:300px;">
			<option value="0">[Select post and click "Add..."]</option>
			
			<?php
			//drop down list
			if ($tot_posts) { 
				foreach($get_posts as $post) :
					$select_post_selected = ( isset($_POST['select_post']) && $post->ID == $_POST['select_post'] ) ? 'selected="selected"': '';
					echo '<option value="'.$post->ID.'" '. $select_post_selected .'>&middot; '.$post->ID.' -> '. get_the_title($post->ID).' </option>';
				endforeach;
			}
			?>
			
			
			</select>
		
			<input name="addSelectedPost" type="button" value="Add..." onClick="javascript:addPost()">
			
		<?php } ?>
		<br />
	
		<div id="inputs_id"  style="width:50px;float:left;">		
		<?php 
		//creates input fields with post id
		for ($i=1;$i<=$number_posts;$i++){ ?>
			<input class="widefat" id="<?php echo 'input_id'.$i ?>" name="<?php echo $this->get_field_name('custom_post'.$i); ?>" type="text" value="<?php echo ${custom_post.$i}; ?>" />
		<?php }
		?>
		</div>
		<div id="inputs_title"  style="width:339px;padding-left:60px;">
		<?php 
		// creates divs with post title
		for ($i=1;$i<=$number_posts;$i++){ ?>
			<div id="<?php echo 'input_title'.$i ?>" style="border:1px solid #CCCCCC;line-height:21px;padding-left:5px;"><?php echo "&nbsp;".get_the_title(${custom_post.$i});?></div>
		<?php }
		
		?>
		
		</div>		
		</div>
		<div style="float:clear"></div><br />
		
		<?php
	}
}
?>
