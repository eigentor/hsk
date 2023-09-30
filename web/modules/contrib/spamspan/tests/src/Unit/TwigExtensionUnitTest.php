<?php

namespace Drupal\Tests\spamspan\Unit;

use Drupal\spamspan\Plugin\Filter\FilterSpamspan;
use Drupal\spamspan\SpamspanService;
use Drupal\spamspan\TwigExtension\SpamspanExtension;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the twig extension.
 *
 * @group spamspan
 */
class TwigExtensionUnitTest extends UnitTestCase {

  /**
   * The system under test.
   *
   * @var \Drupal\Core\Template\TwigExtension
   */
  protected $systemUnderTest;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $filter = [
      'id' => 'filter_spamspan',
      'title' => 'SpamSpan email address encoding filter',
      'description' => 'Attempt to hide email addresses from spam-bots.',
      'type' => 3,
      'settings' => [
        'spamspan_at' => " [at] ",
        'spamspan_use_graphic' => FALSE,
        'spamspan_dot_enable' => FALSE,
        'spamspan_dot' => " [dot] ",
        'spamspan_use_form' => FALSE,
        'spamspan_form_pattern' => '<a href=""%url?goto=%email"">%displaytext</a>',
        'spamspan_form_default_url' => 'contact',
        'spamspan_form_default_displaytext' => 'contact form',
      ],
    ];
    $renderer = $this->createMock('\Drupal\Core\Render\Renderer');
    $filterManager = $this->createMock('\Drupal\filter\FilterPluginManager');
    $filterManager->expects($this->any())->method('getDefinition')
      ->willReturn($filter);
    $filterManager->expects($this->any())->method('createInstance')
      ->willReturn(new FilterSpamspan($filter, 'filter_spamspan', ['provider' => 'filter']));
    $spamspanService = new SpamspanService($filterManager);

    $this->systemUnderTest = new SpamspanExtension($renderer, $spamspanService);
  }

  /**
   * Tests Twig 'spamspan' filter.
   *
   * @dataProvider providerTestTwigSpamspan
   */
  public function testTwigSpamspanFilter($element, $expected_result) {
    $processed = $this->systemUnderTest->spamspanFilter($element);
    $this->assertEquals($expected_result, $processed);
  }

  /**
   * A data provider for ::testTwigSpamspanFilter().
   *
   * @return \Iterator
   *   An iterator.
   */
  public function providerTestTwigSpamspan(): \Iterator {
    yield 'should not remove anchor' => [
      '<a href="http://example.com">link</a>',
      '<a href="http://example.com">link</a>',
    ];
    yield 'should remove anchor' => [
      '<a href="mailto:email@example.com"></a>',
      '<span class="spamspan"><span class="u">email</span> [at] <span class="d">example.com</span></span>',
    ];
    yield 'should not remove img' => [
      '<a href="mailto:email@example.com"><img src="/core/misc/favicon.ico"></a>',
      '<span class="spamspan"><span class="u">email</span> [at] <span class="d">example.com</span><span class="t"> (<img src="/core/misc/favicon.ico">)</span></span>',
    ];
    yield 'should maintain email subject' => [
      '<a href="mailto:email@example.com?subject=Hi there!&body=Dear Sir">some text</a>',
      '<span class="spamspan"><span class="u">email</span> [at] <span class="d">example.com</span><span class="h"> (subject: Hi%20there%21, body: Dear%20Sir) </span><span class="t"> (some text)</span></span>',
    ];
    yield 'should remove email from anchor text' => [
      '<a href="mailto:email@example.com">The email@example.com should show and email2@example.me</a>',
      '<span class="spamspan"><span class="u">email</span> [at] <span class="d">example.com</span><span class="t"> (The email[at]example[dot]com should show and email2[at]example[dot]me)</span></span>',
    ];
    yield 'should maintain anchor classes' => [
      '<a class="someclass" data-before="before" href="mailto:email@example.com" id="someid" data-after="after"></a>',
      '<span class="spamspan" data-spamspan-class="someclass" data-spamspan-data-before="before" data-spamspan-id="someid" data-spamspan-data-after="after"><span class="u">email</span> [at] <span class="d">example.com</span></span>',
    ];
    yield 'should maintain keep encoded subject' => [
      '<a href="mailto:email@example.com?subject=Message%20Subject%2C%20nasty%20%22%20%3Cchars%3F%3E&body=%22This%20is%20a%20message%20body%21%20%3C%20%3E%20%22%3F%0A%0A%21%22%C2%A3%24%25%5E%26%2A%28%29%3A%40~%3B%23%3C%3E%3F%2C.%2F%20%5B%5D%20%7B%7D%20-%3D%20_%2B">some text</a>',
      '<span class="spamspan"><span class="u">email</span> [at] <span class="d">example.com</span><span class="h"> (subject: Message%20Subject%2C%20nasty%20%22%20%3Cchars%3F%3E, body: %22This%20is%20a%20message%20body%21%20%3C%20%3E%20%22%3F%0A%0A%21%22%C2%A3%24%25%5E%26%2A%28%29%3A%40~%3B%23%3C%3E%3F%2C.%2F%20%5B%5D%20%7B%7D%20-%3D%20_%2B) </span><span class="t"> (some text)</span></span>',
    ];
    yield 'should maintain html encode subject' => [
      '<a href="mailto:email@example.com?subject=Hi there!&body=Dear\'Sir">some text</a>',
      '<span class="spamspan"><span class="u">email</span> [at] <span class="d">example.com</span><span class="h"> (subject: Hi%20there%21, body: Dear%27Sir) </span><span class="t"> (some text)</span></span>',
    ];
    yield 'should keep anchor as is' => [
      '<a href="[media/file/1]">[media/file/1]</a>',
      '<a href="[media/file/1]">[media/file/1]</a>',
    ];
  }

}
