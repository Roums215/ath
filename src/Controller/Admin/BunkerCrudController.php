<?php

namespace App\Controller\Admin;

use App\Entity\Bunker;
use App\Entity\Site;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

#[IsGranted('ROLE_ADMIN')]
class BunkerCrudController extends AbstractCrudController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AdminUrlGenerator $adminUrlGenerator
    ) {
    }
    
    public static function getEntityFqcn(): string
    {
        return Bunker::class;
    }
    
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Bunker')
            ->setEntityLabelInPlural('Bunkers')
            ->setPageTitle('index', 'Liste des bunkers')
            ->setPageTitle('new', 'Ajouter un bunker')
            ->setPageTitle('edit', fn (Bunker $bunker) => sprintf('Modifier le bunker <b>%s</b>', $bunker->getNom()))
            ->setPageTitle('detail', fn (Bunker $bunker) => sprintf('Détails du bunker <b>%s</b>', $bunker->getNom()))
            ->setDefaultSort(['site.nom' => 'ASC', 'nom' => 'ASC'])
            ->setFormOptions([
                'validation_groups' => ['Default']
            ])
            ->showEntityActionsInlined()
            ->setSearchFields(['nom', 'benum', 'etat', 'site.nom']);
    }
    
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('site', 'Clinique'));
    }
    
    public function configureActions(Actions $actions): Actions
    {
        // Action pour retourner au site après modification d'un bunker
        $viewSite = Action::new('viewSite', 'Voir la clinique', 'fa fa-hospital')
            ->linkToUrl(function (Bunker $bunker) {
                return $this->adminUrlGenerator
                    ->setController(SiteCrudController::class)
                    ->setAction(Action::DETAIL)
                    ->setEntityId($bunker->getSite()->getId())
                    ->generateUrl();
            })
            ->addCssClass('btn btn-secondary');
            
        return $actions
            ->add(Crud::PAGE_DETAIL, $viewSite)
            ->add(Crud::PAGE_EDIT, $viewSite)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            // Personnaliser les actions existantes au lieu de les ajouter
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setLabel('Enregistrer et revenir')->setIcon('fa fa-check');
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setLabel('Enregistrer et revenir')->setIcon('fa fa-check');
            })
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setIcon('fa fa-plus')->setLabel('Ajouter un bunker');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa fa-edit');
            })
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fa fa-eye');
            });
    }

    public function configureFields(string $pageName): iterable
    {
        // Configuration du champ site
        $siteField = AssociationField::new('site', 'Clinique')
            ->setRequired(true)
            ->setFormTypeOption('choice_label', 'nom')
            ->setColumns(12) // Utilise toute la largeur
            ->setHelp('Sélectionnez la clinique à laquelle ce bunker est rattaché');
            
        // Si un site est déjà fixé (venant de la page d'une clinique), on bloque le champ
        if ($this->getContext()->getRequest()->query->get('site')) {
            $siteField->setFormTypeOption('disabled', true);
        }
        
        yield $siteField;
        yield IdField::new('id', 'ID')->hideOnForm();
        
        yield TextField::new('nom', 'Nom du bunker')
            ->setColumns(6)
            ->setRequired(true);
            
        yield TextField::new('benum', 'Numéro d\'énumération')
            ->setColumns(6)
            ->setHelp('Numéro d\'identification unique du bunker')
            ->setRequired(false);
            
        yield ChoiceField::new('etat', 'État')
            ->setChoices([
                'Pas de retard' => 'Pas de retard',
                'Retard 5 min' => 'Retard 5 min',
                'Retard 10 min' => 'Retard 10 min',
                'Retard 15 min' => 'Retard 15 min',
                'Hors service' => 'Hors service',
            ])
            ->setColumns(6)
            ->renderExpanded(false);
            
        yield AssociationField::new('derniereModifPar', 'Dernière modification par')
            ->setColumns(6)
            ->setFormTypeOption('choice_label', 'PNom')
            ->setRequired(false);
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        
        // Filtrer par site si le paramètre existe
        // Vérifie les deux paramètres possibles: 'site' ou 'site_filter'
        $siteId = $this->getContext()?->getRequest()->query->getInt('site') ?: 
                 $this->getContext()?->getRequest()->query->getInt('site_filter');
                 
        if ($siteId) {
            $qb->andWhere('entity.site = :sid')->setParameter('sid', $siteId);
        }
        
        return $qb;
    }
    
    /**
     * Pré-remplit le champ Site quand on clique sur "Nouveau bunker" depuis un site
     */
    public function createEntity(string $entityFqcn)
    {
        $bunker = new Bunker();
        
        // Si l'URL contient ?site=XX => on initialise
        $siteId = $this->getContext()->getRequest()->query->get('site');
        if ($siteId) {
            $site = $this->entityManager->getRepository(Site::class)->find($siteId);
            if ($site) {
                $bunker->setSite($site);
            }
        }
        
        return $bunker;
    }
}
