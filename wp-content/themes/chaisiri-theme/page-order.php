<?php
get_header();
$srcdir = get_bloginfo('template_directory');
?>

<main id="primary" class="site-main">
	<div id="regist" class="py-5">
		<!-- <img class="bg bg-left" src="<?= $srcdir ?>/images/regist/logo-bg2.png" alt=""> -->
		<h1 class="head">
		<?= (ICL_LANGUAGE_CODE == 'en') ? 'Order' : 'สั่งสินค้า'; ?>
			<div class="line">
				<div></div>
				<div></div>
			</div>
		</h1>
		<div class="container mt-4">
		<?= (ICL_LANGUAGE_CODE == 'en') ? do_shortcode('[ARForms id=102]') : do_shortcode('[ARForms id=101]'); ?>
		</div>
		<img class="bg bg-right" src="<?= $srcdir ?>/images/regist/logo-bg.png" alt="">
	</div>
</main><!-- #main -->

<?php get_footer(); ?>