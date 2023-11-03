(function($){
	$(function(){
		// Media frame
		var vu_media_frame;
		
		// Bind to our click event in order to open up the new media experience.
		$(document.body).on('click.vuOpenMediaManager', '.vu-open-media', function(e){
			e.preventDefault();

			var $this = $(this);

			vu_media_frame = wp.media.frames.vu_media_frame = wp.media({
				className: 'media-frame vu-media-frame',
				toolbar: 'main-insert',
				filterable: 'uploaded',
				multiple: $this.hasClass('multiple') ? 'add' : false,
				title: $this.data('title'), //$this.hasClass('multiple') ? vu_admin_config.media.title.multiple : vu_admin_config.media.title.single,
				library: {
					type: ($this.data('type') !== undefined) ? $this.data('type') : 'image'
				},
				button: {
					text: $this.data('button')//$this.hasClass('multiple') ? vu_admin_config.media.button.multiple : vu_admin_config.media.button.single
				}
			});

			vu_media_frame.on('select', function(){
				if( $this.data('type') === undefined || $this.data('type') === 'image' ){
					if( !$this.hasClass('multiple') ){
						var media_attachment = vu_media_frame.state().get('selection').first().toJSON();

						// Send the attachment URL to our custom input field via jQuery.
						$('#'+ $this.data('input')).val(media_attachment.url);
						$('#'+ $this.data('img')).attr({'src': media_attachment.url});
					} else {
						var media_attachments = vu_media_frame.state().get('selection').toJSON(),
							images_url = [];

						//$('#'+ $this.data('img')).html('');

						$.each(media_attachments, function(index, obj){
							images_url[index] = obj.id;

							var media_url;

							if( obj.sizes.thumbnail !== undefined ){
								media_url = obj.sizes.thumbnail.url;
							} else {
								media_url = obj.sizes.full.url;
							}

							$('#'+ $this.data('img')).append('<div><img data-id="'+ obj.id +'" src="'+ media_url +'"><span>&times;</span></div>');
						});

						$('#'+ $this.data('input')).val(images_url.join(','));
					}
				} else {
					var media_attachment = vu_media_frame.state().get('selection').first().toJSON();

					$('#'+ $this.data('input')).val(media_attachment.url);
				}
			});

			// Now that everything has been set, let's open up the frame.
			vu_media_frame.open();
		});

		//Dependency
		var $vu_dependency = $('.vu_dependency');

		$vu_dependency.each(function(){
			var $this = $(this),
				$element = $('#'+ $this.data('element')),
				value = $this.data('value');

			if( !$element.length ){
				$element = $('[name="'+ $(this).data('element') +'"]');
			}

			//on load
			vu_dependency($this, $element, value);

			$element.on('change', function(){
				vu_dependency($this, $element, value);
			});
		});

		function vu_dependency($this, $element, value){
			if( $element.is('input:radio') || $element.is('input:checkbox') ){
				if( $element.is(':checked') && $element.filter(':checked').val() == value ){
					$this.show();
				} else {
					$this.hide();
				}
			} else {
				if( $element.val() = value ) {
					$this.show();
				} else {
					$this.hide();
				}
			}
		}

		// Order gallery images
		$vu_media_images = $('.vu_media-images');

		$vu_media_images.each(function(){
			var $this = $(this);

			$this.sortable({
				cursor: "move",
				items: "> div",
				opacity: 0.7,
				update: function( event, ui ){
					var images_url = [];

					$this.find('img').each(function(index, value){
						images_url[index] = $(this).data('id');
					});

					$('#'+ $this.data('input')).val(images_url.join(','));
				}
			});
		});

		// Remove image
		$(document.body).on('click', '.vu_media-images span', function(){
			var $container = $(this).parent().parent(),
				images_url = [];

			$(this).parent().remove();

			$container.find('img').each(function(index, value){
				images_url[index] = $(this).data('id');
			});

			$('#'+ $container.data('input')).val(images_url.join(','));
		});

		// Post formats
		var $vu_post_format = $('#post-formats-select input[type="radio"]');
		
		vu_post_format( $vu_post_format.filter(':checked') );

		$vu_post_format.on('change', function(){
			vu_post_format( $(this) );
		});

		function vu_post_format(element){
			var $container = $('#vu_post-format-settings.postbox'),
				input_value = element.val();

			if( input_value != '0' && input_value != 'image' ){
				$container.find('.vu_metabox-container').hide();
				$container.find('.vu_metabox-container[data-format="'+ input_value +'"]').show();
				$container.show();
			} else {
				$container.hide();
			}
		}

		// Gallery
		$(document).ajaxSuccess(function(e, xhr, settings) {
			if( settings.data !== undefined && settings.data.indexOf("action=vc_edit_form") != -1 && settings.data.indexOf("tag=vu_gallery_item") != -1 ) {
				if( vc.shortcodes.get(vc.active_panel.model.attributes.parent_id).get('params').filterable == "1" ) {
					var categories = vc.shortcodes.get(vc.active_panel.model.attributes.parent_id).get('params').categories.split("\n");
						$categories = $('#vc_ui-panel-edit-element[data-vc-shortcode="vu_gallery_item"] .vc_shortcode-param[data-vc-shortcode-param-name="category"] input.category').hide(),
						_categories = $categories.val().split(','),
						$_categories = $('<select multiple class="category"></select>');

					$.each(categories, function(key, value) {
						$_categories
							.append($("<option></option>")
							.attr("value",value)
							.text(value)); 
					});

					//selected
					$.each(_categories, function(key, value){
						$_categories.find('option[value="'+ value +'"]').prop('selected', true);
					});

					$_categories.insertAfter($categories);
				} else {
					$('#vc_ui-panel-edit-element[data-vc-shortcode="vu_gallery_item"] .vc_shortcode-param[data-vc-shortcode-param-name="category"').hide();
				}
			}
		});

		$(document.body).on('change', '#vc_ui-panel-edit-element[data-vc-shortcode="vu_gallery_item"] .vc_shortcode-param[data-vc-shortcode-param-name="category"] select.category', function(){
			$(this).prev('input.category').val($(this).val());
		});
	});
})(jQuery);