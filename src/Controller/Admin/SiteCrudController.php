<?php

namespace App\Controller\Admin;

use App\Entity\Site;
use App\Controller\Admin\BunkerCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class SiteCrudController extends AbstractCrudController
{
    public function __construct(private AdminUrlGenerator $adminUrlGenerator)
    {
    }
    
    public static function getEntityFqcn(): string
    {
        return Site::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Site')
            ->setEntityLabelInPlural('Sites')
            ->setPageTitle('index', 'Liste des cliniques')
            ->setPageTitle('new', 'Ajouter une clinique')
            ->setPageTitle('edit', 'Modifier la clinique')
            ->setPageTitle('detail', 'Détails de la clinique');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->hideOnForm();
        yield TextField::new('nom', 'Nom de la clinique');
        
        yield ImageField::new('imagePath', 'Image de la clinique')
            ->setBasePath('uploads/sites')
            ->setUploadDir('public/uploads/sites')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setRequired(false);
            
        yield AssociationField::new('idAdmin', 'Administrateur')
            ->setFormTypeOption('choice_label', 'ANom');
            
        yield CollectionField::new('bunkers', 'Bunkers')
            ->onlyOnDetail()
            ->renderExpanded() // Affichage déplié pour une meilleure lisibilité
            ->setTemplatePath('admin/site/_bunkers_table.html.twig');
    }

    public function configureActions(Actions $actions): Actions
    {
        // Bouton pour ajouter un bunker directement depuis la page d'un site
        $addBunker = Action::new('addBunker', 'Ajouter un bunker', 'fa fa-plus')
            ->linkToUrl(function (Site $site) {
                /** @var AdminUrlGenerator $urlGen */
                $urlGen = $this->get(AdminUrlGenerator::class);
                
                return $urlGen
                    ->setController(BunkerCrudController::class)
                    ->setAction(Action::NEW)
                    ->set('site', $site->getId()) // on passe l'ID du site
                    ->generateUrl();
            })
            ->addCssClass('btn btn-success');
            
        // Autres actions
        $viewBunkers = Action::new('viewBunkers', 'Voir les bunkers', 'fa fa-microchip')
            ->linkToUrl(function (Site $site) {
                /** @var AdminUrlGenerator $urlGen */
                $urlGen = $this->get(AdminUrlGenerator::class);
                
                return $urlGen
                    ->setController(BunkerCrudController::class)
                    ->setAction(Action::INDEX)
                    ->set('filters[site][comparison]', '=')
                    ->set('filters[site][value]', $site->getId())
                    ->generateUrl();
            })
            ->addCssClass('btn btn-primary');
            
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $addBunker)
            ->add(Crud::PAGE_DETAIL, $viewBunkers);
    }
}
