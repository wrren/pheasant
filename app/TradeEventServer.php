<?php

require __DIR__ . '/../vendor/autoload.php';

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Predis\Async\Client as Redis;

/**
 *	Ratchet web socket server. Distributes trade information to connected clients upon
 *	receipt of trade event messages through redis publications.
 */
class TradeEventServer implements MessageComponentInterface {
    	/// Redis Handle
	protected $redis;
	/// Connected Clients
	protected $clients;

	/**
	 *	Construct a trade event server that will subscribe to published trade
	 *	events using the given redis handle.
	 */
	public function __construct( $redis ) {
		$this->clients 	= new \SplObjectStorage;
		$this->redis 	= $redis;

		$this->redis->pubsub( 'trade.event', array( $this, 'onEvent' ) );
	}

	/**
	 *	Called when a new connection is opened. Adds the connection to the client list
	 */
	public function onOpen( ConnectionInterface $client ) {
		$this->clients->attach( $client );
	}

	/**
	 * 	Called when an event is received through Redis. Sends the trade event information
	 *	to all connected clients.
	 */
	public function onEvent( $event, $pubsub ) {
		foreach( $this->clients as $client ) {
			$client->send( $event->payload );
		}
	}

	/**
	 *	Called when a new message is received from a client. Does nothing.
	 */
	public function onMessage( ConnectionInterface $source, $message ) {
		// Do Nothing
	}

	/**
	 *	Called when a client connection is closed. Removes the client from the client list.
	 */
	public function onClose( ConnectionInterface $client ) {
		$this->clients->detach( $client );
	}

	/**
	 *	Something went wrong! Closes the client connection which will trigger its removal
	 *	from the connection list.
	 */
	public function onError( ConnectionInterface $client, \Exception $e ) {
		$client->close();
	}
}

$loop 	= React\EventLoop\Factory::create();
