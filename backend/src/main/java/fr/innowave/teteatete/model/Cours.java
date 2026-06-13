package fr.innowave.teteatete.model;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.GenerationType;
import jakarta.persistence.Id;
import jakarta.persistence.Table;
import java.time.LocalDate;
import java.time.LocalTime;

@Entity
@Table(name = "cours")
public class Cours {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "idCours")
    private Integer idCours;

    @Column(name = "Titre")
    private String titre;

    @Column(name = "Date")
    private LocalDate date;

    @Column(name = "Heure")
    private LocalTime heure;

    @Column(name = "Taille")
    private Integer taille;

    @Column(name = "Places_restants_Tuteur")
    private Integer placesRestantsTuteur;

    @Column(name = "Places_restants_Eleve")
    private Integer placesRestantsEleve;

    public Integer getIdCours() {
        return idCours;
    }

    public void setIdCours(Integer idCours) {
        this.idCours = idCours;
    }

    public String getTitre() {
        return titre;
    }

    public void setTitre(String titre) {
        this.titre = titre;
    }

    public LocalDate getDate() {
        return date;
    }

    public void setDate(LocalDate date) {
        this.date = date;
    }

    public LocalTime getHeure() {
        return heure;
    }

    public void setHeure(LocalTime heure) {
        this.heure = heure;
    }

    public Integer getTaille() {
        return taille;
    }

    public void setTaille(Integer taille) {
        this.taille = taille;
    }

    public Integer getPlacesRestantsTuteur() {
        return placesRestantsTuteur;
    }

    public void setPlacesRestantsTuteur(Integer placesRestantsTuteur) {
        this.placesRestantsTuteur = placesRestantsTuteur;
    }

    public Integer getPlacesRestantsEleve() {
        return placesRestantsEleve;
    }

    public void setPlacesRestantsEleve(Integer placesRestantsEleve) {
        this.placesRestantsEleve = placesRestantsEleve;
    }
}
