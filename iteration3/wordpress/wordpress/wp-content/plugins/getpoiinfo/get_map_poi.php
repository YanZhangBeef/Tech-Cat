<?php
/*
Plugin Name: GetMapPoi
Description: get_map_poi
Version: 1.11
Author: ZT
*/

function get_map_poi() {
 
    global $wpdb;
    $table_name = '`pier marker`';
    $column_A = 'location_name';
    $column_B = 'fish_species';
    $column_C = 'fishing_allowed';
    $column_D = 'region';
    $column_E = 'latitude';       
    $column_F = 'longitude';

    $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name"  ) );
    if ( ! empty( $results ) ) {
      $output = ' <script> var my_markers = new Set();';
      foreach ( $results as $row ) {
        $output .= 'var my_markers_item = new Map();';
        $output .= 'my_markers_item.set("'.$column_A .'", "'. $row->$column_A .'");';
        $output .= 'my_markers_item.set("'.$column_B .'", "'. $row->$column_B .'");';
        $output .= 'my_markers_item.set("'.$column_C .'", "'. $row->$column_C .'");';
        $output .= 'my_markers_item.set("'.$column_D .'", "'. $row->$column_D .'");';
        $output .= 'my_markers_item.set("'.$column_E .'", '. $row->$column_E .');';
        $output .= 'my_markers_item.set("'.$column_F .'", '. $row->$column_F .');';
        $output .= 'my_markers.add(my_markers_item);';
      }
      $output .= '</script>';
    } else {
      $output = '<p>get data error</p> ';
    }

    return $output;
}

add_shortcode( 'get_map_poi', 'get_map_poi' );
