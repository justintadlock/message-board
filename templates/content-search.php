<?php
/**
 * This template part outputs the search form page.
 */
?>

<header class="mb-page-header">
	<h1 class="mb-page-title"><?php mb_search_page_title(); ?></h1>
</header><!-- .mb-page-header -->

<?php 
	/* Loads the `form-search-advanced.php` template part. */
	mb_get_template_part( 'form-search', 'advanced' );
?>