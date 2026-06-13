package fr.innowave.teteatete.service;

import fr.innowave.teteatete.dto.CourseDto;
import fr.innowave.teteatete.dto.CourseSummaryDto;
import fr.innowave.teteatete.dto.CreateCourseRequest;
import fr.innowave.teteatete.dto.UpdateCourseRequest;
import fr.innowave.teteatete.model.Cours;
import fr.innowave.teteatete.model.Inscription;
import fr.innowave.teteatete.model.InscriptionRole;
import fr.innowave.teteatete.repository.CoursRepository;
import fr.innowave.teteatete.repository.InscriptionRepository;
import fr.innowave.teteatete.util.UserMapper;
import java.util.ArrayList;
import java.util.List;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

@Service
public class CourseService {

    private final CoursRepository coursRepository;
    private final InscriptionRepository inscriptionRepository;

    public CourseService(CoursRepository coursRepository, InscriptionRepository inscriptionRepository) {
        this.coursRepository = coursRepository;
        this.inscriptionRepository = inscriptionRepository;
    }

    public List<CourseSummaryDto> getCoursesForUser(Integer userId) {
        List<Inscription> inscriptions = inscriptionRepository.findByIdUser(userId);
        List<CourseSummaryDto> result = new ArrayList<>();

        for (Inscription inscription : inscriptions) {
            Cours cours = coursRepository.findById(inscription.getIdCours()).orElse(null);
            if (cours == null) {
                continue;
            }
            long participants = inscriptionRepository.countByIdCoursAndRole(cours.getIdCours(), InscriptionRole.eleve)
                    + inscriptionRepository.countByIdCoursAndRole(cours.getIdCours(), InscriptionRole.instructeur);
            result.add(new CourseSummaryDto(
                    cours.getIdCours(),
                    cours.getTitre(),
                    participants,
                    UserMapper.displayRole(inscription.getRole().name())
            ));
        }

        return result;
    }

    public List<CourseDto> getAllCourses() {
        return coursRepository.findAll().stream().map(this::toDto).toList();
    }

    public CourseDto getCourse(Integer idCours) {
        Cours cours = coursRepository.findById(idCours)
                .orElseThrow(() -> new IllegalArgumentException("Cours non trouvé"));
        return toDto(cours);
    }

    public String getCourseTitle(Integer idCours) {
        return getCourse(idCours).titre();
    }

    @Transactional
    public CourseDto createCourse(CreateCourseRequest request) {
        Cours cours = new Cours();
        cours.setTitre(request.titre());
        cours.setDate(request.date());
        cours.setHeure(request.heure());
        cours.setTaille(request.participants());
        cours.setPlacesRestantsEleve(request.participants());
        cours.setPlacesRestantsTuteur(1);
        return toDto(coursRepository.save(cours));
    }

    @Transactional
    public CourseDto updateCourse(Integer idCours, UpdateCourseRequest request) {
        Cours cours = coursRepository.findById(idCours)
                .orElseThrow(() -> new IllegalArgumentException("Cours non trouvé"));
        cours.setTitre(request.titre());
        cours.setDate(request.date());
        cours.setHeure(request.heure());
        return toDto(coursRepository.save(cours));
    }

    @Transactional
    public void deleteCourse(Integer idCours) {
        if (!coursRepository.existsById(idCours)) {
            throw new IllegalArgumentException("Cours non trouvé");
        }
        coursRepository.deleteById(idCours);
    }

    @Transactional
    public void removeParticipant(Integer idCours, Integer idUser) {
        inscriptionRepository.deleteByIdCoursAndIdUser(idCours, idUser);
    }

    private CourseDto toDto(Cours cours) {
        return new CourseDto(
                cours.getIdCours(),
                cours.getTitre(),
                cours.getDate(),
                cours.getHeure(),
                cours.getTaille(),
                cours.getPlacesRestantsTuteur(),
                cours.getPlacesRestantsEleve()
        );
    }
}
