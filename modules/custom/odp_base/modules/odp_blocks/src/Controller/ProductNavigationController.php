<?php

namespace Drupal\odp_blocks\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\odp_blocks\ApiProducts;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Provides class ProductNavigationController.
 */
class ProductNavigationController extends ControllerBase {

  /**
   * Api product object.
   *
   * @var \Drupal\odp_blocks\ApiProducts
   */
  protected $product;

  /**
   * The current path.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $currentPath;

  /**
   * OpenDevPortal config Service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * ProductNavigationController constructor.
   *
   * @param \Drupal\odp_blocks\ApiProducts $product
   *   The plugin api product class.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The plugin request stack service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The plugin request stack service.
   */
  public function __construct(
    ApiProducts $product,
    RequestStack $request_stack,
    ConfigFactoryInterface $config_factory) {
    $this->product = $product;
    $this->currentPath = $request_stack;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('odp_blocks.products'),
      $container->get('request_stack'),
      $container->get('config.factory')
    );
  }

  /**
   * RedirectAfterOrganisationSave callback.
   */
  public function checkFirstContent() {
    $path = $this->currentPath->getCurrentRequest()->getPathInfo();
    $explode_path = explode('/', $path);
    $nid = (int) $explode_path[3];
    $config = $this->configFactory->get('block.block.product_api');
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
