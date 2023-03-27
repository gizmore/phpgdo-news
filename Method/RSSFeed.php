<?php
namespace GDO\News\Method;

use GDO\Core\Application;
use GDO\Core\GDT;
use GDO\Core\Method;
use GDO\News\GDO_News;
use GDO\News\RSS;
use GDO\UI\GDT_HTML;

/**
 * Render news RSS feed.
 *
 * @author gizmore
 */
final class RSSFeed extends Method
{

	public function execute(): GDT
	{
		$query = GDO_News::table()->select()->limit(10);
		$query->where('news_visible')->order('news_created DESC');
		$items = $query->exec()->fetchAllObjects();

		$sitename = sitename();
		$feed = new RSS(
			t('newsfeed_title', [$sitename]),
			t('newsfeed_descr', [$sitename]),
			$items,
			url('News', 'List'),
			url('News', 'RSSFeed'));

		$rss = $feed->render();
		return GDT_HTML::make($rss);
	}

}
