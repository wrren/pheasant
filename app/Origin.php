<?php namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 *	Origins describe the originating country of a trade. Origins are created
 *	when a trade is accepted that contains a previously unseen origin shortcode.
 */
class Origin extends Model {
	/// Table Name
	protected $table 	= 'origins';
	/// Accessible Fields
	protected $fillable	= [ 'name' ];

	/**
	 *	Get all trades that took place in this origin.
	 */
	public function trades() {
		return $this->hasMany( 'App\Trade', 'origin' );
	}
}
