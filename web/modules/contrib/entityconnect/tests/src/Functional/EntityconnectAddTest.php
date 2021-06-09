<?php

namespace Drupal\Tests\entityconnect\Functional;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Tests the Entityconnect add entity function.
 *
 * @group entityconnect
 */
class EntityconnectAddTest extends EntityconnectTestBase {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Grant the add button permission for the ecUser.
    $this->ecUser->addRole('ec_add');
    $this->ecUser->save();

    // Enable add button and disable edit button.
    $this->setEcButtons(TRUE, FALSE);

    $this->drupalLogin($this->ecUser);
  }

  /**
   * Entityconnect add button test.
   */
  public function testAddButton() {
    // Open the create test page.
    $this->drupalGet('node/add/' . $this->testContentType->id());
    $this->assertSession()->elementExists('xpath', '//div[contains(@class, \'entityconnect-add\')]/input');
    $this->assertSession()->elementNotExists('xpath', '//div[contains(@class, \'entityconnect-edit\')]/input');

    // Fill in the title.
    $base_page = $this->getSession()->getPage();
    $base_page->fillField('title[0][value]', 'Base ' . $this->testContentType->label());

    // Click the add button.
    $base_page->findButton('New content')->click();
    $this->assertSession()->responseContains('Create ' . $this->testContentType->label());
    $this->assertSession()->fieldValueEquals('title[0][value]', '');

    // Test Cancel.
    $this->drupalPostForm(NULL, [], 'Cancel');
    $this->assertSession()->fieldValueEquals('title[0][value]', 'Base ' . $this->testContentType->label());

    // Test Create reference Node.
    $base_page = $this->getSession()->getPage();
    $base_page->findButton('New content')->click();
    $ref_page = $this->getSession()->getPage();
    $ref_page->fillField('title[0][value]', 'Referenced ' . $this->testContentType->label());
    $ref_page->findButton('Save')->click();
    $this->assertSession()->fieldValueEquals('title[0][value]', 'Base ' . $this->testContentType->label());

    // Finish creating the base Node.
    $base_page = $this->getSession()->getPage();
    $base_page->findButton('Save')->click();
    // Base node should contain the referenced node.
    $this->assertSession()->pageTextContains('Base ' . $this->testContentType->label());
    $this->assertSession()->pageTextContains('Referenced ' . $this->testContentType->label());
  }

  /**
   * Entityconnect add with multiple types test.
   */
  public function testAddWithMultipleTypes() {
    // Create another content type.
    $extra_type = $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);
    // Add it to the entity reference field as a target.
    $this->updateEntityReferenceFieldTargets([$extra_type->id()]);

    // Test select page not shown if user only has access to one target type.
    $this->drupalGet('node/add/' . $this->testContentType->id());
    $base_page = $this->getSession()->getPage();
    $base_page->findButton('New content')->click();
    // No select page, just go right to create ec test page.
    $this->assertSession()->responseContains('Create ' . $this->testContentType->label());

    // Grant ec user permission to create the extra type.
    $this->drupalCreateRole(["create {$extra_type->id()} content"], "ec_add_{$extra_type->id()}");
    $this->ecUser->addRole("ec_add_{$extra_type->id()}");
    $this->ecUser->save();

    // Now test the page to select node type.
    $this->drupalGet('node/add/' . $this->testContentType->id());
    $base_page = $this->getSession()->getPage();
    $base_page->findButton('New content')->click();
    $this->assertSession()->pageTextContains('Choose type to create and add');
    $this->assertSession()->linkExists($this->testContentType->label());
    $this->assertSession()->linkExists($extra_type->label());

    // Test Extra type selection.
    $sel_page = $this->getSession()->getPage();
    $sel_page->clickLink($extra_type->label());
    $this->assertSession()->pageTextContains('Create ' . $extra_type->label());
  }

}
