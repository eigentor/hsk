<?php

namespace Drupal\Tests\spamspan\Kernel;

use Drupal\filter\FilterPluginCollection;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests spamspan filter.
 *
 * @group spamspan
 */
class FilterSpamspanUnitTest extends KernelTestBase {

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
   * Use DOM flag.
   *
   * @var int
   */
  protected $withDom = 0;

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
    $configuration['settings'] = ['spamspan_parse_dom' => $this->withDom] + $configuration['settings'];
    $this->spamspanFilter->setConfiguration($configuration);

    // Spamspan filter that is set to use contact form.
    $configuration['settings'] = ['spamspan_use_form' => 1] + $configuration['settings'];
    $this->spamspanFilterForm = $manager->createInstance('filter_spamspan', $configuration);

    // Spamspan filter that is set to use graphic at and dot enabled.
    $configuration['settings'] =
      [
        'spamspan_use_form' => 0,
        'spamspan_use_graphic' => 1,
        'spamspan_dot_enable' => 1,
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
      $this->assertSame($output, $prefix . $shouldbe . $suffix);
    }
    else {
      $this->assertSame($output, $prefix . $shouldbe . $suffix, $message);
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
    $this->wrappedAssert($filter, $input, $shouldbe, '<p>', '</p>');
    // Test for email with trailing commas.
    $this->wrappedAssert($filter, $input, $shouldbe, 'some text at the start ', ', next clause.');
    // Test for email with trailing full stop.
    $this->wrappedAssert($filter, $input, $shouldbe, 'some text at the start ', '. next sentence.');
    // Test for email with preceding tag, and no closing tag.
    $this->wrappedAssert($filter, $input, $this->withDom ? $shouldbe . '</dt>' : $shouldbe, '<dt>');
    // Test for brackets.
    $this->wrappedAssert($filter, $input, $shouldbe, '(', ')');
    // Test for newlines.
    $this->wrappedAssert($filter, $input, $shouldbe, PHP_EOL, PHP_EOL);
    // Test for spaces.
    $this->wrappedAssert($filter, $input, $shouldbe, ' ', ' ');
    // Test base64image.
    $this->wrappedAssert($filter, $input, $shouldbe, $this->base64Image, $this->base64Image);

    if (!$this->withDom) {
      // Test for angular brackets.
      $this->wrappedAssert($filter, $input, $shouldbe, '<', '>');
    }
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

    if ($this->withDom) {
      $noemails[] = '<a href="http://test.test/@user.me">contact</a>';
      $noemails[] = '<drupal-entity data-settings="mailto: user@example.com"></drupal-entity>';
    }

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

      '!#$%&\'*+-/=?^_`{|}~.@example.com' => $this->withDom
      ? '<span class="spamspan"><span class="u">!#$%&amp;\'*+-/=?^_`{|}~.</span> [at] <span class="d">example.com</span></span>'
      : '<span class="spamspan"><span class="u">!#$%&\'*+-/=?^_`{|}~.</span> [at] <span class="d">example.com</span></span>',

      '<a href="mailto:email@example.com"></a>' =>
      '<span class="spamspan"><span class="u">email</span> [at] <span class="d">example.com</span></span>',

      '<a href=" mailto:email@example.com ">email@example.com</a>' =>
      '<span class="spamspan"><span class="u">email</span> [at] <span class="d">example.com</span></span>',

      '<a href="mailto:email@example.com"><img src="/core/misc/favicon.ico"></a>' =>
      '<span class="spamspan"><span class="u">email</span> [at] <span class="d">example.com</span><span class="t"> (<img src="/core/misc/favicon.ico">)</span></span>',

      '<a href="mailto:email@example.com?subject=Hi there!&body=Dear Sir">some text</a>' =>
      '<span class="spamspan"><span class="u">email</span> [at] <span class="d">example.com</span><span class="h"> (subject: Hi%20there%21, body: Dear%20Sir) </span><span class="t"> (some text)</span></span>',

      '<a href="mailto:email@example.com">The email@example.com should not show and neither email2@example.me</a>' =>
      '<span class="spamspan"><span class="u">email</span> [at] <span class="d">example.com</span><span class="t"> (The  should not show and neither )</span></span>',

      '<a class="someclass" data-before="before" href="mailto:email@example.com" id="someid" data-after="after"></a>' =>
      '<span class="spamspan"><span class="u">email</span> [at] <span class="d">example.com</span><span class="e">class="someclass" data-before="before" id="someid" data-after="after"</span></span>',

      '<a href="mailto:email@example.com?subject=Message%20Subject%2C%20nasty%20%22%20%3Cchars%3F%3E&body=%22This%20is%20a%20message%20body%21%20%3C%20%3E%20%22%3F%0A%0A%21%22%C2%A3%24%25%5E%26%2A%28%29%3A%40~%3B%23%3C%3E%3F%2C.%2F%20%5B%5D%20%7B%7D%20-%3D%20_%2B">some text</a>' =>
      '<span class="spamspan"><span class="u">email</span> [at] <span class="d">example.com</span><span class="h"> (subject: Message%20Subject%2C%20nasty%20%22%20%3Cchars%3F%3E, body: %22This%20is%20a%20message%20body%21%20%3C%20%3E%20%22%3F%0A%0A%21%22%C2%A3%24%25%5E%26%2A%28%29%3A%40~%3B%23%3C%3E%3F%2C.%2F%20%5B%5D%20%7B%7D%20-%3D%20_%2B) </span><span class="t"> (some text)</span></span>',

      '<a href="mailto:email@example.com?subject=Hi there!&body=Dear\'Sir">some text</a>' =>
      '<span class="spamspan"><span class="u">email</span> [at] <span class="d">example.com</span><span class="h"> (subject: Hi%20there%21, body: Dear%27Sir) </span><span class="t"> (some text)</span></span>',

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
      'user@example.com' => $this->withDom
      ? '<span class="spamspan"><span class="u">user</span><img class="spamspan-image" alt="at" src="' . base_path() . \Drupal::service('extension.list.module')->getPath('spamspan') . '/image.gif"><span class="d">example<span class="o"> [dot] </span>com</span></span>'
      : '<span class="spamspan"><span class="u">user</span><img class="spamspan-image" alt="at" src="' . base_path() . \Drupal::service('extension.list.module')->getPath('spamspan') . '/image.gif" /><span class="d">example<span class="o"> [dot] </span>com</span></span>',
    ];

    foreach ($emails as $input => $shouldbe) {
      $this->variatedAssert($this->spamspanFilterAtDot, $input, $shouldbe);
    }

    // Test the spamspan.js being attached.
    $attached_library = [
      'library' => [
        'spamspan/obfuscate',
      ],
    ];
    $output = $this->spamspanFilter->process('email@example.com', 'und');
    $this->assertSame($attached_library, $output->getAttachments());
  }

}
