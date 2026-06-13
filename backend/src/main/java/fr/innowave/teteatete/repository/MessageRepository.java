package fr.innowave.teteatete.repository;

import fr.innowave.teteatete.model.Message;
import java.util.List;
import org.springframework.data.jpa.repository.JpaRepository;

public interface MessageRepository extends JpaRepository<Message, Integer> {

    List<Message> findByIdCoursOrderByTimestampAsc(Integer idCours);
}
