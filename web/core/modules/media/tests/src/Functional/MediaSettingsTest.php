<?php

namespace Drupal\Tests\media\Functional;

/**
 * Testing the media settings.
 *
 * @group media
 */
class MediaSettingsTest extends MediaFunctionalTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupalLogin($this->createUser(['administer site configuration']));
  }

  /**
   * Tests that media warning appears if oEmbed media types exists.
   */
  public function testStatusPage() {
    $assert_session = $this->assertSession();

    $this->drupalGet('admin/reports/status');
    $assert_session->pageTextNotContains('It is potentially insecure to display oEmbed content in a frame');

    $this->createMediaType('oembed:video');

    $this->drupalGet('admin/reports/status');
    $assert_session->pageTextContains('It is potentially insecure to display oEmbed content in a frame');
  }

}
