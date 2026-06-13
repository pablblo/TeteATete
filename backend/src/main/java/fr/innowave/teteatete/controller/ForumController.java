package fr.innowave.teteatete.controller;

import fr.innowave.teteatete.dto.ForumPostDto;
import fr.innowave.teteatete.security.SecurityUtils;
import fr.innowave.teteatete.service.ForumService;
import java.util.List;
import java.util.Map;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.PutMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/forum")
public class ForumController {

    private final ForumService forumService;

    public ForumController(ForumService forumService) {
        this.forumService = forumService;
    }

    @GetMapping
    public ResponseEntity<List<ForumPostDto>> getAllPosts() {
        return ResponseEntity.ok(forumService.getAllPosts());
    }

    @GetMapping("/unanswered")
    public ResponseEntity<List<ForumPostDto>> getUnansweredPosts() {
        return ResponseEntity.ok(forumService.getUnansweredPosts());
    }

    @PostMapping
    public ResponseEntity<ForumPostDto> createQuestion(@RequestBody Map<String, String> body) {
        String question = body.get("question");
        if (question == null || question.isBlank()) {
            throw new IllegalArgumentException("La question est obligatoire.");
        }
        return ResponseEntity.ok(forumService.createQuestion(SecurityUtils.currentUserId(), question));
    }

    @PutMapping("/{postId}/answer")
    public ResponseEntity<ForumPostDto> saveAnswer(
            @PathVariable Integer postId,
            @RequestBody Map<String, String> body
    ) {
        return ResponseEntity.ok(forumService.saveAnswer(postId, body.get("answer")));
    }

    @PutMapping("/{postId}/edit-answer")
    public ResponseEntity<ForumPostDto> editAnswer(
            @PathVariable Integer postId,
            @RequestBody Map<String, String> body
    ) {
        return ResponseEntity.ok(forumService.editAnswer(postId, body.get("answer")));
    }

    @DeleteMapping("/{postId}")
    public ResponseEntity<Map<String, Object>> deleteQuestion(@PathVariable Integer postId) {
        forumService.deleteQuestion(postId);
        return ResponseEntity.ok(Map.of("success", true));
    }
}
