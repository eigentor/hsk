<?php

namespace Drupal\Tests\entityconnect\Functional;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;

/**
 * Tests the Entityconnect administration.
 *
 * @group entityconnect
 */
class EntityconnectAdminTest extends EntityconnectTestBase {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // EC user needs both EC roles.
    $this->ecUser->addRole('ec_add');
    $this->ecUser->addRole('ec_edit');
    $this->ecUser->save();
  }

  /**
   * Entityconnect administration access test.
   */
  public function testAdminAccess() {

    // Login as the admin user.
    $this->drupalLogin($this->adminUser);

    // Load admin page.
    $this->drupalGet('admin/config/content/entityconnect');
    $this->assertSession()->statusCodeEquals(200);

    // Logout admin user.
    $this->drupalLogout();

    $anyUser = $this->drupalCreateUser([
      'access administration pages',
    ]);

    // Login as any user.
    $this->drupalLogin($anyUser);

    // Attempt to load admin page.
    $this->drupalGet('admin/config/content/entityconnect');
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Entityconnect buttons admin test.
   */
  public function testButtonsAdmin() {

    $this->drupalLogin($this->ecUser);

    // Open the create test page.
    $this->drupalGet(Url::fromRoute('node.add', ['node_type' => $this->testContentType->id()]));
    // By default, the entityconnect buttons should not exist.
    $this->assertSession()->elementNotExists('xpath', '//div[contains(@class, \'entityconnect-\')]/input');
    $this->drupalLogout();

    // Edit the entity reference field.
    $this->drupalLogin($this->adminUser);
    $this->drupalGet("admin/structure/types/manage/{$this->testContentType->id()}/fields/node.{$this->testContentType->id()}.{$this->testRefField->getName()}");
    // Check that the entity connect fields appear in the field edit form.
    $this->assertSession()->pageTextContains('EntityConnect default Parameters');

    // Enable the add and edit buttons.
    $edit = [
      'third_party_settings[entityconnect][buttons][button_add]' => '0',
      'third_party_settings[entityconnect][buttons][button_edit]' => '0',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save settings');
    $this->drupalLogout();

    // Open the create test page.
    $this->drupalLogin($this->ecUser);
    $this->drupalGet('node/add/' . $this->testContentType->id());
    // Check that the entity connect buttons appear.
    $this->assertSession()->elementExists('xpath', '//div[contains(@class, \'entityconnect-\')]/input');
  }

}
