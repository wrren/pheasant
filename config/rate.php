<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Rate Limiting
	|--------------------------------------------------------------------------
	|
	| This file is for specifying rate limiting settings such as rate limit
	| window length, limiting factors and max request count
	|
	|
	*/

	// Rate limit window length in seconds
	'window' 	=> 600,

	// Limiting Factors: 'user' for trader/user-based limiting, 'ip' for ip-address limiting
	'factors'	=> [ 'user', 'ip' ],

	// Max requests per window
	'limit'		=> 1000

];
