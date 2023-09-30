<?php

namespace Drupal\Tests\spamspan\Kernel;

use Drupal\filter\FilterPluginCollection;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests spamspan filter.
 *
 * @group spamspan
 */
class FilterSpamspanKernelTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['system', 'filter', 'spamspan'];

  /**
   * Default spamspan filter.
   *
   * @var \Drupal\spamspan\Plugin\Filter\FilterSpamspan
   */
  protected $spamspanFilter;

  /**
   * Default spamspan filter with spamspan_use_form = 1.
   *
   * @var \Drupal\spamspan\Plugin\Filter\FilterSpamspan
   */
  protected $spamspanFilterForm;

  /**
   * Default spamspan filter with custom at and dot.
   *
   * @var \Drupal\spamspan\Plugin\Filter\FilterSpamspan
   */
  protected $spamspanFilterAtDot;

  /**
   * Test inline image.
   *
   * @var string
   */
  protected $base64Image;

  /**
   * {@inheritDoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig(['system']);

    $manager = $this->container->get('plugin.manager.filter');
    $bag = new FilterPluginCollection($manager, []);
    $filters = $bag->getAll();
    $this->spamspanFilter = $filters['filter_spamspan'];
    $configuration = $this->spamspanFilter->getConfiguration();
    $this->spamspanFilter->setConfiguration($configuration);

    // Spamspan filter that is set to use contact form.
    $configuration['settings'] = ['spamspan_use_form' => 1] + $configuration['settings'];
    $this->spamspanFilterForm = $manager->createInstance('filter_spamspan', $configuration);

    // Spamspan filter that is set to use graphic at and dot enabled.
    $configuration['settings'] =
      [
        'spamspan_use_form' => FALSE,
        'spamspan_use_graphic' => TRUE,
        'spamspan_dot_enable' => TRUE,
      ] + $configuration['settings'];
    $this->spamspanFilterAtDot = $manager->createInstance('filter_spamspan', $configuration);

    // Read the test image from the file provided.
    $this->base64Image = file_get_contents(\Drupal::service('extension.list.module')->getPath('spamspan') . '/tests/src/Kernel/base64image.txt');

  }

  /**
   * Wrapper function.
   *
   * Conveniently wraps some text around main test subject and then asserts.
   */
  protected function wrappedAssert($filter, $input, $shouldbe, $prefix = '', $suffix = '', $message = '') {
    $output = $filter->process($prefix . $input . $suffix, 'und')->getProcessedText();

    if (empty($message)) {
      $this->assertSame($prefix . $shouldbe . $suffix, $output);
    }
    else {
      $this->assertSame($prefix . $shouldbe . $suffix, $output, $message);
    }
  }

  /**
   * Helper function to assert variations.
   */
  protected function variatedAssert($filter, $input, $shouldbe) {
    // Test for bare email;.
    $this->wrappedAssert($filter, $input, $shouldbe);
    // Test for email with text at the start.
    $this->wrappedAssert($filter, $input, $shouldbe, 'some text at the start ');
    // Test for email with text at the end.
    $this->wrappedAssert($filter, $input, $shouldbe, '', ' some text at the end');
    // Test for email with text at the start and end.
    $this->wrappedAssert($filter, $input, $shouldbe, 'some text at the start ', ' some text at the end');
    // Test for email with tags at the start and end.
    if ($input) {
      $this->wrappedAssert($filter, $input, $shouldbe, '<p>', '</p>');
    }
    // Test for email with trailing commas.
    $this->wrappedAssert($filter, $input, $shouldbe, 'some text at the start ', ', next clause.');
    // Test for email with trailing full stop.
    $this->wrappedAssert($filter, $input, $shouldbe, 'some text at the start ', '. next sentence.');
    // Test for email with preceding tag, and no closing tag.
    if ($input) {
      $this->wrappedAssert($filter, $input, $shouldbe, '<dt>', '</dt>');
    }
    // Test for brackets.
    $this->wrappedAssert($filter, $input, $shouldbe, '(', ')');
    // Test for newlines.
    $this->wrappedAssert($filter, $input, $shouldbe, PHP_EOL, PHP_EOL);
    // Test for spaces.
    $this->wrappedAssert($filter, $input, $shouldbe, ' ', ' ');
    // Test base64image.
    $this->wrappedAssert($filter, $input, $shouldbe, $this->base64Image, $this->base64Image);
  }

  /**
   * Tests the align filter.
   */
  public function testSpamSpanFilter() {
    // Test that strings without emails a passed unchanged.
    $noemails = [
      'no email here',
      'oneword',
      '',
      'notan@email',
      'notan@email either',
      'some text and notan.email@something here',
    ];

    $noemails[] = '<a href="http://test.test/@user.me">contact</a>';
    $noemails[] = '<drupal-entity data-settings="mailto: user@example.com"/>';

    foreach ($noemails as $input) {
      $this->variatedAssert($this->spamspanFilter, $input, $input);
    }

    // A list of addresses, together with what they should look like.
    $emails = [
      'user@example.com' =>
      '<span class="spamspan"><span class="u">user</span> [at] <span class="d">example.com</span></span>',

      'user@example.co.uk' =>
      '<span class="spamspan"><span class="u">user</span> [at] <span class="d">example.co.uk</span></span>',

      'user@example.somenewlongtld' =>
      '<span class="spamspan"><span class="u">user</span> [at] <span class="d">example.somenewlongtld</span></span>',

      'user.user@example.com' =>
      '<span class="spamspan"><span class="u">user.user</span> [at] <span class="d">example.com</span></span>',

      'user\'user@example.com' =>
      '<span class="spamspan"><span class="u">user\'user</span> [at] <span class="d">example.com</span></span>',

      'user-user@example.com' =>
      '<span class="spamspan"><span class="u">user-user</span> [at] <span class="d">example.com</span></span>',

      'user_user@example.com' =>
      '<span class="spamspan"><span class="u">user_user</span> [at] <span class="d">example.com</span></span>',

      'user+user@example.com' =>
      '<span class="spamspan"><span class="u">user+user</span> [at] <span class="d">example.com</span></span>',

      '!#$%&\'*+-/=?^_`{|}~.@example.com' => '<span class="spamspan"><span class="u">!#$%&amp;\'*+-/=?^_`{|}~.</span> [at] <span class="d">example.com</span></span>',

      '<a href="mailto:email@example.com"></a>' =>
      '<span class="spamspan"><span class="u">email</span> [at] <span class="d">example.com</span></span>',

      '<a href=" mailto:email@example.com ">email@example.com</a>' =>
      '<span class="spamspan"><span class="u">email</span> [at] <span class="d">example.com</span><span class="t"> (email[at]example[dot]com)</span></span>',

      '<a href="mailto:email@example.com"><img src="/core/misc/favicon.ico"></a>' =>
      '<span class="spamspan"><span class="u">email</span> [at] <span class="d">example.com</span><span class="t"> (<img src="/core/misc/favicon.ico">)</span></span>',

      '<a href="mailto:email@example.com?subject=Hi there!&body=Dear Sir">some text</a>' =>
      '<span class="spamspan"><span class="u">email</span> [at] <span class="d">example.com</span><span class="h"> (subject: Hi%20there%21, body: Dear%20Sir) </span><span class="t"> (some text)</span></span>',

      '<a href="mailto:email@example.com">The email@example.com should show and email2@example.me</a>' =>
      '<span class="spamspan"><span class="u">email</span> [at] <span class="d">example.com</span><span class="t"> (The email[at]example[dot]com should show and email2[at]example[dot]me)</span></span>',

      '<a href="mailto:email@example.com">The email@example.com should show</a> and <a href="mailto:email2@example.com">email2@example.me</a>' =>
      '<span class="spamspan"><span class="u">email</span> [at] <span class="d">example.com</span><span class="t"> (The email[at]example[dot]com should show)</span></span> and <span class="spamspan"><span class="u">email2</span> [at] <span class="d">example.com</span><span class="t"> (email2[at]example[dot]me)</span></span>',

      '<a class="someclass" data-before="before" href="mailto:email@example.com" id="someid" data-after="after"></a>' =>
      '<span class="spamspan" data-spamspan-class="someclass" data-spamspan-data-before="before" data-spamspan-id="someid" data-spamspan-data-after="after"><span class="u">email</span> [at] <span class="d">example.com</span></span>',

      '<a href="mailto:email@example.com?subject=Message%20Subject%2C%20nasty%20%22%20%3Cchars%3F%3E&body=%22This%20is%20a%20message%20body%21%20%3C%20%3E%20%22%3F%0A%0A%21%22%C2%A3%24%25%5E%26%2A%28%29%3A%40~%3B%23%3C%3E%3F%2C.%2F%20%5B%5D%20%7B%7D%20-%3D%20_%2B">some text</a>' =>
      '<span class="spamspan"><span class="u">email</span> [at] <span class="d">example.com</span><span class="h"> (subject: Message%20Subject%2C%20nasty%20%22%20%3Cchars%3F%3E, body: %22This%20is%20a%20message%20body%21%20%3C%20%3E%20%22%3F%0A%0A%21%22%C2%A3%24%25%5E%26%2A%28%29%3A%40~%3B%23%3C%3E%3F%2C.%2F%20%5B%5D%20%7B%7D%20-%3D%20_%2B) </span><span class="t"> (some text)</span></span>',

      '<a href="mailto:email@example.com?subject=Hi there!&body=Dear\'Sir">some text</a>' =>
      '<span class="spamspan"><span class="u">email</span> [at] <span class="d">example.com</span><span class="h"> (subject: Hi%20there%21, body: Dear%27Sir) </span><span class="t"> (some text)</span></span>',

      '<a href="[media/file/1]">[media/file/1]</a>' => '<a href="[media/file/1]">[media/file/1]</a>',

      '<a class="test1 test2 test3 test4" href="mailto:example@mail.com"></a>' =>
      '<span class="spamspan" data-spamspan-class="test1 test2 test3 test4"><span class="u">example</span> [at] <span class="d">mail.com</span></span>',

      '<a id="test-anchor" class="test1 test2 test3 test4" rel="noopener noreferrer" data-test="test-data more-data" href="mailto:example@mail.com"></a>' =>
      '<span class="spamspan" data-spamspan-id="test-anchor" data-spamspan-class="test1 test2 test3 test4" data-spamspan-rel="noopener noreferrer" data-spamspan-data-test="test-data more-data"><span class="u">example</span> [at] <span class="d">mail.com</span></span>',

      '<a href="mailto:example@mail.com" id="test-anchor" class="test1 test2 test3 test4" rel="noopener noreferrer" data-test="test-data more-data" ></a>' =>
      '<span class="spamspan" data-spamspan-id="test-anchor" data-spamspan-class="test1 test2 test3 test4" data-spamspan-rel="noopener noreferrer" data-spamspan-data-test="test-data more-data"><span class="u">example</span> [at] <span class="d">mail.com</span></span>',

      '<a id="test-anchor" class="test1 test2 test3 test4" data-before="before-data more-data" href="mailto:example@mail.com" rel="noopener noreferrer" data-after="before-data even-more-data" ></a>' =>
      '<span class="spamspan" data-spamspan-id="test-anchor" data-spamspan-class="test1 test2 test3 test4" data-spamspan-data-before="before-data more-data" data-spamspan-rel="noopener noreferrer" data-spamspan-data-after="before-data even-more-data"><span class="u">example</span> [at] <span class="d">mail.com</span></span>',

    ];

    foreach ($emails as $input => $shouldbe) {
      $this->variatedAssert($this->spamspanFilter, $input, $shouldbe);
    }

    $basepath = base_path();

    // Use form tests.
    $emails = [
      'user@example.com' =>
      '<a href="' . $basepath . 'contact?goto=dXNlckBleGFtcGxlLmNvbQ%3D%3D">contact form</a>',

      '<a href="mailto:user@example.com">tag contents will be replaced</a>' => '<a href="' . $basepath . 'contact?goto=dXNlckBleGFtcGxlLmNvbQ%3D%3D">contact form</a>',

      'user@example.co.uk[mycontactform]' =>
      '<a href="' . $basepath . 'mycontactform?goto=dXNlckBleGFtcGxlLmNvLnVr">contact form</a>',

      'user@example.com[http://google.com]' =>
      '<a href="http://google.com?goto=dXNlckBleGFtcGxlLmNvbQ%3D%3D">contact form</a>',

      'user@example.museum[mycontactform|Contact me using this form]' =>
      '<a href="' . $basepath . 'mycontactform?goto=dXNlckBleGFtcGxlLm11c2V1bQ%3D%3D">Contact me using this form</a>',
    ];

    foreach ($emails as $input => $shouldbe) {
      $this->variatedAssert($this->spamspanFilterForm, $input, $shouldbe);
    }

    // Graphical at and [dot].
    $emails = [
      'user@example.com' => '<span class="spamspan"><span class="u">user</span><img class="spamspan-image" alt="at" src="' . base_path() . \Drupal::service('extension.list.module')->getPath('spamspan') . '/image.gif"><span class="d">example<span class="o"> [dot] </span>com</span></span>',
    ];

    foreach ($emails as $input => $shouldbe) {
      $this->variatedAssert($this->spamspanFilterAtDot, $input, $shouldbe);
    }

    // Test the spamspan.js is attached.
    $attached_library = [
      'library' => [
        'spamspan/obfuscate',
      ],
    ];
    $output = $this->spamspanFilter->process('email@example.com', 'und');
    $this->assertSame($attached_library, $output->getAttachments());
  }

}
