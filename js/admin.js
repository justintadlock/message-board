jQuery( document ).ready( function() {

	/* ====== Post Screen (post.php, post-new.php) ====== */

	/* === Post Status Select === */

	/* Get the original post status in the case the user clicks "Cancel". */
	var orig_status = jQuery( 'select[name=post_status] option:selected' ).val();

	/* When user clicks the "Edit" post status link. */
	jQuery( 'a.edit-post-status' ).click(
		function( j ) {
			j.preventDefault();

			/* Grab the original status again in case user clicks "OK" or "Cancel" more than once. */
			orig_status = jQuery( 'select[name=post_status] option:selected' ).val();

			/* Hide this link. */
			jQuery( this ).hide();

			/* Open the post status select section. */
			jQuery( '#post-status-select' ).slideToggle();
		}
	);

	/* When the user clicks the "OK" post status button. */
	jQuery( 'a.save-post-status' ).click(
		function( j ) {
			j.preventDefault();

			/* Close the post status select section. */
			jQuery( '#post-status-select' ).slideToggle();

			/* Show the hidden "Edit" link. */
			jQuery( 'a.edit-post-status' ).show();
		}
	);

	/* When the user clicks the "Cancel" post status link. */
	jQuery( 'a.cancel-post-status' ).click(
		function( j ) {
			j.preventDefault();

			/* Close the post status select section. */
			jQuery( '#post-status-select' ).slideToggle();

			/* Show the hidden "Edit" link. */
			jQuery( 'a.edit-post-status' ).show();

			/* Check the original status radio since we're canceling. */
			jQuery( 'option[value="' + orig_status + '"]' ).attr( 'selected', true );

			/* Change the post status text. */
			jQuery( 'strong.mb-current-status' ).text( 
				jQuery( 'option[value="' + orig_status + '"]' ).text()
			);
		}
	);

	/* When a new status is selected, change the post status text to match the selected status. */
	jQuery( 'select[name=post_status]' ).change(
		function() {
			jQuery( 'strong.mb-current-status' ).text( 
				jQuery( 'option:selected', this ).text()
			);
		}
	);

});