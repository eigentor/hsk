<?php

namespace Drupal\Tests\spamspan\FunctionalJavascript;

/**
 * Tests the spamspan javascript functionalities.
 *
 * @group spamspan
 */
class SpamSpanHtmlMailToTest extends SpamSpanJsTestBase {

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
  public function testMailUnobscuringWithSameMailInText() {
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
  public function testMailUnobscuringWithSameMailAndStringInText() {
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
  public function testMailUnobscuringWithDifferentMailInText() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com">different@email.com</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:example@email.com');
    $session->elementTextEquals('css', 'a.spamspan', 'different@email.com');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringWithDifferentMailAndStringInText() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com">Visit different@email.com</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:example@email.com');
    $session->elementTextEquals('css', 'a.spamspan', 'Visit different@email.com');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringNotEscapingOuterSpecialCharacters() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<div id="test-div">!"§$%&/()=? <a href="mailto:example@email.com">Test</a></div>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'div#test-div > a.spamspan');
    $session->elementTextEquals('css', 'div#test-div', '!"§$%&/()=? Test');
    $session->elementAttributeContains('css', 'div#test-div > a.spamspan', 'href', 'mailto:example@email.com');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringNotEscapingInnerSpecialCharacters() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com">!"§$%&/()=?</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan');
    $session->elementTextEquals('css', 'a.spamspan', '!"§$%&/()=?');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:example@email.com');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringWithClass() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com" class="test">Test</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan.test');
    $session->elementAttributeContains('css', 'a.spamspan.test', 'href', 'mailto:example@email.com');
    $session->elementTextEquals('css', 'a.spamspan.test', 'Test');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringWithId() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com" id="test-id">Test</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a#test-id.spamspan');
    $session->elementAttributeContains('css', 'a#test-id.spamspan', 'href', 'mailto:example@email.com');
    $session->elementTextEquals('css', 'a#test-id.spamspan', 'Test');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringWithIdAndClass() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com" id="test-id" class="test">Test</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a#test-id.spamspan.test');
    $session->elementAttributeContains('css', 'a#test-id.spamspan.test', 'href', 'mailto:example@email.com');
    $session->elementTextEquals('css', 'a#test-id.spamspan.test', 'Test');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringWithChildSpan() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com"><span>Test</span></a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan > span');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:example@email.com');
    $session->elementTextEquals('css', 'a.spamspan > span', 'Test');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringChildSpanWithClass() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com"><span class="label">Label</span></a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan > span.label');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:example@email.com');
    $session->elementTextEquals('css', 'a.spamspan > span.label', 'Label');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringMultipleChildSpansWithClass() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com"><span class="label">Label</span> <span class="value">Value</span></a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan > span.label');
    $session->elementExists('css', 'a.spamspan > span.value');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:example@email.com');
    $session->elementTextEquals('css', 'a.spamspan > span.label', 'Label');
    $session->elementTextEquals('css', 'a.spamspan > span.value', 'Value');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringIdChildSpanWithClass() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com" id="test-id"><span class="label">Label</span></a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a#test-id.spamspan > span.label');
    $session->elementAttributeContains('css', 'a#test-id.spamspan', 'href', 'mailto:example@email.com');
    $session->elementTextEquals('css', 'a.spamspan > span.label', 'Label');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringIdAndClassChildSpanWithClass() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com" id="test-id" class="test"><span class="label">Label</span></a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a#test-id.spamspan.test > span.label');
    $session->elementAttributeContains('css', 'a#test-id.spamspan.test', 'href', 'mailto:example@email.com');
    $session->elementTextEquals('css', 'a.spamspan.test > span.label', 'Label');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringIdAndClassMutlipleChildSpanWithClasses() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:example@email.com" id="test-id" class="test"><span class="label">Label</span> <span class="value">Value</span></a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a#test-id.spamspan.test > span.label');
    $session->elementExists('css', 'a#test-id.spamspan.test > span.value');
    $session->elementAttributeContains('css', 'a#test-id.spamspan.test', 'href', 'mailto:example@email.com');
    $session->elementTextEquals('css', 'a.spamspan.test > span.label', 'Label');
    $session->elementTextEquals('css', 'a.spamspan.test > span.value', 'Value');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringMultipleHtmlMailMarkup() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:first@email.com" id="test-id-first" class="test-class-first">first@email.com</a><a href="mailto:second@email.com" id="test-id-second" class="test-class-second">second@email.com</a><a href="mailto:third@email.com" id="test-id-third" class="test-class-third">third@email.com</a><a href="mailto:fourth@email.com" id="test-id-fourth" class="test-class-fourth" data-attribute="fourth">fourth@email.com</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementAttributeContains('css', 'a#test-id-first.spamspan.test-class-first', 'href', 'mailto:first@email.com');
    $session->elementAttributeContains('css', 'a#test-id-second.spamspan.test-class-second', 'href', 'mailto:second@email.com');
    $session->elementAttributeContains('css', 'a#test-id-third.spamspan.test-class-third', 'href', 'mailto:third@email.com');
    $session->elementAttributeContains('css', 'a#test-id-fourth.spamspan.test-class-fourth', 'href', 'mailto:fourth@email.com');
    $session->elementAttributeNotExists('css', 'a#test-id-first.spamspan.test-class-first', 'data-attribute');
    $session->elementAttributeNotExists('css', 'a#test-id-second.spamspan.test-class-second', 'data-attribute');
    $session->elementAttributeNotExists('css', 'a#test-id-third.spamspan.test-class-third', 'data-attribute');
    $session->elementAttributeExists('css', 'a#test-id-fourth.spamspan.test-class-fourth', 'data-attribute');
    $session->elementAttributeContains('css', 'a#test-id-fourth.spamspan.test-class-fourth', 'data-attribute', 'fourth');
    $session->elementTextEquals('css', 'a#test-id-first.spamspan.test-class-first', 'first@email.com');
    $session->elementTextEquals('css', 'a#test-id-second.spamspan.test-class-second', 'second@email.com');
    $session->elementTextEquals('css', 'a#test-id-third.spamspan.test-class-third', 'third@email.com');
    $session->elementTextEquals('css', 'a#test-id-fourth.spamspan.test-class-fourth', 'fourth@email.com');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringChildIconNoText() {
    $session = $this->assertSession();

    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '
          <a class="test-anchor" href="mailto:example@mail.com">
            <i class="test1"></i>
          </a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementAttributeContains('css', 'a.test-anchor', 'href', 'mailto:example@mail.com');
    $session->elementExists('css', 'a.test-anchor > i.test1');
    $session->elementTextEquals('css', 'a.test-anchor > i.test1', '');
    // Find all anchor tags and child elements and see if they match the
    // original count:
    $session->elementsCount('css', 'a.test-anchor', 1);
    $session->elementsCount('css', 'i.test1', 1);
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringWithMultipleClasses() {
    $session = $this->assertSession();

    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '
          <a id="test-anchor" class="test1 test2 test3 test4" href="mailto:example@mail.com"></a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementAttributeContains('css', '#test-anchor', 'href', 'mailto:example@mail.com');
    $session->elementAttributeContains('css', '#test-anchor', 'class', 'test1 test2 test3 test4 spamspan');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringWithMultipleAttributesBeforeHref() {
    $session = $this->assertSession();

    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '
          <a id="test-anchor" class="test1 test2 test3 test4" rel="noopener noreferrer" data-test="test-data more-data" href="mailto:example@mail.com"></a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementAttributeContains('css', '#test-anchor', 'href', 'mailto:example@mail.com');
    $session->elementAttributeContains('css', '#test-anchor', 'class', 'test1 test2 test3 test4 spamspan');
    $session->elementAttributeContains('css', '#test-anchor', 'rel', 'noopener noreferrer');
    $session->elementAttributeContains('css', '#test-anchor', 'data-test', 'test-data more-data');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringWithMultipleAttributesAfterHref() {
    $session = $this->assertSession();

    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '
          <a href="mailto:example@mail.com" id="test-anchor" class="test1 test2 test3 test4" rel="noopener noreferrer" data-test="test-data more-data" ></a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementAttributeContains('css', '#test-anchor', 'href', 'mailto:example@mail.com');
    $session->elementAttributeContains('css', '#test-anchor', 'class', 'test1 test2 test3 test4 spamspan');
    $session->elementAttributeContains('css', '#test-anchor', 'rel', 'noopener noreferrer');
    $session->elementAttributeContains('css', '#test-anchor', 'data-test', 'test-data more-data');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringWithMultipleAttributesBeforeAndAfterHref() {
    $session = $this->assertSession();

    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '
          <a id="test-anchor" class="test1 test2 test3 test4" data-before="before-data more-data" href="mailto:example@mail.com" rel="noopener noreferrer" data-after="before-data even-more-data" ></a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementAttributeContains('css', '#test-anchor', 'href', 'mailto:example@mail.com');
    $session->elementAttributeContains('css', '#test-anchor', 'class', 'test1 test2 test3 test4 spamspan');
    $session->elementAttributeContains('css', '#test-anchor', 'rel', 'noopener noreferrer');
    $session->elementAttributeContains('css', '#test-anchor', 'data-before', 'before-data more-data');
    $session->elementAttributeContains('css', '#test-anchor', 'data-after', 'before-data even-more-data');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringAtInUserNameLabel() {
    $session = $this->assertSession();

    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '
          <a id="test-anchor" href="mailto:exampleatuser@mail.com">exampleatuser@mail.com</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementAttributeContains('css', '#test-anchor', 'href', 'mailto:exampleatuser@mail.com');
    $session->elementTextEquals('css', '#test-anchor', 'exampleatuser@mail.com');
  }

  /**
   * Tests mail unobscuring.
   */
  public function testMailUnobscuringAtInDomainLabel() {
    $session = $this->assertSession();

    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '
        <a id="test-anchor" href="mailto:example@mailatsomething.com">example@mailatsomething.com</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementAttributeContains('css', '#test-anchor', 'href', 'mailto:example@mailatsomething.com');
    $session->elementTextEquals('css', '#test-anchor', 'example@mailatsomething.com');
  }

  /**
   * Tests mail unobscuring when address contains dots in user- and domain-part.
   */
  public function testMailWithDotsUnobscuringWithSameMailInText() {
    $session = $this->assertSession();
    // Create a node :
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:first.middle.name@multi.sub.domain.com">first.middle.name@multi.sub.domain.com</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:first.middle.name@multi.sub.domain.com');
    $session->elementTextEquals('css', 'a.spamspan', 'first.middle.name@multi.sub.domain.com');
  }

  /**
   * Tests mail unobscuring.
   *
   * Tests mail unobscuring when address contains dashes in local- and
   * domain-part.
   */
  public function testMailWithDashesUnobscuringWithSameMailInText() {
    $session = $this->assertSession();
    // Create a node:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:first-name@web-site.com">first-name@web-site.com</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:first-name@web-site.com');
    $session->elementTextEquals('css', 'a.spamspan', 'first-name@web-site.com');
  }

  /**
   * Tests mail unobscuring.
   *
   * Tests mail unobscuring when address contains the string 'at' at any
   * position in local- and domain-part.
   */
  public function testMailWithAtStringUnobscuringWithSameMailInText() {
    $session = $this->assertSession();
    // Create a node :
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:attach-ablative@platz-goat.com">attach-ablative@platz-goat.com</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:attach-ablative@platz-goat.com');
    $session->elementTextEquals('css', 'a.spamspan', 'attach-ablative@platz-goat.com');
  }

  /**
   * Tests mail unobscuring.
   *
   * Tests mail unobscuring when address contains the string 'dot' at any
   * position in local- and domain-part.
   */
  public function testMailWithDotStringUnobscuringWithSameMailInText() {
    $session = $this->assertSession();
    // Create a node :
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:dota.anecdotal@antidote-haggadot.com">dota.anecdotal@antidote-haggadot.com</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:dota.anecdotal@antidote-haggadot.com');
    $session->elementTextEquals('css', 'a.spamspan', 'dota.anecdotal@antidote-haggadot.com');
  }

  /**
   * Tests mail unobscuring when address contains a plus in local-part.
   */
  public function testMailWithPlusCharacterUnobscuringWithSameMailInText() {
    $session = $this->assertSession();
    // Create a node with a mail-adress containing a plus:
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'test123',
      'body' => [
        'value' => '<a href="mailto:mail+context@example.com">mail+context@example.com</a>',
        'format' => 'spamspan_filter',
      ],
    ]);
    $node->save();

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a.spamspan');
    $session->elementAttributeContains('css', 'a.spamspan', 'href', 'mailto:mail+context@example.com');
    $session->elementTextEquals('css', 'a.spamspan', 'mail+context@example.com');
  }

}
