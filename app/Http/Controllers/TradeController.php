<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Trade;
use App\Trader;
use App\Origin;
use App\Currency;

use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Illuminate\Http\Request;
use RedisL4;

class TradeController extends Controller {

	/// Publication Channel
	const Channel = 'trade.event';

	/**
	 * Display a list of all trades
	 * @return Response
	 */
	public function index()
	{
		return response()->json( Trade::all()->toJson() );
	}

	/**
	 *	Publish a trade event to redis so that connected web socket clients will receive it.
	 * @param trade 	Trade Object
	 */
	protected function publish( Trade $trade ) {
		$redis = RedisL4::connection();
		$redis->publish( self::Channel, json_encode( [ 	'id'		=> $trade->id,
								'trader'	=> $trade->trader->id,
								'origin'	=> $trade->origin->name,
								'from_currency'	=> $trade->fromCurrency->name,
								'to_currency'	=> $trade->toCurrency->name,
								'from_amount'	=> $trade->from_amount,
								'to_amount'	=> $trade->to_amount,
								'rate'		=> $trade->rate,
								'time'		=> $trade->time ] ) );
	}

	/**
	 * Store an incoming trade, encoded as JSON in the request body, in the database
	 * @return Response
	 */
	public function store( Request $request )
	{
		if( !$request->isJson() )
		{
			return response()->json( ['result' => 'failure', 'error' => 'missing trade data' ], BaseResponse::HTTP_BAD_REQUEST );
		}

		$trade = Trade::fromJSON( $request->json()->all() );

		if( $trade === false )
		{
			return response()->json( ['result' => 'failure', 'error' => 'malformed trade data' ], BaseResponse::HTTP_UNPROCESSABLE_ENTITY );
		}

		$trader 		= Trader::firstOrCreate( [ 'ext_id' 	=> $trade['userId'] ] );
		$origin 		= Origin::firstOrCreate( [ 'name' 	=> $trade['originatingCountry'] ] );
		$fromCurrency 		= Currency::firstOrCreate( [ 'name'	=> $trade['currencyFrom'] ] ); 
		$toCurrency 		= Currency::firstOrCreate( [ 'name'	=> $trade['currencyTo'] ] );
		
		$date 			= date_create_from_format( 'd-M-y H:i:s', ucwords( strtolower( $trade['timePlaced'] ) ) );

		if( $date === false )
		{
			return response()->json( ['result' => 'failure', 'error' => 'malformed time value' ], BaseResponse::HTTP_UNPROCESSABLE_ENTITY );
		}

		$newTrade 		= new Trade;
		$newTrade->time 	= $date;
		$newTrade->from_amount	= $trade['amountSell'];
		$newTrade->to_amount 	= $trade['amountBuy'];
		$newTrade->rate 	= $trade['rate'];

		$newTrade->origin()->associate( $origin );
		$newTrade->trader()->associate( $trader );
		$newTrade->fromCurrency()->associate( $fromCurrency );
		$newTrade->toCurrency()->associate( $toCurrency );

		$newTrade->save();

		$this->publish( $newTrade );

		return response()->json( [ 'result' => 'success' ], BaseResponse::HTTP_CREATED );
	}

	/**
	 * Display a specific trade
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show( $id )
	{
		return response()->json( Trade::find( $id )->toJson() );
	}

}
