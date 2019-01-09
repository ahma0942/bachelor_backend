<?php
namespace Tqdev\AhmadsRestApi;

use Tqdev\PhpCrudApi\Request;

class Admin
{
	public function __construct(Request &$request)
	{
		switch(strtolower($request->getPathSegment(3))){
			case "projects":
				if($request->getPathSegment(4)!="")
				{
					switch(strtolower($request->getMethod())){
						case "put":
							$this->putProject($request);
							break;
						case "delete":
							$this->deleteProject($request);
							break;
						default:
							_http(404);
					}
				}
				else {
					switch(strtolower($request->getMethod())){
						case "get":
							$this->getProjects();
							break;
						case "post":
							$this->postProject($request);
							break;
						default:
							_http(404);
					}
					break;
				}
				break;
			case "users":
				if($request->getPathSegment(4)!="")
				{
					if($request->getPathSegment(5)!=""){
						switch(strtolower($request->getPathSegment(5))){
							case "projects":
								if($request->getPathSegment(6)!=""){
									switch(strtolower($request->getMethod())) {
										case "post":
											$this->addProjectFromUser($request);
											break;
										case "delete":
											$this->removeProjectFromUser($request);
											break;
										default:
											_http(404);
									}
								}
								else {
									switch(strtolower($request->getMethod())){
										case "get":
											$this->getUserProjects($request);
											break;
										default:
											_http(404);
									}
								}
								break;
							default:
								_http(404);
						}
					}
					else {
						switch(strtolower($request->getMethod())){
							case "put":
								$this->putUsers($request);
								break;
							case "delete":
								$this->deleteUsers($request);
								break;
							default:
								_http(404);
						}
					}
				}
				else {
					switch(strtolower($request->getMethod())){
						case "get":
							$this->getUsers($request);
							break;
						case "post":
							$this->postUsers($request);
							break;
						default:
							_http(404);
					}
					break;
				}
				break;
			case "usersnames":
				switch(strtolower($request->getMethod())){
					case "get":
						$this->getAllUsersNamesAndIds($request);
						break;
					default:
						_http(404);
				}
				break;
			default:
				_http(404);
		}
	}

	private function getUsers(Request &$request){
		$request=new Request(
			'GET',
			'/records/users/',
			'include=id,name,email,role_id&'.
			'filter=deleted,eq,0',
			[],
			''
		);
	}

	private function removeProjectFromUser(Request &$request){
		if(RemoveProjectFromUser($request->getPathSegment(4),$request->getPathSegment(6))) _http(204);
		_http(400);
	}

	private function addProjectFromUser(Request &$request){
		if(AddProjectFromUser($request->getPathSegment(4),$request->getPathSegment(6))) _http(204);
		_http(400);
	}

	private function getUserProjects(Request &$request){
		$request=new Request(
			'GET',
			'/records/projects_has_users/',
			'join=projects&'.
			'filter=user_id,eq,'.$request->getPathSegment(4).'&'.
			'include=projects.id,projects.name,projects.deleted,timestamp',
			[],
			''
		);
	}

	private function putUsers(Request &$request){
		$request=new Request(
			'PUT',
			'/records/users/'.$request->getPathSegment(4),
			'',
			[],
			json_encode($request->getBody())
		);
	}

	private function deleteUsers(Request &$request){
		$body['deleted']=1;
		$request=new Request(
			'PUT',
			'/records/users/'.$request->getPathSegment(4),
			'',
			[],
			json_encode($body)
		);
	}

	private function postUsers(Request &$request){
		global $INFO;
		$body=$request->getBody();
		$password=rand_str(8);
		$token=rand_str(64);
		$confirm_email=rand_str(20);
		$confirm_phone=rand_str(6,'1234567890');

		$msg="<big><big>Hello {$body->name}!<br/><br/>
		
		Thank you for your interest in $INFO[name].<br/>
		You have been invited to join $INFO[name].<br/>
		To activate your account, click the link below and follow the instructions.<br/>
		<a href='$INFO[url]/?p=confirm&token=$confirm_email&email={$body->email}'>$INFO[url]/?p=confirm&token=$confirm_email&email={$body->email}</a><br/>
		We have assigned you a temporary password which you can change when you login. <b>Your temporary password is:</b><br/>
		<h2>$password</h2>
		If you didn't sign up at $INFO[name], simply ignore this mail, and we apologize for any trouble it may have caused you.<br/><br/>
		
		Regards,<br/>
		$INFO[name]</big></big>";
		sendmail("$INFO[name] Confirm Email",$msg,$body->email);

		$body->password=hasher($password);
		$body->token=$token;
		$body->confirmemail=$confirm_email;
		$body->confirmphone=$confirm_phone;
		$body->created=time();

		$request=new Request(
			'POST',
			'/records/users/',
			'',
			[],
			json_encode($body)
		);
	}

	private function getProjects(){
		_http(200, json_encode(GetProjects()));
	}

	private function putProject(Request &$request){
		$request=new Request(
			'PUT',
			'/records/projects/'.$request->getPathSegment(4),
			'',
			[],
			json_encode($request->getBody())
		);
	}

	private function deleteProject(Request &$request){
		$body['deleted']=1;
		$request=new Request(
			'PUT',
			'/records/projects/'.$request->getPathSegment(4),
			'',
			[],
			json_encode($body)
		);
	}

	private function postProject(Request &$request){
		_http(200, json_encode(AddProject($request->getBody()->name, $request->getBody()->admin)));
	}

	private function getAllUsersNamesAndIds(Request &$request){
		$request=new Request(
			'GET',
			'/records/users',
			'include=id,name',
			[],
			''
		);
	}
}
