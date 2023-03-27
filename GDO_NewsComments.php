<?php
namespace GDO\News;

use GDO\Comments\GDO_CommentTable;
use GDO\Core\GDO;

final class GDO_NewsComments extends GDO_CommentTable
{

	public function gdoCommentedObjectTable(): GDO { return GDO_News::table(); }

	public function gdoAllowFiles(): bool { return false; }

	public function gdoEnabled(): bool { return Module_News::instance()->cfgComments(); }

}
