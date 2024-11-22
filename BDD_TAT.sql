-- MySQL Script updated

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

CREATE SCHEMA IF NOT EXISTS `BDD_TAT` DEFAULT CHARACTER SET utf8 ;
USE `BDD_TAT`;

-- -----------------------------------------------------
-- Table `BDD_TAT`.`User`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `BDD_TAT`.`User` (
  `idUser` INT NOT NULL AUTO_INCREMENT,
  `Nom` VARCHAR(45) NULL,
  `Prenom` VARCHAR(45) NULL,
  `Mail` VARCHAR(45) NULL UNIQUE,
  `Mot_de_passe` VARCHAR(255) NOT NULL,
  `Classe` VARCHAR(45) NULL,
  `Photo_de_Profil` BLOB NULL,
  `Admin` TINYINT NULL DEFAULT 0,
  `Bio` VARCHAR(255) NULL,
  PRIMARY KEY (`idUser`))
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `BDD_TAT`.`Evaluation`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `BDD_TAT`.`Evaluation` (
  `idEvaluation` INT NOT NULL AUTO_INCREMENT,
  `Tuteur_ou_Eleve` TINYINT NULL,
  `Note` TINYINT NULL,
  `Commentaire` VARCHAR(200) NULL,
  `idUserAuteur` INT NOT NULL,
  `idUserReceveur` INT NOT NULL,
  PRIMARY KEY (`idEvaluation`),
  INDEX `fk_Evaluation_User1_idx` (`idUserReceveur` ASC),
  CONSTRAINT `fk_UserReceveur`
    FOREIGN KEY (`idUserReceveur`)
    REFERENCES `BDD_TAT`.`User` (`idUser`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_UserAuteur`
    FOREIGN KEY (`idUserAuteur`)
    REFERENCES `BDD_TAT`.`User` (`idUser`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `BDD_TAT`.`Cours`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `BDD_TAT`.`Cours` (
  `idCours` INT NOT NULL AUTO_INCREMENT,
  `Titre` VARCHAR(45) NULL,
  `Date` DATE NULL,
  `Heure` TIME NULL,
  `Taille` INT NULL,
  `Places_restants_Tuteur` INT NULL,
  `Places_restants_Eleve` INT NULL,
  PRIMARY KEY (`idCours`))
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `BDD_TAT`.`Message_Contact`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `BDD_TAT`.`Message_Contact` (
  `idMessage_Contact` INT NOT NULL AUTO_INCREMENT,
  `Mail` VARCHAR(100) NULL,
  `Message` VARCHAR(250) NULL,
  `idUserAuteur` INT NOT NULL,
  `idUserReceveur` INT NOT NULL,
  PRIMARY KEY (`idMessage_Contact`),
  INDEX `fk_idUserAuteur_idx` (`idUserAuteur` ASC),
  INDEX `fk_idUserReceveur_idx` (`idUserReceveur` ASC),
  CONSTRAINT `fk_idUserAuteur`
    FOREIGN KEY (`idUserAuteur`)
    REFERENCES `BDD_TAT`.`User` (`idUser`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_idUserReceveur`
    FOREIGN KEY (`idUserReceveur`)
    REFERENCES `BDD_TAT`.`User` (`idUser`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `BDD_TAT`.`User_Cours`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `BDD_TAT`.`User_Cours` (
  `idUser_Cours` INT NOT NULL AUTO_INCREMENT,
  `Tuteur_ou_Eleve` TINYINT NULL,
  `idUser` INT NOT NULL,
  `idCours` INT NOT NULL,
  PRIMARY KEY (`idUser_Cours`),
  INDEX `fk_idUser_idx` (`idUser` ASC),
  INDEX `fk_idCours_idx` (`idCours` ASC),
  CONSTRAINT `fk_idUser`
    FOREIGN KEY (`idUser`)
    REFERENCES `BDD_TAT`.`User` (`idUser`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_idCours`
    FOREIGN KEY (`idCours`)
    REFERENCES `BDD_TAT`.`Cours` (`idCours`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
