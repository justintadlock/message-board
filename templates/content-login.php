<?php if ( !is_user_logged_in() ) : // Check if user is logged in. ?>

	<div class="loop-meta">
		<h1 class="loop-title">Log In</h1>
	</div>
	<p>Not a member yet?  Check out <a href="/club">the club</a>.</p>
	<?php wp_login_form(); ?>

<?php else : ?>

	<div class="loop-meta">
		<h1 class="loop-title">You are logged in</h1>
	</div>
	<p>You are currently logged in. Please browse the site or create a new topic right here in the forums.</p>

<?php endif; // End logged-in check. ?>