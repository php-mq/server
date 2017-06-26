<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Events\Interfaces;

use PHPMQ\Server\Clients\MessageQueueClient;

/**
 * Interface ProvidesMessageQueueClient
 * @package PHPMQ\Server\Events\Interfaces
 */
interface ProvidesMessageQueueClient
{
	public function getMessageQueueClient() : MessageQueueClient;
}
