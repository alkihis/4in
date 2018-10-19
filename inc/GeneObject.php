<?php

interface Gene {
    public function getID() : string;

    public function getSpecie() : string;

    public function isSequenceADN() : bool;
    public function isSequenceProt() : bool;

    public function getPathways() : array;

    public function getFamily() : string;
    public function getSubFamily() : string;
    public function getFunction() : string;
    public function getName() : string;
    public function getFullName() : string;
}

// class GeneObject
// Contient la représentation d'un gène
// Peut faire référence à UN gène
// Pour des groupes de gènes identiques, voir MultiGeneObject

class GeneObject implements Gene {
    protected $id;
    protected $specie;

    protected $name;
    protected $pathways = [];
    protected $func;
    protected $sequence;
    protected $sequence_pro;
    protected $fullname;
    protected $family;
    protected $sub_family;

    public function __construct(array $row) {
        $this->id = $row['gene_id'];
        $this->specie = $row['specie'];
        $this->name = $row['gene_name'];
        $this->fullname = $row['fullname'];
        $this->family = $row['family'];
        $this->sub_family = $row['subfamily'];
        $this->func = $row['func'];

        $this->sequence = $row['is_seq_adn'] ? true : false;
        $this->sequence_pro = $row['is_seq_pro'] ? true : false;

        $this->pathways = explode(',', $row['pathways']);
    }

    // Implémentations des méthodes de l'interface
    public function getID() : string {
        return $this->id;
    }

    public function getSpecie() : string {
        return $this->specie;
    }

    public function isSequenceADN() : bool {
        return $this->sequence;
    }

    public function isSequenceProt() : bool {
        return $this->sequence_pro;
    }

    public function getPathways() : array {
        return $this->pathways;
    }

    public function getFamily() : string {
        return $this->family;
    }
    public function getSubFamily() : string {
        return $this->sub_family;
    }
    public function getFunction() : string {
        return $this->func;
    }
    public function getName() : string {
        return $this->name;
    }
    public function getFullName() : string {
        return $this->fullname;
    }
}


