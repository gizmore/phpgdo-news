<?php
namespace GDO\News;

use GDO\Core\GDO_Module;
use GDO\Core\GDT_Checkbox;
use GDO\UI\GDT_Link;
use GDO\Core\GDT_Template;
use GDO\UI\GDT_Page;
use GDO\Core\Website;

/**
 * News module.
 * 
 * @author gizmore
 * @version 7.0.1
 * @since 6.3.0
 */
final class Module_News extends GDO_Module
{
    public int $priority = 40;
    
	##############
	### Module ###
	##############
	public function hrefAdministration() : ?string { return href('News', 'Admin'); }
	public function onLoadLanguage() : void { $this->loadLanguage('lang/news'); }
	public function getDependencies() : array { return ['Comments', 'Category', 'Mail']; }
	public function getClasses() : array
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
	public function getConfig() : array
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
	public function cfgBlogbar() { return $this->getConfigValue('news_blogbar'); }
	public function cfgComments() { return $this->getConfigValue('news_comments'); }
	public function cfgGuestNews() { return $this->getConfigValue('news_guests'); }
	public function cfgGuestNewsletter() { return $this->getConfigValue('newsletter_guests'); }
	public function cfgGuestComments() { return $this->getConfigValue('news_guest_comments'); }
	public function cfgLeftBar() { return $this->getConfigValue('hook_sidebar'); }
	
	############
	### Init ###
	############
	public function onInit()
	{
		Website::addLink($this->href('RSSFeed'), 'application/rss+xml', 'alternate', t('rss_newsfeed', [sitename()]));
	}
	
	############
	### Navs ###
	############
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
	
	public function onInitSidebar() : void
	{
	    if ($this->cfgLeftBar())
	    {
    	    GDT_Page::instance()->leftBar()->addField(
    	        GDT_Link::make('link_news')->href(href('News', 'NewsList')));
	    }
	    if ($this->cfgBlogbar())
	    {
	        GDT_Page::instance()->leftBar()->addField(
	            GDT_Template::make()->template('News', 'blogbar.php', ['bar'=>GDT_Page::instance()->leftBar()]));
	    }
	}

}
