<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Interfaces;

/**
 * Interface RepresentsString
 * @package PHPMQ\Server\Interfaces
 */
interface RepresentsString extends \JsonSerializable
{
	public function toString() : string;

	public function __toString() : string;
}
