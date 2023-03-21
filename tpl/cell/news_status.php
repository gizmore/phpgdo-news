<?php

use GDO\News\GDT_NewsStatus;
use GDO\UI\GDT_Icon;
use GDO\UI\GDT_Paragraph;

/** @var $field GDT_NewsStatus * */
$news = $field->getNews();

$icon = 'pause';
$lbl = 'newsletter_status_waiting';
if ($news->isSent())
{
	$icon = 'done_all';
	$lbl = 'newsletter_status_sent';
}
elseif ($news->isSending())
{
	$icon = 'done';
	$lbl = 'newsletter_status_in_queue';
}
$icon = GDT_Icon::iconS($icon);
$lbl = GDT_Paragraph::make()->textRaw($icon . ' ' . t($lbl));

echo $lbl->render();
