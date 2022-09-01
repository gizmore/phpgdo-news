<?php
namespace GDO\News;

use GDO\Category\GDO_Category;
use GDO\Category\GDT_Category;
use GDO\Comments\CommentedObject;
use GDO\Core\GDO;
use GDO\Core\GDT_AutoInc;
use GDO\Core\GDT_CreatedAt;
use GDO\Core\GDT_CreatedBy;
use GDO\Date\GDT_DateTime;
use GDO\Language\Trans;
use GDO\Core\GDT_Template;
use GDO\Core\GDT_Checkbox;
use GDO\User\GDO_User;
use GDO\Core\GDT_Join;
use GDO\Language\GDO_Language;
use GDO\Date\Time;

/**
 * News database entity and table.
 * 
 * @author gizmore
 * @version 7.0.1
 * @since 2.0.0
 * @see GDO_NewsText
 */
final class GDO_News extends GDO implements RSSItem
{
	################
	### Comments ###
	################
	use CommentedObject;
	
	public function gdoCommentTable() { return GDO_NewsComments::table(); }
	public function gdoCommentsEnabled() { return $this->isVisible() && $this->gdoCommentTable()->gdoEnabled(); }
	public function gdoCanComment(GDO_User $user) { return true; }
	
	###########
	### GDO ###
	###########
	public function gdoColumns() : array
	{
	    $iso = GDO_Language::current()->getISO();
	    
	    return [
			GDT_AutoInc::make('news_id'),
			GDT_Category::make('news_category')->emptyInitial(t('no_category')),
			GDT_Checkbox::make('news_visible')->notNull()->initial('0'),
			GDT_DateTime::make('news_send')->label('news_sending')->format(Time::FMT_MINUTE), # is in queue? (sending)
			GDT_DateTime::make('news_sent')->label('news_sent'), # is out of queue? (sent)
			GDT_CreatedAt::make('news_created'),
			GDT_CreatedBy::make('news_creator'),

	        # join
		    GDT_Join::make('newstext')->join(GDO_NewsText::table(), 'nt', "nt.newstext_news = gdo_news.news_id AND nt.newstext_lang = '{$iso}'"),
	    ];
	}
	
	##############
	### Getter ###
	##############
	public function getID() : ?string { return $this->gdoVar('news_id'); }
	public function isSent() { return $this->getSentDate() !== null; }
	public function isSending() { return ($this->getSentDate() === null) && ($this->getSendDate() !== null); }
	public function displayName() { return $this->getTitle(); }
	
	public function getCategory() : ?GDO_Category { return $this->gdoValue('news_category'); }
	public function getCategoryID() { return $this->gdoVar('news_category'); }
	public function isVisible() { return $this->gdoVar('news_visible') === '1'; }
	public function getSendDate() { return $this->gdoVar('news_send'); }
	public function getSentDate() { return $this->gdoVar('news_sent'); }
	public function getCreateDate() { return $this->gdoVar('news_created'); }
	public function displayDay() { return tt($this->getCreateDate(), 'day'); }
	
	public function getCreator() : GDO_User { return $this->gdoValue('news_creator'); }
	public function getCreatorID() { return $this->gdoVar('news_creator'); }
	
	### Perm ###
	public function canEdit(GDO_User $user) { return $user->isStaff(); }
	
	public function href_edit() { return href('News', 'Write', '&id='.$this->getID()); }
	public function href_view() { return href('News', 'View', '&id='.$this->getID()); }
	
	#############
	### Texts ###
	#############
	public function getTitle() { return $this->getTextVar('newstext_title'); }
	public function getMessage() { return $this->getTextVar('newstext_message_output'); }

	public function getTitleISO($iso) { return $this->getTextVarISO('newstext_title', $iso); }
	public function getMessageISO($iso) { return $this->getTextVarISO('newstext_message_output', $iso); }
	
	public function getTextVar($key) { return $this->getText(Trans::$ISO)->gdoVar($key); }
	public function getTextValue($key) { return $this->getText(Trans::$ISO)->gdoValue($key); }
	
	public function getTextVarISO($key, $iso) { return $this->getText($iso)->gdoVar($key); }
	public function getTextValueISO($key, $iso) { return $this->getText($iso)->gdoValue($key); }
	
	public function displayMessage()
	{
		$text = $this->getTxt();
		return $text->gdoColumn('newstext_message')->var($text->getMessage())->renderHTML();
	}

	public function renderCard() : string
	{
		return GDT_Template::php('News', 'news_card.php', ['news' => $this]);
	}
	
	###################
	### Translation ###
	###################
	public function getTxt() : ?GDO_NewsText
	{
		return $this->getText(Trans::$ISO);
	}
	
	public function getText(string $iso, bool $fallback=true) : ?GDO_NewsText
	{
		$texts = $this->getTexts();
		if (isset($texts[$iso]))
		{
			return $texts[$iso];
		}
		if ($fallback)
		{
			return isset($texts[GDO_LANGUAGE]) ?
			    $texts[GDO_LANGUAGE] : array_shift($texts);
		}
		return null;
	}

	/**
	 * @return GDO_NewsText[]
	 */
	public function getTexts() : array
	{
		if (null === ($cache = $this->tempGet('newstexts')))
		{
			$query = GDO_NewsText::table()->select('newstext_lang, gdo_newstext.*');
			$query->where("newstext_news=".$this->getID());
			$cache = $query->exec()->fetchAllArrayAssoc2dObject();
			$this->tempSet('newstexts', $cache);
			$this->recache();
		}
		return $cache;
	}
	
	###############
	### RSSItem ###
	###############
	public function getRSSTitle() { return $this->getTitle(); }
	public function getRSSPubDate() { return Time::parseDateTimeDB($this->gdoVar('news_created')); }
	public function getRSSGUID() { return $this->gdoHashcode(); }
	public function getRSSLink() { return url('News', 'Comments', '&id='.$this->getID()); }
	public function getRSSDescription() { return $this->displayMessage(); }

	##############
	### Render ###
	##############
	public function renderCLI() : string
	{
		return 'H!';
	}
	
}
