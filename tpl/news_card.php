<?php
namespace GDO\News\tpl;
/** @var $news \GDO\News\GDO_News **/
use GDO\UI\GDT_Button;
use GDO\UI\GDT_Card;
use GDO\UI\GDT_HTML;

$user = $news->getCreator();

$card = GDT_Card::make('news')->gdo($news);

$card->creatorHeader();

$card->titleRaw($news->getTitle());

$card->addField(GDT_HTML::make()->var($news->displayMessage()));

if ($news->canEdit($user))
{
	$card->actions()->addField(GDT_Button::make('btn_edit')->href(href('News', 'Write', '&id='.$news->getID()))->icon('edit'));
}

if ($news->gdoCommentsEnabled())
{
	$count = $news->getCommentCount();
	$card->actions()->addField(GDT_Button::make('link_comments')->label('link_comments', [$count])->icon('quote')->href(href('News', 'Comments', '&id='.$news->getID())));
	if ($news->gdoCanComment($user))
	{
		$card->actions()->addField(GDT_Button::make('btn_write_comment')->href(href('News', 'WriteComment', '&id='.$news->getID()))->icon('reply'));
	}
}

echo $card->render();
