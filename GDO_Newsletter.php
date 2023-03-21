<?php
namespace GDO\News;

use GDO\Core\GDO;
use GDO\Core\GDT_AutoInc;
use GDO\Core\GDT_Int;
use GDO\Language\GDT_Language;
use GDO\Mail\GDT_Email;
use GDO\Mail\GDT_EmailFormat;
use GDO\User\GDO_User;
use GDO\User\GDT_User;

/**
 * Newsletter abbonements.
 * This table makes sense, as it can also hold unknown email recipients.
 *
 * @version 7.0.1
 * @since 6.1.0
 * @author gizmore
 */
final class GDO_Newsletter extends GDO
{

	###########
	### GDO ###
	###########
	public static function hasSubscribed(GDO_User $user) { return !!self::getByUser($user); }

	public static function getByUser(GDO_User $user) { return self::getBy('newsletter_user', $user->getID()); }

	public static function hasSubscribedMail($email = null) { return !!self::getByEmail($email); }

	public static function getByEmail($email = null) { return self::getBy('newsletter_email', $email); }

	public function gdoColumns(): array
	{
		return [
			GDT_AutoInc::make('newsletter_id'),
			GDT_Int::make('newsletter_news')->unsigned(), # Last received newsletter for cronjob via web state :P
			GDT_User::make('newsletter_user')->unique(),
			GDT_Email::make('newsletter_email')->unique(),
			GDT_Language::make('newsletter_lang'),
			GDT_EmailFormat::make('newsletter_fmt'),
		];
	}

	public function gdoHashcode(): string
	{
		return self::gdoHashcodeS($this->gdoVars(['newsletter_id', 'newsletter_user', 'newsletter_email']));
	}

	public function renderCLI(): string
	{
		return $this->getUser()->renderUserName();
	}

	##############
	### Static ###
	##############

	public function getUser(): GDO_User { return $this->gdoValue('newsletter_user'); }

	public function hasUser() { return $this->getUserID() !== null; }

	public function getUserID() { return $this->gdoVar('newsletter_user'); }

	public function getLangISO() { return $this->gdoVar('newsletter_lang'); }

	##############
	### Render ###
	##############

	public function getMail() { return $this->gdoValue('newsletter_email'); }

}
