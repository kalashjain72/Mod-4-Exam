<?php

namespace Drupal\blogapi\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provides a JSON response of blogs.
 */
class BlogsApiController extends ControllerBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a new BlogsApiController instance.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * Returns a JSON response of blogs based on configured parameters.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   */
  public function getBlogs() {
    // Load configuration settings.
    $config = $this->config('blogapi.settings');
    $dateRange = $config->get('date_range');
    $authors = $config->get('authors');
    $tags = $config->get('tags');

    // Build a query to fetch blogs based on configured parameters.
    $query = $this->connection->select('node_field_data', 'n')
      ->fields('n', ['title', 'body', 'created'])
      ->condition('n.status', 1)
      ->condition('n.type', 'blog');

    // Apply filters based on configuration.
    if ($dateRange) {
      $query->condition('n.created', strtotime($dateRange), '>=');
    }

    // Add conditions for specific authors.
    if (!empty($authors)) {
      $query->condition('n.uid', $authors, 'IN');
    }

    // Add conditions for specific tags.
    if (!empty($tags)) {
      $query->leftJoin('node__field_tags', 'tags', 'tags.entity_id = n.nid');
      $query->condition('tags.field_tags_target_id', $tags, 'IN');
    }

    // Execute the query.
    $result = $query->execute();

    // Prepare the response data.
    $blogs = [];
    foreach ($result as $row) {
      $blogs[] = [
        'title' => $row->title,
        'body' => $row->body,
        'published_date' => date('Y-m-d', $row->created),
      ];
    }

    return new JsonResponse($blogs);
  }

}
