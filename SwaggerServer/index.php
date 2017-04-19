<?php

error_reporting(E_ALL);

$path = __DIR__;
$parts = explode('/', $path);
array_pop($parts);
$newpath = implode('/', $parts);

require_once $newpath . '/vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\Application;

require_once $path . '/Goals.php';

$app = new Silex\Application();


$app->POST('/Sir_Gamealot/GoalsMaster/1.0.0/goals', function(Application $app, Request $request) {
            
            
            return new Response('How about implementing addGoal as a POST method ?');
            });


$app->POST('/Sir_Gamealot/GoalsMaster/1.0.0/tasks', function(Application $app, Request $request) {
            
            
            return new Response('How about implementing addTask as a POST method ?');
            });


$app->GET('/Sir_Gamealot/GoalsMaster/1.0.0/goals', function(Application $app, Request $request) {
            $userid = $request->get('userid');    
            
			var Goals goals = new Goals();
			return goals.getGoals($userid);
            });


$app->GET('/Sir_Gamealot/GoalsMaster/1.0.0/tasks', function(Application $app, Request $request) {
            $userid = $request->get('userid');    
            
            return new Response('How about implementing getTasks as a GET method ?');
            });


$app->run();

?>
