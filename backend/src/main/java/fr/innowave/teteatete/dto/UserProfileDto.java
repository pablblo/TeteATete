package fr.innowave.teteatete.dto;

public record UserProfileDto(
        Integer idUser,
        String nom,
        String prenom,
        String mail,
        String classe,
        String bio,
        String photoDeProfil,
        boolean admin,
        Integer nbAvertissements
) {
}
