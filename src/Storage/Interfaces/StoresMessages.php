<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Storage\Interfaces;

use hollodotme\PHPMQ\Interfaces\CarriesInformation;
use hollodotme\PHPMQ\Interfaces\IdentifiesMessage;
use hollodotme\PHPMQ\Interfaces\IdentifiesQueue;

/**
 * Interface StoresMessages
 * @package hollodotme\PHPMQ\Storage\Interfaces
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
