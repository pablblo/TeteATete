package fr.innowave.teteatete.dto;

import java.time.LocalDate;
import java.time.LocalTime;

public record CourseDto(
        Integer idCours,
        String titre,
        LocalDate date,
        LocalTime heure,
        Integer taille,
        Integer placesRestantsTuteur,
        Integer placesRestantsEleve
) {
}
