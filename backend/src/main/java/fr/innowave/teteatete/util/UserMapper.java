package fr.innowave.teteatete.util;

import fr.innowave.teteatete.model.User;
import java.util.Base64;

public final class UserMapper {

    private UserMapper() {
    }

    public static String encodePhoto(byte[] photo) {
        if (photo == null || photo.length == 0) {
            return null;
        }
        return Base64.getEncoder().encodeToString(photo);
    }

    public static String displayRole(String role) {
        if ("instructeur".equalsIgnoreCase(role)) {
            return "Tuteur";
        }
        return "Élève";
    }
}
