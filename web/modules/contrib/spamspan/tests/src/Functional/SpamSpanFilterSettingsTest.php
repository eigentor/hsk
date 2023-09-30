<?php

namespace Drupal\Tests\spamspan\Functional;

use Drupal\filter\Entity\FilterFormat;
use Drupal\Tests\BrowserTestBase;

/**
 * This class provides methods specifically for testing something.
 *
 * @group spamspan
 */
class SpamSpanFilterSettingsTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'filter',
    'test_page_test',
    'spamspan',
  ];

  /**
   * A user with authenticated permissions.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $user;

  /**
   * A user with admin permissions.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * The spamspan filter format.
   *
   * @var \Drupal\filter\Entity\FilterFormat
   */
  protected $spamSpanFilterFormat;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->config('system.site')->set('page.front', '/test-page')->save();
    $this->user = $this->drupalCreateUser([]);
    $this->adminUser = $this->drupalCreateUser([]);
    $this->adminUser->addRole($this->createAdminRole('admin', 'admin'));
    $this->adminUser->save();
    $this->drupalLogin($this->adminUser);

    $this->createContentType(['type' => 'article']);

    $this->spamSpanFilterFormat = FilterFormat::create([
      'format' => 'spamspan_filter',
      'name' => 'Spam Span Filter',
      'filters' => [
        'filter_spamspan' => [
          'id' => 'filter_spamspan',
          'status' => TRUE,
          'weight' => 10,
        ],
      ],
    ]);
    $this->spamSpanFilterFormat->save();
  }

  /**
   * Tests the "spamspan_at" filter format setting.
   */
  public function testAtFilterSetting() {
    $session = $this->assertSession();
    // Change the filter format settings:
    $filters = $this->spamSpanFilterFormat->get('filters');
    $filters['filter_spamspan']['settings'] = [
      'spamspan_at' => ' {test} ',
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

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->statusCodeEquals(200);
    $session->pageTextContains('test123');
    $session->elementExists('css', 'span.spamspan');
    $session->elementTextEquals('css', 'span.spamspan', 'example {test} email.com (Test)');
    $session->elementTextEquals('css', 'span.spamspan > span.u', 'example');
    $session->elementTextEquals('css', 'span.spamspan > span.d', 'email.com');
    $session->elementTextEquals('css', 'span.spamspan > span.t', '(Test)');
  }

  /**
   * Tests the "spamspan_use_graphic" filter format setting.
   */
  public function testUseGraphicFilterSetting() {
    $session = $this->assertSession();
    // Change the filter format settings:
    $filters = $this->spamSpanFilterFormat->get('filters');
    $filters['filter_spamspan']['settings'] = [
      'spamspan_use_graphic' => TRUE,
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

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->statusCodeEquals(200);
    $session->pageTextContains('test123');
    $session->elementExists('css', 'span.spamspan');
    $session->elementTextEquals('css', 'span.spamspan > span.u', 'example');
    $session->elementExists('css', 'span.spamspan > img.spamspan-image');
    $session->elementExists('css', 'span.spamspan > img.spamspan-image[src*="image.gif"]');
    $session->elementTextEquals('css', 'span.spamspan > span.d', 'email.com');
    $session->elementTextEquals('css', 'span.spamspan > span.t', '(Test)');
  }

  /**
   * Tests the "spamspan_dot" filter format setting.
   */
  public function testDotFilterSetting() {
    $session = $this->assertSession();
    // Change the filter format settings:
    $filters = $this->spamSpanFilterFormat->get('filters');
    $filters['filter_spamspan']['settings'] = [
      'spamspan_dot_enable' => TRUE,
      'spamspan_dot' => ' {test-dot} ',
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

    // Go to the node, and see if the mail link is displayed correctly:
    $this->drupalGet('/node/' . $node->id());
    $session->statusCodeEquals(200);
    $session->pageTextContains('test123');
    $session->elementExists('css', 'span.spamspan');
    $session->elementTextEquals('css', 'span.spamspan', 'example [at] email {test-dot} com (Test)');
    $session->elementTextEquals('css', 'span.spamspan > span.u', 'example');
    $session->elementTextEquals('css', 'span.spamspan > span.d', 'email {test-dot} com');
    $session->elementTextEquals('css', 'span.spamspan > span.t', '(Test)');
  }

}
