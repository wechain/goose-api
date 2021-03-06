<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get users
 *
 * url params
 * - @param string email
 * - @param string name
 *
 * @var Goose $this
 */

try
{
	// set model
	$model = new Model();
	$model->connect();

	// set where
	$where = '';
	if ($email = Util::getParameter('email'))
	{
		$where .= ' and email LIKE \''.$email.'\'';
	}
	if ($name = Util::getParameter('name'))
	{
		$where .= ' and name LIKE \'%'.$name.'%\'';
	}
	if ($admin = Util::getParameter('admin'))
	{
		$where .= ' and admin='.(int)$admin;
	}

	// check authorization
	$token = Auth::checkAuthorization($model, 'user');
	if (!$token->data->admin)
	{
		$where .= ' and srl='.$token->data->user_srl;
	}

	// output
	$output = Controller::index((object)[
		'goose' => $this,
		'model' => $model,
		'auth' => true,
		'table' => 'users',
		'where' => $where,
	], function($result=null) {
		if (!isset($result->data)) return $result;
		foreach ($result->data as $k=>$o)
		{
			// remove pw field
			if (isset($result->data[$k]->pw))
			{
				unset($result->data[$k]->pw);
			}
		}
		return $result;
	});

	// set token
	if ($token->jwt) $output->_token = $token->jwt;

	// output
	Output::data($output);
}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
