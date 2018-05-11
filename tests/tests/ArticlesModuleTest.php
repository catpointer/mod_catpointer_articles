<?php
/**
 * Articles Module Tests.
 *
 * @copyright  Copyright (C) 2018 catpointersolutions.com, Inc. All rights reserved.
 * @license    See COPYING.txt
 */

namespace Catpointer\Joomla\Module\Site\Articles\Tests;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use CatPointer\Joomla\Module\Site\Articles\ArticlesModule;

/**
 * Base entity tests.
 *
 * @since   __DEPLOY_VERSION__
 */
class ArticlesModuleTest extends \TestCaseDatabase
{
	/**
	 * @test
	 *
	 * @return void
	 */
	public function constructorSetsParams()
	{
		$params = new Registry(['my-param' => 'my-value']);
		$module = new ArticlesModule($params);

		$this->assertSame($params, $module->params());
	}

	/**
	 * @test
	 *
	 * @return void
	 */
	public function articlesReturnsAnArrayOfArticles()
	{
		$module = new ArticlesModule;

		$articles = $module->articles();

		$this->assertTrue(is_array($articles));
		$this->assertNotSame(0, count($articles));
	}

	/**
	 * @test
	 *
	 * @return void
	 */
	public function articlesReturnsArticlesFromSpecifiedCategory()
	{
		$params = new Registry(['catid' => 26]);

		$module = new ArticlesModule($params);

		$articles = $module->articles();

		$this->assertNotEmpty($articles);

		foreach ($articles as $article)
		{
			$this->assertSame(26, (int) $article['catid']);
		}
	}

	/**
	 * @test
	 *
	 * @return void
	 */
	public function articlesOnlyReturnsArticlesWithTags()
	{
		$params = new Registry(['tag' => 2]);

		$module = new ArticlesModule($params);

		$articles = $module->articles();

		$this->assertSame(2, count($module->articles()));
	}

	/**
	 * @test
	 *
	 * @return void
	 */
	public function articlesOnlyReturnsFeaturedArticles()
	{
		$module = new ArticlesModule(new Registry(['show_featured' => ArticlesModule::FEATURED_SHOW_ONLY]));

		$articles = $module->articles();

		$this->assertNotEmpty($articles);

		foreach ($articles as $article)
		{
			$this->assertSame('1', $article['featured']);
		}
	}

	/**
	 * @test
	 *
	 * @return void
	 */
	public function articlesOnlyReturnsNonFeaturedArticles()
	{
		$module = new ArticlesModule(new Registry(['show_featured' => ArticlesModule::FEATURED_HIDE]));

		$articles = $module->articles();

		$this->assertNotEmpty($articles);

		foreach ($articles as $article)
		{
			$this->assertSame('0', $article['featured']);
		}
	}

	/**
	 * @test
	 *
	 * @return void
	 */
	public function articlesReturnsFeaturedAndNonFeaturedArticles()
	{
		$params = new Registry(['show_featured' => ArticlesModule::FEATURED_SHOW]);
		$module = new ArticlesModule($params);

		$articles = $module->articles();

		$this->assertNotEmpty($articles);

		$featuredFound    = false;
		$nonFeaturedFound = false;

		foreach ($articles as $article)
		{
			if ('0' === $article['featured'])
			{
				$nonFeaturedFound = true;
				continue;
			}

			if ('1' === $article['featured'])
			{
				$featuredFound = true;
			}
		}

		$this->assertTrue($featuredFound);
		$this->assertTrue($nonFeaturedFound);
	}

	/**
	 * @test
	 *
	 * @return void
	 */
	public function articlesReturnsArticlesFromSpecifiedAuthor()
	{
		$module = new ArticlesModule(new Registry(['author' => 2]));

		$articles = $module->articles();

		foreach ($articles as $article)
		{
			$this->assertSame('2', $article['created_by']);
		}
	}

	/**
	 * @test
	 *
	 * @return void
	 */
	public function articlesOnlyReturnsSpecifiedLimit()
	{
		$module = new ArticlesModule;

		$this->assertSame(ArticlesModule::DEFAULT_LIMIT, count($module->articles()));
		$this->assertNotSame(ArticlesModule::DEFAULT_LIMIT, 1);

		$params = new Registry(['limit' => 1]);

		$module = new ArticlesModule($params);

		$this->assertSame(1, count($module->articles()));
	}

	/**
	 * @test
	 *
	 * @return void
	 */
	public function articlesOnlyShowsPublishedArticles()
	{
		$module = new ArticlesModule;

		$articles = $module->articles();

		$this->assertNotEmpty($articles);

		foreach ($articles as $article)
		{
			$this->assertSame(1, (int) $article['state']);
		}
	}

	/**
	 * Data provider for authorsIds test
	 *
	 * @return  array
	 */
	public function authorsIdsProvider()
	{
		return [
			[['author' => '2'], [2]],
			[['author' => ''], []],
			[['author' => '    34'], [34]],
			[['author' => null], []]
		];
	}

	/**
	 * @test
	 *
	 * @dataProvider  authorsIdsProvider
	 *
	 * @return void
	 */
	public function authorsIdsReturnsExpectedValues($params, $expected)
	{
		$module = new ArticlesModule(new Registry($params));

		$reflection = new \ReflectionClass($module);
		$method = $reflection->getMethod('authorsIds');
		$method->setAccessible(true);

		$this->assertSame($expected, $method->invoke($module));
	}

	/**
	 * @test
	 *
	 * @return void
	 */
	public function categoriesIdsProvider()
	{
		return [
			[['catid' => ''], []],
			[['catid' => ['23', '', 23, null, '  35']], [23, 35]]
		];
	}

	/**
	 * @test
	 *
	 * @dataProvider  categoriesIdsProvider
	 *
	 * @param   array  $params    Array with params to initialise the module
	 * @param   mixed  $expected  Expected response from categoriesIds method
	 *
	 * @return void
	 */
	public function categoriesIdsReturnsCorrectValues($params, $expected)
	{
		$module = new ArticlesModule(new Registry($params));

		$reflection = new \ReflectionClass($module);
		$method = $reflection->getMethod('categoriesIds');
		$method->setAccessible(true);

		$this->assertSame($expected, $method->invoke($module));
	}

	/**
	 * @test
	 *
	 * @return void
	 */
	public function featuredIdsReturnsCorrectValue()
	{
		$module = new ArticlesModule;

		$reflection = new \ReflectionClass($module);
		$method = $reflection->getMethod('featuredIds');
		$method->setAccessible(true);

		$this->assertSame([], $method->invoke($module));

		$module = new ArticlesModule(new Registry(['show_featured' => '2']));

		$this->assertSame([0], $method->invoke($module));

		$module = new ArticlesModule(new Registry(['show_featured' => '3']));

		$this->assertSame([1], $method->invoke($module));
	}

	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  \PHPUnit_Extensions_Database_DataSet_CsvDataSet
	 */
	protected function getDataSet()
	{
		$dataSet = new \PHPUnit_Extensions_Database_DataSet_CsvDataSet(',', "'", '\\');
		$dataSet->addTable('jos_content', JPATH_TEST_DATABASE . '/jos_content.csv');
		$dataSet->addTable('jos_categories', JPATH_TEST_DATABASE . '/jos_categories.csv');
		$dataSet->addTable('jos_contentitem_tag_map', JPATH_TESTS_CATPOINTER . '/db/data/contentitem_tag_map.csv');

		return $dataSet;
	}

	/**
	 * Data provider for limit().
	 *
	 * @return  array
	 */
	public function limitProvider()
	{
		return [
			[[], ArticlesModule::DEFAULT_LIMIT],
			[['limit' => '24'], 24],
			[['limit' => ''], ArticlesModule::DEFAULT_LIMIT],
			[['limit' => ' '], ArticlesModule::DEFAULT_LIMIT],
			[['limit' => null], ArticlesModule::DEFAULT_LIMIT],
			[['limit' => 'test'], ArticlesModule::DEFAULT_LIMIT],
			[['limit' => ' 3test'], 3]
		];
	}

	/**
	 * @test
	 *
	 * @dataProvider  limitProvider
	 *
	 * @param   array  $params         Module settings
	 * @param   mixed  $expectedLimit  Expected limit returned by limit()
	 *
	 * @return void
	 */
	public function limitReturnsCorrectValue($params, $expectedLimit)
	{
		$module = new ArticlesModule(new Registry($params));

		$reflection = new \ReflectionClass($module);
		$method = $reflection->getMethod('limit');
		$method->setAccessible(true);

		$this->assertSame($expectedLimit, $method->invoke($module));
	}

	/**
	 * @test
	 *
	 * @return void
	 */
	public function layoutDataReturnsExpectedData()
	{
		$module = new ArticlesModule;

		$reflection = new \ReflectionClass($module);
		$method = $reflection->getMethod('layoutData');
		$method->setAccessible(true);

		$this->assertSame(['moduleInstance' => $module], $method->invoke($module));
	}

	/**
	 * @test
	 *
	 * @return void
	 */
	public function orderReturnsCategoryIfPreorderCategoryIsEnabled()
	{
		$module = new ArticlesModule(new Registry(['preorder_category' => '1']));

		$reflection = new \ReflectionClass($module);
		$method = $reflection->getMethod('order');
		$method->setAccessible(true);

		$this->assertSame('c.title ASC, a.publish_up ASC', $method->invoke($module));
	}

	/**
	 * @test
	 *
	 * @return void
	 */
	public function renderReturnsExpectedData()
	{
		$module = new ArticlesModule(new Registry(['limit' => 1]));

		$this->assertSame(1, substr_count($module->render(), '<ul class="articles">'));
	}

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		parent::setUp();

		// Get the mocks
		$this->saveFactoryState();

		\JFactory::$session     = $this->getMockSession();
		\JFactory::$config      = $this->getMockConfig();
		\JFactory::$application = $this->getMockCmsApp();
	}

	/**
	 * This method is called before the first test of this test class is run.
	 *
	 * @return  void
	 */
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();

		static::$driver->getConnection()->exec(file_get_contents(JPATH_TESTS_CATPOINTER . '/db/schema/contentitem_tag_map.sql'));

		\JFactory::$database = static::$driver;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * @test
	 *
	 * @return void
	 */
	public function stringsAreTranslated()
	{
		$this->assertSame(1, substr_count(Text::_('MOD_CATPOINTER_ARTICLES'), 'CatPointer'));
	}
}
