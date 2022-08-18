<?php
namespace GDO\News\Method;

use GDO\Comments\Comments_List;
use GDO\Comments\GDO_CommentTable;
use GDO\News\Module_News;
use GDO\News\GDO_NewsComments;

final class Comments extends Comments_List
{
	public function gdoCommentsTable() : GDO_CommentTable { return GDO_NewsComments::table(); }
	public function hrefAdd() { return href('News', 'Comments', 'id='.$this->object->getID()); }
	
	public function isGuestAllowed() : bool { return Module_News::instance()->cfgGuestNews(); }

}
