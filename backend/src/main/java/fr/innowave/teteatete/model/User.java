package fr.innowave.teteatete.model;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.GenerationType;
import jakarta.persistence.Id;
import jakarta.persistence.Lob;
import jakarta.persistence.Table;

@Entity
@Table(name = "user")
public class User {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "idUser")
    private Integer idUser;

    @Column(name = "Nom")
    private String nom;

    @Column(name = "Prenom")
    private String prenom;

    @Column(name = "Mail", unique = true)
    private String mail;

    @Column(name = "Mot_de_passe", nullable = false)
    private String motDePasse;

    @Column(name = "Classe")
    private String classe;

    @Lob
    @Column(name = "Photo_de_Profil")
    private byte[] photoDeProfil;

    @Column(name = "Admin")
    private Integer admin = 0;

    @Column(name = "reset_token")
    private String resetToken;

    @Column(name = "Bio")
    private String bio;

    @Column(name = "nbAvertissements")
    private Integer nbAvertissements = 0;

    public Integer getIdUser() {
        return idUser;
    }

    public void setIdUser(Integer idUser) {
        this.idUser = idUser;
    }

    public String getNom() {
        return nom;
    }

    public void setNom(String nom) {
        this.nom = nom;
    }

    public String getPrenom() {
        return prenom;
    }

    public void setPrenom(String prenom) {
        this.prenom = prenom;
    }

    public String getMail() {
        return mail;
    }

    public void setMail(String mail) {
        this.mail = mail;
    }

    public String getMotDePasse() {
        return motDePasse;
    }

    public void setMotDePasse(String motDePasse) {
        this.motDePasse = motDePasse;
    }

    public String getClasse() {
        return classe;
    }

    public void setClasse(String classe) {
        this.classe = classe;
    }

    public byte[] getPhotoDeProfil() {
        return photoDeProfil;
    }

    public void setPhotoDeProfil(byte[] photoDeProfil) {
        this.photoDeProfil = photoDeProfil;
    }

    public Integer getAdmin() {
        return admin;
    }

    public void setAdmin(Integer admin) {
        this.admin = admin;
    }

    public String getResetToken() {
        return resetToken;
    }

    public void setResetToken(String resetToken) {
        this.resetToken = resetToken;
    }

    public String getBio() {
        return bio;
    }

    public void setBio(String bio) {
        this.bio = bio;
    }

    public Integer getNbAvertissements() {
        return nbAvertissements;
    }

    public void setNbAvertissements(Integer nbAvertissements) {
        this.nbAvertissements = nbAvertissements;
    }

    public boolean isAdmin() {
        return admin != null && admin == 1;
    }
}
