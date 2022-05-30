<?php

namespace Drupal\mymodule\Controller;


use Drupal\Core\Controller\ControllerBase;


class AppController extends ControllerBase{

    public function index(){
        $query = \Drupal::entityQuery('taxonomy_term');
        $tids = $query->execute();
        $univers = \Drupal\taxonomy\Entity\Term::loadMultiple($tids[0]);
        return [
            "#markup"=> t("hello world mymodule")
        ];
    }

}