<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Storage\Interfaces;

use PHPMQ\Server\Interfaces\CarriesInformation;
use PHPMQ\Server\Interfaces\IdentifiesMessage;
use PHPMQ\Server\Interfaces\IdentifiesQueue;

/**
 * Interface StoresMessages
 * @package PHPMQ\Server\Storage\Interfaces
 */
interface StoresMessages
{
	public function enqueue( IdentifiesQueue $queueName, CarriesInformation $message ) : void;

	public function dequeue( IdentifiesQueue $queueName, IdentifiesMessage $messageId ) : void;

	public function markAsDispached( IdentifiesQueue $queueName, IdentifiesMessage $messageId ) : void;

	/**
	 * @param IdentifiesQueue $queueName
	 * @param int             $countMessages
	 *
	 * @return \Generator|CarriesInformation[]
	 */
	public function getUndispatched( IdentifiesQueue $queueName, int $countMessages = 1 ) : \Generator;

	public function flushQueue( IdentifiesQueue $queueName ) : void;

	public function flushAllQueues() : void;

	public function getQueueStatus( IdentifiesQueue $queueName ) : ProvidesQueueStatus;

	/**
	 * @return \Generator|ProvidesQueueStatus[]
	 */
	public function getAllQueueStatus() : \Generator;
}
