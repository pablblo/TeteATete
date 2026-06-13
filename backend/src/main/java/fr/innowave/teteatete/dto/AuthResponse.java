package fr.innowave.teteatete.dto;

public record AuthResponse(
        String token,
        Integer userId,
        String nom,
        String prenom,
        boolean admin
) {
}
