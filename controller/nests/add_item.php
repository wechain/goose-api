<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * add nest
 *
 * @var Goose $this
 */

try
{
	// check post values
	Util::checkExistValue($_POST, [ 'app_srl', 'id', 'name' ]);

	// check `id`
	if (!Text::allowString($_POST['id']))
	{
		throw new Exception('`id` can be used only in numbers and English.');
	}

	// check,set json
	$json = null;
	if (isset($_POST['json']))
	{
		$json = json_decode(urldecode($_POST['json']), false);
		if (!$json)
		{
			throw new Exception('The json syntax is incorrect.', 204);
		}
		$json = urlencode(json_encode($json, false));
	}

	// set model
	$model = new Model();
	$model->connect();

	// check authorization
	$token = Auth::checkAuthorization($model, 'user');

	// check app
	$cnt = $model->getCount((object)[
		'table' => 'apps',
		'where' => 'srl='.(int)$_POST['app_srl']
	]);
	if (!$cnt->data)
	{
		throw new Exception('There is no `apps` data.', 204);
	}

	// check duplicate nest id
	$cnt = $model->getCount((object)[
		'table' => 'nests',
		'where' => 'id="'.trim($_POST['id']).'"'
	]);
	if ($cnt->data)
	{
		throw new Exception('There is a duplicate `id`.', 204);
	}

	// set output
	$output = Controller::add((object)[
		'goose' => $this,
		'model' => $model,
		'table' => 'nests',
		'data' => (object)[
			'srl' => null,
			'app_srl' => $_POST['app_srl'],
			'user_srl' => (int)$token->data->user_srl,
			'id' => trim($_POST['id']),
			'name' => trim($_POST['name']),
			'description' => trim($_POST['description']),
			'json' => $json,
			'regdate' => date('YmdHis'),
		]
	]);

	// set token
	if ($token) $output->_token = $token->jwt;

	// disconnect db
	$model->disconnect();

	// output data
	Output::data($output);
}
catch (Exception $e)
{
	if (isset($model)) $model->disconnect();
	Error::data($e->getMessage(), $e->getCode());
}
