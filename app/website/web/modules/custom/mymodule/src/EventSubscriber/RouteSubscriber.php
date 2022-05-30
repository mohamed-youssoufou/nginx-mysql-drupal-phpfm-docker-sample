<?php

namespace Drupal\mymodule\EventSubscriber;

use Drupal\Core\Routing\RouteSubscriberBase;
// use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;


class RouteSubscriber extends RouteSubscriberBase{

    //private $request;

    // public function __construct(Request $request)
    // {
    //     $this->request = $request;
    // }
    public function alterRoutes(RouteCollection $collection){
        if($collection->get('mymodule_index')){
            $url = Url::fromRoute('user.login')->toString(); 
            new RedirectResponse($url);
        }
        
    }
}