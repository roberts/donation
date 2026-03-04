<?php

namespace App\Controller\Admin;

use App\Entity\Donation;
use App\Service\DonationReceipt;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminAction;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Mpdf\Mpdf;
use Symfony\Component\HttpFoundation\RequestStack;

class DonationCrudController extends AbstractCrudController
{

    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }
    
    public static function getEntityFqcn(): string
    {
        return Donation::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $viewReceiptIndex = Action::new('View Receipt', 'View Receipt', 'fa fa-receipt')
            ->setLabel(false)
            ->setHtmlAttributes(['target' => '_blank'])
            ->linkToCrudAction('viewReceipt');
        $viewReceiptDetail = Action::new('View Receipt', 'View Receipt', 'fa fa-receipt')
            ->setHtmlAttributes(['target' => '_blank'])
            ->linkToCrudAction('viewReceipt');
        $sendReceipt = Action::new('sendReceipt', 'Send Receipt', 'fa fa-envelope')
            ->setHtmlAttributes(
                ['onclick' => 'return confirm("Are you sure you want to email this receipt?")']
            )
            ->linkToCrudAction('sendReceipt');
        
        return $actions
            ->disable(Action::NEW, Action::EDIT, Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('far fa-eye')->setLabel(false);
            })
            ->add(Crud::PAGE_DETAIL, $viewReceiptDetail)
            ->add(Crud::PAGE_INDEX, $viewReceiptIndex)
            ->add(Crud::PAGE_DETAIL, $sendReceipt)
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->setDefaultSort(['created' => 'DESC'])
        ->setPaginatorPageSize(50)
        ->setPageTitle(Crud::PAGE_INDEX, 'Donations')
        ->showEntityActionsInlined()
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            yield DateTimeField::new('created');
            yield Field::new('filingYear');
            yield Field::new('email');
            yield Field::new('fullName','Full Name');
            yield MoneyField::new('amount')->setCurrency('USD')->setTextAlign('right');
        }

        if (Crud::PAGE_DETAIL === $pageName) {
            yield Field::new('id');
            yield DateTimeField::new('created');
            yield Field::new('email');
            yield Field::new('fullName','Full Name');
            yield Field::new('fullName2','Second Donor');
            yield MoneyField::new('amount')->setCurrency('USD')->setTextAlign('right');
            yield Field::new('paymentIntentId','Stripe ID');
            yield Field::new('filingStatus');
            yield Field::new('filingYear');
            yield Field::new('addressStreet1', 'Address Street');
            yield Field::new('addressCity');
            yield Field::new('addressState');
            yield Field::new('addressPostalCode');
            yield Field::new('addressCountry');
            yield TelephoneField::new('phoneNumber')
            ->formatValue(function ($value) {
                return ($value) ? $value : false;
            });
            yield Field::new('schoolDonationId');
            yield Field::new('schoolDonationName');
            yield Field::new('taxProfessionalName');
            yield TelephoneField::new('taxProfessionalPhone')
            ->formatValue(function ($value) {
                return ($value) ? $value : false;
            });
            yield Field::new('taxProfessionalEmail');
            yield Field::new('billingAddressStreet1');
            yield Field::new('billingAddressCity');
            yield Field::new('billingAddressState');
            yield Field::new('billingAddressPostalCode');
            yield Field::new('billingAddressCountry');          
        }

    }

    #[AdminAction(routePath: '/view_receipt', routeName: 'view_receipt', methods: ['GET', 'POST'])]
    public function viewReceipt(AdminContext $context)
    {
        $donation = $context->getEntity()->getInstance();
        $dr = new DonationReceipt;
        $dr->view_receipt_pdf($donation);
    }

    #[AdminAction(routePath: '/send_receipt', routeName: 'send_receipt', methods: ['GET', 'POST'])]
    public function sendReceipt(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        $donation = $context->getEntity()->getInstance();
        $this->requestStack->getSession()->getFlashBag()->add('success',"Invoice for donation #{$donation->getId()} sent to {$donation->getEmail()}.");

        $dr = new DonationReceipt;
        $dr->send_receipt_pdf($donation);
        $targetUrl = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Crud::PAGE_INDEX)
            // ->setEntityId($question->getId())
            ->generateUrl();
        return $this->redirect($targetUrl);
    }

}
