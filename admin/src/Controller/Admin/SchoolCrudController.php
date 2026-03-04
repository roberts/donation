<?php

namespace App\Controller\Admin;

use App\Entity\School;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use Doctrine\ORM\EntityManagerInterface;

class SchoolCrudController extends AbstractCrudController
{

    public static function getEntityFqcn(): string
    {
        return School::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            Field::new('ibeId')->onlyOnIndex(),
            ChoiceField::new('type')->setChoices([
                'Private' => 'private',
                'Public' => 'public',
            ])->renderExpanded(),
            TextField::new('name')->renderAsHtml(),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle( pageName: 'index', title: 'Schools')
            ->setDefaultSort(['name' => 'ASC'])
            ->showEntityActionsInlined()
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add(ChoiceFilter::new('type')->setChoices(['Private' => 'private','Public' => 'public'])->renderExpanded());
    }

    public function configureActions(Actions $actions): Actions
    {
        $viewInvoice = Action::new('Donation Link')
            ->setIcon('fa fa-link')
            ->setLabel(false)
            ->linkToUrl(function($entity) {
                $url = "https://app.ibefoundation.org/school/{$entity->getIbeId()}";
                return $url;
            })
            ->setHtmlAttributes(['target' => '_blank'])
        ;
        $actions->add(Crud::PAGE_INDEX, $viewInvoice);
        $actions->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
	        return $action->setIcon('far fa-pen-to-square')->setLabel(false);
        });
        $actions->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
	        return $action->setIcon('far fa-trash-can')->setLabel(false);
        });
        return $actions;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $entityInstance->setIbeId(rand(999999,9999999));
        parent::persistEntity($entityManager, $entityInstance);
        $entityInstance->setIbeId($entityInstance->getId());
        parent::persistEntity($entityManager, $entityInstance);
    }

}
