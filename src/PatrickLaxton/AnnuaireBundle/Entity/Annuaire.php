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
    private $filename;

    /**
     * @return string
     */
    public function getFilename() {
        return $this->filename;
    }

    /**
     * @param string $filename
     * @return \PatrickLaxton\AnnuaireBundle\Entity\Annuaire
     */
    public function setFilename($filename) {
        $this->filename = $filename;
        return $this;
    }
}