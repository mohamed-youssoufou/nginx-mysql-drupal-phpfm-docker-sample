<?php


namespace Drupal\bni_ci\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends ControllerBase{

    public function index(){
        return[
            "#markup" => t('Hello worl @name', [
                '@name'=> 'mohamed'
            ])
        ];
    }

    public function contact(){
        return [
            "#markup" => t('contact')
        ];
    }

}