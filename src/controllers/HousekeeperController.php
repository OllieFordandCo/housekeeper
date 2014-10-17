<?php namespace OllieFordandCo\Housekeeper;

use \Artdarek\OAuth\Facade\OAuth;
use \Illuminate\Routing\Controller;
use \Illuminate\Support\Facades\Input;
use \Illuminate\Support\Facades\Session;
use \Illuminate\Support\Facades\Response;
use \Illuminate\Support\Facades\URL;
use \Cartalyst\Sentry\Facades\Laravel\Sentry;
use \Illuminate\Support\Facades\Redirect;

class HousekeeperController extends Controller {

	/**
	 * OAuth Social Login
	 *
	 * @return Response
	 */
	public function loginWith($provider)
	{
		$code = Housekeeper::get_code($provider);
		$providerName = ucfirst($provider);
		$providerService = OAuth::consumer($providerName, URL::to('/login/'.$provider));
		if($code) {
			
			// Set the provider session for future use
			Housekeeper::set_oauth_session($provider);
			$token = Housekeeper::get_access_token($providerService, $provider, $code);
			// Send a request with it
			$result = json_decode( $providerService->request( Housekeeper::$Request_User[$provider] ), true );
			
			$user_data = Housekeeper::user_data_from_provider($result, $provider);
			$user = Housekeeper::get_user($user_data['email']);

			if (!$user) {
				
				Housekeeper::create_social_user($user_data);				
	
			} 

			Sentry::login($user, false);			
			return Redirect::to('/');
				

		} else {
			$url = Housekeeper::redirect_to_provider($providerService, $provider);
			return Response::make()->header( 'Location', (string)$url );
		}
	}

}