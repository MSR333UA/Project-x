<?php
/**
 * The Template for displaying agent archives, also referred to as 'Agent Directorys'
 *
 * Override this template by copying it to yourtheme/propertyhive/archive-agent.php
 *
 * @author      PropertyHive
 * @package     PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

get_header( 'propertyhive' ); global $wpdb; ?>

	<?php
        /**
         * propertyhive_before_main_content hook
         *
         * @hooked propertyhive_output_content_wrapper - 10 (outputs opening divs for the content)
         */
        do_action( 'propertyhive_before_main_content' );
    ?>

        <?php if ( apply_filters( 'propertyhive_show_page_title', true ) ) : ?>

            <h1 class="page-title"><?php echo get_the_title( ph_get_page_id( 'agent_directory' ) ); ?></h1>

        <?php endif; ?>

        <?php
        	 /**
             * propertyhive_before_agent_directory_loop hook
             */
            do_action( 'propertyhive_before_agent_directory_loop' );
        ?>

        <?php if ( have_posts() ) : ?>

        		<ul class="agents clear">

                <?php 
                	while ( have_posts() ) : 

                		the_post(); 

                		$agent = new PH_Agent( $post->ID );
                ?>

                	<li class="clear">

						<?php do_action( 'propertyhive_before_agent_directory_loop_item' ); ?>

					    <div class="agent-logo">
					    	<?php if ( $agent->get_logo_src() != '' ) { ?>
				    		<img src="<?php echo $agent->get_logo_src(); ?>" alt="<?php the_title(); ?>">
				    		<?php } ?>
					    </div>
					    
					    <div class="details">
					    
					    	<h3><?php the_title(); ?></h3>
					        
					    	<?php
                                // Output branches belonging to this agent
                                $branches = $agent->get_branches();

                                if ( !empty($branches) )
                                {
                                    echo '<div class="branches">';
                                    foreach ( $branches as $branch )
                                    {
                                        echo '<div class="branch">';

                                        echo '<h5>' . $branch->post_title . '</h5>';

                                        if ( $branch->get_formatted_full_address() != '' ) { echo '<div class="branch-address">' . $branch->get_formatted_full_address() . '</div>'; }

                                        echo '<div class="branch-telephone-numbers">' . 
                                            ( ( $branch->telephone_number_sales != '' ) ? 'T: ' . $branch->telephone_number_sales . '<br>' : '' ) . 
                                            ( ( $branch->telephone_number_lettings != '' ) ? 'T: ' . $branch->telephone_number_lettings . '<br>' : '' ) . 
                                            ( ( $branch->telephone_number_commercial != '' ) ? 'T: ' . $branch->telephone_number_commercial . '<br>' : '' ) . 
                                        '</div>';

                                        echo '<div class="branch-email-address">' . 
                                            ( ( $branch->email_address_sales != '' ) ? 'E: ' . $branch->email_address_sales . '<br>' : '' ) . 
                                            ( ( $branch->email_address_lettings != '' ) ? 'E: ' . $branch->email_address_lettings . '<br>' : '' ) . 
                                            ( ( $branch->email_address_commercial != '' ) ? 'E: ' . $branch->email_address_commercial . '<br>' : '' ) . 
                                        '</div>';

                                        echo '<div class="branch-actions">';

                                            if ( get_option( 'propertyhive_active_departments_sales' ) == 'yes' ) { echo '<a href="' . get_permalink( ph_get_page_id( 'search_results' ) ) . '?department=residential-sales&branch_id=' . $branch->id . '" class="button">' . __( 'View Properties For Sale', 'propertyhive' ) . '</a> '; }
                                            if ( get_option( 'propertyhive_active_departments_lettings' ) == 'yes' ) { echo '<a href="' . get_permalink( ph_get_page_id( 'search_results' ) ) . '?department=residential-lettings&branch_id=' . $branch->id . '" class="button">' . __( 'View Properties For Let', 'propertyhive' ) . '</a> '; }
                                            if ( get_option( 'propertyhive_active_departments_commercial' ) == 'yes' ) { echo '<a href="' . get_permalink( ph_get_page_id( 'search_results' ) ) . '?department=commercial&branch_id=' . $branch->id . '" class="button">' . __( 'View Commercial Properties', 'propertyhive' ) . '</a> '; }

                                        echo '</div>';

                                        echo '</div>';
                                    }
                                    echo '</div>';
                                }
                            ?>
						
					    </div>
					    
						<?php do_action( 'propertyhive_after_agent_directory_loop_item' ); ?>

					</li>

                <?php endwhile; // end of the loop. ?>

                </ul>

        <?php else: ?>

            <p class="propertyhive-info"><?php _e( 'No agents were found matching your criteria.', 'propertyhive' ); ?></p>

        <?php endif; ?>

        <?php
            /**
             * propertyhive_after_agent_directory_loop hook
             */
            do_action( 'propertyhive_after_agent_directory_loop' );
        ?>

    <?php
        /**
         * propertyhive_after_main_content hook
         *
         * @hooked propertyhive_output_content_wrapper_end - 10 (outputs closing divs for the content)
         */
        do_action( 'propertyhive_after_main_content' );
    ?>

<?php get_footer( 'propertyhive' ); ?>