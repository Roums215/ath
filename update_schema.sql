-- Désactiver les vérifications de clés étrangères
SET FOREIGN_KEY_CHECKS=0;

-- Mettre à jour la table admin
ALTER TABLE `admin` CHANGE roles roles JSON NOT NULL;

-- Mettre à jour la table bunker
ALTER TABLE bunker CHANGE BEnum BEnum VARCHAR(50) DEFAULT NULL, 
                   CHANGE BEtat BEtat VARCHAR(255) DEFAULT 'Pas de retard' NOT NULL, 
                   CHANGE BCreation BCreation DATETIME DEFAULT CURRENT_TIMESTAMP;

-- Mettre à jour la table personnel
ALTER TABLE personnel CHANGE Id_Site Id_Site INT NOT NULL, 
                      CHANGE roles roles JSON NOT NULL;

-- Mettre à jour la table site
ALTER TABLE site CHANGE SLieu SLieu VARCHAR(50) DEFAULT NULL;

-- Mettre à jour la table messenger_messages
ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)';

-- Réactiver les vérifications de clés étrangères
SET FOREIGN_KEY_CHECKS=1;
