DROP TABLE  IF EXISTS paniers,commentaires,commandes, produits, users, typeProduits, etats;

-- --------------------------------------------------------
-- Structure de la table typeproduits
--
CREATE TABLE IF NOT EXISTS typeProduits (
  id int(10) NOT NULL,
  libelle varchar(50) DEFAULT NULL,
  PRIMARY KEY (id)
)  DEFAULT CHARSET=utf8;
-- Contenu de la table typeproduits
INSERT INTO typeProduits (id, libelle) VALUES
(1, 'Développement'),
(2, 'OS'),
(3, 'Anti-virus'),
(4, 'Divers');

-- --------------------------------------------------------
-- Structure de la table etats

CREATE TABLE IF NOT EXISTS etats (
  id int(11) NOT NULL AUTO_INCREMENT,
  libelle varchar(20) NOT NULL,
  PRIMARY KEY (id)
) DEFAULT CHARSET=utf8 ;
-- Contenu de la table etats
INSERT INTO etats (id, libelle) VALUES
(1, 'A préparer'),
(2, 'Expédié');

-- --------------------------------------------------------
-- Structure de la table produits

CREATE TABLE IF NOT EXISTS produits (
  id int(10) NOT NULL AUTO_INCREMENT,
  typeProduit_id int(10) DEFAULT NULL,
  nom varchar(50) DEFAULT NULL,
  prix float(6,2) DEFAULT NULL,
  photo varchar(50) DEFAULT NULL,
  info VARCHAR(300) DEFAULT NULL,
  dispo tinyint(4) NOT NULL,
  stock int(11) NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT fk_produits_typeProduits FOREIGN KEY (typeProduit_id) REFERENCES typeProduits (id) ON DELETE CASCADE
) DEFAULT CHARSET=utf8 ;

INSERT INTO produits (id,typeProduit_id,nom,prix,photo,dispo,stock, info) VALUES
(1,1, 'Oracle','100','oracle.jpg',1,5,'Oracle Database est un système de gestion de base de données relationnelle (SGBDR) qui depuis l''introduction du support du modèle objet dans sa version 8 peut être aussi qualifié de système de gestion de base de données relationnel-objet (SGBDRO). Fourni par Oracle Corporation, il a été développé par Larry Ellison, accompagné entre autres, de Bob Miner et Ed Oates.'),
(2,4, 'Photoshop','300','photoshop.jpg',1,4,'Photoshop est un logiciel de retouche, de traitement et de dessin assisté par ordinateur édité par Adobe. Il est principalement utilisé pour le traitement de photographies numériques, mais sert également à la création d’images ex nihilo.'),
(3,4, 'Adobe Reader','8.5','adobe-reader.jpg',1,10,'information sur le produit'),
(4,1, 'Intellj','8','intellij.jpg',1,5,'IntelliJ IDEA est un IDE Java commercial développé par JetBrains. Il est fréquemment appelé par le simple nom d’« IntelliJ » ou « IDEA » .'),
(5,3, 'Avast','55','avast.jpg',1,4,'Avast Antivirus est un logiciel antivirus développé par la société Avast Software (anciennement Alwil Software) située à Prague en République tchèque.'),
(6,4, 'After Effect','5','aftereffect.jpg',1,10,'After Effects est un logiciel, à la base, de montage vidéo qui est devenu par la suite un outil de composition (compositing en anglais) et d''effets visuels, pionnier de l''animation graphique sur ordinateur personnel, édité par la société Adobe Systems.'),
(7,3, 'Kaspersky','5','kaspersky.jpg',1,10,'Kaspersky Anti-Virus (KAV) (anciennement AntiViral Toolkit Pro ou AVP) est un antivirus créé par la société russe Kaspersky Lab. Cette dernière met aussi à disposition sur son site un système de recherche de virus en ligne.'),
(8,3, 'Mcafee','5','mcafee.jpg',1,10,'McAfee VirusScan est un logiciel antivirus pour les systèmes d''exploitation Windows, Mac, Linux et Unix édité par la société américaine McAfee.'),
(9,1, 'Php Storm','5','phpstorm.jpg',1,10,'PhpStorm 2016 fournit un éditeur de code riche et intelligent pour PHP avec coloration syntaxique, code étendu configuration mise en forme, on-the-fly vérification des erreurs, et le code intelligent achèvement. PhpStorm est un IDE pour HTML, JavaScript et PHP. '),
(10,1, 'C Lion','5','clion.jpg',1,10,'Clion est un environnement de développement multiplateforme pour les langages C/C++. Il est proposé par l’éditeur de logiciels JetBrains qui est très connu surtout pour ses travaux dans l’environnement Java. '),
(11,4, 'Pack Office','5','packoffice.png',1,10,'Microsoft Office est une suite bureautique propriétaire de la société Microsoft fonctionnant avec les plates-formes fixes et mobiles. Elle s''installe sur ordinateur et fournit une suite de logiciels comme : Word, Excel, PowerPoint, OneNote, Outlook, Access et/ou Publisher selon les suites choisies.'),
(12,1, 'Eclipse','5','eclipse.jpg',1,10,'Eclipse est un projet, décliné et organisé en un ensemble de sous-projets de développements logiciels, de la fondation Eclipse visant à développer un environnement de production de logiciels libre qui soit extensible, universel et polyvalent, en s''appuyant principalement sur Java.'),
(13,2, 'Windows 10','5','windows10.jpg',1,10,'Windows 10 est un système d''exploitation de la famille Windows NT développé par la société américaine Microsoft. Officiellement présenté le 30 septembre 2014, il est disponible publiquement depuis le 29 juillet 2015.'),
(14,2, 'Windows 8','5','windows8.jpg',1,10,'Windows 8 est la version du système d''exploitation Windows multiplate-forme qui est commercialisée depuis le 26 octobre 20122. Version intermédiaire Windows 8.1 en 2013.'),
(15,2,'Manjaro','100','Manjaro.png',1,80,'Manjaro Linux, ou tout simplement Manjaro (prononcé comme dans Kilimanjaro), est une distribution Linux proposant Xfce et KDE comme interfaces utilisateur par défaut.');


-- --------------------------------------------------------
-- Structure de la table user
-- valide permet de rendre actif le compte (exemple controle par email )

CREATE TABLE IF NOT EXISTS users (
  id int(11) NOT NULL AUTO_INCREMENT,
  email varchar(255) NOT NULL,
  password varchar(255) NOT NULL,
  login varchar(255) NOT NULL,
  nom varchar(255) NOT NULL,
  code_postal varchar(255) NOT NULL,
  ville varchar(255) NOT NULL,
  adresse varchar(255) NOT NULL,
  valide tinyint NOT NULL,
  droit varchar(255) NOT NULL,
  PRIMARY KEY (id)
) DEFAULT CHARSET=utf8;

-- Contenu de la table users
INSERT INTO users (id,login,nom,password,email,valide,droit) VALUES
(1, 'admin','aministrateur',MD5('admin'), 'admin@gmail.com',1,'DROITadmin'),
(2, 'vendeur','nathan', MD5('vendeur'), 'vendeur@gmail.com',1,'DROITadmin'),
(3, 'client','Bilal', MD5('client'), 'client@gmail.com',1,'DROITclient'),
(4, 'client2','Guillaume', MD5('client2'), 'client2@gmail.com',1,'DROITclient'),
(5, 'client3', 'Tom', MD5('client3'), 'client3@gmail.com',1,'DROITclient');



-- --------------------------------------------------------
-- Structure de la table commandes
CREATE TABLE IF NOT EXISTS commandes (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) NOT NULL,
  prix float(6,2) NOT NULL,
  date_achat  timestamp default CURRENT_TIMESTAMP,
  etat_id int(11) NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT fk_commandes_users FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT fk_commandes_etats FOREIGN KEY (etat_id) REFERENCES etats (id) ON DELETE CASCADE
) DEFAULT CHARSET=utf8 ;



-- --------------------------------------------------------
-- Structure de la table paniers
CREATE TABLE IF NOT EXISTS paniers (
  id int(11) NOT NULL AUTO_INCREMENT,
  quantite int(11) NOT NULL,
  prix float(6,2) NOT NULL,
  dateAjoutPanier timestamp default CURRENT_TIMESTAMP,
  user_id int(11) NOT NULL,
  produit_id int(11) NOT NULL,
  commande_id int(11) DEFAULT NULL,
  PRIMARY KEY (id),
  CONSTRAINT fk_paniers_users FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT fk_paniers_produits FOREIGN KEY (produit_id) REFERENCES produits (id) ON DELETE CASCADE,
  CONSTRAINT fk_paniers_commandes FOREIGN KEY (commande_id) REFERENCES commandes (id) ON DELETE CASCADE
) DEFAULT CHARSET=utf8 ;


-- Structure de la table Commentaires

CREATE TABLE IF NOT EXISTS commentaires (
id int(11) NOT NULL AUTO_INCREMENT,
commentaire text,
date_commentaire  timestamp default CURRENT_TIMESTAMP,
user_id int(11) NOT NULL,
produit_id int(11) NOT NULL,
PRIMARY KEY(id),
CONSTRAINT fk_commentaires_users FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
CONSTRAINT fk_commentaires_produits FOREIGN KEY (produit_id) REFERENCES produits(id) ON DELETE CASCADE
) DEFAULT CHARSET=utf8;





