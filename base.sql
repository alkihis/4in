CREATE TABLE Gene (
    id INT NOT NULL AUTO_INCREMENT,
    func VARCHAR(100),
    gene_name VARCHAR(100),
    fullname VARCHAR(100),
    family VARCHAR(100),
    subfamily VARCHAR(100),
    PRIMARY KEY (id)
);

CREATE TABLE Pathways (
    id_pathway INT NOT NULL AUTO_INCREMENT,
    id INT NOT NULL,
    pathway VARCHAR(100),
    PRIMARY KEY (id_pathway),
    FOREIGN KEY (id) REFERENCES Gene(id) ON DELETE CASCADE
);

CREATE TABLE GeneAssociations (
    id INT NOT NULL,
    gene_id VARCHAR(30),
    sequence_id VARCHAR(50),
    specie VARCHAR(50),
    addi TEXT,
    PRIMARY KEY (gene_id),
    FOREIGN KEY (id) REFERENCES Gene(id) ON DELETE CASCADE
);