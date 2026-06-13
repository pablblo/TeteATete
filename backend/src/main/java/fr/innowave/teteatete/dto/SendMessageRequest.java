package fr.innowave.teteatete.dto;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;

public record SendMessageRequest(
        @NotNull Integer idCours,
        @NotBlank String message
) {
}
