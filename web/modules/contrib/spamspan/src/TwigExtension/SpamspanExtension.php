<?php

namespace Drupal\spamspan\TwigExtension;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Render\RendererInterface;
use Drupal\spamspan\SpamspanInterface;
use Drupal\spamspan\SpamspanService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Provides the SpamSpan filter function within Twig templates.
 */
class SpamspanExtension extends AbstractExtension {

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The Spamspan Service.
   *
   * @var \Drupal\spamspan\SpamspanService
   */
  protected $spamspan;

  /**
   * Constructor of SpamSpanExtension.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\spamspan\SpamspanService $spamspan
   *   The Spamspan Service.
   */
  public function __construct(RendererInterface $renderer, SpamspanService $spamspan) {
    $this->renderer = $renderer;
    $this->spamspan = $spamspan;
  }

  /**
   * {@inheritdoc}
   */
  public function getFilters() {
    return [
      new TwigFilter('spamspan', [$this, 'spamspanFilter'], ['is_safe' => ['html']]),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'spamspan.twig_extension';
  }

  /**
   * Applying spamspan filter to the given string.
   *
   * @param string $string
   *   Text, maybe containing email addresses.
   *
   * @return string
   *   The input text with emails replaced by spans
   */
  public function spamspanFilter($string) {
    $template_attached = [
      '#attached' => [
        'library' => [
          'spamspan/obfuscate',
        ],
      ],
    ];
    $this->renderer->render($template_attached);
    return Xss::filter($this->spamspan->spamspan($string), SpamspanInterface::ALLOWED_HTML);
  }

}
