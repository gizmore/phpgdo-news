<?php
namespace GDO\News;

use GDO\Core\GDT_Template;
use GDO\UI\GDT_Paragraph;
use GDO\UI\WithIcon;
use GDO\Core\WithGDO;
use GDO\User\GDO_User;

final class GDT_NewsletterStatus extends GDT_Paragraph
{
	use WithGDO;
	use WithIcon;
	
	public function isTestable(): bool
	{
		return false;
	}
	
	public function renderHTML() : string
	{
		return GDT_Template::php('News', 'cell/newsletter_status.php', ['field'=>$this]);
	}
	
	public function getUser() : GDO_User
	{
		return $this->gdo;
	}
	
}
