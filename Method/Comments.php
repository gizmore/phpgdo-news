<?php
namespace GDO\News\Method;

use GDO\Comments\Comments_List;
use GDO\Comments\GDO_CommentTable;
use GDO\News\GDO_NewsComments;
use GDO\News\Module_News;

final class Comments extends Comments_List
{

	public function gdoCommentsTable(): GDO_CommentTable { return GDO_NewsComments::table(); }

	public function hrefAdd() { return href('News', 'Comments', 'id=' . $this->object->getID()); }

	public function isGuestAllowed(): string { return Module_News::instance()->cfgGuestNews(); }

}
