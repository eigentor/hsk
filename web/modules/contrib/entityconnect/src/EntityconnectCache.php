<?php

namespace Drupal\entityconnect;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\SessionManager;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A custom class for managing the Entityconnect cache.
 *
 * @package Drupal\entityconnect
 */
class EntityconnectCache {

  /**
   * The private temporary storage.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  private $store;

  /**
   * The session manager object.
   *
   * @var \Drupal\Core\Session\SessionManager
   */

  private $sessionManager;

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  private $account;

  /**
   * Saves our dependencies.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $store
   *   The private storage object.
   * @param \Drupal\Core\Session\SessionManager $sessionManager
   *   The session manager.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user account object.
   */
  public function __construct(PrivateTempStoreFactory $store, SessionManager $sessionManager, AccountInterface $account) {
    $this->store = $store->get('entityconnect');
    $this->sessionManager = $sessionManager;
    $this->account = $account;
    // Start a manual session for anonymous users.
    if ($account->isAnonymous() && !isset($_SESSION['entityconnect_session'])) {
      $_SESSION['entityconnect_session'] = TRUE;
      $sessionManager->start();
    }
  }

  /**
   * Uses Symfony's ContainerInterface to declare dependency for constructor.
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private'),
      $container->get('session_manager'),
      $container->get('current_user')
    );
  }

  /**
   * Gets the data from our PrivateTempStore for the given key.
   *
   * @param string $key
   *   The cache key.
   *
   * @return mixed
   *   The cache data.
   */
  public function get($key) {
    return $this->store->get($key);
  }

  /**
   * Stores the key/data pair in our PrivateTempStore.
   *
   * @param string $key
   *   The cache key.
   * @param mixed $data
   *   The cache data.
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   */
  public function set($key, $data) {
    $this->store->set($key, $data);
  }

  /**
   * Deletes the key/data pair from our PrivateTempStore.
   *
   * @param string $key
   *   The cache key.
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   */
  public function delete($key) {
    $this->store->delete($key);
  }

}
