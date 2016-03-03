<?php 

/*
Plugin Name: Anthology
Plugin URI: 
Description: 
Author: 
Version: 
Author URI: 
*/


/*
 * custom post types
 */

 function anthology_set_post_types () {
      register_post_type(
        'work',
        array(
            'labels' => array(
                'name'          => __( 'Works'),
                'singular_name' => __( 'Work'),
                'all_items'     => __( 'All works'),
                'add_new_item'  => __( 'Add new work' ),
                'view_item'     => __( 'View work'),
                'edit_item'     => __( 'Edit work')
            ),
            'public'      => true,
            'has_archive' => false
        )
    );

   /*
    * should have sections
    *
    * section name (chapter, section, roman numerals, etc.)
    *
    */




 }

 add_action( 'init', 'anthology_set_post_types' );

function anthology_add_custom_meta_boxes() {
    add_meta_box('anthology_tei_attachment', 'TEI File', 'anthology_tei_attachment', 'work', 'normal', 'high');
}

add_action('add_meta_boxes', 'anthology_add_custom_meta_boxes');

/**
 * Allow XML uploads.
 */
function anthology_custom_upload_xml($mimes) {
    $mimes = array_merge($mimes, array('xml' => 'application/xml'));
    return $mimes;
}

add_filter('upload_mimes', 'anthology_custom_upload_xml');

/**
 * HTML for TEI file upload field.
 * check if it has a file--list name of uploaded file, allow for delete
 * allow multiple? not for the time being
 * if file is uploaded, show name
 * currently only allows only one file--new upload unlinks but does not delete 
 */
function anthology_tei_attachment() {

    $html .= '<p class="description">';
    $tei_file = get_post_meta( get_the_ID(), 'anthology_tei_attachment', true );
    if (get_post_meta(get_the_ID(), anthology_tei_attachment_url)[0] == ''){

        $html .= 'Upload your TEI file.';

    } else {
        $html .= 'You have already uploaded a TEI file. Uploading a new file will replace it. Current file: ';

         $html .= '<a href="'.$tei_file['url'].'">'.basename($tei_file['url']).'</a>';
    }

    wp_nonce_field(plugin_basename(__FILE__), 'anthology_tei_attachment_nonce');


    $html .= '</p>';
    $html .= '<input type="file" id="anthology_tei_attachment" name="anthology_tei_attachment" value="" size="25">';
    echo $html;
}

add_action('save_post', 'anthology_save_custom_meta_data');

/**
 * Parse data from TEI file upload field and save file to uploads folder.
 */
function anthology_save_custom_meta_data($id) {
    if(!empty($_FILES['anthology_tei_attachment']['name'])) {
        $supported_types = array('application/xml');
        $arr_file_type = wp_check_filetype(basename($_FILES['anthology_tei_attachment']['name']));
        $uploaded_type = $arr_file_type['type'];

        if(in_array($uploaded_type, $supported_types)) {
            $upload = wp_upload_bits($_FILES['anthology_tei_attachment']['name'], null, file_get_contents($_FILES['anthology_tei_attachment']['tmp_name']));
       
            if(isset($upload['error']) && $upload['error'] != 0) {
                wp_die('There was an error uploading your file. The error is: ' . $upload['error']);
            } else {

                /**   if (get_post_meta(get_the_ID(), anthology_tei_attachment_url)[0] != ''){
                    unlink( $upload[anthology_tei_attachment_url)[0]] );
                } */

                update_post_meta($id, 'anthology_tei_attachment', $upload);
               // update_post_meta($id, 'anthology_tei_attachment_url', wp_upload_dir()['url'].'/'.basename($_FILES['anthology_tei_attachment']['name']));
            }
        }
        else {
            wp_die("The file type that you've uploaded is not TEI/XML.");
        }
    }
}

function anthology_update_edit_form() {
    echo ' enctype="multipart/form-data"';
}

add_action('post_edit_form_tag', 'anthology_update_edit_form');



?> 