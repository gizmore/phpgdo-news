<?php
namespace GDO\News\Method;

use GDO\Core\GDT;
use GDO\Core\GDT_Object;
use GDO\Core\GDT_Token;
use GDO\Core\Method;
use GDO\News\GDO_Newsletter;

/**
 * Unsubscribe newsletter via token.
 *
 * @version 7.0.1
 * @since 2.0.0
 * @author gizmore
 */
final class Unsubscribe extends Method
{

	public function isAlwaysTransactional(): bool { return true; }

	public function gdoParameters(): array
	{
		return [
			GDT_Object::make('id')->notNull()->table(GDO_Newsletter::table()),
			GDT_Token::make('token')->initialNull()->notNull(),
		];
	}

	public function execute(): GDT
	{
		if (
			(!($newsletter = $this->getNewsletter())) ||
			($newsletter->gdoHashcode() !== $this->getToken())
		)
		{
			return $this->error('err_newsletter_not_subscribed');
		}
		$newsletter->delete();
		return $this->message('msg_newsletter_unsubscribed');
	}

	public function getNewsletter(): GDO_Newsletter
	{
		return $this->gdoParameterValue('id');
	}

	public function getToken(): string
	{
		return $this->gdoParameterVar('token');
	}

}
