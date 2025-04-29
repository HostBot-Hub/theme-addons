<?php


function custom_duplicate_post_as_draft() {
    global $wpdb;

    // Security check
    if (!(isset($_GET['post']) || isset($_POST['post']) || (isset($_REQUEST['action']) && 'custom_duplicate_post_as_draft' == $_REQUEST['action']))) {
        wp_die(__('No post to duplicate has been supplied!'));
    }

    // Get the original post ID
    $post_id = (isset($_GET['post']) ? $_GET['post'] : $_POST['post']);
    $post = get_post($post_id);

    // Verify the post exists
    if ($post == null) {
        wp_die('Post creation failed, could not find original post.');
    }

    // Current user as post author
    $current_user = wp_get_current_user();
    $new_post_author = $current_user->ID;

    // Prepare post data for duplication
    $args = array(
        'comment_status' => $post->comment_status,
        'ping_status'    => $post->ping_status,
        'post_author'    => $new_post_author,
        'post_content'   => $post->post_content,  // Keeping post content
        'post_excerpt'   => $post->post_excerpt,
        'post_parent'    => $post->post_parent,
        'post_password'  => $post->post_password,
        'post_status'    => 'draft',  // Set as draft
        'post_title'     => $post->post_title . ' (Copy)',
        'post_type'      => $post->post_type,
        'to_ping'        => $post->to_ping,
        'menu_order'     => $post->menu_order,
    );

    // Insert the duplicated post
    $new_post_id = wp_insert_post($args);

    if (is_wp_error($new_post_id)) {
        wp_die($new_post_id->get_error_message());
    }

    // Duplicate all post meta
    $post_meta_keys = get_post_custom_keys($post_id);
    if (!empty($post_meta_keys)) {
        foreach ($post_meta_keys as $meta_key) {
            $meta_values = get_post_custom_values($meta_key, $post_id);
            foreach ($meta_values as $meta_value) {
                $meta_value = maybe_unserialize($meta_value);
                update_post_meta($new_post_id, $meta_key, wp_slash($meta_value));
            }
        }
    }

    // Regenerate Elementor CSS for the new post
    if (class_exists('Elementor\Core\Files\CSS\Post')) {
        $css = Elementor\Core\Files\CSS\Post::create($new_post_id);
        $css->update();
    }

    // Redirect to edit the new duplicated post
    wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id));
    exit;
}


// Add the duplicate link to post/page list
function custom_duplicate_post_link($actions, $post) {

     if (current_user_can('edit_posts')) {
          $actions['duplicate'] = '<a href="' . wp_nonce_url('admin.php?action=custom_duplicate_post_as_draft&post=' . $post->ID, basename(__FILE__), 'duplicate_nonce') . '" title="Duplicate this item" rel="permalink">Duplicate</a>';
     }
     return $actions;
}
add_filter('post_row_actions', 'custom_duplicate_post_link', 10, 2);
add_filter('page_row_actions', 'custom_duplicate_post_link', 10, 2);


// Add admin action for duplicating
add_action('admin_action_custom_duplicate_post_as_draft', 'custom_duplicate_post_as_draft');

?>