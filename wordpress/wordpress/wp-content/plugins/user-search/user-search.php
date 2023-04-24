<?php
/*
Plugin Name: FishSpeicesSearch
Description: Search fish name
Version: 1.0
Author: Yan Zhang
*/

function user_search() {
    $output = '<div class="search-container">
        <form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post">

            <input type="text" id="search" name="search" placeholder="Fish name...">
            <button type="submit" id="search-btn">Search</button>
        </form>
    </div>';

    if ( isset( $_POST['search'] ) ) {
        $search_term = sanitize_text_field( $_POST['search'] );

        global $wpdb;
        $table_name = 'Nspecies';
        $column_A = 'Fish_name';
        $column_B = 'Image';
		    $column_C = 'Minimum_legal_size';
		    $column_D = 'Bag_limit';       
        $column_E = 'Img_link';
        $column_F = 'Fish_link';
		
        $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE $column_A LIKE %s ", '%' . $wpdb->esc_like( $search_term ) . '%' ) );
        if ( ! empty( $results ) ) {
			$output .= '<div class="list-container">';
			
			$output .= '<ul class="my-list">';
            foreach ( $results as $row ) {
                
				$output .= "<li>";
				$output .= "<div>" . $row->$column_E . "</div>";
				$output .= "<h6>" . $row->$column_F . "</h6>";
				$output .= "</li>";
          }
			$output .= "</ul>";
			
			$output .= "</div>";
		
        } else {
            $output .= "<h6>No matched results</h6>";
        }
       
    }

    return $output;
}

add_shortcode( 'user_search', 'user_search' );

function custom_search_styles() {
    echo '
    <style>
        
    .search-container {
      text-align: right;
      margin-right: 5%;
    }

    
    #search {
      width: 250px;
      height: 30px;
      border-radius: 20px;
      border: none;S
      padding: 5px 10px;
      background-color: #ccc;
      display: inline-block;
      vertical-align: middle;
    }

    
    #search-btn {
      width: 120px;
      height: 40px;
      border-radius: 20px;
      border: none;
      margin-left: 10px;
      background-color: #0B119A;
      color: #fff;
      font-size: 15px;
      font-weight: bold;
      cursor: pointer;
      display: inline-block;
      vertical-align: middle;
    }
	
	.list-container {
        display: flex;
        justify-content: center;
      }

      .my-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
      }

      .my-list li {
        width: 300px;
        box-sizing: border-box;
        padding: 5px;
        background-color: #f2f2f2;
        margin: 35px;
        border-radius: 5px;
        text-align: center;
      }

      @media screen and (max-width: 768px) {
        .my-list li {
          width: calc(45% - 10px);
        }
      }

      @media screen and (max-width: 480px) {
        .my-list li {
          width: 100%;
        }
      }

	 a {
      text-decoration: none;
      color: black;
    }
    </style>';
}

add_action( 'wp_head', 'custom_search_styles' );