CREATE
    DATABASE greenmart;
USE
    greenmart;

-- TABELLA: negozio
CREATE TABLE negozio
(
    ID        INT PRIMARY KEY AUTO_INCREMENT,
    indirizzo VARCHAR(30) NOT NULL,
    nome      VARCHAR(30) NOT NULL
);

-- TABELLA: prodotto
CREATE TABLE prodotto
(
    ID            INT PRIMARY KEY AUTO_INCREMENT,
    id_negozio    INT            NOT NULL,
    nome          VARCHAR(30)    NOT NULL,
    descrizione   TEXT           NOT NULL,
    prezzo        DECIMAL(10, 2) NOT NULL,
    q_disponibile INT            NOT NULL,
    img_url       TEXT           NOT NULL,
    unit          VARCHAR(30)    NOT NULL,
    FOREIGN KEY (id_negozio) REFERENCES negozio (ID)
);

-- TABELLA: utente
CREATE TABLE utente
(
    ID        INT PRIMARY KEY AUTO_INCREMENT,
    nome      VARCHAR(30) NOT NULL,
    email     VARCHAR(30) NOT NULL,
    password  VARCHAR(30) NOT NULL,
    indirizzo VARCHAR(30) NOT NULL
);

-- TABELLA: recensioni
CREATE TABLE recensioni
(
    ID          INT PRIMARY KEY AUTO_INCREMENT,
    id_prodotto INT         NOT NULL,
    id_utente   INT         NOT NULL,
    valutazione FLOAT(1, 1) NOT NULL,
    commento    TEXT        NOT NULL,
    data        DATE        NOT NULL,
    FOREIGN KEY (id_prodotto) REFERENCES prodotto (ID),
    FOREIGN KEY (id_utente) REFERENCES utente (ID)
);

-- TABELLA: carrello
CREATE TABLE carrello
(
    ID             INT PRIMARY KEY AUTO_INCREMENT,
    id_utente      INT  NOT NULL,
    data_creazione DATE NOT NULL,
    FOREIGN KEY (id_utente) REFERENCES utente (ID)
);

-- TABELLA: carrello_prodotto
CREATE TABLE carrello_prodotto
(
    id_carrello INT NOT NULL,
    id_prodotto INT NOT NULL,
    quantità    INT NOT NULL,
    PRIMARY KEY (id_carrello, id_prodotto),
    FOREIGN KEY (id_carrello) REFERENCES carrello (ID),
    FOREIGN KEY (id_prodotto) REFERENCES prodotto (ID)
);

-- TABELLA: pagamento
CREATE TABLE pagamento
(
    ID       INT PRIMARY KEY AUTO_INCREMENT,
    tipo     VARCHAR(30) NOT NULL,
    dettagli TEXT        NOT NULL
);

-- TABELLA: delivery_company
CREATE TABLE delivery_company
(
    ID   INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(30) NOT NULL,
    sede VARCHAR(30) NOT NULL
);

-- TABELLA: postino
CREATE TABLE postino
(
    ID          INT PRIMARY KEY AUTO_INCREMENT,
    id_del_comp INT         NOT NULL,
    nome        VARCHAR(30) NOT NULL,
    telefono    VARCHAR(16) NOT NULL,
    FOREIGN KEY (id_del_comp) REFERENCES delivery_company (ID)
);

-- TABELLA: ordine
CREATE TABLE ordine
(
    ID           INT PRIMARY KEY AUTO_INCREMENT,
    id_carrello  INT  NOT NULL,
    id_pagamento INT  NOT NULL,
    id_postino   INT  NOT NULL,
    data_ordine  DATE NOT NULL,
    stato        INT  NOT NULL COMMENT '0=non spedito; 1=spedito; 2=in spedizione;',
    FOREIGN KEY (id_carrello) REFERENCES carrello (ID),
    FOREIGN KEY (id_pagamento) REFERENCES pagamento (ID),
    FOREIGN KEY (id_postino) REFERENCES postino (ID)
);

-- ##################### AGGIUSTAMENTI #####################

RENAME TABLE utente TO utenti;
RENAME TABLE prodotto TO prodotti;

ALTER TABLE utenti
    ADD COLUMN cognome  VARCHAR(30) NOT NULL AFTER nome,
    ADD COLUMN username VARCHAR(30) NOT NULL AFTER cognome;

ALTER TABLE utenti
    MODIFY COLUMN indirizzo VARCHAR(30) NULL;


ALTER TABLE utente
    MODIFY COLUMN nome VARCHAR(255) NOT NULL,
    MODIFY COLUMN email VARCHAR(255) NOT NULL,
    MODIFY COLUMN password VARCHAR(255) NOT NULL,
    MODIFY COLUMN indirizzo VARCHAR(255) NULL;


ALTER TABLE prodotto
    MODIFY COLUMN nome VARCHAR(255) NOT NULL,
    MODIFY COLUMN unit VARCHAR(255) NOT NULL;


ALTER TABLE prodotti
    MODIFY COLUMN id_negozio INT NULL;


ALTER TABLE prodotti
    MODIFY COLUMN unit VARCHAR(255) NULL;


ALTER TABLE pagamento
    MODIFY COLUMN tipo VARCHAR(255) NOT NULL;


ALTER TABLE delivery_company
    MODIFY COLUMN nome VARCHAR(255) NOT NULL,
    MODIFY COLUMN sede VARCHAR(255) NOT NULL;


ALTER TABLE postino
    MODIFY COLUMN nome VARCHAR(255) NOT NULL,
    MODIFY COLUMN telefono VARCHAR(255) NOT NULL;


ALTER TABLE utenti
    ADD telefono             VARCHAR(20),
    ADD tipo_account         VARCHAR(50) DEFAULT 'Base',
    ADD badge_vip            BOOLEAN     DEFAULT FALSE,
    ADD badge_cliente_fedele BOOLEAN     DEFAULT FALSE,
    ADD badge_verificato     BOOLEAN     DEFAULT FALSE;


CREATE TABLE preferiti
(
    id            INT AUTO_INCREMENT PRIMARY KEY,
    id_utente     INT NOT NULL,
    id_prodotto   INT NOT NULL,
    data_aggiunta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utente) REFERENCES utenti (ID),
    FOREIGN KEY (id_prodotto) REFERENCES prodotto (ID)
);


-- ##################### DATI DI PROVA #####################

INSERT INTO prodotti (nome, prezzo, descrizione, img_url, q_disponibile)
VALUES ('Smartphone XYZ', 299.99, 'Smartphone con schermo 6.5", 64GB memoria, fotocamera 12MP.',
        'https://picsum.photos/150?random=1', 10),

       ('Laptop ABC', 799.00, 'Laptop potente con processore i7, 16GB RAM, 512GB SSD.',
        'https://picsum.photos/150?random=2', 5),

       ('Cuffie Wireless', 59.90, 'Cuffie Bluetooth con cancellazione del rumore.',
        'https://picsum.photos/150?random=3', 20),

       ('Frutta Mista', 9.50, 'Confezione di frutta fresca assortita (mele, banane, arance).',
        'https://picsum.photos/150?random=4', 30),

       ('Maglietta Verde', 15.00, 'Maglietta in cotone 100%, taglia M.',
        'https://picsum.photos/150?random=5', 25);

INSERT INTO prodotto (id_negozio, nome, descrizione, prezzo, q_disponibile, img_url, unit, categoria)
VALUES
-- Sedie ergonomiche da ufficio
(1, 'Sedia Ergonomica Classic', 'Sedia da ufficio ergonomica con supporto lombare regolabile', 129.99, 10,
 'https://images.pexels.com/photos/7845304/pexels-photo-7845304.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2',
 'pezzo', 'Ufficio'),
(2, 'Sedia Ergonomica Pro', 'Sedia ergonomica con poggiatesta e braccioli imbottiti', 159.49, 8,
 'https://www.google.com/url?sa=i&url=https%3A%2F%2Fsediaufficio365.it%2Fsedia-da-ufficio-ergonomica-balance-pro-white-braccioli-3d&psig=AOvVaw3gzWYU4sti-xZA16XySbsp&ust=1747128267439000&source=images&cd=vfe&opi=89978449&ved=0CBQQjRxqFwoTCNDZxJPOnY0DFQAAAAAdAAAAABAE',
 'pezzo', 'Ufficio'),
(3, 'Sedia Ergonomica Mesh', 'Schienale in rete traspirante e seduta imbottita', 119.00, 12,
 'https://www.studiot.it/wp-content/uploads/2020/11/sedie-da-ufficio-ergonomich-1.png', 'pezzo', 'Ufficio'),
(4, 'Sedia Ergonomica Executive', 'Modello premium con regolazioni complete', 199.90, 5,
 'https://img.it.favicdn.net/images/products/300x300/0a0fcc61-7da1-4b2b-8c48-dfaec850c05d.jpg', 'pezzo', 'Ufficio'),

-- PC fissi e portatili
(1, 'PC Desktop Workstation', 'Computer fisso con processore i7 e 16GB RAM', 899.00, 3,
 'https://img.freepik.com/foto-premium/computer-fisso_869423-584.jpg', 'pezzo', 'Informatica'),
(2, 'PC Desktop Gaming', 'PC da gaming con GPU RTX 3060', 1299.00, 2,
 'https://m.media-amazon.com/images/I/41wivBxBMVS.jpg', 'pezzo', 'Informatica'),
(3, 'Notebook Ultrabook 14"', 'Portatile leggero con SSD da 512GB', 749.00, 5,
 'https://images.pexels.com/photos/8946995/pexels-photo-8946995.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2',
 'pezzo', 'Informatica'),
(4, 'Laptop Business Pro', 'Portatile aziendale con display FHD e lunga autonomia', 1049.00, 4,
 'https://images.pexels.com/photos/6612345/pexels-photo-6612345.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2',
 'pezzo', 'Informatica'),

-- Decorazioni natalizie
(5, 'Pupazzo di Neve Decorativo', 'Decorazione natalizia in stoffa da tavolo', 19.90, 15,
 'https://images.pexels.com/photos/753500/pexels-photo-753500.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2',
 'pezzo', 'Decorazioni Natalizie'),
(6, 'Palla di Natale Rossa', 'Decorazione per albero di Natale classica in vetro', 4.50, 50,
 'https://images.pexels.com/photos/1764088/pexels-photo-1764088.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2',
 'pezzo', 'Decorazioni Natalizie'),
(7, 'Stella Dorata per Albero', 'Stella dorata da mettere in cima all’albero', 9.99, 20,
 'https://images.pexels.com/photos/756667/pexels-photo-756667.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2',
 'pezzo', 'Decorazioni Natalizie'),
(8, 'Candela Profumata alla Cannella', 'Candela natalizia decorativa e profumata', 7.99, 30,
 'https://images.pexels.com/photos/278508/pexels-photo-278508.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2',
 'pezzo', 'Decorazioni Natalizie'),

-- Attrezzi per giardinaggio
(5, 'Pala da Giardino', 'Pala in acciaio per lavori di scavo', 24.90, 10,
 'https://images.pexels.com/photos/1301856/pexels-photo-1301856.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2',
 'pezzo', 'Giardinaggio'),
(6, 'Scala Pieghevole 3 Metri', 'Scala in alluminio leggera e resistente', 89.00, 4,
 'https://images.pexels.com/photos/262494/pexels-photo-262494.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2',
 'pezzo', 'Giardinaggio'),
(7, 'Carriola da Giardino', 'Carriola con ruota pneumatica e struttura in metallo', 74.99, 3,
 'https://images.pexels.com/photos/26827231/pexels-photo-26827231/free-photo-of-carriola.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2',
 'pezzo', 'Giardinaggio'),
(8, 'Annaffiatoio da 5L', 'Annaffiatoio in plastica con beccuccio lungo', 11.50, 20,
 'https://images.pexels.com/photos/31988469/pexels-photo-31988469/free-photo-of-gnomo-da-giardino-e-annaffiatoio-alla-luce-del-sole.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2',
 'pezzo', 'Giardinaggio');