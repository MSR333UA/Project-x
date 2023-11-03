<div class="uap-product-links-single-item">
    <div class="uap-single-product-image-wrapp"><img src="<?php echo $data['featureImage'];?>" class="uap-single-product-img" /></div>
    <div class="uap-single-product-name">
    <a href="<?php echo $data['permalink'];?>" target="_blank" class="uap-special-label"><?php echo $data['label'];?></a>
    <?php if ( $data['categories'] ):?>
        <?php foreach ( $data['categories'] as $object ):?>
            <div class="uap-single-product-category-name"> <?php esc_html_e( 'Category: ', 'uap');?><strong><?php echo $object->name;?></strong></div>
        <?php endforeach;?>
    <?php endif;?>
    </div>
    <div class="uap-single-product-price">
	<?php
		if(isset($data['numeric_price']) && isset($data['numeric_regular_price']) && $data['numeric_regular_price'] > $data['numeric_price']){
			echo '<div class="uap-single-product-regular-price">'.$data['regular_price'].'</div> <div class="uap-single-product-sale-price">'.$data['price'].'</div>' ;
		}else{
			echo $data['price'];
		}
	?></div>
    <div class="uap-single-product-rewards">
    <?php if ( $data['showReward'] ):?>
    	<div class="uap-single-product-reward-label"><?php esc_html_e( 'Rewards', 'uap' );?></div>
        <?php echo $data['referral_amount'];?>
    <?php endif;?>
    </div>
    <div class="uap-single-product-link-wrapper">
    <div class="uap-single-product-link js-uap-affiliate-product-affiliate-link" data-post_id="<?php echo $data['id'];?>" ><?php esc_html_e('Get Affiliate Link', 'uap');?></div>
    </div>
    <div class="uap-clear"></div>

</div>
