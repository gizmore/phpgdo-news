<?php
namespace GDO\News\Method;

use GDO\Core\GDO;
use GDO\News\Module_News;
use GDO\News\GDO_News;
use GDO\Table\MethodQueryCards;

/**
 * Render a list of news cards.
 *  
 * @author gizmore
 * @version 7.0.1
 * @since 6.5.0
 */
class NewsList extends MethodQueryCards
{
	public function getMethodTitle() : string { return t('link_news'); }
	
	public function gdoTable() : GDO { return GDO_News::table(); }
	
// 	public function useFetchInto() : bool { return false; }
	
	public function isGuestAllowed() : bool { return Module_News::instance()->cfgGuestNews(); }
	
	public function gdoHeaders() : array
	{
	    return $this->gdoTable()->getGDOColumns([
	        'news_creator', 'news_created']);
	}
	
	public function getDefaultOrder() : ?string { return 'news_created DESC'; }
	
	public function getQuery()
	{
		return parent::getQuery()->where('news_visible')->joinObject('newstext');
	}
	
	public function beforeExecute() : void
	{
	    Module_News::instance()->renderTabs();
	}
	
}
