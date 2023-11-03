<?php
/**
 * Board Contractor
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Property_Board_Contractor
 */
class PH_Meta_Box_Property_Board_Contractor {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;

        $board_contractor_id = get_post_meta($post->ID, '_board_contractor_id', TRUE);
        if ( $board_contractor_id == '' )
        {
            $board_contractor_id = get_option( 'propertyhive_default_board_contractor' );
        }
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

            echo '<input type="hidden" name="_board_contractor_id" id="_board_contractor_id" value="' . $board_contractor_id . '">';

            // No board contractor currently selected
            
            echo '<div id="search_propertyhive_board_contractors"' . ( ($board_contractor_id != '') ? ' style="display:none"' : '' ) . '>';

                echo '<p class="form-field search_propertyhive_board_contractors_keyword_field">
                    <label for="search_propertyhive_board_contractors_keyword">' . __('Search Board Contractors', 'propertyhive') . '</label>
                    <input type="text" class="short" name="search_propertyhive_board_contractors_keyword" id="search_propertyhive_board_contractors_keyword" value="" placeholder="' . __( 'Start typing to search...' , 'propertyhive') . '">
                </p>';
                
                echo '<p class="form-field search_propertyhive_board_contractors_results">
                    <label for="search_propertyhive_board_contractors_results"></label>
                    <span id="search_propertyhive_board_contractors_results"></span>
                </p>';
                
            echo '</div>';

            echo '<div id="existing-board-contractor-details"' . ( ($board_contractor_id == '') ? ' style="display:none"' : '' ) . '>';

            echo '</div>';
	    
        echo '</div>';
        
        echo '</div>';

        echo '<script>
            
              function load_existing_board_contractor_contact(contact_id)
              {
                  // Do AJAX request
                  var data = {
                      action:         \'propertyhive_load_existing_board_contractor\',
                      contact_id:     contact_id,
                      security:       \'' . wp_create_nonce("load-existing-board-contractor") . '\',
                  };
        
                  jQuery.post( \'' . admin_url('admin-ajax.php') . '\', data, function(response) {
                      
                      jQuery(\'#existing-board-contractor-details\').html( response );
                      
                  });
                  
                  jQuery(\'#_board_contractor_id\').val(contact_id);
              }
    
              jQuery(document).ready(function()
              {
                  ';
                  
                  if ($board_contractor_id != '')
                  {
                      echo 'load_existing_board_contractor_contact(' . $board_contractor_id . ');';
                  }
                  
                  echo '
                                    
                  jQuery(\'body\').on(\'click\', \'a[id^=\\\'search-board-contractor-result-\\\']\', function()
                  {
                      var contact_id = jQuery(this).attr(\'id\');
                      contact_id = contact_id.replace(\'search-board-contractor-result-\', \'\');
                      
                      load_existing_board_contractor_contact(contact_id);
                      
                      jQuery(\'#search_propertyhive_board_contractors\').fadeOut(\'fast\', function()
                      {
                            jQuery(\'#existing-board-contractor-details\').fadeIn();
                      });
                      return false;
                  });
                  
                  jQuery(\'body\').on(\'click\', \'a#remove-board-contractor\', function()
                  {
                      jQuery(\'#existing-board-contractor-details\').fadeOut(\'fast\', function()
                      {
                            jQuery(\'#search_propertyhive_board_contractors\').fadeIn();
                      });
                      
                      jQuery(\'#_board_contractor_id\').val(\'\');
                      
                      return false;
                  });
                  
                  // Existing board contractor search
                  jQuery(\'#search_propertyhive_board_contractors_keyword\').keyup(function()
                  {
                      var keyword = jQuery(\'#search_propertyhive_board_contractors_keyword\').val();
                      
                      if (keyword.length == 0)
                      {
                          // Clear existing results
                          jQuery(\'#search_propertyhive_board_contractors_results\').stop(true, true).fadeOut(\'fast\');
                      }
                      else
                      {
                          jQuery(\'#search_propertyhive_board_contractors_results\').stop(true, true).fadeIn(\'fast\');
                          
                          if (keyword.length > 2)
                          {
                                // Do AJAX request
                                var data = {
                                    action:         \'propertyhive_search_board_contractors\',
                                    keyword:        keyword,
                                    security:       \'' . wp_create_nonce("search-board-contractors") . '\',
                                };
                        
                                jQuery.post( \'' . admin_url('admin-ajax.php') . '\', data, function(response) {
                                    
                                    if (response.length > 0)
                                    {
                                        var new_html = \'\';
                                        for (var i in response)
                                        {
                                            new_html += \'<a href="#" id="search-board-contractor-result-\' + response[i].ID + \'">\' + response[i].post_title + \'</a><br>\';
                                        }
                                        jQuery(\'#search_propertyhive_board_contractors_results\').html(new_html);
                                    }
                                    else
                                    {
                                        jQuery(\'#search_propertyhive_board_contractors_results\').html(\'' . __( 'No board contractors found', 'propertyhive' ) . '\');
                                    }
                                    
                                });
                          }
                          else
                          {
                              jQuery(\'#search_propertyhive_board_contractors_results\').html(\'' . __( 'Keep on typing...', 'propertyhive' ) . '\');
                          }
                      }
                  });
              });
          </script>';
        
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;

        update_post_meta( $post_id, '_board_contractor_id', $_POST['_board_contractor_id'] );
    }

}
