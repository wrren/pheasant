<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class TradeEventServerCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'trade:wsserver';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Starts the trade event web socket server';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$loop 	= \React\EventLoop\Factory::create();
		$server = new TradeEventServer();
		$redis 	= new \Predis\Async\Client( 'tcp://127.0.0.1:6379', $loop );

		$redis->connect( array( $server, 'init' ) );

		$webSocket	= new \React\Socket\Server( $loop );
		$webSocket->listen( $this->option( 'listen' ), '0.0.0.0' );
		$socketServer = new \Ratchet\Server\IoServer( new \Ratchet\Http\HttpServer( new \Ratchet\WebSocket\WsServer( $server ) ), $webSocket );

		$loop->run();
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [ [ 'listen', 'l', InputOption::VALUE_OPTIONAL, 'Web Socket Listen Port', 8081 ] ];
	}

}
