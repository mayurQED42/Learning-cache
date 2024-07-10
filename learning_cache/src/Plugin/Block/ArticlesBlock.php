<?php

namespace Drupal\learning_cache\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\node\Entity\Node;

/**
 * Provides a 'Articles block' Block.
 *
 * @Block(
 *   id = "articles_block",
 *   admin_label = @Translation("Articles Block"),
 * )
 */
class ArticlesBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Query the last 3 articles
    $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery()
      ->condition('status', 1)
      ->condition('type', 'article')
      ->sort('created', 'DESC')
      ->range(0, 3)
      ->accessCheck(FALSE);
    $nids = $query->execute();

    $articles = Node::loadMultiple($nids);

    $items = [];
    foreach ($articles as $article) {
      $items[] = $article->toLink()->toRenderable();
    }

    // $build = [
    //   '#theme' => 'item_list',
    //   '#items' => $items,
    //   '#cache' => [
    //     'tags' => ['node_list:article'],
    //   ],
    // ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    // Merge the cache tags of the nodes being displayed.
    if ($nids = array_keys($this->getArticleNids())) {
      return Cache::mergeTags(parent::getCacheTags(), Cache::buildTags('node', $nids));
    }
    return parent::getCacheTags();
  }

  /**
   * Get the NIDs of the last 3 articles.
   */
  protected function getArticleNids() {
    $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery()
      ->condition('status', 1)
      ->condition('type', 'article')
      ->sort('created', 'DESC')
      ->range(0, 3)
      ->accessCheck(FALSE);
    return $query->execute();
  }

}
