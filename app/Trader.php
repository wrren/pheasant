<?php namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 *	Trader model. A Trader is created when trade data containing a previously
 *	unseen userId is received. Traders are linked to trades through foreign
 *	key association.
 */
class Trader extends Model {
	/// Table Name
	protected $table 	= 'traders';
	/// Accessible Fields
	protected $fillable 	= [ 'ext_id' ];
	/// Don't use the Eloquent timestamps
	public $timestamps 	= false;
	/**
	 *	Traders have a one-to-many relationship with trades. This
	 *	function lets eloquent know what the child model is and
	 *	which foreign key it's associated with.
	 */
	public function trades() {
		return $this->hasMany( 'App\Trade', 'trader' );
	}
}
