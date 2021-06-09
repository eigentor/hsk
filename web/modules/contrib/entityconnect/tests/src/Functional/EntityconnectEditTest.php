<?php

namespace Drupal\Tests\entityconnect\Functional;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Tests the Entityconnect add entity function.
 *
 * @group entityconnect
 */
class EntityconnectEditTest extends EntityconnectTestBase {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Grant the edit button permission for the ecUser.
    $this->ecUser->addRole('ec_edit');
    $this->ecUser->save();

    // Enable edit button and disable add button.
    $this->setEcButtons(FALSE, TRUE);

    $this->drupalLogin($this->ecUser);
  }

  /**
   * Entityconnect edit button test.
   */
  public function testEditButton() {
    // Create a node for referencing,.
    $this->drupalCreateNode([
      'type' => $this->testContentType->id(),
      'title' => 'Referenced Node',
      'uid' => $this->ecUser->id(),
    ]);

    // Open the create test page.
    $this->drupalGet('node/add/' . $this->testContentType->id());
    $this->assertSession()->buttonExists('Edit content');
    $this->assertSession()->buttonNotExists('New content');

    // Fill in the title.
    $base_page = $this->getSession()->getPage();
    $base_page->fillField('title[0][value]', 'Base ' . $this->testContentType->label());

    // Fill in the referenced entity.
    $ref_field = $this->getSession()->getPage()->findField($this->testRefField->getName());
    $ref_field->selectOption('Referenced Node');

    // Test Edit reference Node.
    $base_page->findButton('Edit content')->click();
    $ref_page = $this->getSession()->getPage();
    $this->assertSession()->fieldValueEquals('title[0][value]', 'Referenced Node');
    $ref_page->fillField('body[0][value]', 'Some text.');
    $ref_page->findButton('Save')->click();

    // Finish creating the base Node.
    $base_page = $this->getSession()->getPage();
    $base_page->findButton('Save')->click();
    // Base node should contain the referenced node.
    $this->assertSession()->pageTextContains('Referenced Node');

    // Check the changes made to the referenced node.
    $base_page->clickLink('Referenced Node');
    $this->assertSession()->pageTextContains('Some text.');
  }

}
