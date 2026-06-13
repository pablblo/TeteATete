package fr.innowave.teteatete.repository;

import fr.innowave.teteatete.model.ForumPost;
import java.util.List;
import org.springframework.data.jpa.repository.JpaRepository;

public interface ForumRepository extends JpaRepository<ForumPost, Integer> {

    List<ForumPost> findByAnswerIsNullOrderByCreatedAtDesc();

    List<ForumPost> findAllByOrderByCreatedAtDesc();
}
