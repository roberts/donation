<?php

namespace App\Controller\Admin;

use App\Entity\Transaction;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;

class TransactionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Transaction::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::EDIT, Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('far fa-eye')->setLabel(false);
            })
        ;

    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->setDefaultSort(['created' => 'DESC'])
        ->setPaginatorPageSize(50)
        ->setPageTitle(Crud::PAGE_INDEX, 'Transactions')
        ->showEntityActionsInlined()
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            yield DateTimeField::new('created');
            yield MoneyField::new('amount')->setCurrency('USD')->setTextAlign('right');
            yield Field::new('status');
            yield Field::new('paymentIntentId','Stripe ID');
        }

        if (Crud::PAGE_DETAIL === $pageName) {
            yield Field::new('id');
            yield DateTimeField::new('created');
            yield MoneyField::new('amount')->setCurrency('USD')->setTextAlign('right');
            yield Field::new('paymentIntentId','Stripe ID');
            yield Field::new('status');
            yield BooleanField::new('livemode')->renderAsSwitch(true);
        }

    }
}
