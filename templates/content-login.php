<?php if ( ! is_user_logged_in() ) : // Check if user is logged in. ?>

	<div class="loop-meta">
		<h1 class="loop-title"><?php _e( 'Log In', 'message-board' ); ?></h1>
	</div>
	<p><?php _e( 'Not a member yet?  Check out <a href="/club">the club</a>.', 'message-board' ); ?></p>
	<?php wp_login_form(); ?>

<?php else : ?>

	<div class="loop-meta">
		<h1 class="loop-title"><?php _e( 'You are logged in', 'message-board' ); ?></h1>
	</div>
	<p><?php _e( 'You are currently logged in. Please browse the site or create a new topic right here in the forums.', 'message-board' ); ?></p>

<?php endif; // End logged-in check. ?>
