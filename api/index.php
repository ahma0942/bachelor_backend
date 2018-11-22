<?php
error_reporting(E_ERROR | E_PARSE);
include "db.php";
include "PHPMailer/PHPMailerAutoload.php";
include "funcs.php";
include "dal.php";

$INFO=array();
$INFO['name']="G2W Chat";
$INFO['email']['reply']="aal@it-minds.dk";
$param_slice=4;

//DEV
if($_SERVER['HTTP_HOST']=="localhost") $INFO['url']='http://'.$_SERVER['HTTP_HOST']."/".explode("/",$_SERVER['REQUEST_URI'])[1];
//PROD
else $INFO['url']='http://'.$_SERVER['HTTP_HOST'];


use Tqdev\PhpCrudApi\Api;
use Tqdev\PhpCrudApi\Config;
use Tqdev\PhpCrudApi\Request;

cors();

// do not reformat the following line
spl_autoload_register(function ($class) {include str_replace('\\', '/', __DIR__ . "/$class.php");});
// as it is excluded in the build

$config = new Config([
	'address' => '',
	'username' => '',
	'password' => '',
	'database' => '',
	'middlewares' => 'cors',
]);

$path=implode('/',array_slice(explode('/',$_SERVER['REQUEST_URI']),$param_slice));
$query="";
if(strpos($path,"?")!==false) list($path,$query)=explode('?',$path);
$_SERVER['PATH_INFO']="/records/$path";
$_SERVER['QUERY_STRING']=$query;

$request = new Request();
new ValidateRequest($request);
$api = new Api($config);
$response = $api->handle($request);
$response->output();
