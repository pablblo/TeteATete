package fr.innowave.teteatete.controller;

import fr.innowave.teteatete.dto.MessageDto;
import fr.innowave.teteatete.dto.SendMessageRequest;
import fr.innowave.teteatete.security.SecurityUtils;
import fr.innowave.teteatete.service.MessageService;
import jakarta.validation.Valid;
import java.util.List;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/messages")
public class MessageController {

    private final MessageService messageService;

    public MessageController(MessageService messageService) {
        this.messageService = messageService;
    }

    @GetMapping
    public ResponseEntity<List<MessageDto>> getMessages(@RequestParam Integer idCours) {
        return ResponseEntity.ok(messageService.getMessagesByCourse(idCours));
    }

    @PostMapping
    public ResponseEntity<MessageDto> sendMessage(@Valid @RequestBody SendMessageRequest request) {
        return ResponseEntity.ok(messageService.sendMessage(SecurityUtils.currentUserId(), request));
    }
}
