<?php

//ini_set('display_errors',true);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, PUT, GET, DELETE, OPTIONS');
header("Access-Control-Allow-Headers: Access-Control-Allow-Headers, Origin,Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers");

require_once 'vendor/autoload.php';

// Load environment variables
use Symfony\Component\Dotenv\Dotenv;
$dotenv = new Dotenv();
$dotenv->load('/.env');

// Get Stripe key based on environment
$stripeEnv = $_ENV['STRIPE_ENV'] ?? 'live';
$stripeKeyVar = $stripeEnv === 'test' ? 'STRIPE_SECRET_KEY_TEST' : 'STRIPE_SECRET_KEY_LIVE';
define('STRIPE_SECRET_KEY', $_ENV[$stripeKeyVar]);

$_POST = json_decode(file_get_contents("php://input"), true);

if(!$_POST){
	die("Empty payment object");
}
$stripe = new \Stripe\StripeClient(STRIPE_SECRET_KEY);
$intent = $stripe->paymentIntents->create([
    'amount' => ($_POST['amount'] * 100),
    'currency' => 'usd',
    'automatic_payment_methods' => ['enabled' => true],
    'metadata' => [
        'filingStatus' => $_POST['filingStatus'],
        'title' => $_POST['title'],
        'firstName' => $_POST['firstName'],
        'lastName' => $_POST['lastName'],
        'title2' => $_POST['title2'],
        'firstName2' => $_POST['firstName2'],
        'lastName2' => $_POST['lastName2'],
        'phoneNumber' => $_POST['phoneNumber'],
        'addressStreet1' => $_POST['addressStreet1'],
        'addressCity' => $_POST['addressCity'],
        'addressState' => $_POST['addressState'],
        'addressPostalCode' => $_POST['addressPostalCode'],
        'addressCountry' => $_POST['addressCountry'],
        'email' => $_POST['email'],
        'filingYear' => $_POST['filingYear'],
        'qco' => $_POST['qco'],
        'amount' => ($_POST['amount'] * 100),
        'schoolDonationId' => $_POST['schoolDonationId'],
        'schoolDonationName' => $_POST['schoolDonationName'],
        'billingAddressStreet1' => $_POST['billingAddressStreet1'],
        'billingAddressCity' => $_POST['billingAddressCity'],
        'billingAddressState' => $_POST['billingAddressState'],
        'billingAddressPostalCode' => $_POST['billingAddressPostalCode'],
        'billingAddressCountry' => $_POST['billingAddressCountry'],
        'taxProfessionalName' => $_POST['taxProfessionalName'],
        'taxProfessionalPhone' => $_POST['taxProfessionalPhone'],
        'taxProfessionalEmail' => $_POST['taxProfessionalEmail']
    ],
]);

if( isset( $intent->client_secret ) ) {
    echo json_encode( array( 'clientSecret' => $intent->client_secret ) );
}


