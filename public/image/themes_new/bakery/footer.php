<?php if( !is_page_template('template-blank.php') ) : ?>


<!-- Start of HubSpot Embed Code --> <script type="text/javascript" id="hs-script-loader" async defer src="//js.hs-scripts.com/6221557.js"></script> <!-- End of HubSpot Embed Code -->

		<footer class="page-footer">
			<?php if( vu_get_option('show-footer-top') ) : ?>
				<div class="footer-light">

						<?/*<img src="<?=SITE_URL;?>img/envelope.png" class="envelope" />*/?>

					<div class="container">
						<div id="footer-top-widgets">
							<div class="row">
								<?php /*dynamic_sidebar('footer-top-sidebar');*/ ?>




<div class="col-md-12" data-delay="200" style="text-align:center"><div class="widget m-b-50 clearfix widget_text text-9"><h3 class="widget_title">NEWSLETTER</h3>			


<div class="textwidget"><p>Give us your email, and we will send you regular updates for the latest info and events.</p>

<?/*
		<form class="form-subscribe clearfix vu_frm-ajax vu_clear-fields" pmbx_context="89B94BC1-9EC0-4059-B50D-7D697DFE261A" style="max-width:650px;margin:10px auto">
			<div class="hide">
				<input type="hidden" name="action" value="vu_newsletter" pmbx_context="953B60BE-3971-464A-9322-6F3E15E34BC3">
				<input type="hidden" name="_wpnonce" value="7a0a9eb3ba" pmbx_context="E377874B-3579-4EED-8A97-4CB712F3E0B6">
			</div>

			<div class="vu_newsletter_fields clearfix">
				<div class="email-container">
					<input type="text" name="email" pmbx_context="74AA8ED5-5463-4A9D-83F3-6FBA806784B5">
				</div>
				<div class="submit-container">
					<input type="submit" value="Subscribe" pmbx_context="68284821-256E-4DB1-8F5C-031C10AD7FBF">
				</div>
			</div>

			<div class="vu_msg m-t-10"></div>
		</form>
*/?>
<!-- Begin MailChimp Signup Form -->
<link href="//cdn-images.mailchimp.com/embedcode/classic-10_7.css" rel="stylesheet" type="text/css">
<style type="text/css">
	#mc_embed_signup{background:#fff; clear:left; font:14px Helvetica,Arial,sans-serif; }
	/* Add your own MailChimp form style overrides in your site stylesheet or in this style block.
	   We recommend moving this block and the preceding CSS link to the HEAD of your HTML file. */
</style>
<div id="mc_embed_signup">
<form action="//24britishmaintenance.us16.list-manage.com/subscribe/post?u=22aa03163da8badbd7f434dff&amp;id=5a411fe405" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
    <div id="mc_embed_signup_scroll">
	
<div class="indicates-required"><span class="asterisk">*</span> indicates required</div>
<div class="mc-field-group">
	<label for="mce-EMAIL">Email Address  <span class="asterisk">*</span>
</label>
	<input type="email" value="" name="EMAIL" class="required email" id="mce-EMAIL">
</div>
	<div id="mce-responses" class="clear">
		<div class="response" id="mce-error-response" style="display:none"></div>
		<div class="response" id="mce-success-response" style="display:none"></div>
	</div>    <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
    <div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_22aa03163da8badbd7f434dff_5a411fe405" tabindex="-1" value=""></div>
    <div class="clear"><input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button"></div>
    </div>
</form>
</div>
<script type='text/javascript' src='//s3.amazonaws.com/downloads.mailchimp.com/js/mc-validate.js'></script><script type='text/javascript'>(function($) {window.fnames = new Array(); window.ftypes = new Array();fnames[0]='EMAIL';ftypes[0]='email';fnames[1]='FNAME';ftypes[1]='text';fnames[2]='LNAME';ftypes[2]='text';fnames[3]='BIRTHDAY';ftypes[3]='birthday';}(jQuery));var $mcj = jQuery.noConflict(true);</script>
<!--End mc_embed_signup-->

		</div>
		</div></div>





							</div>
						</div>
					</div>
				</div><!-- /footer-light -->
			<?php endif; ?>
			<div class="footer-dark">
				<div class="container">
					<?php if( vu_get_option('show-footer-bottom') ) : ?>
						<div id="footer-bottom-widgets">
							<div class="row">
								<?php dynamic_sidebar('footer-bottom-sidebar'); ?>
							</div>
						</div>
					<?php endif; ?>
					
					<?php if( vu_get_option('show-copyright-text') ) : ?>
					<p class="site-info"><?php echo (vu_get_option('copyright-text')); ?></p>
					<?php endif; ?>

					<?php if( vu_get_option('show-back-to-top') ) : ?>
						<a href="#all" class="to-top scroll-to"></a>
					<?php endif; ?>
				</div>
			</div><!-- /footer-dark -->
		</footer>
	<?php endif; ?>

	</div>


	<?php wp_footer(); ?>
		
</body>

 <!-- Histats.com  START  (aync)-->

<!-- Histats.com  END  -->



</html>