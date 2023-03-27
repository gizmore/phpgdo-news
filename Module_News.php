<?php
namespace GDO\News;

use GDO\Core\GDO_Module;
use GDO\Core\GDT_Checkbox;
use GDO\Core\GDT_Template;
use GDO\Core\Website;
use GDO\Date\GDT_Timestamp;
use GDO\UI\GDT_Link;
use GDO\UI\GDT_Page;
use GDO\User\GDT_ACLRelation;

/**
 * News module.
 *
 * @version 7.0.1
 * @since 6.3.0
 * @author gizmore
 */
final class Module_News extends GDO_Module
{

	public int $priority = 30;

	##############
	### Module ###
	##############
	public function href_administrate_module(): ?string { return href('News', 'Admin'); }

	public function onLoadLanguage(): void { $this->loadLanguage('lang/news'); }

	public function getDependencies(): array
	{
		return [
			'Comments', 'Category', 'Mail',
		];
	}

	public function getFriendencies(): array
	{
		return [
			'GTranslate',
		];
	}

	public function getClasses(): array
	{
		return [
			GDO_News::class,
			GDO_NewsText::class,
			GDO_NewsComments::class,
			GDO_Newsletter::class,
		];
	}

	##############
	### Config ###
	##############
	public function getConfig(): array
	{
		return [
			GDT_Checkbox::make('news_blogbar')->initial('0'),
			GDT_Checkbox::make('news_comments')->initial('1'),
			GDT_Checkbox::make('news_guests')->initial('1'),
			GDT_Checkbox::make('newsletter_guests')->initial('1'),
			GDT_Checkbox::make('news_guest_comments')->initial('1'),
			GDT_Checkbox::make('hook_sidebar')->initial('1'),
		];
	}

	public function getUserConfig(): array
	{
		return [
			GDT_Timestamp::make('news_read_mark')->noacl()->hidden(),
		];
	}

	public function getACLDefaults(): array
	{
		return [
			'news_read_mark' => [GDT_ACLRelation::HIDDEN, '0', null],
		];
	}

	public function onModuleInit(): void {}

	public function onInitSidebar(): void
	{
		Website::addLink([
			'href' => $this->href('RSSFeed'),
			'type' => 'application/rss+xml',
			'rel' => 'alternate',
			'title' => t('rss_newsfeed', [sitename()]),
		]);
		if ($this->cfgLeftBar())
		{
			GDT_Page::instance()->leftBar()->addField(
				GDT_Link::make()->href(href('News', 'NewsList'))->icon('alert')->text('link_news', [GDO_News::numNews(), GDO_News::newNews()]));
			if ($this->cfgBlogbar())
			{
				GDT_Page::instance()->leftBar()->addField(
					GDT_Template::make()->template('News', 'blogbar.php', ['bar' => GDT_Page::instance()->leftBar()]));
			}
		}
	}

	public function cfgLeftBar() { return $this->getConfigValue('hook_sidebar'); }

	public function cfgBlogbar() { return $this->getConfigValue('news_blogbar'); }

	################
	### Settings ###
	################

	public function cfgComments() { return $this->getConfigValue('news_comments'); }

	public function cfgGuestNews() { return $this->getConfigValue('news_guests'); }

	############
	### Init ###
	############

	public function cfgGuestNewsletter() { return $this->getConfigValue('newsletter_guests'); }

	############
	### Navs ###
	############

	public function cfgGuestComments() { return $this->getConfigValue('news_guest_comments'); }

	public function renderTabs()
	{
		GDT_Page::instance()->topResponse()->addField(
			$this->templatePHP('tabs.php'));
	}

	public function renderAdminTabs()
	{
		GDT_Page::instance()->topResponse()->addField(
			GDT_Template::make()->template('News', 'admin_tabs.php'));
	}

}
