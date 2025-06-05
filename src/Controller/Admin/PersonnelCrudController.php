<?php
// src/Controller/Admin/PersonnelCrudController.php
namespace App\Controller\Admin;

use App\Entity\Personnel;
use App\Entity\Site;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\{Crud, Actions};
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{
    IdField,
    TextField,
    AssociationField,
    ChoiceField
};
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class PersonnelCrudController extends AbstractCrudController
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public static function getEntityFqcn(): string
    {
        return Personnel::class;
    }

    /* ---------- champs ---------- */
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('idPersonnel')->onlyOnIndex();

        yield TextField::new('pNom',    'Nom');
        yield TextField::new('pPrenom', 'Prénom');

        // champ mot de passe non mappé
        yield TextField::new('plainPassword', 'Mot de passe')
            ->setFormType(PasswordType::class)
            ->setFormTypeOption('mapped', false)        // ← remplace setMapped(false)
            ->onlyOnForms()
            ->setRequired($pageName === Crud::PAGE_NEW);

        // Clinique d'appartenance
        yield AssociationField::new('site', 'Clinique');

        // Sélecteur de rôles simple
        yield ChoiceField::new('roles')
            ->allowMultipleChoices()
            ->renderExpanded(false)
            ->setChoices([
                'Personnel'   => 'ROLE_PERSONNEL',
                'Admin'       => 'ROLE_ADMIN',
            ])
            ->hideOnIndex();
    }

    /* ---------- hachage du mot de passe ---------- */
    public function persistEntity(EntityManagerInterface $em, $entityInstance): void
    {
        $this->handlePassword($entityInstance);
        parent::persistEntity($em, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $em, $entityInstance): void
    {
        $this->handlePassword($entityInstance);
        parent::updateEntity($em, $entityInstance);
    }

    private function handlePassword(Personnel $personnel): void
    {
        $plain = $this->getContext()->getRequest()->request->all('Personnel')['plainPassword'] ?? null;

        if ($plain) {
            $personnel->setPassword(
                $this->hasher->hashPassword($personnel, $plain)
            );
        }
    }
}
