<!doctype html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<?php if ( is_singular() && pings_open( get_queried_object() ) ) : ?>
			<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
		<?php endif; ?>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php wp_head(); ?>
	</head>
	<body <?php body_class(); ?>>
		<!-- iframe load area -->
		<div class="demo-wrap">
			<iframe class="demo-frame-block" frameborder="0" noresize="noresize">
				
			</iframe>
		</div>
		<!-- switcher bar -->
		<header class="switcher-bar clearfix">
			<div class="container-fluid">
				<div class="row">
					<div class="col-md-3">
						<a href="<?php echo cds_url(); ?>" class="logo-block" title="Site Logo">
							<?php print cds_logo(); ?>
						</a>
					</div>
					<div class="col-md-2">
						<div id="page_select" class="page-select-block">
							<span></span>
							<div class="page-holder">
								<ul id="page_list" class="page-list">
									
								</ul>
							</div>
						</div>
					</div>
					<div class="col-md-3">
						<div id="demo_select" class="demo-select-block">
							
						</div>
					</div>
					<div class="col-sm-4">
						<div class="controlling-tools-block">
							<div class="close-btn-block">
								<a href="#" class="close-frame-btn">
									<i class="fa fa-times"></i>
								</a>
							</div>	
							<div class="bye-btn-block hidden-xs">
								<a href="#" class="buy-btn">
									<i class="fa fa-shopping-cart"></i>
									<span><?php esc_html_e('Buy Now','cp-demo-switcher'); ?></span>
								</a>
							</div>
							<div class="responsive-controll hidden-xs">
								<ul class="viewports">
									<li class="active"><a href="#" data-size="100%"><i class="fa fa-desktop"></i></a></li>
									<li><a href="#" data-size="768px"><i class="fa fa-tablet"></i></a></li>
									<li><a href="#" data-size="380px"><i class="fa fa-mobile"></i></a></li>
								</ul> 
							</div>
						</div>
					</div>
				</div>
			</div>
		</header>

		<div class="demo-switcher-carousel-block">
			<div class="carousel-block-inner">
			</div>
		</div>
		<?php wp_footer(); ?>
	</body>
</html>
