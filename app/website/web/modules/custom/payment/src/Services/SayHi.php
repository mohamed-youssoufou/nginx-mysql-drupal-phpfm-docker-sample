<?php

namespace Drupal\payment\Services;

use Drupal\Core\StringTranslation\StringTranslationTrait;

class SayHi{

    use StringTranslationTrait;

    public function getSalutation() {
        $time = new \DateTime();
        if ((int) $time->format('G') >= 00 && (int) $time->format('G') < 12) {
            return $this->t('Good morning world');
        }
        if ((int) $time->format('G') >= 12 && (int) $time->format('G') < 18) {
            return $this->t('Good afternoon world');
        }
        if ((int) $time->format('G') >= 18) {
            return $this->t('Good evening world');
        }
    }

}