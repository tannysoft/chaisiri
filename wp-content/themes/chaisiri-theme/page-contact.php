<?php
get_header();
$srcdir = get_bloginfo('template_directory');
?>

<main id="primary" class="site-main">
	<div id="contact_detail">
		<div class="banner-fade position-relative">
			<div class="about_detail_wrapper">
				<!-- <div class="banner-zoom"></div> -->
				<img class="banner-zoom" src=<?= get_field('banner_head'); ?> alt="banner" />
			</div>
			<div data-aos-duration="1000" data-aos="fade-right" class="box">
				<h1><?= (ICL_LANGUAGE_CODE == 'en') ? 'Contact Us' : 'ติดต่อเรา'; ?></h1>
				<h2><?= (ICL_LANGUAGE_CODE == 'en') ? 'Chaisiri Nylon Canvas Factory Ltd. (CSNC)' : 'บริษัท โรงงานทอผ้าใบไนล่อนชัยศิริ จำกัด(CSNC)'; ?></h2>
			</div>
		</div>
		<div data-aos-duration="1000" data-aos="fade-up" class="position-relative">
			<img class="contact-bg" src="<?= $srcdir ?>/images/contact/logo-bg.png" alt="bg" />
			<div class="container">
				<!-- Map -->
				<iframe class="my-4 my-md-5" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3877.1124751046127!2d100.2546456148294!3d13.650921490412857!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0xc997226ada77e47e!2zMTPCsDM5JzAzLjMiTiAxMDDCsDE1JzI0LjYiRQ!5e0!3m2!1sth!2sth!4v1656486824435!5m2!1sth!2sth" style="border:0;" allowfullscreen="" loading="lazy"></iframe>

				<!-- Faq -->
				<div id="faq">
					<h1>FAQ</h1>
					<h2><?= (ICL_LANGUAGE_CODE == 'en') ? 'Frequently Asked Questions' : 'ข้อมูลคำถาม+คำตอบ ที่พบบ่อยจากลูกค้าและผู้ติดต่อ'; ?></h2>
					<div class="accordion mt-3">

						<?php
						$rows = get_field('faq');
						if ($rows) {
							$i = 1;
							foreach ($rows as $row) {
								$title = $row['faq-title'];
								$detail = $row['faq-detail'];
								$id = $post->ID
						?>
								<?php if ($title) : ?>
									<div class="card">
										<div id="faqhead-<?php echo $i; ?>">
											<a href="#" class="btn btn-header-link collapsed" data-toggle="collapse" data-target="#faq-<?php echo $i; ?>" aria-expanded="true" aria-controls="faq-<?php echo $i; ?>">
												<?= $title ?>
											</a>
										</div>
										<div id="faq-<?php echo $i; ?>" class="collapse" aria-labelledby="faqhead-<?php echo $i; ?>" data-parent="#faq">
											<div class="card-body">
												<?= $detail ?>
											</div>
										</div>
									</div>
								<?php endif ?>
						<?php
								$i++;
							}
						}
						?>

						<!-- <div class="card">
							<div id="faqhead1">
								<a href="#" class="btn btn-header-link collapsed" data-toggle="collapse" data-target="#faq1" aria-expanded="true" aria-controls="faq1">
									<?= (ICL_LANGUAGE_CODE == 'en') ? 'Which sizes are available?' : 'มีขนาดเท่าไรบ้าง'; ?>
								</a>
							</div>
							<div id="faq1" class="collapse" aria-labelledby="faqhead1" data-parent="#faq">
								<div class="card-body">
									<?= (ICL_LANGUAGE_CODE == 'en') ? 'Our products are available in a size of 12-80 inches.' : 'ขนาดตั้งแต่ 12 นิ้ว จนถึง 80 นิ้ว'; ?>
								</div>
							</div>
						</div>
						<div class="card">
							<div id="faqhead2">
								<a href="#" class="btn btn-header-link collapsed" data-toggle="collapse" data-target="#faq2" aria-expanded="true" aria-controls="faq2">
									<?= (ICL_LANGUAGE_CODE == 'en') ? 'How many should the minimum order quantity be?' : 'จำนวนขั้นต่ำในการสั่ง'; ?>
								</a>
							</div>
							<div id="faq2" class="collapse" aria-labelledby="faqhead2" data-parent="#faq">
								<div class="card-body">
									<?php if (ICL_LANGUAGE_CODE == 'en') : ?>
										Upon customers’ order quantity Contact Us <br>
										Website: www.chaisiri.com About Us <br>
										Tel. 034-471742 <br>
										Fax. 034-471775 <br>
										E-mail: sale@chaisiri.com <br>
										Line: salechaisiri3 <br>
										Facebook: Chaisiri_CSNC@hotmail.com
									<?php else : ?>
										ตามความต้องการลูกค้า สามารถติดต่อเรา ตามช่องทาง คือ <br>
										-เว็บไซด์ www.chaisiri.com หน้าติดต่อเรา <br>
										-หมายเลขโทรศัพท์ 034-471742 หรือ แฟ็กซ์ 034-471775 <br>
										-อีเมล์ ( E-mail): sale@chaisiri.com <br>
										-ไลน์ ( Line ) : salechaisiri3 <br>
										-Facebook : Chaisiri_CSNC@hotmail.com
									<?php endif ?>
								</div>
							</div>
						</div>
						<div class="card">
							<div id="faqhead3">
								<a href="#" class="btn btn-header-link collapsed" data-toggle="collapse" data-target="#faq3" aria-expanded="true" aria-controls="faq3">
									<?= (ICL_LANGUAGE_CODE == 'en') ? 'How do laminated bags differ from nonlaminated bags?' : 'แบบเคลือบและไม่เคลือบต่างกันอย่างไร , สังเกตอย่างไร'; ?>
								</a>
							</div>
							<div id="faq3" class="collapse" aria-labelledby="faqhead3" data-parent="#faq">
								<div class="card-body">
									<?php if (ICL_LANGUAGE_CODE == 'en') : ?>
										Film laminated bags will give better appearance with more vivid colours.
										Also, they are moisture-proof. On the other hand, nonlaminated bags are stronger and more flexible.
										Further information could be found on
										www.chaisiri.com Our Products.
									<?php else : ?>
										แบบเคลือบจะมีความเงา สวยงาม แข็งแรง ป้องกันความชื้นและการรั่วซึมได้
										แบบเคลือบกราเวียร์(ฟิล์ม)จะมีความเงา สีสันที่สวยงาม ที่ทันสมัย คมชัด แข็งแรง
										ป้องกันความชื้นและการรั่วซึมได้ แบบไม่เคลือบจะมีความแข็งแรง ยืดหยุ่น สามารถดูจากเว็บไซด์ www.chaisiri.com หน้าผลิตภัณฑ์
									<?php endif ?>
								</div>
							</div>
						</div>
						<div class="card">
							<div id="faqhead4">
								<a href="#" class="btn btn-header-link collapsed" data-toggle="collapse" data-target="#faq4" aria-expanded="true" aria-controls="faq4">
									<?= (ICL_LANGUAGE_CODE == 'en') ? 'How can I see the products?' : 'มีรูปตัวอย่างหรือไม่'; ?>
								</a>
							</div>
							<div id="faq4" class="collapse" aria-labelledby="faqhead4" data-parent="#faq">
								<div class="card-body">
									<?= (ICL_LANGUAGE_CODE == 'en') ? 'Details of products could be found on www.chaisiri.com Our Products.' : 'มีรูปตัวอย่าง สามารถดูรายละเอียดสินค้าได้ที่ www.chaisiri.com หน้าผลิตภัณฑ์'; ?>
								</div>
							</div>
						</div> -->
					</div>
				</div>
				<!-- Form -->
				<div class="position-relative mt-4 mt-md-5" style="z-index: 10;">
					<h1 class="head">
						<?= (ICL_LANGUAGE_CODE == 'en') ? 'Questions' : 'ติดต่อสอบถาม'; ?>
						<div class="line">
							<div></div>
							<div></div>
						</div>
					</h1>
					<div class="mt-3">
						<?= do_shortcode('[contact-form-7 id="76" title="Contact form 2"]') ?>
					</div>
				</div>
			</div>
			<img class="contact-bg2" src="<?= $srcdir ?>/images/contact/logo-bg2.png" alt="bg" />
		</div>
	</div>
</main><!-- #main -->

<?php get_footer(); ?>