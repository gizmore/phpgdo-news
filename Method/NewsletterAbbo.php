<?php
namespace GDO\News\Method;

use GDO\Form\GDT_AntiCSRF;
use GDO\Core\GDT_Enum;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Submit;
use GDO\Form\MethodForm;
use GDO\Language\GDT_Language;
use GDO\Language\Trans;
use GDO\Mail\GDT_Email;
use GDO\Mail\GDT_EmailFormat;
use GDO\News\GDT_NewsletterStatus;
use GDO\News\Module_News;
use GDO\News\GDO_Newsletter;
use GDO\User\GDO_User;

/**
 * Susbscribe to the newsletter.
 * 
 * @author gizmore
 * @see News
 * @see Newsletter
 * @see News_Send
 * @version 7.0.1
 * @since 6.3.0
 */
final class NewsletterAbbo extends MethodForm
{
	public function isGuestAllowed() : bool
	{
		return Module_News::instance()->cfgGuestNewsletter();
	}
	
	public function isUserRequired() : bool
	{
		return false;
	}
	
	public function onRenderTabs() : void
	{
	    Module_News::instance()->renderTabs();
	}
	
	public function createForm(GDT_Form $form) : void
	{
		$user = GDO_User::current();
		$mem = $user->isMember();
		$subscribed = $mem ? GDO_Newsletter::hasSubscribed($user) : true;
		$form->gdo($user);
		$form->addFields(
			GDT_NewsletterStatus::make('status')->gdo($user),
			GDT_Enum::make('yn')->enumValues('yes', 'no')->initial($subscribed?'yes':'no')->label('newsletter_subscribed')->writeable($mem),
			GDT_EmailFormat::make('newsletter_fmt')->initial($mem?$user->getMailFormat():GDT_EmailFormat::HTML)->writeable(!$mem),
			GDT_Language::make('newsletter_lang')->initial($mem?$user->getLangISO():Trans::$ISO)->writeable(!$mem),
			GDT_Email::make('newsletter_email')->initial($user->getMail())->writeable(!$mem),
			GDT_AntiCSRF::make(),
		);
		$form->actions()->addField(GDT_Submit::make());
	}
	
	public function formValidated(GDT_Form $form)
	{
		return $this->formAction($form)->addField($this->renderPage());
	}
	
	public function formAction(GDT_Form $form)
	{
		$user = GDO_User::current();
		$oldsub = $user->isMember() ? GDO_Newsletter::hasSubscribed($user) : false;
		
		if ($form->getFormVar('yn') === 'yes')
		{
			if ($user->isMember())
			{
				if ($oldsub)
				{
					return $this->error('err_newsletter_already_subscribed');
				}
				$initial = array('newsletter_user' => $user->getID());
			}
			elseif (null === ($email = $form->getFormVar('newsletter_email')))
			{
				return $this->error('err_newsletter_no_email');
			}
			elseif (GDO_Newsletter::hasSubscribedMail($email))
			{
				return $this->error('err_newsletter_already_subscribed');
			}
			else
			{
				$initial = $form->getFormVars();
			}
			GDO_Newsletter::blank($initial)->insert();
			return $this->message('msg_newsletter_subscribed');
		}
		elseif (!$oldsub)
		{
			return $this->error('err_newsletter_not_subscribed');
		}
		else
		{
			GDO_Newsletter::table()->deleteWhere('newsletter_user='.$user->getID());
			return $this->message('msg_newsletter_unsubscribed');
		}
	}

}
