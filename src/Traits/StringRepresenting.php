<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Traits;

/**
 * Trait StringRepresenting
 * @package hollodotme\PHPMQ\Traits
 */
trait StringRepresenting
{
	abstract public function toString() : string;

	public function __toString() : string
	{
		return $this->toString();
	}

	public function jsonSerialize() : string
	{
		return $this->toString();
	}
}
