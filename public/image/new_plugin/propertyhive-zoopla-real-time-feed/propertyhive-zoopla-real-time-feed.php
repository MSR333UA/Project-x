<?php
/**
 * Plugin Name: Property Hive Zoopla Real-Time Feed Add On
 * Plugin Uri: http://wp-property-hive.com/addons/zoopla-real-time-feed/
 * Description: Add On for Property Hive allowing real-time feeds to Zoopla
 * Version: 1.0.22
 * Author: PropertyHive
 * Author URI: http://wp-property-hive.com
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Zooplarealtimefeed' ) ) :

final class PH_Zooplarealtimefeed {

    /**
     * @var string
     */
    public $version = '1.0.22';

    /**
     * @var Property Hive The single instance of the class
     */
    protected static $_instance = null;
    
    /**
     * Main Property Hive Real-Time Feed Instance
     *
     * Ensures only one instance of Property Hive Real-Time Feed is loaded or can be loaded.
     *
     * @static
     * @return Property Hive Real-Time Feed - Main instance
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

    	$this->id    = 'zooplarealtimefeed';
        $this->label = __( 'Zoopla Real-Time Feed', 'propertyhive' );

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes(); 

        add_action( 'admin_init', array( $this, 'run_custom_zooplarealtimefeed_cron') );

        add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ), array( $this, 'plugin_add_settings_link' ) );

        add_action( 'admin_notices', array( $this, 'realtimefeed_error_notices') );

        add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_tab' ), 19 );
        add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'propertyhive_settings_save_' . $this->id, array( $this, 'save' ) );

        add_action( 'propertyhive_admin_field_zoopla_realtime_portals', array( $this, 'portals_setting' ) );
        add_action( 'propertyhive_admin_field_zoopla_realtime_logs', array( $this, 'logs_setting' ) );
        add_action( 'propertyhive_admin_field_zoopla_certificate_file', array( $this, 'certificate_file_upload' ) );
        add_action( 'propertyhive_admin_field_zoopla_private_key_file', array( $this, 'private_key_file_upload' ) );
        add_action( 'propertyhive_property_marketing_fields', array( $this, 'add_zooplarealtimefeed_checkboxes' ) );
        add_action( 'propertyhive_save_property_marketing', array( $this, 'save_zooplarealtimefeed_checkboxes' ), 10, 1 );

        add_action( 'manage_property_posts_custom_column', array( $this, 'custom_property_columns' ), 3 );

        add_filter( 'propertyhive_property_filter_marketing_options', array( $this, 'rtdf_property_filter_marketing_options' ) );
        add_filter( 'propertyhive_property_filter_query', array( $this, 'rtdf_property_filter_query' ), 10, 2 );

        add_action( 'propertyhive_property_bulk_edit_end', array( $this, 'rtdf_bulk_edit_options' ) );
        add_action( 'propertyhive_property_bulk_edit_save', array( $this, 'rtdf_bulk_edit_save' ), 10, 1 );

        add_action( 'phzooplarealtimefeedcronhook', array( $this, 'real_time_feed_reconcile_properties' ) );
        add_action( 'phzooplarealtimefeedcronhook', array( $this, 'send_properties_post_exclusivity' ) );

        add_filter( 'propertyhive_enquiry_sources', array( $this, 'add_enquiry_sources' ) );
        add_action( 'phzooplarealtimefeedcronhook', array( $this, 'process_ftp_enquiries' ) );

        add_action( 'save_post', array( $this, 'send_realtime_feed_request' ), 99 );

        add_action( 'update_post_meta', array( $this, 'check_on_market_update' ), 10, 4 );
    }

    // If a property is taken off the market remove the last sent data so the 'only update if different' setting works should it be put back on the market again
    public function check_on_market_update( $meta_id, $object_id, $meta_key, $meta_value )
    {
        if ( get_post_type($object_id) == 'property' && $meta_key == '_on_market' )
        {
            $original_value = get_post_meta( $object_id, $meta_key, TRUE );

            if ( $original_value != $meta_value )
            {
                if ($meta_value == '')
                {
                    $current_options = get_option( 'propertyhive_zooplarealtimefeed' );
                    $portals = array();

                    if ($current_options !== FALSE)
                    {
                        if (isset($current_options['portals']))
                        {
                            $portals = $current_options['portals'];
                        }
                    }

                    if ( !empty($portals) )
                    {
                        foreach ( $portals as $portal_id => $portal )
                        {
                            delete_post_meta( $object_id, '_zoopla_realtime_sha1_' . $portal_id . '_sales' );
                            delete_post_meta( $object_id, '_zoopla_realtime_sha1_' . $portal_id . '_lettings' );
                        }
                    }
                }
            }
        }
    }

    public function send_properties_post_exclusivity()
    {
        $current_options = get_option( 'propertyhive_zooplarealtimefeed' );
        $portals = array();

        if ($current_options !== FALSE)
        {
            if (isset($current_options['portals']))
            {
                $portals = $current_options['portals'];
            }
        }

        if ( !empty($portals) )
        {
            foreach ( $portals as $portal_id => $portal )
            {
                if ( $portal['mode'] != 'live' ) { continue; } // Only continue past this point if the portal is live

                // Get all properties active on this portal
                $portal['portal_id'] = $portal_id; // Add to array so easier to pass around

                // Get property
                $args = array(
                    'post_type' => 'property',
                    'nopaging' => true,
                    'post_status' => 'publish',
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key' => '_zoopla_realtime_portal_' . $portal_id,
                            'value' => 'yes'
                        ),
                        array(
                            'key' => '_on_market',
                            'value' => 'yes'
                        )
                    )
                );
                $property_query = new WP_Query( $args );

                if ($property_query->have_posts())
                {
                    while ($property_query->have_posts())
                    {
                        $property_query->the_post();

                        // Check this property doesn't have exclusivity to another portal
                        $exclusivity_portal_id = get_post_meta( get_the_ID(), '_exclusivity_portal_id', TRUE );
                        if ( $exclusivity_portal_id != '' && $exclusivity_portal_id != $portal_id  )
                        {
                            // This property does have exclusivity to another portal. Only send if exclusivity expiry has passed
                            $exclusivity_expires = get_post_meta( get_the_ID(), '_exclusivity_expires', TRUE );
                            if ( 
                                $exclusivity_expires == '' || 
                                ( 
                                    $exclusivity_expires != '' && 
                                    time() > strtotime($exclusivity_expires) &&
                                    time() < ( strtotime($exclusivity_expires) + 200000 ) // ideally we'd keep a track of which portals need sending and then mark once done, but this will do for now
                                ) 
                            )
                            {
                                global $post;  

                                if ( empty( $post ) )
                                    $post = get_post(get_the_ID());

                                $property = new PH_Property( get_the_ID() );

                                $success = $this->create_send_property_request( $portal, $post, $property );
                            }
                        }
                    }
                }
            }
        }
    }

    public function add_enquiry_sources( $sources )
    {
        $current_options = get_option( 'propertyhive_zooplarealtimefeed' );
        $portals = array();

        if ($current_options !== FALSE)
        {
            if (isset($current_options['portals']))
            {
                $portals = $current_options['portals'];
            }
        }

        if ( !empty($portals) )
        {
            foreach ( $portals as $portal_id => $portal )
            {
                if ( $portal['mode'] != 'live' ) { continue; } // Only continue past this point if the portal is live

                $sources['zoopla_rtdf_portal_' . $portal_id] = $portal['name'];
            }
        }

        return $sources;
    }

    public function process_ftp_enquiries()
    {
        $current_options = get_option( 'propertyhive_zooplarealtimefeed' );
        $portals = array();

        if ($current_options !== FALSE)
        {
            if (isset($current_options['portals']))
            {
                $portals = $current_options['portals'];
            }
        }

        if ( !empty($portals) ) {
            foreach ( $portals as $portal_id => $portal ) {

                $host     = $portal['enquiries_ftp_host'];
                $username = $portal['enquiries_ftp_username'];
                $password = $portal['enquiries_ftp_password'];

                // Enquiry FTP Area credentials have been populated
                if ( $host !== '' && $username !== '' && $password !== '' ) {

                    unset($errorMessage);
                    // Check we can connect successfully to FTP area
                    if ( function_exists("ssh2_connect") ) {
                        if ( $connection = ssh2_connect($host, 22) ) {
                            if ( ssh2_auth_password($connection, $username, $password) ) {
                                if ( $stream = ssh2_sftp($connection) ) {
                                    if ( !$dir = opendir("ssh2.sftp://{$stream}/./") ) {
                                        $errorMessage = 'Unable to open Zoopla enquiry root directory';
                                    }
                                } else {
                                    $errorMessage = 'Unable to create a stream when getting Zoopla enquiries';
                                }
                            } else {
                                $errorMessage = 'Unable to authenticate Zoopla enquiry FTP connection';
                            }
                        } else {
                            $errorMessage = 'Unable to connect to Zoopla enquiry host';
                        }
                    } else {
                        $errorMessage = 'SSH2 package not installed on WordPress when trying to pull Zoopla enquiries';
                    }

                    if ( !isset($errorMessage) ) {

                        $enquiryFiles = array();
                        while ( ($file = readdir($dir))  !== false) {
                            if ( !in_array($file, array('.', '..')) ) {
                                array_push($enquiryFiles, $file);
                            }
                        }

                        foreach ( $enquiryFiles as $enquiryFileUrl ) {
                            $fileContents = file_get_contents('ssh2.sftp://' . $stream . '/' . $enquiryFileUrl);
                            $enquiriesXml = simplexml_load_string($fileContents);

                            foreach( $enquiriesXml->ZooplaLead as $enquiry ) {

                                // Check enquiry hasn't been inserted already
                                $args = array(
                                    'post_type' => 'enquiry',
                                    'nopaging' => true,
                                    'meta_query' => array(
                                        array(
                                            'key' => '_imported_ref_' . $portal_id,
                                            'value' => (string)$enquiry['publicLeadId'],
                                        ),
                                    )
                                );
                                $enquiries_query = new WP_Query( $args );
                                wp_reset_postdata();

                                if ( !$enquiries_query->have_posts() ) {
                                        $enquirerName = implode(' ', array_filter(array($enquiry->FirstName, $enquiry->LastName)));

                                        $title = __( 'Property Enquiry', 'propertyhive' ) . ': ' . get_the_title( (int)$enquiry->SourceListingId );
                                        $title .= ' ' . __( 'from', 'propertyhive' ) . ' ' . sanitize_text_field( $enquirerName );

                                        // insert enquiry
                                        $enquiry_post = array(
                                            'post_title' => $title,
                                            'post_content' => '',
                                            'post_type' => 'enquiry',
                                            'post_status' => 'publish',
                                            'comment_status' => 'closed',
                                            'ping_status' => 'closed',
                                            'post_date' => date("Y-m-d H:i:s", strtotime($enquiry->LeadCreationDate)),
                                        );
                                        // Insert the post into the database
                                        $enquiry_post_id = wp_insert_post( $enquiry_post );

                                        add_post_meta( $enquiry_post_id, '_imported_ref_' . $portal_id, (string)$enquiry['publicLeadId'] );
                                        add_post_meta( $enquiry_post_id, '_status', 'open' );
                                        add_post_meta( $enquiry_post_id, '_source', 'zoopla_rtdf_portal_' . $portal_id );
                                        add_post_meta( $enquiry_post_id, '_negotiator_id', '' );
                                        add_post_meta( $enquiry_post_id, '_office_id', (int)get_post_meta( (int)$enquiry->SourceListingId, '_office_id', TRUE ) );
                                        add_post_meta( $enquiry_post_id, '_property_id', (int)$enquiry->SourceListingId );

                                        $enquiryDetails = $enquiry->children();
                                        foreach( $enquiryDetails as $nodeName => $value ) {
                                            if ( $nodeName !== 'SearchDetails' ) {
                                                add_post_meta( $enquiry_post_id, $nodeName, sanitize_textarea_field( (string)$value ) );
                                            }
                                        }

                                        if ( isset($enquiry->SearchDetails->SearchUrl) ) {
                                            add_post_meta( $enquiry_post_id, 'SearchUrl', sanitize_textarea_field( (string)$enquiry->SearchDetails->SearchUrl ) );
                                        }

                                        do_action( 'propertyhive_zoopla_realtime_feed_enquiry_imported', $enquiry_post_id, $enquiry );

                                        wp_reset_postdata();
                                }
                            }
                            unlink('ssh2.sftp://' . $stream . '/' . $enquiryFileUrl);
                        }
                    } else {
                        $this->log_error($portal_id, 2, $errorMessage);
                    }
                }
            }
        }
    }

    public function plugin_add_settings_link( $links )
    {
        $settings_link = '<a href="' . admin_url('admin.php?page=ph-settings&tab=zooplarealtimefeed') . '">' . __( 'Settings' ) . '</a>';
        array_push( $links, $settings_link );

        $docs_link = '<a href="https://docs.wp-property-hive.com/add-ons/zoopla-real-time-data-feed/" target="_blank">' . __( 'Documentation' ) . '</a>';
        array_push( $links, $docs_link );

        return $links;
    }

    public function rtdf_property_filter_query( $vars, $typenow )
    {
        if ( 'property' === $typenow ) 
        {
            if ( ! empty( $_GET['_marketing'] ) && substr($_GET['_marketing'], 0, 23) == 'zoopla_realtime_portal_' ) 
            {
                $portal_id = sanitize_text_field( str_replace("zoopla_realtime_portal_", "", $_GET['_marketing']) );

                $vars['meta_query'][] = array(
                    'key' => '_on_market',
                    'value' => 'yes'
                );

                $vars['meta_query'][] = array(
                    'key' => '_zoopla_realtime_portal_' . $portal_id,
                    'value' => 'yes'
                );
            }
        }

        return $vars;
    }

    public function rtdf_property_filter_marketing_options( $options )
    {
        $current_options = get_option( 'propertyhive_zooplarealtimefeed' );
        $portals = array();

        if ($current_options !== FALSE)
        {
            if (isset($current_options['portals']))
            {
                $portals = $current_options['portals'];
            }
        }

        if ( !empty($portals) )
        {
            foreach ( $portals as $portal_id => $portal )
            {
                if ( $portal['mode'] == 'test' || $portal['mode'] == 'live' )
                {
                    $options['zoopla_realtime_portal_' . $portal_id] = 'Active On ' . $portal['name'];
                }
            }
        }

        return $options;
    }

    public function custom_property_columns( $column )
    {
        global $post, $propertyhive, $the_property;

        if ( empty( $the_property ) || $the_property->ID != $post->ID ) 
        {
            $the_property = new PH_Property( $post->ID );
        }

        switch ( $column ) 
        {
            case 'status' :
            {
                $current_zooplarealtimefeed_options = get_option( 'propertyhive_zooplarealtimefeed' );
                $portals = array();

                if ($current_zooplarealtimefeed_options !== FALSE)
                {
                    if (isset($current_zooplarealtimefeed_options['portals']))
                    {
                        $portals = $current_zooplarealtimefeed_options['portals'];
                    }
                }

                if ( !empty($portals) )
                {
                    foreach ( $portals as $portal_id => $portal )
                    {
                        if ( ( $portal['mode'] == 'test' || $portal['mode'] == 'live' ) && $the_property->_on_market == 'yes' && $the_property->{'_zoopla_realtime_portal_' . $portal_id} == 'yes' )
                        {
                            echo '<br>' . $portal['name'];
                        }
                    }
                }

                break;
            }
        }
    }

    public function rtdf_bulk_edit_save( $property )
    {
        $portals = array();
        $current_realtime_feed_options = get_option( 'propertyhive_zooplarealtimefeed' );
            
        if ($current_realtime_feed_options !== FALSE)
        {
            if (isset($current_realtime_feed_options['portals']))
            {
                $portals = $current_realtime_feed_options['portals'];
            }
        }

        if ( !empty($portals) )
        {
            foreach ( $portals as $portal_id => $portal )
            {
                if ($portal['mode'] == 'test' || $portal['mode'] == 'live')
                {
                    if ( isset($_REQUEST['_zoopla_realtime_portal_' . $portal_id]) && $_REQUEST['_zoopla_realtime_portal_' . $portal_id] == 'yes' )
                    {
                        update_post_meta( $property->id, '_zoopla_realtime_portal_' . $portal_id, 'yes' );
                    }
                    elseif ( isset($_REQUEST['_zoopla_realtime_portal_' . $portal_id]) && $_REQUEST['_zoopla_realtime_portal_' . $portal_id] == 'no' )
                    {
                        update_post_meta( $property->id, '_zoopla_realtime_portal_' . $portal_id, '' );
                    }
                }
            }
        }
    }

    public function rtdf_bulk_edit_options()
    {
        $portals = array();
        $current_realtime_feed_options = get_option( 'propertyhive_zooplarealtimefeed' );
            
        if ($current_realtime_feed_options !== FALSE)
        {
            if (isset($current_realtime_feed_options['portals']))
            {
                $portals = $current_realtime_feed_options['portals'];
            }
        }

        if ( !empty($portals) )
        {
            foreach ( $portals as $portal_id => $portal )
            {
                if ($portal['mode'] == 'test' || $portal['mode'] == 'live')
                {
?>
        <div class="inline-edit-group">
            <label class="alignleft">
                <span class="title"><?php _e( 'Active On ' . $portal['name'], 'propertyhive' ); ?></span>
                <span class="input-text-wrap">
                    <select class="zoopla_realtime_portal_<?php echo $portal_id; ?>" name="_zoopla_realtime_portal_<?php echo $portal_id; ?>">
                    <?php
                        $options = array(
                            ''  => __( '— No Change —', 'propertyhive' ),
                            'yes' => __( 'Yes', 'propertyhive' ),
                            'no' => __( 'No', 'propertyhive' ),
                        );
                        foreach ($options as $key => $value) {
                            echo '<option value="' . esc_attr( $key ) . '">' . $value . '</option>';
                        }
                    ?>
                    </select>
                </span>
            </label>
        </div>
<?php
                }
            }
        }
    }

    public function run_custom_zooplarealtimefeed_cron() 
    {
        if( isset($_GET['custom_zooplarealtimefeed_cron']) && $_GET['custom_zooplarealtimefeed_cron'] == 'phzooplarealtimefeedcronhook' )
        {
            do_action($_GET['custom_zooplarealtimefeed_cron']);
        }

        if ( isset($_GET['action']) && $_GET['action'] == 'zooplapushall' && isset($_GET['id']) && $_GET['id'] != '' )
        {
            $portals = array();
            $current_realtime_feed_options = get_option( 'propertyhive_zooplarealtimefeed' );
                
            if ($current_realtime_feed_options !== FALSE)
            {
                if (isset($current_realtime_feed_options['portals']))
                {
                    $portals = $current_realtime_feed_options['portals'];
                }
            }
            if (!empty($portals))
            {
                foreach ($portals as $portal_id => $portal)
                {
                    if ( $portal_id != $_GET['id'] ) { continue; } // Only continue past this point if the passed id

                    if ( $portal['mode'] != 'live' ) { continue; } // Only continue past this point if the portal is live

                    $portal['portal_id'] = $portal_id; // Add to array so easier to pass around

                    // Get all properties
                    $args = array(
                        'post_type' => 'property',
                        'nopaging' => true,
                        'meta_query' => array(
                            'relation' => 'AND',
                            array(
                                'key' => '_zoopla_realtime_portal_' . $portal_id,
                                'value' => 'yes'
                            ),
                            array(
                                'key' => '_on_market',
                                'value' => 'yes'
                            )
                        )
                    );
                    $property_query = new WP_Query( apply_filters( 'ph_zoopla_rtdf_push_all_query_args', $args ) );

                    if ($property_query->have_posts())
                    {
                        while ($property_query->have_posts())
                        {
                            $property_query->the_post();

                            $property = new PH_Property( get_the_ID() );

                            // Work out if the office has changed since the last time we sent this property and do a remove request first if so
                            $previous_sent_office_id = get_post_meta( get_the_ID(), '_zoopla_realtime_previous_office_id', TRUE );
                            $current_office_id = $property->_office_id;

                            $ok_to_send = true;
                            if ( $previous_sent_office_id != '' && $previous_sent_office_id != $current_office_id )
                            {
                                // Office has changed. Need to send a remove request first
                                $success = $this->create_remove_property_request( $portal, get_post(get_the_ID()), $property, $previous_sent_office_id );

                                if ($success === FALSE)
                                {
                                    $ok_to_send = false;
                                }

                            }

                            if ( $ok_to_send )
                            {
                                $success = $this->create_send_property_request( $portal, get_post(get_the_ID()), $property );

                                if ($success === FALSE)
                                {
                                }
                                else
                                {
                                    if ( $previous_sent_office_id != $current_office_id )
                                    {
                                        update_post_meta( get_the_ID(), '_zoopla_realtime_previous_office_id', $current_office_id );
                                    }
                                }
                            }
                        }
                    }

                    wp_reset_postdata();

                } // end foreach portals
            }
        }
    }

    private function includes()
    {
        include_once( 'includes/class-ph-zooplarealtimefeed-install.php' );
    }

    /**
     * Define PH_Realtimefeed Constants
     */
    private function define_constants() 
    {
        define( 'PH_ZOOPLAREALTIMEFEED_PLUGIN_FILE', __FILE__ );
        define( 'PH_ZOOPLAREALTIMEFEED_VERSION', $this->version );
    }

    /**
     * Output error message if core Property Hive plugin isn't active
     */
    public function realtimefeed_error_notices() 
    {
        if (!is_plugin_active('propertyhive/propertyhive.php'))
        {
            $message = "The Property Hive plugin must be installed and activated before you can use the Property Hive Zoopla Real-Time Feed add-on";
            echo"<div class=\"error\"> <p>$message</p></div>";
        }
        else
        {
            if (!function_exists('curl_version'))
            {
                $error = 'You must enable cURL before real-time feed requests can be sent';
            }

            $error = '';    
            $uploads_dir = wp_upload_dir();
            if( $uploads_dir['error'] === FALSE )
            {
                $uploads_dir = $uploads_dir['basedir'] . '/zoopla_realtime/';
                
                if ( ! @file_exists($uploads_dir) )
                {
                    if ( ! @mkdir($uploads_dir) )
                    {
                        $error = 'Unable to create subdirectory in uploads folder for use by Property Hive Zoopla Real-Time feed plugin. Please ensure the <a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank" title="WordPress Codex - Changing File Permissions">correct permissions</a> are set.';
                    }
                }
                else
                {
                    if ( ! @is_writeable($uploads_dir) )
                    {
                        $error = 'The uploads folder is not currently writeable and will need to be before the feed can be ran. Please ensure the <a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank" title="WordPress Codex - Changing File Permissions">correct permissions</a> are set.';
                    }
                }
            }
            else
            {
                $error = 'An error occured whilst trying to create the uploads folder. Please ensure the <a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank" title="WordPress Codex - Changing File Permissions">correct permissions</a> are set. '.$uploads_dir['error'];
            }

            $screen = get_current_screen();
            
            if ( $screen->id == 'property-hive_page_ph-settings' && isset( $_GET['tab'] ) && $_GET['tab'] == 'zooplarealtimefeed' && !isset($_GET['section']) ) 
            {
                // Check has at least one live portal
                $current_zooplarealtimefeed_options = get_option( 'propertyhive_zooplarealtimefeed' );

                if ( $current_zooplarealtimefeed_options !== FALSE )
                { 
                    if ( isset($current_zooplarealtimefeed_options['portals']) )
                    {
                        $portals = $current_zooplarealtimefeed_options['portals'];

                        foreach ( $portals as $i => $portal )
                        {
                            if ( $portal['mode'] == 'live' )
                            {
                                $current_mappings = ( ( isset($current_zooplarealtimefeed_options['mappings']) ) ? $current_zooplarealtimefeed_options['mappings'] : array() );

                                if ( $this->has_non_overseas_portal() )
                                {
                                    $availability_mapping = ( isset($current_mappings['availability']) ? $current_mappings['availability'] : array() );

                                    if ( count($availability_mapping) == 0 )
                                    {
                                        $notice = __( 'No mapping has been specified for availabilities. This will result in properties getting rejected.', 'propertyhive' ) . '<br><br>';
                                        $notice .= '<a href="' . admin_url( 'admin.php?page=ph-settings&tab=zooplarealtimefeed&section=mapping_availability' ) . '" class="button-primary">Map Availabilities</a>';

                                        echo '<div class="notice notice-info"><p><strong>' . $notice . '</strong></p></div>';
                                    }
                                }

                                if ( $this->has_overseas_portal() )
                                {
                                    $overseas_availability_mapping = ( isset($current_mappings['overseas-availability']) ? $current_mappings['overseas-availability'] : array() );

                                    if ( count($overseas_availability_mapping) == 0 )
                                    {
                                        $notice = __( 'No mapping has been specified for overseas availabilities. This will result in properties getting rejected.', 'propertyhive' ) . '<br><br>';
                                        $notice .= '<a href="' . admin_url( 'admin.php?page=ph-settings&tab=zooplarealtimefeed&section=mapping_overseas-availability' ) . '" class="button-primary">Map Overseas Availabilities</a>';

                                        echo '<div class="notice notice-info"><p><strong>' . $notice . '</strong></p></div>';
                                    }
                                }

                                $property_type_mapping = ( isset($current_mappings['property-type']) ? $current_mappings['property-type'] : array() );

                                if ( count($property_type_mapping) == 0 )
                                {
                                    $notice = __( 'No mapping has been specified for property types. This will result in properties getting rejected.', 'propertyhive' ) . '<br><br>';
                                    $notice .= '<a href="' . admin_url( 'admin.php?page=ph-settings&tab=zooplarealtimefeed&section=mapping_property-type' ) . '" class="button-primary">Map Property Types</a>';

                                    echo '<div class="notice notice-info"><p><strong>' . $notice . '</strong></p></div>';
                                }

                                break;
                            }
                        }
                    }
                }
            }

            if ( isset( $_GET['zooplarealtime_failed'] ) && $_GET['zooplarealtime_failed'] == 1 ) {
                $error = 'Failed to send property to one or more realtime feeds. ';
                if ( isset($_GET['zooplarealtime_error']) && $_GET['zooplarealtime_error'] != '' )
                {
                    $realtime_error = base64_decode($_GET['zooplarealtime_error']);
                    if ( $realtime_error !== FALSE && $realtime_error != '' )
                    {
                        $error .= '<br>The error was as follows: <em>' . htmlentities($realtime_error) . '</em><br>';
                    }
                }
                $error .= '<a href="' . admin_url( 'admin.php?page=ph-settings&tab=zooplarealtimefeed' . ( ( isset($_GET['zooplarealtime_log_id']) && $_GET['zooplarealtime_log_id'] != '' ) ? '&log_id=' . $_GET['zooplarealtime_log_id'] : '' ) . '#logs' ) . '">View Detailed Error Log</a>';
            }

            if( $error != '' )
            {
                echo '<div class="error"><p><strong>'.$error.'</strong></p></div>';
            }

            /*if ( isset($_GET['action']) && $_GET['action'] == 'zooplapushall' && isset($_GET['id']) && $_GET['id'] != '' )
            {
                $message = "All properties pushed. Please check <a href=\"#logs\">the logs</a> to ensure there were no failures";
                echo"<div class=\"updated notice-success\"> <p>$message</p></div>";
            }*/
        }
    }


    public function add_zooplarealtimefeed_checkboxes()
    {
        global $post;
        
    	$portals = array();
    	$current_zooplarealtimefeed_options = get_option( 'propertyhive_zooplarealtimefeed' );
            
        if ($current_zooplarealtimefeed_options !== FALSE)
        {
	       	if (isset($current_zooplarealtimefeed_options['portals']))
	       	{
	       		$portals = $current_zooplarealtimefeed_options['portals'];
	       	}
	    }
	    if (!empty($portals))
	    {
	    	echo '<p class="form-field"><label><strong>' . __( 'Send to Portals', 'propertyhive' ) . ':</strong></label></p>';

	    	foreach ($portals as $i => $portal)
	    	{
	    		if ($portal['mode'] == 'test' || $portal['mode'] == 'live')
	    		{
				    propertyhive_wp_checkbox( array( 
			            'id' => '_zoopla_realtime_portal_' . $i, 
			            'label' => $portal['name'] . ' (<a href="' . admin_url( 'admin.php?page=ph-settings&tab=zooplarealtimefeed&portal_id=' . $i . '&property_id=' . $post->ID . '#logs' ) . '">' . __( 'Logs', 'propertyhive' ) . '</a>)', 
			        ) );
				}
		    }
		}
    }

    public function save_zooplarealtimefeed_checkboxes( $post_id )
    {
    	$portals = array();
    	$current_zooplarealtimefeed_options = get_option( 'propertyhive_zooplarealtimefeed' );
            
        if ($current_zooplarealtimefeed_options !== FALSE)
        {
	       	if (isset($current_zooplarealtimefeed_options['portals']))
	       	{
	       		$portals = $current_zooplarealtimefeed_options['portals'];
	       	}
	    }
	    if (!empty($portals))
	    {
	    	foreach ($portals as $i => $portal)
	    	{
	    		if ($portal['mode'] == 'test' || $portal['mode'] == 'live')
	    		{
    				update_post_meta($post_id, '_zoopla_realtime_portal_' . $i, ( isset($_POST['_zoopla_realtime_portal_' . $i]) ? $_POST['_zoopla_realtime_portal_' . $i] : '' ) );
    			}
    		}
    	}
    }

    /**
     * Add a new settings tab to the PropertyHive settings tabs array.
     *
     * @param array $settings_tabs Array of PropertyHive setting tabs & their labels
     * @return array $settings_tabs Array of PropertyHive setting tabs & their labels
     */
    public function add_settings_tab( $settings_tabs ) {
        $settings_tabs['zooplarealtimefeed'] = __( 'Zoopla RTDF', 'propertyhive' );
        return $settings_tabs;
    }

    /**
     * Uses the PropertyHive options API to save settings.
     *
     * @uses propertyhive_update_options()
     * @uses self::get_settings()
     */
    public function save() {
        global $current_section, $post;

        if (strpos($current_section, 'mapping_') !== FALSE)
        {
            $custom_field = str_replace("mapping_", "", $current_section);

            $current_zooplarealtimefeed_options = get_option( 'propertyhive_zooplarealtimefeed' );
            $current_mappings = ( ( isset($current_zooplarealtimefeed_options['mappings']) ) ? $current_zooplarealtimefeed_options['mappings'] : array() );
            $current_mappings = ( ( isset($current_mappings[$custom_field]) ) ? $current_mappings[$custom_field] : array() );

            $term_ids = explode(",", $_POST[$custom_field . '_term_ids']);

            $new_mapping = array();
            foreach ($term_ids as $term_id)
            {
                if ($_POST[$custom_field . '_' . $term_id] != '')
                {
                    $new_mapping[$term_id] = $_POST[$custom_field . '_' . $term_id];
                }
            }

            if (!isset($current_zooplarealtimefeed_options['mappings']))
            {
                $current_zooplarealtimefeed_options['mappings'] = array();
            }
            $current_zooplarealtimefeed_options['mappings'][$custom_field] = $new_mapping;

            update_option( 'propertyhive_zooplarealtimefeed', $current_zooplarealtimefeed_options );

            PH_Admin_Settings::add_message( __( ucwords($custom_field) . ' custom field mapping updated successfully', 'propertyhive' ) . ' ' . '<a href="' . admin_url( 'admin.php?page=ph-settings&tab=zooplarealtimefeed' ) . '">' . __( 'Return to Real-Time Feed Options', 'propertyhive' ) . '</a>' );
        }
        else
        {
            switch ($current_section)
            {
                case 'addportal': 
                {
                    // TODO: Validate
                    $error = '';
                    $certificate_file_name = '';

                    if ( !isset($_FILES['certificate_file']) || $_FILES['certificate_file']['size'] == 0 )
                    {
                        // No file uploaded
                    }
                    else
                    {
                        try {

                            // Check $_FILES['upfile']['error'] value.
                            switch ($_FILES['certificate_file']['error']) {
                                case UPLOAD_ERR_OK:
                                    break;
                                case UPLOAD_ERR_NO_FILE:
                                    throw new RuntimeException('No file sent.');
                                case UPLOAD_ERR_INI_SIZE:
                                case UPLOAD_ERR_FORM_SIZE:
                                    $error = __( 'Signed certificate file exceeded filesize limit.', 'propertyhive' );
                                default:
                                    $error = __( 'Unknown error when uploading signed certificate file.', 'propertyhive' );
                            }

                            if ($error == '')
                            {  
                                // You should also check filesize here. 
                                if ($_FILES['certificate_file']['size'] > 1000000) {
                                    $error = __( 'Exceeded filesize limit.', 'propertyhive' );
                                }

                                if ($error == '')
                                {  
                                    $ext = 'crt';

                                    // Check if the extension is active on the server
                                    if (class_exists('finfo'))
                                    {
                                        // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
                                        // Check MIME Type by yourself.
                                        $finfo = new finfo(FILEINFO_MIME_TYPE);
                                        if (false === $ext = array_search(
                                            $finfo->file($_FILES['certificate_file']['tmp_name']),
                                            array(
                                                'crt' => 'application/octet-stream',
                                                'crt' => 'text/plain'
                                            ),
                                            true
                                        )) {
                                            $error = __( 'Signed certificate file must be of type .crt', 'propertyhive' );
                                        }
                                    }

                                    if ($error == '')
                                    { 
                                        $uploads_dir = wp_upload_dir();
                                        $uploads_dir = $uploads_dir['basedir'] . '/zoopla_realtime/';

                                        $certificate_file_name = sha1_file($_FILES['certificate_file']['tmp_name']) . '.' . $ext;

                                        // You should name it uniquely.
                                        // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
                                        // On this example, obtain safe unique name from its binary data.
                                        if (!move_uploaded_file(
                                            $_FILES['certificate_file']['tmp_name'],
                                            sprintf(
                                                $uploads_dir . '%s',
                                                $certificate_file_name
                                            )
                                        )) {
                                            $error = __( 'Failed to move uploaded certificate file.', 'propertyhive' );
                                        }
                                    }

                                }

                            }

                        } catch (RuntimeException $e) {

                            $error = $e->getMessage();

                        }
                    }

                    $private_key_file_name = '';

                    if ( !isset($_FILES['private_key_file']) || $_FILES['private_key_file']['size'] == 0 )
                    {
                        // No file uploaded
                    }
                    else
                    {
                        try {

                            // Check $_FILES['upfile']['error'] value.
                            switch ($_FILES['private_key_file']['error']) {
                                case UPLOAD_ERR_OK:
                                    break;
                                case UPLOAD_ERR_NO_FILE:
                                    throw new RuntimeException('No file sent.');
                                case UPLOAD_ERR_INI_SIZE:
                                case UPLOAD_ERR_FORM_SIZE:
                                    $error = __( 'Private key file exceeded filesize limit.', 'propertyhive' );
                                default:
                                    $error = __( 'Unknown error when uploading private key file.', 'propertyhive' );
                            }

                            if ($error == '')
                            {  
                                // You should also check filesize here. 
                                if ($_FILES['private_key_file']['size'] > 1000000) {
                                    $error = __( 'Exceeded filesize limit.', 'propertyhive' );
                                }

                                if ($error == '')
                                {  
                                    $ext = 'pem';

                                    // Check if the extension is active on the server
                                    if (class_exists('finfo'))
                                    {
                                        // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
                                        // Check MIME Type by yourself.
                                        $finfo = new finfo(FILEINFO_MIME_TYPE);
                                        if (false === $ext = array_search(
                                            $finfo->file($_FILES['private_key_file']['tmp_name']),
                                            array(
                                                'pem' => 'application/octet-stream',
                                                'pem' => 'text/plain'
                                            ),
                                            true
                                        )) {
                                            $error = __( 'Private key file must be of type .pem', 'propertyhive' );
                                        }
                                    }

                                    if ($error == '')
                                    { 
                                        $uploads_dir = wp_upload_dir();
                                        $uploads_dir = $uploads_dir['basedir'] . '/zoopla_realtime/';

                                        $private_key_file_name = sha1_file($_FILES['private_key_file']['tmp_name']) . '.' . $ext;

                                        // You should name it uniquely.
                                        // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
                                        // On this example, obtain safe unique name from its binary data.
                                        if (!move_uploaded_file(
                                            $_FILES['private_key_file']['tmp_name'],
                                            sprintf(
                                                $uploads_dir . '%s',
                                                $private_key_file_name
                                            )
                                        )) {
                                            $error = __( 'Failed to move uploaded private key file.', 'propertyhive' );
                                        }
                                    }

                                }

                            }

                        } catch (RuntimeException $e) {

                            $error = $e->getMessage();

                        }
                    }

                    if ($error == '')
                    {                    
                        $current_zooplarealtimefeed_options = get_option( 'propertyhive_zooplarealtimefeed' );
                        
                        if ($current_zooplarealtimefeed_options === FALSE)
                        {
                        	// This is a new option
                        	$new_zooplarealtimefeed_options = array();
                        	$new_zooplarealtimefeed_options['portals'] = array();
                        }
                        else
                        {
                        	$new_zooplarealtimefeed_options = $current_zooplarealtimefeed_options;
                        }

                        $branch_codes = array();

                        $query_args = array(
            	            'post_type' => 'office',
            	            'nopaging' => true,
            	            'orderby' => 'title',
            	            'order' => 'ASC'
            	        );
            	        $office_query = new WP_Query( $query_args );
            	        
            	        if ( $office_query->have_posts() )
            	        {
            	            while ( $office_query->have_posts() )
            	            {
            	                $office_query->the_post();
            	                
            	                if ( get_option( 'propertyhive_active_departments_sales' ) == 'yes' || get_option( 'propertyhive_active_departments_commercial' ) == 'yes' )
            	                {
            	                	$branch_codes['branch_code_' . $post->ID . '_sales'] = trim($_POST['branch_code_' . $post->ID . '_sales']);
            	                }
            	                if ( get_option( 'propertyhive_active_departments_lettings' ) == 'yes' || get_option( 'propertyhive_active_departments_commercial' ) == 'yes' )
            	                {
            	                	$branch_codes['branch_code_' . $post->ID . '_lettings'] = trim($_POST['branch_code_' . $post->ID . '_lettings']);
            	                }

                                $custom_departments = ph_get_custom_departments();
                                if ( !empty($custom_departments) )
                                {
                                    foreach ( $custom_departments as $key => $custom_department )
                                    {
                                        $branch_codes['branch_code_' . $post->ID . '_' . $key] = trim($_POST['branch_code_' . $post->ID . '_' . $key]);
                                    }
                                }
            	            }
            	        }
            	        else
            	        {

            	        }
            	        wp_reset_postdata();

                        $portal = array(
                        	'name' => wp_strip_all_tags( $_POST['portal_name'] ),
                        	'mode' => $_POST['mode'],
                            'overseas' => ( (isset($_POST['overseas'])) ? $_POST['overseas'] : '' ),
                            'unique_property_id' => ( (isset($_POST['unique_property_id'])) ? $_POST['unique_property_id'] : 'post_id' ),
                            'only_send_if_different' => ( (isset($_POST['only_send_if_different'])) ? $_POST['only_send_if_different'] : '' ),

                        	'certificate_file' => $certificate_file_name,
                        	'private_key_file' => $private_key_file_name,

                            'send_property_api_url' => trim($_POST['send_property_api_url']),
                            'remove_property_api_url' => trim($_POST['remove_property_api_url']),
                            'get_branch_properties_api_url' => trim($_POST['get_branch_properties_api_url']),

                        	'branch_codes' => $branch_codes
                        );

                        if ( get_option('propertyhive_module_disabled_enquiries') != 'yes' )
                        {
                            $portal['enquiries_ftp_host'] = trim($_POST['enquiries_ftp_host']);
                            $portal['enquiries_ftp_username'] = trim($_POST['enquiries_ftp_username']);
                            $portal['enquiries_ftp_password'] = trim($_POST['enquiries_ftp_password']);
                        }

                        $new_zooplarealtimefeed_options['portals'][] = $portal;

                        update_option( 'propertyhive_zooplarealtimefeed', $new_zooplarealtimefeed_options );

                        PH_Admin_Settings::add_message( __( 'Portal added successfully', 'propertyhive' ) . ' ' . '<a href="' . admin_url( 'admin.php?page=ph-settings&tab=zooplarealtimefeed' ) . '">' . __( 'Return to Real-Time Feed Options', 'propertyhive' ) . '</a>' );
                    }
                    else
                    {
                        PH_Admin_Settings::add_error( __( 'Error: ', 'propertyhive' ) . ' ' . $error . ' <a href="' . admin_url( 'admin.php?page=ph-settings&tab=zooplarealtimefeed' ) . '">' . __( 'Return to Real-Time Feed Options', 'propertyhive' ) . '</a>' );
                    }

                    break;
                }
                case 'editportal': {

                    $current_id = ( !isset( $_REQUEST['id'] ) ) ? '' : sanitize_title( $_REQUEST['id'] );
                    $current_zooplarealtimefeed_options = get_option( 'propertyhive_zooplarealtimefeed' );

                    $error = '';
                    $certificate_file_name = $current_zooplarealtimefeed_options['portals'][$current_id]['certificate_file'];

                    if ( !isset($_FILES['certificate_file']) || $_FILES['certificate_file']['size'] == 0 )
                    {
                        // No file uploaded
                    }
                    else
                    {
                        try {

                            // Check $_FILES['upfile']['error'] value.
                            switch ($_FILES['certificate_file']['error']) {
                                case UPLOAD_ERR_OK:
                                    break;
                                case UPLOAD_ERR_NO_FILE:
                                    throw new RuntimeException('No file sent.');
                                case UPLOAD_ERR_INI_SIZE:
                                case UPLOAD_ERR_FORM_SIZE:
                                    $error = __( 'Certificate file exceeded filesize limit.', 'propertyhive' );
                                default:
                                    $error = __( 'Unknown error when uploading certificate file.', 'propertyhive' );
                            }

                            if ($error == '')
                            {  
                                // You should also check filesize here. 
                                if ($_FILES['certificate_file']['size'] > 1000000) {
                                    $error = __( 'Exceeded filesize limit.', 'propertyhive' );
                                }

                                if ($error == '')
                                {  
                                    $ext = 'crt';
                                    
                                    // Check if the extension is active on the server
                                    if (class_exists('finfo'))
                                    {
                                        // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
                                        // Check MIME Type by yourself.
                                        $finfo = new finfo(FILEINFO_MIME_TYPE);
                                        if (false === $ext = array_search(
                                            $finfo->file($_FILES['certificate_file']['tmp_name']),
                                            array(
                                                'crt' => 'application/octet-stream',
                                                'crt' => 'text/plain'
                                            ),
                                            true
                                        )) {
                                            $error = __( 'Certificate file must be of type .pem', 'propertyhive' );
                                        }
                                    }

                                    if ($error == '')
                                    { 
                                        $uploads_dir = wp_upload_dir();
                                        $uploads_dir = $uploads_dir['basedir'] . '/zoopla_realtime/';

                                        $certificate_file_name = sha1_file($_FILES['certificate_file']['tmp_name']) . '.' . $ext;

                                        // You should name it uniquely.
                                        // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
                                        // On this example, obtain safe unique name from its binary data.
                                        if (!move_uploaded_file(
                                            $_FILES['certificate_file']['tmp_name'],
                                            sprintf(
                                                $uploads_dir . '%s',
                                                $certificate_file_name
                                            )
                                        )) {
                                            $error = __( 'Failed to move uploaded certificate file.', 'propertyhive' );
                                        }
                                    }

                                }

                            }

                        } catch (RuntimeException $e) {

                            $error = $e->getMessage();

                        }
                    }

                    $private_key_file_name = $current_zooplarealtimefeed_options['portals'][$current_id]['private_key_file'];

                    if ( !isset($_FILES['private_key_file']) || $_FILES['private_key_file']['size'] == 0 )
                    {
                        // No file uploaded
                    }
                    else
                    {
                        try {

                            // Check $_FILES['upfile']['error'] value.
                            switch ($_FILES['private_key_file']['error']) {
                                case UPLOAD_ERR_OK:
                                    break;
                                case UPLOAD_ERR_NO_FILE:
                                    throw new RuntimeException('No file sent.');
                                case UPLOAD_ERR_INI_SIZE:
                                case UPLOAD_ERR_FORM_SIZE:
                                    $error = __( 'Private key file exceeded filesize limit.', 'propertyhive' );
                                default:
                                    $error = __( 'Unknown error when uploading private key file.', 'propertyhive' );
                            }

                            if ($error == '')
                            {  
                                // You should also check filesize here. 
                                if ($_FILES['private_key_file']['size'] > 1000000) {
                                    $error = __( 'Exceeded filesize limit.', 'propertyhive' );
                                }

                                if ($error == '')
                                {  
                                    $ext = 'pem';

                                    // Check if the extension is active on the server
                                    if (class_exists('finfo'))
                                    {
                                        // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
                                        // Check MIME Type by yourself.
                                        $finfo = new finfo(FILEINFO_MIME_TYPE);
                                        if (false === $ext = array_search(
                                            $finfo->file($_FILES['private_key_file']['tmp_name']),
                                            array(
                                                'pem' => 'application/octet-stream',
                                                'pem' => 'text/plain'
                                            ),
                                            true
                                        )) {
                                            $error = __( 'Private key file must be of type .pem', 'propertyhive' );
                                        }
                                    }

                                    if ($error == '')
                                    { 
                                        $uploads_dir = wp_upload_dir();
                                        $uploads_dir = $uploads_dir['basedir'] . '/zoopla_realtime/';

                                        $private_key_file_name = sha1_file($_FILES['private_key_file']['tmp_name']) . '.' . $ext;

                                        // You should name it uniquely.
                                        // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
                                        // On this example, obtain safe unique name from its binary data.
                                        if (!move_uploaded_file(
                                            $_FILES['private_key_file']['tmp_name'],
                                            sprintf(
                                                $uploads_dir . '%s',
                                                $private_key_file_name
                                            )
                                        )) {
                                            $error = __( 'Failed to move uploaded private key file.', 'propertyhive' );
                                        }
                                    }

                                }

                            }

                        } catch (RuntimeException $e) {

                            $error = $e->getMessage();

                        }
                    }

                    if ($error == '')
                    {
                   		$new_zooplarealtimefeed_options = $current_zooplarealtimefeed_options;

                   		$branch_codes = array();

                        $query_args = array(
            	            'post_type' => 'office',
            	            'nopaging' => true,
            	            'orderby' => 'title',
            	            'order' => 'ASC'
            	        );
            	        $office_query = new WP_Query( $query_args );
            	        
            	        if ( $office_query->have_posts() )
            	        {
            	            while ( $office_query->have_posts() )
            	            {
            	                $office_query->the_post();
            	                
            	                if ( get_option( 'propertyhive_active_departments_sales' ) == 'yes' || get_option( 'propertyhive_active_departments_commercial' ) == 'yes' )
            	                {
            	                	$branch_codes['branch_code_' . $post->ID . '_sales'] = trim($_POST['branch_code_' . $post->ID . '_sales']);
            	                }
            	                if ( get_option( 'propertyhive_active_departments_lettings' ) == 'yes' || get_option( 'propertyhive_active_departments_commercial' ) == 'yes' )
            	                {
            	                	$branch_codes['branch_code_' . $post->ID . '_lettings'] = trim($_POST['branch_code_' . $post->ID . '_lettings']);
            	                }

                                $custom_departments = ph_get_custom_departments();
                                if ( !empty($custom_departments) )
                                {
                                    foreach ( $custom_departments as $key => $custom_department )
                                    {
                                        $branch_codes['branch_code_' . $post->ID . '_' . $key] = trim($_POST['branch_code_' . $post->ID . '_' . $key]);
                                    }
                                }
            	            }
            	        }
            	        else
            	        {

            	        }
            	        wp_reset_postdata();

                        $portal = array(
                        	'name' => wp_strip_all_tags( trim( $_POST['portal_name'] ) ),
                        	'mode' => $_POST['mode'],
                            'overseas' => ( (isset($_POST['overseas'])) ? $_POST['overseas'] : '' ),
                            'unique_property_id' => ( (isset($_POST['unique_property_id'])) ? $_POST['unique_property_id'] : 'post_id' ),
                            'only_send_if_different' => ( (isset($_POST['only_send_if_different'])) ? $_POST['only_send_if_different'] : '' ),

                        	'certificate_file' => $certificate_file_name,
                            'private_key_file' => $private_key_file_name,

                            'send_property_api_url' => trim($_POST['send_property_api_url']),
                            'remove_property_api_url' => trim($_POST['remove_property_api_url']),
                            'get_branch_properties_api_url' => trim($_POST['get_branch_properties_api_url']),

                        	'branch_codes' => $branch_codes
                        );

                        if ( get_option('propertyhive_module_disabled_enquiries') != 'yes' )
                        {
                            $portal['enquiries_ftp_host'] = trim($_POST['enquiries_ftp_host']);
                            $portal['enquiries_ftp_username'] = trim($_POST['enquiries_ftp_username']);
                            $portal['enquiries_ftp_password'] = trim($_POST['enquiries_ftp_password']);
                        }

                        $new_zooplarealtimefeed_options['portals'][$current_id] = $portal;

                   		update_option( 'propertyhive_zooplarealtimefeed', $new_zooplarealtimefeed_options );
                        
                        PH_Admin_Settings::add_message( __( 'Portal details updated successfully', 'propertyhive' ) . ' ' . '<a href="' . admin_url( 'admin.php?page=ph-settings&tab=zooplarealtimefeed' ) . '">' . __( 'Return to Real-Time Feed Options', 'propertyhive' ) . '</a>' );
                    }
                    else
                    {
                        PH_Admin_Settings::add_error( __( 'Error: ', 'propertyhive' ) . ' ' . $error . ' <a href="' . admin_url( 'admin.php?page=ph-settings&tab=zooplarealtimefeed' ) . '">' . __( 'Return to Real-Time Feed Options', 'propertyhive' ) . '</a>' );
                    }

                    break;
                }
                default:
                {
                	propertyhive_update_options( self::get_portals_settings() );
                }
            }
        }
    }

    /**
     * Uses the PropertyHive admin fields API to output settings.
     *
     * @uses propertyhive_admin_fields()
     * @uses self::get_settings()
     */
    public function output() {

    	global $current_section, $hide_save_button;
        
        if (strpos($current_section, 'mapping_') !== FALSE)
        {
            // Doing custom field mapping
            propertyhive_admin_fields( self::get_customfields_settings() );
        }
        else
        {
            switch ($current_section)
            {
                case "addportal":
                {
                    propertyhive_admin_fields( self::get_portals_settings() );
                    break;
                }
                case "editportal":
                {
                    propertyhive_admin_fields( self::get_portals_settings() );
                    break;
                }
                default:
                {
                    $hide_save_button = true;
                    propertyhive_admin_fields( self::get_zooplarealtimefeed_settings() );
                }
            }
        }
	}

	/**
     * Get all the main settings for this plugin
     *
     * @return array Array of settings
     */
	public function get_zooplarealtimefeed_settings() {

        $html = '';
        
        $sections = $this->get_customfields_sections();

        $current_zooplarealtimefeed_options = get_option( 'propertyhive_zooplarealtimefeed' );
        $current_mappings = ( ( isset($current_zooplarealtimefeed_options['mappings']) ) ? $current_zooplarealtimefeed_options['mappings'] : array() );
        
        $i = 0;
        foreach ($sections as $key => $value)
        {
            $html .= '<p>
                <a href="' . admin_url( 'admin.php?page=ph-settings&tab=zooplarealtimefeed&section=mapping_' . $key ) . '">' . $value . '</a>';

            $this_mapping = ( isset($current_mappings[$key]) ? $current_mappings[$key] : array() );

            if (empty($this_mapping))
            {
                $html .= ' - <span style="color:#900"><strong>No mappings set</strong></span>';
            }
            else
            {
                // get number of options in custom field
                $num_terms = 0;

                $args = array(
                    'hide_empty' => false,
                    'parent' => 0
                );
                $terms = get_terms( str_replace("overseas_", "", str_replace("-", "_", $key)), $args );
                if ( !empty( $terms ) && !is_wp_error( $terms ) )
                {
                    $num_terms = count($terms);

                    foreach ($terms as $term)
                    {
                        $args = array(
                            'hide_empty' => false,
                            'parent' => $term->term_id
                        );
                        $subterms = get_terms( str_replace("-", "_", $key), $args );
                        
                        if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                        {
                            $num_terms = $num_terms + count($subterms);
                        }
                    }
                }
                if ($num_terms == count($this_mapping))
                {
                    $html .= ' - <span style="color:#090"><strong>All mappings set</strong></span>';
                }
                else
                {
                    $html .= ' - <span style="color:#FC0"><strong>Custom fields exist with no mapping set</strong></span>';
                }
            }

            $html .= '
            </p>';
        }

	    $settings = array(

	        array( 'title' => __( 'Portals', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'portals' ),

	        array(
                'type'      => 'zoopla_realtime_portals',
            ),

	        array( 'type' => 'sectionend', 'id' => 'portals'),

            array( 'title' => __( 'Logs', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'logs' ),

            array(
                'type'      => 'zoopla_realtime_logs',
            ),

            array( 'type' => 'sectionend', 'id' => 'logs'),

	       	array( 'title' => __( 'Custom Field Mapping', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'custom_field_mapping' ),

	        array(
                'type'      => 'html',
                'title'     => __( 'Custom Fields', 'propertyhive' ),
                'html'      => $html
            ),

	       	array( 'type' => 'sectionend', 'id' => 'custom_field_mapping'),
	    );
	    return apply_filters( 'ph_settings_zooplarealtimefeed_settings', $settings );
	}

    public function get_real_time_feed_mapping_values($custom_field) {

        if ($custom_field == 'availability' || $custom_field == 'overseas-availability')
        {
            return array(
                'available' => 'Available',
                'under_offer' => 'Under Offer',
                'sold_subject_to_contract' => 'Sold STC',
                'sold' => 'Sold',
                'let_agreed' => 'Let Agreed',
                'let' => 'Let',
            );
        }
        if ($custom_field == 'property-type')
        {
            return array(
                'barn_conversion' => 'Barn conversion',
                'block_of_flats' => 'Block of flats',
                'bungalow' => 'Bungalow',
                'chalet' => 'Chalet',
                'chateau' => 'Château',
                'cottage' => 'Cottage',
                'country_house' => 'Country house',
                'detached' => 'Detached house',
                'detached_bungalow' => 'Detached bungalow',
                'end_terrace' => 'End terrace house',
                'equestrian' => 'Equestrian property',
                'farm' => 'Farm',
                'farmhouse' => 'Farmhouse',
                'finca' => 'Finca',
                'flat' => 'Flat',
                'houseboat' => 'Houseboat',
                'land' => 'Land',
                'link_detached' => 'Link-detached house',
                'lodge' => 'Lodge',
                'longere' => 'Longère',
                'maisonette' => 'Maisonette',
                'mews' => 'Mews house',
                'park_home' => 'Mobile/park home',
                'parking' => 'Parking/garage',
                'riad' => 'Riad',
                'semi_detached' => 'Semi-detached house',
                'semi_detached_bungalow' => 'Semi-detached bungalow',
                'studio' => 'Studio',
                'terraced' => ' Terraced house',
                'terraced_bungalow' => 'Terraced bungalow',
                'town_house' => 'Town house',
                'villa' => 'Villa',
            );
        }
        if ($custom_field == 'commercial-property-type')
        {
            return array(
                'block_of_flats' => 'Block of flats',
                'business_park' => 'Business park',
                'farm' => 'Farm',
                'hotel' => 'Hotel/guest house',
                'industrial' => 'Industrial',
                'land' => 'Land',
                'leisure' => 'Leisure/hospitality',
                'light_industrial' => 'Light industrial',
                'office' => 'Office',
                'parking' => 'Parking/garage',
                'pub_bar' => 'Pub/bar',
                'restaurant' => 'Restaurant/cafe',
                'retail' => 'Retail premises',
                'warehouse' => 'Warehouse',
            );
        }
        if ($custom_field == 'outside-space')
        {
            return array(
                'balcony' => 'Balcony',
                'communal_garden' => 'Communal Garden',
                'private_garden' => 'Private Garden',
                'roof_terrace' => 'Roof Terrace',
                'terrace' => 'Terrace',
            );
        }
        if ($custom_field == 'parking')
        {
            return array(
                'double_garage' => 'Double Garage',
                'off_street_parking' => 'Off-Street Parking',
                'residents_parking' => 'Residents Parking',
                'single_garage' => 'Single Garage',
                'underground' => 'Underground',
            );
        }
        if ($custom_field == 'price-qualifier')
        {
            return array(
                'fixed_price' => 'Fixed Price',
                'from' => 'From',
                'guide_price' => 'Guide Price',
                'non_quoting' => 'Non-Quoting',
                'offers_in_the_region_of' => 'OIRO',
                'offers_over' => 'Offers Over',
                'price_on_application' => 'POA',
                'sale_by_tender' => 'Sale by Tender',
            );
        }
        if ($custom_field == 'tenure' || $custom_field == 'commercial-tenure')
        {
            return array(
                'feudal' => 'Feudal',
                'freehold' => 'Freehold',
                'leasehold' => 'Leasehold',
                'share_of_freehold' => 'Share of Freehold',
            );
        }
        if ($custom_field == 'furnished')
        {
            return array(
                'furnished' => 'Furnished',
                'part_furnished' => 'Part-furnished',
                'unfurnished' => 'Unfurnished',
                'furnished_or_unfurnished' => 'Furnished or Unfurnished',
            );
        }

    }

    /**
     * Get custom field mapping settings
     *
     * @return array Array of settings
     */
    public function get_customfields_settings() {

        global $current_section, $post;

        $custom_field = str_replace("mapping_", "", $current_section);

        $current_zooplarealtimefeed_options = get_option( 'propertyhive_zooplarealtimefeed' );
        $current_mappings = ( ( isset($current_zooplarealtimefeed_options['mappings']) ) ? $current_zooplarealtimefeed_options['mappings'] : array() );
        $current_mappings = ( ( isset($current_mappings[$custom_field]) ) ? $current_mappings[$custom_field] : array() );

        $custom_field_options = array();
        $options = array();
        $args = array(
            'hide_empty' => false,
            'parent' => 0
        );
        $terms = get_terms( str_replace("overseas_", "", str_replace("-", "_", $custom_field)), $args );

        $term_ids = array();

        $selected_value = '';
        if ( !empty( $terms ) && !is_wp_error( $terms ) )
        {
            foreach ($terms as $term)
            {
                $options[$term->term_id] = $term->name;
                $term_ids[] = $term->term_id;

                $args = array(
                    'hide_empty' => false,
                    'parent' => $term->term_id
                );
                $subterms = get_terms( str_replace("-", "_", $custom_field), $args );
                
                if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                {
                    foreach ($subterms as $term)
                    {
                        $options[$term->term_id] = ' - ' . $term->name;
                        $term_ids[] = $term->term_id;
                    }
                }
            }
        }



        $settings = array(

            array( 'title' => __( ucwords( str_replace("-", " ", $custom_field) ) . ' Mapping', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'customfield_mapping' )

        );

        $mapping_values = $this->get_real_time_feed_mapping_values($custom_field);

        foreach ($options as $term_id => $term_name)
        {
            $settings[] = array(
                'title' => __( $term_name, 'propertyhive' ),
                'id'        => $custom_field . '_' . $term_id,
                'type'      => 'select',
                'options'   => array('' => '') + $mapping_values,
                'default' => ( (isset($current_mappings[$term_id])) ? $current_mappings[$term_id] : ''),
            );
        }

        $settings[] = array(
            'id'        => $custom_field . '_term_ids',
            'type'      => 'hidden',
            'default'   => implode(",", $term_ids)
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'customfield_mapping');

        return $settings;

    }

	/**
     * Get add/edit portal settings
     *
     * @return array Array of settings
     */
	public function get_portals_settings() {

		global $current_section, $post;

		$current_id = ( !isset( $_REQUEST['id'] ) ) ? '' : sanitize_title( $_REQUEST['id'] );

		$portal_details = array();

		if ($current_id != '')
		{
			// We're editing one

			$current_zooplarealtimefeed_options = get_option( 'propertyhive_zooplarealtimefeed' );

			$portals = $current_zooplarealtimefeed_options['portals'];

			if (isset($portals[$current_id]))
			{
				$portal_details = $portals[$current_id];
			}
		}

        // Should we show overseas option? Yes if we operate in a non UK countries
        $show_overseas = false;
        $countries = get_option( 'propertyhive_countries', array() );
        if ( is_array($countries) && !empty($countries) )
        {
            foreach ( $countries as $country )
            {
                if ( $country != 'GB' )
                {
                    $show_overseas = true;
                }
            }
        }

        $send_property_api_url_help = 'For Zoopla this will likely be:<br>Test: https://realtime-listings-api.webservices.zpg.co.uk/sandbox/v1/listing/update<br>Live: https://realtime-listings-api.webservices.zpg.co.uk/live/v1/listing/update';

        $remove_property_api_url_help = 'For Zoopla this will likely be:<br>Test: https://realtime-listings-api.webservices.zpg.co.uk/sandbox/v1/listing/delete<br>Live: https://realtime-listings-api.webservices.zpg.co.uk/live/v1/listing/delete';

        $get_branch_properties_api_url_help = 'For Zoopla this will likely be:<br>Test: https://realtime-listings-api.webservices.zpg.co.uk/sandbox/v1/listing/list<br>Live: https://realtime-listings-api.webservices.zpg.co.uk/live/v1/listing/list';

		$settings = array(

	        array( 'title' => __( ( $current_section == 'addportal' ? 'Add Portal' : 'Edit Portal' ), 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'portals' ),

	        array(
                'title' => __( 'Portal Name', 'propertyhive' ),
                'id'        => 'portal_name',
                'default'   => ( (isset($portal_details['name'])) ? $portal_details['name'] : ''),
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),

            array(
                'title' => __( 'Mode', 'propertyhive' ),
                'id'        => 'mode',
                'type'      => 'select',
                'options' 	=> array(
                	'off' => 'Off',
                	'test' => 'Test',
                	'live' => 'Live'
                ),
                'default' => ( (isset($portal_details['mode'])) ? $portal_details['mode'] : ''),
                'desc' => __( 'Test mode will mean the portal can be selected on the property record, but no feeds will be sent.', 'propertyhive' ),
            )

        );

        $settings[] = array(
            'title' => __( 'Overseas', 'propertyhive' ),
            'id'        => 'overseas',
            'type'      => 'checkbox',
            'default' => ( (isset($portal_details['overseas']) && $portal_details['overseas'] == 1) ? 'yes' : ''),
        );

	    $settings[] = array( 'type' => 'sectionend', 'id' => 'portals');

	    $settings[] = array( 'title' => __( 'Certificate Details', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'certificate_details' );

        $settings[] = array(
            'title' => __( 'Signed Certificate File (.crt)', 'propertyhive' ),
            'id'        => 'certificate_file',
            'default'   => ( (isset($portal_details['certificate_file'])) ? $portal_details['certificate_file'] : ''),
            'type'      => 'zoopla_certificate_file',
            'desc_tip'  =>  false,
        );

	    $settings[] = array(
            'title' => __( 'Private Key File (.pem)', 'propertyhive' ),
            'id'        => 'private_key_file',
            'default'   => ( (isset($portal_details['private_key_file'])) ? $portal_details['private_key_file'] : ''),
            'type'      => 'zoopla_private_key_file',
            'desc_tip'  =>  false,
        );

	    $settings[] = array( 'type' => 'sectionend', 'id' => 'certificate_details');

        $settings[] = array( 'title' => __( 'API URLs', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'api_urls' );

        $settings[] = array(
            'title' => __( 'Send Property API URL', 'propertyhive' ),
            'id'        => 'send_property_api_url',
            'default'   => ( (isset($portal_details['send_property_api_url'])) ? $portal_details['send_property_api_url'] : ''),
            'type'      => 'text',
            'desc'      => '<p id="sendpropertyurlshelp">' . $send_property_api_url_help . '</p>',
        );

        $settings[] = array(
            'title' => __( 'Remove Property API URL', 'propertyhive' ),
            'id'        => 'remove_property_api_url',
            'default'   => ( (isset($portal_details['remove_property_api_url'])) ? $portal_details['remove_property_api_url'] : ''),
            'type'      => 'text',
            'desc'      => '<p id="removepropertyurlshelp">' . $remove_property_api_url_help . '</p>',
        );

        $settings[] = array(
            'title' => __( 'Get Branch Properties API URL', 'propertyhive' ),
            'id'        => 'get_branch_properties_api_url',
            'default'   => ( (isset($portal_details['get_branch_properties_api_url'])) ? $portal_details['get_branch_properties_api_url'] : ''),
            'type'      => 'text',
            'desc'      => '<p id="getbranchpropertieslisturlshelp">' . $get_branch_properties_api_url_help . '</p>',
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'api_urls');

	    $settings[] = array( 'title' => __( 'Branch Codes', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'branch_codes' );

		$query_args = array(
            'post_type' => 'office',
            'nopaging' => true,
            'orderby' => 'title',
            'order' => 'ASC'
        );
        $office_query = new WP_Query( $query_args );
        
        if ( $office_query->have_posts() )
        {
            while ( $office_query->have_posts() )
            {
                $office_query->the_post();
                
                if ( get_option( 'propertyhive_active_departments_sales' ) == 'yes' || get_option( 'propertyhive_active_departments_commercial' ) == 'yes' )
                {
                	$settings[] = array(
		                'title' 	=> get_the_title() . ' ('. __( 'Sales', 'propertyhive' ) . ')',
		                'id'        => 'branch_code_' . $post->ID . '_sales',
		                'default'   => ( (isset($portal_details['branch_codes']['branch_code_' . $post->ID . '_sales'])) ? $portal_details['branch_codes']['branch_code_' . $post->ID . '_sales'] : ''),
		                'type'      => 'text',
		                'desc_tip'  =>  false,
		            );
                }
                if ( get_option( 'propertyhive_active_departments_lettings' ) == 'yes' || get_option( 'propertyhive_active_departments_commercial' ) == 'yes' )
                {
                	$settings[] = array(
		                'title' 	=> get_the_title() . ' ('. __( 'Lettings', 'propertyhive' ) . ')',
		                'id'        => 'branch_code_' . $post->ID . '_lettings',
		                'default'   => ( (isset($portal_details['branch_codes']['branch_code_' . $post->ID . '_lettings'])) ? $portal_details['branch_codes']['branch_code_' . $post->ID . '_lettings'] : ''),
		                'type'      => 'text',
		                'desc_tip'  =>  false,
		            );
                }

                $custom_departments = ph_get_custom_departments();
                if ( !empty($custom_departments) )
                {
                    foreach ( $custom_departments as $key => $custom_department )
                    {
                        $settings[] = array(
                            'title'     => get_the_title() . ' ('. $custom_department['name'] . ')',
                            'id'        => 'branch_code_' . $post->ID . '_' . $key,
                            'default'   => ( (isset($portal_details['branch_codes']['branch_code_' . $post->ID . '_' . $key])) ? $portal_details['branch_codes']['branch_code_' . $post->ID . '_' . $key] : ''),
                            'type'      => 'text',
                            'desc_tip'  =>  false,
                        );
                    }
                }
            }
        }
        else
        {

        }
        wp_reset_postdata();

        $settings[] =  array( 'type' => 'sectionend', 'id' => 'branch_codes');

        $settings[] = array( 'title' => __( 'Advanced Settings', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'advanced' );

        $settings[] = array(
            'title'     => __( 'Only Send Property If Different From Last Time Sent', 'propertyhive' ),
            'id'        => 'only_send_if_different',
            'type'      => 'checkbox',
            'default'   => ( (isset($portal_details['only_send_if_different']) && $portal_details['only_send_if_different'] == '1') ? 'yes' : ''),
            'desc'      => __( 'By default a property will be sent to the portal each time it is saved. Most of the time the data might remain unchanged thus causing unnecessary requests to be sent. Select this option if we should only send the property if the data has changed since last time it was sent. Especially applicable if importing properties AND sending them in real-time feeds, otherwise you can probably leave this unticked.', 'propertyhive' ),
        );

        $settings[] = array(
            'title' => __( 'Unique Property ID', 'propertyhive' ),
            'id'        => 'unique_property_id',
            'type'      => 'radio',
            'default' => ( (isset($portal_details['unique_property_id']) && $portal_details['unique_property_id'] == 'ref') ? 'ref' : 'post_id'),
            'options'   => array(
                'post_id' => 'WordPress Post ID (recommended)',
                'ref' => 'Property Reference Number',
            ),
            'desc' => __( 'We need a unique property identifier to link a property in Property Hive to the same property on the portal. We recommend using the WordPress Post ID as this will always be unique. However, if you wish to use the reference number entered on the property record you must ensure a) one exists and b) it is completely unique.<br><br><strong>Please note</strong>: Changing this option once properties have already been sent will result in them being seen as completely new properties. You will also need to re-push all properties.', 'propertyhive' ),
        );

        if ( get_option('propertyhive_module_disabled_enquiries') != 'yes' )
        {
            $settings[] = array(
                'title'   => __( 'Enquiries FTP Host', 'propertyhive' ),
                'id'      => 'enquiries_ftp_host',
                'type'    => 'text',
                'default' => ( (isset($portal_details['enquiries_ftp_host'])) ? $portal_details['enquiries_ftp_host'] : ''),
            );

            $settings[] = array(
                'title'   => __( 'Enquiries FTP Username', 'propertyhive' ),
                'id'      => 'enquiries_ftp_username',
                'type'    => 'text',
                'default' => ( (isset($portal_details['enquiries_ftp_username'])) ? $portal_details['enquiries_ftp_username'] : ''),
            );

            $settings[] = array(
                'title'   => __( 'Enquiries FTP Password', 'propertyhive' ),
                'id'      => 'enquiries_ftp_password',
                'type'    => 'text',
                'default' => ( (isset($portal_details['enquiries_ftp_password'])) ? $portal_details['enquiries_ftp_password'] : ''),
            );

            // If SSH2 isn't installed, show a warning message
            if (!function_exists("ssh2_connect")) {
                $settings[] = array(
                    'title' => '',
                    'id'    => 'missing_ssh2_warning',
                    'type'  => 'html',
                    'html'  => 'Your WordPress installation does not contain the <a href="https://www.php.net/manual/en/intro.ssh2.php" target="_blank">SSH2 package</a>. This is required to process enquiries from Zoopla',
                );
            }
        }

        // Change URL help text depending on whether overseas or not
        $settings[] = array(
            'type' => 'html',
            'html' => '<script>

                jQuery(document).ready(function()
                {
                    jQuery(\'input[name="overseas"]\').change(function()
                    {
                        ph_set_rtdf_api_url_help_text();
                    });

                    ph_set_rtdf_api_url_help_text();
                });

                function ph_set_rtdf_api_url_help_text()
                {
                    if (jQuery(\'input[name="overseas"]:checked\').length > 0)
                    {
                        jQuery(\'#sendpropertyurlshelp\').html(\'' . $send_property_api_url_help . '\');
                        jQuery(\'#removepropertyurlshelp\').html(\'' . $remove_property_api_url_help . '\');
                        jQuery(\'#getbranchpropertieslisturlshelp\').html(\'' . $get_branch_properties_api_url_help . '\');
                    }
                    else
                    {
                        jQuery(\'#sendpropertyurlshelp\').html(\'' . $send_property_api_url_help . '\');
                        jQuery(\'#removepropertyurlshelp\').html(\'' . $remove_property_api_url_help . '\');
                        jQuery(\'#getbranchpropertieslisturlshelp\').html(\'' . $get_branch_properties_api_url_help . '\');
                    }
                }

            </script>'
        );

	    $settings[] =  array( 'type' => 'sectionend', 'id' => 'advanced');

	    return $settings;
	}

    public function certificate_file_upload( $value )
    {
        ?>
            <tr valign="top">
                <th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ) ?></th>
                <td class="forminp">

                    <?php
                        if ($value['default'] != '')
                        {
                            $uploads_dir = wp_upload_dir();
                            $uploads_dir = $uploads_dir['baseurl'] . '/zoopla_realtime/';
                            echo '<a href="' . $uploads_dir . $value['default'] . '" target="_blank">Download current signed certificate</a><br><br>';
                        }
                    ?>

                    <input type="file" name="<?php echo esc_attr( $value['id'] ); ?>" id="<?php echo esc_attr( $value['id'] ); ?>" />

                </td>
            </tr>
        <?php
    }

    public function private_key_file_upload( $value )
    {
        ?>
            <tr valign="top">
                <th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ) ?></th>
                <td class="forminp">

                    <?php
                        if ($value['default'] != '')
                        {
                            $uploads_dir = wp_upload_dir();
                            $uploads_dir = $uploads_dir['baseurl'] . '/zoopla_realtime/';
                            echo '<a href="' . $uploads_dir . $value['default'] . '" target="_blank">Download current private key</a><br><br>';
                        }
                    ?>

                    <input type="file" name="<?php echo esc_attr( $value['id'] ); ?>" id="<?php echo esc_attr( $value['id'] ); ?>" />

                </td>
            </tr>
        <?php
    }

    /**
     * Output list of logs and allow download
     *
     * @access public
     * @return void
     */
    public function logs_setting() {
        global $wpdb, $post;
        
        $current_realtime_options = get_option( 'propertyhive_zooplarealtimefeed' );
        $portals = array();
        if ($current_realtime_options !== FALSE)
        {
            if (isset($current_realtime_options['portals']))
            {
                $portals = $current_realtime_options['portals'];
            }
        }

        $options = '';
        $query = "
            SELECT 
                *
            FROM 
                " . $wpdb->prefix . "ph_zooplarealtimefeed_logs_error
            WHERE
                1=1 ";
        if ( isset($_GET['portal_id']) && !empty((int)$_GET['portal_id']) )
        {
            $query .= " AND portal_id='" . (int)$_GET['portal_id'] . "' ";
        }
        if ( isset($_GET['property_id']) && !empty((int)$_GET['property_id']) )
        {
            $query .= " AND post_id='" . (int)$_GET['property_id'] . "' ";
        }
        $query .= "
            ORDER BY 
                " . $wpdb->prefix . "ph_zooplarealtimefeed_logs_error.error_date DESC
            LIMIT 250
        ";
        $error_results = $wpdb->get_results($query);
        $errors = array();
        if ( $error_results )
        {
            foreach ($error_results as $error_result)
            {
                $portal_name = '';
                if (!empty($portals))
                {
                    foreach ($portals as $i => $portal)
                    {
                        if ($i == $error_result->portal_id)
                        {
                            $portal_name = $portal['name'];
                        }
                    }
                }

                $error_result->error_date = date("jS M Y H:i", strtotime($error_result->error_date));

                $title = '';
                $link = '';
                $edit_link = '';
                if ($error_result->post_id != '') 
                {
                    $title = get_the_title($error_result->post_id);
                    $link = get_permalink($error_result->post_id);
                    $edit_link = get_edit_post_link($error_result->post_id);
                }
                $options .= '<option value="' . $error_result->id . '"';
                if ( isset($_GET['log_id']) && $_GET['log_id'] == $error_result->id )
                {
                    $options .= ' selected';
                }
                $options .= '>' . ( ($error_result->severity == 0) ? 'Success' : 'Failure' ) . ' - ' . $portal_name . ': ' . $error_result->error_date . ( ($title != '') ? ' - ' . $title : '' ) . '</option>';

                $error_result->title = $title;
                $error_result->request = htmlentities($error_result->request);
                $error_result->response = htmlentities($error_result->response);
                $error_result->link = $link;
                $error_result->edit_link = $edit_link;
                $error_result->portal_name = $portal_name;

                $errors[$error_result->id] = $error_result;
            }
        }

        ?>
        <tr valign="top" id="logs">
            <th scope="row" class="titledesc"><?php _e( 'Log Files', 'propertyhive' ) ?></th>
            <td class="forminp">
            <?php if ($options != '') { ?>
                <select name="feed_portal_id" id="feed_portal_id">
                    <option></option>
                    <?php echo $options; ?>
                </select>
                <div id="realtime_error"></div>
                <script>
                    var realtime_errors = <?php echo json_encode($errors); ?>;
                    jQuery(document).ready(function()
                    {
                        jQuery('select#feed_portal_id').change(function()
                        {
                            load_realtime_log();
                        });

                        load_realtime_log();
                    });

                    function load_realtime_log()
                    {
                        if (jQuery('select#feed_portal_id').val() == '')
                        {
                            jQuery('#realtime_error').html('');
                        }
                        else
                        {
                            var html = '';

                            html += '<p><strong>Status:</strong> ' + ( (realtime_errors[jQuery('select#feed_portal_id').val()].severity == 0) ? 'Success' : 'Failed' ) + '</p>';
                            html += '<p><strong>Portal:</strong> ' + realtime_errors[jQuery('select#feed_portal_id').val()].portal_name + '</p>';
                            html += '<p><strong>Date:</strong> ' + realtime_errors[jQuery('select#feed_portal_id').val()].error_date + '</p>';
                            if (realtime_errors[jQuery('select#feed_portal_id').val()].title) { html += '<p><strong>Property:</strong> ' + realtime_errors[jQuery('select#feed_portal_id').val()].title + ' <a href="' + realtime_errors[jQuery('select#feed_portal_id').val()].edit_link + '">Edit</a> | <a href="' + realtime_errors[jQuery('select#feed_portal_id').val()].link + '" target="_blank">View</a></p>'; }
                            html += '<p><strong>Error:</strong> ' + realtime_errors[jQuery('select#feed_portal_id').val()].message + '</p>';
                            html += '<p><strong>Request:</strong> ' + realtime_errors[jQuery('select#feed_portal_id').val()].request + '</p>';
                            html += '<p><strong>Response:</strong> ' + realtime_errors[jQuery('select#feed_portal_id').val()].response + '</p>';

                            jQuery('#realtime_error').html(html);
                        }
                    }
                </script>
            <?php }else{ echo 'No feeds ran yet. Log files will be available here for 7 days when feeds are ran'; } ?>
            </td>
        </tr>
        <?php
    }

    /**
     * Find out if an overseas portal is setup. Helps to distinguish which options are shown
     *
     * @access private
     * @return boolean
     */
    private function has_overseas_portal()
    {
        $current_zooplarealtimefeed_options = get_option( 'propertyhive_zooplarealtimefeed' );
        $portals = array();
        if ( $current_zooplarealtimefeed_options !== FALSE )
        {
            if ( isset($current_zooplarealtimefeed_options['portals']) && is_array($current_zooplarealtimefeed_options['portals']) && !empty($current_zooplarealtimefeed_options['portals']) )
            {
                $portals = $current_zooplarealtimefeed_options['portals'];

                foreach ( $portals as $portal )
                {
                    if ( isset($portal['overseas']) && $portal['overseas'] == '1' )
                    {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Find out if an non-overseas portal is setup. Helps to distinguish which options are shown
     *
     * @access private
     * @return boolean
     */
    private function has_non_overseas_portal()
    {
        $num_overseas = 0;

        $current_zooplarealtimefeed_options = get_option( 'propertyhive_zooplarealtimefeed' );
        $portals = array();
        if ( $current_zooplarealtimefeed_options !== FALSE )
        {
            if ( isset($current_zooplarealtimefeed_options['portals']) && is_array($current_zooplarealtimefeed_options['portals']) && !empty($current_zooplarealtimefeed_options['portals']) )
            {
                $portals = $current_zooplarealtimefeed_options['portals'];

                foreach ( $portals as $portal )
                {
                    if ( isset($portal['overseas']) && $portal['overseas'] == '1' )
                    {
                        ++$num_overseas;
                    }
                }

                if ( count($portals) == $num_overseas )
                {
                    // All portals are overseas
                    return false;
                }
            }
        }

        return true;
    }

	/**
     * Output list of portals
     *
     * @access public
     * @return void
     */
    public function portals_setting() {
        global $wpdb, $post;

        // Should we show overseas option? Yes if we operate in a non UK countries
        $show_overseas = false;
        $countries = get_option( 'propertyhive_countries', array() );
        if ( is_array($countries) && !empty($countries) )
        {
            foreach ( $countries as $country )
            {
                if ( $country != 'GB' )
                {
                    $show_overseas = true;
                }
            }
        }
        
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="<?php echo admin_url( 'admin.php?page=ph-settings&tab=zooplarealtimefeed&section=addportal' ); ?>" class="button alignright"><?php echo __( 'Add New Portal', 'propertyhive' ); ?></a>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc"><?php _e( 'Portals', 'propertyhive' ) ?></th>
            <td class="forminp">
                <table class="ph_portals widefat" cellspacing="0">
                    <thead>
                        <tr>
                        	<th class="active"><?php _e( 'Active', 'propertyhive' ); ?></th>
                            <th class="name"><?php _e( 'Name', 'propertyhive' ); ?></th>
                            <?php if ($show_overseas) { ?><th class="overseas"><?php _e( 'Overseas', 'propertyhive' ); ?></th><?php } ?>
                            <th class="branches"><?php _e( 'Branches', 'propertyhive' ); ?></th>
                            <th class="api-urls"><?php _e( 'API URLs', 'propertyhive' ); ?></th>
                            <th class="certificate"><?php _e( 'Certificate', 'propertyhive' ); ?></th>
                            <th class="properties"><?php _e( 'Active Properties', 'propertyhive' ); ?></th>
                            <th class="settings">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        	$current_zooplarealtimefeed_options = get_option( 'propertyhive_zooplarealtimefeed' );
                        	$portals = array();
                        	if ($current_zooplarealtimefeed_options !== FALSE)
                        	{
                        		if (isset($current_zooplarealtimefeed_options['portals']))
                        		{
                        			$portals = $current_zooplarealtimefeed_options['portals'];
                        		}
                        	}

                        	if (!empty($portals))
                        	{
                                $num_columns = $show_overseas ? '8' : '7';
                        		foreach ($portals as $i => $portal)
                        		{
                                    if( $portal['mode'] == 'test' )
                                    {
                                        ?>
                                        <tr>
                                            <td colspan="<?php echo $num_columns; ?>" style="padding-bottom:0px;">
                                                <span style="color:#900">No properties will be sent for this feed until it is set to Live</span>
                                            </td>
                                        </tr>
                                        <?php
                                    }
		                        	echo '<tr>';
		                        		echo '<td width="3%" class="active">' . ucwords($portal['mode']) . '</td>';
		                        		echo '<td class="name">' . $portal['name'] . '</td>';
                                        if ($show_overseas) { echo '<td class="overseas">' . ( (isset($portal['overseas']) && $portal['overseas'] == '1') ? 'Yes' : 'No') . '</td>'; }
		                        		echo '<td class="branches">';
		                        		if (isset($portal['branch_codes']) && !empty($portal['branch_codes']))
		                        		{
		                        			$query_args = array(
									            'post_type' => 'office',
									            'nopaging' => true,
									            'orderby' => 'title',
									            'order' => 'ASC'
									        );
									        $office_query = new WP_Query( $query_args );
									        
									        if ( $office_query->have_posts() )
									        {
									            while ( $office_query->have_posts() )
									            {
									                $office_query->the_post();
									                
									                if ( get_option( 'propertyhive_active_departments_sales' ) == 'yes' || get_option( 'propertyhive_active_departments_commercial' ) == 'yes' )
									                {
									                	echo get_the_title() . ' - ' . ( ( isset($portal['branch_codes']['branch_code_' . $post->ID . '_sales']) ) ? $portal['branch_codes']['branch_code_' . $post->ID . '_sales'] : '' ) . '<br>';
									                }
									                if ( get_option( 'propertyhive_active_departments_lettings' ) == 'yes' || get_option( 'propertyhive_active_departments_commercial' ) == 'yes' )
									                {
									                	echo get_the_title() . ' - ' . ( ( isset($portal['branch_codes']['branch_code_' . $post->ID . '_lettings']) ) ? $portal['branch_codes']['branch_code_' . $post->ID . '_lettings'] : '' ) . '<br>';
									                }

                                                    $custom_departments = ph_get_custom_departments();
                                                    if ( !empty($custom_departments) )
                                                    {
                                                        foreach ( $custom_departments as $key => $custom_department )
                                                        {
                                                            echo get_the_title() . ' - ' . ( ( isset($portal['branch_codes']['branch_code_' . $post->ID . '_' . $key]) ) ? $portal['branch_codes']['branch_code_' . $post->ID . '_' . $key] : '' ) . '<br>';
                                                        }
                                                    }
									            }
									        }
									        else
									        {

									        }
									        wp_reset_postdata();
		                        		}
		                        		else
		                        		{
		                        			echo '-';
		                        		}

		                        		echo '</td>';
                                        echo '<td class="certificate">
                                            ' . ( 
                                                    (
                                                        $portal['send_property_api_url'] != '' &&
                                                        $portal['remove_property_api_url'] != '' &&
                                                        $portal['get_branch_properties_api_url'] != ''
                                                    ) ? 
                                                    '<span style="color:#090">All set</span>' : 
                                                    '<span style="color:#900">Missing one or more API URLs</span>'
                                            ) . '
                                        </td>';
		                        		echo '<td class="certificate">
                                            Signed Certificate File: ' . ( ($portal['certificate_file'] != '') ? '<span style="color:#090">Uploaded</span>' : '<span style="color:#900">Not Uploaded</span>' ) . '<br>
		                        			Private Key File: ' . ( ($portal['private_key_file'] != '') ? '<span style="color:#090">Uploaded</span>' : '<span style="color:#900">Not Uploaded</span>' ) . '
		                        		</td>';
                                        echo '</td>';
                                        echo '<td class="properties">';

                                            $args = array(
                                                'post_type' => 'property',
                                                'nopaging' => true,
                                                'fields' => 'ids',
                                            );

                                            $meta_query = array(
                                                array(
                                                    'key' => '_on_market',
                                                    'value' => 'yes'
                                                ),
                                                array(
                                                    'key' => '_zoopla_realtime_portal_' . $i,
                                                    'value' => 'yes'
                                                )
                                            );

                                            if ( isset($portal['overseas']) && $portal['overseas'] == '1' )
                                            {
                                                $meta_query[] = array(
                                                    'key' => '_address_country',
                                                    'value' => array('', 'GB'),
                                                    'compare' => 'NOT IN'
                                                );
                                                $meta_query[] = array(
                                                    'key' => '_department',
                                                    'value' => 'residential-sales',
                                                );
                                            }
                                            else
                                            {
                                                $meta_query[] = array(
                                                    'key' => '_address_country',
                                                    'value' => array('', 'GB'),
                                                    'compare' => 'IN'
                                                );
                                            }

                                            $args['meta_query'] = $meta_query;

                                            $property_query = new WP_Query( $args );
                                            
                                            echo number_format($property_query->found_posts);

                                        echo '</td>';
		                        		echo '<td class="settings">
		                        			<a class="button" href="' . admin_url( 'admin.php?page=ph-settings&tab=zooplarealtimefeed&section=editportal&id=' . $i ) . '">' . __( 'Edit', 'propertyhive' ) . '</a>';
                                        if ( $portal['mode'] == 'live' )
                                        {
                                            echo '&nbsp;<a onclick="alert(\'This will push all properties to ' . $portal['name'] . ' that are on the market and have been selected to be sent to ' . $portal['name'] . '. Please be patient as this may take a few minutes.\'); setTimeout(function() { jQuery(\'#push_all_' . $i . '\').html(\'Pushing...\'); jQuery(\'#push_all_' . $i . '\').attr(\'disabled\', true) }, 10);" id="push_all_' . $i . '" class="button" href="' . admin_url( 'admin.php?page=ph-settings&tab=zooplarealtimefeed&action=zooplapushall&id=' . $i ) . '">' . __( 'Push All Properties', 'propertyhive' ) . '</a>';
                                        }
		                        		echo '</td>';
		                        	echo '</tr>';
	                        	}
                        	}
                        	else
                        	{
                        		echo '<tr>';
	                        		echo '<td align="center" colspan="' . ( ($show_overseas) ? 7 : 6) . '">' . __( 'No portals exist', 'propertyhive' ) . '</td>';
	                        	echo '</tr>';
                        	}
                        ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="<?php echo admin_url( 'admin.php?page=ph-settings&tab=zooplarealtimefeed&section=addportal' ); ?>" class="button alignright"><?php echo __( 'Add New Portal', 'propertyhive' ); ?></a>
            </td>
        </tr>
        <?php
    }

    /**
     * Get custom fields sections
     * Cloned from core class-ph-settings-custom-fields.php
     * Except 'locations' and 'sale by' as these aren't used in the real-time feed
     *
     * @return array
     */
    public function get_customfields_sections() {
        $sections = array();
        
        // Residential Custom Fields
        if ( $this->has_non_overseas_portal() )
        {
            $sections[ 'availability' ] = __( 'Availabilities', 'propertyhive' );
            add_action( 'propertyhive_admin_field_custom_fields_availability', array( $this, 'custom_fields_availability_setting' ) );
        }
        if ( $this->has_overseas_portal() )
        {
            $sections[ 'overseas-availability' ] = trim( ( $this->has_non_overseas_portal() ? __( 'Overseas', 'propertyhive' ) : '' ) . ' ' . __( 'Availabilities', 'propertyhive' ) );
            add_action( 'propertyhive_admin_field_custom_fields_overseas_availability', array( $this, 'custom_fields_overseas_availability_setting' ) );
        }

        $sections[ 'property-type' ] = __( 'Property Types', 'propertyhive' );
        add_action( 'propertyhive_admin_field_custom_fields_property_type', array( $this, 'custom_fields_property_type_setting' ) );
        
        if ( get_option( 'propertyhive_active_departments_commercial' ) == 'yes' )
        {
            $sections[ 'commercial-property-type' ] = __( 'Commercial Property Types', 'propertyhive' );
            add_action( 'propertyhive_admin_field_custom_fields_commercial_property_type', array( $this, 'custom_fields_commercial_property_type_setting' ) );
        }

        $sections[ 'outside-space' ] = __( 'Outside Spaces', 'propertyhive' );
        add_action( 'propertyhive_admin_field_custom_fields_outside_space', array( $this, 'custom_fields_outside_space_setting' ) );
        
        $sections[ 'parking' ] = __( 'Parking', 'propertyhive' );
        add_action( 'propertyhive_admin_field_custom_fields_parking', array( $this, 'custom_fields_parking_setting' ) );

        // Residential Sales Custom Fields
        $sections[ 'price-qualifier' ] = __( 'Price Qualifiers', 'propertyhive' );
        add_action( 'propertyhive_admin_field_custom_fields_price_qualifier', array( $this, 'custom_fields_price_qualifier_setting' ) );
    
        if ( $this->has_non_overseas_portal() )
        {
            $sections[ 'tenure' ] = __( 'Tenures', 'propertyhive' );
            add_action( 'propertyhive_admin_field_custom_fields_tenure', array( $this, 'custom_fields_tenure_setting' ) );

            if ( get_option( 'propertyhive_active_departments_commercial' ) == 'yes' )
            {
                $sections[ 'commercial-tenure' ] = __( 'Commercial Tenures', 'propertyhive' );
                add_action( 'propertyhive_admin_field_custom_fields_commercial_tenure', array( $this, 'custom_fields_commercial_tenure_setting' ) );
            }
            
            // Residential Lettings Custom Fields
            $sections[ 'furnished' ] = __( 'Furnished', 'propertyhive' );
            add_action( 'propertyhive_admin_field_custom_fields_furnished', array( $this, 'custom_fields_furnished_setting' ) );
        }

        return $sections;
    }

    /**
     * Output list of custom field mappings
     *
     * @access public
     * @return void
     */
    public function customfields_setting() {
        global $post;
        
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc"><?php _e( 'Portals', 'propertyhive' ) ?></th>
            <td class="forminp">
                


            </td>
        </tr>
        <?php
    }

    public function get_mapped_value($post_id, $taxonomy)
    {
        $term_list = wp_get_post_terms($post_id, str_replace("-", "_", str_replace("overseas_", "", $taxonomy)), array("fields" => "ids"));

        if ( !is_wp_error($term_list) && !empty($term_list) )
        {
            $current_zooplarealtimefeed_options = get_option( 'propertyhive_zooplarealtimefeed' );
            $current_mappings = ( ( isset($current_zooplarealtimefeed_options['mappings']) ) ? $current_zooplarealtimefeed_options['mappings'] : array() );
            $current_mappings = ( ( isset($current_mappings[str_replace("_", "-", $taxonomy)]) ) ? $current_mappings[str_replace("_", "-", $taxonomy)] : array() );

            if (isset($current_mappings[$term_list[0]]))
            {
                return $current_mappings[$term_list[0]];
            }
        }

        return '';
    }

    public function real_time_feed_reconcile_properties()
    {
        $portals = array();
        $current_realtime_feed_options = get_option( 'propertyhive_zooplarealtimefeed' );
            
        if ($current_realtime_feed_options !== FALSE)
        {
            if (isset($current_realtime_feed_options['portals']))
            {
                $portals = $current_realtime_feed_options['portals'];
            }
        }

        if (!empty($portals))
        {
            $custom_departments = ph_get_custom_departments();

            foreach ($portals as $portal_id => $portal)
            {
                if ( $portal['mode'] != 'live' ) { continue; } // Only continue past this point if the portal is live

                $portal['portal_id'] = $portal_id; // Add to array so easier to pass around

                $sales_related_custom_departments = array();
                foreach ( $custom_departments as $key => $custom_department )
                {
                    if ( $custom_department['based_on'] == 'residential-sales' )
                    {
                        $sales_related_custom_departments[] = $key;
                    }
                }

                // Get array of sales branch codes we need to check for reconcilliation
                $branch_codes = $portal['branch_codes'];
                $new_branch_codes = array();
                foreach ($branch_codes as $id => $branch_code)
                {
                    if ( trim($branch_code) != '' )
                    {
                        if ( substr($id, -6) == '_sales' )
                        {
                            $new_branch_codes[] = $branch_code;
                        }
                        elseif ( !empty($sales_related_custom_departments) )
                        {
                            foreach ( $sales_related_custom_departments as $sales_related_custom_department )
                            {
                                if ( strpos($id, '_' . $sales_related_custom_department) !== false )
                                {
                                    $new_branch_codes[] = $branch_code;
                                }
                            }
                        }
                    }
                }
                $branch_codes = array_unique($new_branch_codes);

                if (
                    !empty($branch_codes) &&
                    (
                        get_option( 'propertyhive_active_departments_sales' ) == 'yes' ||
                        get_option( 'propertyhive_active_departments_commercial' ) == 'yes'  ||
                        !empty($sales_related_custom_departments)
                    )
                )
                {
                    foreach ( $branch_codes as $branch_code )
                    {
                        // Make request to get sales branch properties
                        $request_data = array();

                        $request_data['branch_reference'] = $branch_code;

                        $response = $this->do_curl_request( $portal, $portal['get_branch_properties_api_url'], 'http://realtime-listings.webservices.zpg.co.uk/docs/v1.2/schemas/listing/list.json', $request_data, '', false );

                        if ($response !== FALSE) 
                        {
                            if (isset($response['listings']) && is_array($response['listings']) && !empty($response['listings']))
                            {
                                foreach ($response['listings'] as $property)
                                {
                                    $agent_ref = str_replace($branch_code . '_', "", $property['listing_reference']);

                                    $ok_to_remove = false;
                                    if ( isset($portal['unique_property_id']) && $portal['unique_property_id'] == 'ref' )
                                    {
                                        // find property with this agent ref
                                        $args = array(
                                            'post_type' => 'property',
                                            'posts_per_page' => 1,
                                            'meta_query' => array(
                                                array(
                                                    'key' => '_reference_number',
                                                    'value' => $agent_ref,
                                                    'compare' => '='
                                                ),
                                                array(
                                                    'key' => '_zoopla_realtime_portal_' . $portal_id,
                                                    'value' => 'yes'
                                                ),
                                                array(
                                                    'key' => '_on_market',
                                                    'value' => 'yes'
                                                )
                                            )
                                        );

                                        $property_query = new WP_Query($args);

                                        if ( $property_query->have_posts() )
                                        {

                                        }
                                        else
                                        {
                                            $ok_to_remove = true;
                                        }
                                            
                                    }
                                    else
                                    {
                                        if (!is_numeric($agent_ref))
                                        {
                                            // The agent agent ref we've sent should always be a post ID and therefore an integer
                                            $ok_to_remove = true;
                                        }
                                        else
                                        {
                                            // Check if this agent ref is active, on market and selected to be sent to the portal
                                            $args = array(
                                                'post_type' => 'property',
                                                'posts_per_page' => 1,
                                                'p' => $agent_ref,
                                                'meta_query' => array(
                                                    'relation' => 'AND',
                                                    array(
                                                        'key' => '_zoopla_realtime_portal_' . $portal_id,
                                                        'value' => 'yes'
                                                    ),
                                                    array(
                                                        'key' => '_on_market',
                                                        'value' => 'yes'
                                                    )
                                                )
                                            );
                                            $property_query = new WP_Query( $args );
                                            if ( is_numeric($agent_ref) && $property_query->have_posts() )
                                            {
                                                // Don't do anything, we found this property
                                            }
                                            else
                                            {
                                                $ok_to_remove = true;
                                            }
                                        }
                                    }

                                    if ($ok_to_remove)
                                    {
                                        // Hmm.. This property was on the portal but not an active, on market property in Property Hive
                                        // Let's remove it.
                                        $request_data = array();

                                        $request_data['listing_reference'] = $branch_code . '_' . $agent_ref;
                                        //$request_data['deletion_reason'] = '';

                                        $request_data = apply_filters( 'ph_zoopla_rtdf_remove_request_data', $request_data );
                                        
                                        $this->do_curl_request( $portal, $portal['remove_property_api_url'], 'http://realtime-listings.webservices.zpg.co.uk/docs/v1.2/schemas/listing/delete.json', $request_data, '' );
                                    }
                                    wp_reset_postdata();

                                } // end foreach property

                            } // end if properties set
                        }

                    } // end foreach sales branch codes

                } // end if sales branch codes not empty

                $lettings_related_custom_departments = array();
                foreach ( $custom_departments as $key => $custom_department )
                {
                    if ( $custom_department['based_on'] == 'residential-lettings' )
                    {
                        $lettings_related_custom_departments[] = $key;
                    }
                }

                // Get array of lettings branch codes we need to check for reconcilliation
                $branch_codes = $portal['branch_codes'];
                $new_branch_codes = array();
                foreach ($branch_codes as $id => $branch_code)
                {
                    if ( trim($branch_code) != '' )
                    {
                        if ( substr($id, -9) == '_lettings' )
                        {
                            $new_branch_codes[] = $branch_code;
                        }
                        elseif ( !empty($lettings_related_custom_departments) )
                        {
                            foreach ( $lettings_related_custom_departments as $lettings_related_custom_department )
                            {
                                if ( strpos($id, '_' . $lettings_related_custom_department) !== false )
                                {
                                    $new_branch_codes[] = $branch_code;
                                }
                            }
                        }
                    }
                }
                $branch_codes = array_unique($new_branch_codes);

                if (
                    !empty($branch_codes) &&
                    (
                        get_option( 'propertyhive_active_departments_lettings' ) == 'yes' ||
                        get_option( 'propertyhive_active_departments_commercial' ) == 'yes'  ||
                        !empty($lettings_related_custom_departments)
                    )
                )
                {
                    foreach ( $branch_codes as $branch_code )
                    {
                        // Make request to get lettings branch properties
                        $request_data = array();

                        // Network
                        $request_data['branch_reference'] = $branch_code;

                        $response = $this->do_curl_request( $portal, $portal['get_branch_properties_api_url'], 'http://realtime-listings.webservices.zpg.co.uk/docs/v1.2/schemas/listing/list.json', $request_data, '', false );

                        if ($response !== FALSE) 
                        {
                            if (isset($response['property']) && is_array($response['property']) && !empty($response['property']))
                            {
                                foreach ($response['property'] as $property)
                                {
                                    $agent_ref = str_replace($branch_code . '_', "", $property['listing_reference']);

                                    $ok_to_remove = false;
                                    if ( isset($portal['unique_property_id']) && $portal['unique_property_id'] == 'ref' )
                                    {
                                        // find property with this agent ref
                                        $args = array(
                                            'post_type' => 'property',
                                            'posts_per_page' => 1,
                                            'meta_query' => array(
                                                array(
                                                    'key' => '_reference_number',
                                                    'value' => $agent_ref,
                                                    'compare' => '='
                                                ),
                                                array(
                                                    'key' => '_zoopla_realtime_portal_' . $portal_id,
                                                    'value' => 'yes'
                                                ),
                                                array(
                                                    'key' => '_on_market',
                                                    'value' => 'yes'
                                                )
                                            )
                                        );

                                        $property_query = new WP_Query($args);
                                        
                                        if ( $property_query->have_posts() )
                                        {

                                        }
                                        else
                                        {
                                            $ok_to_remove = true;
                                        }
                                            
                                    }
                                    else
                                    {
                                        if (!is_numeric($agent_ref))
                                        {
                                            // The agent agent ref we've sent should always be a post ID and therefore an integer
                                            $ok_to_remove = true;
                                        }
                                        else
                                        {
                                            // Check if this agent ref is active, on market and selected to be sent to the portal
                                            $args = array(
                                                'post_type' => 'property',
                                                'posts_per_page' => 1,
                                                'p' => $agent_ref,
                                                'meta_query' => array(
                                                    'relation' => 'AND',
                                                    array(
                                                        'key' => '_zoopla_realtime_portal_' . $portal_id,
                                                        'value' => 'yes'
                                                    ),
                                                    array(
                                                        'key' => '_on_market',
                                                        'value' => 'yes'
                                                    )
                                                )
                                            );
                                            $property_query = new WP_Query( $args );
                                            if ( is_numeric($agent_ref) && $property_query->have_posts() )
                                            {
                                                // Don't do anything, we found this property
                                            }
                                            else
                                            {
                                                $ok_to_remove = true;
                                            }
                                        }
                                    }

                                    if ($ok_to_remove)
                                    {
                                        // Hmm.. This property was on the portal but not an active, on market property in Property Hive
                                        // Let's remove it.
                                        $request_data = array();

                                        $request_data['listing_reference'] = $branch_code . '_' . $agent_ref;
                                        //$request_data['deletion_reason'] = '';
                                        
                                        $request_data = apply_filters( 'ph_zoopla_rtdf_remove_request_data', $request_data );
                                        
                                        $this->do_curl_request( $portal, $portal['remove_property_api_url'], 'http://realtime-listings.webservices.zpg.co.uk/docs/v1.2/schemas/listing/delete.json', $request_data, '' );
                                    }
                                    wp_reset_postdata();

                                } // end foreach property

                            } // end if properties set

                        }

                    } // end foreach lettings branch codes

                } // end if lettings branch codes not empty

            } // end foreach portal

        } // end if portals not empty
    }

    public function send_realtime_feed_request( $post_id ) 
    {
        global $wpdb;

        if ( $post_id == null )
            return;

        if ( get_post_type($post_id) != 'property' )  
            return; 

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
          return;

        // If this is just a revision, don't make request.
        if ( wp_is_post_revision( $post_id ) )
            return;

        if ( get_post_status( $post_id ) == 'auto-draft' )
            return;

        $wpdb->query( "DELETE FROM " . $wpdb->prefix . "ph_zooplarealtimefeed_logs_error WHERE error_date < DATE_SUB(NOW(), INTERVAL 7 DAY)" );

        global $post;  

        if ( empty( $post ) )
            $post = get_post($post_id);

        $property = new PH_Property( $post_id );

        $portals = array();
        $current_realtime_feed_options = get_option( 'propertyhive_zooplarealtimefeed' );
            
        if ($current_realtime_feed_options !== FALSE)
        {
            if (isset($current_realtime_feed_options['portals']))
            {
                $portals = $current_realtime_feed_options['portals'];
            }
        }
        if (!empty($portals))
        {
            foreach ($portals as $portal_id => $portal)
            {
                if ( $portal['mode'] != 'live' ) { continue; } // Only continue past this point if the portal is live

                $portal['portal_id'] = $portal_id; // Add to array so easier to pass around

                $property_send_request_send = false;

                // Get property
                $args = array(
                    'post_type' => 'property',
                    'nopaging' => true,
                    'p' => $post_id,
                    'post_status' => 'publish',
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key' => '_zoopla_realtime_portal_' . $portal_id,
                            'value' => 'yes'
                        ),
                        array(
                            'key' => '_on_market',
                            'value' => 'yes'
                        )
                    )
                );
                $property_query = new WP_Query( apply_filters( 'ph_zoopla_rtdf_send_query_args', $args ) );

                if ($property_query->have_posts())
                {
                    while ($property_query->have_posts())
                    {
                        $property_query->the_post();

                        $property_send_request_send = true;

                        // Work out if the office has changed since the last time we sent this property and do a remove request first if so
                        $previous_sent_office_id = get_post_meta( $post->ID, '_zoopla_realtime_previous_office_id', TRUE );
                        $current_office_id = $property->_office_id;

                        $ok_to_send = true;
                        if ( $previous_sent_office_id != '' && $previous_sent_office_id != $current_office_id )
                        {
                            // Office has changed. Need to send a remove request first
                            $success = $this->create_remove_property_request( $portal, $post, $property, $previous_sent_office_id );

                            if ($success === FALSE)
                            {
                                add_filter( 'redirect_post_location', array( $this, 'add_notice_query_var' ), 99, 2 );
                                $ok_to_send = false;
                            }

                        }

                        // Check this property doesn't have exclusivity to another portal
                        $exclusivity_portal_id = get_post_meta( $post->ID, '_exclusivity_portal_id', TRUE );
                        if ( $exclusivity_portal_id != '' && $exclusivity_portal_id != $portal_id  )
                        {
                            // This property does have exclusivity to another portal. Only send if exclusivity expiry has passed
                            $exclusivity_expires = get_post_meta( $post->ID, '_exclusivity_expires', TRUE );
                            if ( $exclusivity_expires != '' && strtotime($exclusivity_expires) > time() )
                            {
                                $ok_to_send = false;
                            }
                        }

                        if ( $ok_to_send )
                        {
                            $success = $this->create_send_property_request( $portal, $post, $property );

                            if ($success === FALSE)
                            {
                                add_filter( 'redirect_post_location', array( $this, 'add_notice_query_var' ), 99, 2 );
                            }
                            else
                            {
                                if ( $previous_sent_office_id != $current_office_id )
                                {
                                    update_post_meta( $post->ID, '_zoopla_realtime_previous_office_id', $current_office_id );
                                }
                            }
                        }
                    }
                }

                wp_reset_postdata();

                if ( !$property_send_request_send )
                {
                    // send request not sent. Must need to remove it
                    $success = $this->create_remove_property_request( $portal, $post, $property );

                    if ($success === FALSE)
                    {
                        add_filter( 'redirect_post_location', array( $this, 'add_notice_query_var' ), 99, 2 );
                    }
                }
            }
        }
    }

    public function add_notice_query_var( $location, $post_id ) 
    {
        global $wpdb;

        $log_id = '';
        $error = '';

        $query = "
            SELECT 
                id,
                message
            FROM 
                " . $wpdb->prefix . "ph_zooplarealtimefeed_logs_error
            WHERE
                severity = '1'
            AND
                post_id = '" . $post_id . "'
            ORDER BY 
                error_date DESC
            LIMIT 1
        ";
        $error_results = $wpdb->get_results($query);
        $errors = array();
        if ( $error_results )
        {
            foreach ($error_results as $error_result)
            {
                $log_id = $error_result->id;
                $error = $error_result->message;
            }
        }

        remove_filter( 'redirect_post_location', array( $this, 'add_notice_query_var' ), 99 );
        return add_query_arg( array( 'zooplarealtime_failed' => '1', 'zooplarealtime_error' => ( ( $error != '' ) ? urlencode(base64_encode($error)) : '' ), 'zooplarealtime_log_id' => $log_id ), $location );
    }

    public function create_send_property_request( $portal, $post, $property )
    {
        $custom_departments = ph_get_custom_departments();
        $original_department = get_post_meta($post->ID, '_department', true);
        $departments = array();

        if ( $original_department == 'commercial' || ph_get_custom_department_based_on( $original_department ) == 'commercial' )
        {
            $departments = array();
            if ( get_post_meta($post->ID, '_for_sale', true) == 'yes' )
            {
                $departments[] = 'sales';
            }
            if ( get_post_meta($post->ID, '_to_rent', true) == 'yes' )
            {
                $departments[] = 'lettings';
            }
        }
        else
        {
            $departments[] = str_replace("residential-", '', $original_department);
        }

        if ( $departments )
        {
            foreach ( $departments as $department )
            {
                $overseas = false;
                $country = get_post_meta($post->ID, '_address_country', true);
                if ( isset($portal['overseas']) && $portal['overseas'] == '1' )
                {
                    // Portal has been marked as overseas

                    if ( $country == '' || $country == 'GB' )
                    {
                        // We don't want to send a GB property to an overseas feed. Let's just get outta here...
                        return true;
                    }
                    else
                    {
                        $overseas = true;
                    }
                }
                else
                {
                    // Portal has not been marked as overseas. Assume it's UK resi feed
                    if ( $country != '' && $country != 'GB' )
                    {
                        // We don't want to send a non-GB property to a UK resi feed. Let's just get outta here...
                        return true;
                    }
                }

                // Only send sales properties to overseas portals
                if ( $overseas && $department != 'sales' && ph_get_custom_department_based_on( $original_department ) != 'residential-sales' )
                {
                    $this->log_error($portal['portal_id'], 1, "Only sales properties can be sent to overseas portals", '', '', $post->ID);

                    return false;
                }

                $branch_codes = $portal['branch_codes'];

                $branch_code = ( 
                    isset( $branch_codes['branch_code_' . get_post_meta($post->ID, '_office_id', true) . '_' . $department] ) ?
                    $branch_codes['branch_code_' . get_post_meta($post->ID, '_office_id', true) . '_' . $department] : 
                    '' 
                );

                if ( array_key_exists($original_department, $custom_departments) )
                {
                    $branch_code = (
                        isset( $branch_codes['branch_code_' . get_post_meta($post->ID, '_office_id', true) . '_' . $original_department] ) ?
                        $branch_codes['branch_code_' . get_post_meta($post->ID, '_office_id', true) . '_' . $original_department] :
                        ''
                    );
                }

                $request_data = array();

                $fees = '';
                if ( ($original_department == 'residential-lettings' || ph_get_custom_department_based_on( $original_department ) == 'residential-lettings') && get_option('propertyhive_lettings_fees', '') != '' )
                {
                    $fees = substr(strip_tags(get_option('propertyhive_lettings_fees', '')), 0, 4000);
                }
                if ( ($original_department == 'commercial' || ph_get_custom_department_based_on( $original_department ) == 'commercial') && get_option('propertyhive_lettings_fees_commercial', '') != '' )
                {
                    $fees = substr(strip_tags(get_option('propertyhive_lettings_fees_commercial', '')), 0, 4000);
                }
                if ( $fees != '' ) { $request_data['administration_fees'] = $fees; }
                if ( $property->available_date != '' ) { $request_data['available_from_date'] = $property->available_date; }
                $request_data['bathrooms'] = (int)$property->bathrooms;
                $request_data['branch_reference'] = $branch_code;
                $request_data['category'] = ( ( $original_department == 'commercial' || ph_get_custom_department_based_on( $original_department ) == 'commercial' ) ? 'commercial' : 'residential' );
                if ( $property->deposit != '' ) { $request_data['deposit'] = floatval($property->deposit); }
                $request_data['detailed_description'] = array(
                    array(
                        'text' => trim( ( strip_tags($property->get_formatted_description()) != '' ) ? $property->get_formatted_description() : get_the_excerpt() )
                    )
                );
                $request_data['display_address'] = get_the_title($post->ID);
                $features = $property->get_features();
                if ( !empty($features) ) { $request_data['feature_list'] = $features; }
                if ( ($department == 'lettings' || ph_get_custom_department_based_on( $original_department ) == 'residential-lettings') && $this->get_mapped_value($post->ID, 'furnished') != '' ) { $request_data['furnished_state'] = $this->get_mapped_value($post->ID, 'furnished'); }
                $request_data['life_cycle_status'] = $this->get_mapped_value($post->ID, ( $overseas ? 'overseas_' : '' ) . 'availability');
                $request_data['listing_reference'] = $branch_code . '_' . ( ( isset($portal['unique_property_id']) && $portal['unique_property_id'] == 'ref' ) ? get_post_meta($post->ID, '_reference_number', true) : $post->ID );
                $request_data['living_rooms'] = (int)$property->reception_rooms;
                $request_data['location'] = array(
                    'country_code' => ( ( $property->address_country != '' ) ? $property->address_country : 'GB' ),
                );
                if ( $property->address_name_number != '' ) { $request_data['location']['property_number_or_name'] = $property->address_name_number; }
                if ( $property->address_street != '' ) { $request_data['location']['street_name'] = $property->address_street; }
                if ( $property->address_two != '' ) { $request_data['location']['locality'] = $property->address_two; }
                $town = '';
                if ( $property->address_three != '' ) 
                { 
                    $town = $property->address_three; 
                }
                elseif ( $property->address_two )
                { 
                    $town = $property->address_two; 
                }
                elseif ( $property->address_four )
                { 
                    $town = $property->address_four; 
                }
                if ( $town != '' ) { $request_data['location']['town_or_city'] = $town; }
                if ( $property->address_four != '' ) { $request_data['location']['county'] = $property->address_four; }
                if ( $property->address_postcode != '' ) { $request_data['location']['postal_code'] = strtoupper($property->address_postcode); }
                if ( floatval($property->latitude) != '' && floatval($property->longitude) != '' )
                {
                    $request_data['location']['coordinates'] = array();
                    if ( floatval($property->latitude) != '' ) { $request_data['location']['coordinates']['latitude'] = floatval($property->latitude); }
                    if ( floatval($property->longitude) != '' ) { $request_data['location']['coordinates']['longitude'] = floatval($property->longitude); }
                }
                if ( $this->get_mapped_value($post->ID, 'outside_space') != '' ) { $request_data['outside_space'] = array( $this->get_mapped_value($post->ID, 'outside_space') ); }
                if ( $this->get_mapped_value($post->ID, 'parking') != '' ) { $request_data['parking'] = array( $this->get_mapped_value($post->ID, 'parking') ); }

                $rent_frequency = '';
                if ($original_department == 'residential-lettings' || ph_get_custom_department_based_on( $original_department ) == 'residential-lettings')
                {
                    $rent_frequency = 'per_month';
                    switch ( $property->_rent_frequency )
                    {
                        case "pppw": { $rent_frequency = 'per_person_per_week'; break; }
                        case "pw": { $rent_frequency = 'per_week'; break; }
                        case "pcm": { $rent_frequency = 'per_month'; break; }
                        case "pq": { $rent_frequency = 'per_quarter'; break; }
                        case "pa": { $rent_frequency = 'per_year'; break; }
                    }
                }
                elseif ($original_department == 'commercial' || ph_get_custom_department_based_on( $original_department ) == 'commercial')
                {
                    if ( $department == 'lettings' )
                    {
                        $rent_frequency = 'per_month';
                        switch ( $property->_rent_units )
                        {
                            case "pw": { $rent_frequency = 'per_week'; break; }
                            case "pcm": { $rent_frequency = 'per_month'; break; }
                            case "pq": { $rent_frequency = 'per_quarter'; break; }
                            case "pa": { $rent_frequency = 'per_year'; break; }
                        }
                    }
                }

                $price = ( ( $original_department == "residential-sales" || ph_get_custom_department_based_on( $original_department ) == 'residential-sales' ) ? $property->price : $property->rent );
                $price_qualifier = $this->get_mapped_value($post->ID, 'price_qualifier');
                if ( $original_department == 'commercial' || ph_get_custom_department_based_on( $original_department ) == 'commercial' )
                {
                    $price = '';
                    if ( $department == 'sales' )
                    {
                        $price_from = get_post_meta($post->ID, '_price_from', true);
                        $price_to = get_post_meta($post->ID, '_price_to', true);

                        if ( $price_to != '' && $price_to != '0' && $price_from != '' && $price_from != '0' && $price_to != $price_from )
                        {
                            $price = $price_from;
                            $price_qualifier = 'from';
                        }
                        elseif ( $price_to != '' && $price_to != '0' )
                        {
                            $price = $price_to;
                        }
                        elseif ( $price_from != '' && $price_from != '0' )
                        {
                            $price = $price_from;
                        }

                        $poa = get_post_meta($post->ID, '_price_poa', true);
                        if ($poa == 'yes')
                        {
                            $price_qualifier = 'price_on_application';
                        }
                    }
                    if ( $department == 'lettings' )
                    {
                        $rent_from = get_post_meta($post->ID, '_rent_from', true);
                        $rent_to = get_post_meta($post->ID, '_rent_to', true);

                        if ( $rent_to != '' && $rent_to != '0' && $rent_from != '' && $rent_from != '0' && $rent_to != $rent_from )
                        {
                            $price = $rent_from;
                            $price_qualifier = 'from';
                        }
                        elseif ( $rent_to != '' && $rent_to != '0' )
                        {
                            $price = $rent_to;
                        }
                        elseif ( $rent_from != '' && $rent_from != '0' )
                        {
                            $price = $rent_from;
                        }

                        $poa = get_post_meta($post->ID, '_rent_poa', true);
                        if ($poa == 'yes')
                        {
                            $price_qualifier = 'price_on_application';
                        }
                    }
                }
                else
                {
                    $poa = get_post_meta($post->ID, '_poa', true);
                    if ($poa == 'yes')
                    {
                        $price_qualifier = 'price_on_application';
                    }
                }
                $request_data['pricing'] = array(
                    'transaction_type' => ( ($department == "lettings" || ph_get_custom_department_based_on( $original_department ) == 'residential-lettings') ? 'rent' : 'sale' ),
                    'currency_code' => ( $property->currency != "" ? $property->currency : 'GBP' ),
                    'price' => (int)$price,
                );
                if ( $rent_frequency != '' ) { $request_data['pricing']['rent_frequency'] = $rent_frequency; }
                if ( $price_qualifier != '' ) { $request_data['pricing']['price_qualifier'] = $price_qualifier; }

                $area_set = false;
                if ( ($original_department == 'commercial' || ph_get_custom_department_based_on( $original_department ) == 'commercial') && $department == 'lettings' )
                {
                    if ( 
                        in_array($property->_rent_units, array('psqft', 'psqm', 'pacre', 'phectare')) 
                        &&
                        (
                            $property->_floor_area_from != '' ||
                            $property->_floor_area_to != ''
                        )
                    )
                    {
                        if ( isset($request_data['pricing']['price']) ) { unset($request_data['pricing']['price']); }

                        $price = 0;

                        $rent_from = get_post_meta($post->ID, '_rent_from', true);
                        $rent_to = get_post_meta($post->ID, '_rent_to', true);

                        if ( $rent_to != '' && $rent_to != '0' )
                        {
                            $price = $rent_to;
                        }
                        elseif ( $rent_from != '' && $rent_from != '0' )
                        {
                            $price = $rent_from;
                        }

                        $units = 'sq_feet';
                        switch ( $property->_rent_units )
                        {
                            case "psqm": { $units = 'sq_metres'; break; }
                            case "pacre": { $units = 'acres'; break; }
                            case "phectare": { $units = 'hectares'; break; }
                        }
                        $request_data['pricing']['price_per_unit_area'] = array(
                            'price' => floatval($price),
                            'units' => $units,
                        );

                        $request_data['pricing']['rent_frequency'] = 'per_year';

                        $units = 'sq_feet';
                        switch ( $property->_floor_area_units )
                        {
                            case "sqm": { $units = 'sq_metres'; break; }
                            case "acre": { $units = 'acres'; break; }
                            case "hectare": { $units = 'hectares'; break; }
                        }

                        $internal = array();
                        if ( $property->_floor_area_from != '' )
                        {
                            $internal['minimum'] = array(
                                'value' => floatval($property->_floor_area_from),
                                'units' => $units,
                            );
                        }
                        if ( $property->_floor_area_to != '' )
                        {
                            $internal['maximum'] = array(
                                'value' => floatval($property->_floor_area_to),
                                'units' => $units,
                            );
                        }
                        $request_data['areas'] = array(
                            'internal' => $internal
                        );

                        $area_set = true;
                    }
                }
                if ( ($original_department == 'commercial' || ph_get_custom_department_based_on( $original_department ) == 'commercial') && $department == 'sales' )
                {
                    if ( 
                        in_array($property->_price_units, array('psqft', 'psqm', 'pacre', 'phectare')) 
                        &&
                        (
                            $property->_floor_area_from != '' ||
                            $property->_floor_area_to != ''
                        )
                    )
                    {
                        if ( isset($request_data['pricing']['price']) ) { unset($request_data['pricing']['price']); }

                        $price = 0;

                        $price_from = get_post_meta($post->ID, '_price_from', true);
                        $price_to = get_post_meta($post->ID, '_price_to', true);

                        if ( $price_to != '' && $price_to != '0' )
                        {
                            $price = $price_to;
                        }
                        elseif ( $price_from != '' && $price_from != '0' )
                        {
                            $price = $price_from;
                        }

                        $units = 'sq_feet';
                        switch ( $property->_price_units )
                        {
                            case "psqm": { $units = 'sq_metres'; break; }
                            case "pacre": { $units = 'acres'; break; }
                            case "phectare": { $units = 'hectares'; break; }
                        }
                        $request_data['pricing']['price_per_unit_area'] = array(
                            'price' => floatval($price),
                            'units' => $units,
                        );

                        $units = 'sq_feet';
                        switch ( $property->_floor_area_units )
                        {
                            case "sqm": { $units = 'sq_metres'; break; }
                            case "acre": { $units = 'acres'; break; }
                            case "hectare": { $units = 'hectares'; break; }
                        }

                        $internal = array();
                        if ( $property->_floor_area_from != '' )
                        {
                            $internal['minimum'] = array(
                                'value' => floatval($property->_floor_area_from),
                                'units' => $units,
                            );
                        }
                        if ( $property->_floor_area_to != '' )
                        {
                            $internal['maximum'] = array(
                                'value' => floatval($property->_floor_area_to),
                                'units' => $units,
                            );
                        }
                        $request_data['areas'] = array(
                            'internal' => $internal
                        );

                        $area_set = true;
                    }
                }
                if ( ($original_department == 'commercial' || ph_get_custom_department_based_on( $original_department ) == 'commercial') && !$area_set )
                {
                    if ( 
                        $property->_floor_area_from != '' ||
                        $property->_floor_area_to != ''
                    )
                    {
                        $units = 'sq_feet';
                        switch ( $property->_floor_area_units )
                        {
                            case "sqm": { $units = 'sq_metres'; break; }
                            case "acre": { $units = 'acres'; break; }
                            case "hectare": { $units = 'hectares'; break; }
                        }

                        $internal = array();
                        if ( $property->_floor_area_from != '' )
                        {
                            $internal['minimum'] = array(
                                'value' => floatval($property->_floor_area_from),
                                'units' => $units,
                            );
                        }
                        if ( $property->_floor_area_to != '' )
                        {
                            $internal['maximum'] = array(
                                'value' => floatval($property->_floor_area_to),
                                'units' => $units,
                            );
                        }
                        $request_data['areas'] = array(
                            'internal' => $internal
                        );
                    }
                }

                $property_type = $this->get_mapped_value($post->ID, ( ( $original_department == 'commercial' || ph_get_custom_department_based_on( $original_department ) == 'commercial' ) ? 'commercial_' : '' ) . 'property_type');
                if ( $property_type != '' ) { $request_data['property_type'] = $property_type; }
                $request_data['summary_description'] = trim(get_the_excerpt());
                $tenure = ( ($department == "sales" || ph_get_custom_department_based_on( $original_department ) == 'residential-sales') ? $this->get_mapped_value($post->ID, ( ( $original_department == 'commercial' || ph_get_custom_department_based_on( $original_department ) == 'commercial' ) ? 'commercial_' : '' ) . 'tenure') : '' );
                if ( $tenure != '' ) { $request_data['tenure'] = $tenure; }
                $request_data['total_bedrooms'] = (int)$property->bedrooms;

                $request_data['content'] = array();
                 
                // IMAGES
                if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
                {
                    $photo_urls = $property->_photo_urls;
                    if ( !is_array($photo_urls) ) { $photo_urls = array(); }

                    foreach ( $photo_urls as $photo )
                    {
                        $media = array(
                            'url' => isset($photo['url']) ? $photo['url'] : '',
                            'type' => 'image',
                        );
                        if ( isset( $photo['title'] ) && $photo['title'] != '' )
                        {
                            $media['caption'] = $photo['title'];
                        }

                        $request_data['content'][] = $media;
                    }
                }
                else
                {
                    $attachment_ids = $property->get_gallery_attachment_ids();
                    foreach ($attachment_ids as $attachment_id)
                    {
                        $url = wp_get_attachment_image_src( $attachment_id, 'large' );
                        if ($url !== FALSE)
                        {
                            $attachment_data = wp_prepare_attachment_for_js( $attachment_id );

                            $media = array(
                                'url' => $url[0],
                                'type' => 'image',
                            );
                            if ( isset( $attachment_data['alt'] ) && $attachment_data['alt'] != '' )
                            {
                                $media['caption'] = $attachment_data['alt'];
                            }

                            $request_data['content'][] = $media;
                        }
                    }
                }

                // FLOORPLANS
                if ( get_option('propertyhive_floorplans_stored_as', '') == 'urls' )
                {
                    $floorplan_urls = $property->_floorplan_urls;
                    if ( is_array($floorplan_urls) && !empty( $floorplan_urls ) )
                    {
                        foreach ($floorplan_urls as $floorplan)
                        {
                            $media = array(
                                'url' => isset($floorplan['url']) ? $floorplan['url'] : '',
                                'type' => 'floor_plan',
                                'caption' => ( isset($floorplan['title']) && trim($floorplan['title']) != '' ) ? $floorplan['title'] : 'Floorplan',
                            );

                            $request_data['content'][] = $media;
                        }
                    }
                }
                else
                {
                    $attachment_ids = $property->get_floorplan_attachment_ids();
                    foreach ($attachment_ids as $attachment_id)
                    {
                        // Get large version of attachment image
                        $url = wp_get_attachment_image_src( $attachment_id, 'large' );
                        if ($url === FALSE)
                        {
                            // No image available so check if floorplan attachment is valid PDF
                            $attachment_url = wp_get_attachment_url($attachment_id);
                            if ($attachment_url !== FALSE)
                            {
                                $file_type = wp_check_filetype($attachment_url);
                                if ( $file_type['type'] === 'application/pdf' )
                                {
                                    $url = array( $attachment_url );
                                    $pdf_floorplan = true;
                                }
                            }
                        }

                        // We've found a valid image or PDF floorplan
                        if ($url !== FALSE)
                        {
                            // For the caption, use the Alt field for images and Title for PDFs
                            $caption_field = !isset( $pdf_floorplan ) ? 'alt' : 'title';
                            $attachment_data = wp_prepare_attachment_for_js( $attachment_id );

                            $media = array(
                                'url' => $url[0],
                                'type' => 'floor_plan',
                            );
                            if ( isset( $attachment_data[$caption_field] ) && $attachment_data[$caption_field] != '' )
                            {
                                $media['caption'] = $attachment_data[$caption_field];
                            }

                            $request_data['content'][] = $media;
                        }
                    }
                }

                // BROCHURES
                if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
                {
                    $brochure_urls = $property->_brochure_urls;
                    if ( is_array($brochure_urls) && !empty( $brochure_urls ) )
                    {
                        foreach ($brochure_urls as $brochure)
                        {
                            $media = array(
                                'url' => isset($brochure['url']) ? $brochure['url'] : '',
                                'type' => 'brochure',
                                'caption' => ( isset($brochure['title']) && trim($brochure['title']) != '' ) ? $brochure['title'] : 'Brochure',
                            );

                            $request_data['content'][] = $media;
                        }
                    }
                }
                else
                {
                    $attachment_ids = $property->get_brochure_attachment_ids();
                    foreach ($attachment_ids as $attachment_id)
                    {
                        $url = wp_get_attachment_url( $attachment_id );
                        if ($url !== FALSE)
                        {
                            $attachment_data = wp_prepare_attachment_for_js( $attachment_id );

                            $media = array(
                                'url' => $url,
                                'type' => 'brochure',
                            );
                            if ( isset( $attachment_data['alt'] ) && $attachment_data['alt'] != '' )
                            {
                                $media['caption'] = $attachment_data['alt'];
                            }

                            $request_data['content'][] = $media;
                        }
                    }
                }

                // EPCS
                if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
                {
                    $epc_urls = $property->_epc_urls;
                    if ( is_array($epc_urls) && !empty( $epc_urls ) )
                    {
                        foreach ($epc_urls as $epc)
                        {
                            $media = array(
                                'url' => isset($epc['url']) ? $epc['url'] : '',
                                'type' => ( strpos($epc['url'], '.pdf') === FALSE ? 'epc_graph' : 'epc_report' ),
                                'caption' => ( isset($epc['title']) && trim($epc['title']) != '' ) ? $epc['title'] : 'EPC',
                            );

                            $request_data['content'][] = $media;
                        }
                    }
                }
                else
                {
                    $attachment_ids = $property->get_epc_attachment_ids();
                    foreach ($attachment_ids as $attachment_id)
                    {
                        $url = wp_get_attachment_url( $attachment_id );
                        if ($url !== FALSE)
                        {
                            $attachment_data = wp_prepare_attachment_for_js( $attachment_id );

                            $media = array(
                                'url' => $url,
                                'type' => ( strpos($url, '.pdf') === FALSE ? 'epc_graph' : 'epc_report' ),
                            );
                            if ( isset( $attachment_data['alt'] ) && $attachment_data['alt'] != '' )
                            {
                                $media['caption'] = $attachment_data['alt'];
                            }

                            $request_data['content'][] = $media;
                        }
                    }
                }

                // VIRTUAL TOURS
                $virtual_tour_urls = $property->get_virtual_tour_urls();
                if ( !empty($virtual_tour_urls) )
                {
                    foreach ($virtual_tour_urls as $url)
                    {
                        if ( trim($url) != '' )
                        {
                            $media = array(
                                'url' => $url,
                                'type' => 'virtual_tour',
                                'caption' => 'Virtual Tour',
                            );

                            $request_data['content'][] = $media;
                        }
                    }
                }

                $request_data = apply_filters( 'ph_zoopla_rtdf_send_request_data', $request_data, $post->ID );

                array_walk_recursive( $request_data, array($this, 'replace_bad_characters' ) );

                $do_request = true;
                if ( isset($portal['only_send_if_different']) && $portal['only_send_if_different'] == '1' )
                {
                    $previous_hash = get_post_meta( $post->ID, '_zoopla_realtime_sha1_' . $portal['portal_id'] . '_' . $department, TRUE );

                    $request_data_to_check = $request_data;

                    if ( $previous_hash == sha1(json_encode($request_data_to_check)) )
                    {
                        // Matches the data sent last time. Don't send again
                        $do_request = false;
                    }
                }

                if ( $do_request )
                {
                    $request = $this->do_curl_request( $portal, $portal['send_property_api_url'], 'http://realtime-listings.webservices.zpg.co.uk/docs/v1.2/schemas/listing/update.json', $request_data, $post->ID );

                    if ( $request !== FALSE )
                    {
                        $request_data_to_check = $request_data;

                        // Request was successful
                        // Save the SHA-1 hash so we know for next time whether to push it again or not
                        update_post_meta( $post->ID, '_zoopla_realtime_sha1_' . $portal['portal_id'] . '_' . $department, sha1(json_encode($request_data_to_check)) );
                    }
                }
                else
                {
                    $request = true;
                }
            }
        }
        return $request;
    }

    public function create_remove_property_request( $portal, $post, $property, $office_id = '' )
    {
        $branch_codes = $portal['branch_codes'];

        if ( $office_id == '' )
        {
            $office_id = $property->office_id;
        }

        $original_department = $property->department;
        $departments = array();
        if ( $original_department == 'commercial' || ph_get_custom_department_based_on( $original_department ) == 'commercial' )
        {
            $departments = array();
            if ( $property->for_sale == 'yes' )
            {
                $departments[] = 'sales';
            }
            if ( $property->to_rent == 'yes' )
            {
                $departments[] = 'lettings';
            }
        }
        else
        {
            $departments[] = str_replace("residential-", '', $original_department);
        }

        $response = true;

        if ( !empty($departments) )
        {
            foreach ( $departments as $department )
            {
                $branch_code = ( 
                    isset( $branch_codes['branch_code_' . $office_id . '_' . $department] ) ?
                    $branch_codes['branch_code_' . $office_id . '_' . $department] : 
                    '' 
                );

                if ( trim($branch_code) == '' )
                {
                    continue;
                }

                // Should check branch properties before making remove request
                $request_data = array();

                // Network
                $request_data['branch_reference'] = $branch_code;

                $response = $this->do_curl_request( $portal, $portal['get_branch_properties_api_url'], 'http://realtime-listings.webservices.zpg.co.uk/docs/v1.2/schemas/listing/list.json', $request_data, $post->ID, false );

                if ($response === FALSE) { return false; }

                $ok_to_remove = false;
                $agent_ref = $post->ID;
                if (isset($response['listings']) && is_array($response['listings']) && !empty($response['listings']))
                {
                    foreach ($response['listings'] as $property)
                    {
                        if ( isset($portal['unique_property_id']) && $portal['unique_property_id'] == 'ref' )
                        {
                            if ( $property['listing_reference'] == $branch_code . '_' . get_post_meta($post->ID, '_reference_number', true) )
                            {
                                // We found this property to be active on the site
                                $ok_to_remove = true;
                                $agent_ref = get_post_meta($post->ID, '_reference_number', true);
                                break;
                            }  
                        }
                        else
                        {
                            if ( $property['listing_reference'] == $branch_code . '_' . $post->ID )
                            {
                                // We found this property to be active on the site
                                $ok_to_remove = true;
                                break;
                            }
                        }
                    }
                }

                if (!$ok_to_remove) { return true; }

                $request_data = array();

                // Network
                $request_data['listing_reference'] = $branch_code . '_' . $agent_ref;
                //$request_data['deletion_reason'] = '';

                $request_data = apply_filters( 'ph_zoopla_rtdf_remove_request_data', $request_data );

                $do_request = true;
                if ( isset($portal['only_send_if_different']) && $portal['only_send_if_different'] == '1' )
                {
                    $previous_hash = get_post_meta( $post->ID, '_zoopla_realtime_sha1_' . $portal['portal_id'] . '_' . $department, TRUE );

                    $request_data_to_check = $request_data;

                    if ( $previous_hash == sha1(json_encode($request_data_to_check)) )
                    {
                        // Matches the data sent last time. Don't send again
                        $do_request = false;
                    }
                }

                if ( $do_request )
                {
                    $response = $this->do_curl_request( $portal, $portal['remove_property_api_url'], 'http://realtime-listings.webservices.zpg.co.uk/docs/v1.2/schemas/listing/delete.json', $request_data, $post->ID );

                    if ( $response !== FALSE )
                    {
                        $request_data_to_check = $request_data;

                        // Request was successful
                        // Save the SHA-1 hash so we know for next time whether to push it again or not
                        update_post_meta( $post->ID, '_zoopla_realtime_sha1_' . $portal['portal_id'] . '_' . $department, sha1(json_encode($request_data_to_check)) );
                    }
                }
                else
                {
                    $response = true;
                }
            }
        }

        return $response;
    }

    public function do_curl_request( $portal, $api_url, $profile_url, $request_data, $post_id, $log_success = true ) 
    {
        $request_data = json_encode($request_data);

        if ( apply_filters( 'ph_zoopla_rtdf_perform_request', true ) !== true )
        {
            $this->log_error($portal['portal_id'], 1, "Disabling request due to ph_zoopla_rtdf_perform_request filter", $request_data, '', $post_id);
            return false;
        }

        $ch = curl_init();

        $uploads_dir = wp_upload_dir();
                 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request_data);

        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_SSLKEY, $uploads_dir['basedir'] . '/zoopla_realtime/'. $portal['private_key_file']);
        curl_setopt($ch, CURLOPT_SSLCERT, $uploads_dir['basedir'] . '/zoopla_realtime/'. $portal['certificate_file']);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; profile=' . $profile_url, // e.g. http://realtime-listings.webservices.zpg.co.uk/docs/v1.2/schemas/listing/update.json
            'ZPG-Listing-ETag: ' . sha1($request_data) . time(),
        ));

        $output = curl_exec($ch);

        if ( $output === FALSE )
        {
            $this->log_error($portal['portal_id'], 1, "Error sending cURL request: " .curl_errno($ch) . " - " . curl_error($ch), $request_data, '', $post_id);
        
            return false;
        }
        else
        {
            $response = json_decode($output, TRUE);

            if (isset($response['error_name']) && !empty($response['error_name']))
            {
                $this->log_error($portal['portal_id'], 1, "Error returned from " . $portal['name'] . " in response: " . $response['error_name'] . ( ( isset($response['error_advice']) ) ? " - " . $response['error_advice'] : '' ), $request_data, $output, $post_id);
                
                return false;
            }
            else
            {
                if ( $log_success )
                {
                    $this->log_error($portal['portal_id'], 0, "Request successful", $request_data, $output, $post_id);
                }
            }
        }

        return $response;
    }

    public function replace_bad_characters( &$value, $key )
    {
        if ( is_string($value) )
        {
            // Replace bad dash and apostrophe character that breaks JSON
            $value = str_replace( "’", "'", str_replace( '–', '-', $value ));
        }
    }

    /*
     * Logs an error to $wpdb->prefix . ph_zooplarealtimefeed_logs_error table
     * 
     * @param $portal_id (int) - The portalID
     * @param $severity (int) - 0 = Debug / Information Message, 1 = Critical. Caused property not to send, 2 = Warning
     * @param $message (string) - Human-readable message 
     * @param $request (string) - JSON request sent to portal
     * @param $response (string) - JSON response received from API
     * @param $post_id (int) - The WordPress post/property ID
     */
    function log_error($portal_id, $severity, $message, $request = '', $response = '', $post_id = '') 
    {
        global $wpdb;

        $data = array(
            'portal_id' => $portal_id,
            'post_id' => $post_id,
            'severity' => $severity,
            'message' => substr($message, 0, 255),
            'request' => $request,
            'response' => $response,
            'error_date' => date("Y-m-d H:i:s")
        );

        $wpdb->insert( 
            $wpdb->prefix . "ph_zooplarealtimefeed_logs_error", 
            $data
        );
    }
    
}

endif;

/**
 * Returns the main instance of PH_Zooplarealtimefeed to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return PH_Zooplarealtimefeed
 */
function PHZRTF() {
    return PH_Zooplarealtimefeed::instance();
}

$PHZRTF = PHZRTF();

if( is_admin() && file_exists(  dirname( __FILE__ ) . '/propertyhive-zoopla-real-time-feed-update.php' ) )
{
    include_once( dirname( __FILE__ ) . '/propertyhive-zoopla-real-time-feed-update.php' );
}