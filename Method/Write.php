<?php
namespace GDO\News\Method;

use GDO\Admin\MethodAdmin;
use GDO\Date\Time;
use GDO\Form\GDT_AntiCSRF;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Submit;
use GDO\Form\MethodForm;
use GDO\Language\Module_Language;
use GDO\News\GDT_NewsStatus;
use GDO\News\Module_News;
use GDO\News\GDO_News;
use GDO\News\GDO_NewsText;
use GDO\UI\GDT_Card;
use GDO\UI\GDT_Message;
use GDO\UI\GDT_Divider;
use GDO\UI\GDT_Tab;
use GDO\UI\GDT_Tabs;
use GDO\Util\Common;
use GDO\UI\GDT_Title;

/**
 * Write a news entry.
 * This is a bit more complex form with tabs for each edited language.
 * 
 * @author gizmore
 * @version 6.11.0
 * @since 3.0.0
 */
final class Write extends MethodForm
{
	use MethodAdmin;
	
	public function getPermission() : ?string { return 'staff'; }
	
	/**
	 * @var GDO_News
	 */
	private $news;
	
	public function onInit()
	{
		if ($id = Common::getRequestString('id'))
		{
			$this->news = GDO_News::table()->find($id);
		}
	}
	
	public function beforeExecute() : void
	{
		$this->renderNavBar('News');
		Module_News::instance()->renderAdminTabs();
	}
	
	public function createForm(GDT_Form $form) : void
	{
		$news = GDO_News::table();
		
		$form->textRaw(GDT_NewsStatus::make('status')->gdo($news)->renderHTML());
		
		# Category select
		$form->addFields(
			$news->gdoColumn('news_category'),
			GDT_Divider::make('div_texts'),
		);
		
		# Translation tabs
		$tabs = GDT_Tabs::make('tabs');
		foreach (Module_Language::instance()->cfgSupported() as $iso => $language)
		{
			# New tab
			$tab = GDT_Tab::make('tab_'.$iso)->labelRaw($language->renderName());

			# 2 Fields
			$primary = $iso === GDO_LANGUAGE;
			$title = GDT_Title::make()->name("iso][$iso][newstext_title")->label('title')->notNull($primary);
			$message = $this->makeMessageField($iso); 
			
			if ($this->news)
			{ # Old values
				if ($text = $this->news->getText($iso, false))
				{
					$title->initial($text->getTitle());
					$messageText = $text->getMessage();
					$message->initial($messageText);
				}
			}
			# Add
			$tab->addField($title);
			$tab->addField($message);
			$tabs->tab($tab);
		}
		$form->addField($tabs);
			
		# Buttons
	    $form->actions()->addField(GDT_Submit::make()->label('btn_save'));
	    
		# Dynamic buttons
		if ($this->news)
		{
			$form->actions()->addField(GDT_Submit::make('preview')->label('btn_preview'));
			
			if (!$this->news->isVisible())
			{
			    $form->actions()->addField(GDT_Submit::make('visible')->label('btn_visible'));
			}
			else
			{
			    $form->actions()->addField(GDT_Submit::make('invisible')->label('btn_invisible'));
				if (!$this->news->isSent())
				{
				    $form->actions()->addField(GDT_Submit::make('send')->label('btn_send_mail'));
				}
			}
		}

		$form->addField(GDT_AntiCSRF::make());
	}
	
	/**
	 * Make a message field for an iso.
	 * @param string $iso
	 * @return GDT_Message
	 */
	private function makeMessageField(string $iso) : GDT_Message
	{
	    $primary = $iso === GDO_LANGUAGE;
	    return GDT_Message::make("newstext_message[{$iso}]")->label('message')->notNull($primary);
	}
	
	private function updateNews(GDT_Form $form)
	{
	    # Update news
	    $news = $this->news ? $this->news : GDO_News::blank();
	    $catData = $form->getField('news_category')->getGDOData();
	    $news->setVars($catData);
	    $news->save();
	    
	    # Update texts
	    foreach ($_REQUEST[$form->name]['iso'] as $iso => $data)
	    {
	        $title = trim($data['newstext_title']);
	        $message = trim($data['newstext_message']);
	        if ($title && $message)
	        {
	            GDO_NewsText::blank([
	                'newstext_news' => $news->getID(),
	                'newstext_lang' => $iso,
	                'newstext_title' => $title,
	                'newstext_message' => $message,
	            ])->replace();
	        }
	    }
	    
	    if ($this->news)
	    {
	        $this->news->tempUnset('newstexts');
	        $this->news->recache();
	        $this->resetForm();
	    }
	    
	    return $news;
	}
	
	public function formValidated(GDT_Form $form)
	{
	    $news = $this->updateNews($form);

		if ($this->news)
		{
			return $this->message('msg_news_edited')->addField($this->renderPage());
		}
		
		$hrefEdit = href('News', 'Write', '&id='.$news->getID());
		$this->redirectMessage('msg_news_created', null, $hrefEdit);
		return $this->renderPage();
	}
	
	public function onSubmit_visible(GDT_Form $form)
	{
	    $this->news->saveVars(array(
	        'news_visible' => '1',
            'news_created' => Time::getDate(),
	    ));
	    $this->resetForm();
		return $this->message('msg_news_visible')->addField($this->renderPage());
	}
	
	public function onSubmit_invisible(GDT_Form $form)
	{
		$this->news->saveVar('news_visible', '0');
		$this->resetForm();
		return $this->message('msg_news_invisible')->addField($this->renderPage());
	}
	
	############
	### Mail ###
	############
	public function onSubmit_preview(GDT_Form $form)
	{
	    # Save
	    $this->updateNews($form);
	    
	    # Show card and form
	    return GDT_Card::make()->gdo($this->news)->
    	    addField(GDT_Divider::make())->
    	    addField($this->renderPage());
	}
	
	public function onSubmit_send(GDT_Form $form)
	{
		$this->news->saveVar('news_send', Time::getDate());
		return $this->message('msg_news_queue')->addField($this->renderPage());
	}
	
}
