<?php
namespace GDO\News\Method;

use GDO\Comments\Comments_Write;
use GDO\Comments\GDO_CommentTable;
use GDO\Core\GDT;
use GDO\News\GDO_NewsComments;
use GDO\News\Module_News;
use GDO\User\GDO_User;

final class WriteComment extends Comments_Write
{

	public function gdoCommentsTable(): GDO_CommentTable { return GDO_NewsComments::table(); }

	public function hrefList(): string { return href('News', 'Comments', '&id=' . $this->object->getID()); }

	public function isGuestAllowed(): bool { return Module_News::instance()->cfgGuestComments(); }

	public function execute(): GDT
	{
		$user = GDO_User::current();
		$module = Module_News::instance();
		if (!$module->cfgComments())
		{
			return $this->error('err_comments_disabled');
		}
		elseif ((!$module->cfgGuestComments()) && (!$user->isMember()))
		{
			return $this->error('err_guest_comments_disabled');
		}
		return parent::execute();
	}

}
