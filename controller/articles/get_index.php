<?php
namespace Core;
use Exception;

if (!defined('__GOOSE__')) exit();

/**
 * get articles
 *
 * url params
 * - @param int app
 * - @param int nest
 * - @param int category
 * - @param int user
 * - @param string q
 *
 * @var Goose $this
 */

try
{
	// set where
	$where = '';
	if ($app = $_GET['app'])
	{
		$where .= ' and app_srl='.$app;
	}
	if ($nest = $_GET['nest'])
	{
		$where .= ' and nest_srl='.$nest;
	}
	if ($category = $_GET['category'])
	{
		$where .= ($category === 'null') ? ' and category_srl IS NULL' : ' and category_srl='.$category;
	}
	if ($q = $_GET['q'])
	{
		$where .= ' and (title LIKE \'%'.$q.'%\' or content LIKE \'%'.$q.'%\')';
	}

	// set model
	$model = new Model();
	$model->connect();

	// check access
	$token = Controller::checkAccessIndex($model, true);
	$where .= (!$token->data->admin && $token->data->user_srl) ? ' and user_srl='.(int)$token->data->user_srl : '';

	// set output
	$output = Controller::index((object)[
		'goose' => $this,
		'model' => $model,
		'table' => 'articles',
		'where' => $where,
		'json_field' => ['json']
	]);

	// get category name
	if ($output->data && Util::checkKeyInExtField('category_name'))
	{
		$output->data->index = \Controller\articles\Util::extendCategoryNameInItems($model, $output->data->index);
	}

	// get nest name
	if ($output->data && Util::checkKeyInExtField('nest_name'))
	{
		$output->data->index = \Controller\articles\Util::extendNestNameInItems($model, $output->data->index);
	}

	// get next page
	if ($output->data && Util::checkKeyInExtField('next_page'))
	{
		$nextPage = \Controller\articles\Util::getNextPage($this, $model, $where);
		if ($nextPage) $output->data->nextPage = $nextPage;
	}

	// set token
	if ($token) $output->_token = $token->jwt;

	// output
	Output::data($output);
}
catch (Exception $e)
{
	if (isset($model)) $model->disconnect();
	Error::data($e->getMessage(), $e->getCode());
}
