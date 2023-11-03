<form role="search" method="get" class="search-form" action="<?php echo home_url( '/' ); ?>">
	<input type="search" class="search-field" placeholder="<?php echo esc_attr_x( 'Type and hit enter', 'bakery'); ?>" name="s" value="<?php echo get_search_query() ?>"/>
	<input type="submit" class="search-submit hide" value="<?php echo esc_attr_x( 'Search', 'bakery'); ?>" />
</form>