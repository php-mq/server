<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Storage;

use PHPMQ\Server\Exceptions\RuntimeException;
use PHPMQ\Server\Interfaces\CarriesInformation;
use PHPMQ\Server\Interfaces\IdentifiesMessage;
use PHPMQ\Server\Interfaces\IdentifiesQueue;
use PHPMQ\Server\Loggers\Monitoring\Constants\ServerMonitoring;
use PHPMQ\Server\Storage\Interfaces\ConfiguresMessageQueueSQLite;
use PHPMQ\Server\Storage\Interfaces\ProvidesQueueStatus;
use PHPMQ\Server\Storage\Interfaces\StoresMessages;
use PHPMQ\Server\Types\Message;
use PHPMQ\Server\Types\MessageId;
use PHPMQ\Server\Types\MessageQueueStatus;
use Psr\Log\LoggerAwareTrait;

/**
 * Class MessageQueueSQLite
 * @package PHPMQ\Server\Storage
 */
final class MessageQueueSQLite implements StoresMessages
{
	use LoggerAwareTrait;

	private const ERROR_CODE_ENQUEUE           = 100;

	private const ERROR_CODE_DEQUEUE           = 200;

	private const ERROR_CODE_MARK_DISPATCHED   = 300;

	private const ERROR_CODE_MARK_UNDISPATCHED = 310;

	private const ERROR_CODE_GET_UNDISPATCHED  = 400;

	private const ERROR_CODE_FLUSH_QUEUE       = 500;

	private const ERROR_CODE_QUEUE_STATUS      = 600;

	private const CREATE_TABLE_QUERY           = 'BEGIN;
		 CREATE TABLE IF NOT EXISTS `queue` (
			`messageId` CHAR(32),
			`queueName` VARCHAR(50),
			`content` TEXT,
			`createdAt` INTEGER,
			`dispatched` INTEGER
		 );
		 CREATE UNIQUE INDEX messageIdQueueName ON `queue` (`messageId`, `queueName`);
		 COMMIT;';

	/** @var ConfiguresMessageQueueSQLite */
	private $config;

	/** @var \PDO */
	private $pdo;

	public function __construct( ConfiguresMessageQueueSQLite $config )
	{
		$this->config = $config;
	}

	/**
	 * @param IdentifiesQueue    $queueName
	 * @param CarriesInformation $message
	 *
	 * @throws \PHPMQ\Server\Exceptions\RuntimeException
	 */
	public function enqueue( IdentifiesQueue $queueName, CarriesInformation $message ) : void
	{
		$this->getPDO()->beginTransaction();

		try
		{
			$statement = $this->getPDO()->prepare(
				'INSERT INTO `queue` 
					(`messageId`, `queueName`, `content`, `createdAt`, `dispatched`) 
				 VALUES 
				    (:messageId, :queueName, :content, :createdAt, 0)'
			);

			$statement->execute(
				[
					'messageId' => $message->getMessageId()->toString(),
					'queueName' => $queueName->toString(),
					'content'   => $message->getContent(),
					'createdAt' => $message->createdAt(),
				]
			);

			$this->getPDO()->commit();

			$this->logger->debug(
				sprintf(
					'Message with ID %s enqueued in %s.',
					$message->getMessageId()->toString(),
					$queueName->toString()
				),
				[
					'monitoring' => ServerMonitoring::MESSAGE_ENQUEUED,
					'queueName'  => $queueName,
					'message'    => $message,
				]
			);
		}
		catch ( \PDOException $e )
		{
			$this->getPDO()->rollBack();

			throw new RuntimeException(
				'Could not enqueue message with ID ' . $message->getMessageId(),
				self::ERROR_CODE_ENQUEUE,
				$e
			);
		}
	}

	private function getPDO() : \PDO
	{
		if ( null === $this->pdo )
		{
			$this->config->getMessageQueuePath();

			$this->pdo = new \PDO( 'sqlite:' . $this->config->getMessageQueuePath() );
			$this->pdo->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );

			$this->pdo->exec( self::CREATE_TABLE_QUERY );
		}

		return $this->pdo;
	}

	/**
	 * @param IdentifiesQueue   $queueName
	 * @param IdentifiesMessage $messageId
	 *
	 * @throws \PHPMQ\Server\Exceptions\RuntimeException
	 */
	public function dequeue( IdentifiesQueue $queueName, IdentifiesMessage $messageId ) : void
	{
		$this->getPDO()->beginTransaction();

		try
		{
			$statement = $this->getPDO()->prepare(
				'DELETE FROM `queue` 
                 WHERE `queueName` = :queueName
                    AND `messageId` = :messageId'
			);

			$statement->execute(
				[
					'queueName' => $queueName->toString(),
					'messageId' => $messageId->toString(),
				]
			);

			$this->getPDO()->commit();

			$this->logger->debug(
				sprintf(
					'Message with ID %s dequeued from %s.',
					$messageId->toString(),
					$queueName->toString()
				),
				[
					'monitoring' => ServerMonitoring::MESSAGE_DEQUEUED,
					'queueName'  => $queueName,
					'messageId'  => $messageId,
				]
			);
		}
		catch ( \PDOException $e )
		{
			$this->getPDO()->rollBack();

			throw new RuntimeException(
				'Could not dequeue message with ID: ' . $messageId,
				self::ERROR_CODE_DEQUEUE,
				$e
			);
		}
	}

	/**
	 * @param IdentifiesQueue   $queueName
	 * @param IdentifiesMessage $messageId
	 *
	 * @throws \PHPMQ\Server\Exceptions\RuntimeException
	 */
	public function markAsDispached( IdentifiesQueue $queueName, IdentifiesMessage $messageId ) : void
	{
		$this->getPDO()->beginTransaction();

		try
		{
			$statement = $this->getPDO()->prepare(
				'UPDATE `queue` SET `dispatched` = 1 
				 WHERE `queueName` = :queueName 
				    AND `messageId` = :messageId'
			);

			$statement->execute(
				[
					'queueName' => $queueName->toString(),
					'messageId' => $messageId->toString(),
				]
			);

			$this->getPDO()->commit();

			$this->logger->debug(
				sprintf(
					'Message with ID %s marked as dispatched in %s.',
					$messageId->toString(),
					$queueName->toString()
				),
				[
					'monitoring' => ServerMonitoring::MESSAGE_DISPATCHED,
					'queueName'  => $queueName,
					'messageId'  => $messageId,
				]
			);
		}
		catch ( \PDOException $e )
		{
			$this->getPDO()->rollBack();

			throw new RuntimeException(
				'Could not mark message as undispatched with ID: ' . $messageId,
				self::ERROR_CODE_MARK_DISPATCHED,
				$e
			);
		}
	}

	/**
	 * @param IdentifiesQueue   $queueName
	 * @param IdentifiesMessage $messageId
	 *
	 * @throws \PHPMQ\Server\Exceptions\RuntimeException
	 */
	public function markAsUndispatched( IdentifiesQueue $queueName, IdentifiesMessage $messageId ) : void
	{
		$this->getPDO()->beginTransaction();

		try
		{
			$statement = $this->getPDO()->prepare(
				'UPDATE `queue` SET `dispatched` = 0 
				 WHERE `queueName` = :queueName 
				    AND `messageId` = :messageId'
			);

			$statement->execute(
				[
					'queueName' => $queueName->toString(),
					'messageId' => $messageId->toString(),
				]
			);

			$this->getPDO()->commit();

			$this->logger->debug(
				sprintf(
					'Message with ID %s marked as undispatched in %s.',
					$messageId->toString(),
					$queueName->toString()
				),
				[
					'monitoring' => ServerMonitoring::MESSAGE_UNDISPATCHED,
					'queueName'  => $queueName,
					'messageId'  => $messageId,
				]
			);
		}
		catch ( \PDOException $e )
		{
			$this->getPDO()->rollBack();

			throw new RuntimeException(
				'Could not mark message as dispatched with ID: ' . $messageId,
				self::ERROR_CODE_MARK_UNDISPATCHED,
				$e
			);
		}
	}

	/**
	 * @param IdentifiesQueue $queueName
	 * @param int             $countMessages
	 *
	 * @throws \PHPMQ\Server\Exceptions\RuntimeException
	 * @return \Generator|CarriesInformation[]
	 */
	public function getUndispatched( IdentifiesQueue $queueName, int $countMessages = 1 ) : \Generator
	{
		try
		{
			$statement = $this->getPDO()->prepare(
				"SELECT `messageId`, `content`, `createdAt`
				 FROM `queue` 
				 WHERE `queueName` = :queueName
				    AND `dispatched` = 0 
				 ORDER BY `createdAt` ASC 
				 LIMIT {$countMessages}"
			);

			$statement->execute( [ 'queueName' => $queueName->toString() ] );

			while ( $row = $statement->fetch( \PDO::FETCH_ASSOC ) )
			{
				yield new Message( new MessageId( $row['messageId'] ), $row['content'], (int)$row['createdAt'] );
			}
		}
		catch ( \PDOException $e )
		{
			throw new RuntimeException( 'Could not get undispatched messages', self::ERROR_CODE_GET_UNDISPATCHED, $e );
		}
	}

	/**
	 * @param IdentifiesQueue $queueName
	 *
	 * @throws \PHPMQ\Server\Exceptions\RuntimeException
	 */
	public function flushQueue( IdentifiesQueue $queueName ) : void
	{
		$this->getPDO()->beginTransaction();

		try
		{
			$statment = $this->getPDO()->prepare( 'DELETE FROM `queue` WHERE `queueName` = :queueName' );
			$statment->execute( [ 'queueName' => $queueName->toString() ] );

			$this->getPDO()->commit();

			$this->logger->debug(
				sprintf( 'Queue %s flushed.', $queueName->toString() ),
				[
					'monitoring' => ServerMonitoring::QUEUE_FLUSHED,
					'queueName'  => $queueName,
				]
			);
		}
		catch ( \PDOException $e )
		{
			$this->getPDO()->rollBack();

			throw new RuntimeException( 'Could not flush queue', self::ERROR_CODE_FLUSH_QUEUE, $e );
		}
	}

	/**
	 * @throws \PHPMQ\Server\Exceptions\RuntimeException
	 */
	public function flushAllQueues() : void
	{
		try
		{
			$this->getPDO()->exec( 'DROP TABLE IF EXISTS `queue`' );
			$this->getPDO()->exec( self::CREATE_TABLE_QUERY );

			$this->logger->debug(
				'All queues flushed.',
				[
					'monitoring' => ServerMonitoring::ALL_QUEUES_FLUSHED,
				]
			);
		}
		catch ( \PDOException $e )
		{
			throw new RuntimeException( 'Could not flush all queues', self::ERROR_CODE_FLUSH_QUEUE, $e );
		}
	}

	/**
	 * @param IdentifiesQueue $queueName
	 *
	 * @throws \PHPMQ\Server\Exceptions\RuntimeException
	 * @return ProvidesQueueStatus
	 */
	public function getQueueStatus( IdentifiesQueue $queueName ) : ProvidesQueueStatus
	{
		try
		{
			$statement = $this->getPDO()->prepare(
				'SELECT 
					COUNT(1) AS `countTotal`, 
					SUM(CASE WHEN `dispatched` = 0 THEN 1 ELSE 0 END) AS `countUndispatched`, 
					SUM(CASE WHEN `dispatched` = 1 THEN 1 ELSE 0 END) AS `countDispatched` 
				 FROM `queue` WHERE `queueName` = :queueName'
			);

			$statement->execute( [ 'queueName' => $queueName->toString() ] );

			$statusData              = (array)$statement->fetch( \PDO::FETCH_ASSOC );
			$statusData['queueName'] = $queueName->toString();

			return new MessageQueueStatus( $statusData );
		}
		catch ( \PDOException $e )
		{
			throw new RuntimeException( 'Could not get queue status', self::ERROR_CODE_QUEUE_STATUS, $e );
		}
	}

	/**
	 * @throws \PHPMQ\Server\Exceptions\RuntimeException
	 * @return \Generator|ProvidesQueueStatus[]
	 */
	public function getAllQueueStatus() : \Generator
	{
		try
		{
			$statement = $this->getPDO()->query(
				'SELECT `queueName`, 
					COUNT(1) AS `countTotal`, 
					SUM(CASE WHEN `dispatched` = 0 THEN 1 ELSE 0 END) AS `countUndispatched`, 
					SUM(CASE WHEN `dispatched` = 1 THEN 1 ELSE 0 END) AS `countDispatched` 
				 FROM `queue` WHERE 1 GROUP BY `queueName`'
			);

			while ( $statusData = $statement->fetch( \PDO::FETCH_ASSOC ) )
			{
				yield new MessageQueueStatus( $statusData );
			}
		}
		catch ( \PDOException $e )
		{
			throw new RuntimeException( 'Could not get all queue status', self::ERROR_CODE_QUEUE_STATUS, $e );
		}
	}
}
