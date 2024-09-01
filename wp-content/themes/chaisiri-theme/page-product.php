<?php
get_header();
$srcdir = get_bloginfo('template_directory');
?>

<main id="primary" class="site-main">
	<div id="products">
		<div class="banner-fade position-relative">
			<div class="about_detail_wrapper">
				<img class="banner-zoom" src=<?= get_field('banner_head'); ?> alt="banner" />
			</div>
			<div data-aos-duration="1000" data-aos="fade-right" class="box">
				<div class="line-90">
					<div></div>
					<div></div>
				</div>
				<h1>
					<?= (ICL_LANGUAGE_CODE == 'en') ? 'Products' : 'ผลิตภัณฑ์'; ?>
				</h1>
				<h2>
					<?php if (ICL_LANGUAGE_CODE == 'en') : ?>
						High quality, durable, beautiful and safe <br>
						with standards for consumers
					<?php else : ?>
						มีคุณภาพสูง คงทนสวยงาม และปลอดภัย <br>
						พร้อมทั้งได้มาตรฐานต่อผู้บริโภค
					<?php endif ?>
				</h2>
			</div>
		</div>
		<div id="product_bg">
			<div data-aos-duration="1000" data-aos="fade-up" class="container py-4 py-md-5">
				<ul class="nav nav-pills nav-justified align-items-center" id="pills-tab" role="tablist">
					<li class="nav-item">
						<a class="nav-link active" id="pills-1-tab" data-toggle="pill" href="#pills-1" role="tab" aria-controls="pills-1" aria-selected="true">
							<?= (ICL_LANGUAGE_CODE == 'en') ? 'WOVEN SACKS' : 'กระสอบ'; ?>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="pills-2-tab" data-toggle="pill" href="#pills-2" role="tab" aria-controls="pills-2" aria-selected="false">
							<?= (ICL_LANGUAGE_CODE == 'en') ? 'TARPAULIN' : 'ผ้าใบ'; ?>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="pills-3-tab" data-toggle="pill" href="#pills-3" role="tab" aria-controls="pills-3" aria-selected="false">
							<?= (ICL_LANGUAGE_CODE == 'en') ? 'MESH BAGS' : 'ถุงตาข่าย'; ?>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="pills-4-tab" data-toggle="pill" href="#pills-4" role="tab" aria-controls="pills-4" aria-selected="false">
							<?= (ICL_LANGUAGE_CODE == 'en') ? 'OTHER PRODUCTS' : 'สินค้าอื่นๆ'; ?>
						</a>
					</li>
				</ul>
				<div class="tab-content" id="pills-tabContent">
					<div class="tab-pane fade show active" id="pills-1" role="tabpanel" aria-labelledby="pills-1-tab">
						<div class="row padding align-items-center">
							<div class="col-md-7">
								<h2><?= (ICL_LANGUAGE_CODE == 'en') ? 'WOVEN SACKS' : 'กระสอบ'; ?></h2>
								<ul class="fa-ul my-4">
									<?php if (get_field('woven-sacks')) : ?>
										<?php while (the_repeater_field('woven-sacks')) : ?>
											<li><span class="fa-li"><i class="fas fa-caret-right"></i></span>
												<?php the_sub_field('woven-sacks-detail'); ?>
											</li>
										<?php endwhile; ?>
									<?php endif; ?>
								</ul>
								<a href="/order<?= (ICL_LANGUAGE_CODE == 'en') ? '?lang=en' : ''; ?>" class="btn btn1 mr-2"><?= (ICL_LANGUAGE_CODE == 'en') ? 'Request a purchase order' : 'ขอใบสั่งซื้อสินค้า'; ?></a>
								<a href="/contact<?= (ICL_LANGUAGE_CODE == 'en') ? '?lang=en' : ''; ?>" class="btn btn1"><?= (ICL_LANGUAGE_CODE == 'en') ? 'Ask for more information' : 'สอบถามเพิ่มเติม'; ?></a>
							</div>
							<div class="col-md-5 mt-3 mt-md-0">
								<?php $woven_sacks_img = get_field('woven-sacks-img');
								if ($woven_sacks_img) : ?>
									<img class="w-100" src=<?= $woven_sacks_img ?> alt="product" />
								<?php else : ?>
									<div class="box-gray">No image</div>
								<?php endif ?>
							</div>
							<div class="col-12 pt-4">
								<div class="owl-carousel owl-theme">
									<!-- loop -->
									<?php
									$args = array(
										'post_type' => 'sack',
										'posts_per_page' => -1,
										'post_status' => 'publish',
										'orderby' => 'publish_date',
										'order' => 'ASC',
									);
									$query = new WP_Query($args);
									if ($query->have_posts()) :
									?>
										<?php while ($query->have_posts()) : $query->the_post(); ?>
											<div class="shadow text-center">
												<a href="<?php the_permalink(); ?>">
													<?php if (has_post_thumbnail()) : ?>
														<?= the_post_thumbnail('full'); ?>
													<?php else : ?>
														<img class="mb-3" src="<?= $srcdir ?>/images/thumb.jpg" alt=<?= get_the_title() ?> />
													<?php endif ?>
												</a>
												<h6><?= get_the_title() ?></h6>
												<a class="btn more" href="<?php the_permalink(); ?>"><?= (ICL_LANGUAGE_CODE == 'en') ? 'more details' : 'ดูรายละเอียดเพิ่ม'; ?></a>
											</div>
										<?php endwhile;
										wp_reset_postdata();
										wp_reset_query();
										?>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
					<div class="tab-pane fade" id="pills-2" role="tabpanel" aria-labelledby="pills-2-tab">
						<div class="row padding align-items-center">
							<div class="col-md-7">
								<h2><?= (ICL_LANGUAGE_CODE == 'en') ? 'TARPAULIN' : 'ผ้าใบ'; ?></h2>
								<ul class="fa-ul my-4">
									<?php if (get_field('tarpaulin')) : ?>
										<?php while (the_repeater_field('tarpaulin')) : ?>
											<li><span class="fa-li"><i class="fas fa-caret-right"></i></span>
												<?php the_sub_field('tarpaulin-detail'); ?>
											</li>
										<?php endwhile; ?>
									<?php endif; ?>
								</ul>
								<a href="/order<?= (ICL_LANGUAGE_CODE == 'en') ? '?lang=en' : ''; ?>" class="btn btn1 mr-2"><?= (ICL_LANGUAGE_CODE == 'en') ? 'Request a purchase order' : 'ขอใบสั่งซื้อสินค้า'; ?></a>
								<a href="/contact<?= (ICL_LANGUAGE_CODE == 'en') ? '?lang=en' : ''; ?>" class="btn btn1"><?= (ICL_LANGUAGE_CODE == 'en') ? 'Ask for more information' : 'สอบถามเพิ่มเติม'; ?></a>
							</div>
							<div class="col-md-5 mt-3 mt-md-0">
								<?php $tarpaulin_img = get_field('tarpaulin-img');
								if ($tarpaulin_img) : ?>
									<img class="w-100" src=<?= $tarpaulin_img ?> alt="product" />
								<?php else : ?>
									<div class="box-gray">No image</div>
								<?php endif ?>
							</div>
							<div class="col-12 pt-4">
								<div class="owl-carousel owl-theme">
									<!-- loop -->
									<?php
									$args = array(
										'post_type' => 'canvas',
										'posts_per_page' => -1,
										'post_status' => 'publish',
										'orderby' => 'publish_date',
										'order' => 'ASC',
									);
									$query = new WP_Query($args);
									if ($query->have_posts()) :
									?>
										<?php while ($query->have_posts()) : $query->the_post(); ?>
											<div class="shadow text-center">
												<a href="<?php the_permalink(); ?>">
													<?php if (has_post_thumbnail()) : ?>
														<?= the_post_thumbnail('full'); ?>
													<?php else : ?>
														<img class="mb-3" src="<?= $srcdir ?>/images/thumb.jpg" alt=<?= get_the_title() ?> />
													<?php endif ?>
												</a>
												<h6><?= get_the_title() ?></h6>
												<a class="btn more" href="<?php the_permalink(); ?>"><?= (ICL_LANGUAGE_CODE == 'en') ? 'more details' : 'ดูรายละเอียดเพิ่ม'; ?></a>
											</div>
										<?php endwhile;
										wp_reset_postdata();
										wp_reset_query();
										?>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
					<div class="tab-pane fade" id="pills-3" role="tabpanel" aria-labelledby="pills-3-tab">
						<div class="row padding align-items-center">
							<div class="col-md-7">
								<h2><?= (ICL_LANGUAGE_CODE == 'en') ? 'MESH BAGS' : 'ถุงตาข่าย'; ?></h2>
								<ul class="fa-ul my-4">
									<?php if (get_field('mesg-bags')) : ?>
										<?php while (the_repeater_field('mesg-bags')) : ?>
											<li><span class="fa-li"><i class="fas fa-caret-right"></i></span>
												<?php the_sub_field('mesg-bags-detail'); ?>
											</li>
										<?php endwhile; ?>
									<?php endif; ?>
								</ul>
								<a href="/order<?= (ICL_LANGUAGE_CODE == 'en') ? '?lang=en' : ''; ?>" class="btn btn1 mr-2"><?= (ICL_LANGUAGE_CODE == 'en') ? 'Request a purchase order' : 'ขอใบสั่งซื้อสินค้า'; ?></a>
								<a href="/contact<?= (ICL_LANGUAGE_CODE == 'en') ? '?lang=en' : ''; ?>" class="btn btn1"><?= (ICL_LANGUAGE_CODE == 'en') ? 'Ask for more information' : 'สอบถามเพิ่มเติม'; ?></a>
							</div>
							<div class="col-md-5 mt-3 mt-md-0">
								<?php $mesg_bags_img = get_field('mesg-bags-img');
								if ($mesg_bags_img) : ?>
									<img class="w-100" src=<?= $mesg_bags_img ?> alt="product" />
								<?php else : ?>
									<div class="box-gray">No image</div>
								<?php endif ?>
							</div>
							<div class="col-12 pt-4">
								<div class="owl-carousel owl-theme">
									<!-- loop -->
									<?php
									$args = array(
										'post_type' => 'mesh_bag',
										'posts_per_page' => -1,
										'post_status' => 'publish',
										'orderby' => 'publish_date',
										'order' => 'ASC',
									);
									$query = new WP_Query($args);
									if ($query->have_posts()) :
									?>
										<?php while ($query->have_posts()) : $query->the_post(); ?>
											<div class="shadow text-center">
												<a href="<?php the_permalink(); ?>">
													<?php if (has_post_thumbnail()) : ?>
														<?= the_post_thumbnail('full'); ?>
													<?php else : ?>
														<img class="mb-3" src="<?= $srcdir ?>/images/thumb.jpg" alt=<?= get_the_title() ?> />
													<?php endif ?>
												</a>
												<h6><?= get_the_title() ?></h6>
												<a class="btn more" href="<?php the_permalink(); ?>"><?= (ICL_LANGUAGE_CODE == 'en') ? 'more details' : 'ดูรายละเอียดเพิ่ม'; ?></a>
											</div>
										<?php endwhile;
										wp_reset_postdata();
										wp_reset_query();
										?>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
					<div class="tab-pane fade" id="pills-4" role="tabpanel" aria-labelledby="pills-4-tab">
						<div class="row padding align-items-center">
							<div class="col-md-7">
								<h2><?= (ICL_LANGUAGE_CODE == 'en') ? 'OTHER PRODUCTS' : 'สินค้าอื่นๆ'; ?></h2>
								<ul class="fa-ul my-4">
									<?php if (get_field('other-products')) : ?>
										<?php while (the_repeater_field('other-products')) : ?>
											<li><span class="fa-li"><i class="fas fa-caret-right"></i></span>
												<?php the_sub_field('other-products-detail'); ?>
											</li>
										<?php endwhile; ?>
									<?php endif; ?>
								</ul>
								<a href="/order<?= (ICL_LANGUAGE_CODE == 'en') ? '?lang=en' : ''; ?>" class="btn btn1 mr-2"><?= (ICL_LANGUAGE_CODE == 'en') ? 'Request a purchase order' : 'ขอใบสั่งซื้อสินค้า'; ?></a>
								<a href="/contact<?= (ICL_LANGUAGE_CODE == 'en') ? '?lang=en' : ''; ?>" class="btn btn1"><?= (ICL_LANGUAGE_CODE == 'en') ? 'Ask for more information' : 'สอบถามเพิ่มเติม'; ?></a>
							</div>
							<div class="col-md-5 mt-3 mt-md-0">
								<?php $other_product_img = get_field('other-product-img');
								if ($other_product_img) : ?>
									<img class="w-100" src=<?= $other_product_img ?> alt="product" />
								<?php else : ?>
									<div class="box-gray">No image</div>
								<?php endif ?>
							</div>
							<div class="col-12 pt-4">
								<div class="owl-carousel owl-theme">
									<!-- loop -->
									<?php
									$args = array(
										'post_type' => 'other_products',
										'posts_per_page' => -1,
										'post_status' => 'publish',
										'orderby' => 'publish_date',
										'order' => 'ASC',
									);
									$query = new WP_Query($args);
									if ($query->have_posts()) :
									?>
										<?php while ($query->have_posts()) : $query->the_post(); ?>
											<div class="shadow text-center">
												<a href="<?php the_permalink(); ?>">
													<?php if (has_post_thumbnail()) : ?>
														<?= the_post_thumbnail('full'); ?>
													<?php else : ?>
														<img class="mb-3" src="<?= $srcdir ?>/images/thumb.jpg" alt=<?= get_the_title() ?> />
													<?php endif ?>
												</a>
												<h6><?= get_the_title() ?></h6>
												<a class="btn more" href="<?php the_permalink(); ?>"><?= (ICL_LANGUAGE_CODE == 'en') ? 'more details' : 'ดูรายละเอียดเพิ่ม'; ?></a>
											</div>
										<?php endwhile;
										wp_reset_postdata();
										wp_reset_query();
										?>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div id="product_bg2">
			<div class="container">
				<h1><?= (ICL_LANGUAGE_CODE == 'en') ? 'How can new customers order our products?' : 'ขั้นตอนการสั่งผลิตสินค้าสำหรับลูกค้าใหม่'; ?></h1>
				<div class="row">
					<div class="col-md-3 mt-3 text-center">
						<img class="bounce-hover" src="<?= $srcdir ?>/images/product/chat.png" alt="chat" />
						<h6>
							<?= (ICL_LANGUAGE_CODE == 'en') ? 'contact the sales teams' : 'ติดต่อกับฝ่ายขายของบริษัทชัยศิริ ได้ตามช่องทาง ดังนี้'; ?>
						</h6>
						<h5><i class="fas fa-phone mt-2"></i>034-471742</h5>
						<h5><i class="fas fa-envelope"></i>sale@chaisiri.com</h5>
					</div>
					<div class="col-md-3 mt-3 text-center">
						<img class="bounce-hover" src="<?= $srcdir ?>/images/product/check.png" alt="check" />
						<h6>
							<?= (ICL_LANGUAGE_CODE == 'en') ? 'customise the products by informing of colours, patterns or sizes' : 'แจ้งความต้องการ เช่น สี รูปแบบสินค้า ขนาดสินค้าที่ต้องการเป็นต้น'; ?>
						</h6>
					</div>
					<div class="col-md-3 mt-3 text-center">
						<img class="bounce-hover" src="<?= $srcdir ?>/images/product/cal.png" alt="cal" />
						<h6><?= (ICL_LANGUAGE_CODE == 'en') ? 'receive a quotation from the sales team' : 'ฝ่ายขายออกใบเสนอราคาให้ลูกค้า'; ?></h6>
					</div>
					<div class="col-md-3 mt-3 text-center">
						<img class="bounce-hover" src="<?= $srcdir ?>/images/product/delivery.png" alt="delivery" />
						<h6><?= (ICL_LANGUAGE_CODE == 'en') ? 'receive products' : 'จัดส่งสินค้าให้ลูกค้า'; ?></h6>
						<a href="/order<?= (ICL_LANGUAGE_CODE == 'en') ? '?lang=en' : ''; ?>" class="btn btn2 mt-3">
							<i class="fa fa-chevron-right mr-2"></i>
							<?= (ICL_LANGUAGE_CODE == 'en') ? 'Request a purchase order' : 'ขอใบสั่งซื้อสินค้า'; ?>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</main>

<?php get_footer(); ?>