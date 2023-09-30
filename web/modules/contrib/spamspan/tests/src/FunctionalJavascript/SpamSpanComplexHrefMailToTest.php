<?php

namespace Drupal\Tests\spamspan\FunctionalJavascript;

/**
 * Tests the spamspan javascript functionalities.
 *
 * @todo This class is currently disabled, it should be used to test
 * https://www.drupal.org/project/spamspan/issues/3220650. You can enable the
 * class by removing the "abstract" from the class name
 *
 * @group spamspan
 */
abstract class SpamSpanComplexHrefMailToTest extends SpamSpanJsTestBase {

  /**
   * {@inheritdoc}
   *
   * @todo Remove this class property in https://www.drupal.org/node/3091878/.
   */
  protected $failOnJavascriptConsoleErrors = FALSE;

  /**
   * Tests mail unobscuring.
   */
  public function testSimpleMailUnobscuring() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com">Test</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:example@email.com');
    $session->elementTextEquals('css', 'a.spamspan', 'Test');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringWithMultipleWordsInText() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com">Test Test Test</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:example@email.com');
    $session->elementTextEquals('css', 'a.spamspan', 'Test Test Test');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringWithMailInText() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com">example@email.com</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:example@email.com');
    $session->elementTextEquals('css', 'a.spamspan', 'example@email.com');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringWithMailAndStringInText() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com">Visit example@email.com</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:example@email.com');
    $session->elementTextEquals('css', 'a.spamspan', 'Visit example@email.com');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringToMultipleRecipients() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com%3B%20other@email.com">Test</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:example@email.com%3B%20other@email.com');
    $session->elementTextEquals('css', 'a.spamspan', 'Test');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringToMultipleRecipientsAndMailInText() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com%3B%20other@email.com">Please write example@email.com and other@email.com</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:example@email.com%3B%20other@email.com');
    $session->elementTextEquals('css', 'a.spamspan', 'Please write example@email.com and other@email.com');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringWithCc() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com?cc=other@email.com">Test</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:example@email.com?cc=other@email.com');
    $session->elementTextEquals('css', 'a.spamspan', 'Test');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringWithCcAndMultipleRecipients() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com%3B%20other@email.com?cc=another@email.com">Test</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:example@email.com%3B%20other@email.com?cc=another@email.com');
    $session->elementTextEquals('css', 'a.spamspan', 'Test');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringWithMultipleCc() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com?cc=other@email.com%3B%20another@email.com">Test</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:example@email.com?cc=other@email.com%3B%20another@email.com');
    $session->elementTextEquals('css', 'a.spamspan', 'Test');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringWithBcc() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com?bcc=other@email.com">Test</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:example@email.com?bcc=other@email.com');
    $session->elementTextEquals('css', 'a.spamspan', 'Test');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringWithMultipleBcc() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com?bcc=other@email.com%3B%20another@email.com">Test</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:mailto:example@email.com?bcc=other@email.com%3B%20another@email.com');
    $session->elementTextEquals('css', 'a.spamspan', 'Test');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringWithCcAndBcc() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com?cc=other@email.com&bcc=another@email.com">Test</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:example@email.com?cc=other@email.com&bcc=another@email.com');
    $session->elementTextEquals('css', 'a.spamspan', 'Test');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringWithMultipleCcAndMultipleBcc() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com?cc=other@email.com%3B%20another@email.com&bcc=another@email.com%3B%20another@email.com">Test</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:example@email.com?cc=other@email.com%3B%20another@email.com&bcc=another@email.com%3B%20another@email.com');
    $session->elementTextEquals('css', 'a.spamspan', 'Test');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringWithMultipleCcMultipleBccAndMultipleRecipients() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com%3B%20other@email.com?cc=other@email.com%3B%20another@email.com&bcc=another@email.com%3B%20another@email.com">Test</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:example@email.com%3B%20other@email.com?cc=other@email.com%3B%20another@email.com&bcc=another@email.com%3B%20another@email.com');
    $session->elementTextEquals('css', 'a.spamspan', 'Test');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringWithSubject() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com?subject=Test%20Subject">Test</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:example@email.com?subject=Test%20Subject');
    $session->elementTextEquals('css', 'a.spamspan', 'Test');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringWithSubjectAndCc() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com?subject=Test%20Subject&cc=other@email.com">Test</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:example@email.com?subject=Test%20Subject&cc=other@email.com');
    $session->elementTextEquals('css', 'a.spamspan', 'Test');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringWithSubjectAndMultipleCc() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com?subject=Test%20Subject&cc=other@email.com%3B%20another@email.com">Test</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:example@email.com?subject=Test%20Subject&cc=other@email.com%3B%20another@email.com');
    $session->elementTextEquals('css', 'a.spamspan', 'Test');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringWithSubjectCcAndBcc() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com?subject=Test%20Subject&cc=other@email.com&bcc=other@email.com">Test</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:example@email.com?subject=Test%20Subject&cc=other@email.com&bcc=other@email.com');
    $session->elementTextEquals('css', 'a.spamspan', 'Test');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringWithSubjectMultipleCcAndMultipleBcc() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com?subject=Test%20Subject&cc=other@email.com%3B%20another@email.com&bcc=other@email.com%3B%20another@email.com">Test</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:example@email.com?subject=Test%20Subject&cc=other@email.com%3B%20another@email.com&bcc=other@email.com%3B%20another@email.com');
    $session->elementTextEquals('css', 'a.spamspan', 'Test');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringWithSubjectMultipleCcMultipleBccAndMultipleRecipients() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com%3B%20another@email.com?subject=Test%20Subject&cc=other@email.com%3B%20another@email.com&bcc=other@email.com%3B%20another@email.com">Test</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:example@email.com%3B%20another@email.com?subject=Test%20Subject&cc=other@email.com%3B%20another@email.com&bcc=other@email.com%3B%20another@email.com');
    $session->elementTextEquals('css', 'a.spamspan', 'Test');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringWithBody() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com?body=Test%20Body">Test</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:example@email.com?body=Test%20Body');
    $session->elementTextEquals('css', 'a.spamspan', 'Test');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringWithBodyAndCc() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com?body=Test%20Body&cc=other@email.com">Test</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:example@email.com?body=Test%20Body&cc=other@email.com');
    $session->elementTextEquals('css', 'a.spamspan', 'Test');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringWithBodyCcAndBcc() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com?body=Test%20Body&cc=other@email.com&bcc=another@email.com">Test</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:example@email.com?body=Test%20Body&cc=other@email.com&bcc=another@email.com');
    $session->elementTextEquals('css', 'a.spamspan', 'Test');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringWithBodyCcBccAndSubject() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com?body=Test%20Body&cc=other@email.com&bcc=another@email.com&subject=Test%Subject">Test</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:example@email.com?body=Test%20Body&cc=other@email.com&bcc=another@email.com&subject=Test%Subject');
    $session->elementTextEquals('css', 'a.spamspan', 'Test');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringWithBodyMultipleCcMultipleBccSubjectAndMultipleRecipients() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com%3B%20another@email.com?body=Test%20Body&cc=other@email.com%3B%20another@email.com&bcc=other@email.com%3B%20another@email.com&subject=Test%Subject">Test</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:example@email.com%3B%20another@email.com?body=Test%20Body&cc=other@email.com%3B%20another@email.com&bcc=other@email.com%3B%20another@email.com&subject=Test%Subject');
    $session->elementTextEquals('css', 'a.spamspan', 'Test');
  }

}
