<?php

namespace Drupal\Tests\spamspan\FunctionalJavascript;

use Drupal\filter\Entity\FilterFormat;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests the spamspan javascript functionalities.
 *
 * @group spamspan
 */
abstract class SpamSpanJsTestBase extends WebDriverTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'node',
    'filter',
    'test_page_test',
    'spamspan',
  ];

  /**
   * A user with admin permissions.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $adminUser;

  /**
   * A user with authenticated permissions.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $user;

  /**
   * The spamspan filter format.
   *
   * @var \Drupal\filter\Entity\FilterFormat
   */
  protected $spamSpanFilterFormat;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

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

}
