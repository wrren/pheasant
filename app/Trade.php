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
}
