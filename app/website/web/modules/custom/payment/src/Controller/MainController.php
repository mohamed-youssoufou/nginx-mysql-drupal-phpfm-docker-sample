<?php

namespace Drupal\payment\Controller;

use Drupal\Core\Controller\ControllerBase;
// use \Drupal\Entit

class MainController extends ControllerBase{



    public function index(){
        $query = \Drupal::entityQuery('node');
        $query->condition('type', 'methodes_de_paiement');
        $entity_ids = $query->execute();

        $data = [];
        foreach ($entity_ids as $id => $nid) {
            $entity = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
            $data[$id]["field_libelle"] = $entity->get('field_libelle')->value;
            $data[$id]["field_lien_payment"] = $entity->get('field_lien_payment')->value;
            $data[$id]["field_logo"] = $entity->get('field_logo')->value;
        }

        // dd($data);
        
        return [
            "#markup" => t('hey')
        ];
    }
}