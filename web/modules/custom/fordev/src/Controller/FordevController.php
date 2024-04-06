<?php declare(strict_types = 1);

namespace Drupal\fordev\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for Fordev routes.
 */
final class FordevController extends ControllerBase 
{

    /**
     * Builds the response.
     */
    public function __invoke(): array
    {
        $db = \Drupal::database();
        $query = $db->select('node', 'n');
        $query->leftJoin('node_field_data', 'nd', 'nd.nid = n.nid');
        $query->leftJoin('node__body', 'nb', 'nb.entity_id = n.nid');
        $query->leftJoin('node__field_tags', 'nt', 'nt.entity_id = n.nid');
        $query->leftJoin('taxonomy_term_field_data', 'ntd', 'ntd.tid = nt.field_tags_target_id');
        $query->leftJoin('paragraph__field_title', 'pt', 'pt.entity_id = n.nid');

        


        $query->fields('nt', ['field_tags_target_id']);
        $query->fields('ntd', ['name']);
        $query->fields('pt', ['field_title_value']);



        $query->fields('n', ['nid', 'type']);
        $query->fields('nb', ['body_value']);  // Specify fields from 'node' table
        $query->fields('nd', ['title']);      // Specify fields from 'node_field_data' table
        $result = $query->execute()->fetchAll();
        dd($result);

        $pager = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(2);
        $results = $pager->execute()->fetchAll();
        foreach ($results as $result) {
            $rows[] = [
              "data" => [
                $result->nid,
                $result->type,
                $result->title,
                $result->name,
                $result->body_value,
              ],
            ];
        }
        $header = [
          'nid' => "ID",
          'type' => 'Type',
          'title' => 'Title',
          'name' => 'Category Name',
          'body_value' => 'Body value',

        ];
        $build['table'] = [
          '#theme' => 'table',
          '#header' => $header,
          '#rows' => $rows,
          '#empty' => t('No result found'),
        ];
        $build['pager'] = array(
          '#type' => 'pager'
        );
        return $build;
    }

}
