package fr.innowave.teteatete.dto;

import java.time.LocalDate;
import java.time.LocalTime;

public record UpdateCourseRequest(
        String titre,
        LocalDate date,
        LocalTime heure
) {
}
