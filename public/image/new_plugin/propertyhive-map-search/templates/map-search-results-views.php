<?php
    echo '<div class="propertyhive-views">
    <ul>';
    foreach ( $views as $view => $options )
    {
        $href = isset($options['href']) ? $options['href'] : get_permalink( ph_get_page_id('search_results') );
        $href .= strpos($href, '?') === FALSE ? '?' : '&';
        $href .= trim($new_query_string . ( ( isset($options['default']) && $options['default'] === true ) ? '' : '&view=' . $view ), '&');
        $href = trim($href, '?');

        echo '<li class="' . esc_attr(sanitize_title($view)) . '-view';
        if (
            ( isset($options['active']) && $options['active'] === true )
            ||
            ( !isset($options['active']) && isset($_GET['view']) && $_GET['view'] == $view )
        )
        {
            echo ' active';
        }
        elseif (
            isset($options['default']) &&
            $options['default'] === true &&
            (
                !isset($_GET['view']) ||
                ( isset($_GET['view']) && $_GET['view'] == '' )
            )
        )
        {
            echo ' active';
        }

        echo '"><a href="' . $href . '">' . $options['content'] . '</a></li>';
    }
    echo '</ul>
    </div>';