<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Donation;
use App\Entity\School;
use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;

#[AdminDashboard(routePath: '/', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function index(): Response
    {
        $transactionRepository = $this->entityManager->getRepository( Transaction::class );
        $recentTransactions = $transactionRepository->findRecentTransactions();
 
        $donationRepository = $this->entityManager->getRepository( Donation::class );
        $donationTotals = $donationRepository->getDonationTotals();
        $donationTotalsByYear = $donationRepository->getDonationTotalsByYear();
        $recentDonations = $donationRepository->findRecentDonations();
        
        return $this->render('admin/dashboard.html.twig', [
            'donationTotals' => $donationTotals,
            'donationTotalsByYear' => $donationTotalsByYear,
            'recentDonations' => $recentDonations,
            'recentTransactions' => $recentTransactions,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        // yield MenuItem::section('Foo');
        yield MenuItem::linkToCrud('Donations', 'fas fa-gift', Donation::class);
        yield MenuItem::linkToCrud('Transactions', 'fas fa-credit-card', Transaction::class);
        // yield MenuItem::section('Bar');
        yield MenuItem::linkToCrud('Schools', 'fas fa-school', School::class);
    }

    public function configureAssets(): Assets
    {
        return Assets::new()->addCssFile('css/admin.css');
    }
}
