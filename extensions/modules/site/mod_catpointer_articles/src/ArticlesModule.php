<?php
/**
 * @package     Catpointer.Module
 * @subpackage  Frontend.mod_catpointer_articles.
 *
 * @copyright  Copyright (C) 2017 catpointersolutions.com. All rights reserved.
 * @license    See COPYING.txt
 */

namespace CatPointer\Joomla\Module\Site\Articles;

require_once __DIR__ . '/../bootstrap.php';

defined('_JEXEC') || die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;
use Phproberto\Joomla\Twig\Twig;
use Joomla\Utilities\ArrayHelper;

/**
 * Articles module.
 *
 * @since   __DEPLOY_VERSION__
 */
class ArticlesModule
{
	/**
	 * Default limit if not specified in module settings.
	 *
	 * @const
	 */
	const DEFAULT_LIMIT = 10;

	/**
	 * Articles to show.
	 *
	 * @var  array
	 */
	protected $articles;

	/**
	 * Module parameters
	 *
	 * @var  Registry
	 */
	protected $params;

	/**
	 * Constructor.
	 *
	 * @param   Registry|null  $params  Module parameters
	 */
	public function __construct(Registry $params = null)
	{
		$this->params = $params ?: new Registry;
	}

	/**
	 * Retrieve module params.
	 *
	 * @return  Registry
	 */
	public function params()
	{
		return $this->params;
	}

	/**
	 * Retrieve articles to show.
	 *
	 * @return  array
	 */
	public function articles()
	{
		if (null === $this->articles)
		{
			$this->articles = $this->loadArticles();
		}

		return $this->articles;
	}

	/**
	 * Retrieve the data for the layout.
	 *
	 * @return  array
	 */
	protected function layoutData()
	{
		return [
			'moduleInstance' => $this
		];
	}

	/**
	 * Load articles from DB.
	 *
	 * @return  array
	 */
	protected function loadArticles()
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('a.*')
			->select($db->qn('c.title', 'category_title'))
			->from($db->qn('#__content', 'a'))
			->innerjoin(
				$db->qn('#__categories', 'c')
				. ' ON ' . $db->qn('a.catid') . ' = ' . $db->qn('c.id')
			)
			->where($db->qn('a.state') . ' = 1')
			->where($db->qn('c.published') . ' = 1');

		$catIds = $this->categoriesIds();

		if ($catIds)
		{
			$query->where($db->qn('a.catid') . ' IN(' . implode(',', $catIds) . ')');
		}

		$tagsIds = $this->tagsIds();

		if ($tagsIds)
		{
			$subQuery = $db->getQuery(true)
				->select('DISTINCT content_item_id')
				->from($db->quoteName('#__contentitem_tag_map'))
				->where('tag_id IN (' . implode(',', $tagsIds) . ')')
				->where('type_alias = ' . $db->quote('com_content.article'));

			$query->innerJoin('(' . (string) $subQuery . ') AS tagmap ON tagmap.content_item_id = a.id');
		}

		$query->order($db->escape($this->order()));

		$db->setQuery($query, 0, $this->limit());

		$articles = $db->loadAssocList('id') ?: [];

		foreach ($articles as &$article)
		{
			$this->prepareArticle($article);
		}

		return $articles;
	}

	/**
	 * Identifiers of categories to show.
	 *
	 * @return  array
	 */
	protected function categoriesIds()
	{
		return ArrayHelper::toInteger(
			array_values(
				array_unique(
					array_filter((array) $this->params()->get('catid'))
				)
			)
		);
	}

	/**
	 * Identifiers of tags to show.
	 *
	 * @return  array
	 */
	protected function tagsIds()
	{
		return ArrayHelper::toInteger(
			array_values(
				array_unique(
					array_filter((array) $this->params()->get('tag'))
				)
			)
		);
	}

	/**
	 * Number of articles to show.
	 *
	 * @return  integer
	 */
	protected function limit()
	{
		$limit = (int) $this->params()->get('limit', '10');

		return $limit ?: self::DEFAULT_LIMIT;
	}

	/**
	 * Get the active order.
	 *
	 * @return  string
	 */
	protected function order()
	{
		$order = $this->params()->get('ordering', 'a.publish_up');

		if ($order !== 'rand()')
		{
			$order .= ' ' . $this->params()->get('ordering_direction', 'ASC');
		}

		if ('1' !== $this->params->get('preorder_category', '0'))
		{
			return $order;
		}

		return 'c.title ASC, ' . $order;
	}

	/**
	 * Prepare an article to be sent to layout.
	 *
	 * @param   array  $article  Array containing article data
	 *
	 * @return  void
	 */
	public function prepareArticle(&$article)
	{
		\JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');

		$slug = $article['id'] . ':' . $article['alias'];

		$url = \ContentHelperRoute::getArticleRoute($slug, $article['catid'], $article['language']);

		$article['link'] = Route::_($url);
	}

	/**
	 * Render the module.
	 *
	 * @param   string  $layout  Layout to render
	 * @param   array   $data    Optional data for the layout
	 *
	 * @return  string
	 */
	public function render($layout = 'default', array $data = [])
	{
		$data = array_merge($this->layoutData(), $data);

		$layout = '@module/mod_catpointer_articles/' . trim($layout) . '.html.twig';

		return Twig::render($layout, $data);
	}
}
