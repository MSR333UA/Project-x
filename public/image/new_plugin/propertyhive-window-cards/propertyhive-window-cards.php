<?php
/**
 * Plugin Name: Property Hive Window Cards Add On
 * Plugin Uri: http://wp-property-hive.com/addons/window-cards/
 * Description: Add On for Property Hive allowing users to create printable window cards
 * Version: 1.0.2
 * Author: PropertyHive
 * Author URI: http://wp-property-hive.com
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once( dirname(__FILE__) . '/vendor/autoload.php' );
//require_once( dirname(__FILE__) . '/includes/dompdf/autoload.inc.php' );

use mikehaertl\wkhtmlto\Pdf;
//use Dompdf\Dompdf;

if ( ! class_exists( 'PH_Window_Cards' ) ) :

final class PH_Window_Cards {

    /**
     * @var string
     */
    public $version = '1.0.2';

    /**
     * @var Property Hive The single instance of the class
     */
    protected static $_instance = null;
    
    /**
     * Main Property Hive Window Cards Instance
     *
     * Ensures only one instance of Property Hive Window Cards is loaded or can be loaded.
     *
     * @static
     * @return Property Hive Window Cards - Main instance
     */
    public static function instance() 
    {
        if ( is_null( self::$_instance ) ) 
        {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {

        $this->id    = 'windowcards';
        $this->label = __( 'Window Cards', 'propertyhive' );

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        add_action( 'admin_notices', array( $this, 'window_cards_error_notices') );

        add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ), array( $this, 'plugin_add_settings_link' ) );

        add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_tab' ), 19 );
        add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'propertyhive_settings_save_' . $this->id, array( $this, 'save' ) );
        add_action( 'propertyhive_admin_field_window_card_header_image', array( $this, 'window_card_header_image_file_upload' ) );

        add_filter( 'propertyhive_admin_property_actions', array( $this, 'add_window_card_to_property_actions' ), 10, 2 );

        add_action( 'admin_init', array( $this, 'do_window_card' ) );
    }

    public function plugin_add_settings_link( $links )
    {
        $settings_link = '<a href="' . admin_url('admin.php?page=ph-settings&tab=windowcards') . '">' . __( 'Settings' ) . '</a>';
        array_push( $links, $settings_link );
        return $links;
    }

    public function add_window_card_to_property_actions( $actions, $post_id )
    {
        $actions[] = '<a 
            href="' . admin_url('?windowcard=' . $post_id) . '"
            target="_blank"
            class="button"
            style="width:100%; margin-bottom:7px; text-align:center" 
        >' . __('Print Window Card', 'propertyhive') . '</a>';

        return $actions;
    }

    public function do_window_card()
    {
        global $post;

        if ( is_admin() && isset($_GET['windowcard']) && $_GET['windowcard'] != '' )
        {
            $current_settings = get_option( 'propertyhive_window_cards', array() );

            $post = get_post( (int)$_GET['windowcard'] );

            $property = new PH_Property( (int)$_GET['windowcard'] );

            $pdf = ( isset($current_settings['output']) && $current_settings['output'] == 'pdf' ) ? true : false;

            ob_start();
?>
<!DOCTYPE html>
<html lang="en-US">
    <head>
        <!-- Metadata -->
        <meta charset="UTF-8">
        <meta name="robots" content="noindex, nofollow">
        <!-- Title -->
        <title><?php the_title(); ?></title>

        <?php include( dirname(PH_WINDOW_CARDS_PLUGIN_FILE) . "/includes/core-css.php"); ?>

    </head>
    <body>

        <div class="outer">

            <?php
                if ( isset($current_settings['header_image_attachment_id']) )
                {
            ?>
            <div class="header">
                <img src="<?php if ($pdf) { echo get_attached_file( $current_settings['header_image_attachment_id'] ); }else{ $image = wp_get_attachment_image_src( $current_settings['header_image_attachment_id'], 'full' ); echo $image[0]; } ?>" style="max-width:100%;" alt="">
            </div>
            <?php
                }
            ?>

            <?php 
                $layout = ( isset($current_settings['layout']) ? $current_settings['layout'] : '1' );
                if ( isset( $_GET['layout'] ) && $_GET['layout'] != '' )
                {
                    $layout = $_GET['layout'];
                }

                if ( $layout == 'custom' )
                {
                    include( locate_template( 'propertyhive/window-card.php' ) );
                }
                else
                {
                    include( dirname(__FILE__) . "/includes/layouts/" . $layout . ".php" ); 
                }
            ?>

        </div>

    </body>
</html>
<?php
            $html = ob_get_clean();
            $html = trim($html); // Remove any white space before and after HTML

            if ( $pdf )
            {
                $options = array();
                if ( isset($current_settings['xvfb']) && $current_settings['xvfb'] == '1' )
                {
                    $options = array(
                        // Explicitly tell wkhtmltopdf that we're using an X environment
                        'use-xserver',

                        // Enable built in Xvfb support in the command
                        'commandOptions' => array(
                            'enableXvfb' => true,

                            // Optional: Set your path to xvfb-run. Default is just 'xvfb-run'.
                            // 'xvfbRunBinary' => '/usr/bin/xvfb-run',

                            // Optional: Set options for xfvb-run. The following defaults are used.
                            // 'xvfbRunOptions' =>  '--server-args="-screen 0, 1024x768x24"',
                        ) 
                    );
                }

                if ( isset($current_settings['orientation']) && $current_settings['orientation'] != '' )
                {
                    $options['orientation'] = $current_settings['orientation'];
                }
                if ( isset($current_settings['paper_size']) && $current_settings['paper_size'] != '' )
                {
                    $options['page-size'] = $current_settings['paper_size'];
                }
                if ( isset($current_settings['margin_top']) && $current_settings['margin_top'] != '' )
                {
                    $options['margin-top'] = $current_settings['margin_top'];
                }
                if ( isset($current_settings['margin_bottom']) && $current_settings['margin_bottom'] != '' )
                {
                    $options['margin-bottom'] = $current_settings['margin_bottom'];
                }
                if ( isset($current_settings['margin_left']) && $current_settings['margin_left'] != '' )
                {
                    $options['margin-left'] = $current_settings['margin_left'];
                }
                if ( isset($current_settings['margin_right']) && $current_settings['margin_right'] != '' )
                {
                    $options['margin-right'] = $current_settings['margin_right'];
                }

                $pdf = new Pdf( $options );

                $pdf->addPage($html);

                // ... or send to client for inline display
                if (!$pdf->send())
                {
                    echo $pdf->getError();
                }

                /*$dompdf = new Dompdf();
                $dompdf->loadHtml($html);

                $dompdf->setPaper('A4', 'portrait');

                $dompdf->set_option('isRemoteEnabled', true);
                //$dompdf->set_option('debugLayout', true);

                // Render the HTML as PDF
                $dompdf->render();

                // Output the generated PDF to Browser
                $dompdf->stream();*/
            }
            else
            {
                echo $html;
            }

            die();
        }
    }

    /**
     * Define PH Window Cards Constants
     */
    private function define_constants() 
    {
        define( 'PH_WINDOW_CARDS_PLUGIN_FILE', __FILE__ );
        define( 'PH_WINDOW_CARDS_VERSION', $this->version );
    }

    private function includes()
    {
        //include_once( dirname( __FILE__ ) . "/includes/class-ph-map-search-install.php" );
    }

    /**
     * Output error message if core Property Hive plugin isn't active
     */
    public function window_cards_error_notices() 
    {
        if (!is_plugin_active('propertyhive/propertyhive.php'))
        {
            $message = __( "The Property Hive plugin must be installed and activated before you can use the Property Hive Window Cards add-on", 'propertyhive' );
            echo"<div class=\"error\"> <p>$message</p></div>";
        }
    }

    /**
     * Add a new settings tab to the Property Hive settings tabs array.
     *
     * @param array $settings_tabs Array of Property Hive setting tabs & their labels, excluding the Subscription tab.
     * @return array $settings_tabs Array of Property Hive setting tabs & their labels, including the Subscription tab.
     */
    public function add_settings_tab( $settings_tabs ) {
        $settings_tabs[$this->id] = $this->label;
        return $settings_tabs;
    }

    /**
     * Uses the Property Hive admin fields API to output settings.
     *
     * @uses propertyhive_admin_fields()
     * @uses self::get_settings()
     */
    public function output() {

        global $current_section;
        
        propertyhive_admin_fields( self::get_window_cards_settings() );
    }

    /**
     * Get window_cards settings
     *
     * @return array Array of settings
     */
    public function get_window_cards_settings() {

        global $post;

        $preview_link = false;

        $args = array(
            'post_type' => 'property',
            'posts_per_page' => 1,
            'orderby' => 'rand',
            'meta_query' => array(
                array(
                    'key' => '_on_market',
                    'value' => 'yes',
                    'compare' => '='
                )
            )
        );
        $property_query = new WP_Query( $args );

        if ( $property_query->have_posts() )
        {
            while ( $property_query->have_posts() )
            {
                $property_query->the_post();

                $preview_link = admin_url();
                $preview_link .= ( ( strpos($preview_link, '?') === FALSE ) ? '?' : '&' ) . 'windowcard=' . $post->ID;
            }
            
        }
        wp_reset_postdata();


        $current_settings = get_option( 'propertyhive_window_cards', array() );

        $settings = array(

            array( 'title' => __( 'Window Card Settings', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'window_card_settings' )

        );

        $settings[] = array(
            'title'     => __( 'Output Type', 'propertyhive' ),
            'id'        => 'output',
            'type'      => 'radio',
            'default'   => ( isset($current_settings['output']) ? $current_settings['output'] : 'html'),
            'options'   => array(
                'html' => __( 'HTML', 'propertyhive' ),
                'pdf' => __( 'PDF', 'propertyhive' ) . ' <small><em>(requires <a href="http://wkhtmltopdf.org/" target="_blank">wkhtmltopdf</a> be installed on the server)</em></small>',
            ),
        );

        $settings[] = array(
            'title'     => __( 'Header Image', 'propertyhive' ),
            'id'        => 'header_image',
            'type'      => 'window_card_header_image',
            'default'   => ( isset($current_settings['header_image_attachment_id']) ? $current_settings['header_image_attachment_id'] : ''),
            'desc_tip'  =>  __( 'Recommended width: 900px', 'propertyhive' ),
        );

        $settings[] = array(
            'title'     => __( 'Layout', 'propertyhive' ),
            'id'        => 'layout',
            'type'      => 'radio',
            'default'   => ( isset($current_settings['layout']) ? $current_settings['layout'] : '1'),
            'options'   => array(
                '1' => __( 'Layout One', 'propertyhive' ) . ( ( $preview_link !== FALSE ) ? ' (<a href="' . $preview_link . '&layout=1" target="_blank">' . __( 'Preview', 'propertyhive' ) . '</a>)' : '' ),
                //'2' => __( 'Layout Two', 'propertyhive' ) . ( ( $preview_link !== FALSE ) ? ' (<a href="' . $preview_link . '&layout=2" target="_blank">' . __( 'Preview', 'propertyhive' ) . '</a>)' : '' ),
                'custom' => __( 'Custom Layout', 'propertyhive' ) . ( ( $preview_link !== FALSE && locate_template('propertyhive/window-card.php') != '' ) ? ' (<a href="' . $preview_link . '&layout=custom" target="_blank">' . __( 'Preview', 'propertyhive' ) . '</a>)' : '' ) . ' <small><em>(' . __( 'Should be uploaded to', 'propertyhive' ) . ' ' . get_stylesheet_directory_uri() . '/propertyhive/window-card.php)</em></small>'
            ),
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'window_card_settings');

        $settings[] = array( 'title' => __( 'PDF Options', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'window_card_pdf_settings' );

		
        $settings[] = array(
            'title'     => __( 'Orientation', 'propertyhive' ),
            'id'        => 'orientation',
            'type'      => 'select',
            'default'   => ( ( isset($current_settings['orientation']) && $current_settings['orientation'] != '' ) ? $current_settings['orientation'] : 'portrait'),
            'options'   => array(
                'portrait' => __( 'Portrait', 'propertyhive' ),
                'landscape' => __( 'Landscape', 'propertyhive' ),
            ),
        );

        $paper_sizes = array(
            'A4' => __( 'A4', 'propertyhive' ),
            'A3' => __( 'A3', 'propertyhive' ),
        );
        $paper_sizes = apply_filters( 'propertyhive_printable_brochure_paper_sizes', $paper_sizes );

        $settings[] = array(
            'title'     => __( 'Paper Size', 'propertyhive' ),
            'id'        => 'paper_size',
            'type'      => 'select',
            'default'   => ( ( isset($current_settings['paper_size']) && $current_settings['paper_size'] != '' ) ? $current_settings['paper_size'] : 'A4'),
            'options'   => $paper_sizes,
        );

        $settings[] = array(
            'title'     => __( 'Margin Top', 'propertyhive' ),
            'id'        => 'margin_top',
            'type'      => 'number',
            'default'   => ( ( isset($current_settings['margin_top']) && $current_settings['margin_top'] != '' ) ? $current_settings['margin_top'] : '10'),
        );

        $settings[] = array(
            'title'     => __( 'Margin Bottom', 'propertyhive' ),
            'id'        => 'margin_bottom',
            'type'      => 'number',
            'default'   => ( ( isset($current_settings['margin_bottom']) && $current_settings['margin_bottom'] != '' ) ? $current_settings['margin_bottom'] : '10'),
        );

        $settings[] = array(
            'title'     => __( 'Margin Left', 'propertyhive' ),
            'id'        => 'margin_left',
            'type'      => 'number',
            'default'   => ( ( isset($current_settings['margin_left']) && $current_settings['margin_left'] != '' ) ? $current_settings['margin_left'] : '10'),
        );

        $settings[] = array(
            'title'     => __( 'Margin Right', 'propertyhive' ),
            'id'        => 'margin_right',
            'type'      => 'number',
            'default'   => ( ( isset($current_settings['margin_right']) && $current_settings['margin_right'] != '' ) ? $current_settings['margin_right'] : '10'),
        );
		
        $settings[] = array(
            'title'     => __( 'Use X Environment?', 'propertyhive' ),
            'id'        => 'xvfb',
            'type'      => 'checkbox',
            'default'   => ( ( isset($current_settings['xvfb']) && $current_settings['xvfb'] == '1' ) ? 'yes' : ''),
            'desc_tip'  => true,
            'desc'      => "When 'Output Type' is set to 'PDF' the wkhtmltopdf package is used. Sometimes this requires an X-environment. If you get the error 'Cannot connect to X server' then try ticking this box. Note that this requires the xvfb package be installed on the server too."
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'window_card_pdf_settings');

        $settings[] = array( 'type' => 'sectionend', 'id' => 'window_card_help');

        $settings[] = array( 'title' => __( 'Printing Window Cards', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'window_card_pdf_settings' );

        $settings[] = array(
            'title'     =>  __( 'Printing Window Cards', 'propertyhive' ),
            'type'      => 'html',
            'html'      => "To print a window card, navigate to a property in Property Hive and click 'Print Window Card' from the list of available actions."
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'window_card_help');

        return $settings;
    }

    public function window_card_header_image_file_upload( $value )
    {
        ?>
            <tr valign="top" id="window_card_header_image_row">
                <th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ) ?></th>
                <td class="forminp">

                    <?php
                        if ($value['default'] != '')
                        {
                            echo '<p><img src="' . wp_get_attachment_url( $value['default'] ) . '" style="max-width:100%;" alt="Header Image"></p>';
                        }
                    ?>

                    <input type="file" name="<?php echo esc_attr( $value['id'] ); ?>" id="<?php echo esc_attr( $value['id'] ); ?>" />
                    <?php if (isset($value['desc_tip']) && $value['desc_tip'] != '') { ?>
                        <br><em><?php echo $value['desc_tip']; ?></em>
                    <?php } ?>
                </td>
            </tr>
        <?php
    }

    /**
     * Uses the Property Hive options API to save settings.
     *
     * @uses propertyhive_update_options()
     * @uses self::get_settings()
     */
    public function save() {

        $existing_propertyhive_window_cards = get_option( 'propertyhive_window_cards', array() );

        $propertyhive_window_cards = array(
            'output' => ( (isset($_POST['output'])) ? $_POST['output'] : '' ),
            'layout' => ( (isset($_POST['layout'])) ? $_POST['layout'] : '' ),
            'orientation' => ( (isset($_POST['orientation'])) ? $_POST['orientation'] : '' ),
            'paper_size' => ( (isset($_POST['paper_size'])) ? $_POST['paper_size'] : '' ),
            'margin_top' => ( (isset($_POST['margin_top'])) ? $_POST['margin_top'] : '' ),
            'margin_bottom' => ( (isset($_POST['margin_bottom'])) ? $_POST['margin_bottom'] : '' ),
            'margin_left' => ( (isset($_POST['margin_left'])) ? $_POST['margin_left'] : '' ),
            'margin_right' => ( (isset($_POST['margin_right'])) ? $_POST['margin_right'] : '' ),
            'xvfb' => ( (isset($_POST['xvfb'])) ? $_POST['xvfb'] : '' ),
        );

        $propertyhive_window_cards = array_merge( $existing_propertyhive_window_cards, $propertyhive_window_cards );

        $error = '';
        if ($_FILES['header_image']['size'] == 0)
        {
            // No file uploaded
        }
        else
        {
            // Check $_FILES['upfile']['error'] value.
            switch ($_FILES['header_image']['error']) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_NO_FILE:
                    throw new RuntimeException('No file sent.');
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $error = __( 'Header image exceeded filesize limit.', 'propertyhive' );
                default:
                    $error = __( 'Unknown error when uploading header image.', 'propertyhive' );
            }

            if ($error == '')
            {
                $attachment_id = media_handle_upload( 'header_image', 0 );

                if ( is_wp_error( $attachment_id ) ) {
                    // There was an error uploading the image.
                } else {
                    // The image was uploaded successfully!
                    $propertyhive_window_cards['header_image_attachment_id'] = $attachment_id;
                }
            }
        }

        update_option( 'propertyhive_window_cards', $propertyhive_window_cards );
    }
}

endif;

/**
 * Returns the main instance of PH_Window_Cards to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return PH_Window_Cards
 */
function PHWC() {
    return PH_Window_Cards::instance();
}

PHWC();

if( is_admin() && file_exists(  dirname( __FILE__ ) . '/propertyhive-window-cards-update.php' ) )
{
    include_once( dirname( __FILE__ ) . '/propertyhive-window-cards-update.php' );
}