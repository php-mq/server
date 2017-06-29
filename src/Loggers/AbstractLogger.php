<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Loggers;

use Psr\Log\AbstractLogger as PsrAbstractLogger;

/**
 * Class AbstractLogger
 * @package PHPMQ\Server\Loggers
 */
abstract class AbstractLogger extends PsrAbstractLogger
{
	protected function getLogMessage( string $level, string $message, array $context ) : string
	{
		return sprintf(
			'[%s] | %s | %s%s',
			$level,
			date( 'c' ),
			$this->interpolateMessage( $message, $context ),
			!empty( $context ) ? (' | Context: ' . print_r( $context, true )) : ''
		);
	}

	protected function interpolateMessage( string $message, array $context ) : string
	{
		$replace = [];

		foreach ( $context as $key => $value )
		{
			if ( !is_array( $value ) && (!is_object( $value ) || method_exists( $value, '__toString' )) )
			{
				$replace["{{$key}}"] = $value;
			}
		}

		return strtr( $message, $replace );
	}
}
