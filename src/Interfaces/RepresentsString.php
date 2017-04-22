<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Interfaces;

/**
 * Interface RepresentsString
 * @package hollodotme\PHPMQ\Interfaces
 */
interface RepresentsString extends \JsonSerializable
{
	public function toString() : string;

	public function __toString() : string;
}
