<?php
use Tqdev\PhpCrudApi\Request;
use Tqdev\AhmadsRestApi\User;
use Tqdev\AhmadsRestApi\Projects;
use Tqdev\AhmadsRestApi\Messages;
use Tqdev\AhmadsRestApi\Admin;

class ValidateRequest
{
	public function __construct(Request &$request)
	{
		if($request->getMethod()=="OPTIONS") _http(200);
		if($request->getMethod()=="POST" && strtolower($request->getPathSegment(2))=="login") $this->login($request->getBody());
		$user=_auth();

		switch(strtolower($request->getPathSegment(2))){
			case "user":
				new User($request, $user);
				break;
			case "projects":
				new Projects($request, $user);
				break;
			case "messages":
				new Messages($request, $user);
				break;
			case "admin":
				if($user['role_id']!=2) _http(401);
				new Admin($request);
				break;
			default:
				_http(404);
				exit;
		}
	}

	private function login($body){
		$user=login($body->email,$body->password);
		if($user===false) {
			$output['err'] = 'Wrong Credentials';
			_http(401, json_encode($output));
		}
		else {
			_http(200,json_encode($user));
		}
	}
}
