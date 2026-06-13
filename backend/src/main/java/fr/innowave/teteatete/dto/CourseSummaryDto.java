package fr.innowave.teteatete.dto;

public record CourseSummaryDto(
        Integer idCours,
        String titre,
        Long participants,
        String role
) {
}
