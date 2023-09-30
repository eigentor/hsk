<?php

namespace Drupal\metatag\Unit;

use Drupal\spamspan\SpamspanTrait;
use Drupal\Tests\UnitTestCase;

/**
 * This class provides methods for testing the filter methods of Spamspan.
 *
 * @group metatag
 */
class SpamSpanFilterUnitTest extends UnitTestCase {

  /**
   * A test class using the "SpamspanTrait".
   *
   * @var object
   */
  protected $spamSpanTraitObject;

  /**
   * {@inheritDoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // We need an anonymous class here, to not apply the "SpamspanTrait"
    // on the test class itself:
    $this->spamSpanTraitObject = new class() {
      use SpamspanTrait;

      /**
       * The filter settings array, so the trait methods work as expected.
       *
       * @var array
       */
      protected $settings = [
        'spamspan_at' => ' [at] ',
        'spamspan_use_graphic' => FALSE,
        'spamspan_dot_enable' => FALSE,
        'spamspan_dot' => ' [dot] ',
        'spamspan_use_form' => FALSE,
        'spamspan_form_pattern' => '<a href="%url?goto=%email">%displaytext</a>',
        'spamspan_form_default_url' => 'contact',
        'spamspan_form_default_displaytext' => 'contact form',
      ];
    };

  }

  /**
   * Tests the "mailto" obscure method.
   *
   * @dataProvider providerTestMailToMethod
   */
  public function testMailToMethod($element, $expected_result) {
    $processed = $this->spamSpanTraitObject->replaceMailtoLinks($element);
    $this->assertEquals($expected_result, $processed);
  }

  /**
   * A data provider for ::testMailToMethod().
   *
   * @return \Iterator
   *   An iterator.
   */
  public function providerTestMailToMethod(): \Iterator {
    yield 'simpleMailtoTest' => [
      '<a href="mailto:example@email.com">Test</a>',
      '<span class="spamspan"><span class="u">example</span> [at] <span class="d">email.com</span><span class="t"> (Test)</span></span>',
    ];
  }

  /**
   * Tests the "replaceBareEmailAddresses" obscure method.
   *
   * @dataProvider providerTestReplaceBareEmailAddressesMethod
   */
  public function testReplaceBareEmailAddressesMethod($element, $expected_result) {
    $processed = $this->spamSpanTraitObject->replaceBareEmailAddresses($element);
    $this->assertEquals($expected_result, $processed);
  }

  /**
   * A data provider for ::testReplaceBareEmailAddressesMethod().
   *
   * @return \Iterator
   *   An iterator.
   */
  public function providerTestReplaceBareEmailAddressesMethod(): \Iterator {
    yield 'simpleReplaceBareEmailAddressesTest' => [
      'example@email.com',
      '<span class="spamspan"><span class="u">example</span> [at] <span class="d">email.com</span></span>',
    ];
  }

  /**
   * Tests the "mailto" obscure method.
   */
  public function testLinkMailToObscureMethodIsIdempotent() {
    // Create the link html text to test:
    $aMailTo = '<a href="mailto:example@email.com">Test</a>';
    // Run the mailto obscure method on itself a bunch of time, to see if we
    // still get the desired output:
    for ($i = 0; $i < 10; $i++) {
      $aMailTo = $this->spamSpanTraitObject->replaceMailtoLinks($aMailTo);
    }
    // See if the obscure pattern looks as expected:
    $this->assertSame(
      '<span class="spamspan"><span class="u">example</span> [at] <span class="d">email.com</span><span class="t"> (Test)</span></span>',
      $aMailTo
    );
  }

  /**
   * Tests the a "mailto" obscure method.
   */
  public function testObscureBareEmailAddressMethodIsIdempotent() {
    // Create the link html text to test:
    $mail = 'example@email.com';
    // Run the mailto obscure method on itself a bunch of time, to see if we
    // still get the desired output:
    for ($i = 0; $i < 10; $i++) {
      $mail = $this->spamSpanTraitObject->replaceBareEmailAddresses($mail);
    }
    // See if the obscure pattern looks as expected:
    $this->assertSame(
      '<span class="spamspan"><span class="u">example</span> [at] <span class="d">email.com</span></span>',
      $mail
    );
  }

  /**
   * Tests the email address with options obscure method.
   *
   * @todo What exactly does "replaceEmailAddressesWithOptions" do?
   */
  public function todoTestObscureEmailAddressWithOptionsMethodSimple() {
    // Create the link html text to test:
    $mail = 'example@email.com[mycontact|Contact me]';
    // Obscure the link element:
    $obscuredMail = $this->spamSpanTraitObject->replaceEmailAddressesWithOptions($mail);
    // See if the obscure pattern looks as expected:
    $this->assertSame(
      '<span class="spamspan"><span class="u">example</span> [at] <span class="d">email.com</span></span>',
      $obscuredMail
    );
  }

}
