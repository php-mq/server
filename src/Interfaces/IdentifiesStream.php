<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Interfaces;

/**
 * Interface IdentifiesStream
 * @package PHPMQ\Server\Interfaces
 */
interface IdentifiesStream extends RepresentsString
{
	public function equals( IdentifiesStream $other ) : bool;
}
