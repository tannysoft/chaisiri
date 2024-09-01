<?php get_header();
$srcdir = get_bloginfo('template_directory');
?>
<!-- menu -->
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
<!-- menu button -->
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
<!-- Banner ------------------>
<section id="banner">
	<div class="banner d-none d-md-block">
		<?= do_shortcode('[smartslider3 slider="1"]') ?>
	</div>
	<div class="banner d-block d-md-none">
		<?php if (ICL_LANGUAGE_CODE == 'en') : ?>
			<?= do_shortcode('[smartslider3 slider="13"]'); ?>
		<?php else : ?>
			<?= do_shortcode('[smartslider3 slider="2"]') ?>
		<?php endif ?>
	</div>
	<div class="container text-position w-100 d-none d-md-block">
		<h1 data-aos-duration="1000" data-aos="fade-right"><?= get_field('banner_head') ?></h1>
		<h2 data-aos-duration="1500" data-aos="fade-right"><?= get_field('banner_sub') ?></h2>
		<div class="border-b"></div>
		<div data-aos-duration="1200" data-aos="fade-up">
			<div class="title title-1"><?= get_field('banner_text1') ?></div>
			<div class="title title-1 mt-2"><?= get_field('banner_text2') ?></div>
		</div>
	</div>
</section>

<!-- About ------------------->
<section id="about" data-aos-duration="700" data-aos="zoom-out">
	<div class="container text-center py-5">
		<div class="title">
			<?= (ICL_LANGUAGE_CODE == 'en') ? 'Chaisiri Nylon Canvas Factory Ltd. (CSNC)' : 'บริษัท โรงงานทอผ้าใบไนล่อนชัยศิริ จำกัด (CSNC)'; ?>
		</div>
		<div class="detail">
			<?php if (ICL_LANGUAGE_CODE == 'en') : ?>
				Chaisiri Nylon Canvas Factory Ltd. (CSNC) is a manufacturer of high-quality woven sacks, <br>
				PP mesh bags and PP woven fabrics. We have forty-three years of experiencein offering durable <br>
				and safe products which could meet standards of both domestic and international consumers.
			<?php else : ?>
				ผู้ผลิต กระสอบพลาสติกสาน ถุงตาข่าย และผ้าใบพลาสติกสาน ที่มีคุณภาพสูง คงทน<br>
				สวยงามและปลอดภัยพร้อมทั้งได้มาตรฐานต่อผู้บริโภคทั้งในและต่างประเทศ<br>
				ด้วยประสบการณ์มากกว่า 43 ปี
			<?php endif ?>
		</div>
	</div>
</section>

<!-- Product ----------------->
<section id="product" data-aos-duration="700" data-aos="fade-up">
	<div class="container">
		<h1 class="head">
			<?= (ICL_LANGUAGE_CODE == 'en') ? 'Our Products' : 'ผลิตภัณฑ์ของเรา'; ?>
			<div class="line">
				<div></div>
				<div></div>
			</div>
		</h1>
		<div class="box-tab mt-3">
			<ul class="nav nav-pills nav-justified align-items-center" id="pills-tab" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" id="pills-1-tab" data-toggle="pill" href="#pills-1" role="tab" aria-controls="pills-1" aria-selected="true">
						<?= (ICL_LANGUAGE_CODE == 'en') ? 'WOVEN SACKS' : 'กระสอบ'; ?>
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" id="pills-3-tab" data-toggle="pill" href="#pills-3" role="tab" aria-controls="pills-3" aria-selected="false">
						<?= (ICL_LANGUAGE_CODE == 'en') ? 'TARPAULIN' : 'ผ้าใบ'; ?>
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" id="pills-4-tab" data-toggle="pill" href="#pills-4" role="tab" aria-controls="pills-4" aria-selected="false">
						<?= (ICL_LANGUAGE_CODE == 'en') ? 'MESH BAGS' : 'ถุงตาข่าย'; ?>
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" id="pills-5-tab" data-toggle="pill" href="#pills-5" role="tab" aria-controls="pills-5" aria-selected="false">
						<?= (ICL_LANGUAGE_CODE == 'en') ? 'PP woven bag' : 'กระเป๋า'; ?>
					</a>
				</li>
			</ul>
			<div class="tab-content" id="pills-tabContent">
				<div class="tab-pane fade show active" id="pills-1" role="tabpanel" aria-labelledby="pills-1-tab">
					<div class="box bg-1 py-4 py-md-5">
						<div class="col-lg-6 col-md-8 text-center">
							<h2><?= get_field('product_type_title_1'); ?></h2>
							<div class="detail">
								<?= get_field('product_type_detail_1'); ?>
							</div>
							<div class="better">
								<h4 class="mt-4"><?= (ICL_LANGUAGE_CODE == 'en') ? 'How are we better?' : 'ดีกว่าอย่างไร?'; ?></h4>
								<?php if (get_field('woven-sacks')) : ?>
									<?php while (the_repeater_field('woven-sacks')) : ?>
										<div class="d-flex align-items-center mt-2">
											<img class="icon" src="<?= $srcdir ?>/images/product1_icon2.svg" alt="icon">
											<div class="text">
												<?php the_sub_field('woven-sacks-detail'); ?>
											</div>
										</div>
									<?php endwhile; ?>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
				<div class="tab-pane fade" id="pills-3" role="tabpanel" aria-labelledby="pills-3-tab">
					<div class="box bg-2 py-4 py-md-5">
						<div class="col-lg-6 col-md-8 text-center">
							<h2><?= get_field('product_type_title_2'); ?></h2>
							<div class="detail">
								<?= get_field('product_type_detail_2'); ?>
							</div>
							<div class="better">
								<h4 class="mt-4"><?= (ICL_LANGUAGE_CODE == 'en') ? 'How are we better?' : 'ดีกว่าอย่างไร?'; ?></h4>
								<?php if (get_field('tarpaulin')) : ?>
									<?php while (the_repeater_field('tarpaulin')) : ?>
										<div class="d-flex align-items-center mt-2">
											<img class="icon" src="<?= $srcdir ?>/images/product1_icon2.svg" alt="icon">
											<div class="text">
												<?php the_sub_field('tarpaulin-detail'); ?>
											</div>
										</div>
									<?php endwhile; ?>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
				<div class="tab-pane fade" id="pills-4" role="tabpanel" aria-labelledby="pills-4-tab">
					<div class="box bg-3 py-4 py-md-5">
						<div class="col-lg-6 col-md-8 text-center">
							<h2><?= get_field('product_type_title_3'); ?></h2>
							<div class="detail">
								<?= get_field('product_type_detail_3'); ?>
							</div>
							<div class="better">
								<h4 class="mt-4"><?= (ICL_LANGUAGE_CODE == 'en') ? 'How are we better?' : 'ดีกว่าอย่างไร?'; ?></h4>
								<?php if (get_field('mesg-bags')) : ?>
									<?php while (the_repeater_field('mesg-bags')) : ?>
										<div class="d-flex align-items-center mt-2">
											<img class="icon" src="<?= $srcdir ?>/images/product1_icon2.svg" alt="icon">
											<div class="text">
												<?php the_sub_field('mesg-bags-detail'); ?>
											</div>
										</div>
									<?php endwhile; ?>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
				<div class="tab-pane fade" id="pills-5" role="tabpanel" aria-labelledby="pills-5-tab">
					<div class="box bg-4 py-4 py-md-5">
						<div class="col-lg-6 col-md-8 text-center">
							<h2><?= get_field('product_type_title_4'); ?></h2>
							<div class="detail">
								<?= get_field('product_type_detail_4'); ?>
							</div>
							<div class="better">
								<h4 class="mt-4"><?= (ICL_LANGUAGE_CODE == 'en') ? 'How are we better?' : 'ดีกว่าอย่างไร?'; ?></h4>
								<?php if (get_field('other-products')) : ?>
									<?php while (the_repeater_field('other-products')) : ?>
										<div class="d-flex align-items-center mt-2">
											<img class="icon" src="<?= $srcdir ?>/images/product1_icon2.svg" alt="icon">
											<div class="text">
												<?php the_sub_field('other-products-detail'); ?>
											</div>
										</div>
									<?php endwhile; ?>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- Trust ------------------->
<section id="trust" data-aos-duration="700" data-aos="fade-up">
	<div class="container text-center">
		<h1 class="head">
			<?= (ICL_LANGUAGE_CODE == 'en') ? 'Trust from national customers' : 'ความไว้วางใจจากลูกค้าระดับประเทศ'; ?>
			<div class="line">
				<div></div>
				<div></div>
			</div>
		</h1>
		<h5 class="my-3">
			<?php if (ICL_LANGUAGE_CODE == 'en') : ?>
				We are honored and trusted to be a part of the success <br> of many leading domestic and international organisations.
			<?php else : ?>
				เราได้รับเกียรติและความไว้วางใจให้เป็นส่วนหนึ่งในความสำเร็จ <br> ขององค์กรชั้นนำมากมายทั้งในและต่างประเทศ ในหลากหลายกลุ่มธุรกิจ
			<?php endif ?>
		</h5>
		<img src="<?= $srcdir ?>/images/trust.png" alt="trust" />
	</div>
</section>

<!-- Quality ----------------->
<section id="quality" data-aos-duration="700" data-aos="fade-up">
	<div class="container h-100">
		<div class="row align-items-center h-100">
			<div class="col-md-6 d-block d-md-none px-0">
				<img class="thumnail" src="<?= $srcdir ?>/images/quality-bg-mobile.png" alt="quality" />
			</div>
			<div class="col-md-6 content text-center text-md-left">
				<h1><?= (ICL_LANGUAGE_CODE == 'en') ? 'The International Organisation for Standardisation (ISO)' : 'รับรองคุณภาพระดับสากล'; ?></h1>
				<p class="pt-3">
					<?php if (ICL_LANGUAGE_CODE == 'en') : ?>
						In 2010, Chaisiri Nylon Canvas Factory Ltd. received
						the “Supplier Award” from IRPC Public Company Ltd. (IRPC)
						and was certified with ISO:9001:2008 in 2011. Currently,
						Chaisiri Nylon Canvas Factory Ltd. is working hard to demonstrate
						your commitment to quality service and customer satisfaction
						by delivering high quality woven bag products and woven fabrics.
					<?php else : ?>
						ในปี 2553 บริษัท โรงงานทอผ้าใบไนล่อนชัยศิริ จำกัด ได้รับรางวัล <br>
						“ซัพพลายเออร์ยอดเยี่ยม” จาก บริษัท ไออาร์พีซี จำกัด (มหาชน) หรือ <br>
						“ไออาร์พีซี” และได้การรับรองมาตรฐาน ISO:9001:2008 ในปี 2554 <br>
						โดยปัจจุบันทาง บริษัท โรงงานทอผ้าใบไนล่อนชัยศิริ จำกัด ได้เล็งเห็น <br>
						ถึงศักยภาพและการเติบโตอย่างยั่งยืนของตลาดบรรจุภัณฑ์กระสอบ <br>
						และ <span class="nowrap">ผ้าใบพลาสติกสาน</span>
					<?php endif ?>
				</p>
				<img class="logo pt-3" src="<?= $srcdir ?>/images/chaisiri_quality_2023.png" alt="quality" />
			</div>
		</div>
	</div>
</section>

<!-- Contact ----------------->
<section id="contact">
	<div class="container">
		<h1 class="head">
			<?= (ICL_LANGUAGE_CODE == 'en') ? 'Contact us' : 'ติดต่อเรา'; ?>
			<div class="line">
				<div></div>
				<div></div>
			</div>
		</h1>
		<div class="row">
			<div class="col-md-7 mt-4">
				<iframe class="map" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3877.1124751046127!2d100.2546456148294!3d13.650921490412857!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0xc997226ada77e47e!2zMTPCsDM5JzAzLjMiTiAxMDDCsDE1JzI0LjYiRQ!5e0!3m2!1sth!2sth!4v1656486824435!5m2!1sth!2sth" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
				<hr>
				<div class="d-block d-md-flex align-items-center justify-content-center flex-wrap">
					<a class="btn p-1">
						<img src="<?= $srcdir ?>/images/phone.png" alt="phone" /> +(66) 034-471742
					</a>
					<div class="br"></div>
					<a class="btn p-1">
						<img src="<?= $srcdir ?>/images/fax.png" alt="fax" /> +(66) 034-471775
					</a>
					<div class="br"></div>
					<a class="btn p-1" href="mailto:sale@chaisiri.com">
						<img src="<?= $srcdir ?>/images/mail.png" alt="mail" /> sale@chaisiri.com
					</a>
					<div class="br"></div>
					<a class="btn p-1" href="https://line.me/ti/p/~salechaisiri3" target="blank">
						<img src="<?= $srcdir ?>/images/line.png" alt="line" />
					</a>
					<a target="blank" class="btn" href="https://www.facebook.com/%E0%B8%9A%E0%B8%A3%E0%B8%B4%E0%B8%A9%E0%B8%B1%E0%B8%97-%E0%B9%82%E0%B8%A3%E0%B8%87%E0%B8%87%E0%B8%B2%E0%B8%99%E0%B8%97%E0%B8%AD%E0%B8%9C%E0%B9%89%E0%B8%B2%E0%B9%83%E0%B8%9A%E0%B9%84%E0%B8%99%E0%B8%A5%E0%B9%88%E0%B8%AD%E0%B8%99%E0%B8%8A%E0%B8%B1%E0%B8%A2%E0%B8%A8%E0%B8%B4%E0%B8%A3%E0%B8%B4-%E0%B8%88%E0%B8%B3%E0%B8%81%E0%B8%B1%E0%B8%94-CSNC-100182412451173/">
						<img src="<?= $srcdir ?>/images/fb.png" alt="facebook" />
					</a>
				</div>
			</div>
			<div class="col-md-5 mt-4">
				<div class="box">
					<?= do_shortcode('[contact-form-7 id="54" title="Contact form"]') ?>
					<!-- <form>
						<div class="form-group">
							<label for="name">ชื่อ-นามสกุล</label>
							<input type="text" class="form-control" id="name">
						</div>
						<div class="form-group">
							<label for="email">อีเมล</label>
							<input type="email" class="form-control" id="email">
						</div>
						<div class="form-group">
							<label for="tel">เบอร์โทรศัพท์</label>
							<input type="text" class="form-control" id="tel">
						</div>
						<div class="form-group">
							<label for="subject">เรื่องที่ต้องการติดต่อ</label>
							<select id="subject" class="form-control">
								<option selected>ทำเอกสาร / ขอใบเสนอราคา</option>
								<option>2</option>
								<option>3</option>
							</select>
						</div>
						<button type="submit" class="btn btn-block btn-send">ส่งข้อมูล</button>
					</form> -->
				</div>
			</div>
		</div>
	</div>
</section>

<?php get_footer();
