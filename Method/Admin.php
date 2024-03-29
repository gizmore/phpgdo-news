<?php
namespace GDO\News\Method;

use GDO\Admin\MethodAdmin;
use GDO\Core\GDO;
use GDO\DB\Query;
use GDO\News\GDO_News;
use GDO\News\Module_News;
use GDO\Table\MethodQueryTable;
use GDO\UI\GDT_EditButton;
use GDO\UI\GDT_Title;

/**
 * Overview of news entries.
 *
 * @version 7.0.1
 * @since 6.10.0
 * @author gizmore
 */
final class Admin extends MethodQueryTable
{

	use MethodAdmin;

	public function getPermission(): ?string { return 'staff'; }

	public function onRenderTabs(): void
	{
		$this->renderAdminBar();
		Module_News::instance()->renderAdminTabs();
	}

	public function gdoTable(): GDO { return GDO_News::table(); }


	public function getDefaultOrder(): ?string { return 'news_created DESC'; }

	public function getMethodTitle(): string { return t('list_news_newslist'); }

	public function gdoHeaders(): array
	{
		$t = $this->gdoTable();
		return [
			GDT_EditButton::make(),
			$t->gdoColumn('news_category'),
			GDT_Title::make('newstext_title'),
			$t->gdoColumn('news_category'),
			$t->gdoColumn('news_visible'),
			$t->gdoColumn('news_send'),
			$t->gdoColumn('news_sent'),
			$t->gdoColumn('news_created'),
			$t->gdoColumn('news_creator'),
		];
	}

	public function getQuery(): Query
	{
		$query = parent::getQuery()->select('nt.*');
		$query->joinObject('newstext');
		return $query;
	}


}
