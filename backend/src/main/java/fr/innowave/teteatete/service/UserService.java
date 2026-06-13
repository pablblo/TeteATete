package fr.innowave.teteatete.service;

import fr.innowave.teteatete.dto.UserProfileDto;
import fr.innowave.teteatete.model.User;
import fr.innowave.teteatete.repository.UserRepository;
import fr.innowave.teteatete.util.UserMapper;
import java.util.List;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

@Service
public class UserService {

    private final UserRepository userRepository;

    public UserService(UserRepository userRepository) {
        this.userRepository = userRepository;
    }

    public UserProfileDto getProfile(Integer userId) {
        User user = userRepository.findById(userId)
                .orElseThrow(() -> new IllegalArgumentException("Utilisateur introuvable"));
        return toDto(user);
    }

    public List<UserProfileDto> searchProfiles(String query) {
        return userRepository.searchByFullName(query).stream().map(this::toDto).toList();
    }

    @Transactional
    public void deleteUser(Integer userId) {
        if (!userRepository.existsById(userId)) {
            throw new IllegalArgumentException("Utilisateur introuvable");
        }
        userRepository.deleteById(userId);
    }

    @Transactional
    public void updateAdminStatus(Integer userId, boolean admin) {
        User user = userRepository.findById(userId)
                .orElseThrow(() -> new IllegalArgumentException("Utilisateur introuvable"));
        user.setAdmin(admin ? 1 : 0);
        userRepository.save(user);
    }

    @Transactional
    public void sendWarning(Integer userId, String motif) {
        User user = userRepository.findById(userId)
                .orElseThrow(() -> new IllegalArgumentException("Utilisateur introuvable"));
        int warnings = user.getNbAvertissements() == null ? 0 : user.getNbAvertissements();
        user.setNbAvertissements(warnings + 1);
        userRepository.save(user);
    }

    private UserProfileDto toDto(User user) {
        return new UserProfileDto(
                user.getIdUser(),
                user.getNom(),
                user.getPrenom(),
                user.getMail(),
                user.getClasse(),
                user.getBio(),
                UserMapper.encodePhoto(user.getPhotoDeProfil()),
                user.isAdmin(),
                user.getNbAvertissements()
        );
    }
}
