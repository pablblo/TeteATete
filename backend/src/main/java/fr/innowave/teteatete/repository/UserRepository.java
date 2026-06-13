package fr.innowave.teteatete.repository;

import fr.innowave.teteatete.model.User;
import java.util.List;
import java.util.Optional;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;

public interface UserRepository extends JpaRepository<User, Integer> {

    Optional<User> findByMail(String mail);

    @Query("""
            SELECT u FROM User u
            WHERE CONCAT(u.prenom, ' ', u.nom) LIKE CONCAT('%', :query, '%')
            """)
    List<User> searchByFullName(@Param("query") String query);
}
