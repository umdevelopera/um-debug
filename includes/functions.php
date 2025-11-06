<?php
/**
 * Functions of the "UM Debug tools" plugin.
 *
 * @package um_ext\um_debug
 */

/**
 * Save variable to display it in the profiling section.
 *
 * @global \umd $umd
 * @param mixed  $var       Variable data.
 * @param string $key       Optional label for the variable.
 * @param bool   $dublicate Save the variable again if it repeats. Default false.
 * @return \umd
 */
function umd( $var = null, $key = null, $dublicate = false ) {
	global $umd;
	if ( ! is_null( $var ) ) {
		$umd->profiling()->save_var( $var, $key, $dublicate );
	}
	return $umd;
}

/**
 * Save current backtrace to display it in the profiling section.
 *
 * @param string $key Optional label for the backtrace.
 * @return array Elements from debug_backtrace.
 */
function umdb( $key = null ) {
	$backtrace = debug_backtrace( 2 );
	array_shift( $backtrace );
	umd()->profiling()->save_backtrace( $backtrace, $key );
	return $backtrace;
}
