<div class="iump-view-user-wrapp-temp1 iump-color-scheme-<?php echo $data['color_scheme_class'];?>">
	<?php if ($data['color_scheme_class'] !=''){ ?>
	<style>
		.iump-view-user-wrapp-temp1 .ihc-levels-wrapper .ihc-top-level-box{
			background-color:#<?php echo $data['color_scheme_class'];?>;
			border-color:#<?php echo $data['color_scheme_class'];?>;
			color:#fff;
		}
		.iump-view-user-wrapp-temp1 .ihc-levels-wrapper{

		}
		.iump-view-user-wrapp-temp1 .ihc-middle-side .iump-name{
			color:#<?php echo $data['color_scheme_class'];?>;
		}
		.iump-view-user-wrapp-temp1 .ihc-levels-wrapper{
			background-color: transparent;
		}
	</style>
	<?php } ?>
	<?php if (empty($data['banner'])){ ?>
	<style>
		.iump-view-user-wrapp-temp1 .ihc-user-page-top-ap-wrapper{
			padding-top:70px;
		}
	</style>
	<?php } ?>
	<?php if ( !empty( $data['ihc_badges_on'] ) && !empty( $data['ihc_badge_custom_css'] ) ):?>
		<style>
			<?php echo stripslashes( $data['ihc_badge_custom_css'] );?>
		</style>
	<?php endif;?>
	
	<div class="ihc-user-page-top-ap-wrapper">
	<?php if (!empty($data['avatar'])):?>
		<div class="ihc-left-side">
			<div class="ihc-user-page-details">
				<div class="ihc-user-page-avatar"><img src="<?php echo $data['avatar'];?>" class="ihc-member-photo"></div>
			</div>
		</div>
	<?php endif;?>
	<div class="ihc-middle-side">
		<?php if (!empty($data['flag'])):?>
			<span class="iump-flag"><?php echo $data['flag'];?></span>
		<?php endif;?>
		<?php if (!empty($data['name'])):?>
			<span class="iump-name"><?php echo $data['name'];?></span>
		<?php endif;?>
		<div class="iump-addiional-elements">
		<?php if (!empty($data['username'])):?>
			<span class="iump-element iump-username"><?php echo $data['username'];?></span>
		<?php endif;?>

		<?php if (!empty($data['email'])):?>
			<span class="iump-element iump-email"><?php echo $data['email'];?></span>
		<?php endif;?>

		<?php if (!empty($data['website'])):?>
			<span class="iump-element iump-website"><a href="<?php echo $data['website'];?>" target="_blank"><?php echo $data['website'];?></a></span>
		<?php endif;?>

		<?php if (!empty($data['since'])):?>
			<span class="iump-element iump-since"><?php echo __('Joined ', 'ihc');?><?php echo $data['since'];?></span>
		<?php endif;?>
		</div>

	</div>
	<div class="ihc-clear"></div>
	<?php if (!empty($data['banner'])):
		$bn_style ='';
		if($data['banner'] !='default')
			$bn_style =' style="background-image:url('.$data['banner'].');"';
	?>
	<div class="ihc-user-page-top-ap-background" <?php echo $bn_style; ?>></div>
	<?php endif;?>

	</div>
	<?php if (!empty($data['levels'])):?>
		<div class="ihc-levels-wrapper">
			<?php foreach ($data['levels'] as $lid => $level):?>
				<?php
					$is_expired_class = '';
					if (isset($level['expire_time']) && indeed_get_unixtimestamp_with_timezone()>strtotime( $level['expire_time'] ) ){
						$is_expired_class = 'ihc-expired-level';
					}
				?>
				<?php if (!empty($data['ihc_badges_on']) && !empty($level['badge_image_url'])):?>
					<div class="iump-badge-wrapper <?php echo $is_expired_class;?>"><img src="<?php echo $level['badge_image_url'];?>" class="iump-badge" title="<?php echo $level['label'];?>" /></div>
				<?php elseif (!empty($level['label'])):?>
					<div class="ihc-top-level-box <?php echo $is_expired_class;?>"><?php echo $level['label'];?></div>
				<?php endif;?>
			<?php endforeach;?>
		</div>
	<?php endif;?>



	<?php if (!empty($data['custom_fields'])):
    //dd($data['custom_fields']);
    ?>
		<div class="iump-user-fields-list">
			<?php foreach ($data['custom_fields'] as $label => $value):?>
				<?php if ($value!=''):?>
					<div class="iump-user-field"><div class="iump-label"><?php echo $label; ?></div> <div class="iump-value"><p> <?php echo $value;?> </p></div><div class="ihc-clear"></div></div>

				<?php endif;?>
			<?php endforeach;?>
		</div>
	<?php endif;?>

	<?php if (!empty($data['content'])):?>
		<div class="iump-additional-content">
			<?php echo $data['content'];?>
		</div>
	<?php endif;?>

</div>
