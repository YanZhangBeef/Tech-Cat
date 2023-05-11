<?php
	/*
    * Template Name: Testing
	* Template Post Type: post, page 
    */

	get_header();
	$var = "";

	if ( isset ( $_GET['Species'] ) ) {
	
		$var = sanitize_text_field($_GET['Species']);
		global $wpdb;
        $table_name = 'Nspecies';
        $column_A = 'Fish_name';
        $column_B = 'Image';
		    $column_C = 'Minimum_legal_size';
		    $column_D = 'Bag_limit';       
        $column_E = 'Img_link';
        $column_F = 'Fish_link';
		
		$results = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE $column_A LIKE %s ", $var ) );
		if ( ! empty( $results ) ) {
			
		
        } else {
            $output .= "<h6>No matched results</h6>";
        }

	}
	echo "Hello " . $var;
	
 	get_footer();

?>