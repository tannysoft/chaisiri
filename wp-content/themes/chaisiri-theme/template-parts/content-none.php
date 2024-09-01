<?php

/**
 * Template part for displaying a message that posts cannot be found
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package chaisiri-theme
 */

?>

<section class="container no-results not-found my-5">
	<header class="page-header">
		<h1 class="page-title"><?php esc_html_e('ไม่พบตำแหน่งงานที่ค้นหา', 'chaisiri-theme'); ?></h1>
	</header><!-- .page-header -->

	<div class="page-content">
		<?php
		if (is_home() && current_user_can('publish_posts')) :

			printf(
				'<p>' . wp_kses(
					/* translators: 1: link to WP admin new post page. */
					__('Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'chaisiri-theme'),
					array(
						'a' => array(
							'href' => array(),
						),
					)
				) . '</p>',
				esc_url(admin_url('post-new.php'))
			);

		elseif (is_search()) :
		?>
			<p><?php esc_html_e('ขออภัย ไม่มีอะไรตรงกับคำค้นหาของคุณ โปรดลองอีกครั้งโดยใช้คำหลักอื่น', 'chaisiri-theme'); ?></p>
		<?php
		else :
		?>
			<p><?php esc_html_e('ดูเหมือนว่าเราไม่สามารถค้นหาสิ่งที่คุณกำลังมองหาได้ บางทีการค้นหาอาจช่วยได้', 'chaisiri-theme'); ?></p>
		<?php
		endif;
		?>
	</div><!-- .page-content -->
</section><!-- .no-results -->