package fr.innowave.teteatete.controller;

import fr.innowave.teteatete.dto.UserProfileDto;
import fr.innowave.teteatete.security.SecurityUtils;
import fr.innowave.teteatete.service.UserService;
import java.util.List;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/users")
public class UserController {

    private final UserService userService;

    public UserController(UserService userService) {
        this.userService = userService;
    }

    @GetMapping("/me")
    public ResponseEntity<UserProfileDto> getCurrentUser() {
        return ResponseEntity.ok(userService.getProfile(SecurityUtils.currentUserId()));
    }

    @GetMapping("/search")
    public ResponseEntity<List<UserProfileDto>> searchUsers(@RequestParam String query) {
        return ResponseEntity.ok(userService.searchProfiles(query));
    }

    @GetMapping("/{idUser}")
    public ResponseEntity<UserProfileDto> getUser(@PathVariable Integer idUser) {
        return ResponseEntity.ok(userService.getProfile(idUser));
    }
}
