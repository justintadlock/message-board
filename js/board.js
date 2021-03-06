jQuery( document ).ready( function() {

	/* ====== Form Handling ====== */

	/* Change editor class so it doesn't overwrite theme styles. */
	jQuery( 'textarea[name="mb_forum_content"], textarea[name="mb_topic_content"], textarea[name="mb_reply_content"]' ).removeClass( 'wp-editor-area' ).addClass( 'mb-editor-area' );

	/* Prevent double submissions of plugin forms. */
	jQuery( '#mb-forum-form, #mb-topic-form, #mb-reply-form' ).on(
		'submit',
		function( e ) {
			jQuery( '[type="submit"]' ).prop( 'disabled', true );
		}
	);

	/* === Forum Form === */

	/* Get the forum title. */
	var mb_forum_title   = jQuery( '#mb-forum-form input[name="mb_forum_title"]' ).attr( 'value' );

	/* If there's no title, disable the submit button. */
	if ( '' == mb_forum_title ) {
		jQuery( '#mb-forum-form input[type="submit"]' ).prop( 'disabled', true );
	}

	/* Wait for changes to the forum title and remove the `disable` attribute if there's a title. */
	jQuery( '#mb-forum-form input[name="mb_forum_title"]' ).on(
		'input',
		function() {
			mb_forum_title = jQuery( this ).attr( 'value' );

			if ( '' != mb_forum_title ) {
				jQuery( '#mb-forum-form input[type="submit"]' ).prop( 'disabled', false );
			} else {
				jQuery( '#mb-forum-form input[type="submit"]' ).prop( 'disabled', true );
			}
		}
	);

	/* === Topic Form === */

	/* Count the number of options in the topic type and status selects. */
	var mb_topic_type_options   = jQuery( 'select#mb_topic_type option' ).length;
	var mb_topic_status_options = jQuery( 'select#mb_post_status option' ).length;

	/* If no more than one topic type option, hide the select. */
	if ( 1 >= mb_topic_type_options ) {
		jQuery( 'select#mb_topic_type' ).closest( '.mb-form-type' ).hide();
	}

	/* If no more than one topic status option, hide the select. */
	if ( 1 >= mb_topic_status_options ) {
		jQuery( 'select#mb_post_status' ).closest( '.mb-form-status' ).hide();
	}

	/* Get the topic title and content. */
	var mb_topic_title   = jQuery( '#mb-topic-form input[name="mb_topic_title"]' ).attr( 'value' );
	var mb_topic_content = jQuery( '#mb-topic-form textarea[name="mb_topic_content"]' ).val();

	/* If there's no title or content, disable the submit button. */
	if ( '' == mb_topic_title || '' == mb_topic_content ) {
		jQuery( '#mb-topic-form input[type="submit"]' ).prop( 'disabled', true );
	}

	/* Wait for changes to the topic title and remove the `disable` attribute if there's a title and content. */
	jQuery( '#mb-topic-form input[name="mb_topic_title"]' ).on(
		'input',
		function() {
			mb_topic_title = jQuery( this ).attr( 'value' );

			if ( '' != mb_topic_title && '' != mb_topic_content ) {
				jQuery( '#mb-topic-form input[type="submit"]' ).prop( 'disabled', false );
			} else {
				jQuery( '#mb-topic-form input[type="submit"]' ).prop( 'disabled', true );
			}
		}
	);

	/* Wait for changes to the topic content and remove the `disable` attribute if there's content and a title. */
	jQuery( '#mb-topic-form textarea[name="mb_topic_content"]' ).on(
		'input',
		function() {
			mb_topic_content = jQuery( '#mb-topic-form textarea[name="mb_topic_content"]' ).val();

			if ( '' != mb_topic_title && '' != mb_topic_content ) {
				jQuery( '#mb-topic-form input[type="submit"]' ).prop( 'disabled', false );
			} else {
				jQuery( '#mb-topic-form input[type="submit"]' ).prop( 'disabled', true );
			}
		}
	);

	/* === Reply Form === */

	/* Get the reply content. */
	var mb_reply_content = jQuery( '#mb-reply-form textarea[name="mb_reply_content"]' ).val();

	/* If there's no content, disable the submit button. */
	if ( '' == mb_reply_content ) {
		jQuery( '#mb-reply-form input[type="submit"]' ).prop( 'disabled', true );
	}

	/* Wait for changes to the reply content and remove the `disable` attribute if there's content. */
	jQuery( '#mb-reply-form textarea[name="mb_reply_content"]' ).on(
		'input',
		function() {
			mb_reply_content = jQuery( '#mb-reply-form textarea[name="mb_reply_content"]' ).val();

			if ( '' != mb_reply_content ) {
				jQuery( '#mb-reply-form input[type="submit"]' ).prop( 'disabled', false );
			} else {
				jQuery( '#mb-reply-form input[type="submit"]' ).prop( 'disabled', true );
			}
		}
	);

});