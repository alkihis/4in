<?php

/**
 * Gene
 * 
 * Represents a gene (one only, not a gene and all his homologous).
 * 
 * Can be constructed by mysqli_assoc_result or gene_id_string
 */
class Gene {
    protected $id;
    protected $real_id;
    protected $specie;

    protected $name;
    protected $pathways = [];
    protected $func;
    protected $sequence;
    protected $sequence_pro;

    protected $full_adn;
    protected $full_pro;

    protected $fullname;
    protected $family;
    protected $sub_family;

    protected $has_link;
    protected $is_link;
    protected $alias;
    protected $addi;

    /**
     * Construct a gene by mysqli_row or gene_id_string
     *
     * @param array|string $row
     */
    public function __construct($row) {
        if (is_string($row)) { // Construction à partir de l'ID
            $row = self::loadAssocGene($row);

            if ($row === null) {
                throw new RuntimeException("Unknown gene");
            }
        }

        // Si on a un tableau, on construit le Gene depuis le mysqli_result_array_assoc
        if (is_array($row)) {
            $this->id = $row['gene_id'];
            $this->real_id = (int)($row['id'] ?? 0);
            $this->specie = $row['specie'];
            $this->name = $row['gene_name'];
            $this->fullname = $row['fullname'];
            $this->family = $row['family'];
            $this->sub_family = $row['subfamily'];
            $this->func = $row['func'];

            $this->sequence = $row['is_seq_adn'] ? true : false;
            $this->sequence_pro = $row['is_seq_pro'] ? true : false;

            $this->full_adn = $row['sequence_adn'] ?? null;
            $this->full_pro = $row['sequence_pro'] ?? null;

            $paths = explode(',', $row['pathways']);
            if (count($paths) === 1 && $paths[0] === "") {
                $this->pathways = [];
            }
            else {
                $this->pathways = $paths;
            }

            $this->has_link = $row['linkable'] !== '0';
            $this->is_link = $row['linkable'] !== null;

            $this->alias = $row['alias'] ?? null;
            $this->addi = $row['addi'] ?? null;
        }
        else {
            throw new NotImplementedException("Unknown type of argument passed to Gene constructor : " . gettype($row));
        }
    }

    /**
     * Charge un gène via son ID numérique, et renvoie le tableau de résultat SQL.
     * 
     * Renvoie null si il n'existe pas.
     *
     * @param string $id
     * @return array|null
     */
    static protected function loadAssocGene(string $id) : ?array {
        global $sql;

        $id = mysqli_real_escape_string($sql, $id);

        $q = mysqli_query($sql, "SELECT g.*, a.gene_id, a.specie, a.sequence_adn, a.sequence_pro, a.linkable, a.alias, a.addi,
            (SELECT GROUP_CONCAT(DISTINCT p.pathway SEPARATOR ',')
            FROM Pathways p 
            WHERE g.id = p.id) as pathways,
            (CASE 
                WHEN a.sequence_adn IS NOT NULL THEN 1
                ELSE 0
            END) as is_seq_adn,
            (CASE 
                WHEN a.sequence_pro IS NOT NULL THEN 1
                ELSE 0
            END) as is_seq_pro
        FROM GeneAssociations a 
        JOIN Gene g ON a.id=g.id
        WHERE a.gene_id = '$id'
        GROUP BY a.gene_id, g.id");

        if (!$q) {
            Logger::write("Unable to load gene using Gene::loadAssocGene : " . mysqli_errno($sql) . " / " . mysqli_error($sql));
            return null;
        }

        if (mysqli_num_rows($q) === 0){
            return null;
        }

        return mysqli_fetch_assoc($q);
    }

    /**
     * Indique des informations de debug
     * Tableau formaté comme un Gene SQL
     *
     * @return array
     */
    public function __debugInfo() : array {
        return [
            'GeneAssociations' => [
                'gene_id' => $this->id,
                'specie' => $this->specie,
                'sequence_adn' => $this->sequence,
                'sequence_pro' => $this->sequence_pro,
                'linkable' => ($this->is_link ? ($this->has_link ? "1" : "0") : null),
                'alias' => $this->alias,
                'addi' => $this->addi
            ],
            'Gene' => [
                'id' => $this->real_id,
                'func' => $this->func,
                'gene_name' => $this->name,
                'fullname' => $this->fullname,
                'family' => $this->family,
                'subfamily' => $this->sub_family
            ],
            'Pathways' => $this->pathways
        ];
    }

    // Implémentations des méthodes de l'interface
    public function getID() : string {
        return $this->id;
    }

    public function getRealID() : ?int {
        return $this->real_id;
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

    public function getSeqADN() : ?string {
        return $this->full_adn;
    }
    public function getSeqProt() : ?string {
        return $this->full_pro;
    }

    public function hasLink() : bool {
        return $this->has_link;
    }

    public function isLinkDefined() : bool {
        return $this->is_link;
    }

    public function getAlias() : ?string {
        return $this->alias;
    }

    public function getAdditionalInfos() : ?string {
        return $this->addi;
    }
}
