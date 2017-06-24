<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Storage\Interfaces;

use PHPMQ\Server\Interfaces\IdentifiesMessage;
use PHPMQ\Server\Interfaces\IdentifiesQueue;

/**
 * Interface StoresMessages
 * @package PHPMQ\Server\Storage\Interfaces
 */
interface StoresMessages
{
	public function enqueue( IdentifiesQueue $queueName, ProvidesMessageData $message ) : void;

	public function dequeue( IdentifiesQueue $queueName, IdentifiesMessage $messageId ) : void;

	public function markAsDispached( IdentifiesQueue $queueName, IdentifiesMessage $messageId ) : void;

	public function markAsUndispatched( IdentifiesQueue $queueName, IdentifiesMessage $messageId ) : void;

	/**
	 * @param IdentifiesQueue $queueName
	 * @param int             $countMessages
	 *
	 * @return \Generator|ProvidesMessageData[]
	 */
	public function getUndispatched( IdentifiesQueue $queueName, int $countMessages = 1 ) : \Generator;

	public function flushQueue( IdentifiesQueue $queueName ) : void;

	public function flushAllQueues() : void;
}
