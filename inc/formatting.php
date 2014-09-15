<?php

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
	$text = str_replace( array( '&#38;','&amp;' ), '&',            $text );
	$text = str_replace( '&#39;',                   "'",           $text );

	if ( '<pre><code>' == $matches[1] )
		$text = "\n$text\n";

	return "`$text`";
}
