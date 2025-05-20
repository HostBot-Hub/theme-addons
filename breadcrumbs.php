<?php
/**
 * Breadcrumb logic
 *
*/
function bot_breadcrumb_trail() {

     $separator  = ' > ';
     $home_text  = 'Home';
     $breadcrumb = [];

     // Add Home link
     $breadcrumb[] = '<a href="' . home_url() . '">' . $home_text . '</a>';

     if(is_single()) {

          $post_type = get_post_type();

          if ($post_type === 'post') {
               $categories = get_the_category();

               if ($categories) {
                    $breadcrumb[] = '<a href="' . get_category_link($categories[0]->term_id) . '">' . $categories[0]->name . '</a>';
               }
          } 
          else {
               $post_type_obj = get_post_type_object($post_type);

               if ($post_type_obj && !empty($post_type_obj->labels->singular_name)) {
                    $breadcrumb[] = $post_type_obj->labels->singular_name;
               }
          }
          $breadcrumb[] = get_the_title();
     } 
     elseif(is_page()) {

          global $post;

          $parents = [];
          $parent_id = $post->post_parent;

          // Traverse up the page tree
          while ($parent_id) {
               $page = get_post($parent_id);
               $parents[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
               $parent_id = $page->post_parent;
          }

          // Since we’re going bottom-up, reverse it
          $parents    = array_reverse($parents);
          $breadcrumb = array_merge($breadcrumb, $parents);

          $breadcrumb[] = get_the_title();
     } 
     else {
          $breadcrumb_map = [
               'is_category' => single_cat_title('', false),
               'is_tag'      => single_tag_title('', false),
          ];

          foreach($breadcrumb_map as $func => $label) {
               if (function_exists($func) && $func()) {
                    $breadcrumb[] = $label;
                    break;
               }
          }
     }
     return '<div class="breadcrumb-trail">' . implode($separator, $breadcrumb) . '</div>';
}

?>