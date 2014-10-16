<?php namespace OllieFordandCo\Housekeeper;


class Housekeeper 
{

	public function getCurrentUser() {
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
	
	public function getfreshbooks($request, $params = NULL) {
		
			// get freshbooks service
			$freshbooks = \OAuth::consumer( 'Freshbooks' );
	
			$result = $freshbooks->request( null, 'POST', '<?xml version="1.0" encoding="utf-8" ?><request method="'.$request.'">'.$params.'</request>' );
				
			$result = new \SimpleXmlElement($result);
				
			return $result;	
		
	}
	
}