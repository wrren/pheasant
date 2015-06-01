<?php namespace App\Http\Middleware;

use Closure;

/**
 *	The User Rate Limiter limits the rate at which trades can be submitted
 *	to the server based on the userId field encoded in incoming trade JSON. 
 *	Users are limited to N requests per M second window, as set in 
 *	the application configuration.
 */
class UserRateLimiter {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle( $request, Closure $next )
	{	
		if( !self::shouldLimit() || !$request->isJson() )
		{
			return $next( $request );
		}

		$trade = Trade::fromJSON( $request->json() );

		if( $trade === false )
		{
			return $next( $request );
		}

		$user 		= $trade->userId;
		$window 	= RateLimiter::window();
		$limit		= RateLimiter::limit();

		if( RateLimiter::exceeded( $user, $window, $limit ) )
		{
			return response( '', Response::HTTP_TOO_MANY_REQUESTS )->json( [ 'result' => 'failure', 'error' => 'rate limit exceeded'] );
		}

		return $next( $request );
	}

	/**
	 *	Check whether the application config indicates that
	 *	the IP rate limiter should be used.
	 */
	public static function shouldLimit() {
		return in_array( 'user', RateLimiter::factors() );
	}

}
