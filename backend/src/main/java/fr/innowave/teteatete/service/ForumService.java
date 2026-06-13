package fr.innowave.teteatete.service;

import fr.innowave.teteatete.dto.ForumPostDto;
import fr.innowave.teteatete.model.ForumPost;
import fr.innowave.teteatete.model.User;
import fr.innowave.teteatete.repository.ForumRepository;
import fr.innowave.teteatete.repository.UserRepository;
import java.time.LocalDateTime;
import java.util.ArrayList;
import java.util.List;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

@Service
public class ForumService {

    private final ForumRepository forumRepository;
    private final UserRepository userRepository;

    public ForumService(ForumRepository forumRepository, UserRepository userRepository) {
        this.forumRepository = forumRepository;
        this.userRepository = userRepository;
    }

    public List<ForumPostDto> getAllPosts() {
        return mapPosts(forumRepository.findAllByOrderByCreatedAtDesc());
    }

    public List<ForumPostDto> getUnansweredPosts() {
        return mapPosts(forumRepository.findByAnswerIsNullOrderByCreatedAtDesc());
    }

    @Transactional
    public ForumPostDto createQuestion(Integer userId, String question) {
        ForumPost post = new ForumPost();
        post.setUserId(userId);
        post.setQuestion(question.trim());
        post.setCreatedAt(LocalDateTime.now());
        ForumPost saved = forumRepository.save(post);
        return mapPost(saved);
    }

    @Transactional
    public ForumPostDto saveAnswer(Integer postId, String answer) {
        ForumPost post = forumRepository.findById(postId)
                .orElseThrow(() -> new IllegalArgumentException("Question introuvable"));
        post.setAnswer(answer.trim());
        return mapPost(forumRepository.save(post));
    }

    @Transactional
    public ForumPostDto editAnswer(Integer postId, String answer) {
        return saveAnswer(postId, answer);
    }

    @Transactional
    public void deleteQuestion(Integer postId) {
        if (!forumRepository.existsById(postId)) {
            throw new IllegalArgumentException("Question introuvable");
        }
        forumRepository.deleteById(postId);
    }

    private List<ForumPostDto> mapPosts(List<ForumPost> posts) {
        List<ForumPostDto> result = new ArrayList<>();
        for (ForumPost post : posts) {
            result.add(mapPost(post));
        }
        return result;
    }

    private ForumPostDto mapPost(ForumPost post) {
        User user = userRepository.findById(post.getUserId()).orElse(null);
        return new ForumPostDto(
                post.getId(),
                post.getUserId(),
                user != null ? user.getPrenom() : null,
                user != null ? user.getNom() : null,
                post.getQuestion(),
                post.getAnswer(),
                post.getCreatedAt()
        );
    }
}
