<?php
	/**
	 * Bakery Theme Options
	 */

	define("TD_OPTIONS", "bakery_options");

	if ( ! class_exists( 'Redux_Framework_bakery_options' ) ) {

		class Redux_Framework_bakery_options {

			public $args = array();
			public $sections = array();
			public $theme;
			public $ReduxFramework;

			public function __construct() {

				if ( ! class_exists( 'ReduxFramework' ) ) {
					return;
				}

				// This is needed. Bah WordPress bugs.  ;)
				if ( true == Redux_Helpers::isTheme( __FILE__ ) ) {
					$this->initSettings();
				} else {
					add_action( 'plugins_loaded', array( $this, 'initSettings' ), 10 );
				}

			}

			public function initSettings() {
				// Just for demo purposes. Not needed per say.
				$this->theme = wp_get_theme();

				// Set the default arguments
				$this->setArguments();

				// Create the sections and fields
				$this->setSections();

				if ( ! isset( $this->args['opt_name'] ) ) { // No errors please
					return;
				}

				$this->ReduxFramework = new ReduxFramework( $this->sections, $this->args );
			}

			public function setSections() {
				ob_start();

				$this->theme = wp_get_theme();

				?>

				<div>
					<p class="item-uri">
						<strong><?php echo __('Theme URL', 'bakery_options' ); ?>: </strong>
						<a href="<?php echo $this->theme->get( 'ThemeURI' ); ?>" target="_blank"><?php echo $this->theme->get( 'Name' ); ?></a>
					</p>

					<p class="item-author">
						<strong><?php echo __('Author', 'bakery_options' ); ?>: </strong>
						<a href="<?php echo $this->theme->get( 'AuthorURI' ); ?>" target="_blank"><?php echo $this->theme->get( 'Author' ); ?></a>
					</p>

					<p class="item-version">
						<strong><?php echo __('Version', 'bakery_options' ); ?>: <?php echo $this->theme->get( 'Version' ); ?></strong>
					</p>

					<p class="item-tags">
						<strong><?php echo __('Tags', 'bakery_options' ); ?>: </strong>
						<?php echo $this->theme->display( 'Tags' ); ?>
					</p>

					<div style="margin-top: 30px;">
						<h3><?php echo __('Documentation', 'bakery_options' ); ?></h3>
					</div>
					
					<div class="redux-section-desc">
						<p>Please refer to our <a href="http://milingona.co" target="_blank">online documentation</a> for more instructions on how to use the theme. If you have any questions that are beyond the scope of this documentation, don't hesitate to contact us.</p><br>
					</div>

					<div style="margin-top: 30px;">
						<h3><?php echo __('Thank you!', 'bakery_options' ); ?></h3>
					</div>
					
					<div class="redux-section-desc">
						<p>Thank you very much for choosing Bakery! We truly appreciate and really hope that you'll enjoy our theme! We've done our very best to make it as extensive and feature, while also keeping it incredibly easy and slick to use. So, if you like this theme, <strong>please support us by rating us 5 stars</strong>.</p><br>
						<p>Please visit <a href="http://milingona.co" target="_blank">milingona.co</a> to keep up to date on the new update releases as well as all the themes we create.</p>
					</div>
				</div>

				<?php
				$item_info = ob_get_contents();

				ob_end_clean();

				$admin_email = get_option('admin_email');

				//General
				$this->sections[] = array(
					'title'  => __( 'General', 'bakery_options' ),
					'desc'   => __( '', 'bakery_options' ),
					'icon'   => 'fa fa-cogs',
					'fields' => array(
						array(
							'id'       => 'favicon',
							'type'     => 'media',
							'preview'  => false,
							'title'    => __( 'Favicon', 'bakery_options' ),
							'subtitle' => __( 'Upload your site\'s favicon.', 'bakery_options' ),
							'desc'     => __( 'File dimension should be 16 x 16 pixels in .png or .ico formats.<br>Note: Old IEs will work only if you upload an <b>.ico</b> image.', 'bakery_options' ),
						),
						array(
							'id'       => 'apple-touch-icon',
							'type'     => 'media',
							'preview'  => false,
							'title'    => __( 'Apple Touch Icon', 'bakery_options' ),
							'subtitle' => __( 'Upload your site\'s apple touch icon.', 'bakery_options' ),
							'desc'     => __( 'File dimension should be 129 x 129 pixels in <b>.png</b> format.', 'bakery_options' ),
						),
						array(
							'id'       => 'open-graph-meta-data',
							'type'     => 'switch',
							'title'    => __( 'Open Graph meta data', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Turn on to show Open Graph meta data.', 'bakery_options' ),
							'on'       => __( 'On', 'bakery_options' ),
							'off'      => __( 'Off', 'bakery_options' ),
							'default'  => true,
						),
						array(
							'id'       => 'animation',
							'type'     => 'switch',
							'title'    => __( 'Animation', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Turn on to show elements with animations.', 'bakery_options' ),
							'on'       => __( 'On', 'bakery_options' ),
							'off'      => __( 'Off', 'bakery_options' ),
							'default'  => true,
						),
						array(
							'id'       => 'smooth-scroll',
							'type'     => 'switch',
							'title'    => __( 'Smooth Scroll', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Turn on to enabale smooth scrolling.', 'bakery_options' ),
							'on'       => __( 'On', 'bakery_options' ),
							'off'      => __( 'Off', 'bakery_options' ),
							'default'  => true,
						),
						array(
							'id'       => 'preloader',
							'type'     => 'switch',
							'title'    => __( 'Preloader', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Turn on to show preloader while page is loading.', 'bakery_options' ),
							'on'       => __( 'On', 'bakery_options' ),
							'off'      => __( 'Off', 'bakery_options' ),
							'default'  => false,
						),
						array(
							'id'       => 'preloader-image',
							'type'     => 'media',
							'required' => array( 'preloader', '=', true ),
							'preview'  => true,
							'title'    => __( 'Preloader image', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( '', 'bakery_options' ),
							'default'  => array(
								'url' => THEME_ASSETS .'images/preloader.gif'
							)
						),
						array(
							'id'       => 'site-mode',
							'type'     => 'button_set',
							'title'    => __( 'Site Mode', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Select site\'s mode. Note: If \'Under Construction\' mode is selected, only logged in users will be able to see the real site\'s content.', 'bakery_options' ),
							'options'  => array(
								'normal' => 'Normal',
								'under_construction' => 'Under Construction'
							),
							'default'  => 'normal'
						),
						array(
							'id'       => 'site-mode-page',
							'type'     => 'select',
							'required' => array( 'site-mode', '=', 'under_construction' ),
							'data'     => 'pages',
							'title'    => __( 'Maintenance or Coming Soon Page', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Select maintenance or coming soon page.', 'bakery_options' ),
							'default'  => ''
						)
					)
				);
				
				//Styling
				$this->sections[] = array(
					'title'  => __( 'Styling', 'bakery_options' ),
					'desc'   => __( '', 'bakery_options' ),
					'icon'   => 'fa fa-paint-brush',
					'fields' => array(
						/*array(
							'id'       => 'theme-skin',
							'type'     => 'button_set',
							'title'    => __( 'Skin', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Select skin color of your theme.', 'bakery_options' ),
							'options'  => array(
								'light' => 'Light',
								'dark' => 'Dark'
							),
							'default'  => 'light'
						),*/
						array(
							'id'       => 'primary-color',
							'type'     => 'color',
							'title'    => __( 'Primary Color', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Select primary color of your theme.', 'bakery_options' ),
							'transparent' => false,
							'validate' => 'color',
							'default'  => '#fdb822',
						),
						array(
							'id'       => 'secondary-color',
							'type'     => 'color',
							'title'    => __( 'Secondary Color', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Select secondary color of your theme.', 'bakery_options' ),
							'transparent' => false,
							'validate' => 'color',
							'default'  => '#684f40',
						),
						array(
							'id'       => 'boxed-layout',
							'type'     => 'switch',
							'title'    => __( 'Boxed Layout', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Turn on to enable Boxed Layout. If enabled you can use an image as backgroud.', 'bakery_options' ),
							'on'       => __( 'On', 'bakery_options' ),
							'off'      => __( 'Off', 'bakery_options' ),
							'default'  => false,
						),
						array(
							'id'       => 'body-background',
							'type'     => 'background',
							'title'    => __( 'Background', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Upload a large and beatiful background image for your site. Note: This will override the background color and background pattern.', 'bakery_options' ),
							'output'   => array('body'),
							'default'  => array(
								'background-color' => '#fff'
							)
						),
						array(
							'id'       => 'bg-pattern',
							'type'     => 'select_image',
							'title'    => __( 'Pattern', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Select the pattern that will be applied to the background. Note: This will override the background color.', 'bakery_options' ),
							'options'  => array(
								array(
									'alt' => 'Default Pattern',
									'img' => THEME_ASSETS .'images/patterns/default.jpg'
								),
								array(
									'alt' => 'Pattern 1',
									'img' => THEME_ASSETS .'images/patterns/pattern-1.png'
								),
								array(
									'alt' => 'Pattern 2',
									'img' => THEME_ASSETS .'images/patterns/pattern-2.png'
								),
								array(
									'alt' => 'Pattern 3',
									'img' => THEME_ASSETS .'images/patterns/pattern-3.png'
								),
								array(
									'alt' => 'Pattern 4',
									'img' => THEME_ASSETS .'images/patterns/pattern-4.png'
								)
							),
							'default'  => ''
						)
					)
				);
				
				//Typography
				$this->sections[] = array(
					'title'  => __( 'Typography', 'bakery_options' ),
					'desc'   => __( '', 'bakery_options' ),
					'icon'   => 'fa fa-font',
					'fields' => array(
						array(
							'id'          => 'body-typography',
							'type'        => 'typography', 
							'title'       => __('Body', 'bakery_options' ),
							'google'      => true, 
							'font-backup' => false,
							'subsets'     => false,
							'line-height' => false,
							'text-align'  => false,
							'units'       => 'px',
							'output'      => array('body', 'article', '.article-header-5 h1', '.article-header-5 h3', '.blog-post-comments .blog-post-comment-heading', '.blog-post .blog-post-tags a', '.blog-post-small h3', '.quote', '.gallery-slider-it .gallery-slider-detail', '.form-control'),
							'subtitle'    => __('', 'bakery_options' ),
							'default'     => array(
								'font-family' => 'Open Sans',
								'font-size'   => '14px',
								'font-weight' => '400',
								'color'       => '#696969',
								'google'      => true
							)
						),
						array(
							'id'          => 'main-menu-typography',
							'type'        => 'typography', 
							'title'       => __('Navigation', 'bakery_options' ),
							'google'      => true, 
							'font-backup' => true,
							'subsets'     => true,
							'line-height' => true,
							'text-align'  => true,
							'text-transform'  => true,
							'units'       => 'px',
							'output'      => array('.vu_main-menu > ul > li a'),
							'subtitle'    => __('', 'bakery_options' ),
							'default'     => array(
								'font-family' => 'Montserrat',
								'font-size'   => '14px',
								'font-weight' => '700',
								'color'       => '#684f40',
								'text-transform' => 'uppercase',
								'google'      => true
							)
						),
						array(
							'id'          => 'main-sub-menu-typography',
							'type'        => 'typography', 
							'title'       => __('Navigation Submenu', 'bakery_options' ),
							'google'      => true, 
							'font-backup' => true,
							'subsets'     => true,
							'line-height' => true,
							'text-align'  => true,
							'text-transform' => true,
							'units'       => 'px',
							'output'      => array('.vu_main-menu ul li ul.sub-menu li a'),
							'subtitle'    => __('', 'bakery_options' ),
							'default'     => array(
								'font-family' => 'Montserrat',
								'font-size'   => '14px',
								'line-height' => '20px',
								'font-weight' => '400',
								'color'       => '#684f40',
								'text-transform' => 'none',
								'google'      => true
							)
						),
						array(
							'id'          => 'header-title-typography',
							'type'        => 'typography', 
							'title'       => __('Header Title', 'bakery_options' ),
							'google'      => true, 
							'font-backup' => false,
							'subsets'     => false,
							'line-height' => false,
							'text-align'  => false,
							'units'       => 'px',
							'output'      => array('.full-header h1'),
							'subtitle'    => __('', 'bakery_options' ),
							'default'     => array(
								'font-family' => 'Montserrat',
								'font-size'   => '30px',
								'font-weight' => '700',
								'color'       => '#fff',
								'google'      => true
							)
						),
						array(
							'id'          => 'header-subtitle-typography',
							'type'        => 'typography', 
							'title'       => __('Header Subtitle', 'bakery_options' ),
							'google'      => true, 
							'font-backup' => false,
							'subsets'     => false,
							'line-height' => false,
							'text-align'  => false,
							'units'       => 'px',
							'output'      => array('.full-header h3'),
							'subtitle'    => __('', 'bakery_options' ),
							'default'     => array(
								'font-family' => 'Open Sans',
								'font-size'   => '16px',
								'font-weight' => '400',
								'color'       => '#fff',
								'google'      => true
							)
						),
						array(
							'id'          => 'headings-typography',
							'type'        => 'typography', 
							'title'       => __('Headings', 'bakery_options' ),
							'google'      => true, 
							'font-backup' => false,
							'subsets'     => false,
							'line-height' => false,
							'text-align'  => false,
							'font-size'   => false,
							'font-style'  => false,
							'color'       => false,
							'units'       => 'px',
							'output'      => array('h1, h2, h3, h4, h5, h6'),
							'desc'    => __('Select h1, h2, h3, h4, h5, h6 font family, font weight & style', 'bakery_options' ),
							'default'     => array(
								'font-family' => 'Montserrat',
								'font-weight' => '700',
								'google'      => true
							)
						)
					)
				);

				//Header
				$this->sections[] = array(
					'title'  => __( 'Header', 'bakery_options' ),
					'desc'   => __( '', 'bakery_options' ),
					'icon'   => 'fa fa-toggle-up',
					'fields' => array(
						array(
							'id'       => 'top-bar',
							'type'     => 'switch',
							'title'    => __( 'Show Top Bar', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Turn on to show top bar above main menu', 'bakery_options' ),
							'on'       => __( 'On', 'bakery_options' ),
							'off'      => __( 'Off', 'bakery_options' ),
							'default'  => true,
						),
						array(
							'id'       => 'top-bar-left',
							'type'     => 'textarea',
							'required' => array( 'top-bar', '=', true ),
							'title'    => __( 'Top Bar Left', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Enter top bar left side content.', 'bakery_options' ),
							'default'  => '<span><i class="fa fa-phone-square"></i>0800 123 456 878</span>|<span><i class="fa fa-envelope"></i>info@bakerytheme.com</span>',
						),
						array(
							'id'       => 'top-bar-right',
							'type'     => 'textarea',
							'required' => array( 'top-bar', '=', true ),
							'title'    => __( 'Top Bar Right', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Enter top bar right side contnt.', 'bakery_options' ),
							'default'  => '[vu_social_network url="http://facebook.com" icon="fa fa-facebook"][vu_social_network url="http://www.twitter.com/milingona_" icon="fa fa-twitter"][vu_social_network url="#" icon="fa fa-rss"][vu_social_network url="http://www.pinterest.com" icon="fa fa-pinterest"][vu_social_network url="http://www.linkedin.com" icon="fa fa-linkedin"]',
						),
						array(
							'id'       => 'header-type',
							'type'     => 'button_set',
							'title'    => __( 'Header Type', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Select header type.', 'bakery_options' ),
							'options'  => array(
								'logo-middle' => 'Logo in the middle',
								'logo-top' => 'Logo on top',
								'logo-left' => 'Logo on left',
								'logo-right' => 'Logo on right'
							),
							'default'  => 'logo-middle'
						),
						array(
							'id'       => 'logo',
							'type'     => 'media',
							'title'    => __( 'Logo', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Upload a custom logo for your site.', 'bakery_options' ),
							'default'  => array(
								'url' => THEME_ASSETS .'images/logo.png',
								'width' => '109',
								'height' => '112'
							)
						),
						array(
							'id'       => 'logo-width',
							'type'     => 'slider',
							'title'    => __( 'Logo Container Width', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Enter logo container width in px.', 'bakery_options' ),
							'min'      => 50,
							'max'      => 300,
							'step'      => 1,
							'default'  => 120
						),
						array(
							'id'       => 'main-menu-item-padding',
							'type'     => 'spacing',
							'title'    => __( 'Main Menu Item Padding', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Enter main menu item top, right, bottom and left padding.', 'bakery_options' ),
							'output'   => array('.vu_main-menu > ul > li a'),
							'units'    => array('px'),
							'default'  => array(
								'padding-top'     => '12px', 
								'padding-right'   => '25px', 
								'padding-bottom'  => '12px', 
								'padding-left'    => '25px',
								'units'          => 'px', 
							)
						),
						array(
							'id'       => 'hamburger-menu',
							'type'     => 'text',
							'title'    => __('Hamburger Menu', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter width in px by which hamburger menu will be shown.', 'bakery_options' ),
							'validate' => 'numeric',
							'default' => 992
						),
						array(
							'id'       => 'fixed-header',
							'type'     => 'switch',
							'title'    => __( 'Fixed Header on Scroll', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Turn on to show fixed header when scrolling down.', 'bakery_options' ),
							'on'       => __( 'On', 'bakery_options' ),
							'off'      => __( 'Off', 'bakery_options' ),
							'default'  => true,
						),
						array(
							'id'       => 'fixed-header-offset',
							'type'     => 'text',
							'required' => array( 'fixed-header', '=', true ),
							'title'    => __('Fixed Header Offset', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter distance in px by which fixed header is shown.', 'bakery_options' ),
							'validate' => 'numeric',
							'default' => 200
						),
						array(
							'id'       => 'logo-secondary',
							'type'     => 'media',
							'required' => array( 'fixed-header', '=', true ),
							'title'    => __( 'Secondary Logo', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Upload a custom secondary logo for your site.', 'bakery_options' ),
							'default'  => array(
								'url' => THEME_ASSETS .'images/logo_secondary.png',
								'width' => '64',
								'height' => '37'
							)
						),
						array(
							'id'       => 'header-title-bg-prallax',
							'type'     => 'switch',
							'title'    => __( 'Header Title Background Parallax', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Turn on to enable parallax effect on header title backgrounds.', 'bakery_options' ),
							'on'       => __( 'On', 'bakery_options' ),
							'off'      => __( 'Off', 'bakery_options' ),
							'default'  => true,
						)
					)
				);
				
				//Blog
				$this->sections[] = array(
					'title'  => __( 'Blog', 'bakery_options' ),
					'desc'   => __( 'All blog related options are listed here.', 'bakery_options' ),
					'icon'   => 'fa fa-edit',
					'fields' => array(
						array(
							'id'       => 'blog-layout',
							'type'     => 'image_select',
							'title'    => __( 'Layout', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc' => __( 'Select blog layout.', 'bakery_options' ),
							'options'  => array(
								'left-sidebar' => array(
									'alt' => 'Left Sidebar',
									'img' => ReduxFramework::$_url .'assets/img/left-sidebar.png'
								),
								'right-sidebar' => array(
									'alt' => 'Right Sidebar',
									'img' => ReduxFramework::$_url .'assets/img/right-sidebar.png'
								),
								'no-sidebar' => array(
									'alt' => 'Without Sidebar',
									'img' => ReduxFramework::$_url .'assets/img/no-sidebar.png'
								)
							),
							'default'  => 'right-sidebar'
						),
						array(
							'id'       => 'blog-social',
							'type'     => 'switch',
							'title'    => __( 'Social Media Sharing Buttons', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Activate this to enable social sharing buttons on blog posts.', 'bakery_options' ),
							'on'       => __( 'On', 'bakery_options' ),
							'off'      => __( 'Off', 'bakery_options' ),
							'default'  => true,
						),
						array(
							'id'       => 'blog-social-networks',
							'type'     => 'checkbox',
							'required' => array( 'blog-social', '=', true ),
							'title'    => __( 'Social Networks', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Select social networks to be shown in single posts.', 'bakery_options' ),
							'options'  => array(
								'facebook' => 'Facebook',
								'twitter' => 'Twitter',
								'google-plus' => 'Google+',
								'pinterest' => 'Pinterest',
								'linkedin' => 'LinkedIn'
							),
							'default'  => array(
								'facebook' => '1',
								'twitter' => '1',
								'google-plus' => '1',
								'pinterest' => '1',
								'linkedin' => '0'
							)
						),
						array(
							'id'       => 'show-author',
							'type'     => 'switch',
							'title'    => __( 'Show Author', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Activate this to show author for blog posts.', 'bakery_options' ),
							'on'       => __( 'On', 'bakery_options' ),
							'off'      => __( 'Off', 'bakery_options' ),
							'default'  => false,
						),
						array(
							'id'       => 'show-tags',
							'type'     => 'switch',
							'title'    => __( 'Show Tags', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Activate this to show tags on single posts.', 'bakery_options' ),
							'on'       => __( 'On', 'bakery_options' ),
							'off'      => __( 'Off', 'bakery_options' ),
							'default'  => true,
						)
					)
				);

				//Portfolio
				$this->sections[] = array(
					'title'  => __( 'Portfolio', 'bakery_options' ),
					'desc'   => __( '', 'bakery_options' ),
					'icon'   => 'fa fa-briefcase',
					'fields' => array(
						array(
							'id'       => 'portfolio-layout',
							'type'     => 'image_select',
							'title'    => __( 'Layout', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc' => __( 'Select layout for the portfolio template. Note: The option selected here will apply also for search, category and tag pages.', 'bakery_options' ),
							'options'  => array(
								'left-sidebar' => array(
									'alt' => 'Left Sidebar',
									'img' => ReduxFramework::$_url .'assets/img/left-sidebar.png'
								),
								'right-sidebar' => array(
									'alt' => 'Right Sidebar',
									'img' => ReduxFramework::$_url .'assets/img/right-sidebar.png'
								),
								'no-sidebar' => array(
									'alt' => 'Without Sidebar',
									'img' => ReduxFramework::$_url .'assets/img/no-sidebar.png'
								)
							),
							'default'  => 'right-sidebar'
						),
						array(
							'id'       => 'portfolio-header-bg',
							'type'     => 'media',
							'title'    => __( 'Header Background', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Upload a custom background for header section that will be the default background for portfolio items related pages like category, tag pages etc.', 'bakery_options' ),
							'default'  => array(
								'url' => ''
							)
						),
						array(
							'id'       => 'portfolio-item-count',
							'type'     => 'spinner',
							'title'    => __('Number of portfolio items', 'bakery_options' ),
							'desc'     => __('Select number of portfolio items per page.', 'bakery_options' ),
							'default'  => '12',
							'min'      => '1',
							'step'     => '1',
							'max'      => '50',
						),
						array(
							'id'       => 'portfolio-social',
							'type'     => 'switch',
							'title'    => __( 'Social Media Sharing Buttons', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Activate this to enable social sharing buttons on the single portfolio page.', 'bakery_options' ),
							'on'       => __( 'On', 'bakery_options' ),
							'off'      => __( 'Off', 'bakery_options' ),
							'default'  => true,
						),
						array(
							'id'       => 'portfolio-social-networks',
							'type'     => 'checkbox',
							'required' => array( 'portfolio-social', '=', true ),
							'title'    => __( 'Social Networks', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Select social networks to be shown in the single portfolio page.', 'bakery_options' ),
							'options'  => array(
								'facebook' => 'Facebook',
								'twitter' => 'Twitter',
								'google-plus' => 'Google+',
								'pinterest' => 'Pinterest',
								'linkedin' => 'LinkedIn'
							),
							'default'  => array(
								'facebook' => '1',
								'twitter' => '1',
								'google-plus' => '1',
								'pinterest' => '1',
								'linkedin' => '0'
							)
						),
						array(
							'id'       => 'portfolio-slider-autoplay',
							'type'     => 'text',
							'title'    => __( 'Slider Autoplay', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Change to any integrer for example <b>5000</b> to play every <b>5</b> seconds a silde/image in single portfolio page. Leave blank to disable autoplay.', 'bakery_options' ),
							'default'  => '5000'
						),
						array(
							'id'       => 'portfolio-item-tags',
							'type'     => 'switch',
							'title'    => __( 'Show Tags', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Activate this to show tags on the single portfolio page.', 'bakery_options' ),
							'on'       => __( 'On', 'bakery_options' ),
							'off'      => __( 'Off', 'bakery_options' ),
							'default'  => true,
						),
						array(
							'id'       => 'portfolio-item-link',
							'type'     => 'checkbox',
							'title'    => __( 'Portfolio Item Link', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Select portfolio item link options.', 'bakery_options' ),
							'options'  => array(
								'icon-link' => __('Show Link Icon', 'bakery_options'),
								'title-link' => __('Show Link From Title', 'bakery_options')
							),
							'default'  => array(
								'icon-link' => '1',
								'title-link' => '1'
							)
						),
						array(
							'id'       => 'portfolio-item-price',
							'type'     => 'switch',
							'title'    => __( 'Show Price', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Activate this to show the portfolio items prices.', 'bakery_options' ),
							'on'       => __( 'On', 'bakery_options' ),
							'off'      => __( 'Off', 'bakery_options' ),
							'default'  => true
						),
						array(
							'id'       => 'portfolio-item-currency',
							'type'     => 'text',
							'required' => array( 'portfolio-item-price', '=', true ),
							'title'    => __( 'Currency', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Select default currency for portfolio items prices.', 'bakery_options' ),
							'default'  => '$'
						),
						//Ribbon #1
						array(
							'id'       => 'ribbon-name-1',
							'type'     => 'text',
							'required' => array( 'portfolio-item-ribbons', '>=', 1 ),
							'title'    => __( 'Ribbon Text #1', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Enter text for ribbon #1.', 'bakery_options' ),
							'default'  => __( 'New', 'bakery_options' )
						),
						//Ribbon #2
						array(
							'id'       => 'ribbon-name-2',
							'type'     => 'text',
							'required' => array( 'portfolio-item-ribbons', '>=', 2 ),
							'title'    => __( 'Ribbon Text #2', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Enter text for ribbon #2.', 'bakery_options' ),
							'default'  => ''
						),
						//Ribbon #3
						array(
							'id'       => 'ribbon-name-3',
							'type'     => 'text',
							'required' => array( 'portfolio-item-ribbons', '>=', 3 ),
							'title'    => __( 'Ribbon Text #3', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Enter text for ribbon #3', 'bakery_options' ),
							'default'  => ''
						),
						//Ribbon #4
						array(
							'id'       => 'ribbon-name-4',
							'type'     => 'text',
							'required' => array( 'portfolio-item-ribbons', '>=', 4 ),
							'title'    => __( 'Ribbon Text #4', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Enter text for ribbon #4.', 'bakery_options' ),
							'default'  => ''
						),
						//Ribbon #5
						array(
							'id'       => 'ribbon-name-5',
							'type'     => 'text',
							'required' => array( 'portfolio-item-ribbons', '>=', 5 ),
							'title'    => __( 'Ribbon Text #5', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Enter text for ribbon #5.', 'bakery_options' ),
							'default'  => ''
						),
						//Ribbon #6
						array(
							'id'       => 'ribbon-name-6',
							'type'     => 'text',
							'required' => array( 'portfolio-item-ribbons', '>=', 6 ),
							'title'    => __( 'Ribbon Text #6', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Enter text for ribbon #6.', 'bakery_options' ),
							'default'  => ''
						),
						//Ribbon #7
						array(
							'id'       => 'ribbon-name-7',
							'type'     => 'text',
							'required' => array( 'portfolio-item-ribbons', '>=', 7 ),
							'title'    => __( 'Ribbon Text #7', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Enter text for ribbon #7.', 'bakery_options' ),
							'default'  => ''
						),
						//Ribbon #8
						array(
							'id'       => 'ribbon-name-8',
							'type'     => 'text',
							'required' => array( 'portfolio-item-ribbons', '>=', 8 ),
							'title'    => __( 'Ribbon Text #8', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Enter text for ribbon #8.', 'bakery_options' ),
							'default'  => ''
						),
						//Ribbon #9
						array(
							'id'       => 'ribbon-name-9',
							'type'     => 'text',
							'required' => array( 'portfolio-item-ribbons', '>=', 9 ),
							'title'    => __( 'Ribbon Text #9', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Enter text for ribbon #9.', 'bakery_options' ),
							'default'  => ''
						),
						//Ribbon #10
						array(
							'id'       => 'ribbon-name-10',
							'type'     => 'text',
							'required' => array( 'portfolio-item-ribbons', '>=', 10 ),
							'title'    => __( 'Ribbon Text #10', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Enter text for ribbon #10.', 'bakery_options' ),
							'default'  => ''
						),
						//Number of Ribbons
						array(
							'id'       => 'portfolio-item-ribbons',
							'type'     => 'spinner',
							'title'    => __('Number of Ribbons', 'bakery_options' ),
							'desc'     => __('Want more ribbons?', 'bakery_options' ),
							'default'  => '1',
							'min'      => '1',
							'step'     => '1',
							'max'      => '10',
						),
						array(
							'id'       => 'portfolio-item-slug',
							'type'     => 'text',
							'title'    => __( 'Portfolio Item Slug', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Enter a custom portfolio item slug', 'bakery_options' ),
							'default'  => 'portfolio-item'
						),
						array(
							'id'       => 'portfolio-category-slug',
							'type'     => 'text',
							'title'    => __( 'Portfolio Category Slug', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Enter a custom portfolio category slug', 'bakery_options' ),
							'default'  => 'portfolio-category'
						),
						array(
							'id'       => 'portfolio-tag-slug',
							'type'     => 'text',
							'title'    => __( 'Portfolio Tag Slug', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Enter a custom portfolio tag slug', 'bakery_options' ),
							'default'  => 'portfolio-tag'
						),
						array(
							'id'       => 'related-portfolio-items',
							'type'     => 'switch',
							'title'    => __( 'Related Portfolio Items', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Activate this to show related portfolio items on the single portfolio item page.', 'bakery_options' ),
							'on'       => __( 'On', 'bakery_options' ),
							'off'      => __( 'Off', 'bakery_options' ),
							'default'  => true,
						),
						array(
							'id'       => 'related-portfolio-items-count',
							'type'     => 'spinner',
							'required' => array( 'related-portfolio-items', '=', true ),
							'title'    => __('Number of Related Portfolio Items', 'bakery_options' ),
							'desc'     => __('Select number of portfolio items to be shown as related portfolio items.', 'bakery_options' ),
							'default'  => '8',
							'min'      => '1',
							'step'     => '1',
							'max'      => '20',
						),
						array(
							'id'       => 'related-portfolio-items-title',
							'type'     => 'text',
							'required' => array( 'related-portfolio-items', '=', true ),
							'title'    => __( 'Related Portfolio Items Title', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Select default title for related portfolio items.', 'bakery_options' ),
							'default'  => __( 'Related Portfolio Items', 'bakery_options')
						),
						array(
							'id'       => 'related-portfolio-items-subtitle',
							'type'     => 'text',
							'required' => array( 'related-portfolio-items', '=', true ),
							'title'    => __( 'Related Portfolio Items Subtitle', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Select default subtitle for related portfolio items.', 'bakery_options' ),
							'default'  => __( 'Check out some of our similar portfolio items', 'bakery' )
						)
					)
				);
				
				//Order Form
				$this->sections[] = array(
					'title'  => __( 'Order Form', 'bakery_options' ),
					'desc'   => __( '', 'bakery_options' ),
					'icon'   => 'fa fa-credit-card',
					'fields' => array(
						array(
							'id'       => 'portfolio-item-ordering',
							'type'     => 'switch',
							'title'    => __( 'Ordering', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Activate this to enable ordering. Note: Order link on portfolio item hover and order form inside it will be shown.', 'bakery_options' ),
							'on'       => __( 'On', 'bakery_options' ),
							'off'      => __( 'Off', 'bakery_options' ),
							'default'  => true,
						),
						array(
							'id'       => 'order-recipient',
							'type'     => 'text',
							'required' => array( 'portfolio-item-ordering', '=', true ),
							'title'    => __( 'To', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Where to send order?', 'bakery_options' ),
							'validate' => 'email',
							'default'  => $admin_email
						),
						array(
							'id'       => 'order-subject',
							'type'     => 'text',
							'required' => array( 'portfolio-item-ordering', '=', true ),
							'title'    => __( 'Subject', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Order subject?', 'bakery_options' ),
							'default'  => __( 'New order from:', 'bakery_options' ) .' [name]'
						),
						array(
							'id'       => 'order-body',
							'type'     => 'textarea',
							'required' => array( 'portfolio-item-ordering', '=', true ),
							'title'    => __( 'Message body', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Order email structure and content.', 'bakery_options' ),
							'rows'     => 15,
							'default'  => "From: [name] <[email]>\nSubject: [subject]\n\nPhone: [phone]\nLocation: [location]\nDate: [date]\nTime: [time]\nProduct(s): [products]\n\nFuther details:\n[message]\n\n--\nThis e-mail was sent from an order form on ". get_bloginfo("name") ." (". get_home_url() .")"
						),
						array(
							'id'       => 'order-msg-sent-ok',
							'type'     => 'text',
							'required' => array( 'portfolio-item-ordering', '=', true ),
							'title'    => __( 'Order was sent successfully', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( '', 'bakery_options' ),
							'default'  => __( 'Your order has been sent successfully.', 'bakery_options' )
						),
						array(
							'id'       => 'order-msg-sent-ng',
							'type'     => 'text',
							'required' => array( 'portfolio-item-ordering', '=', true ),
							'title'    => __( 'Order was failed to send', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( '', 'bakery_options' ),
							'default'  => __( 'Failed to send your order. Please try again.', 'bakery_options' )
						),
						array(
							'id'       => 'order-msg-validation-error',
							'type'     => 'text',
							'required' => array( 'portfolio-item-ordering', '=', true ),
							'title'    => __( 'Validation errors occurred', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( '', 'bakery_options' ),
							'default'  => __( 'Validation errors occurred. Please confirm the fields and submit it again.', 'bakery_options' )
						),
						array(
							'id'       => 'order-msg-invalid-required',
							'type'     => 'text',
							'required' => array( 'portfolio-item-ordering', '=', true ),
							'title'    => __( 'There are some fields that the sender must fill in', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( '', 'bakery_options' ),
							'default'  => __( 'Please fill in all the required fields.', 'bakery_options' )
						)
					)
				);
				
				if( in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) ) {
					//Products
					$this->sections[] = array(
						'title'  => __( 'Shop', 'bakery_options' ),
						'desc'   => __( '', 'bakery_options' ),
						'icon'   => 'fa fa-shopping-cart',
						'fields' => array(
							array(
								'id'       => 'shop-layout',
								'type'     => 'image_select',
								'title'    => __( 'Layout', 'bakery_options' ),
								'subtitle' => __( '', 'bakery_options' ),
								'desc' => __( 'Select layout for shop pages. The option selected here will apply also for search, category and tag pages.', 'bakery_options' ),
								'options'  => array(
									'left-sidebar' => array(
										'alt' => 'Left Sidebar',
										'img' => ReduxFramework::$_url .'assets/img/left-sidebar.png'
									),
									'right-sidebar' => array(
										'alt' => 'Right Sidebar',
										'img' => ReduxFramework::$_url .'assets/img/right-sidebar.png'
									),
									'no-sidebar' => array(
										'alt' => 'Without Sidebar',
										'img' => ReduxFramework::$_url .'assets/img/no-sidebar.png'
									)
								),
								'default'  => 'right-sidebar'
							),
							array(
								'id'       => 'product-count',
								'type'     => 'spinner',
								'title'    => __('Number of products', 'bakery_options' ),
								'desc'     => __('Select number of products per page.', 'bakery_options' ),
								'default'  => '12',
								'min'      => '1',
								'step'     => '1',
								'max'      => '50',
							),
							array(
								'id'       => 'wc-cat-header-bg',
								'type'     => 'button_set',
								'title'    => __( 'Category Header Background', 'bakery_options' ),
								'subtitle' => __( '', 'bakery_options' ),
								'desc'     => __( 'Select from where to get category header background.', 'bakery_options' ),
								'options'  => array(
									'cat-thumbnail' => 'Category Thumbnail',
									'shop-page-header-bg' => 'Shop Page Header Background'
								),
								'default'  => 'cat-thumbnail'
							),
							array(
								'id'       => 'shop-show-basket-icon',
								'type'     => 'switch',
								'title'    => __( 'Show Basket Icon', 'bakery_options' ),
								'subtitle' => __( '', 'bakery_options' ),
								'desc'     => __( 'Activate this to show basket icon in main menu.', 'bakery_options' ),
								'on'       => __( 'On', 'bakery_options' ),
								'off'      => __( 'Off', 'bakery_options' ),
								'default'  => true,
							),
							array(
								'id'       => 'product-social',
								'type'     => 'switch',
								'title'    => __( 'Social Media Sharing Buttons', 'bakery_options' ),
								'subtitle' => __( '', 'bakery_options' ),
								'desc'     => __( 'Activate this to enable social sharing buttons on the single product page.', 'bakery_options' ),
								'on'       => __( 'On', 'bakery_options' ),
								'off'      => __( 'Off', 'bakery_options' ),
								'default'  => true,
							),
							array(
								'id'       => 'product-social-networks',
								'type'     => 'checkbox',
								'required' => array( 'product-social', '=', true ),
								'title'    => __( 'Social Networks', 'bakery_options' ),
								'subtitle' => __( '', 'bakery_options' ),
								'desc'     => __( 'Select social networks to be shown in the single product page.', 'bakery_options' ),
								'options'  => array(
									'facebook' => 'Facebook',
									'twitter' => 'Twitter',
									'google-plus' => 'Google+',
									'pinterest' => 'Pinterest',
									'linkedin' => 'LinkedIn'
								),
								'default'  => array(
									'facebook' => '1',
									'twitter' => '1',
									'google-plus' => '1',
									'pinterest' => '1',
									'linkedin' => '0'
								)
							),
							array(
								'id'       => 'related-products',
								'type'     => 'switch',
								'title'    => __( 'Related Products', 'bakery_options' ),
								'subtitle' => __( '', 'bakery_options' ),
								'desc'     => __( 'Activate this to show related products on the single product page.', 'bakery_options' ),
								'on'       => __( 'On', 'bakery_options' ),
								'off'      => __( 'Off', 'bakery_options' ),
								'default'  => true,
							),
							array(
								'id'       => 'related-products-items-count',
								'type'     => 'spinner',
								'required' => array( 'related-products', '=', true ),
								'title'    => __('Number of Related Products Items', 'bakery_options' ),
								'desc'     => __('Select number of products items to be shown as related products items.', 'bakery_options' ),
								'default'  => '4',
								'min'      => '1',
								'step'     => '1',
								'max'      => '20',
							),
							array(
								'id'       => 'related-products-title',
								'type'     => 'text',
								'required' => array( 'related-products', '=', true ),
								'title'    => __( 'Related Products Title', 'bakery_options' ),
								'subtitle' => __( '', 'bakery_options' ),
								'desc'     => __( 'Enter default title for related products.', 'bakery_options' ),
								'default'  => __( 'Related Products', 'bakery_options')
							),
							array(
								'id'       => 'related-products-subtitle',
								'type'     => 'text',
								'required' => array( 'related-products', '=', true ),
								'title'    => __( 'Related Products Subtitle', 'bakery_options' ),
								'subtitle' => __( '', 'bakery_options' ),
								'desc'     => __( 'Enter default subtitle for related products.', 'bakery_options' ),
								'default'  => __( 'Check out some of our similar products', 'bakery' )
							)
						)
					);
				}
				
				//Contact
				$this->sections[] = array(
					'title'  => __( 'Contact', 'bakery_options' ),
					'desc'   => __('Please find here all the options for cofiguring the contact form.', 'bakery_options' ),
					'icon'   => 'fa fa-envelope-o',
					'fields' => array(
						array(
							'id'       => 'mail-recipient',
							'type'     => 'text',
							'title'    => __( 'To', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( '', 'bakery_options' ),
							'validate' => 'email',
							'default'  => $admin_email
						),
						array(
							'id'       => 'mail-sender',
							'type'     => 'text',
							'title'    => __( 'From', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( '', 'bakery_options' ),
							'default'  => '[name] <'. $admin_email .'>'
						),
						array(
							'id'       => 'mail-subject',
							'type'     => 'text',
							'title'    => __( 'Subject', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( '', 'bakery_options' ),
							'default'  => '[subject]'
						),
						array(
							'id'       => 'mail-additional-headers',
							'type'     => 'textarea',
							'title'    => __( 'Additional headers', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( '', 'bakery_options' ),
							'rows'     => 3,
							'default'  => 'Reply-To: [email]'
						),
						array(
							'id'       => 'mail-use-html',
							'type'     => 'checkbox',
							'title'    => __( 'Use HTML content type', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( '', 'bakery_options' ),
							'default'  => '0'
						),
						array(
							'id'       => 'mail-body',
							'type'     => 'textarea',
							'title'    => __( 'Message body', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( '', 'bakery_options' ),
							'rows'     => 8,
							'default'  => "From: [name] <[email]>\nSubject: [subject]\n\nMessage Body:\n[message]\n\n--\nThis e-mail was sent from a contact form on ". get_bloginfo("name") ." (". get_home_url() .")"
						),
						array(
							'id'       => 'msg-mail-sent-ok',
							'type'     => 'text',
							'title'    => __( 'Sender\'s message was sent successfully', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( '', 'bakery_options' ),
							'default'  => __( 'Your message has been sent successfully.', 'bakery_options' )
						),
						array(
							'id'       => 'msg-mail-sent-ng',
							'type'     => 'text',
							'title'    => __( 'Sender\'s message was failed to send', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( '', 'bakery_options' ),
							'default'  => __( 'Failed to send your message. Please try again.', 'bakery_options' )
						),
						array(
							'id'       => 'msg-validation-error',
							'type'     => 'text',
							'title'    => __( 'Validation errors occurred', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( '', 'bakery_options' ),
							'default'  => __( 'Validation errors occurred. Please confirm the fields and submit it again.', 'bakery_options' )
						),
						array(
							'id'       => 'msg-invalid-required',
							'type'     => 'text',
							'title'    => __( 'There are some fields that the sender must fill in', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( '', 'bakery_options' ),
							'default'  => __( 'Please fill in all the required fields.', 'bakery_options' )
						)
					)
				);
				
				//Map
				$this->sections[] = array(
					'title'  => __( 'Map', 'bakery_options' ),
					'desc'   => __('Please find here all the map options. To convert an address into latitude & longitude please use <a href="http://www.latlong.net/convert-address-to-lat-long.html">this converter.</a>', 'bakery_options' ),
					'icon'   => 'fa fa-map-marker',
					'fields' => array(
						array(
							'id'       => 'center-lat',
							'type'     => 'text',
							'title'    => __( 'Map Center Latitude', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Enter latitude for the map center point.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'center-lng',
							'type'     => 'text',
							'title'    => __( 'Map Center Longitude', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Enter longitude for the map center point.', 'bakery_options' ),
							'default'  => ''
						),
						array(
							'id'       => 'zoom-level',
							'type'     => 'text',
							'title'    => __( 'Default Map Zoom Level', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __('Enter default map zoom level. Note: Value should be between 1-18, 1 being the entire earth and 18 being right at street level.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'map-height',
							'type'     => 'text',
							'title'    => __( 'Map Height', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Enter map container height in px.', 'bakery_options' ),
							'validate' => 'numeric',
							'default'  => '580'
						),
						array(
							'id'       => 'map-type',
							'type'     => 'select',
							'title'    => __( 'Map Type', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Select map type.', 'bakery_options' ),
							'options'  => array(
								"roadmap" => __("Roadmap", 'bakery_options'),
								"satellite" => __("Satellite", 'bakery_options'),
								"hybrid" => __("Hybrid", 'bakery_options'),
								"terrain" => __("Terrain", 'bakery_options')
							),
							'default'  => ''
						),
						array(
							'id'       => 'map-style',
							'type'     => 'select_image',
							'required' => array( 'map-type', '=', 'roadmap' ),
							'title'    => __( 'Map Style', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc' => __( 'Select how you want map to look like.', 'bakery_options' ),
							'compiler' => true,
							'options'  => array(
								"1" => array(
									'alt' => 'Subtle Grayscale',
									'img' => ReduxFramework::$_url .'assets/img/map-styles/15-subtle-grayscale.png#1'
								),
								"2" => array(
									'alt' => 'Blue Water',
									'img' => ReduxFramework::$_url .'assets/img/map-styles/25-blue-water.png#2'
								),
								"3" => array(
									'alt' => 'Shades of Grey',
									'img' => ReduxFramework::$_url .'assets/img/map-styles/38-shades-of-grey.png#3'
								),
								"4" => array(
									'alt' => 'Pale Dawn',
									'img' => ReduxFramework::$_url .'assets/img/map-styles/1-pale-dawn.png#4'
								),
								"5" => array(
									'alt' => 'Apple Maps-esque',
									'img' => ReduxFramework::$_url .'assets/img/map-styles/42-apple-maps-esque.png#5'
								),
								"6" => array(
									'alt' => 'Light Monochrome',
									'img' => ReduxFramework::$_url .'assets/img/map-styles/29-light-monochrome.png#6'
								),
								"7" => array(
									'alt' => 'Greyscale',
									'img' => ReduxFramework::$_url .'assets/img/map-styles/5-greyscale.png#7'
								),
								"8" => array(
									'alt' => 'Neutral Blue',
									'img' => ReduxFramework::$_url .'assets/img/map-styles/13-neutral-blue.png#8'
								),
								"9" => array(
									'alt' => 'Become a Dinosaur',
									'img' => ReduxFramework::$_url .'assets/img/map-styles/74-becomeadinosaur.png#9'
								),
								"10" => array(
									'alt' => 'Blue Gray',
									'img' => ReduxFramework::$_url .'assets/img/map-styles/60-blue-gray.png#10'
								),
								"11" => array(
									'alt' => 'Icy Blue',
									'img' => ReduxFramework::$_url .'assets/img/map-styles/7-icy-blue.png#11'
								),
								"12" => array(
									'alt' => 'Clean Cut',
									'img' => ReduxFramework::$_url .'assets/img/map-styles/77-clean-cut.png#12'
								),
								"13" => array(
									'alt' => 'Muted Blue',
									'img' => ReduxFramework::$_url .'assets/img/map-styles/83-muted-blue.png#13'
								),
								"14" => array(
									'alt' => 'Old Timey',
									'img' => ReduxFramework::$_url .'assets/img/map-styles/22-old-timey.png#14'
								),
								"15" => array(
									'alt' => 'Red Hues',
									'img' => ReduxFramework::$_url .'assets/img/map-styles/31-red-hues.png#15'
								),
								"16" => array(
									'alt' => 'Nature',
									'img' => ReduxFramework::$_url .'assets/img/map-styles/47-nature.png#16'
								),
								"17" => array(
									'alt' => 'Turquoise Water',
									'img' => ReduxFramework::$_url .'assets/img/map-styles/8-turquoise-water.png#17'
								),
								"18" => array(
									'alt' => 'Just Places',
									'img' => ReduxFramework::$_url .'assets/img/map-styles/65-just-places.png#18'
								),
								"19" => array(
									'alt' => 'Ultra Light',
									'img' => ReduxFramework::$_url .'assets/img/map-styles/151-ultra-light.png#19'
								),
								"20" => array(
									'alt' => 'Subtle Green',
									'img' => ReduxFramework::$_url .'assets/img/map-styles/49-subtle-green.png#20'
								),
								"21" => array(
									'alt' => 'Simple & Light',
									'img' => ReduxFramework::$_url .'assets/img/map-styles/229-simple-and-light.png#21'
								)
							),
							'default'  => null,
						),
						array(
							'id'       => 'map-tilt-45',
							'type'     => 'switch',
							'required' => array( 'map-type', '=', 'satellite' ),
							'title'    => __( 'Tilt 45°', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( '', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => false
						),
						array(
							'id'       => 'use-marker-img',
							'type'     => 'switch',
							'title'    => __( 'Use Image for Markers', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Use a custom map marker?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => false
						),
						array(
							'id'       => 'marker-img',
							'type'     => 'media',
							'required' => array( 'use-marker-img', '=', true ),
							'title'    => __( 'Marker Icon Upload', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Upload image that will be used as map marker.', 'bakery_options' )
						),
						array(
							'id'       => 'enable-map-animation',
							'type'     => 'switch',
							'title'    => __( 'Enable Marker Animation', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Enable marker animation? Note: This will cause marker(s) to do a quick bounce as they load in.', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => false
						),
						array(
							'id'       => 'map-others-options',
							'type'     => 'checkbox',
							'title'    => __( 'Others Options', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Select other options you want to apply on the map.', 'bakery_options' ),
							'options'  => array(
								'draggable' => __('Draggable', 'bakery_options'),
								'zoomControl' => __('Zoom Control', 'bakery_options'),
								'disableDoubleClickZoom' => __('Disable Double Click Zoom', 'bakery_options'),
								'scrollwheel' => __('Scroll Wheel', 'bakery_options'),
								'panControl' => __('Pan Control', 'bakery_options'),
								'mapTypeControl' => __('Map Type Control', 'bakery_options'),
								'scaleControl' => __('Scale Control', 'bakery_options'),
								'streetViewControl' => __('Street View Control', 'bakery_options')
							),
							'default' => array(
								'draggable' => true
							)
						),

						// ***** Map Point 1 ***** //
						array(
							'id'       => 'map-point-1',
							'type'     => 'switch',
							'title'    => __('Location #1', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Show location #1?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => false
						),
						array(
							'id'       => 'latitude1',
							'type'     => 'text',
							'required' => array( 'map-point-1', '=', true ),
							'title'    => __('Latitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter longitude for location #1.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'longitude1',
							'type'     => 'text',
							'required' => array( 'map-point-1', '=', true ),
							'title'    => __('Longitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter latitude for location #1.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'map-info1',
							'type'     => 'textarea',
							'required' => array( 'map-point-1', '=', true ),
							'title'    => __('Map Info Window Text', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('If you would like to display any text in an info window for your #1 location, please enter it here.', 'bakery_options' )
						),

						// ***** Map Point 2 ***** //
						array(
							'id'       => 'map-point-2',
							'type'     => 'switch',
							'required' => array( 'number-of-locations', '>=', 2 ),
							'title'    => __('Location #2', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Show location #2?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => false
						),
						array(
							'id'       => 'latitude2',
							'type'     => 'text',
							'required' => array( 'map-point-2', '=', true ),
							'title'    => __('Latitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter longitude for location #2.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'longitude2',
							'type'     => 'text',
							'required' => array( 'map-point-2', '=', true ),
							'title'    => __('Longitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter latitude for location #2.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'map-info2',
							'type'     => 'textarea',
							'required' => array( 'map-point-2', '=', true ),
							'title'    => __('Map Info Window Text', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('If you would like to display any text in an info window for your #2 location, please enter it here.', 'bakery_options' )
						),

						// ***** Map Point 3 ***** //
						array(
							'id'       => 'map-point-3',
							'type'     => 'switch',
							'required' => array( 'number-of-locations', '>=', 3 ),
							'title'    => __('Location #3', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Show location #3?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => false
						),
						array(
							'id'       => 'latitude3',
							'type'     => 'text',
							'required' => array( 'map-point-3', '=', true ),
							'title'    => __('Latitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter longitude for location #3.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'longitude3',
							'type'     => 'text',
							'required' => array( 'map-point-3', '=', true ),
							'title'    => __('Longitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter latitude for location #3.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'map-info3',
							'type'     => 'textarea',
							'required' => array( 'map-point-3', '=', true ),
							'title'    => __('Map Info Window Text', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('If you would like to display any text in an info window for your #3 location, please enter it here.', 'bakery_options' )
						),

						// ***** Map Point 4 ***** //
						array(
							'id'       => 'map-point-4',
							'type'     => 'switch',
							'required' => array( 'number-of-locations', '>=', 4 ),
							'title'    => __('Location #4', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Show location #4?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => false
						),
						array(
							'id'       => 'latitude4',
							'type'     => 'text',
							'required' => array( 'map-point-4', '=', true ),
							'title'    => __('Latitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter longitude for location #4.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'longitude4',
							'type'     => 'text',
							'required' => array( 'map-point-4', '=', true ),
							'title'    => __('Longitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter latitude for location #4.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'map-info4',
							'type'     => 'textarea',
							'required' => array( 'map-point-4', '=', true ),
							'title'    => __('Map Info Window Text', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('If you would like to display any text in an info window for your #4 location, please enter it here.', 'bakery_options' )
						),

						// ***** Map Point 5 ***** //
						array(
							'id'       => 'map-point-5',
							'type'     => 'switch',
							'required' => array( 'number-of-locations', '>=', 5 ),
							'title'    => __('Location #5', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Show location #5?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => false
						),
						array(
							'id'       => 'latitude5',
							'type'     => 'text',
							'required' => array( 'map-point-5', '=', true ),
							'title'    => __('Latitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter longitude for location #5.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'longitude5',
							'type'     => 'text',
							'required' => array( 'map-point-5', '=', true ),
							'title'    => __('Longitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter latitude for location #5.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'map-info5',
							'type'     => 'textarea',
							'required' => array( 'map-point-5', '=', true ),
							'title'    => __('Map Info Window Text', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('If you would like to display any text in an info window for your #5 location, please enter it here.', 'bakery_options' )
						),

						// ***** Map Point 6 ***** //
						array(
							'id'       => 'map-point-6',
							'type'     => 'switch',
							'required' => array( 'number-of-locations', '>=', 6 ),
							'title'    => __('Location #6', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Show location #6?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => false
						),
						array(
							'id'       => 'latitude6',
							'type'     => 'text',
							'required' => array( 'map-point-6', '=', true ),
							'title'    => __('Latitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter latitude for location #6.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'longitude6',
							'type'     => 'text',
							'required' => array( 'map-point-6', '=', true ),
							'title'    => __('Longitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter longitude for location #6.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'map-info6',
							'type'     => 'textarea',
							'required' => array( 'map-point-6', '=', true ),
							'title'    => __('Map Info Window Text', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('If you would like to display any text in an info window for your #6 location, please enter it here.', 'bakery_options' )
						),

						// ***** Map Point 7 ***** //
						array(
							'id'       => 'map-point-7',
							'type'     => 'switch',
							'required' => array( 'number-of-locations', '>=', 7 ),
							'title'    => __('Location #7', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Show location #7?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => false
						),
						array(
							'id'       => 'latitude7',
							'type'     => 'text',
							'required' => array( 'map-point-7', '=', true ),
							'title'    => __('Latitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter latitude for location #7.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'longitude7',
							'type'     => 'text',
							'required' => array( 'map-point-7', '=', true ),
							'title'    => __('Longitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter longitude for location #7.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'map-info7',
							'type'     => 'textarea',
							'required' => array( 'map-point-7', '=', true ),
							'title'    => __('Map Info Window Text', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('If you would like to display any text in an info window for your #7 location, please enter it here.', 'bakery_options' )
						),

						// ***** Map Point 8 ***** //
						array(
							'id'       => 'map-point-8',
							'type'     => 'switch',
							'required' => array( 'number-of-locations', '>=', 8 ),
							'title'    => __('Location #8', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Show location #8?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => false
						),
						array(
							'id'       => 'latitude8',
							'type'     => 'text',
							'required' => array( 'map-point-8', '=', true ),
							'title'    => __('Latitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter latitude for location #8.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'longitude8',
							'type'     => 'text',
							'required' => array( 'map-point-8', '=', true ),
							'title'    => __('Longitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter longitude for location #8.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'map-info8',
							'type'     => 'textarea',
							'required' => array( 'map-point-8', '=', true ),
							'title'    => __('Map Info Window Text', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('If you would like to display any text in an info window for your #8 location, please enter it here.', 'bakery_options' )
						),

						// ***** Map Point 9 ***** //
						array(
							'id'       => 'map-point-9',
							'type'     => 'switch',
							'required' => array( 'number-of-locations', '>=', 9 ),
							'title'    => __('Location #9', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Show location #9?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => false
						),
						array(
							'id'       => 'latitude9',
							'type'     => 'text',
							'required' => array( 'map-point-9', '=', true ),
							'title'    => __('Latitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter latitude for location #9.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'longitude9',
							'type'     => 'text',
							'required' => array( 'map-point-9', '=', true ),
							'title'    => __('Longitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter longitude for location #9.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'map-info9',
							'type'     => 'textarea',
							'required' => array( 'map-point-9', '=', true ),
							'title'    => __('Map Info Window Text', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('If you would like to display any text in an info window for your #9 location, please enter it here.', 'bakery_options' )
						),

						// ***** Map Point 10 ***** //
						array(
							'id'       => 'map-point-10',
							'type'     => 'switch',
							'required' => array( 'number-of-locations', '>=', 10 ),
							'title'    => __('Location #10', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Show location #10?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => false
						),
						array(
							'id'       => 'latitude10',
							'type'     => 'text',
							'required' => array( 'map-point-10', '=', true ),
							'title'    => __('Latitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter latitude for location #10.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'longitude10',
							'type'     => 'text',
							'required' => array( 'map-point-10', '=', true ),
							'title'    => __('Longitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter longitude for location #10.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'map-info10',
							'type'     => 'textarea',
							'required' => array( 'map-point-10', '=', true ),
							'title'    => __('Map Info Window Text', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('If you would like to display any text in an info window for your #10 location, please enter it here.', 'bakery_options' )
						),

						// ***** Map Point 11 ***** //
						array(
							'id'       => 'map-point-11',
							'type'     => 'switch',
							'required' => array( 'number-of-locations', '>=', 11 ),
							'title'    => __('Location #11', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Show location #11?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => false
						),
						array(
							'id'       => 'latitude11',
							'type'     => 'text',
							'required' => array( 'map-point-11', '=', true ),
							'title'    => __('Latitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter latitude for location #11.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'longitude11',
							'type'     => 'text',
							'required' => array( 'map-point-11', '=', true ),
							'title'    => __('Longitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter longitude for location #11.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'map-info11',
							'type'     => 'textarea',
							'required' => array( 'map-point-11', '=', true ),
							'title'    => __('Map Info Window Text', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('If you would like to display any text in an info window for your 11th location, please enter it here.', 'bakery_options' )
						),

						// ***** Map Point 12 ***** //
						array(
							'id'       => 'map-point-12',
							'type'     => 'switch',
							'required' => array( 'number-of-locations', '>=', 12 ),
							'title'    => __('Location #12', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Show location #12?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => false
						),
						array(
							'id'       => 'latitude12',
							'type'     => 'text',
							'required' => array( 'map-point-12', '=', true ),
							'title'    => __('Latitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter latitude for location #12.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'longitude12',
							'type'     => 'text',
							'required' => array( 'map-point-12', '=', true ),
							'title'    => __('Longitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter longitude for location #12.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'map-info12',
							'type'     => 'textarea',
							'required' => array( 'map-point-12', '=', true ),
							'title'    => __('Map Info Window Text', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('If you would like to display any text in an info window for your 12th location, please enter it here.', 'bakery_options' )
						),

						// ***** Map Point 13 ***** //
						array(
							'id'       => 'map-point-13',
							'type'     => 'switch',
							'required' => array( 'number-of-locations', '>=', 13 ),
							'title'    => __('Location #13', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Show location #13?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => false
						),
						array(
							'id'       => 'latitude13',
							'type'     => 'text',
							'required' => array( 'map-point-13', '=', true ),
							'title'    => __('Latitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter latitude for location #13.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'longitude13',
							'type'     => 'text',
							'required' => array( 'map-point-13', '=', true ),
							'title'    => __('Longitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter longitude for location #13.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'map-info13',
							'type'     => 'textarea',
							'required' => array( 'map-point-13', '=', true ),
							'title'    => __('Map Info Window Text', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('If you would like to display any text in an info window for your 13th location, please enter it here.', 'bakery_options' )
						),

						// ***** Map Point 14 ***** //
						array(
							'id'       => 'map-point-14',
							'type'     => 'switch',
							'required' => array( 'number-of-locations', '>=', 14 ),
							'title'    => __('Location #14', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Show location #14?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => false
						),
						array(
							'id'       => 'latitude14',
							'type'     => 'text',
							'required' => array( 'map-point-14', '=', true ),
							'title'    => __('Latitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter latitude for location #14.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'longitude14',
							'type'     => 'text',
							'required' => array( 'map-point-14', '=', true ),
							'title'    => __('Longitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter longitude for location #14.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'map-info14',
							'type'     => 'textarea',
							'required' => array( 'map-point-14', '=', true ),
							'title'    => __('Map Info Window Text', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('If you would like to display any text in an info window for your 14th location, please enter it here.', 'bakery_options' )
						),

						// ***** Map Point 15 ***** //
						array(
							'id'       => 'map-point-15',
							'type'     => 'switch',
							'required' => array( 'number-of-locations', '>=', 15 ),
							'title'    => __('Location #15', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Show location #15?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => false
						),
						array(
							'id'       => 'latitude15',
							'type'     => 'text',
							'required' => array( 'map-point-15', '=', true ),
							'title'    => __('Latitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter latitude for location #15.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'longitude15',
							'type'     => 'text',
							'required' => array( 'map-point-15', '=', true ),
							'title'    => __('Longitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter longitude for location #15.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'map-info15',
							'type'     => 'textarea',
							'required' => array( 'map-point-15', '=', true ),
							'title'    => __('Map Info Window Text', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('If you would like to display any text in an info window for your 15th location, please enter it here.', 'bakery_options' )
						),

						// ***** Map Point 16 ***** //
						array(
							'id'       => 'map-point-16',
							'type'     => 'switch',
							'required' => array( 'number-of-locations', '>=', 16 ),
							'title'    => __('Location #16', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Show location #16?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => false
						),
						array(
							'id'       => 'latitude16',
							'type'     => 'text',
							'required' => array( 'map-point-16', '=', true ),
							'title'    => __('Latitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter latitude for location #16.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'longitude16',
							'type'     => 'text',
							'required' => array( 'map-point-16', '=', true ),
							'title'    => __('Longitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter longitude for location #16.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'map-info16',
							'type'     => 'textarea',
							'required' => array( 'map-point-16', '=', true ),
							'title'    => __('Map Info Window Text', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('If you would like to display any text in an info window for your 16th location, please enter it here.', 'bakery_options' )
						),

						// ***** Map Point 17 ***** //
						array(
							'id'       => 'map-point-17',
							'type'     => 'switch',
							'required' => array( 'number-of-locations', '>=', 17 ),
							'title'    => __('Location #17', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Show location #17?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => false
						),
						array(
							'id'       => 'latitude17',
							'type'     => 'text',
							'required' => array( 'map-point-17', '=', true ),
							'title'    => __('Latitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter latitude for location #17.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'longitude17',
							'type'     => 'text',
							'required' => array( 'map-point-17', '=', true ),
							'title'    => __('Longitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter longitude for location #17.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'map-info17',
							'type'     => 'textarea',
							'required' => array( 'map-point-17', '=', true ),
							'title'    => __('Map Info Window Text', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('If you would like to display any text in an info window for your 17th location, please enter it here.', 'bakery_options' )
						),

						// ***** Map Point 18 ***** //
						array(
							'id'       => 'map-point-18',
							'type'     => 'switch',
							'required' => array( 'number-of-locations', '>=', 18 ),
							'title'    => __('Location #18', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Show location #18?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => false
						),
						array(
							'id'       => 'latitude18',
							'type'     => 'text',
							'required' => array( 'map-point-18', '=', true ),
							'title'    => __('Latitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter latitude for location #18.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'longitude18',
							'type'     => 'text',
							'required' => array( 'map-point-18', '=', true ),
							'title'    => __('Longitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter longitude for location #18.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'map-info18',
							'type'     => 'textarea',
							'required' => array( 'map-point-18', '=', true ),
							'title'    => __('Map Info Window Text', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('If you would like to display any text in an info window for your 18th location, please enter it here.', 'bakery_options' )
						),

						// ***** Map Point 19 ***** //
						array(
							'id'       => 'map-point-19',
							'type'     => 'switch',
							'required' => array( 'number-of-locations', '>=', 19 ),
							'title'    => __('Location #19', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Show location #19?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => false
						),
						array(
							'id'       => 'latitude19',
							'type'     => 'text',
							'required' => array( 'map-point-19', '=', true ),
							'title'    => __('Latitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter latitude for location #19.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'longitude19',
							'type'     => 'text',
							'required' => array( 'map-point-19', '=', true ),
							'title'    => __('Longitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter longitude for location #19.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'map-info19',
							'type'     => 'textarea',
							'required' => array( 'map-point-19', '=', true ),
							'title'    => __('Map Info Window Text', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('If you would like to display any text in an info window for your 19th location, please enter it here.', 'bakery_options' )
						),

						// ***** Map Point 20 ***** //
						array(
							'id'       => 'map-point-20',
							'type'     => 'switch',
							'required' => array( 'number-of-locations', '>=', 20 ),
							'title'    => __('Location #20', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Show location #20?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => false
						),
						array(
							'id'       => 'latitude20',
							'type'     => 'text',
							'required' => array( 'map-point-20', '=', true ),
							'title'    => __('Latitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter latitude for location #20.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'longitude20',
							'type'     => 'text',
							'required' => array( 'map-point-20', '=', true ),
							'title'    => __('Longitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter longitude for location #20.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'map-info20',
							'type'     => 'textarea',
							'required' => array( 'map-point-20', '=', true ),
							'title'    => __('Map Info Window Text', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('If you would like to display any text in an info window for your 20th location, please enter it here.', 'bakery_options' )
						),

						// ***** Map Point 21 ***** //
						array(
							'id'       => 'map-point-21',
							'type'     => 'switch',
							'required' => array( 'number-of-locations', '>=', 21 ),
							'title'    => __('Location #21', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Show location #21?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => false
						),
						array(
							'id'       => 'latitude21',
							'type'     => 'text',
							'required' => array( 'map-point-21', '=', true ),
							'title'    => __('Latitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter latitude for location #21.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'longitude21',
							'type'     => 'text',
							'required' => array( 'map-point-21', '=', true ),
							'title'    => __('Longitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter longitude for location #21.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'map-info21',
							'type'     => 'textarea',
							'required' => array( 'map-point-21', '=', true ),
							'title'    => __('Map Info Window Text', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('If you would like to display any text in an info window for your 21th location, please enter it here.', 'bakery_options' )
						),

						// ***** Map Point 22 ***** //
						array(
							'id'       => 'map-point-22',
							'type'     => 'switch',
							'required' => array( 'number-of-locations', '>=', 22 ),
							'title'    => __('Location #22', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Show location #22?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => false
						),
						array(
							'id'       => 'latitude22',
							'type'     => 'text',
							'required' => array( 'map-point-22', '=', true ),
							'title'    => __('Latitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter latitude for location #22.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'longitude22',
							'type'     => 'text',
							'required' => array( 'map-point-22', '=', true ),
							'title'    => __('Longitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter longitude for location #22.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'map-info22',
							'type'     => 'textarea',
							'required' => array( 'map-point-22', '=', true ),
							'title'    => __('Map Info Window Text', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('If you would like to display any text in an info window for your 22th location, please enter it here.', 'bakery_options' )
						),

						// ***** Map Point 23 ***** //
						array(
							'id'       => 'map-point-23',
							'type'     => 'switch',
							'required' => array( 'number-of-locations', '>=', 23 ),
							'title'    => __('Location #23', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Show location #23?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => false
						),
						array(
							'id'       => 'latitude23',
							'type'     => 'text',
							'required' => array( 'map-point-23', '=', true ),
							'title'    => __('Latitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter latitude for location #23.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'longitude23',
							'type'     => 'text',
							'required' => array( 'map-point-23', '=', true ),
							'title'    => __('Longitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter longitude for location #23.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'map-info23',
							'type'     => 'textarea',
							'required' => array( 'map-point-23', '=', true ),
							'title'    => __('Map Info Window Text', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('If you would like to display any text in an info window for your 23th location, please enter it here.', 'bakery_options' )
						),

						// ***** Map Point 24 ***** //
						array(
							'id'       => 'map-point-24',
							'type'     => 'switch',
							'required' => array( 'number-of-locations', '>=', 24 ),
							'title'    => __('Location #24', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Show location #24?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => false
						),
						array(
							'id'       => 'latitude24',
							'type'     => 'text',
							'required' => array( 'map-point-24', '=', true ),
							'title'    => __('Latitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter latitude for location #24.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'longitude24',
							'type'     => 'text',
							'required' => array( 'map-point-24', '=', true ),
							'title'    => __('Longitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter longitude for location #24.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'map-info24',
							'type'     => 'textarea',
							'required' => array( 'map-point-24', '=', true ),
							'title'    => __('Map Info Window Text', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('If you would like to display any text in an info window for your 24th location, please enter it here.', 'bakery_options' )
						),

						// ***** Map Point 25 ***** //
						array(
							'id'       => 'map-point-25',
							'type'     => 'switch',
							'required' => array( 'number-of-locations', '>=', 25 ),
							'title'    => __('Location #25', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Show location #25?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => false
						),
						array(
							'id'       => 'latitude25',
							'type'     => 'text',
							'required' => array( 'map-point-25', '=', true ),
							'title'    => __('Latitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter latitude for location #25.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'longitude25',
							'type'     => 'text',
							'required' => array( 'map-point-25', '=', true ),
							'title'    => __('Longitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter longitude for location #25.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'map-info25',
							'type'     => 'textarea',
							'required' => array( 'map-point-25', '=', true ),
							'title'    => __('Map Info Window Text', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('If you would like to display any text in an info window for your 25th location, please enter it here.', 'bakery_options' )
						),

						// ***** Map Point 26 ***** //
						array(
							'id'       => 'map-point-26',
							'type'     => 'switch',
							'required' => array( 'number-of-locations', '>=', 26 ),
							'title'    => __('Location #26', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Show location #26?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => false
						),
						array(
							'id'       => 'latitude26',
							'type'     => 'text',
							'required' => array( 'map-point-26', '=', true ),
							'title'    => __('Latitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter latitude for location #26.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'longitude26',
							'type'     => 'text',
							'required' => array( 'map-point-26', '=', true ),
							'title'    => __('Longitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter longitude for location #26.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'map-info26',
							'type'     => 'textarea',
							'required' => array( 'map-point-26', '=', true ),
							'title'    => __('Map Info Window Text', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('If you would like to display any text in an info window for your 26th location, please enter it here.', 'bakery_options' )
						),

						// ***** Map Point 27 ***** //
						array(
							'id'       => 'map-point-27',
							'type'     => 'switch',
							'required' => array( 'number-of-locations', '>=', 27 ),
							'title'    => __('Location #27', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Show location #27?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => false
						),
						array(
							'id'       => 'latitude27',
							'type'     => 'text',
							'required' => array( 'map-point-27', '=', true ),
							'title'    => __('Latitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter latitude for location #27.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'longitude27',
							'type'     => 'text',
							'required' => array( 'map-point-27', '=', true ),
							'title'    => __('Longitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter longitude for location #27.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'map-info27',
							'type'     => 'textarea',
							'required' => array( 'map-point-27', '=', true ),
							'title'    => __('Map Info Window Text', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('If you would like to display any text in an info window for your 27th location, please enter it here.', 'bakery_options' )
						),

						// ***** Map Point 28 ***** //
						array(
							'id'       => 'map-point-28',
							'type'     => 'switch',
							'required' => array( 'number-of-locations', '>=', 28 ),
							'title'    => __('Location #28', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Show location #28?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => false
						),
						array(
							'id'       => 'latitude28',
							'type'     => 'text',
							'required' => array( 'map-point-28', '=', true ),
							'title'    => __('Latitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter latitude for location #28.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'longitude28',
							'type'     => 'text',
							'required' => array( 'map-point-28', '=', true ),
							'title'    => __('Longitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter longitude for location #28.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'map-info28',
							'type'     => 'textarea',
							'required' => array( 'map-point-28', '=', true ),
							'title'    => __('Map Info Window Text', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('If you would like to display any text in an info window for your 28th location, please enter it here.', 'bakery_options' )
						),

						// ***** Map Point 29 ***** //
						array(
							'id'       => 'map-point-29',
							'type'     => 'switch',
							'required' => array( 'number-of-locations', '>=', 29 ),
							'title'    => __('Location #29', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Show location #29?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => false
						),
						array(
							'id'       => 'latitude29',
							'type'     => 'text',
							'required' => array( 'map-point-29', '=', true ),
							'title'    => __('Latitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter latitude for location #29.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'longitude29',
							'type'     => 'text',
							'required' => array( 'map-point-29', '=', true ),
							'title'    => __('Longitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter longitude for location #29.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'map-info29',
							'type'     => 'textarea',
							'required' => array( 'map-point-29', '=', true ),
							'title'    => __('Map Info Window Text', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('If you would like to display any text in an info window for your 29th location, please enter it here.', 'bakery_options' )
						),

						// ***** Map Point 30 ***** //
						array(
							'id'       => 'map-point-30',
							'type'     => 'switch',
							'required' => array( 'number-of-locations', '>=', 30 ),
							'title'    => __('Location #30', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Show location #30?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => false
						),
						array(
							'id'       => 'latitude30',
							'type'     => 'text',
							'required' => array( 'map-point-30', '=', true ),
							'title'    => __('Latitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter latitude for location #30.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'longitude30',
							'type'     => 'text',
							'required' => array( 'map-point-30', '=', true ),
							'title'    => __('Longitude', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Enter longitude for location #30.', 'bakery_options' ),
							'validate' => 'numeric'
						),
						array(
							'id'       => 'map-info30',
							'type'     => 'textarea',
							'required' => array( 'map-point-30', '=', true ),
							'title'    => __('Map Info Window Text', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('If you would like to display any text in an info window for your 30th location, please enter it here.', 'bakery_options' )
						),

						array(
							'id'       => 'number-of-locations',
							'type'     => 'spinner',
							'title'    => __('Number of locations', 'bakery_options' ),
							'desc'     => __('Select number of locations to be shown in the map.', 'bakery_options' ),
							'default'  => '1',
							'min'      => '1',
							'step'     => '1',
							'max'      => '30',
						)
					)
				);
				
				//Footer
				$this->sections[] = array(
					'title'  => __( 'Footer', 'bakery_options' ),
					'desc'   => __( 'Here are all options related with both footers, top and bottom as well as copyright text area.', 'bakery_options' ),
					'icon'   => 'fa fa-toggle-down',
					'fields' => array(
						array(
							'id'       => 'show-footer-top',
							'type'     => 'switch',
							'title'    => __( 'Show Footer Top', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Show top part of footer?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => true,
						),
						array(
							'id'       => 'show-footer-top-logo',
							'type'     => 'switch',
							'required' => array( 'show-footer-top', '=', true ),
							'title'    => __( 'Show Footer Top Logo', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Show logo in the top part of the footer?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => true,
						),
						array(
							'id'       => 'footer-top-logo',
							'type'     => 'media',
							'required' => array( 'show-footer-top-logo', '=', true ),
							'title'    => __( 'Footer Top Logo', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Upload logo to be shown in the top part of the footer.', 'bakery_options' ),
							'default'  => array(
								'url' => THEME_ASSETS .'images/logo2.png'
							)
						),
						array(
							'id'       => 'footer-top-bg-color',
							'type'     => 'background',
							'title'    => __( 'Footer Top Background Color', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Select background color of the top part of the footer.', 'bakery_options' ),
							'background-repeat' => false,
							'background-attachment' => false,
							'background-position' => false,
							'background-image' => false,
							'background-size' => false,
							'preview' => false,
							'transparent' => false,
							'output'   => '.page-footer .footer-light',
							'default'  => array(
								'background-color' => '#303133'
							)
						),
						array(
							'id'       => 'footer-top-text-color',
							'type'     => 'color',
							'title'    => __( 'Footer Top Text Color', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Select text color for the top part of the footer.', 'bakery_options' ),
							'transparent' => false,
							'output'   => '.page-footer .footer-light',
							'default'  => '#d6d8db'
						),
						array(
							'id'       => 'show-footer-bottom',
							'type'     => 'switch',
							'title'    => __( 'Show Footer Bottom', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Would you like to show the bottom part of the footer?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => true,
						),
						array(
							'id'       => 'footer-bottom-layout',
							'type'     => 'image_select',
							'required' => array( 'show-footer-bottom', '=', true ),
							'title'    => __( 'Footer Bottom Layout', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc' => __( 'Footer layout?', 'bakery_options' ),
							'options'  => array(
								'6' => array(
									'alt' => '2 Columns',
									'img' => ReduxFramework::$_url .'assets/img/2col.png'
								),
								'4' => array(
									'alt' => '3 Columns',
									'img' => ReduxFramework::$_url .'assets/img/3col.png'
								),
								'3' => array(
									'alt' => '4 Columns',
									'img' => ReduxFramework::$_url .'assets/img/4col.png'
								)
							),
							'default'  => '3'
						),
						array(
							'id'       => 'footer-bottom-background',
							'type'     => 'background',
							'required' => array( 'show-footer-bottom', '=', true ),
							'title'    => __( 'Background Image', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Select color or upload background image for the footer bottom.', 'bakery_options' ),
							'default'  => array(
								'background-color'      => '#28292b',
								'background-repeat'     => 'no-repeat',
								'background-attachment' => '',
								'background-position'   => 'right center',
								'background-image'      => THEME_ASSETS .'images/logo-footer-background.png',
								'background-clip'       => '',
								'background-origin'     => '',
								'background-size'       => ''
							),
							'output'   => array( '.page-footer .footer-dark' )
						),
						array(
							'id'       => 'show-copyright-text',
							'type'     => 'switch',
							'title'    => __( 'Show Copyright Text', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Show copyright area?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => true,
						),
						array(
							'id'       => 'copyright-text',
							'type'     => 'textarea',
							'required' => array( 'show-copyright-text', '=', true ),
							'title'    => __('Copyright Text', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('Copyright area content?', 'bakery_options' ),
							'validate' => 'html',
							'default'  => date('Y') .' '. __('All rights reserved. Powered by <a href="http://themeforest.net/user/milingona_/portfolio" target="_blank">Milingona</a>', 'bakery_options' )
						),
						array(
							'id'       => 'show-back-to-top',
							'type'     => 'switch',
							'title'    => __( 'Show Back to Top', 'bakery_options' ),
							'subtitle' => __( '', 'bakery_options' ),
							'desc'     => __( 'Show back to top button on all pages?', 'bakery_options' ),
							'on'       => __( 'Yes', 'bakery_options' ),
							'off'      => __( 'No', 'bakery_options' ),
							'default'  => true,
						)
					)
				);
				
				//3rd Party
				$this->sections[] = array(
					'title'  => __( '3rd Party', 'bakery_options' ),
					'desc'   => __( '', 'bakery_options' ),
					'icon'   => 'fa fa-plug',
					'fields' => array(
						array(
							'id'       => 'twitter-consumer-key',
							'type'     => 'text',
							'title'    => __('Twitter Consumer Key', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('1. Go to "https://dev.twitter.com/apps", login with our twitter account and click "Create a new application".<br>2. Fill out the required fields, accept the rules of the road, and then click on the "Create your Twitter application"<br>3. Once the app has been created, click the "Create my access token" button.<br>4. You are done! You will need the following data later on', 'bakery_options' ),
							'default'  => ''
						),
						array(
							'id'       => 'twitter-consumer-secret',
							'type'     => 'text',
							'title'    => __('Twitter Consumer Secret', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('', 'bakery_options' ),
							'default'  => ''
						),
						array(
							'id'       => 'twitter-user-token',
							'type'     => 'text',
							'title'    => __('Twitter Access Token', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('', 'bakery_options' ),
							'default'  => ''
						),
						array(
							'id'       => 'twitter-user-secret',
							'type'     => 'text',
							'title'    => __('Twitter Access Token Secret', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('', 'bakery_options' ),
							'default'  => ''
						),
						array(
							'id'       => 'google-map-api-key',
							'type'     => 'text',
							'title'    => __('Google Map API Key', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('', 'bakery_options' ),
							'default'  => ''
						),
						array(
							'id'       => 'google-analytics-tracking-code',
							'type'     => 'textarea',
							'title'    => __('Google Analytics Tracking Code', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('', 'bakery_options' ),
							'default'  => ''
						),
						array(
							'id'       => 'mailchimp-api',
							'type'     => 'text',
							'title'    => __('MailChimp API', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('', 'bakery_options' ),
							'default'  => ''
						),
						array(
							'id'       => 'mailchimp-list-id',
							'type'     => 'text',
							'title'    => __('MailChimp List ID', 'bakery_options' ),
							'subtitle' => __('', 'bakery_options' ),
							'desc'     => __('', 'bakery_options' ),
							'default'  => ''
						)
					)
				);
				
				//Custom Code
				$this->sections[] = array(
					'title'  => __( 'Custom Code', 'bakery_options' ),
					'desc'   => __( '', 'bakery_options' ),
					'icon'   => 'fa fa-code',
					'fields' => array(
						array(
							'id'       => 'custom-css',
							'type'     => 'ace_editor',
							'title'    => __( 'CSS Code', 'bakery_options' ),
							'subtitle' => __( 'Paste your CSS code here', 'bakery_options' ),
							'mode'     => 'css',
							'validate' => 'css',
							'theme'    => 'monokai',
							'desc'     => 'Possible modes can be found at <a href="http://ace.c9.io" target="_blank">http://ace.c9.io/</a>.',
							'default'  => ''
						),

						array(
							'id'       => 'custom-js',
							'type'     => 'ace_editor',
							'title'    => __('JS Code', 'bakery_options' ),
							'subtitle' => __('Paste your JS code here', 'bakery_options' ),
							'mode'     => 'javascript',
							'validate' => 'js',
							'theme'    => 'monokai',
							'desc'     => 'Possible modes can be found at <a href="http://ace.c9.io" target="_blank">http://ace.c9.io/</a>.',
							'default'  => ''
						)
					)
				);

				$this->sections[] = array(
					'type' => 'divide',
				);

				//Import / Export
				$this->sections[] = array(
					'title'  => __( 'Import / Export', 'bakery_options' ),
					'desc'   => __( 'Import and Export your Redux Framework settings from file, text or URL.', 'bakery_options' ),
					'icon'   => 'fa fa-refresh',
					'fields' => array(
						array(
							'id'         => 'import-export',
							'type'       => 'import_export',
							'title'      => 'Import Export',
							'subtitle'   => 'Save and restore your Redux options',
							'full_width' => false,
						),
					)
				);

				$this->sections[] = array(
					'type' => 'divide',
				);

				//Theme Information
				$this->sections[] = array(
					'icon'   => 'fa fa-info-circle',
					'title'  => __( 'Theme Information', 'bakery_options' ),
					'desc'   => __( '', 'bakery_options' ),
					'fields' => array(
						array(
							'id'      => 'raw-info',
							'type'    => 'raw',
							'content' => $item_info,
						)
					)
				);

				$this->sections[] = array(
					'type' => 'divide',
				);

				$this->sections[] = array(
					'id' => 'wbc_importer_section',
					'title'  => __( 'Install Demo Content', 'bakery_options' ),
					'desc'   => '
						<div style="overflow: hidden;">
							<div style="background-color: #F5FAFD; margin: 0px 0px; padding: 0 15px; color: #0C518F; border: 1px solid #CAE0F3; clear:both; line-height:18px;">
								<p class="tie_message_hint">Importing demo content is the easiest way to setup your theme. It will
								allow you to quickly edit everything instead of creating content from scratch. When you import the data please be aware of the followings:</p>

								<ul style="padding-left: 20px; list-style-position: inside; list-style-type: square;">
									<li>Posts, pages, images, widgets and menus will be imported.</li>
									<li>Imported images are copyrighted and are use for demo purporse only.</li>
									<li>Import process will take couple of minutes.</li>
								</ul>
							</div>

							<div style="background-color: #FFC7C7; margin: 10px 0 5px; padding: 0 15px; color: #7B0000; border: 1px solid #FF7C7C; clear:both; line-height:18px;">
								<p class="tie_message_hint" style="margin: 15px 0 0 0;">Before you begin, make sure all the required plugins are activated.</p>
								<p class="tie_message_hint" style="margin: 5px 0 13px 0;">If you want to install <strong>Demo Shop</strong>, please make sure to install <strong>WooCommerce</strong> before you run import AND skip the <strong>Setup Wizard</strong>.</p>
							</div>
						</div>
					',
					'icon'   => 'fa fa-hand-pointer-o',
					'fields' => array(
						array(
							'id'   => 'wbc_demo_importer',
							'type' => 'wbc_importer'
						)
					)
				);
			}

			// All the possible arguments for Redux
			public function setArguments() {
				$theme = wp_get_theme();

				$this->args = array(
					'opt_name'             => 'vu_theme_options',
					'display_name'         => $theme->get( 'Name' ),
					'display_version'      => $theme->get( 'Version' ),
					'menu_title'           => esc_html__( 'Bakery', 'bakery_options' ),
					'page_title'           => esc_html__( 'Bakery Options', 'bakery_options' ),
					'admin_bar_icon'       => 'dashicons-admin-generic',
					'page_slug'            => 'vu_bakery_options',
					'dev_mode'             => false,
					'forced_dev_mode_off'  => false,
					'update_notice'        => false,
					'show_import_export'   => true,
					'show_options_object'  => true,
					'footer_credit'        => wp_kses( __('Copyright &copy; 2016 <a href="http://themeforest.net/user/milingona_/portfolio" target="_blank">Milingona</a>. All Rights Reserved.', 'bakery_options'), array('a' => array('href' => array(), 'target' => array())) )
				);
			}
		}

		global $vu_bakery_options;
		$vu_bakery_options = new Redux_Framework_bakery_options();
	} else {
		echo "The class named Redux_Framework_bakery_options has already been called. <strong>Developers, you need to prefix this class with your company name or you'll run into problems!</strong>";
	}
?>