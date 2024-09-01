<?php
get_header();
$srcdir = get_bloginfo('template_directory');
?>
<style>
	#pdpa ul {
		list-style :disc;
		margin-left: 2em;
	}
	#pdpa p {
		text-indent: 3ch;
	}
</style>
<main id="primary" class="site-main">
	<div id="pdpa" class="py-5 mt-3">
		<div class="container">
			<div class="row">
				<div class="col-8 mx-auto">
				<?= get_post_field('post_content', $post->ID) ?>
				</div>
			</div>
		</div>
	</div>
</main><!-- #main -->

<?php get_footer(); ?>