<?php

namespace Drupal\bni_ci\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

class RouteSubscriber extends RouteSubscriberBase {

    /**
      * {@inheritdoc}
      */
    public function alterRoutes(RouteCollection $collection){
        if($route = $collection->get('bni_ci_index')){
            $route->setPath('/contact');
        }
    }
}