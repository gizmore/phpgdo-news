<?php
namespace GDO\News\Method;

use GDO\Date\Time;
use GDO\Mail\GDT_EmailFormat;
use GDO\Mail\Mail;
use GDO\News\GDO_News;
use GDO\UI\GDT_Link;
use GDO\News\GDO_Newsletter;
use GDO\Cronjob\MethodCronjob;

/**
 * Send newsletter via cronjob.
 * @author gizmore
 * @since 3.0
 * @version 5.0
 */
final class Send extends MethodCronjob
{
	public function run()
	{
		$table = GDO_News::table();
		$query = $table->select();
		$query->where("news_send IS NOT NULL AND news_sent IS NULL");
		$query->order('news_send');
		if ($news = $table->fetch($query->first()->exec()))
		{
			$this->sendNewsletter($news);
		}
	}
	
	private function sendNewsletter(GDO_News $news)
	{
		$this->logNotice("Sending newsletter for {$news->getTitle()}");
		$table = GDO_Newsletter::table();
		$query = $table->select('*')->where("newsletter_news IS NULL OR newsletter_news != {$news->getID()}");
		$result = $query->exec();
		$count = 0;
		while ($newsletter = $table->fetch($result))
		{
			$this->sendNewsletterTo($news, $newsletter);
			$count++;
		}
		$this->logNotice("Sent $count newsletter emails.");
		$news->saveVar('news_sent', Time::getDate());
	}
	
	private function sendNewsletterTo(GDO_News $news, GDO_Newsletter $newsletter)
	{
		$mail = $this->mailSkeleton($news, $newsletter);
		if ($user = $newsletter->getUser())
		{
			$mail->sendToUser($user);
		}
		elseif ($mail = $newsletter->getMail())
		{
			$mail->setReceiver($mail);
			if ($mail->getMailFormat() === GDT_EmailFormat::TEXT)
			{
				$mail->sendAsText();
			}
			else
			{
				$mail->sendAsHTML();
			}
		}
		$newsletter->saveVar('newsletter_news', $news->getID());
	}
		
	private function mailSkeleton(GDO_News $news, GDO_Newsletter $newsletter)
	{
		$user = $newsletter->getUser();
		$iso = $user ? $user->getLangISO() : $newsletter->getLangISO();
		$sitename = sitename();
		$username = $user ? $user->renderUserName() : tiso($iso, 'dear_member_of', [$sitename]);
		$date = tt($news->getCreateDate());
		$title = html($news->getTitleISO($iso));
		$author = $news->getCreator()->renderUserName();
		$message = html($news->getMessageISO($iso));
		$unsubscribeLink = GDT_Link::anchor(
				url('News', 'Unsubscribe', '&id='.$newsletter->getID().'&token='.$newsletter->gdoHashcode()));
		$mail = Mail::botMail();
		$mail->setSubject(tiso($iso, 'mail_subj_newsletter', [$sitename, $title]));
		$args = [$username, $sitename, $unsubscribeLink, $date, $title, $author, $message];
		$mail->setBody(tiso($iso, 'mail_body_newsletter', $args));
		return $mail;
	}
	
}
