<?php

namespace PatrickLaxton\AnnuaireBundle\Entity;

/**
 * Personne
 *
 */
class Annuaire {
    /**
     * @var string
     */
    private $file;

    /**
     * @return string
     */
    public function getFile() {
        return $this->file;
    }

    /**
     * @param string $file
     * @return \PatrickLaxton\AnnuaireBundle\Entity\Annuaire
     */
    public function setFile($file) {
        $this->file = $file;
        return $this;
    }
}