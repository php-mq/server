<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Interfaces;

/**
 * Interface CarriesInformation
 * @package hollodotme\PHPMQ\Interfaces
 */
interface CarriesInformation
{
	public function getMessageId() : IdentifiesMessage;

	public function getContent() : string;

	public function createdAt() : int;
}
