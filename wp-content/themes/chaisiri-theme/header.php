<?php

/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package chaisiri-theme
 */
$srcdir = get_bloginfo('template_directory');
?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
	<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<div id="page" class="site">
		<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e('Skip to content', 'chaisiri-theme'); ?></a>
		<header id="masthead" class="site-header fixed-top">
			<div id="line_nav">
				<div></div>
				<div></div>
			</div>
			<nav class="navbar navbar-expand-lg navbar-light">
				<div class="container">
					<div class="site-branding">
						<?php
						the_custom_logo();
						if (is_front_page() && is_home()) :
						?>
							<h1 class="site-title"><a href="<?= esc_url(home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a></h1>
						<?php

						endif;
						$chaisiri_theme_description = get_bloginfo('description', 'display');
						if ($chaisiri_theme_description || is_customize_preview()) :
						?>
							<p class="site-description"><?= $chaisiri_theme_description; ?></p>
						<?php endif; ?>
					</div>
					<div class="collapse navbar-collapse" id="navbarNav">
						<ul class="navbar-nav ml-auto">
							<li class="nav-item">
								<a class="nav-link" href="<?= esc_url(home_url('/')); ?>"><?= (ICL_LANGUAGE_CODE == 'en') ? 'Home' : 'หน้าแรก'; ?></a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="<?= esc_url(home_url('/about')); ?>"><?= (ICL_LANGUAGE_CODE == 'en') ? 'About Us' : 'เกี่ยวกับเรา'; ?></a>
							</li>
							<li class="nav-item dropdown">
								<a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									<?= (ICL_LANGUAGE_CODE == 'en') ? 'Product' : 'ผลิตภัณฑ์'; ?>
								</a>
								<ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
									<li><a class="dropdown-item" href="<?= esc_url(home_url('/product')); ?>"><?= (ICL_LANGUAGE_CODE == 'en') ? 'Our Product' : 'ผลิตภัณฑ์ของเรา'; ?></a></li>
									<li><a class="dropdown-item" href="<?= esc_url(home_url('/order')); ?>"><?= (ICL_LANGUAGE_CODE == 'en') ? 'Order' : 'สั่งสินค้า'; ?></a></li>
								</ul>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="<?= esc_url(home_url('/work')); ?>"><?= (ICL_LANGUAGE_CODE == 'en') ? 'Apply for work' : 'สมัครงาน'; ?></a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="<?= esc_url(home_url('/contact')); ?>"><?= (ICL_LANGUAGE_CODE == 'en') ? 'Contact Us' : 'ติดต่อเรา'; ?></a>
							</li>
							<li class="nav-item d-flex align-items-center">
								<a class="social mx-2" href="https://line.me/ti/p/~salechaisiri3" target="blank">
									<img src="<?= $srcdir ?>/images/line.svg" alt="line" />
								</a>
								<a class="social mr-2" href="https://www.facebook.com/profile.php?id=100075957613287" target="blank">
									<img src="<?= $srcdir ?>/images/facebook.svg" alt="facebook" />
								</a>
								<?php
								if (ICL_LANGUAGE_CODE == 'en') :
								?>
									<a class="lang" href="?lang=th"><img src="<?= $srcdir ?>/images/th.svg" alt="th" /> TH</a>
								<?php
								else :
								?>
									<a class="lang" href="?lang=en"><img src="<?= $srcdir ?>/images/en.svg" alt="en" /> EN</a>
								<?php endif; ?>
							</li>
						</ul>
					</div>
					<?php
					if (is_front_page()) : ?>
					<?php else : ?>
						<menu class="menu d-block d-lg-none">
							<ul class="navbar-nav ml-auto">
								<li class="nav-item">
									<a class="nav-link" href="<?= esc_url(home_url('')); ?>"><?= (ICL_LANGUAGE_CODE == 'en') ? 'Home' : 'หน้าแรก'; ?></a>
								</li>
								<li class="nav-item">
									<a class="nav-link" href="<?= esc_url(home_url('/about')); ?>"><?= (ICL_LANGUAGE_CODE == 'en') ? 'About Us' : 'เกี่ยวกับเรา'; ?></a>
								</li>
								<li class="nav-item">
									<a class="nav-link" href="<?= esc_url(home_url('/product')); ?>"><?= (ICL_LANGUAGE_CODE == 'en') ? 'Our Product' : 'ผลิตภัณฑ์ของเรา'; ?></a>
								</li>
								<li class="nav-item">
									<a class="nav-link" href="<?= esc_url(home_url('/order')); ?>"><?= (ICL_LANGUAGE_CODE == 'en') ? 'Order' : 'สั่งสินค้า'; ?></a>
								</li>
								<li class="nav-item">
									<a class="nav-link" href="<?= esc_url(home_url('/work')); ?>"><?= (ICL_LANGUAGE_CODE == 'en') ? 'Apply for work' : 'สมัครงาน'; ?></a>
								</li>
								<li class="nav-item">
									<a class="nav-link" href="<?= esc_url(home_url('/contact')); ?>"><?= (ICL_LANGUAGE_CODE == 'en') ? 'Contact Us' : 'ติดต่อเรา'; ?></a>
								</li>
								<li class="nav-item d-flex align-items-center mt-4">
									<a class="social mr-3" href="https://line.me/ti/p/~salechaisiri3" target="blank">
										<img src="<?= $srcdir ?>/images/line.svg" alt="line" />
									</a>
									<a class="social mr-3" href="https://www.facebook.com/profile.php?id=100075957613287" target="blank">
										<img src="<?= $srcdir ?>/images/facebook.svg" alt="facebook" />
									</a>
									<?php
									if (ICL_LANGUAGE_CODE == 'en') :
									?>
										<a class="lang" href="?lang=th"><img src="<?= $srcdir ?>/images/th.svg" alt="th" /> TH</a>
									<?php
									else :
									?>
										<a class="lang" href="?lang=en"><img src="<?= $srcdir ?>/images/en.svg" alt="en" /> EN</a>
									<?php endif; ?>
								</li>
							</ul>
						</menu>
						<div id="main_mobile">
							<nav class="menu-btn d-block d-lg-none">
								<ul>
									<li></li>
									<li></li>
									<li></li>
								</ul>
							</nav>
						</div>
					
				</div>
			</nav>
			<?php endif ?>

			<!-- <nav id="site-navigation" class="main-navigation">
			<button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false"><?php esc_html_e('Primary Menu', 'chaisiri-theme'); ?></button>
			<?php
			// wp_nav_menu(
			// 	array(
			// 		'theme_location' => 'menu-1',
			// 		'menu_id'        => 'primary-menu',
			// 	)
			// );
			?>
		</nav> -->

		</header><!-- #masthead -->