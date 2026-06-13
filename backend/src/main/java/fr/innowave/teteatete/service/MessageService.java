package fr.innowave.teteatete.service;

import fr.innowave.teteatete.dto.MessageDto;
import fr.innowave.teteatete.dto.SendMessageRequest;
import fr.innowave.teteatete.model.Inscription;
import fr.innowave.teteatete.model.Message;
import fr.innowave.teteatete.model.User;
import fr.innowave.teteatete.repository.CoursRepository;
import fr.innowave.teteatete.repository.InscriptionRepository;
import fr.innowave.teteatete.repository.MessageRepository;
import fr.innowave.teteatete.repository.UserRepository;
import fr.innowave.teteatete.util.UserMapper;
import java.time.LocalDateTime;
import java.util.ArrayList;
import java.util.List;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

@Service
public class MessageService {

    private final MessageRepository messageRepository;
    private final UserRepository userRepository;
    private final CoursRepository coursRepository;
    private final InscriptionRepository inscriptionRepository;

    public MessageService(
            MessageRepository messageRepository,
            UserRepository userRepository,
            CoursRepository coursRepository,
            InscriptionRepository inscriptionRepository
    ) {
        this.messageRepository = messageRepository;
        this.userRepository = userRepository;
        this.coursRepository = coursRepository;
        this.inscriptionRepository = inscriptionRepository;
    }

    public List<MessageDto> getMessagesByCourse(Integer idCours) {
        List<Message> messages = messageRepository.findByIdCoursOrderByTimestampAsc(idCours);
        List<MessageDto> result = new ArrayList<>();

        for (Message message : messages) {
            User user = userRepository.findById(message.getIdUser()).orElse(null);
            if (user == null) {
                continue;
            }
            Inscription inscription = inscriptionRepository
                    .findByIdCoursAndIdUser(idCours, message.getIdUser())
                    .orElse(null);
            String role = inscription != null ? inscription.getRole().name() : "eleve";
            result.add(new MessageDto(
                    message.getIdMessage(),
                    message.getMessage(),
                    message.getTimestamp(),
                    user.getNom(),
                    user.getPrenom(),
                    UserMapper.encodePhoto(user.getPhotoDeProfil()),
                    role
            ));
        }

        return result;
    }

    @Transactional
    public MessageDto sendMessage(Integer userId, SendMessageRequest request) {
        if (!coursRepository.existsById(request.idCours())) {
            throw new IllegalArgumentException("idCours invalide.");
        }

        Message message = new Message();
        message.setIdCours(request.idCours());
        message.setIdUser(userId);
        message.setMessage(request.message().trim());
        message.setTimestamp(LocalDateTime.now());
        Message saved = messageRepository.save(message);

        User user = userRepository.findById(userId)
                .orElseThrow(() -> new IllegalArgumentException("Utilisateur introuvable"));
        Inscription inscription = inscriptionRepository.findByIdCoursAndIdUser(request.idCours(), userId).orElse(null);
        String role = inscription != null ? inscription.getRole().name() : "eleve";

        return new MessageDto(
                saved.getIdMessage(),
                saved.getMessage(),
                saved.getTimestamp(),
                user.getNom(),
                user.getPrenom(),
                UserMapper.encodePhoto(user.getPhotoDeProfil()),
                role
        );
    }
}
