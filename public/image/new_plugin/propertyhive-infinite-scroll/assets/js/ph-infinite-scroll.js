var ph_loading_properties = false;
var ph_reinstating_position = false;
var ph_current_page = 1;
var ph_total_posts = ph_is_ajax_object.total_posts;
var ph_posts_per_page = ph_is_ajax_object.posts_per_page;
var ph_document_is_ready = false; // used to ensure scroll event desn't fire before document is ready

jQuery(document).ready(function()
{
    // Add listener to property URLs
    jQuery('.properties').on('click', 'a', function()
    {
        sessionStorage.setItem('ph_current_page', ph_current_page);
        sessionStorage.setItem('ph_scroll_pos', jQuery(window).scrollTop());
    });
});

jQuery(window).on('load', function()
{
    if ( sessionStorage.getItem('ph_current_page') != '' && sessionStorage.getItem('ph_current_page') > 1 )
    {
        // We came from results that had been scrolled
        var target_ph_current_page = sessionStorage.getItem('ph_current_page');
        scroll_pos = sessionStorage.getItem('ph_scroll_pos');

        sessionStorage.removeItem('ph_current_page');
        sessionStorage.removeItem('ph_scroll_pos');

        sessionStorage.clear();

        ph_reinstating_position = true;

        var deferreds = [];

        // Need to reinstate results from where we left off
        ph_grab_properties(2, target_ph_current_page, scroll_pos);
        ph_current_page = target_ph_current_page;
    }

    jQuery('.ph-infinite-scroll-button a').click(function(e)
    {
        e.preventDefault();

        jQuery(this).attr('disabled', 'disabled');

        ph_current_page = ph_current_page + 1;
        ph_grab_properties( ph_current_page );
    });

    ph_document_is_ready = true;
});

jQuery(window).scroll(function()
{
    if ( ph_is_ajax_object.functionality != 'button' && ph_document_is_ready && !ph_reinstating_position && !ph_loading_properties && (ph_current_page * ph_posts_per_page) < ph_total_posts )
    {
        var window_top = jQuery(window).scrollTop();
        var window_height = jQuery(window).height();

        // Check if bottom of properties is in viewport
        if ( (jQuery('.properties').offset().top + jQuery('.properties').outerHeight(true)) < (window_top + window_height) )
        {
            ph_current_page = ph_current_page + 1;
            ph_grab_properties(ph_current_page);
        }
    }
    else
    {
        // No more to load or already doing a load of some kind
    }
});

function ph_grab_properties( page, reinstating_to_page, scroll_pos )
{
    if ( ph_reinstating_position || (!ph_reinstating_position && !ph_loading_properties) )
    {
        reinstating_to_page = reinstating_to_page || false;
        scroll_pos = scroll_pos || 0;

        ph_loading_properties = true;

        // Show loading indicator
        jQuery('.ph-infinite-scroll-loading').show();

        jQuery( document ).trigger('ph:infinite_scroll_loading_properties');

        // Do AJAX request
        var data = {
            'action': 'propertyhive_infinite_load_properties',
            'query_vars' : ph_is_ajax_object.query_vars,
            'query_string' : ph_is_ajax_object.query_string,
            'paged' : page
        };

        jQuery.post(ph_is_ajax_object.ajax_url, data, function( properties ) 
        {
            jQuery('.properties').append(properties);

            jQuery('.ph-infinite-scroll-loading').hide();

            if ( ph_reinstating_position && reinstating_to_page != false )
            {
                if ( reinstating_to_page == page )
                {
                    ph_reinstating_position = false;

                    setTimeout(function() { jQuery(window).scrollTop(scroll_pos); }, 50, scroll_pos); // add very slight delay. Found it helpful when re-setting position
                }
                else
                {
                    ph_grab_properties( page + 1, reinstating_to_page, scroll_pos );
                }
            }

            jQuery('.ph-infinite-scroll-button a').attr('disabled', false);

            if ( (ph_current_page * ph_posts_per_page) >= ph_total_posts )
            {
                jQuery('.ph-infinite-scroll-button').hide();
            }

            // Update results count
            if ( jQuery('.propertyhive-result-count').length )
            {
                // get the bit which contains 'x-x' only
                var result_count_html = jQuery('.propertyhive-result-count').html().split(" ");
                var current_result_count = '';
                for ( var i in result_count_html )
                {
                    if ( result_count_html[i].length > 1 && result_count_html[i].indexOf('–') != -1 )
                    {
                        current_result_count = result_count_html[i];
                    }
                }
                if ( current_result_count != '' )
                {
                    var new_result_count = '1–';
                    if ( ( ph_posts_per_page * ph_current_page ) < ph_total_posts )
                    {
                        new_result_count = new_result_count + ( ph_posts_per_page * ph_current_page );
                    }
                    else
                    {
                        new_result_count = new_result_count + ph_total_posts;
                    }
                    jQuery('.propertyhive-result-count').html( jQuery('.propertyhive-result-count').html().replace(current_result_count, new_result_count) );
                }
            }

            jQuery( document ).trigger('ph:infinite_scroll_loaded_properties');

            jQuery(window).trigger('scroll');
            jQuery(window).trigger('resize');

            ph_loading_properties = false;
        }, 'html');
    }
}