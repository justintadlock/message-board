<?php
/**
 * This template part outputs the forum archive content.  The forum archive is the board home page if 
 * the plugin is set to show forums on front (default).
 *
 * Theme authors can overwrite this template by placing a `/board/content-archive-forum.php` template 
 * in their theme folder.
 */
?>

<header class="mb-page-header">
	<h1 class="mb-page-title"><?php the_archive_title(); ?></h1>
</header><!-- .mb-page-header -->

<?php 
	/**
	  Loads the `loop-forum-hierarchical.php` template part if the forum archive should be hierarchical. 
	  Otherwise, it uses the `loop-forum-flat.php` template part.  Falls back to the `loop-forum.php` 
	  template part.
	 */
	mb_get_template_part( 'loop-forum', mb_show_hierarchical_forums() ? 'hierarchical' : 'flat' );
?>

<?php 
	/* Loads the `form-forum-new.php` template part.  Falls back to the `form-forum.php` template part. */
	mb_get_template_part( 'form-forum', 'new' );
?>