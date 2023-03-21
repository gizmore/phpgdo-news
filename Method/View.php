<?php
namespace GDO\News\Method;

use GDO\Core\GDO;
use GDO\News\GDO_News;
use GDO\Table\MethodQueryCard;

final class View extends MethodQueryCard
{

	public function gdoTable(): GDO { return GDO_News::table(); }

	public function getMethodTitle(): string
	{
		return $this->getNews()->getTitle();
	}

	public function getNews(): GDO_News
	{
		return $this->getQueryCard();
	}

}
