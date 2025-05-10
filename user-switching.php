<?php
/**
 * Snippet Name:    User Switching
 * Snippet Type:    PHP (WordPress admin hook)
 * Description:     Lets an admin switch to another user (stashing their own ID in a cookie) and switch back.
 * Author:          HostBot
 * Version:         1.1.0
 *
 * Changelog:
 * - 1.1.0: Added user existence checks, safe redirects, i18n, and consistent cookie handling.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'BOT_ORIGINAL_ADMIN_COOKIE', 'bot_original_admin_id' );
define( 'BOT_COOKIE_EXPIRE',          3600 ); // seconds

/**
 * Core switch logic.
 */
function bot_user_switching( $user_id ) {
    if ( current_user_can( 'administrator' ) && ! isset( $_COOKIE[ BOT_ORIGINAL_ADMIN_COOKIE ] ) ) {
        $orig = get_current_user_id();
        setcookie(
            BOT_ORIGINAL_ADMIN_COOKIE,
            $orig,
            time() + BOT_COOKIE_EXPIRE,
            COOKIEPATH,
            COOKIE_DOMAIN,
            is_ssl(),
            true
        );
        $_COOKIE[ BOT_ORIGINAL_ADMIN_COOKIE ] = $orig;
    }

    wp_clear_auth_cookie();
    wp_set_current_user( $user_id );
    wp_set_auth_cookie( $user_id );
    wp_safe_redirect( admin_url() );
    exit();
}

/**
 * “Switch To” link in each user row.
 */
function bot_add_switch_user_link( $actions, $user ) {
    if ( current_user_can( 'administrator' ) && get_current_user_id() !== $user->ID ) {
        $target = get_userdata( $user->ID );
        if ( $target && user_can( $target, 'read' ) ) {
            $url = wp_nonce_url(
                add_query_arg(
                    [
                        'action' => 'bot_switch_user',
                        'user'   => $user->ID,
                    ],
                    admin_url( 'users.php' )
                ),
                'bot_switch_user_' . $user->ID
            );
            $actions['bot_switch_user'] = sprintf(
                '<a href="%1$s">%2$s</a>',
                esc_url( $url ),
                esc_html__( 'Switch To User', 'bot-user-switch' )
            );
        }
    }
    return $actions;
}
add_filter( 'user_row_actions', 'bot_add_switch_user_link', 10, 2 );

/**
 * Handle “switch to” action.
 */
function bot_handle_user_switching_action() {
    if (
        empty( $_GET['action'] ) ||
        'bot_switch_user' !== $_GET['action']
    ) {
        return;
    }

    if (
        ! empty( $_GET['user'] ) &&
        ! empty( $_GET['_wpnonce'] ) &&
        current_user_can( 'administrator' ) &&
        wp_verify_nonce( $_GET['_wpnonce'], 'bot_switch_user_' . intval( $_GET['user'] ) )
    ) {
        $user_id = intval( $_GET['user'] );
        $target  = get_userdata( $user_id );

        if ( $target && user_can( $target, 'read' ) ) {
            bot_user_switching( $user_id );
        }
    }

    wp_die( esc_html__( 'Invalid user selected or security check failed.', 'bot-user-switch' ) );
}
add_action( 'admin_init', 'bot_handle_user_switching_action' );

/**
 * “Switch Back” link in the admin bar.
 */
function bot_add_switch_back_link( $wp_admin_bar ) {
    if ( empty( $_COOKIE[ BOT_ORIGINAL_ADMIN_COOKIE ] ) ) {
        return;
    }

    $orig = intval( $_COOKIE[ BOT_ORIGINAL_ADMIN_COOKIE ] );
    if ( $orig && get_current_user_id() !== $orig ) {
        $user  = get_userdata( $orig );
        $name  = $user ? $user->display_name : __( 'Original Admin', 'bot-user-switch' );
        $nonce = wp_create_nonce( 'bot_switch_back_' . $orig );
        $url   = add_query_arg(
            [
                'action'   => 'bot_switch_back',
                'user'     => $orig,
                '_wpnonce' => $nonce,
            ],
            admin_url( 'users.php' )
        );

        $wp_admin_bar->add_node( [
            'id'    => 'bot-switch-back',
            'title' => sprintf( esc_html__( 'Switch Back to %s', 'bot-user-switch' ), esc_html( $name ) ),
            'href'  => esc_url( $url ),
            'meta'  => [ 'class' => 'bot-switch-back-link' ],
        ] );
    }
}
add_action( 'admin_bar_menu', 'bot_add_switch_back_link', 100 );

/**
 * Handle “switch back” action.
 */
function bot_handle_switch_back_action() {
    if (
        empty( $_GET['action'] ) ||
        'bot_switch_back' !== $_GET['action']
    ) {
        return;
    }

    if (
        ! empty( $_GET['user'] ) &&
        ! empty( $_GET['_wpnonce'] ) &&
        wp_verify_nonce( $_GET['_wpnonce'], 'bot_switch_back_' . intval( $_GET['user'] ) ) &&
        isset( $_COOKIE[ BOT_ORIGINAL_ADMIN_COOKIE ] ) &&
        intval( $_COOKIE[ BOT_ORIGINAL_ADMIN_COOKIE ] ) === intval( $_GET['user'] )
    ) {
        $user_id = intval( $_GET['user'] );

        wp_clear_auth_cookie();
        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id );

        setcookie(
            BOT_ORIGINAL_ADMIN_COOKIE,
            '',
            time() - BOT_COOKIE_EXPIRE,
            COOKIEPATH,
            COOKIE_DOMAIN,
            is_ssl(),
            true
        );
        unset( $_COOKIE[ BOT_ORIGINAL_ADMIN_COOKIE ] );

        wp_safe_redirect( admin_url() );
        exit();
    }

    wp_die( esc_html__( 'Invalid switch‑back request or session expired.', 'bot-user-switch' ) );
}
add_action( 'admin_init', 'bot_handle_switch_back_action' );