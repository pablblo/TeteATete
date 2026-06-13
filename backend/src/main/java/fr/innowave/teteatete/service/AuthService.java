package fr.innowave.teteatete.service;

import fr.innowave.teteatete.dto.AuthResponse;
import fr.innowave.teteatete.dto.LoginRequest;
import fr.innowave.teteatete.dto.RegisterRequest;
import fr.innowave.teteatete.model.User;
import fr.innowave.teteatete.repository.UserRepository;
import fr.innowave.teteatete.security.JwtService;
import fr.innowave.teteatete.security.UserPrincipal;
import org.springframework.security.authentication.AuthenticationManager;
import org.springframework.security.authentication.UsernamePasswordAuthenticationToken;
import org.springframework.security.core.Authentication;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

@Service
public class AuthService {

    private final UserRepository userRepository;
    private final PasswordEncoder passwordEncoder;
    private final JwtService jwtService;
    private final AuthenticationManager authenticationManager;

    public AuthService(
            UserRepository userRepository,
            PasswordEncoder passwordEncoder,
            JwtService jwtService,
            AuthenticationManager authenticationManager
    ) {
        this.userRepository = userRepository;
        this.passwordEncoder = passwordEncoder;
        this.jwtService = jwtService;
        this.authenticationManager = authenticationManager;
    }

    public AuthResponse login(LoginRequest request) {
        Authentication authentication = authenticationManager.authenticate(
                new UsernamePasswordAuthenticationToken(request.email(), request.password())
        );
        UserPrincipal principal = (UserPrincipal) authentication.getPrincipal();
        User user = principal.getUser();
        String token = jwtService.generateToken(principal);
        return new AuthResponse(token, user.getIdUser(), user.getNom(), user.getPrenom(), user.isAdmin());
    }

    @Transactional
    public AuthResponse register(RegisterRequest request) {
        if (userRepository.findByMail(request.email()).isPresent()) {
            throw new IllegalArgumentException("Cet email est déjà utilisé.");
        }

        User user = new User();
        user.setNom(request.nom());
        user.setPrenom(request.prenom());
        user.setMail(request.email());
        user.setMotDePasse(passwordEncoder.encode(request.password()));
        user.setClasse(request.classe());
        user.setAdmin(0);
        user.setNbAvertissements(0);
        userRepository.save(user);

        UserPrincipal principal = new UserPrincipal(user);
        String token = jwtService.generateToken(principal);
        return new AuthResponse(token, user.getIdUser(), user.getNom(), user.getPrenom(), user.isAdmin());
    }
}
