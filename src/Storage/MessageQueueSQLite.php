<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace hollodotme\PHPMQ\Storage;

use hollodotme\PHPMQ\Exceptions\RuntimeException;
use hollodotme\PHPMQ\Interfaces\CarriesInformation;
use hollodotme\PHPMQ\Interfaces\IdentifiesMessage;
use hollodotme\PHPMQ\Interfaces\IdentifiesQueue;
use hollodotme\PHPMQ\Storage\Interfaces\ConfiguresMessageQueue;
use hollodotme\PHPMQ\Storage\Interfaces\ProvidesQueueStatus;
use hollodotme\PHPMQ\Storage\Interfaces\StoresMessages;
use hollodotme\PHPMQ\Types\Message;
use hollodotme\PHPMQ\Types\MessageId;
use hollodotme\PHPMQ\Types\MessageQueueStatus;

/**
 * Class MessageQueueSQLite
 * @package hollodotme\PHPMQ\Storage
 */
final class MessageQueueSQLite implements StoresMessages
{
	private const ERROR_CODE_ENQUEUE          = 100;

	private const ERROR_CODE_DEQUEUE          = 200;

	private const ERROR_CODE_MARK_DISPATCHED  = 300;

	private const ERROR_CODE_GET_UNDISPATCHED = 400;

	private const ERROR_CODE_FLUSH_QUEUE      = 500;

	private const ERROR_CODE_QUEUE_STATUS     = 600;

	private const CREATE_TABLE_QUERY          = 'CREATE TABLE IF NOT EXISTS `queue` (
													`messageId` CHAR(32) PRIMARY KEY,
													`queueName` VARCHAR(50),
													`content` TEXT,
													`createdAt` INTEGER,
													`dispatched` INTEGER
												 )';

	/** @var ConfiguresMessageQueue */
	private $config;

	/** @var \PDO */
	private $pdo;

	public function __construct( ConfiguresMessageQueue $config )
	{
		$this->config = $config;
	}

	/**
	 * @param IdentifiesQueue    $queueName
	 * @param CarriesInformation $message
	 *
	 * @throws \hollodotme\PHPMQ\Exceptions\RuntimeException
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
	 * @throws \hollodotme\PHPMQ\Exceptions\RuntimeException
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
	 * @throws \hollodotme\PHPMQ\Exceptions\RuntimeException
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
		}
		catch ( \PDOException $e )
		{
			$this->getPDO()->rollBack();

			throw new RuntimeException(
				'Could not mark message as dispatched with ID: ' . $messageId,
				self::ERROR_CODE_MARK_DISPATCHED,
				$e
			);
		}
	}

	/**
	 * @param IdentifiesQueue $queueName
	 * @param int             $countMessages
	 *
	 * @throws \hollodotme\PHPMQ\Exceptions\RuntimeException
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
			throw new RuntimeException( 'Could not get queue status', self::ERROR_CODE_GET_UNDISPATCHED, $e );
		}
	}

	/**
	 * @param IdentifiesQueue $queueName
	 *
	 * @throws \hollodotme\PHPMQ\Exceptions\RuntimeException
	 */
	public function flushQueue( IdentifiesQueue $queueName ) : void
	{
		$this->getPDO()->beginTransaction();

		try
		{
			$statment = $this->getPDO()->prepare( 'DELETE FROM `queue` WHERE `queueName` = :queueName' );
			$statment->execute( [ 'queueName' => $queueName->toString() ] );

			$this->getPDO()->commit();
		}
		catch ( \PDOException $e )
		{
			$this->getPDO()->rollBack();

			throw new RuntimeException( 'Could not flush queue', self::ERROR_CODE_FLUSH_QUEUE, $e );
		}
	}

	/**
	 * @throws \hollodotme\PHPMQ\Exceptions\RuntimeException
	 */
	public function flushAllQueues() : void
	{
		try
		{
			$this->getPDO()->exec( 'DROP TABLE IF EXISTS `queue`' );
			$this->getPDO()->exec( self::CREATE_TABLE_QUERY );
		}
		catch ( \PDOException $e )
		{
			throw new RuntimeException( 'Could not flush all queues', self::ERROR_CODE_FLUSH_QUEUE, $e );
		}
	}

	/**
	 * @param IdentifiesQueue $queueName
	 *
	 * @throws \hollodotme\PHPMQ\Exceptions\RuntimeException
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
	 * @throws \hollodotme\PHPMQ\Exceptions\RuntimeException
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

			while ( $statusData = (array)$statement->fetch( \PDO::FETCH_ASSOC ) )
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
