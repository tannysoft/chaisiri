<?php

/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package chaisiri-theme
 */
$srcdir = get_bloginfo('template_directory');
?>
<div id="product_detail">
	<div class="container py-5">
		<h1 class="head">
			<?= get_the_title() ?>
			<div class="line">
				<div></div>
				<div></div>
			</div>
		</h1>
		<div class="my-4 text-center">
			<?php
			$banner = get_field('product-banner');
			if ($banner) : ?>
				<img src=<?= $banner ?> alt="banner">
			<?php else : ?>
				<div></div>
			<?php endif ?>
		</div>
		<?php
		$title = get_field('product-title');
		if ($title) : ?>
			<div class="row box mx-0 align-items-center">
				<div class="col-md-6 px-3 px-md-0">
					<?php the_content(); ?>
				</div>
				<div class="col-md-6 mt-3 mt-md-0">
					<h1>
						<?= get_field('product-title'); ?>
					</h1>
					<div class="line n-center">
						<div></div>
						<div></div>
					</div>
					<p class="mt-3">
						<?= get_field('product-detail'); ?>
					</p>
				</div>
			</div>
		<?php else : ?>
			<div></div>
		<?php endif ?>
	</div>
	<div class="contact">
		<div class="container">
			<div class="block">
				<h1>
					<?= (ICL_LANGUAGE_CODE == 'en') ? 'For more information & Offers' : 'สอบถามข้อมูลเพิ่มเติ่ม & ขอใบเสนอราคา'; ?>
				</h1>
				<div class="d-flex justify-content-center my-3">
					<a href="<?= esc_url(home_url('order')); ?>" class="btn"><?= (ICL_LANGUAGE_CODE == 'en') ? 'How to order' : 'วิธีการสั่งซื้อ'; ?></a>
					<a href="<?= esc_url(home_url('contact')); ?>" class="btn ml-3"><?= (ICL_LANGUAGE_CODE == 'en') ? 'Ask' : 'สอบถาม'; ?></a>
				</div>
				<div class="social d-flex justify-content-center align-items-center">
					<a href="tel:+(66) 034-471742"><img src="<?= $srcdir ?>/images/product/call.png" alt="call"></a>
					<a href="mailto:sale@chaisiri.com"><img src="<?= $srcdir ?>/images/product/mail.png" alt="mail"></a>
					<a target="blank" href="https://line.me/ti/p/~salechaisiri3"><img src="<?= $srcdir ?>/images/product/line.png" alt="line"></a>
					<a target="blank" href="https://www.facebook.com/%E0%B8%9A%E0%B8%A3%E0%B8%B4%E0%B8%A9%E0%B8%B1%E0%B8%97-%E0%B9%82%E0%B8%A3%E0%B8%87%E0%B8%87%E0%B8%B2%E0%B8%99%E0%B8%97%E0%B8%AD%E0%B8%9C%E0%B9%89%E0%B8%B2%E0%B9%83%E0%B8%9A%E0%B9%84%E0%B8%99%E0%B8%A5%E0%B9%88%E0%B8%AD%E0%B8%99%E0%B8%8A%E0%B8%B1%E0%B8%A2%E0%B8%A8%E0%B8%B4%E0%B8%A3%E0%B8%B4-%E0%B8%88%E0%B8%B3%E0%B8%81%E0%B8%B1%E0%B8%94-CSNC-100182412451173/"><img src="<?= $srcdir ?>/images/product/fb.png" alt="fb"></a>
				</div>
			</div>
		</div>
	</div>
</div>