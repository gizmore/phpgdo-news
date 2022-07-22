<?php
use GDO\UI\GDT_Bar;
use GDO\UI\GDT_Link;

$bar = GDT_Bar::make()->horizontal();
$bar->addFields(
	GDT_Link::make('btn_overview')->href(href('News', 'Admin')),
	GDT_Link::make('link_write_news')->href(href('News', 'Write')),
	GDT_Link::make('link_newsletters')->href(href('News', 'Newsletters')),
);
echo $bar->renderCell();
