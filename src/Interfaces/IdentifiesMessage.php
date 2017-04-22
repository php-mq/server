<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Interfaces;

/**
 * Interface IdentifiesMessage
 * @package hollodotme\PHPMQ\Interfaces
 */
interface IdentifiesMessage extends RepresentsString
{
	public static function generate() : IdentifiesMessage;
}
