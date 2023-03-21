<?php
namespace GDO\News\Method;

use GDO\Admin\MethodAdmin;
use GDO\Core\GDO;
use GDO\News\GDO_Newsletter;
use GDO\News\Module_News;
use GDO\Table\MethodQueryTable;

/**
 * Table of newsletter subscriptions.
 *
 * @version 7.0.1
 * @since 6.0.0
 * @author gizmore
 */
final class Newsletters extends MethodQueryTable
{

	use MethodAdmin;

	public function getMethodTitle(): string
	{
		return t('mt_news_newsletterabbo');
	}

	public function gdoTable(): GDO
	{
		return GDO_Newsletter::table();
	}

	public function onRenderTabs(): void
	{
		$this->renderAdminBar();
		Module_News::instance()->renderAdminTabs();
	}

}
