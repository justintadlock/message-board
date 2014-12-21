<?php
/**
 * This template is the fallback template if no other top-level template files can be found in the theme. 
 * This can be overwritten by context-specific templates or by a `/board/board.php` in the theme.
 */

get_header(); // Load theme's header.php template part. ?>

<?php
	/* Filter hook for theme devs to overwrite the theme compat wrap open. */
	echo apply_filters( 'mb_theme_compat_wrap_open', '<div id="content" class="content">' );
?>

	<?php
		/*
		 * Action hook for the plugin to output its content. Technically, what this will 
		 * do is load one of the `content-*.php` template parts for the specific page 
		 * that is being viewed. Themes can either overwrite those template parts or 
		 * overwrite this entire template.
		 */
		do_action( 'mb_theme_compat' );
	?>

<?php
	/* Filter hook for theme devs to overwrite the wrapper close. */
	echo apply_filters( 'mb_theme_compat_wrap_close', '</div><!-- #content -->' );
?>

<?php get_footer(); // Load theme's footer.php template part. ?>