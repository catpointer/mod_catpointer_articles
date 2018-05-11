<?php
/**
 * Joomla! .
 *
 * @copyright  Copyright (C) 2017 Roberto Segura López, Inc. All rights reserved.
 * @license    See COPYING.txt
 */

require_once JPATH_BASE . '/tests/unit/bootstrap.php';

if (!defined('JPATH_TESTS_CATPOINTER'))
{
	define('JPATH_TESTS_CATPOINTER', realpath(__DIR__));
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../extensions/modules/site/mod_catpointer_articles/bootstrap.php';

