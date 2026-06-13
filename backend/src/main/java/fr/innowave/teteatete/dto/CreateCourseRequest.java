package fr.innowave.teteatete.dto;

import java.time.LocalDate;
import java.time.LocalTime;

public record CreateCourseRequest(
        String titre,
        LocalDate date,
        LocalTime heure,
        Integer participants
) {
}
