<?php
namespace GDO\News\Method;

use GDO\Admin\MethodAdmin;
use GDO\Table\MethodQueryTable;
use GDO\News\Module_News;
use GDO\News\GDO_Newsletter;

/**
 * Table of newsletter subscriptions.
 * @author gizmore
 * @version 6.10
 * @since 6.0
 */
final class Newsletters extends MethodQueryTable
{
	use MethodAdmin;
	
	public function getTitleLangKey() { return 'link_newsletters'; }
	
	public function getPermission() : ?string { return 'staff'; }
	
	public function gdoTable() { return GDO_Newsletter::table(); }
	
	public function beforeExecute() : void
	{
	    $this->renderAdminBar();
	    Module_News::instance()->renderAdminTabs();
	}
	
}
