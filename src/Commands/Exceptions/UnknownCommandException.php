<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Commands\Exceptions;

use PHPMQ\Server\Exceptions\RuntimeException;

/**
 * Class UnknownCommandException
 * @package PHPMQ\Server\Commands\Exceptions
 */
final class UnknownCommandException extends RuntimeException
{
	/** @var string */
	private $unknownCommandString;

	public function getUnknownCommandString() : string
	{
		return $this->unknownCommandString;
	}

	public function withUnknownCommandString( string $unknownCommandString ) : self
	{
		$this->unknownCommandString = trim( $unknownCommandString );
		$this->message              = sprintf( 'Unknown command: %s', $this->unknownCommandString );

		return $this;
	}
}
