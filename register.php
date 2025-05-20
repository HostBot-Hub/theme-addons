<?php
/**
 * Register blocks encies for guttenberg integration
 *
 * @link https://github.com/Automattic/themes/blob/trunk/archeo/functions.php
 * 
*/
function bot_register_theme_blocks() {

     // Registration assets
     $registration = '/blocks/js/dist/blocks.js';
     $asset_file   = get_template_directory() . '/dist/blocks.asset.php';


     // Include the asset file to retrieve dependencies
     if(file_exists($asset_file)) {
          $asset        = include($asset_file);
          $dependencies = $asset['dependencies'];
          $version      = $asset['version'] ;
     }


     // Register the script once
     wp_register_script(
          'block-registration-script',
          get_template_directory_uri() . $registration,
          isset($dependencies) ? $dependencies : array('wp-blocks', 'wp-editor', 'wp-element'),
          isset($version) ? $version : filemtime(get_template_directory() . $registration),
          true
     );


     // Custom blocks for registration
     $blocks = [
          [
               'name' => 'custom/breadcrumbs',
               'script' => 'block-registration-script',
               'render_callback' => 'bot_breadcrumb_trail',
          ],
          [
               'name' => 'custom/attachment-view',
               'script' => 'block-registration-script', 
               'render_callback' => 'bot_attachment_view_render',
          ]
     ];


     // Loop through and register each block
     foreach($blocks as $block) {
          register_block_type(
               $block['name'],
               array(
                    'editor_script'   => $block['script'],
                    'render_callback' => $block['render_callback'],
               )
          );
     }
}
add_action('init', 'bot_register_theme_blocks');



/*
 * Register the block callbacks
 *
*/
require_once(get_template_directory() . '/blocks/breadcrumbs.php');
require_once(get_template_directory() . '/blocks/attachment-view.php');


?>