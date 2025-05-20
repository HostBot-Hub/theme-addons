<?php

function NLPS_attachment_view_render() {

     if(!is_attachment()) {
          return '';
     }

     $post_id          = get_the_ID();
     $mime_type        = get_post_mime_type( $post_id );
     $file_url         = wp_get_attachment_url( $post_id );
     $attachment_title = get_the_title();

     // List of document MIME types that should be prefixed with '/document/'
     $document_mimes = array(
          'application/pdf',
          'application/msword',
          'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
          'application/vnd.ms-excel',
          'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
     );

     $output = '';

     // If the attachment is a document
     if ( in_array( $mime_type, $document_mimes ) ) {
          // For PDFs, you might choose to embed them with an iframe.
          if ( 'application/pdf' === $mime_type ) {
               $output .= '<iframe src="' . esc_url( $file_url ) . '" width="100%" height="600"></iframe>';
          } else {
               // For Word or Excel files, output a download link.
               $output .= '<p><a href="' . esc_url( $file_url ) . '">Download ' . esc_html( $attachment_title ) . '</a></p>';
          }
     }
     // If it�s an image, display it using an <img> tag.
     elseif ( strpos( $mime_type, 'image/' ) === 0 ) {
          $output .= '<figure>';
          $output .= '<img src="' . esc_url( $file_url ) . '" alt="' . esc_attr( $attachment_title ) . '" style="max-width:100%; height:auto;" />';
          $output .= '<figcaption>' . esc_html( $attachment_title ) . '</figcaption>';
          $output .= '</figure>';
     }
     // Fallback for other file types: just a download link.
     else {
          $output .= '<p><a href="' . esc_url( $file_url ) . '">Download ' . esc_html( $attachment_title ) . '</a></p>';
     }

     return $output;
}

?>