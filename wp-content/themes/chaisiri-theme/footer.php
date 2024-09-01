<?php

/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package chaisiri-theme
 */
$srcdir = get_bloginfo('template_directory');
?>
<!-- Back to top button -->
<!-- <a id="backToTop"><i class="fa-solid fa-arrow-up"></i></a> -->

<div class="share-wrapper">
	<div class="social">
		<ul>
			<li class="phone">
				<a href="tel:+(66) 034-471742" tooltip="+(66) 034-471742" flow="left"><img src="<?= $srcdir ?>/images/icons/call.png" alt="call"></a>
			</li>
			<li class="fax">
				<div tooltip="+(66) 034-471775" flow="left"><img src="<?= $srcdir ?>/images/icons/fax.png" alt="fax"></div>
			</li>
			<li class="mail">
				<a href="mailto:sale@chaisiri.com" tooltip="sale@chaisiri.com" flow="left"><img src="<?= $srcdir ?>/images/icons/email.png" alt="email"></a>
			</li>
			<li class="lines">
				<a href="https://line.me/ti/p/~salechaisiri3" target="_blank" title="line" tooltip="salechaisiri3" flow="left"><img src="<?= $srcdir ?>/images/icons/line.png" alt="line"></a>
			</li>
			<li class="facebook">
				<a href="https://www.facebook.com/%E0%B8%9A%E0%B8%A3%E0%B8%B4%E0%B8%A9%E0%B8%B1%E0%B8%97-%E0%B9%82%E0%B8%A3%E0%B8%87%E0%B8%87%E0%B8%B2%E0%B8%99%E0%B8%97%E0%B8%AD%E0%B8%9C%E0%B9%89%E0%B8%B2%E0%B9%83%E0%B8%9A%E0%B9%84%E0%B8%99%E0%B8%A5%E0%B9%88%E0%B8%AD%E0%B8%99%E0%B8%8A%E0%B8%B1%E0%B8%A2%E0%B8%A8%E0%B8%B4%E0%B8%A3%E0%B8%B4-%E0%B8%88%E0%B8%B3%E0%B8%81%E0%B8%B1%E0%B8%94-CSNC-100182412451173/" target="_blank" tooltip="บริษัท โรงงานทอผ้าใบไนล่อนชัยศิริ จำกัด (CSNC)" flow="left">
					<img src="<?= $srcdir ?>/images/icons/facebook.png" alt="facebook">
				</a>
			</li>
		</ul>
	</div>
	<!-- <i class="fa-brands fa-rocketchat share">test</i> -->
	<div class="share">
		<img src="<?= $srcdir ?>/images/icons/bubble-chat.png" alt="chat">
	</div>
</div>

<footer>
	<div class="container py-4">
		<div class="row align-items-center">
			<div class="col-md-4 text-center text-md-right"><img class="logo" src="<?= $srcdir ?>/images/csnc_logo_white.svg" alt="logo"></div>
			<div class="col-md-8 mt-4 mt-md-0 location">
				<?php if (ICL_LANGUAGE_CODE == 'en') : ?>
					CHAISIRI NYLON CANVAS FACTORY LTD. <br>
					124 MOO 1 DONKAIDEE, KRATHUM BAEN, SAMUT SAKHON, THAILAND 74110
				<?php else : ?>
					บริษัท โรงงานทอผ้าใบไนล่อนชัยศิริ จำกัด<br> 124 หมู่ 1 ตำบล ดอนไก่ดี อำเภอ กระทุ่มแบน จังหวัด สมุทรสาคร 74110
				<?php endif ?>
			</div>
		</div>
	</div>
	<div class="bottom">Copyright © 2021 Chaisiri Nylon Canvas Factory Ltd, All Right Reserved.</div>
</footer>
</div><!-- #page -->

<?php wp_footer(); ?>
</body>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>
	AOS.init();

	function toggleClass(targetElement, addedClass) {
		if (targetElement.classList.contains(addedClass)) {
			targetElement.classList.remove(addedClass);
		} else {
			targetElement.classList.add(addedClass);
		}
	};

	var $ = jQuery;

	$('.menu-btn').click(function(e) {
		e.preventDefault(); // stops link from making page jump to the top
		e.stopPropagation(); // when you click the button, it stops the page from seeing it as clicking the body too
		// This toggleClass is for added 'menu--open' to our menu HTML element. This will open the menu.
		toggleClass(document.querySelector('.menu'), 'menu--open');
		// This toggleClass is for adding '.menu-btn--on' to our menu button HTML element. This creates the animation of the hamburger/hotdog icon to the close icon.
		toggleClass(document.querySelector('.menu-btn'), 'menu-btn--on');
	});

	$('.menu').click(function(e) {
		e.stopPropagation(); // when you click within the content area, it stops the page from seeing it as clicking the body too
	});

	$('body').click(function() {
		$('.menu').removeClass('menu--open');
		$('.menu-btn').removeClass('menu-btn--on');
	});

	$('.menu ul li a').click(function() {
		$('.menu').removeClass('menu--open');
		$('.menu-btn').removeClass('menu-btn--on');
	});
	$('.owl-carousel').owlCarousel({
		loop: false,
		margin: 10,
		responsiveClass: true,
		responsive: {
			0: {
				items: 1,
				nav: false,
				dots: true
			},
			600: {
				items: 3,
				nav: true,
				dots: true
			},
			1000: {
				items: 4,
				nav: true,
				dots: true
			}
		}
	})
	$("#arffrm_100_container .arf_repeater_add_new_button").html('<i class="fa fa-plus fa-md"></i> เพิ่มประวัติ');
	$("#arffrm_100_container .arf_repeater_add_new_button").addClass('order-1');
	$("#arffrm_100_container .arf_repeater_remove_new_button").html('<i class="fa fa-minus fa-md"></i> ลบประวัติ');
	$("#arffrm_100_container .arf_repeater_remove_new_button").addClass('order-2');

	$("#arffrm_103_container .arf_repeater_add_new_button").html('<i class="fa fa-plus fa-md"></i> Add More');
	$("#arffrm_103_container .arf_repeater_add_new_button").addClass('order-1');
	$("#arffrm_103_container .arf_repeater_remove_new_button").html('<i class="fa fa-minus fa-md"></i> Remove');
	$("#arffrm_103_container .arf_repeater_remove_new_button").addClass('order-2');

	$("#arffrm_101_container .arf_repeater_add_new_button").html('<i class="fa fa-plus fa-md"></i> เพิ่มสินค้า');
	$("#arffrm_101_container .arf_repeater_add_new_button").addClass('order-1');
	$("#arffrm_101_container .arf_repeater_remove_new_button").html('<i class="fa fa-minus fa-md"></i> ลบสินค้า');
	$("#arffrm_101_container .arf_repeater_remove_new_button").addClass('order-2');

	$("#arffrm_102_container .arf_repeater_add_new_button").html('<i class="fa fa-plus fa-md"></i> Add More');
	$("#arffrm_102_container .arf_repeater_add_new_button").addClass('order-1');
	$("#arffrm_102_container .arf_repeater_remove_new_button").html('<i class="fa fa-minus fa-md"></i> Remove');
	$("#arffrm_102_container .arf_repeater_remove_new_button").addClass('order-2');

	$("#arffrm_100_container .arf_repeater_field").append("<hr>");
	$("#arffrm_101_container .arf_repeater_field").append("<hr>");
	$("#arffrm_102_container .arf_repeater_field").append("<hr>");
	$("#arffrm_103_container .arf_repeater_field").append("<hr>");

	var btn = $('#backToTop');

	$(window).scroll(function() {
		if ($(window).scrollTop() > 300) {
			btn.addClass('show');
		} else {
			btn.removeClass('show');
		}
	});

	btn.on('click', function(e) {
		e.preventDefault();
		$('html, body').animate({
			scrollTop: 0
		}, '300');
	});

	function share() {
		this.classList.toggle('active');
		document.querySelector('.share-wrapper .social ul').classList.toggle('active');
	}
	document.querySelector('.share').addEventListener('click', share);

	$('.read-more').click(function() {
		$(this).prev().slideToggle();
		// if (($(this).text()) == "Read More") {
		// 	$(this).text("Read Less");
		// } else {
		// 	$(this).text("Read More");
		// }
	});
</script>

</html>