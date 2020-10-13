<?php

namespace Drupal\opendevx_navigation_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Component\Utility\Unicode;

/**
 * Provides a 'Admin Dashboard Links' Block.
 *
 * @Block(
 *   id = "admin_dashboard_links_block",
 *   admin_label = @Translation("Admin Dashboard Links Block"),
 *   category = @Translation("Admin Dashboard Links Block"),
 * )
 */
class AdminDashboardLinks extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * AdminDashboardLinks constructor.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $attributes =  [
      'target' => $config['dvp_admin_links_settings']['link_target'] ? '_blank' : '_self',
    ];
    $admin_links = [];
    $map_links_values = preg_split("/[\r\n,;]+/", $config['dvp_admin_links_settings']['links']);

    foreach ($map_links_values as $mapValue) {
      $values = explode("::", $mapValue);
      if (count($values) == 2) {
        $admin_links[] =
        Link::fromTextAndUrl(Unicode::ucfirst($values[0]), Url::fromUserInput($values[1], ['attributes' => $attributes]))->toString();
      }
    }

    return $content = [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#title' => 'Administer Settings',
      '#items' => $admin_links,
      '#attributes' => ['class' => 'admin-dashboard-links-block'],
      '#wrapper_attributes' => ['class' => 'container'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();
    $form['dvp_admin_links_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Settings'),
      '#open'=> TRUE,
    ];

    $form['dvp_admin_links_settings']['link_target'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Open links in new tab.'),
      '#default_value' => $config['dvp_admin_links_settings']['link_target'],
    ];

    $form['dvp_admin_links_settings']['links'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Administrative Links'),
      '#description' => $this->t('Enter a line separated list of link titles with their admin url separated by :: .<br>
			For example LINK-TITLE::/admin/config. The url must starts with a forward slash /. You can enter multiple values separated by comma, new line or semi-colon.'),
      '#default_value' => $config['dvp_admin_links_settings']['links'],
    ];


    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['dvp_admin_links_settings'] = $values['dvp_admin_links_settings'];
  }

}
