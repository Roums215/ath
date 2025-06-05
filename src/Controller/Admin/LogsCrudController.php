<?php

namespace App\Controller\Admin;

use App\Entity\Logs;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class LogsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Logs::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Log')
            ->setEntityLabelInPlural('Logs')
            ->setPageTitle('index', 'Historique des actions')
            ->setDefaultSort(['LDate' => 'DESC'])
            ->setSearchFields(['LAction', 'idPersonnel.PNom', 'idPersonnel.PPrenom']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('idLogs', 'ID')->hideOnForm();
        
        yield ChoiceField::new('LAction', 'Action')
            ->setChoices([
                'Suppression' => 'delete',
                'Mise à jour' => 'update',
                'Création' => 'create',
                'Connexion' => 'connexion',
                'Création de compte' => 'creationCompte'
            ])
            ->renderAsBadges([
                'delete' => 'danger',
                'update' => 'warning',
                'create' => 'success',
                'connexion' => 'info',
                'creationCompte' => 'primary',
            ]);
            
        yield DateTimeField::new('LDate', 'Date');
        
        yield AssociationField::new('idPersonnel', 'Personnel')
            ->setFormTypeOption('choice_label', function($personnel) {
                return $personnel->getPNom() . ' ' . $personnel->getPPrenom();
            });
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            // Désactiver la création et l'édition pour les logs (lecture seule)
            ->disable(Action::NEW, Action::EDIT, Action::DELETE);
    }
}
