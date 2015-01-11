<?php
/**
 * Formatting functions. These mostly deal with formatting the content.
 *
 * @package    MessageBoard
 * @subpackage Includes
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2014, Justin Tadlock
 * @link       https://github.com/justintadlock/message-board
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/**
 * Post kses filter for topics/replies.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $content
 * @return string
 */
function mb_filter_post_kses( $content ) {
	return current_user_can( 'unfiltered_html' ) ? $content : wp_filter_post_kses( $content );
}

/**
 * Function for using backticks to wrap text in code tags. This is code from the original standalone 
 * bbPress software (not the plugin).
 *
 * @author    bbPress
 * @license   http://www.gnu.org/licenses/gpl-2.0.html
 * @link      http://bbpress.org
 * @link      http://bbpress.org/download/legacy/
 *
 * @since  1.0.0
 * @access public
 * @param  string  $text
 * @return string
 */
function mb_code_trick( $text ) {

	$text = str_replace( array( "\r\n", "\r" ), "\n", $text );

	$text = preg_replace_callback( "|(`)(.*?)`|",      'mb_encodeit', $text );
	$text = preg_replace_callback( "!(^|\n)`(.*?)`!s", 'mb_encodeit', $text );

	return $text;
}

/**
 * Function for reversing the `mb_code_trick()` output. This is code from the original standalone 
 * bbPress software (not the plugin).
 *
 * @author    bbPress
 * @license   http://www.gnu.org/licenses/gpl-2.0.html
 * @link      http://bbpress.org
 * @link      http://bbpress.org/download/legacy/
 *
 * @since  1.0.0
 * @access public
 * @param  string  $text
 * @return string
 */
function mb_code_trick_reverse( $text ) {

	$text = preg_replace_callback( "!(<pre><code>|<code>)(.*?)(</code></pre>|</code>)!s", 'mb_decodeit', $text );

	$text = str_replace( array('<p>', '<br />'), '',       $text );
	$text = str_replace( '</p>',                  "\n",     $text );
	$text = str_replace( '<coded_br />',          '<br />', $text );
	$text = str_replace( '<coded_p>',             '<p>',    $text );
	$text = str_replace( '</coded_p>',            '</p>',   $text );

	return $text;
}

/**
 * Function for encoding HTML and wrapping the output in `<code>` and `<pre>` tags.  Used along with the
 * `mb_code_trick()` function as a callback. This is code from the original standalone bbPress software 
 * (not the plugin).
 *
 * @author    bbPress
 * @license   http://www.gnu.org/licenses/gpl-2.0.html
 * @link      http://bbpress.org
 * @link      http://bbpress.org/download/legacy/
 *
 * @since  1.0.0
 * @access public
 * @param  string  $text
 * @return string
 */
function mb_encodeit( $matches ) {

	$text = trim( $matches[2] );
	$text = htmlspecialchars( $text, ENT_QUOTES );

	$text = str_replace(  array( "\r\n", "\r" ), "\n",   $text );
	$text = preg_replace( "|\n\n\n+|",           "\n\n",  $text );
	$text = str_replace(  '&amp;amp;',           '&amp;', $text );
	$text = str_replace(  '&amp;lt;',            '&lt;',  $text );
	$text = str_replace(  '&amp;gt;',            '&gt;',  $text );
	$text = str_replace( array( '&quot;' ),                   '"',           $text );

	$text = str_replace( '[', '&#091;', $text );

	$text = "<code>$text</code>";

	if ( "`" != $matches[1] )
		$text = "<pre>$text</pre>";

	return $text;
}

/**
 * Function for decoding encoded HTML and wrapping the output in backtick (`) characters.  Used along with 
 * the `mb_code_trick_reverse()` function as a callback. This is code from the original standalone bbPress 
 * software (not the plugin).
 *
 * @author    bbPress
 * @license   http://www.gnu.org/licenses/gpl-2.0.html
 * @link      http://bbpress.org
 * @link      http://bbpress.org/download/legacy/
 *
 * @since  1.0.0
 * @access public
 * @param  string  $text
 * @return string
 */
function mb_decodeit( $matches ) {

	$text = $matches[2];
	$trans_table = array_flip( get_html_translation_table( HTML_ENTITIES ) );
	$text = strtr( $text, $trans_table );

	$text = str_replace( '<br />',                 '<coded_br />', $text );
	$text = str_replace( '<p>',                    '<coded_p>',    $text );
	$text = str_replace( '</p>',                   '</coded_p>',   $text );
	$text = str_replace( array( '&#038;', '&#38;','&amp;' ), '&',            $text );
	$text = str_replace( array( '&#039;', '&#39;' ),                   "'",           $text );
	$text = str_replace( array( '&quot;' ),                   '"',           $text );

	if ( '<pre><code>' == $matches[1] )
		return preg_replace( "|\n\n\n+|",           "\n\n",  "\n`\n$text\n`" );

	return "`$text`";
}

/**
 * Helper function.
 *
 * @author    bbPress
 * @license   http://www.gnu.org/licenses/gpl-2.0.html
 * @link      http://bbpress.org
 * @link      http://bbpress.org/download/legacy/
 *
 * @since  1.0.0
 * @access private
 * @param  string  $text
 * @param  string  $key
 * @param  string  $preg
 * @return string
 */
function _mb_encode_bad_empty( &$text, $key, $preg ) {
	if ( strpos( $text, '`' ) !== 0 )
		$text = preg_replace( "|&lt;($preg)\s*?/*?&gt;|i", '<$1 />', $text );
}

/**
 * Helper function.
 *
 * @author    bbPress
 * @license   http://www.gnu.org/licenses/gpl-2.0.html
 * @link      http://bbpress.org
 * @link      http://bbpress.org/download/legacy/
 *
 * @since  1.0.0
 * @access private
 * @param  string  $text
 * @param  string  $key
 * @param  string  $preg
 * @return string
 */
function _mb_encode_bad_normal( &$text, $key, $preg ) {
	if ( strpos( $text, '`' ) !== 0 )
		$text = preg_replace( "|&lt;(/?$preg)&gt;|i", '<$1>', $text );
}

/**
 * Helper function.
 *
 * @author    bbPress
 * @license   http://www.gnu.org/licenses/gpl-2.0.html
 * @link      http://bbpress.org
 * @link      http://bbpress.org/download/legacy/
 *
 * @since  1.0.0
 * @access public
 * @param  string  $text
 * @param  string  $key
 * @param  string  $preg
 * @return string
 */
function mb_encode_bad( $text ) {

	$text = preg_split( '@(`[^`]*`)@m', $text, -1, PREG_SPLIT_NO_EMPTY + PREG_SPLIT_DELIM_CAPTURE );

	$allowed = mb_allowed_tags();
	$empty = array( 'br' => true, 'hr' => true, 'img' => true, 'input' => true, 'param' => true, 'area' => true, 'col' => true, 'embed' => true );

	foreach ( $allowed as $tag => $args ) {
		$preg = $args ? "$tag(?:\s.*?)?" : $tag;

		if ( isset( $empty[ $tag ] ) )
			array_walk( $text, '_mb_encode_bad_empty', $preg );
		else
			array_walk( $text, '_mb_encode_bad_normal', $preg );
	}

	return join( '', $text );
}

/**
 * Returns allowed tags.
 *
 * @since  1.0.0
 * @access public
 * @return array
 */
function mb_allowed_tags() {

	$not_allowed = array(
		'area',
		'aside',
		'article',
		'button',
		'col',
		'colgroup',
		'details',
		'div',
		'fieldset',
		'font',
		'footer',
		'form',
		'header',
		'hgroup',
		'input',
		'label',
		'legend',
		'map',
		'menu',
		'nav',
		'option',
		'section',
		'select',
		'summary',
		'textarea',
		'title'
	);

	$allowed = wp_kses_allowed_html( 'post' );

	foreach ( $not_allowed as $remove ) {
		if ( isset( $allowed[ $remove ] ) )
			unset( $allowed[ $remove ] );
	}

	return apply_filters( 'mb_allowed_tags', $allowed );
}
