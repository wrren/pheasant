<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class TradeController extends Controller {

	/**
	 * Display a list of all trades
	 * @return Response
	 */
	public function index()
	{
		return response()->json( Trade::all()->toJson() );
	}

	/**
	 * Store an incoming trade, encoded as JSON in the request body, in the database
	 * @return Response
	 */
	public function store()
	{
		if( !$request->isJson() )
		{
			return response( '', Response::HTTP_BAD_REQUEST )->json( ['result' => 'failure', 'error' => 'missing trade data' ] );
		}

		$trade = Trade::fromJSON( $request->json() );

		if( $trade === false )
		{
			return response( '', Response::HTTP_BAD_REQUEST )->json( ['result' => 'failure', 'error' => 'malformed trade data' ] );
		}

		$trader 		= Trader::create( [ 'ext_id' 	=> $trade->userId ] );
		$origin 		= Origin::create( [ 'name' 	=> $trade->originatingCountry ] );
		$fromCurrency 		= Currency::create( [ 'name'	=> $trade->currencyFrom ] ); 
		$toCurrency 		= Currency::create( [ 'name'	=> $trade->currencyTo ] );
		$newTrade 		= new Trade;
		$newTrade->time 	= DateTime::createFromFormat( $trade->timePlaced, '%d-%M-%y %H:%i:%s' )->getTimestamp();
		$newTrade->from_amount	= $trade->amountSell;
		$newTrade->to_amount 	= $trade->amountBuy;
		$newTrade->rate 	= $trade->rate;

		$newTrade->origin()->associate( $origin );
		$newTrade->trader()->associate( $trader );
		$newTrade->fromCurrency()->associate( $fromCurrency );
		$newTrade->toCurrency()->associate( $toCurrency );

		$newTrade->save();

		return response()->json( [ 'result' => 'success' ] );
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
