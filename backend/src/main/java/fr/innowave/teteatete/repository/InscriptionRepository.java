package fr.innowave.teteatete.repository;

import fr.innowave.teteatete.model.Inscription;
import fr.innowave.teteatete.model.InscriptionRole;
import java.util.List;
import java.util.Optional;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;

public interface InscriptionRepository extends JpaRepository<Inscription, Integer> {

    List<Inscription> findByIdUser(Integer idUser);

    Optional<Inscription> findByIdCoursAndIdUser(Integer idCours, Integer idUser);

    long countByIdCoursAndRole(Integer idCours, InscriptionRole role);

    void deleteByIdCoursAndIdUser(Integer idCours, Integer idUser);

    @Query("""
            SELECT i FROM Inscription i
            WHERE i.idUser = :idUser
            """)
    List<Inscription> findCoursesForUser(@Param("idUser") Integer idUser);
}
