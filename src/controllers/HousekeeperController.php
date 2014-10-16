<?php namespace OllieFordandCo\Housekeeper;

use \Artdarek\OAuth\Facade\OAuth;
use \Illuminate\Routing\Controller;
use \Illuminate\Support\Facades\Input;

class HousekeeperController extends Controller {

	/**
	 * Twitter Gateway
	 *
	 * @return Response
	 */
	public function loginWithTwitter()
	{

	// get data from input
	$code = Input::get( 'oauth_token' );
	$oauth_verifier = Input::get( 'oauth_verifier' );
	\Session::push('ollieford.user', 'provider');	
	// Instantiate the twitter service using the credentials, http client and storage mechanism for the token
	$twitterService = \OAuth::consumer( 'Twitter',  'http://beta.ollieford.com/login/twitter');
	
		if (!empty($code)) {
			$token = $twitterService->getStorage()->retrieveAccessToken('Twitter');

            // This was a callback request from google, get the token
			$twitterService->requestAccessToken( $code, $oauth_verifier, $token->getRequestTokenSecret() );

            // Send a request with it
			$result = json_decode( $twitterService->request( 'account/verify_credentials.json') );
			
			$user_exists = true;
			
			try
			{
				// get user by screen_name
				$user = \Sentry::findUserByLogin($result->screen_name);
			}
			catch (\Cartalyst\Sentry\Users\UserNotFoundException $e)
			{
				$user_exists = false;
			}
		
			if ($user_exists) {
				
				\Sentry::login($user, false);
				\Session::put('ollieford.user.provider', 'twitter');
				return \Redirect::route('welcome');
			
			} else {

				$banner = (isset($result->profile_banner_url)) ? $result->profile_banner_url : '';

				// Create the user
				$user = \Sentry::createUser(array(
					'user_id'	=> $result->id_str,
					'avatar'	=> $result->profile_image_url,
					'banner'	=> $banner,
					'email'		=> strtolower($result->screen_name).'@esvibe.com',
					'username'	=> $result->screen_name,
					'provider'	=> 'Twitter',
					'password'  => md5('TemporalPassword'),
					'activated' => true,
				));
				
				// Find the group using the group id
				$guestGroup = \Sentry::findGroupById(2);
				
				// Assign the group to the user
				$user->addGroup($guestGroup);
				
				\Sentry::login($user, false);					
				\Session::put('ollieford.user.provider', 'twitter');
				return \Redirect::route('welcome');
				
			}
		
		} else {
			// extra request needed for oauth1 to request a request token :-)
			$token = $twitterService->requestRequestToken();
			
			$url = $twitterService->getAuthorizationUri(array('oauth_token' => $token->getRequestToken()));
			return \Redirect::to(htmlspecialchars_decode($url));
			
		}

	} 

	/**
	 * Login user with facebook
	 *
	 * @return void
	 */
	
	public function loginWithFacebook() {
	
		// get data from input
		$code = Input::get( 'code' );
	
		// get fb service
		$fb = OAuth::consumer( 'Facebook' );
		\Session::push('ollieford.user', 'provider');	
		
		// check if code is valid
	
		// if code is provided get user data and sign in
		if ( !empty( $code ) ) {
	
			// This was a callback request from facebook, get the token
			$token = $fb->requestAccessToken( $code );
	
			// Send a request with it
			$result = json_decode( $fb->request( '/me' ), true );
	
			$user_exists = true;
			
			try
			{
				// get user by screen_name
				$user = \Sentry::findUserByLogin($result['name']);
			}
			catch (\Cartalyst\Sentry\Users\UserNotFoundException $e)
			{
				$user_exists = false;
			}
		
			if ($user_exists) {
				
				\Sentry::login($user, false);;			
				\Session::put('ollieford.user.provider', 'facebook');
				return \Redirect::route('welcome');
			
			} else {

				// Create the user
				$user = \Sentry::createUser(array(
					'user_id'	=> $result['id'],
					'avatar'	=> $result['picture']['data']['url'],
					'banner'	=> $result['cover']['source'],
					'email'		=> $result['email'],
					'username'	=> $result['username'],
					'provider'	=> 'Facebook',
					'password'  => md5('TemporalPassword'),
					'activated' => true,
				));

				// Find the group using the group id
				$guestGroup = \Sentry::findGroupById(2);
				
				// Assign the group to the user
				$user->addGroup($guestGroup);
				
				\Sentry::login($user, false);
				\Session::put('ollieford.user.provider', 'facebook');
				return \Redirect::route('welcome');
				
			}
	
		}
		// if not ask for permission first
		else {
			// get fb authorization
			$url = $fb->getAuthorizationUri();
	
			// return to facebook login url
			return \Response::make()->header( 'Location', (string)$url );
		}
	
	}

	/**
	 * Login user with freshbooks
	 *
	 * @return void
	 */
	
	public function loginWithFreshbooks() {
	
		// get data from input
		$code = Input::get( 'oauth_token' );
		$oauth_verifier = Input::get( 'oauth_verifier' );
		\Session::push('ollieford.user', 'provider');	
		
		// get fb service
		$freshbooks = OAuth::consumer( 'Freshbooks' );
	
		// check if code is valid
	
		// if code is provided get user data and sign in
		if ( !empty( $code ) ) {
	
			$token = $freshbooks->getStorage()->retrieveAccessToken('Freshbooks', 'http://intranet.ollieford.com/gatekeeper/freshbooks/');

            // This was a callback request from google, get the token
			$freshbooks->requestAccessToken( $code, $oauth_verifier, $token->getRequestTokenSecret() );

			$result = $freshbooks->request( null, 'POST', '<?xml version="1.0" encoding="utf-8" ?><request method="staff.current"></request>' );
			
			$result= new \SimpleXmlElement($result);
			
			$user_exists = true;
			
			try
			{
				// get user by screen_name
				$user = \Sentry::findUserByLogin($result->staff->username);
			}
			catch (\Cartalyst\Sentry\Users\UserNotFoundException $e)
			{
				$user_exists = false;
			}
		
			if ($user_exists) {
				
				\Sentry::login($user, false);
				\Session::put('ollieford.user.provider', 'freshbooks');
				return \Redirect::route('welcome');
			
			} else {

				// Create the user
				$user = \Sentry::createUser(array(
					'username'  => $result->staff->username,
					'email'		=> $result->staff->email,
					'password'  => md5('TemporalPassword'),
					'activated' => true,
				));
				
				// Find the group using the group id
				$guestGroup = \Sentry::findGroupById(2);
				
				// Assign the group to the user
				$user->addGroup($guestGroup);
				
				\Sentry::login($user, false);
				\Session::put('ollieford.user.provider', 'freshbooks');
				return \Redirect::route('welcome');
				
			}

	
		}
		// if not ask for permission first
		else {
			
			$token = $freshbooks->requestRequestToken();

			$url = $freshbooks->getAuthorizationUri(array('oauth_token' => $token->getRequestToken()));
			return \Redirect::to(htmlspecialchars_decode($url));
	
		}
	
	}
	
}