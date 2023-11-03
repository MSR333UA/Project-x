<?php
/**
 * Plugin Name: Property Hive Printable Brochures Add On
 * Plugin Uri: http://wp-property-hive.com/addons/printable-brochures/
 * Description: Add On for Property Hive allowing users to create printable property brochures
 * Version: 1.0.14
 * Author: PropertyHive
 * Author URI: http://wp-property-hive.com
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once( dirname(__FILE__) . '/vendor/autoload.php' );
require_once( dirname(__FILE__) . '/includes/dompdf/autoload.inc.php' );

use mikehaertl\wkhtmlto\Pdf;
use Dompdf\Dompdf;

if ( ! class_exists( 'PH_Printable_Brochures' ) ) :

final class PH_Printable_Brochures {

    /**
     * @var string
     */
    public $version = '1.0.14';

    /**
     * @var Property Hive The single instance of the class
     */
    protected static $_instance = null;
    
    /**
     * Main Property Hive Printable Brochures Instance
     *
     * Ensures only one instance of Property Hive Printable Brochures is loaded or can be loaded.
     *
     * @static
     * @return Property Hive Printable Brochures - Main instance
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

        $this->id    = 'printablebrochures';
        $this->label = __( 'Printable Brochures', 'propertyhive' );

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        add_action( 'admin_init', array( $this, 'check_remove_header_image') );

        add_action( 'admin_notices', array( $this, 'printable_brochures_error_notices') );

        add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ), array( $this, 'plugin_add_settings_link' ) );

        add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_tab' ), 19 );
        add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'propertyhive_settings_save_' . $this->id, array( $this, 'save' ) );
        add_action( 'propertyhive_admin_field_print_header_image', array( $this, 'print_header_image_file_upload' ) );

        add_filter( 'propertyhive_single_property_actions', array( $this, 'add_printable_brochure_action' ) );

        add_filter( 'propertyhive_admin_property_actions', array( $this, 'add_preview_brochure_to_property_actions' ), 10, 2 );

        add_action( 'wp', array( $this, 'do_printable_brochure' ) );

        add_filter( 'propertyhive_elementor_tabbed_details_display_options', array( $this, 'add_print_to_elementor_tabbed_details' ) );
        add_filter( 'propertyhive_elementor_tabbed_details_show_tab', array( $this, 'show_print_elementor_tab' ), 10, 3 );
        add_action( 'propertyhive_elementor_tabbed_details_tab_contents', array( $this, 'show_print_elementor_tab_contents' ), 10, 2 );
    }

    public function add_print_to_elementor_tabbed_details( $options )
    {
        $options['print'] = __( 'Print', 'propertyhive' );
        
        return $options;
    }

    public function show_print_elementor_tab( $show, $property, $display )
    {
        if ( $display == 'print' )
        {
            $current_settings = get_option( 'propertyhive_printable_brochures', array() );

            if ( isset($current_settings['display']) )
            {
                if ( $current_settings['display'] == 'no' )
                {
                    $show = false;
                }
                elseif ( $current_settings['display'] == 'if_none' )
                {
                    if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
                    {
                        $brochure_urls = $property->brochure_urls;
                        if ( !is_array($brochure_urls) ) { $brochure_urls = array(); }

                        if ( !empty($brochure_urls) )
                        {
                            $show = false;
                        }
                    }
                    else
                    {
                        $brochure_ids = $property->get_brochure_attachment_ids();
                        if ( !empty($brochure_ids) )
                        {
                            $show = false;
                        }
                    }
                }
            }
        }

        return $show;
    }

    public function show_print_elementor_tab_contents( $property, $display )
    {
        if ( $display == 'print' )
        {
            $url = get_permalink($property->id);
            $url .= ( ( strpos($url, '?') === FALSE ) ? '?' : '&' ) . 'print=1';

            echo '<a href="' . $url . '" target="_blank" rel="nofollow">' . __( 'Print', 'propertyhive' ) . '</a>';
        }
    }

    public function check_remove_header_image()
    {
        if ( isset($_GET['removeheaderimage']) && $_GET['removeheaderimage'] == '1' )
        {
            $current_settings = get_option( 'propertyhive_printable_brochures', array() );

            $current_settings['header_image_attachment_id'] = '';

            update_option( 'propertyhive_printable_brochures', $current_settings );
        }
    }

    public function plugin_add_settings_link( $links )
    {
        $settings_link = '<a href="' . admin_url('admin.php?page=ph-settings&tab=printablebrochures') . '">' . __( 'Settings' ) . '</a>';
        array_push( $links, $settings_link );
        return $links;
    }

    public function add_preview_brochure_to_property_actions( $actions, $post_id )
    {
        $actions[] = '<a 
            href="' . get_permalink($post_id) . ( (strpos(get_permalink($post_id), '?') !== FALSE) ? '&' : '?' ) . 'print=1"
            target="_blank"
            class="button"
            style="width:100%; margin-bottom:7px; text-align:center" 
        >' . __('Preview Brochure', 'propertyhive') . '</a>';

        return $actions;
    }

    public function do_printable_brochure()
    {
        global $post;

        if ( is_single() && $post->post_type == 'property' && isset($_GET['print']) && $_GET['print'] == '1' )
        {
            $current_settings = get_option( 'propertyhive_printable_brochures', array() );

            $property = new PH_Property( $post->ID );

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

        <?php include( dirname(PH_PRINTABLE_BROCHURES_PLUGIN_FILE) . "/includes/core-css.php"); ?>

    </head>
    <body>

        <div class="outer">

            <?php
                if ( isset($current_settings['header_image_attachment_id']) && $current_settings['header_image_attachment_id'] != '' )
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
                    include( locate_template( 'propertyhive/print.php' ) );
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

            if ($pdf)
            {
                if ( isset($_GET['debug']) )
                {
                    echo $html;
                    die();
                }

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

                if ( isset($current_settings['binary']) && $current_settings['binary'] != '' )
                {
                    $options['binary'] = $current_settings['binary'];
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
                if ( isset($current_settings['ignore_warnings']) && $current_settings['ignore_warnings'] == '1' )
                {
                    $options['ignoreWarnings'] = true;
                }

                $options = apply_filters( 'propertyhive_wkhtmltopdf_options', $options );
                $options = apply_filters( 'propertyhive_printable_brochures_wkhtmltopdf_options', $options );
                $pdf = new Pdf( $options );

                $pdf->addPage($html);

                // ... or send to client for inline display
                if (!$pdf->send())
                {
                    echo $pdf->getError();
                }
            }
            elseif ( isset($current_settings['output']) && $current_settings['output'] == 'dompdf' )
            {
                if ( apply_filters('propertyhive_printable_brochures_dompdf_strip_html_whitespace', true) === true ) { $html = preg_replace('/>\s+</', "><", $html); }

                if ( isset($_GET['debug']) )
                {
                    echo $html;
                    die();
                }

                $dompdf = new Dompdf();
                $dompdf->loadHtml($html);

                $dompdf->setPaper(( isset($current_settings['paper_size']) && $current_settings['paper_size'] != '' ) ? $current_settings['paper_size'] : 'A4', ( isset($current_settings['orientation']) && $current_settings['orientation'] != '' ) ? $current_settings['orientation'] : 'portrait');

                $options = $dompdf->getOptions();
                $options->setIsRemoteEnabled(true);
                //$options->setDebugLayout(true);

                // Dompdf's suggestion to enable if you encounter rendering issues from poorly-formed HTML
                //$options->setIsHtml5ParserEnabled(true);

                $dompdf->setOptions($options);

                // Render the HTML as PDF
                $dompdf->render();

                // Output the generated PDF to Browser
                $dompdf->stream();
            }
            else
            {
                echo $html;
            }
            die();
        }
    }

    /**
     * Define PH Printable Brochures Constants
     */
    private function define_constants() 
    {
        define( 'PH_PRINTABLE_BROCHURES_PLUGIN_FILE', __FILE__ );
        define( 'PH_PRINTABLE_BROCHURES_VERSION', $this->version );
    }

    private function includes()
    {
        //include_once( dirname( __FILE__ ) . "/includes/class-ph-map-search-install.php" );
    }

    /**
     * Output error message if core Property Hive plugin isn't active
     */
    public function printable_brochures_error_notices() 
    {
        if (!is_plugin_active('propertyhive/propertyhive.php'))
        {
            $message = __( "The Property Hive plugin must be installed and activated before you can use the Property Hive Printable Brochures add-on", 'propertyhive' );
            echo"<div class=\"error\"> <p>$message</p></div>";
        }
    }

    public function add_printable_brochure_action( $actions )
    {
        global $post, $property;

        $current_settings = get_option( 'propertyhive_printable_brochures', array() );

        $display = true;
        if ( isset($current_settings['display']) )
        {
            if ( $current_settings['display'] == 'no' )
            {
                $display = false;
            }
            elseif ( $current_settings['display'] == 'if_none' )
            {
                if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
                {
                    $brochure_urls = $property->brochure_urls;
                    if ( !is_array($brochure_urls) ) { $brochure_urls = array(); }

                    if ( !empty($brochure_urls) )
                    {
                        $display = false;
                    }
                }
                else
                {
                    $brochure_ids = $property->get_brochure_attachment_ids();
                    if ( !empty($brochure_ids) )
                    {
                        $display = false;
                    }
                }
            }
        }

        if ( $display )
        {
            $url = get_permalink();
            $url .= ( ( strpos($url, '?') === FALSE ) ? '?' : '&' ) . 'print=1';

            $actions[] = array(
                'href' =>  $url,
                'label' => (isset($current_settings['link_text']) && $current_settings['link_text'] != '') ? $current_settings['link_text'] : __( 'Print Details', 'propertyhive' ),
                'class' => 'action-printable-brochure',
                'attributes' => array(
                    'target' => '_blank',
                    'rel' => 'nofollow'
                )
            );
        }

        return $actions;
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
        
        propertyhive_admin_fields( self::get_printable_brochures_settings() );
    }

    /**
     * Get printable_brochures settings
     *
     * @return array Array of settings
     */
    public function get_printable_brochures_settings() {

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

                $preview_link = get_permalink($post->ID);
                $preview_link .= ( ( strpos($preview_link, '?') === FALSE ) ? '?' : '&' ) . 'print=1';
            }
            
        }
        wp_reset_postdata();


        $current_settings = get_option( 'propertyhive_printable_brochures', array() );

        $settings = array(

            array( 'title' => __( 'Printable Brochures Settings', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'printable_brochures_settings' )

        );

        $settings[] = array(
            'title'     => __( 'Display On Property Details', 'propertyhive' ),
            'id'        => 'display',
            'type'      => 'radio',
            'default'   => ( isset($current_settings['display']) ? $current_settings['display'] : ''),
            'options'   => array(
                '' => __( 'Always', 'propertyhive' ),
                'if_none' => __( 'Only if no brochures uploaded to property record', 'propertyhive' ),
                'no' => __( 'Never', 'propertyhive' ),
            ),
        );

        $settings[] = array(
            'title'     => __( 'Output Type', 'propertyhive' ),
            'id'        => 'output',
            'type'      => 'radio',
            'default'   => ( isset($current_settings['output']) ? $current_settings['output'] : 'html'),
            'options'   => array(
                'html' => __( 'HTML', 'propertyhive' ),
                'pdf' => __( 'PDF', 'propertyhive' ) . ' (Using wkhtmltopdf) <small><em>- requires <a href="http://wkhtmltopdf.org/" target="_blank">wkhtmltopdf</a> be installed on the server</em></small>',
                'dompdf' => __( 'PDF', 'propertyhive' ) . ' (Using dompdf) <small><em> - does not require anything to be separately installed on the server</em></small>',
            ),
        );

        $settings[] = array(
            'title'     => __( 'Header Image', 'propertyhive' ),
            'id'        => 'header_image',
            'type'      => 'print_header_image',
            'default'   => ( isset($current_settings['header_image_attachment_id']) ? $current_settings['header_image_attachment_id'] : ''),
            'desc_tip'  =>  __( 'Recommended width: 900px', 'propertyhive' ),
        );

        $settings[] = array(
            'title'     => __( 'Link Text', 'propertyhive' ),
            'id'        => 'link_text',
            'type'      => 'text',
            'default'   => ( isset($current_settings['link_text']) ? $current_settings['link_text'] : 'Print Details'),
        );

        $settings[] = array(
            'title'     => __( 'Layout', 'propertyhive' ),
            'id'        => 'layout',
            'type'      => 'radio',
            'default'   => ( isset($current_settings['layout']) ? $current_settings['layout'] : '1'),
            'options'   => array(
                '1' => __( 'Layout One', 'propertyhive' ) . ( ( $preview_link !== FALSE ) ? ' (<a href="' . $preview_link . '&layout=1" target="_blank">' . __( 'Preview', 'propertyhive' ) . '</a>)' : '' ),
                '2' => __( 'Layout Two', 'propertyhive' ) . ( ( $preview_link !== FALSE ) ? ' (<a href="' . $preview_link . '&layout=2" target="_blank">' . __( 'Preview', 'propertyhive' ) . '</a>)' : '' ),
                'custom' => __( 'Custom Layout', 'propertyhive' ) . ( ( $preview_link !== FALSE && locate_template('propertyhive/print.php') != '' ) ? ' (<a href="' . $preview_link . '&layout=custom" target="_blank">' . __( 'Preview', 'propertyhive' ) . '</a>)' : '' ) . ' <small><em>(' . __( 'Should be uploaded to', 'propertyhive' ) . ' ' . get_stylesheet_directory_uri() . '/propertyhive/print.php)</em></small>'
            ),
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'printable_brochures_settings');

        $settings[] = array( 'title' => __( 'PDF Options', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'pdf_settings' );

        $settings[] = array(
            'title'     => __( 'Orientation', 'propertyhive' ),
            'id'        => 'orientation',
            'type'      => 'select',
            'default'   => ( ( isset($current_settings['orientation']) && $current_settings['orientation'] != '' ) ? $current_settings['orientation'] : 'Portrait'),
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
            'title'     => __( 'wkhtmltopdf Binary Path', 'propertyhive' ),
            'id'        => 'binary',
            'type'      => 'text',
            'default'   => ( ( isset($current_settings['binary']) && $current_settings['binary'] != '' ) ? $current_settings['binary'] : 'wkhtmltopdf'),
            'desc_tip'  => false,
            'desc'      => 'If you get an error about command not being found it\'s likely that this path is wrong. Your hosting company will be able to advise on the correct path.' 
        );

        $settings[] = array(
            'title'     => __( 'Use X Environment?', 'propertyhive' ),
            'id'        => 'xvfb',
            'type'      => 'checkbox',
            'default'   => ( ( isset($current_settings['xvfb']) && $current_settings['xvfb'] == '1' ) ? 'yes' : ''),
            'desc_tip'  => true,
            'desc'      => "When 'Output Type' is set to 'PDF' the wkhtmltopdf package is used. Sometimes this requires an X-environment. If you get the error 'Cannot connect to X server' then try ticking this box. Note that this requires the xvfb package be installed on the server too."
        );

        $settings[] = array(
            'title'     => __( 'Ignore Warnings', 'propertyhive' ),
            'id'        => 'ignore_warnings',
            'type'      => 'checkbox',
            'default'   => ( ( isset($current_settings['ignore_warnings']) && $current_settings['ignore_warnings'] == '1' ) ? 'yes' : ''),
            'desc_tip'  => true,
            'desc'      => "Sometimes an error such as 'Failed Without Error Message' will prevent the PDF from being generated with no obviious cause. Ignoring warnings and ticking this box can sometimes solve this."
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'pdf_settings');

        return $settings;
    }

    public function print_header_image_file_upload( $value )
    {
        ?>
            <tr valign="top" id="print_header_image_row">
                <th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ) ?></th>
                <td class="forminp">

                    <?php
                        if ($value['default'] != '')
                        {
                            echo '<p><img src="' . wp_get_attachment_url( $value['default'] ) . '" style="max-width:100%;" alt="Header Image"></p>
                            <p><a href="' . admin_url('admin.php?page=ph-settings&tab=printablebrochures&removeheaderimage=1') . '">Remove Image</a></p>';
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

        $existing_propertyhive_printable_brochures = get_option( 'propertyhive_printable_brochures', array() );

        $propertyhive_printable_brochures = array(
            'display' => ( (isset($_POST['display'])) ? $_POST['display'] : '' ),
            'output' => ( (isset($_POST['output'])) ? $_POST['output'] : '' ),
            'link_text' => ( (isset($_POST['link_text'])) ? $_POST['link_text'] : '' ),
            'layout' => ( (isset($_POST['layout'])) ? $_POST['layout'] : '' ),
            'orientation' => ( (isset($_POST['orientation'])) ? $_POST['orientation'] : '' ),
            'paper_size' => ( (isset($_POST['paper_size'])) ? $_POST['paper_size'] : '' ),
            'margin_top' => ( (isset($_POST['margin_top'])) ? $_POST['margin_top'] : '' ),
            'margin_bottom' => ( (isset($_POST['margin_bottom'])) ? $_POST['margin_bottom'] : '' ),
            'margin_left' => ( (isset($_POST['margin_left'])) ? $_POST['margin_left'] : '' ),
            'margin_right' => ( (isset($_POST['margin_right'])) ? $_POST['margin_right'] : '' ),
            'binary' => ( (isset($_POST['binary'])) ? $_POST['binary'] : '' ),
            'xvfb' => ( (isset($_POST['xvfb'])) ? $_POST['xvfb'] : '' ),
            'ignore_warnings' => ( (isset($_POST['ignore_warnings'])) ? $_POST['ignore_warnings'] : '' ),
        );

        $propertyhive_printable_brochures = array_merge( $existing_propertyhive_printable_brochures, $propertyhive_printable_brochures );

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
                    $propertyhive_printable_brochures['header_image_attachment_id'] = $attachment_id;
                }
            }
        }

        update_option( 'propertyhive_printable_brochures', $propertyhive_printable_brochures );
    }
}

endif;

/**
 * Returns the main instance of PH_Printable_Brochures to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return PH_Printable_Brochures
 */
function PHPB() {
    return PH_Printable_Brochures::instance();
}

PHPB();

if( is_admin() && file_exists(  dirname( __FILE__ ) . '/propertyhive-printable-brochures-update.php' ) )
{
    include_once( dirname( __FILE__ ) . '/propertyhive-printable-brochures-update.php' );
}