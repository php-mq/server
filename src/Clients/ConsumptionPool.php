<?php declare(strict_types=1);
/**
 * @author h.woltersdorf
 */

namespace PHPMQ\Server\Clients;

use PHPMQ\Server\Clients\Interfaces\ProvidesConsumptionInfo;
use PHPMQ\Server\Interfaces\IdentifiesStream;

/**
 * Class ConsumptionPool
 * @package PHPMQ\Server\Clients
 */
final class ConsumptionPool
{
	/** @var array|ProvidesConsumptionInfo[] */
	private $consumptionInfos = [];

	public function setConsumptionInfo( IdentifiesStream $streamId, ProvidesConsumptionInfo $consumptionInfo ) : void
	{
		$this->consumptionInfos[ $streamId->toString() ] = $consumptionInfo;
	}

	public function getConsumptionInfo( IdentifiesStream $streamId ) : ProvidesConsumptionInfo
	{
		return $this->consumptionInfos[ $streamId->toString() ] ?? new NullConsumptionInfo();
	}

	public function removeConsumptionInfo( IdentifiesStream $streamId ) : void
	{
		unset( $this->consumptionInfos[ $streamId->toString() ] );
	}
}
