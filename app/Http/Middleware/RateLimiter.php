<?php namespace App\Http\Middleware;

use Closure;
use RedisL4;
use Config;

/**
 *	Describes the request window start time and request count for a single user
 */
class RateInfo {
	/// Time at which the window began
	public $windowStart;
	/// Number of requests recorded for this user
	public $requests;

	/**
	 *	Construct a rate info object with its window start time set to 
	 *	the current time, and request count set to zero.
	 */
	public function __construct() {
		$this->windowStart 	= time();
		$this->requests 	= 0;
	}

	/**
	 *	Increment the request count
	 */
	public function increment() {
		++$this->requests;
	}

	/**
	 *	Check whether the given request limit has been exceeded
	 * @param limit Request Count Limit
	 * @return	True - If the given limit was exceeded. False otherwise.
	 */
	public function exceeded( $limit ) {
		return $this->requests > $limit;
	}

	/**
	 *	Check whether the window described by this object has expired, based
	 *	on the given window length parameter.
	 * @param length	Rate window length in seconds
	 * @return		True - If the stored rate window has expired. False otherwise.
	 */
	public function expired( $length ) {
		return ( time() - $this->windowStart ) > $length;
	}
}

/**
 *	Limits the incoming request rate based request count and window
 *	length for various user key types.
 */
class RateLimiter {

	/// Redis Handle
	private $redis;

	/**
	 *	Default Constructor
	 */
	public function __construct() {
		$this->redis = RedisL4::connection();
	}

	/**
	 *	Get the rate limiting factors defined in the application config
	 */
	public static function factors() {
		return Config::get( 'rate.factors', [] );
	}

	/**
	 *	Get the rate limiting window length in seconds as defined in the application config
	 */
	public static function window() {
		return Config::get( 'rate.window', PHP_INT_MAX );
	}

	/**
	 *	Get the rate limit per window interval as defined in the application config
	 */
	public static function limit() {
		return Config::get( 'rate.limit', PHP_INT_MAX );
	}

	/**
	 *	Given a key describing a user, check whether the user has exceeded
	 *	their rate limit as described by the given window length and
	 *	request limit.
	 * @param user		User Key
	 * @param window	Request window length in seconds
	 * @param limit 	Request count limit
	 * @return 		True - If the given user has exceeded their rate limit. False otherwise.
	 */
	protected function exceeded( $user, $window, $limit ) {
		$info 	= $this->redis->get( $user );
		
		if( $info && ( $info = unserialize( $info ) ) !== FALSE )
		{
			if( $info->expired( $window ) )
			{
				$info = new RateInfo;
				$this->store( $user, $info );
				return false;
			}

			if( $info->exceeded( $limit ) )
			{
				return true;
			}
		}
		else
		{
			$info = new RateInfo;
			$this->store( $user, $info );
			return false;
		}
	}

	/**
	 *	Store the given rate info object and associate it with the specified user
	 * @param user	User Key
	 * @param info	Rate Info
	 */
	protected function store( $user, $info ) {
		$this->redis->set( $user, serialize( $info ) );
	}

	/**
	 *	Increment the request count associated with the given user.
	 * @param user	User key
	 */
	protected function increment( $user ) {
		$info = $this->redis->get( $user );

		if( $info && ( $info = unserialize( $info ) ) !== FALSE )
		{
			$info->increment();
			$this->store( $user, $info );
		}
	}
}
