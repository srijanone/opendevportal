<?php

namespace Drupal\opendevx_block\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\HttpFoundation\JsonResponse;

class CtaController extends ControllerBase {

  /**
   * @var mixed $currentPath
   */
  protected $currentPath;

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * @var mixed $storePath
   */
  protected $storePath;

  /**
   * CtaController constructor.
   *
   * @param mixed $product
   *   The plugin api product class.
   * @param mixed $request_stack
   *   The plugin request stack service.
   */
  public function __construct(RequestStack $request_stack,
  AccountInterface $account,
  PrivateTempStoreFactory $temp_store) {
    $this->currentPath = $request_stack;
    $this->account = $account;
    $this->storePath = $temp_store->get('opendevx_block');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('current_user'),
      $container->get('tempstore.private')
    );
  }

  /**
   * Redirect CTA Url callback.
   */
  public function redirectCtaUrl($apikey, $node_id) {

    // get the api key from config
    $api_key1 = \Drupal::settings('api_key')->get('api_key');
    if ($api_key1 == $apikey) {
      if (// Check if the node is valid) {
        // Get all field of node
        // Convert above array to json and return
        $result_array = json_decode($node_array);
        return new JsonResponse($result_array, 200, []);
      }
    }
  }

}
