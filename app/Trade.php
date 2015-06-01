<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;

/**
 *	Model for a single trade. Created when trade JSON is received through the
 *	trade controller.
 */
class Trade extends Model {

	/// Table Name
	protected $table 	= 'trades';
	/// Accessible Fields
	protected $fillable	= [ 'from_amount', 'to_amount', 'rate', 'time' ];
	/// Don't use the Eloquent timestamps
	public $timestamps 	= false;

	/**
	 *	Get the trader that executed this trade
	 */
	public function trader() {
		return $this->belongsTo( 'App\Trader' );
	}

	/**
	 *	Get the originating country for this trade
	 */
	public function origin() {
		return $this->belongsTo( 'App\Origin' );
	}

	/**
	 *	Get the currency that was bought in this trade
	 */
	public function toCurrency() {
		return $this->belongsTo( 'App\Currency' );
	}

	/**
	 *	Get the currency that was sold in this trade
	 */
	public function fromCurrency() {
		return $this->belongsTo( 'App\Currency' );
	}

	/**
	 *	Attempt to decode a Trade object from the provided JSON
	 * @param json	JSON String
	 * @return 	Trade Object or false on failure
	 */
	public static function fromJSON( $json )
	{
		$validator = Validator::make(	$json,
						[ 	'userId' 		=> 'required|integer',
							'currencyFrom'		=> 'required|size:3',
							'currencyTo'		=> 'required|size:3',
							'amountSell'		=> 'required|numeric',
							'amountBuy'		=> 'required|numeric',
							'rate'			=> 'required|numeric',
							'timePlaced'		=> 'required',
							'originatingCountry'	=> 'required|size:2' ] );

		if( $validator->fails() ) {
			foreach( $validator->messages() as $message ) {
				Log::info( "Trade Validation Failure: " . $message );
			}

			return false;
		}

		return $json;
	}
}
