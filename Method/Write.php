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
use GDO\UI\GDT_Message;
use GDO\UI\GDT_Divider;
use GDO\UI\GDT_Tab;
use GDO\UI\GDT_Tabs;
use GDO\UI\GDT_Title;
use GDO\Core\GDT_Object;
use GDO\UI\GDT_HTML;
use GDO\Core\GDT_Tuple;
use GDO\Language\Trans;
use GDO\UI\GDT_Menu;
use GDO\Language\GDT_Language;

/**
 * Write and edit a news entry. Put in mailqueue and translate.
 * 
 * This is a bit more complex form with tabs for each edited language.
 * 
 * @author gizmore
 * @version 7.0.1
 * @since 3.0.0
 */
final class Write extends MethodForm
{
	use MethodAdmin;

	private ?GDO_News $news = null;
	
	public function getPermission() : ?string { return 'staff'; }
	
	public function gdoParameters() : array
	{
		return [
			GDT_Object::make('id')->table(GDO_News::table()),
		];
	}
	
	protected function getNews() : ?GDO_News
	{
		if (!isset($this->news))
		{
			$this->news = $this->gdoParameterValue('id');
		}
		return $this->news;
	}
	
	public function onRenderTabs() : void
	{
		$this->renderAdminBar();
		Module_News::instance()->renderAdminTabs();
	}
	
	public function createForm(GDT_Form $form) : void
	{
		$news = $this->getNews();
		$table = GDO_News::table();
		
		if ($news)
		{
			$form->textRaw(GDT_NewsStatus::make('status')->gdo($news)->renderHTML());
		}
		
		if ($gt = module_enabled('GTranslate'))
		{
			$sourceIso = GDT_Language::make('source_iso')->initialCurrent();
			$form->addFields($sourceIso);
		}
		
		# Category select
		$form->addFields(
			$table->gdoColumn('news_category'),
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
			$title = GDT_Title::make()->name("newstext_title_{$iso}")->label('title')->notNull($primary);
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
			$tabs->addTab($tab);
			if ($gt)
			{
				if ($sourceIso->getVar() !== $iso)
				{
					$tab->addField(GDT_Menu::make()->addField(
						GDT_Submit::make()->onclick([$this, 'onTranslateLanguage'], $iso),
					));
				}
			}
		}
		$form->addField($tabs);
			
		# Buttons
	    $form->actions()->addField(GDT_Submit::make()->label('btn_save'));
	    
		# Dynamic buttons
		if ($this->news)
		{
			$form->actions()->addFields(
				GDT_Submit::make('preview')->label('btn_preview')
					->onclick([$this, 'onSubmit_preview']));
			
			if ($gt)
			{
				$form->actions()->addFields(
					GDT_Submit::make('translate_all')->label('translate_all')
						->onclick([$this, 'onTranslateAll']));
			}
			
			if (!$this->news->isVisible())
			{
			    $form->actions()->addField(
			    	GDT_Submit::make('visible')->label('btn_visible')->
			    		onclick([$this, 'onSubmit_visible']));
			}
			else
			{
			    $form->actions()->addField(GDT_Submit::make('invisible')->label('btn_invisible'));
				if (!$this->news->isSent())
				{
				    $form->actions()->addField(
				    	GDT_Submit::make('send')->label('btn_send_mail')->
				    		onclick([$this, 'onSubmit_send']));
				}
			}
		}

		$form->addField(GDT_AntiCSRF::make());
	}
	
	/**
	 * Make a message field for an iso.
	 */
	private function makeMessageField(string $iso) : GDT_Message
	{
	    $primary = $iso === GDO_LANGUAGE;
	    return GDT_Message::make("newstext_message_{$iso}")->label('message')->notNull($primary);
	}
	
	private function updateNews(GDT_Form $form)
	{
	    # Update news
	    $news = $this->news ? $this->news : GDO_News::blank();
	    $catData = $form->getField('news_category')->getGDOData();
	    $news->setVars($catData);
	    $news->save();
	    
	    # Update texts
	    foreach (Module_Language::instance()->cfgSupported() as $language)
	    {
	    	$iso = $language->getISO();
	    	$title = $this->gdoParameterVar("newstext_title_{$iso}");
	    	$message = $this->gdoParameterVar("newstext_message_{$iso}");
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
		return $this->redirectMessage('msg_news_created', null, $hrefEdit);
	}
	
	public function onSubmit_visible(GDT_Form $form)
	{
	    $this->news->saveVars([
	        'news_visible' => '1',
            'news_created' => Time::getDate(),
	    ]);
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

	    # Render preview cards in all isos
	    $iso = Trans::$ISO;
	    $response = GDT_Tuple::make();
	    foreach (Module_Language::instance()->cfgSupported() as $language)
	    {
	    	Trans::setISO($language->getISO());
		    $card = GDT_HTML::make()->var($this->news->renderCard());
		    $response->addField($card);
	    }
	    Trans::setISO($iso);
	    
	    # Add default response
	    $response->addField(GDT_Divider::make());
	    $response->addField($this->renderPage());
	    return $response;
	}
	
	public function onSubmit_send(GDT_Form $form)
	{
		$this->news->saveVar('news_send', Time::getDate());
		return $this->message('msg_news_queue')->addField($this->renderPage());
	}
	
	#################
	### Translate ###
	#################
	public function onTranslateAll(GDT_Form $form)
	{
		$langs = Module_Language::instance()->cfgSupported();
		foreach (array_keys($langs) as $iso)
		{
			$this->onTranslateLanguage($iso);
		}
	}

	public function onTranslateLanguage(string $iso)
	{
		$src = $this->gdoParameter('source_iso');
		if ($src !== $iso)
		{
			$this->message('msg_news_translated', [$src, $iso]);
		}
	}
	
}
