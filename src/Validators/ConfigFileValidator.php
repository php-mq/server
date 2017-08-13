<?php declare(strict_types=1);
/**
 * @author hollodotme
 */

namespace PHPMQ\Server\Validators;

use PHPMQ\Server\Validators\Interfaces\ValidatesEnvironment;

/**
 * Class ConfigFileValidator
 * @package PHPMQ\Server\Validators
 */
final class ConfigFileValidator implements ValidatesEnvironment
{
	/** @var string */
	private $configFilePath;

	/** @var bool|\SimpleXMLElement */
	private $xml;

	/** @var bool */
	private $passed;

	/** @var array */
	private $messages;

	/** @var array|\LibXMLError[] */
	private $xmlErrors;

	public function __construct( string $configFilePath )
	{
		$this->configFilePath = $configFilePath;
		$this->passed         = false;
		$this->messages       = [];

		libxml_use_internal_errors( true );

		$this->xml       = simplexml_load_file( $configFilePath );
		$this->xmlErrors = libxml_get_errors();

		libxml_use_internal_errors( false );
	}

	public function failed() : bool
	{
		$this->checkForParseErrors();

		$this->passed && $this->checkMessageQueueServer();
		$this->passed && $this->checkMaintenanceServer();
		$this->passed && $this->checkStorage();

		return !$this->passed;
	}

	private function checkForParseErrors() : void
	{
		if ( $this->xml === false )
		{
			$this->addErrorMessage( 'Could not read configuration file: "%s". Parse errors:', $this->configFilePath );
			$this->addXmlErrors();

			return;
		}

		$this->passed = true;
	}

	private function addErrorMessage( string $format, ...$params ) : void
	{
		$this->messages[] = sprintf( '<bg:red>ERROR:<:bg> ' . $format, ...$params );
	}

	private function addXmlErrors() : void
	{
		/** @var \LibXMLError $xmlError */
		foreach ( $this->xmlErrors as $xmlError )
		{
			$this->addErrorMessage(
				'[%s] %s in line %d and column %d',
				$xmlError->code,
				$xmlError->message,
				$xmlError->line,
				$xmlError->column
			);
		}
	}

	private function checkMessageQueueServer() : void
	{
		$baseXPath = '/PHPMQ/servers/messagequeue';

		if ( !$this->elementExists( $baseXPath ) )
		{
			$this->passed = false;
			$this->addErrorMessage( 'Missing configuration part: servers/messagequeue' );

			return;
		}

		if ( !$this->oneElementOfListExists( $baseXPath, [ 'network', 'unix' ] ) )
		{
			$this->passed = false;
			$this->addErrorMessage( 'Invalid message queue server socket type. Allowed: network, unix' );

			return;
		}

		$networkXPath            = $baseXPath . '/network';
		$networkMandatoryConfigs = [ 'host', 'port' ];

		if ( $this->elementExists( $networkXPath )
		     && !$this->mandatoryConfigValuesExist( $networkXPath, $networkMandatoryConfigs )
		)
		{
			$this->passed = false;
			$this->addErrorMessage(
				'Invalid message queue server socket config. Values for %s must be configured.',
				implode( ', ', $networkMandatoryConfigs )
			);

			return;
		}

		$unixXPath            = $baseXPath . '/unix';
		$unixMandatoryConfigs = [ 'path' ];

		if ( $this->elementExists( $unixXPath )
		     && !$this->mandatoryConfigValuesExist( $unixXPath, $unixMandatoryConfigs )
		)
		{
			$this->passed = false;
			$this->addErrorMessage(
				'Invalid message queue server socket config. Values for %s must be configured.',
				implode( ', ', $networkMandatoryConfigs )
			);

			return;
		}
	}

	private function oneElementOfListExists( string $baseXPath, array $elements ) : bool
	{
		foreach ( $elements as $element )
		{
			if ( $this->elementExists( $baseXPath . '/' . $element ) )
			{
				return true;
			}
		}

		return false;
	}

	private function elementExists( string $xpath ) : bool
	{
		return (count( $this->xml->xpath( $xpath ) ) >= 1);
	}

	private function mandatoryConfigValuesExist( string $baseXPath, array $names ) : bool
	{
		foreach ( $names as $name )
		{
			if ( !$this->configValueExists( $baseXPath, $name ) )
			{
				return false;
			}
		}

		return true;
	}

	private function configValueExists( string $baseXPath, string $name ) : bool
	{
		return (count( $this->xml->xpath( $baseXPath . "/config[@name=\"{$name}\"][@value]" ) ) >= 1);
	}

	private function checkMaintenanceServer() : void
	{
		$baseXPath = '/PHPMQ/servers/maintenance';

		if ( !$this->elementExists( $baseXPath ) )
		{
			return;
		}

		if ( !$this->oneElementOfListExists( $baseXPath, [ 'network', 'unix' ] ) )
		{
			$this->passed = false;
			$this->addErrorMessage( 'Invalid maintenance server socket type. Allowed: network, unix' );

			return;
		}

		$networkXPath            = $baseXPath . '/network';
		$networkMandatoryConfigs = [ 'host', 'port' ];

		if ( $this->elementExists( $networkXPath )
		     && !$this->mandatoryConfigValuesExist( $networkXPath, $networkMandatoryConfigs )
		)
		{
			$this->passed = false;
			$this->addErrorMessage(
				'Invalid maintenance server socket config. Values for %s must be configured.',
				implode( ', ', $networkMandatoryConfigs )
			);

			return;
		}

		$unixXPath            = $baseXPath . '/unix';
		$unixMandatoryConfigs = [ 'path' ];

		if ( $this->elementExists( $unixXPath )
		     && !$this->mandatoryConfigValuesExist( $unixXPath, $unixMandatoryConfigs )
		)
		{
			$this->passed = false;
			$this->addErrorMessage(
				'Invalid maintenance server socket config. Values for %s must be configured.',
				implode( ', ', $networkMandatoryConfigs )
			);

			return;
		}
	}

	private function checkStorage() : void
	{
		$baseXPath = '/PHPMQ/storage';

		if ( !$this->elementExists( $baseXPath ) )
		{
			$this->passed = false;
			$this->addErrorMessage( 'Missing configuration part: storage' );

			return;
		}

		if ( !$this->oneElementOfListExists( $baseXPath, [ 'sqlite', 'redis' ] ) )
		{
			$this->passed = false;
			$this->addErrorMessage( 'Invalid storage type. Allowed: sqlite, redis' );

			return;
		}

		$sqliteXPath            = $baseXPath . '/sqlite';
		$sqliteMandatoryConfigs = [ 'path' ];

		if ( $this->elementExists( $sqliteXPath )
		     && !$this->mandatoryConfigValuesExist( $sqliteXPath, $sqliteMandatoryConfigs )
		)
		{
			$this->passed = false;
			$this->addErrorMessage(
				'Invalid storage config. Values for %s must be configured.',
				implode( ', ', $sqliteMandatoryConfigs )
			);

			return;
		}

		$redisXPath            = $baseXPath . '/redis';
		$redisMandatoryConfigs = [
			'host', 'port', 'database', 'timeout', 'password', 'prefix', 'backgroundSaveBehaviour',
		];

		if ( $this->elementExists( $redisXPath )
		     && !$this->mandatoryConfigValuesExist( $redisXPath, $redisMandatoryConfigs )
		)
		{
			$this->passed = false;
			$this->addErrorMessage(
				'Invalid storage config. Values for %s must be configured.',
				implode( ', ', $redisMandatoryConfigs )
			);

			return;
		}
	}

	public function getMessages() : array
	{
		return $this->messages;
	}
}
