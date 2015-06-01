<?php namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 *	Defines a currency, referenced by its short code. Created when a trade
 *	is accepted containing a previously unseen currency code.
 */
class Currency extends Model {
	/// Table Name
	protected $table 	= 'currencies';
	/// Accessible Fields
	protected $fillable	= [ 'name' ];
	/// Don't use the Eloquent timestamps
	public $timestamps 	= false;

	/**
	 *	Get all trades in in which this was the currency sold.
	 */
	public function sales() {
		return $this->hasMany( 'App\Trade', 'from_currency' );
	}

	/**
	 *	Get all trades in which this was the currency bought
	 */
	public function buys() {
		return $this->hasMany( 'App\Trade', 'to_currency' );
	}
}
