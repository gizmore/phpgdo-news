<?php
namespace GDO\News\Test;

use GDO\Core\GDT;
use GDO\News\GDO_News;
use GDO\News\GDO_NewsComments;
use GDO\News\Method\Write;
use GDO\News\Method\WriteComment;
use GDO\Tests\GDT_MethodTest;
use GDO\Tests\TestCase;
use GDO\UI\GDT_Redirect;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertStringContainsString;

/**
 * Test for the news module.
 * Only write one entry for automated tests to kick in?
 *
 * @version 7.0.1
 * @since 6.10.0
 * @author gizmore
 */
final class NewsTest extends TestCase
{

	public function testNews()
	{
		$numNews = GDO_News::table()->countWhere();

		# Create
		$method = Write::make();
		$parameters = [
			'newstext_title_en' => 'Test news entry',
			'newstext_message_en' => '<div>I am happy to announce.<br/><br/>A comprehensive demo is available.<br/><br/>Happy Challenging!</div>',
			'newstext_title_de' => 'Test News Eintrag',
			'newstext_message_de' => '<div>Ich freue zu verkünden<br/><br/>Eine umfangreiche GDO6-Demo hat das Licht der Welt entdeckt.<br/><br/>Viel Spaß beim hacken!</div>',
		];
		$response = GDT_MethodTest::make()->method($method)
			->inputs($parameters)
			->execute('submit');
		$this->assertCode(GDT_Redirect::CODE, 'Check if a News::Write entry can be created.');
		assertEquals($numNews + 1, GDO_News::table()->countWhere(), 'check if news were created.');

		# Edit
		$method = Write::make();
		$parameters['id'] = '1';
		$parameters['newstext_message_de'] = '<div>Ich freue mich zu verkünden<br/><br/>Eine umfangreiche GDO6-Demo hat das Licht der Welt entdeckt.<br/><br/>Viel Spaß beim hacken!</div>';
		$response = GDT_MethodTest::make()->method($method)
			->inputs($parameters)
			->execute('submit');
		$html = $response->renderMode(GDT::RENDER_HTML);
		assertStringContainsString('freue mich zu verk', $html, 'Check if news message got changed.');
		assertEquals($numNews + 1, GDO_News::table()->countWhere(), 'check if newscount still 1');

		# Add a comment
		$amt = GDO_NewsComments::table()->countWhere();
		$method = WriteComment::make();
		$parameters = [
			'id' => '1',
			'comment_message' => 'Ein wirklich gute Nachricht! :)',
		];
		$response = GDT_MethodTest::make()->method($method)
			->inputs($parameters)
			->execute('submit');
		$html = $response->renderMode(GDT::RENDER_WEBSITE);
		assertEquals($amt + 1, GDO_NewsComments::table()->countWhere(), 'Check if comment-count is now 1.');
		$this->assertOK('Check if a News::WriteComment entry can be created.');
	}

}
