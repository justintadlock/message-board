<?php
/**
 * This template part outputs the login page content.
 */
?>

<header class="mb-page-header">
	<h1 class="mb-page-title"><?php is_user_logged_in() ? _e( 'Logged In', 'message-board' ) : _e( 'Log In', 'message-board' ); ?></h1>
</header><!-- .mb-page-header -->

<?php if ( is_user_logged_in() ) : // Checks if the user is already logged into the site. ?>

	<?php echo wpautop( 'You are currently logged in. Feel free to participate in the forums.', 'message-board' ); ?>

<?php else : // If the user is not logged in. ?>

	<?php wp_login_form( array( 'redirect' => mb_get_board_url() ) ); ?>

<?php endif; // End logged-in check. ?>