<?php

/**
 * Switch to a different user. 
 * If current user is an admin and we haven't switched yet, store the current admin ID in a cookie.
 */
function custom_user_switching($user_id) {
    
    // If current user is admin and we haven't set an "original_admin_id" cookie, set it now
    if ( current_user_can('administrator') && ! isset($_COOKIE['original_admin_id']) ) {
        $current_admin_id = get_current_user_id();

        // Set cookie to expire in 1 hour (3600 seconds). Adjust to fit your needs.
        setcookie(
            'original_admin_id',
            $current_admin_id,
            time() + 3600,
            SITECOOKIEPATH,
            COOKIE_DOMAIN,
            is_ssl(),
            true // HTTPOnly
        );
        // Make sure the cookie is available this request by populating $_COOKIE immediately
        $_COOKIE['original_admin_id'] = $current_admin_id;
    }

    // Perform the actual switch
    wp_clear_auth_cookie();
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);

    // Redirect to admin dashboard (or somewhere else)
    wp_redirect(admin_url());
    exit();
}



/**
 * Add "Switch to User" link to each user row, if:
 * - Current user is admin
 * - The row user is NOT the current user
 */
function add_switch_user_link($actions, $user) {
    if ( current_user_can('administrator') && get_current_user_id() !== $user->ID ) {
        // Build a nonce-protected link
        $switch_link = wp_nonce_url(
            admin_url('users.php?action=switch_user&user=' . $user->ID),
            'switch_user_' . $user->ID
        );
        $actions['switch_user'] = '<a href="' . $switch_link . '">Switch To User</a>';
    }
    return $actions;
}
add_filter('user_row_actions', 'add_switch_user_link', 10, 2);



/**
 * If the URL has action=switch_user, verify nonce & switch.
 */
function handle_user_switching_action() {
    if ( isset($_GET['action']) && $_GET['action'] === 'switch_user' && current_user_can('administrator') ) {
        $user_id = isset($_GET['user']) ? intval($_GET['user']) : 0;
        
        // Check our nonce for security
        if ( $user_id && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'switch_user_' . $user_id ) ) {
            custom_user_switching($user_id);
        } else {
            wp_die('Security check failed.');
        }
    }
}
add_action('admin_init', 'handle_user_switching_action');



function add_switch_back_link($wp_admin_bar) {
    if ( isset($_COOKIE['original_admin_id']) ) {
        $original_admin_id = (int) $_COOKIE['original_admin_id'];

        // Show the link ONLY if you're not that same user
        if ( $original_admin_id > 0 && get_current_user_id() !== $original_admin_id ) {

            // Get the original user data for a nicer display
            $original_user = get_userdata($original_admin_id);
            $display_name  = $original_user ? $original_user->display_name : 'Original Admin'; 
            // or you could use $original_user->user_login if you prefer

            // Create a WP nonce
            $nonce = wp_create_nonce( 'switch_back_' . $original_admin_id );

            // Build the "Switch Back" URL
            $switch_url = add_query_arg( [
                'action'   => 'switch_back',
                'user'     => $original_admin_id,
                '_wpnonce' => $nonce
            ], admin_url('users.php'));

            // Add a node to the WP Admin Bar
            $args = [
                'id'    => 'switch-back',
                'title' => 'Switch Back to ' . esc_html($display_name),
                'href'  => $switch_url,
                'meta'  => ['class' => 'switch-back-link']
            ];
            $wp_admin_bar->add_node($args);
        }
    }
}
add_action('admin_bar_menu', 'add_switch_back_link', 100);



/**
 * Handle the switch_back action. Verifies nonce, checks cookie, switches back to original admin.
 */
function handle_switch_back_action() {
    if ( isset($_GET['action']) && $_GET['action'] === 'switch_back' ) {
        $user_id = isset($_GET['user']) ? intval($_GET['user']) : 0;

        if ( $user_id && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'switch_back_' . $user_id ) ) {
            // Check the cookie to ensure the user_id matches
            if ( isset($_COOKIE['original_admin_id']) && intval($_COOKIE['original_admin_id']) === $user_id ) {

                // Switch back
                wp_clear_auth_cookie();
                wp_set_current_user($user_id);
                wp_set_auth_cookie($user_id);

                // Clear the original_admin_id cookie now that we've switched back
                setcookie(
                    'original_admin_id',
                    '',
                    time() - 3600, // expire in the past
                    SITECOOKIEPATH,
                    COOKIE_DOMAIN,
                    is_ssl(),
                    true
                );

                // (Optional) Update $_COOKIE array so the link doesn�t appear if we immediately reload
                unset($_COOKIE['original_admin_id']);

                wp_redirect(admin_url());
                exit();
            } else {
                wp_die('Invalid switch back request or your session has expired.');
            }
        } else {
            wp_die('Security check failed.');
        }
    }
}
add_action('admin_init', 'handle_switch_back_action');

?>