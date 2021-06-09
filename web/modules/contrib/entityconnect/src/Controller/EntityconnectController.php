<?php

namespace Drupal\entityconnect\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\Link;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\entityconnect\EntityconnectCache;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Returns responses for Entityconnect module routes.
 */
class EntityconnectController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Temporary session storage for entityconnect.
   *
   * @var \Drupal\entityconnect\EntityconnectCache
   */
  protected $entityconnectCache;

  /**
   * Drupal renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Drupal\Core\Messenger\MessengerInterface definition.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a new EntityconnectController.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Renderer object.
   * @param \Drupal\entityconnect\EntityconnectCache $entityconnectCache
   *   Entityconnect Cache object.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger object.
   */
  public function __construct(RendererInterface $renderer, EntityconnectCache $entityconnectCache, MessengerInterface $messenger) {
    $this->renderer = $renderer;
    $this->entityconnectCache = $entityconnectCache;
    $this->messenger = $messenger;
  }

  /**
   * Uses Symfony's ContainerInterface to declare dependency for constructor.
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer'),
      $container->get('entityconnect.cache'),
      $container->get('messenger')
    );
  }

  /**
   * We redirect to the form page with the build_cache_id as a get param.
   *
   * @param string $cache_id
   *   Build cache id.
   * @param bool $cancel
   *   Whether or not the request was cancelled.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   The url of the parent page.
   */
  public function returnTo($cache_id, $cancel = FALSE) {
    $cache_data = $this->entityconnectCache->get($cache_id);
    $cache_data['cancel'] = $cancel;
    $this->entityconnectCache->set($cache_id, $cache_data);
    $css_id = 'edit-' . str_replace('_', '-', $cache_data['field']) . '-wrapper';
    $options = [
      'query' => [
        'build_cache_id' => $cache_id,
        'return' => TRUE,
      ],
      'fragment' => $css_id,
    ];
    // Collect additional request parameters, skip 'q', since this is
    // the destination.
    foreach ($cache_data['params'] as $key => $value) {
      if ('build_cache_id' == $key) {
        continue;
      }
      $options['query'][$key] = $value;
    }
    $options['absolute'] = TRUE;
    $url = Url::fromRoute($cache_data['dest_route_name'], $cache_data['dest_route_params'], $options);
    return new RedirectResponse($url->toString());
  }

  /**
   * Page callback: Redirect to edit form.
   *
   * @param string $cache_id
   *   The id of the parent form cache.
   *
   * @return array|RedirectResponse
   *   The page of the entity to edit or list of entities.
   */
  public function edit($cache_id) {
    $data = $this->entityconnectCache->get($cache_id);

    $entity_type = $data['target_entity_type'];
    $target_id = $this->fixTargetId($data['target_id']);

    $args = [$cache_id, $entity_type, $target_id];
    $edit_info = $this->moduleHandler()->invokeAll('entityconnect_edit_info', $args);

    // Merge in default values.
    foreach ($edit_info as $data) {
      $edit_info += [
        'content' => [
          'href' => '',
          'label' => '',
          'description' => '',
        ],
        'theme_callback' => 'entityconnect_entity_add_list',
      ];
    }

    $context = [
      'cache_id' => $cache_id,
      'entity_type' => $entity_type,
      'target_id' => $target_id,
    ];
    $this->moduleHandler()->alter('entityconnect_edit_info', $edit_info, $context);

    if (isset($edit_info)) {
      $content = $edit_info['content'];
      $theme = $edit_info['theme_callback'];

      if (count($content) == 1) {
        $item = array_pop($content);
        if (is_array($item['href'])) {
          $url = array_shift($item['href']);
        }
        else {
          $url = $item['href'];
        }

        if (!$url) {
          $this->returnWithMessage($this->t('Invalid url: %url', ['%url' => $url]), 'warning', $cache_id);
        }
        return new RedirectResponse($url);

      }

      return [
        '#theme' => $theme,
        '#items' => $content,
        '#cache_id' => $cache_id,
        '#cancel_link' => Link::createFromRoute($this->t('Cancel'), 'entityconnect.return', ['cache_id' => $cache_id, 'cancel' => TRUE]),
      ];

    }

    return $this->returnWithMessage($this->t('Nothing to edit.'), 'warning', $cache_id);

  }

  /**
   * Callback for creating the build array of entities to edit.
   *
   * @param string $cache_id
   *   The id of parent form cache.
   * @param string $entity_type
   *   The target entity type.
   * @param array|int $target_id
   *   The target id.
   *
   * @return array
   *   The edit build array.
   *
   * @throws \Exception
   */
  public static function editInfo($cache_id, $entity_type, $target_id) {

    if (empty($entity_type)) {
      throw new \Exception('Entity type can not be empty');
    }

    if (empty($target_id)) {
      throw new \Exception('Target_id can not be empty');
    }

    $content = [];

    $options = [
      'query' => ['build_cache_id' => $cache_id, 'child' => TRUE],
      'absolute' => TRUE,
    ];

    if (is_array($target_id)) {
      $info = \Drupal::entityTypeManager()->getStorage($entity_type)->loadMultiple($target_id);
      foreach ($info as $key => $value) {
        $content[$key] = [
          'label' => $value->label(),
          'href' => \Drupal::urlGenerator()->generateFromRoute('entity.' . $entity_type . '.edit_form', [$entity_type => $key], $options),
          'description' => '',
        ];
      }
    }
    else {
      $content[$entity_type]['href'] = \Drupal::urlGenerator()->generateFromRoute('entity.' . $entity_type . '.edit_form', [$entity_type => $target_id], $options);
    }

    return [
      'content' => $content,
    ];
  }

  /**
   * Add a new connecting entity.
   *
   * @param string $cache_id
   *   The id of the parent form cache.
   *
   * @return array|RedirectResponse
   *   The page of the entity to be added or a list of acceptable types.
   */
  public function add($cache_id) {
    $data = $this->entityconnectCache->get($cache_id);
    $entity_type = $data['target_entity_type'];
    $acceptable_types = $data['acceptable_types'];

    $args = [$cache_id, $entity_type, $acceptable_types];
    $add_info = $this->moduleHandler()->invokeAll('entityconnect_add_info', $args);

    // Merge in default values.
    foreach ($add_info as $data) {
      $add_info += [
        'content' => [
          'href' => '',
          'label' => '',
          'description' => '',
        ],
        'theme_callback' => 'entityconnect_entity_add_list',
      ];
    }

    $context = [
      'cache_id' => $cache_id,
      'entity_type' => $entity_type,
      'acceptable_tpes' => $acceptable_types,
    ];
    $this->moduleHandler()->alter('entityconnect_add_info', $add_info, $context);

    if (isset($add_info)) {
      $content = $add_info['content'];
      $theme = $add_info['theme_callback'];

      if (count($content) == 1) {
        $item = array_pop($content);
        if (is_array($item['href'])) {
          $url = array_shift($item['href']);
        }
        else {
          $url = $item['href'];
        }
        if (!$url) {
          $this->returnWithMessage($this->t('Invalid url: %url', ['%url' => $url]), 'warning', $cache_id);
        }
        return new RedirectResponse($url);
      }

      return [
        '#theme' => $theme,
        '#items' => $content,
        '#cache_id' => $cache_id,
        '#cancel_link' => Link::createFromRoute($this->t('Cancel'), 'entityconnect.return', ['cache_id' => $cache_id, 'cancel' => TRUE]),
      ];
    }

    return $this->returnWithMessage($this->t('Nothing to add.'), 'warning', $cache_id);

  }

  /**
   * Callback for creating the build array of entity types to add.
   *
   * @param string $cache_id
   *   The parent form cache id.
   * @param string $entity_type
   *   The target entity type.
   * @param array $acceptable_types
   *   An array of types that can be added via entityconnect.
   *
   * @return array
   *   The build array of entity types to add
   *
   * @throws \Exception
   */
  public static function addInfo($cache_id, $entity_type, array $acceptable_types) {
    if (empty($entity_type)) {
      throw new \Exception('Entity type can not be empty');
    }

    $content = [];

    $routes = static::getAddRoute($entity_type);

    $options = [
      'query' => ['build_cache_id' => $cache_id, 'child' => TRUE],
      'absolute' => TRUE,
    ];

    if (!empty($routes)) {
      $route_name = key($routes);
      /** @var \Symfony\Component\Routing\Route $route */
      $route = current($routes);
      // If no parameters just try to get the url from route name.
      if (empty($params = $route->getOption('parameters'))) {
        $content[$entity_type]['href'] = \Drupal::urlGenerator()->generateFromRoute($route_name, [], $options);;
      }
      // Otherwise, get the url from route name and parameters.
      else {
        // Should only be one parameter.
        $route_param_key = key($params);
        foreach ($acceptable_types as $acceptable_type) {
          $type = \Drupal::entityTypeManager()->getStorage($route_param_key)->load($acceptable_type);
          if ($type) {
            $route_params = [$route_param_key => $acceptable_type];
            $href = \Drupal::urlGenerator()->generateFromRoute($route_name, $route_params, $options);;
            $content[$type->id()] = [
              'href' => $href,
              'label' => $type->label(),
              'description' => method_exists($type, 'getDescription') ? $type->getDescription() : '',
            ];
          }
        }
      }
    }
    if (!empty($content)) {
      return [
        'content' => $content,
      ];
    }
    return [];
  }

  /**
   * Sets a message upon return to help with errors.
   *
   * @param string $msg
   *   The message to display.
   * @param string $status
   *   Message status.
   * @param string $cache_id
   *   Cache id of the parent.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   The parent page to go back to.
   */
  private function returnWithMessage($msg, $status, $cache_id) {
    $this->messenger->addStatus($msg, $status);
    return $this->redirect('entityconnect.return', ['cache_id' => $cache_id, 'cancel' => TRUE]);
  }

  /**
   * Makes sure our target id's are correct.
   *
   * @param array|int $target_id
   *   The target entity id.
   *
   * @return array|int
   *   The fixed target_id.
   */
  private function fixTargetId($target_id) {
    $array_target_id = is_array($target_id) ? $target_id : [$target_id];
    foreach ($array_target_id as $key => $value) {
      if (!is_numeric($value) && is_string($value)) {
        if ($value = EntityAutocomplete::extractEntityIdFromAutocompleteInput($value)) {
          $array_target_id[$key] = $value;
        }
      }
    }

    return count($array_target_id) == 1 ? $array_target_id[0] : $array_target_id;
  }

  /**
   * Returns the Symfony routes of the given entity's add form.
   *
   * @param string $entity_type
   *   The target entity type.
   *
   * @return array
   *   An array of add page routes for the given entity type.
   */
  public static function getAddRoute($entity_type) {
    /** @var \Drupal\Core\Routing\RouteProvider $route_provider */
    $route_provider = \Drupal::getContainer()->get('router.route_provider');

    $route_name = [];

    switch ($entity_type) {
      case 'node':
        $route_name[] = 'node.add';
        break;

      case 'user':
        $route_name[] = 'user.admin_create';
        break;

      case 'shortcut':
        $route_name[] = 'shortcut.link_add';
        break;

      default:
        // Some default add form route names.
        $route_name = [
          $entity_type . '.add_form',
          'entity.' . $entity_type . '.add_form',
        ];
    }

    return $route_provider->getRoutesByNames($route_name);

  }

}
