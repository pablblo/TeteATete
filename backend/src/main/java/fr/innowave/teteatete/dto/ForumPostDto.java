package fr.innowave.teteatete.dto;

import java.time.LocalDateTime;

public record ForumPostDto(
        Integer id,
        Integer userId,
        String prenom,
        String nom,
        String question,
        String answer,
        LocalDateTime createdAt
) {
}
