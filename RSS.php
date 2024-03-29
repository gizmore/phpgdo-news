<?php
namespace GDO\News;

use DateTime;
use GDO\Core\Application;
use GDO\Core\GDT_Template;
use GDO\Date\Time;
use GDO\Language\Trans;
use GDO\Net\GDT_Url;

final class RSS
{

	private $title;
	private $descr;
	/**
	 * @var GDO_News[]
	 */
	private $items;
	private $webURL;
	private $feedURL;

	public function __construct($title, $descr, array $items, $webURL, $feedURL)
	{
		$this->title = $title;
		$this->descr = $descr;
		$this->items = $items;
		$this->webURL = $webURL;
		$this->feedURL = $feedURL;
	}

	public static function displayCData($data)
	{
		return '<![CDATA[' . htmlspecialchars($data) . ']]>';
	}

	public function setWebURL($webURL)
	{
		$this->webURL = $webURL;
	}

	public function setFeedURL($feedURL)
	{
		$this->feedURL = $feedURL;
	}

	public function setTitle($title)
	{
		$this->title = $title;
	}

	public function setDescription($descr)
	{
		$this->descr = $descr;
	}

	public function setItems(array $items)
	{
		$this->items = $items;
	}

	public function render()
	{
		hdr('Content-Type: application/xml; charset=UTF-8');

		$rss_date = self::displayDate($this->guessRSSDate());

		$tVars = [
			'items' => $this->items,
			'title_link' => $this->feedURL,
			'feed_title' => $this->title,
			'feed_description' => $this->descr,
			'language' => Trans::$ISO,
			'image_url' => GDT_Url::absolute(GDO_WEB_ROOT . 'favicon.ico'),
			'image_link' => $this->webURL,
			'image_width' => '32',
			'image_height' => '32',
			'pub_date' => $rss_date,
			'build_date' => $rss_date,
		];

		return GDT_Template::php('News', 'rss.php', $tVars);
	}

	public static function displayDate(DateTime $date = null)
	{
		if ($date === null)
		{
			$date = DateTime::createFromFormat('U', Application::$TIME);
		}
		return $date->format(DateTime::RSS);
	}

	public function guessRSSDate()
	{
		if (count($this->items) === 0)
		{
			return Time::getDateTime(Time::getTimestamp(GDO_SITECREATED));
		}
		else
		{
			$item = $this->items[0];
			return $item->getRSSPubDate();
		}
	}

}
