<?php namespace App\Http\Middleware;

use Closure;

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
			return response( '', Response::HTTP_TOO_MANY_REQUESTS )->json( [ 'result' => 'failure', 'error' => 'rate limit exceeded'] );
		}

		
	}

	/**
	 *	Check whether the application config indicates that
	 *	the IP rate limiter should be used.
	 */
	public static function shouldLimit() {
		return in_array( 'ip', RateLimiter::factors() );
	}

}
