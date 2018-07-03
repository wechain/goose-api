<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get app
 *
 * @var Goose $this
 */

try
{
	// check srl
	if (!((int)$this->params['srl'] && $this->params['srl'] > 0))
	{
		throw new Exception('Not found srl', 404);
	}

	// check authorization
	$token = Auth::checkAuthorization();

	// set output
	$output = Controller::item((object)[
		'goose' => $this,
		'table' => 'app',
		'srl' => (int)$this->params['srl'],
	]);

	// set token
	if ($token) $output->_token = $token;

	// output data
	Output::data($output);
}
catch (Exception $e)
{
	Error::data($e->getMessage(), $e->getCode());
}
