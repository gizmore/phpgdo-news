<?php
namespace GDO\News;

use GDO\Core\GDT_Template;
use GDO\Core\WithGDO;
use GDO\UI\GDT_Label;
use GDO\UI\WithIcon;

/**
 * Render a news status.
 *
 * @version 7.0.1
 */
final class GDT_NewsStatus extends GDT_Label
{

	use WithGDO;
	use WithIcon;

	public function isTestable(): bool
	{
		return false;
	}

	public function renderHTML(): string
	{
		return GDT_Template::php('News', 'cell/news_status.php', ['field' => $this]);
	}

	public function getNews(): GDO_News
	{
		return $this->gdo;
	}

}
