<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Interfaces;

/**
 * Interface IdentifiesMessage
 * @package PHPMQ\Server\Interfaces
 */
interface IdentifiesMessage extends RepresentsString
{
	public static function generate() : IdentifiesMessage;
}
