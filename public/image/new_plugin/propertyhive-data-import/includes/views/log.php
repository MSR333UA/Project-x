<div class="wrap propertyhive">

    <h1><?php _e('Import Log', 'propertyhive'); ?></h1>

    <pre style="overflow:auto; max-height:450px; background:#FFF; border-top:1px solid #CCC; border-bottom:1px solid #CCC"><?php
        foreach ( $log as $entry )
        {
            echo ( ( isset($entry['post_id']) && $entry['post_id'] != '' ) ? $entry['post_id'] . ' - ' : '' ) . $entry['message'] . "\n";
        }
    ?></pre>

</div>