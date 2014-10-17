<?php namespace OllieFordandCo\Housekeeper;

use \Illuminate\Support\Facades\Session;

class Housekeeper 
{

	public static $OAuth_Version = array(	
		'twitter' => 'OAuth1',
		'facebook' => 'OAuth2',
		'freshbooks' => 'OAuth1',
		'google' => 'OAuth2',
		'linkedin' => 'OAuth2'
	);

	public static $Request_User = array(	
		'twitter' => 'account/verify_credentials.json',
		'facebook' => '/me',
		'freshbooks' => 'xml',
		'linkedin' => '/people/~?format=json',
		'google' => 'https://www.googleapis.com/oauth2/v1/userinfo'
	);

	public static function set_oauth_session($provider) {
		if (!Session::has('intranet.oauth.provider'))
		{
			$oauthSession = array(
				'provider' => $provider
			);			
			Session::put('intranet.oauth', $oauthSession);
		}
	}

	public static function get_code($provider) {		
			
		if(array_key_exists($provider, Housekeeper::$OAuth_Version)) {
		
			switch (Housekeeper::$OAuth_Version[$provider]) {
			  case "OAuth2":
				// Get code from facebook
				return \Input::get( 'code' );
			  case "OAuth1":
				// Get code from twitter
				$oauth_token = \Input::get( 'oauth_token' );
				$oauth_verifier = \Input::get( 'oauth_verifier' );
				if(!empty($oauth_token) && !empty($oauth_verifier)) {
					return array($oauth_token, $oauth_verifier);			
				} else {
					return false;
				}			  				
			}		
		
		}
		
		return false;
	}

	public static function redirect_to_provider($providerService, $provider) {	
			
		switch (Housekeeper::$OAuth_Version[$provider]) {
		  case "OAuth1":
			// extra request needed for oauth1 to request a request token :-)
			$token = $providerService->requestRequestToken();
			$auth_uri_params = array('oauth_token' => $token->getRequestToken());				
		  break;
		  default:
			$auth_uri_params = array();
		  break;					
		}		

		$url = $providerService->getAuthorizationUri($auth_uri_params);
		return $url;					
	}

	public static function get_access_token($providerService, $provider, $code) {
		if(array_key_exists($provider, Housekeeper::$OAuth_Version)) {
		
			switch (Housekeeper::$OAuth_Version[$provider]) {
			  case "OAuth1":
			  
				$token = $providerService->requestAccessToken( $code[0], $code[1] );
				
			  break;	
			  case "OAuth2":
			  
			  	$token = $providerService->requestAccessToken( $code );
				
			  break;					
			}		
		
		}
	
		return $token;
	
	}

	public static function user_data_from_provider($result, $provider) {

		$user_data = array();
		$user_data['oauth_provider'] = $provider;	
		switch ($provider) {
		  case "twitter":
			$user_data['email'] = $result['id_str'];
		  break;
		  case "facebook":
			$user_data['email'] = $result['email'];
		  break;
		  case "freshbooks":
			$user_data['email'] = $result->staff->email;
		  break;
		  case "linkedin":
			$user_data['email'] = $result['email'];
		  break;
		  case "google":
			$user_data['email'] = $result['email'];
		  break;			  		  		  		  					
		}		
		$user_data['password'] = ' ';	
		$user_data['activated'] = true;	
		return $user_data;
	}

	public static function create_social_user($user_data) {
		if(!empty($user_data['email'])) {
			// Create the user
			$user = \Sentry::createUser($user_data);			
			return $user;
		}	
	}

	public static function get_user($user_email) {
		try
		{
			// get user by screen_name
			$user = \Sentry::findUserByLogin($user_email);
			return $user;
		}
		catch (\Cartalyst\Sentry\Users\UserNotFoundException $e)
		{
			return false;
		}		
	}
	

	public static function getCurrentUser() {
		try
		{
			// Get the current active/logged in user
			$user = \Sentry::getUser();
		}
		catch (\Cartalyst\Sentry\Users\UserNotFoundException $e)
		{
			$user = 'User not Found';
		}	
		return $user;
	}
	
	public static function getfreshbooks($request, $params = NULL) {
		
			// get freshbooks service
			$freshbooks = \OAuth::consumer( 'Freshbooks' );
	
			$result = $freshbooks->request( null, 'POST', '<?xml version="1.0" encoding="utf-8" ?><request method="'.$request.'">'.$params.'</request>' );
				
			$result = new \SimpleXmlElement($result);
				
			return $result;	
		
	}
	
}