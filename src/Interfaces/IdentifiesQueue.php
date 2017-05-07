<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Interfaces;

/**
 * Interface IdentifiesQueue
 * @package PHPMQ\Server\Interfaces
 */
interface IdentifiesQueue extends RepresentsString
{
	public function equals( IdentifiesQueue $other ) : bool;
}
