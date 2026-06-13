package fr.innowave.teteatete.dto;

import jakarta.validation.constraints.NotBlank;

public record ApiMessage(
        boolean success,
        String message
) {
    public static ApiMessage ok(String message) {
        return new ApiMessage(true, message);
    }

    public static ApiMessage error(String message) {
        return new ApiMessage(false, message);
    }
}
