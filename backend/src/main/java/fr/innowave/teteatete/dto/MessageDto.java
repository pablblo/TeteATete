package fr.innowave.teteatete.dto;

import com.fasterxml.jackson.annotation.JsonProperty;
import java.time.LocalDateTime;

public record MessageDto(
        Integer idMessage,
        String message,
        LocalDateTime timestamp,
        @JsonProperty("Nom") String nom,
        @JsonProperty("Prenom") String prenom,
        @JsonProperty("Photo_de_Profil") String photoDeProfil,
        String role
) {
}
