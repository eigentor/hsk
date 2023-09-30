<?php

namespace Drupal\Tests\spamspan\FunctionalJavascript;

/**
 * Tests the spamspan filter settings.
 *
 * @group spamspan
 */
class SpamSpanFilterSettingsJsEnabledTest extends SpamSpanJsTestBase {

  /**
   * Tests the "spamspan_use_form" filter format setting.
   */
  public function testUseFormFilterSetting() {
    $session = $this->assertSession();
    // Change the filter format settings:
    $filters = $this->spamSpanFilterFormat->get('filters');
    $filters['filter_spamspan']['settings'] = [
      'spamspan_use_form' => TRUE,
      'spamspan_form_pattern' => '<a id="test-id" href="%url?goto=%email">%displaytext</a>',
      'spamspan_form_default_url' => 'test-url',
      'spamspan_form_default_displaytext' => 'Test Form',
    ];
    $this->spamSpanFilterFormat->set('filters', $filters)->save();
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
    $encodedMail = urlencode(base64_encode('example@email.com'));

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->pageTextContains('test123');
    $session->elementExists('css', 'a#test-id');
    $session->elementAttributeContains('css', 'a#test-id', 'href', '/test-url?goto=' . $encodedMail);
    $session->elementTextEquals('css', 'a#test-id', 'Test Form');
  }

}
