<?php
namespace Tqdev\AhmadsRestApi;

use Tqdev\PhpCrudApi\Request;

class Projects
{
	public function __construct(Request &$request, $user)
	{
		switch(strtolower($request->getPathSegment(3))){
			case "":
				switch(strtolower($request->getMethod())){
					case "get":
						$this->get($request, $user);
						break;
					default:
						_http(404);
				}
				break;
			default:
				_http(404);
		}
	}

	private function get(Request &$request, $user){
		$request=new Request(
			'GET',
			'/records/projects_has_users/',
			'join=projects&'.
			'filter=user_id,eq,'.$user['id'].'&'.
			'include=projects.id,projects.name,projects.avatar',[],''
		);
	}
}
