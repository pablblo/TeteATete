package fr.innowave.teteatete.controller;

import fr.innowave.teteatete.dto.ApiMessage;
import fr.innowave.teteatete.service.UserService;
import java.util.Map;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/admin")
public class AdminController {

    private final UserService userService;

    public AdminController(UserService userService) {
        this.userService = userService;
    }

    @DeleteMapping("/users/{idUser}")
    public ResponseEntity<ApiMessage> deleteUser(@PathVariable Integer idUser) {
        userService.deleteUser(idUser);
        return ResponseEntity.ok(ApiMessage.ok("Utilisateur supprimé."));
    }

    @PostMapping("/users/{idUser}/admin-status")
    public ResponseEntity<ApiMessage> updateAdminStatus(
            @PathVariable Integer idUser,
            @RequestBody Map<String, Boolean> body
    ) {
        boolean admin = Boolean.TRUE.equals(body.get("admin"));
        userService.updateAdminStatus(idUser, admin);
        return ResponseEntity.ok(ApiMessage.ok("Statut administrateur mis à jour."));
    }

    @PostMapping("/users/{idUser}/warnings")
    public ResponseEntity<ApiMessage> sendWarning(
            @PathVariable Integer idUser,
            @RequestBody Map<String, String> body
    ) {
        userService.sendWarning(idUser, body.getOrDefault("motif", ""));
        return ResponseEntity.ok(ApiMessage.ok("Avertissement enregistré."));
    }
}
