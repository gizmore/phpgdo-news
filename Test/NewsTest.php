<?php
namespace GDO\News\Test;

use GDO\Tests\GDT_MethodTest;
use GDO\Tests\TestCase;
use GDO\News\Method\Write;
use GDO\News\GDO_News;
use function PHPUnit\Framework\assertStringContainsString;
use function PHPUnit\Framework\assertEquals;
use GDO\Core\GDT;

/**
 * Tests for the news module.
 * Only write one entry for automated tests to kick in?
 *
 * @author gizmore
 * @version 7.0.1
 * @since 6.10.0
 */
final class NewsTest extends TestCase
{
	public function testNews()
	{
		$method = Write::make();
		$parameters = [
			'newstext_title_en' => 'Test news entry',
			'newstext_message_en' => '<div>I am happy to announce.<br/><br/>A comprehensive demo is available.<br/><br/>Happy Challenging!</div>',
			'newstext_title_de' => 'Test News Eintrag',
			'newstext_message_de' => '<div>Ich freue zu verkünden<br/><br/>Eine umfangreiche GDO6-Demo hat das Licht der Welt entdeckt.<br/><br/>Viel Spaß beim hacken!</div>',
		];
		$response = GDT_MethodTest::make()->method($method)
			->parameters($parameters)
			->execute();
		$this->assert200("Check if a News::Write entry can be created.");
		assertEquals(1, GDO_News::table()->countWhere(), 'check if news were created.');

		$method = Write::make();
		$parameters['id'] = '1';
		$parameters['newstext_message_de'] = '<div>Ich freue mich zu verkünden<br/><br/>Eine umfangreiche GDO6-Demo hat das Licht der Welt entdeckt.<br/><br/>Viel Spaß beim hacken!</div>';
		$response = GDT_MethodTest::make()->method($method)
			->parameters($parameters)
			->execute();
		$html = $response->renderMode(GDT::RENDER_HTML);
		assertStringContainsString('freue mich zu verk', $html, 'Check if news message got changed.');
		assertEquals(1, GDO_News::table()->countWhere(), 'check if newscount still 1');
	}

}
