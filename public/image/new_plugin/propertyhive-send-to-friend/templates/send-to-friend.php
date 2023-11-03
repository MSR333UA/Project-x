<li class="action-send-to-friend">
    
    <a data-fancybox data-src="#sendToFriend<?php echo $post->ID; ?>" href="javascript:;"><?php _e( 'Send To Friend', 'propertyhive' ); ?></a>

    <!-- LIGHTBOX FORM -->
    <div id="sendToFriend<?php echo $post->ID; ?>" style="display:none;">
        
        <h2><?php _e( 'Send To Friend', 'propertyhive' ); ?></h2>

        <p><?php _e( 'Send details of ' . get_the_title() . ' to a friend by completing the information below.', 'propertyhive' ); ?></p>
        
        <?php $this->propertyhive_send_to_friend_form(); ?>
        
    </div>
    <!-- END LIGHTBOX FORM -->
    
</li>
