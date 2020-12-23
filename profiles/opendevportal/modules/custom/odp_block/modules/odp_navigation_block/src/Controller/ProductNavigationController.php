<?php

namespace Drupal\odp_navigation_block\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\odp_block\ApiProducts;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ProductNavigationController extends ControllerBase {

  /**
   * Api product object.
   *
   * @var mixed $product
   *
   */
  protected $product;

  /**
   * @var mixed $currentPath
   */
  protected $currentPath;

  /**
   * ProductNavigationController constructor.
   *
   * @param mixed $product
   *   The plugin api product class.
   * @param mixed $request_stack
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
      $container->get('odp_block.products'),
      $container->get('request_stack')
    );
  }

  /**
   * redirectAfterOrganisationSave callback.
   */
  public function checkFirstContent() {
    $path = $this->currentPath->getCurrentRequest()->getPathInfo();
    $explode_path = explode('/', $path);
    $nid = (int)$explode_path[3];
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
