<?php namespace App\Http\Middleware;

use Closure;
use Request;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

/**
 *	The IP Rate Limiter limits the rate at which clients can communicate
 *	with the server based on the request source IP. Requests are limited
 *	to N requests per M second window, as set in the application
 *	configuration.
 */
class IPRateLimiter extends RateLimiter {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle( $request, Closure $next )
	{	
		if( !self::shouldLimit() )
		{
			return $next( $request );
		}

		$ip 		= Request::getClientIp();
		$window 	= RateLimiter::window();
		$limit		= RateLimiter::limit();

		if( RateLimiter::exceeded( $ip, $window, $limit ) )
		{
			return response()->json( [ 'result' => 'failure', 'error' => 'rate limit exceeded'], BaseResponse::HTTP_TOO_MANY_REQUESTS );
		}

		RateLimiter::increment( $ip );

		return $next( $request );
	}

	/**
	 *	Check whether the application config indicates that
	 *	the IP rate limiter should be used.
	 */
	public static function shouldLimit() {
		return in_array( 'ip', RateLimiter::factors() );
	}

}
