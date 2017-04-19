<?php

/**
 * Plugin Name: WP Discourse Existing Topic
 * Plugin URI: https://github.com/joebuhlig/wp-discourse-existing-topic
 * GitHub Plugin URI: https://github.com/joebuhlig/wp-discourse-existing-topic
 * Description: This plugin adds the ability to embed an existing topic into a post.
 * Version: 1.0.1
 * Author: Joe Buhlig
 * Author URI: https://joebuhlig.com
 * License: GPL2
 */

/* Meta box setup function. */
function discourse_existing_topic_post_meta_boxes_setup() {

  /* Add meta boxes on the 'add_meta_boxes' hook. */
  add_action( 'add_meta_boxes', 'discourse_existing_topic_add_meta_box' );
}

/* Create one or more meta boxes to be displayed on the post editor screen. */
function discourse_existing_topic_add_meta_box() {

  add_meta_box(
    'discourse-existing-topic',      // Unique ID
    esc_html__( 'Discourse Existing Topic', 'example' ),    // Title
    'discourse_existing_topic_meta_box',   // Callback function
    'post',         // Admin page (or post type)
    'side',         // Context
    'high'         // Priority
  );
}

  /* Display the post meta box. */
function discourse_existing_topic_meta_box( $object, $box ) { ?>

  <?php wp_nonce_field( basename( __FILE__ ), 'discourse_existing_topic_nonce' ); ?>

  <p>
    <label for="discourse-permalink"><?php _e( "Topic Permalink", '12345' ); ?></label>
    <br />
    <input class="widefat" type="text" name="discourse-permalink" id="discourse-permalink" value="<?php echo esc_attr( get_post_meta( $object->ID, 'discourse_permalink', true ) ); ?>" size="30" />
  </p>
<?php }

/* Save the meta box's post metadata. */
function discourse_existing_topic_save_post_class_meta() {
  global $post;
  
  /* Verify the nonce before proceeding. */
  if ( !isset( $_POST['discourse_existing_topic_nonce'] ) || !wp_verify_nonce( $_POST['discourse_existing_topic_nonce'], basename( __FILE__ ) ) )
    return;

  /* Get the post type object. */
  $post_type = get_post_type_object( $post->post_type );

  /* Check if the current user has permission to edit the post. */
  if ( !current_user_can( $post_type->cap->edit_post, $post->ID ) )
    return;

  /* Get the posted data and sanitize it for use as an HTML class. */
  $new_discourse_permalink_value = ( isset( $_POST['discourse-permalink'] ) ? $_POST['discourse-permalink'] : '' );

  update_discourse_existing_topic_meta($post->ID, 'discourse_permalink', $new_discourse_permalink_value);
  update_discourse_existing_topic_meta($post->ID, 'publish_to_discourse', true);
}

function update_discourse_existing_topic_meta($post_id, $meta_key, $new_meta_value){
  /* Get the meta value of the custom field key. */
  $meta_value = get_post_meta( $post_id, $meta_key, true );

  /* If a new meta value was added and there was no previous value, add it. */
  if ( $new_meta_value && '' == $meta_value )
    add_post_meta( $post_id, $meta_key, $new_meta_value, true );

  /* If the new meta value does not match the old value, update it. */
  elseif ( $new_meta_value && $new_meta_value != $meta_value )
    update_post_meta( $post_id, $meta_key, $new_meta_value );

  /* If there is no new meta value but an old value exists, delete it. */
  elseif ( '' == $new_meta_value && $meta_value )
    delete_post_meta( $post_id, $meta_key, $meta_value );
}

/* Fire our meta box setup function on the post editor screen. */
add_action( 'load-post.php', 'discourse_existing_topic_post_meta_boxes_setup' );
add_action( 'load-post-new.php', 'discourse_existing_topic_post_meta_boxes_setup' );
add_action('save_post', 'discourse_existing_topic_save_post_class_meta', 11);