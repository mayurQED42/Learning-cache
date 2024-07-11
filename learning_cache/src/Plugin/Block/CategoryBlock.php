<?php

namespace Drupal\learning_cache\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\Entity\Node;

/**
 * Provides a 'Category Articles' Block.
 *
 * @Block(
 *   id = "category_block",
 *   admin_label = @Translation("Category Articles Block"),
 * )
 */
class CategoryBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Exercise - 2:
    // Create a custom block which shows articles from the prefered category selected on user account.
    // Handle cache scenerios using a custom cache context.
    // Load the current user.
    $user = \Drupal::entityTypeManager()->getStorage('user')->load(\Drupal::currentUser()->id());

    // Get the category field value from the user profile.
    $category_field = $user->get('field_category')->getValue();
    if (empty($category_field)) {
      return [
        '#markup' => $this->t('No category selected in your profile.'),
      ];
    }

    $category_tid = $category_field[0]['target_id'];

    // Query the latest 3 articles from the selected category.
    $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery()
      ->condition('status', 1)
      ->condition('type', 'article')
      ->condition('field_category', $category_tid)
      ->sort('created', 'DESC')
      ->range(0, 3)
      ->accessCheck(TRUE);

    $nids = $query->execute();

    if (empty($nids)) {
      return [
        '#markup' => $this->t('No articles found for the selected category.'),
      ];
    }

    $nodes = Node::loadMultiple($nids);

    $items = [];
    foreach ($nodes as $node) {
      $items[] = $node->toLink()->toRenderable();
    }

    $build = [
      '#theme' => 'item_list',
      '#items' => $items,
      '#cache' => [
        'contexts' => ['user'],
      ],
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['user']);
  }

}
