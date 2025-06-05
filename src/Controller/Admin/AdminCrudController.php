<?php

namespace App\Controller\Admin;

use App\Entity\Admin;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class AdminCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Admin::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Administrateur')
            ->setEntityLabelInPlural('Administrateurs')
            ->setPageTitle('index', 'Liste des administrateurs')
            ->setPageTitle('new', 'Ajouter un administrateur')
            ->setPageTitle('edit', 'Modifier l\'administrateur')
            ->setPageTitle('detail', 'Détails de l\'administrateur');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('idAdmin', 'ID')->hideOnForm();
        yield TextField::new('ANom', 'Nom');
        yield TextField::new('APrenom', 'Prénom');
        
        // Pour le mot de passe, on utilise un champ texte en mode création
        // mais on le cache en mode édition pour éviter de l'écraser
        $passwordField = TextField::new('AMdp', 'Mot de passe');
        
        if ($pageName === Crud::PAGE_EDIT) {
            $passwordField->setHelp('Laissez vide pour conserver le mot de passe actuel');
        }
        
        yield $passwordField;
        
        // Ajout des rôles
        yield ChoiceField::new('roles', 'Rôles')
            ->setChoices([
                'Administrateur' => 'ROLE_ADMIN',
                'Super Administrateur' => 'ROLE_SUPER_ADMIN'
            ])
            ->allowMultipleChoices()
            ->renderExpanded();
        
        yield DateTimeField::new('ACreation', 'Date de création')
            ->hideOnForm();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::SAVE_AND_ADD_ANOTHER);
    }
}