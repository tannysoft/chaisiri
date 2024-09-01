<?php
get_header();
$srcdir = get_bloginfo('template_directory');
$philosophy = get_field('philosophy');
?>

<main id="primary" class="site-main">
	<div id="about_detail" class="pb-5">

		<!-- Banner ---------------------------------------->
		<?php $about_banner = get_field('about-banner');
		if ($about_banner) : ?>
			<div class="d-flex justify-content-center">
				<img class="banner" src="<?= $about_banner ?>" alt="about_banner">
			</div>
		<?php else : ?>
			<?= do_shortcode('[presto_player id=347]'); ?>
		<?php endif; ?>
		<!-- End Banner ------------------------------------>

		<div class="container pt-4 pt-md-5">
			<h1 data-aos-duration="1000" data-aos="fade-up" class="head">
				<?= (ICL_LANGUAGE_CODE == 'en') ? 'About Us' : 'เกี่ยวกับเรา'; ?>
				<div class="line">
					<div></div>
					<div></div>
				</div>
			</h1>
			<div data-aos-duration="1000" data-aos="fade-up" class="detail1 mt-4">
				<?= the_content(); ?>
			</div>
		</div>
		<div class="bg-left pb-5" data-aos-duration="1000" data-aos="fade-up">
			<div class="container text-center">
				<img class="detail-img" src=<?= get_field('about_img_1'); ?> alt="about">
			</div>
		</div>
		<div class="row py-4 py-md-5 mx-0 align-items-center">
			<div class="col-md-6">
				<div data-aos-duration="1000" data-aos="fade-right" class="detail2">
					<b><?= get_field('about_detail_1'); ?></b>
				</div>
				<div data-aos-duration="1000" data-aos="fade-right" class="detail2 mt-md-4 mt-3">
					<?= get_field('about_detail_2'); ?>
				</div>
			</div>
			<div class="col-md-6 mt-3 mt-md-0 bg-right" data-aos-duration="1000" data-aos="fade-up">
				<img class="detail-img2" src=<?= get_field('about_img_2'); ?> alt="about">
			</div>
		</div>
		<div class="container">
			<div id="about_line">
				<div></div>
				<div></div>
				<div></div>
			</div>
			<div class="about-head mt-4 mt-md-5">
				<h1>
					<?php if (ICL_LANGUAGE_CODE == 'en') : ?>
						<b> Philosophy
							<span>and</span> Business Ideology
						</b>
					<?php else : ?>
						<b> ปรัชญา
							<span>และ</span> อุดมการณ์การดำเนินธุรกิจ
						</b>
					<?php endif ?>
				</h1>
				<h3>(CSNC Business Philosophy)</h3>
			</div>
		</div>
		<div class="row mt-3 mx-0 align-items-center bg-philosophy pt-3 pt-lg-0">
			<!-- row 1 -->
			<div class="col-lg-5 order-2 order-lg-1">
				<div data-aos-duration="1500" data-aos="fade-right" class="detail3 ml-auto">
					<h1>
						<?= $philosophy['philosophy_title_1']; ?>
					</h1>
					<div id="about_line2">
						<div></div>
						<div></div>
					</div>
					<p><?= $philosophy['philosophy_detail_1']; ?></p>
				</div>
			</div>
			<div class="col-lg-7 pr-lg-0 order-1 order-lg-2">
				<div class="about_detail_wrapper" data-aos-duration="1000" data-aos="fade-right">
					<img class="w-100 zoom_in" src=<?= $philosophy['philosophy_img_1']; ?> alt="philosophy">
				</div>
			</div>
		</div>
		<!-- row 2 -->
		<div class="row mx-0 align-items-center bg-philosophy pt-3 pt-lg-0">
			<div class="col-lg-7 pl-lg-0">
				<div class="about_detail_wrapper" data-aos-duration="1000" data-aos="fade-right">
					<img class="w-100 zoom_in" src=<?= $philosophy['philosophy_img_2']; ?> alt="philosophy">
				</div>
			</div>
			<div class="col-lg-5">
				<div data-aos-duration="1500" data-aos="fade-right" class="detail3 mr-auto">
					<h1><?= $philosophy['philosophy_title_2']; ?></h1>
					<div id="about_line2">
						<div></div>
						<div></div>
					</div>
					<p><?= $philosophy['philosophy_detail_2']; ?></p>
				</div>
			</div>
		</div>
		<!-- row 3 -->
		<div class="row mx-0 align-items-center bg-philosophy pt-3 pt-lg-0">
			<div class="col-lg-5 order-2 order-lg-1">
				<div data-aos-duration="1000" data-aos="fade-right" class="detail3 ml-auto">
					<h1><?= $philosophy['philosophy_title_3']; ?></h1>
					<div id="about_line2">
						<div></div>
						<div></div>
					</div>
					<p><?= $philosophy['philosophy_detail_3']; ?></p>
				</div>
			</div>
			<div class="col-lg-7 pr-lg-0 order-1 order-lg-2">
				<div class="about_detail_wrapper" data-aos-duration="1000" data-aos="fade-right">
					<img class="w-100 zoom_in" src=<?= $philosophy['philosophy_img_3']; ?> alt="philosophy">
				</div>
			</div>
		</div>
		<!-- row 4 -->
		<div class="row mx-0 align-items-center bg-philosophy pt-3 pt-lg-0">
			<div class="col-lg-7 pl-lg-0">
				<div class="about_detail_wrapper" data-aos-duration="1000" data-aos="fade-right">
					<img class="w-100 zoom_in" src=<?= $philosophy['philosophy_img_4']; ?> alt="philosophy">
				</div>
			</div>
			<div class="col-lg-5">
				<div data-aos-duration="1000" data-aos="fade-right" class="detail3 mr-auto">
					<h1><?= $philosophy['philosophy_title_4']; ?></h1>
					<div id="about_line2">
						<div></div>
						<div></div>
					</div>
					<p><?= $philosophy['philosophy_detail_4']; ?></p>
				</div>
			</div>
		</div>
		<!-- last detail -->
		<div class="container text-center">
			<div data-aos-duration="1000" data-aos="fade-up" class="detail4 mt-4 mt-md-5">
				<?= get_field('about_detail_3'); ?>
			</div>
			<div data-aos-duration="1000" data-aos="fade-up" class="box detail4 mt-4">
				<?= get_field('about_detail_4'); ?>
			</div>
		</div>
	</div>
</main><!-- #main -->

<?php get_footer(); ?>