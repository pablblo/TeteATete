package fr.innowave.teteatete.repository;

import fr.innowave.teteatete.model.Cours;
import org.springframework.data.jpa.repository.JpaRepository;

public interface CoursRepository extends JpaRepository<Cours, Integer> {
}
