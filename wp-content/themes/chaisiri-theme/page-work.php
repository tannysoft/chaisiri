<?php
get_header();
$srcdir = get_bloginfo('template_directory');
?>

<main id="primary" class="site-main">
	<div id="work">
		<div class="banner-fade position-relative">
			<div class="about_detail_wrapper">
				<img class="banner-zoom" src=<?= get_field('banner_head'); ?> alt="banner" />
			</div>
			<div data-aos-duration="1000" data-aos="fade-right" class="box box-m">
				<h1>
					<?= (ICL_LANGUAGE_CODE == 'en') ? 'Apply for work' : 'สมัครงาน'; ?>
				</h1>
				<h2>
					<?= (ICL_LANGUAGE_CODE == 'en') ? 'Search for vacancies' : 'ตำแหน่งงานที่มองหา'; ?>
				</h2>
				<form id="searchform" method="get" action="<?= esc_url(home_url('/')); ?>">
					<input type="text" class="search-field" name="s" value="<?php echo get_search_query(); ?>">
					<?php if (ICL_LANGUAGE_CODE == 'en') : ?>
						<input type="hidden" name="lang" value="en">
					<?php endif ?>
					<button class="btn" type="submit"><?= (ICL_LANGUAGE_CODE == 'en') ? 'search' : 'ค้นหา'; ?></button>
				</form>
			</div>
		</div>
		<div class="position-relative">
			<img class="contact-bg" src="<?= $srcdir ?>/images/contact/logo-bg.png" alt="bg" />
			<div class="container py-4 py-md-5">
				<div class="row">
					<div class="col-md-7 order-2 order-md-1">
						<img class="w-100" src=<?= get_field('work_img'); ?> alt="position" />
					</div>
					<div class="col-md-5 px-3 px-md-0 order-1 order-md-2">
						<!-- loop -->
						<?php
						$args = array(
							'post_type' => 'position',
							'posts_per_page' => -1,
							'post_status' => 'publish',
							'orderby' => 'publish_date',
							'order' => 'DESC',
						);
						$query = new WP_Query($args);
						if ($query->have_posts()) :
						?>
							<div class="position mt-0 mt-md-4">
								<h1 class="head mb-4">
									<?= (ICL_LANGUAGE_CODE == 'en') ? 'Available Vacancies' : 'ตำแหน่งที่เปิดรับสมัคร'; ?>
									<div class="line">
										<div></div>
										<div></div>
									</div>
								</h1>
								<div class="scrollbar" id="style-1">
									<div class="force-overflow">
										<?php while ($query->have_posts()) : $query->the_post(); ?>
											<div class="box-list position-relative">
												<div class="row mx-0 align-items-center">
													<div class="col-8 px-0">
														<h3><?= get_the_title() ?></h3>
													</div>
													<div class="col-4 px-0 text-right">
														<a class="btn btn-regist" href="<?= esc_url(home_url('/regist')); ?>">
															<?= (ICL_LANGUAGE_CODE == 'en') ? 'Apply for a job!' : 'สมัครงานคลิก!'; ?>
														</a>
													</div>
													<div class="col-12 px-0 text-right">
														<hr>
														<div class="read-more-content">
															<?= get_the_content() ?>
														</div>
														<a href="javascript:void(0);" class="btn read-more">
															<?= (ICL_LANGUAGE_CODE == 'en') ? 'Read More' : 'ดูรายละเอียด'; ?>
															<i class="fa-solid fa-plus"></i>
														</a>
													</div>
												</div>
											</div>
										<?php endwhile; ?>
									</div>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<img class="contact-bg2" src="<?= $srcdir ?>/images/contact/logo-bg2.png" alt="bg" />
		</div>
	</div>
</main><!-- #main -->

<?php get_footer(); ?>