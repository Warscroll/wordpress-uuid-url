<?php
/*
Plugin Name: Secure UUID Permalinks
Description: Automatically generates a 64-character UUID as the slug for new posts and pages, with dashes added at random intervals.
Version: 1.0
Author: Warscroll
*/

function generate_custom_uuid_slug() {
    // Generate a random 64-character string
    $uuid = bin2hex(random_bytes(32)); // 64 characters

    // Add dashes at random intervals
    $num_dashes = rand(4, 8); // Random number of dashes to insert, e.g., between 4 and 8 dashes
    for ($i = 0; $i < $num_dashes; $i++) {
        $position = rand(1, strlen($uuid) - 1); // Choose a random position in the string for the dash
        $uuid = substr_replace($uuid, '-', $position, 0);
    }

    return $uuid;
}

function set_custom_uuid_slug_on_save($post_id) {
    // Avoid running on autosaves or revisions
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (wp_is_post_revision($post_id)) return;

    // Get the post type
    $post_type = get_post_type($post_id);
    
    // Apply only to posts and pages
    if (in_array($post_type, array('post', 'page'))) {
        // Get the current post
        $post = get_post($post_id);

        // Check if the post already has a slug; only set UUID if slug is empty
        if (empty($post->post_name)) {
            // Generate the custom UUID and update the post slug
            $uuid_slug = generate_custom_uuid_slug();
            // Temporarily remove the save_post action to avoid an infinite loop
            remove_action('save_post', 'set_custom_uuid_slug_on_save');
            wp_update_post(array(
                'ID' => $post_id,
                'post_name' => $uuid_slug,
            ));
            // Reattach the save_post action
            add_action('save_post', 'set_custom_uuid_slug_on_save');
        }
    }
}
add_action('save_post', 'set_custom_uuid_slug_on_save');
