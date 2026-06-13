package fr.innowave.teteatete.controller;

import fr.innowave.teteatete.dto.CourseDto;
import fr.innowave.teteatete.dto.CourseSummaryDto;
import fr.innowave.teteatete.dto.CreateCourseRequest;
import fr.innowave.teteatete.dto.UpdateCourseRequest;
import fr.innowave.teteatete.security.SecurityUtils;
import fr.innowave.teteatete.service.CourseService;
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
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/courses")
public class CourseController {

    private final CourseService courseService;

    public CourseController(CourseService courseService) {
        this.courseService = courseService;
    }

    @GetMapping
    public ResponseEntity<List<CourseSummaryDto>> getCoursesForCurrentUser(
            @RequestParam(required = false) Integer idUser
    ) {
        Integer userId = idUser != null ? idUser : SecurityUtils.currentUserId();
        return ResponseEntity.ok(courseService.getCoursesForUser(userId));
    }

    @GetMapping("/all")
    public ResponseEntity<List<CourseDto>> getAllCourses() {
        return ResponseEntity.ok(courseService.getAllCourses());
    }

    @GetMapping("/{idCours}/title")
    public ResponseEntity<Map<String, String>> getCourseTitle(@PathVariable Integer idCours) {
        return ResponseEntity.ok(Map.of("Titre", courseService.getCourseTitle(idCours)));
    }

    @GetMapping("/{idCours}")
    public ResponseEntity<CourseDto> getCourse(@PathVariable Integer idCours) {
        return ResponseEntity.ok(courseService.getCourse(idCours));
    }

    @PostMapping
    public ResponseEntity<CourseDto> createCourse(@RequestBody CreateCourseRequest request) {
        return ResponseEntity.ok(courseService.createCourse(request));
    }

    @PutMapping("/{idCours}")
    public ResponseEntity<CourseDto> updateCourse(
            @PathVariable Integer idCours,
            @RequestBody UpdateCourseRequest request
    ) {
        return ResponseEntity.ok(courseService.updateCourse(idCours, request));
    }

    @DeleteMapping("/{idCours}")
    public ResponseEntity<Map<String, Object>> deleteCourse(@PathVariable Integer idCours) {
        courseService.deleteCourse(idCours);
        return ResponseEntity.ok(Map.of("success", true, "message", "Cours supprimé avec succès."));
    }

    @DeleteMapping("/{idCours}/participants/{idUser}")
    public ResponseEntity<Map<String, Object>> removeParticipant(
            @PathVariable Integer idCours,
            @PathVariable Integer idUser
    ) {
        courseService.removeParticipant(idCours, idUser);
        return ResponseEntity.ok(Map.of("success", true, "message", "Participant retiré."));
    }
}
