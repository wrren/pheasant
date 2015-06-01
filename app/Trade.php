<?php namespace App;

use Illuminate\Database\Eloquent\Model;

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
		return $this->belongsTo( 'App\Trader', 'trader' );
	}

	/**
	 *	Get the originating country for this trade
	 */
	public function origin() {
		return $this->belongsTo( 'App\Origin', 'origin' );
	}

	/**
	 *	Get the currency that was bought in this trade
	 */
	public function boughtCurrency() {
		return $this->belongsTo( 'App\Currency', 'to_currency' );
	}

	/**
	 *	Get the currency that was sold in this trade
	 */
	public function soldCurrency() {
		return $this->belongsTo( 'App\Currency', 'from_currency' );
	}

	/**
	 *	Attempt to decode a Trade object from the provided JSON
	 * @param json	JSON String
	 * @return 	Trade Object or false on failure
	 */
	public static function fromJSON( $json )
	{
		$obj = json_decode( $json );

		return ( 	property_exists( $obj, 'userId' ) 		&&
				property_exists( $obj, 'currencyFrom' )		&&
				property_exists( $obj, 'currencyTo' )		&&
				property_exists( $obj, 'amountSell' )		&&
				property_exists( $obj, 'amountBuy' )		&&
				property_exists( $obj, 'rate' )			&&
				property_exists( $obj, 'timePlaced' )		&&
				property_exists( $obj, 'originatingCountry' )	) ? $obj : false;
	}
}
