<?php
/**
 * @package     Catpointer.Module
 * @subpackage  Frontend.mod_catpointer_articles
 *
 * @copyright   Copyright (C) 2018 webete.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

JLoader::import('twig.library');

$composerAutoload = __DIR__ . '/vendor/autoload.php';

if (!file_exists($composerAutoload))
{
	throw new \RuntimeException('Error loading module dependencies');
}

require_once $composerAutoload;

$lang = Factory::getLanguage();
$lang->load('mod_catpointer_articles', __DIR__);
