package fr.innowave.teteatete.model;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.EnumType;
import jakarta.persistence.Enumerated;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.GenerationType;
import jakarta.persistence.Id;
import jakarta.persistence.Table;
import java.time.LocalDateTime;

@Entity
@Table(name = "inscription")
public class Inscription {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "idInscription")
    private Integer idInscription;

    @Column(name = "idCours", nullable = false)
    private Integer idCours;

    @Column(name = "idUser", nullable = false)
    private Integer idUser;

    @Column(name = "Date_Inscription")
    private LocalDateTime dateInscription;

    @Enumerated(EnumType.STRING)
    @Column(name = "role", nullable = false)
    private InscriptionRole role;

    public Integer getIdInscription() {
        return idInscription;
    }

    public void setIdInscription(Integer idInscription) {
        this.idInscription = idInscription;
    }

    public Integer getIdCours() {
        return idCours;
    }

    public void setIdCours(Integer idCours) {
        this.idCours = idCours;
    }

    public Integer getIdUser() {
        return idUser;
    }

    public void setIdUser(Integer idUser) {
        this.idUser = idUser;
    }

    public LocalDateTime getDateInscription() {
        return dateInscription;
    }

    public void setDateInscription(LocalDateTime dateInscription) {
        this.dateInscription = dateInscription;
    }

    public InscriptionRole getRole() {
        return role;
    }

    public void setRole(InscriptionRole role) {
        this.role = role;
    }
}
