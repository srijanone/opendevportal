<?php

namespace Drupal\opendevx_navigation_block\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\opendevx_block\ApiProducts;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides controller class ProductNavigationController.
 */
class ProductNavigationController extends ControllerBase {

  /**
   * Api product object.
   *
   * @var \Drupal\opendevx_block\ApiProducts
   */
  protected $product;

  /**
   * The request stack instance.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $currentPath;

  /**
   * ProductNavigationController constructor.
   *
   * @param \Drupal\opendevx_block\ApiProducts $product
   *   The plugin api product class.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The plugin request stack service.
   */
  public function __construct(ApiProducts $product, RequestStack $request_stack) {
    $this->product = $product;
    $this->currentPath = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('opendevx_block.products'),
      $container->get('request_stack')
    );
  }

  /**
   * RedirectAfterOrganisationSave callback.
   */
  public function checkFirstContent() {
    $path = $this->currentPath->getCurrentRequest()->getPathInfo();
    $explode_path = explode('/', $path);
    $nid = (int) $explode_path[3];
    $config = \Drupal::config('block.block.product_api');
    $navigation = $config->getRawData()['settings']['dvp_sidebar_navigation'];
    $list = [];
    if (!empty($navigation)) {
      foreach ($navigation as $key => $value) {
        if ($value['show'] == 1) {
          $list[$key] = !empty($value['navigation_text']) ? $value['navigation_text'] : $value['type'];
        }
      }
    }
    $index_content = $this->product->setProductId($nid)->getFirstIndexProductContents($list);
    $output = !empty($index_content) ?
    $index_content : $this->currentPath->getCurrentRequest()->headers->get('referer');
    $response = new RedirectResponse($output);

    return $response;
  }

}
