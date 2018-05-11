<?php
/**
 * @package     Catpointer.Module
 * @subpackage  Frontend.mod_catpointer_articles
 *
 * @copyright   Copyright (C) 2018 catpointersolutions.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/bootstrap.php';

use CatPointer\Joomla\Module\Site\Articles\ArticlesModule;

$modInstance = new ArticlesModule($params);

$domId = 'mod-catpointer-articles-' . (!empty($module->id) ? $module->id : uniqid());

echo $modInstance->render($params->get('layout', 'default'), compact('domId', 'module'));
