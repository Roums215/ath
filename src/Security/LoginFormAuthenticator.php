<?php

namespace App\Security;

use App\Entity\Admin;
use App\Entity\Personnel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\HttpFoundation\Session\Session;
use Psr\Log\LoggerInterface;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'login';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function authenticate(Request $request): Passport
    {
        // Récupération des données du formulaire
        $username = $request->request->get('_username', '');
        $password = $request->request->get('_password', '');
        $userType = $request->request->get('user_type', 'admin'); // Par défaut on cherche un admin
        
        // Stockage en session pour debug et utilisation ultérieure
        $request->getSession()->set('user_type', $userType);
        $request->getSession()->set('_security.last_username', $username);
        $request->getSession()->set('debug_auth_attempt', [
            'time' => new \DateTime(),
            'username' => $username,
            'user_type' => $userType,
            'password_length' => strlen($password),
            'request_method' => $request->getMethod(),
        ]);
        
        // Création d'un UserBadge avec recherche adaptée au type d'utilisateur
        $userBadge = new UserBadge($username, function($userIdentifier) use ($userType) {
            // Recherche d'utilisateur selon le type sélectionné
            $user = null;
            
            if ($userType === 'admin') {
                // IMPORTANT: Pour Admin, lors de la connexion, on s'attend à ce que le nom d'utilisateur soit exactement 'Admin'
                // et on attend directement la valeur exacte de ANom
                $user = $this->entityManager->getRepository(Admin::class)->findOneBy(['anom' => $userIdentifier]);
                
                // Si pas trouvé avec correspondance exacte, essai avec insensibilité à la casse
                if (!$user) {
                    $user = $this->entityManager->createQueryBuilder()
                        ->select('a')
                        ->from(Admin::class, 'a')
                        ->where('LOWER(a.anom) = LOWER(:identifier)')
                        ->setParameter('identifier', $userIdentifier)
                        ->getQuery()
                        ->getOneOrNullResult();
                }
                
                // Si toujours pas trouvé, comme dernier recours, on cherche tous les admins
                if (!$user && $userIdentifier === 'Admin') {
                    $user = $this->entityManager->getRepository(Admin::class)->findOneBy([]);
                }
            } else {
                // Pour Personnel, dans la migration, l'utilisateur créé a PNom = 'Doe'
                // Donc si on reçoit 'personnel', on cherche un utilisateur avec PNom = 'Doe'
                if (strtolower($userIdentifier) === 'personnel') {
                    $user = $this->entityManager->getRepository(Personnel::class)->findOneBy(['pNom' => 'Doe']);
                } else {
                    // Sinon recherche normale
                    $user = $this->entityManager->getRepository(Personnel::class)->findOneBy(['pNom' => $userIdentifier]);
                    
                    // Si pas trouvé, essai insensible à la casse
                    if (!$user) {
                        $user = $this->entityManager->createQueryBuilder()
                            ->select('p')
                            ->from(Personnel::class, 'p')
                            ->where('LOWER(p.pNom) = LOWER(:identifier)')
                            ->setParameter('identifier', $userIdentifier)
                            ->getQuery()
                            ->getOneOrNullResult();
                    }
                }
            }
            
            // Si on ne trouve pas l'utilisateur, on lance une exception
            if (!$user) {
                throw new CustomUserMessageAuthenticationException('Identifiants invalides. Essayez Admin/Admin123! pour admin ou Doe/Personnel123! pour personnel.');
            }
            
            return $user;
        });
        
        // Retourne le Passport avec les identifiants sans vérification CSRF
        return new Passport(
            $userBadge,
            new PasswordCredentials($password),
            [] // Pas de badges CSRF
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $token->getUser();
        $isProduction = $_SERVER['APP_ENV'] === 'prod' || getenv('APP_ENV') === 'prod';
        
        // En production, forcer le schéma HTTPS pour toutes les redirections
        $referenceType = $isProduction ? 
            UrlGeneratorInterface::ABSOLUTE_URL : 
            UrlGeneratorInterface::ABSOLUTE_PATH;
        
        // Redirection vers la page précédemment demandée si elle existe
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            // S'assurer que le targetPath utilise HTTPS si nous sommes en production
            if ($isProduction && strpos($targetPath, 'http://') === 0) {
                $targetPath = str_replace('http://', 'https://', $targetPath);
            }
            return new RedirectResponse($targetPath);
        }
        
        // Redirection spécifique selon le type d'utilisateur
        try {
            if ($user instanceof \App\Entity\Admin) {
                // Route d'accueil admin (EasyAdmin) - forcer URL absolue avec HTTPS en prod
                return new RedirectResponse($this->urlGenerator->generate('admin', [], $referenceType));
            } elseif ($user instanceof \App\Entity\Personnel) {
                // Route d'accueil personnel - forcer URL absolue avec HTTPS en prod
                return new RedirectResponse($this->urlGenerator->generate('personnel_dashboard_home', [], $referenceType));
            }
        } catch (\Exception $e) {
            // Si la génération de la route échoue, enregistrer pour débogage
            error_log('Erreur lors de la redirection après authentification: ' . $e->getMessage());
        }
        
        // Fallback ultime : page d'accueil ou login
        // En production, on construit une URL complète HTTPS
        if ($isProduction) {
            return new RedirectResponse('https://' . $request->getHost() . '/');
        } else {
            return new RedirectResponse('/');
        }
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
