<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Storage\Interfaces;

use PHPMQ\Protocol\Interfaces\IdentifiesMessage;

/**
 * Interface ProvidesMessageData
 * @package PHPMQ\Server\Storage\Interfaces
 */
interface ProvidesMessageData
{
	public function getMessageId() : IdentifiesMessage;

	public function getContent() : string;

	public function createdAt() : int;
}
