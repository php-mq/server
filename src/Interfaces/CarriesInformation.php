<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Interfaces;

/**
 * Interface CarriesInformation
 * @package PHPMQ\Server\Interfaces
 */
interface CarriesInformation
{
	public function getMessageId() : IdentifiesMessage;

	public function getContent() : string;

	public function createdAt() : int;
}
