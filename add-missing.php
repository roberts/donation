<?php

use App\Kernel;
use App\Entity\Transaction;
use App\Entity\Donation;
use Symfony\Component\Dotenv\Dotenv;

ini_set('display_errors', true);
error_reporting(E_ALL);

require_once __DIR__ . '/webhooks/stripe/vendor/autoload.php';
require_once __DIR__ . '/admin/vendor/autoload.php';

echo "Starting missing donation creation script...\n\n";

// Load environment variables from root .env
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

// Get Stripe credentials based on environment
$stripeEnv = $_ENV['STRIPE_ENV'] ?? 'live';
$stripeKeyVar = $stripeEnv === 'test' ? 'STRIPE_SECRET_KEY_TEST' : 'STRIPE_SECRET_KEY_LIVE';
define('STRIPE_SECRET_KEY', $_ENV[$stripeKeyVar]);

$stripe = new \Stripe\StripeClient(STRIPE_SECRET_KEY);

// Boot Symfony kernel (same pattern as webhook)
$kernel = new Kernel('dev', true);
$kernel->boot();
$entityManager = $kernel->getContainer()->get('doctrine.orm.entity_manager');

// Transaction IDs to process (will fetch metadata from Stripe)
$transactionIds = [36, 37, 38, 39, 40, 41, 42];

echo "Looking for transactions without donations...\n";

$processedCount = 0;
$errorCount = 0;
$skippedCount = 0;

foreach ($transactionIds as $transactionId) {
    $transaction = $entityManager->getRepository(Transaction::class)->find($transactionId);
    
    if (!$transaction) {
        echo "  [SKIP] Transaction #$transactionId not found\n";
        $skippedCount++;
        continue;
    }
    
    $paymentIntentId = $transaction->getPaymentIntentId();
    
    // Check if donation already exists (idempotent check)
    if ($entityManager->getRepository(Donation::class)->paymentIntentIdExists($paymentIntentId)) {
        echo "  [SKIP] Transaction #$transactionId ($paymentIntentId) - Donation already exists\n";
        $skippedCount++;
        continue;
    }
    
    // Check if transaction succeeded
    if ($transaction->getStatus() !== 'succeeded') {
        echo "  [SKIP] Transaction #$transactionId ($paymentIntentId) - Status: {$transaction->getStatus()}\n";
        $skippedCount++;
        continue;
    }
    
    echo "  [PROCESS] Transaction #$transactionId ($paymentIntentId)\n";
    
    try {
        // Retrieve payment intent from Stripe to get metadata
        echo "    [STRIPE] Fetching payment intent from Stripe API...\n";
        $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId, []);
        
        if (!isset($paymentIntent['metadata']) || empty($paymentIntent['metadata'])) {
            echo "    [ERROR] No metadata found for payment intent\n";
            $errorCount++;
            continue;
        }
        
        $md = $paymentIntent['metadata'];
        $amount = $transaction->getAmount();
        
        // Validate required metadata fields
        $requiredFields = ['firstName', 'lastName', 'email', 'filingYear'];
        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (empty($md[$field])) {
                $missingFields[] = $field;
            }
        }
        
        if (!empty($missingFields)) {
            echo "    [ERROR] Missing metadata fields: " . implode(', ', $missingFields) . "\n";
            $errorCount++;
            continue;
        }
        
        // Create donation
        $d = new Donation();
        $d->setPaymentIntentId($paymentIntentId);
        $d->setFilingStatus($md['filingStatus'] ?? null);
        $d->setTitle($md['title'] ?? null);
        $d->setFirstName($md['firstName']);
        $d->setLastName($md['lastName']);
        $d->setTitle2($md['title2'] ?? null);
        $d->setFirstName2($md['firstName2'] ?? null);
        $d->setLastName2($md['lastName2'] ?? null);
        $d->setPhoneNumber($md['phoneNumber'] ?? null);
        $d->setAddressStreet1($md['addressStreet1'] ?? null);
        $d->setAddressCity($md['addressCity'] ?? null);
        $d->setAddressState($md['addressState'] ?? null);
        $d->setAddressPostalCode($md['addressPostalCode'] ?? null);
        $d->setAddressCountry($md['addressCountry'] ?? 'USA');
        $d->setEmail($md['email']);
        $d->setFilingYear((int)$md['filingYear']);
        $d->setQco($md['qco'] ?? null);
        $d->setAmount($amount);
        $d->setSchoolDonationId((int)($md['schoolDonationId'] ?? 0));
        $d->setSchoolDonationName($md['schoolDonationName'] ?? null);
        $d->setBillingAddressStreet1($md['billingAddressStreet1'] ?? null);
        $d->setBillingAddressCity($md['billingAddressCity'] ?? null);
        $d->setBillingAddressState($md['billingAddressState'] ?? null);
        $d->setBillingAddressPostalCode($md['billingAddressPostalCode'] ?? null);
        $d->setBillingAddressCountry($md['billingAddressCountry'] ?? null);
        $d->setTaxProfessionalName($md['taxProfessionalName'] ?? null);
        $d->setTaxProfessionalPhone($md['taxProfessionalPhone'] ?? null);
        $d->setTaxProfessionalEmail($md['taxProfessionalEmail'] ?? null);
        $d->setCreated($transaction->getCreated() ?? new \DateTime());
        
        $entityManager->persist($d);
        $entityManager->flush();
        
        echo "    [SUCCESS] Created donation for {$md['firstName']} {$md['lastName']} - Filing Year: {$md['filingYear']} - Amount: $" . ($amount / 100) . "\n";
        
        $processedCount++;
        
    } catch (\Exception $e) {
        echo "    [ERROR] " . $e->getMessage() . "\n";
        $errorCount++;
    }
    
    echo "\n";
}

echo "==========================================\n";
echo "Script completed!\n";
echo "  Processed: $processedCount\n";
echo "  Skipped:   $skippedCount\n";
echo "  Errors:    $errorCount\n";
echo "==========================================\n";
