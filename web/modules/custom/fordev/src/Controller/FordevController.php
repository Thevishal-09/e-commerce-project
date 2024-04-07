<?php declare(strict_types = 1);

namespace Drupal\fordev\Controller;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

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
        $query->leftJoin('paragraph__field_profile_pic', 'pim', 'pim.entity_id = n.nid');
        $query->leftJoin('file_managed', 'npfdi', 'pim.field_profile_pic_target_id = npfdi.fid');

        $query->fields('nt', ['field_tags_target_id']);
        $query->fields('npfdi', ['uri']);
        $query->fields('ntd', ['name']);
        $query->fields('pt', ['field_title_value']);
        $query->fields('n', ['nid', 'type']);
        $query->fields('nb', ['body_value']);
        $query->fields('nd', ['title']);
        $result = $query->execute()->fetchAll();
        // dd($result);

        $pager = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(2);
        $results = $pager->execute()->fetchAll();
        foreach ($results as $result) {
          $host = \Drupal::request()->getSchemeAndHttpHost();
          // dd(host);
            $newBaseUrl = "{$host}/sites/default/files/";
              dd($newBaseUrl);
            $product_image_url = $result->uri;
            $newPath = str_replace('public://', $newBaseUrl, $product_image_url);
            $directory = "public://";
            $file = system_retrieve_file($newPath, $directory, true, FileSystemInterface::EXISTS_RENAME);
            // $file_url = file_create_url($file->getFileUri());
            $uris = $file->getFileUri();
            $file_url = \Drupal::service('file_url_generator')->generateAbsoluteString($uris);
            // $file_data = "<img src='{$file_url}' alt='File Image' />";
            $file_data = "<img src='{$file_url}' alt='File Image' style='max-width: 100px;' />";

            // dd($file_url);
            $rows[] = [
              "data" => [
                $result->nid,
                $result->type,
                $result->title,
                $result->name,
                $result->body_value,
                $result->field_title_value,
                $file_data
                
              ],
            ];
        } 
        $header = [
          'nid' => "ID",
          'type' => 'Type',
          'title' => 'Title',
          'name' => 'Category Name',
          'body_value' => 'Body value',
          'field_title_value' => 'Para title',
          'uri' => 'Para images'

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
