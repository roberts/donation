<?php

use App\Kernel;
use App\Entity\Transaction;
use App\Entity\Donation;
use Symfony\Component\Dotenv\Dotenv;

ini_set('display_errors',true);

require_once 'vendor/autoload.php';

// Load environment variables from root .env
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/../../.env');

// Get Stripe credentials based on environment
$stripeEnv = $_ENV['STRIPE_ENV'] ?? 'live';
$stripeKeyVar = $stripeEnv === 'test' ? 'STRIPE_SECRET_KEY_TEST' : 'STRIPE_SECRET_KEY_LIVE';
$webhookSecretVar = $stripeEnv === 'test' ? 'STRIPE_WEBHOOK_SECRET_TEST' : 'STRIPE_WEBHOOK_SECRET_LIVE';

define('STRIPE_SECRET_KEY', $_ENV[$stripeKeyVar]);
$endpoint_secret = $_ENV[$webhookSecretVar];
define('ENV_FILE', __DIR__ . '/../../.env');

$stripe = new \Stripe\StripeClient(STRIPE_SECRET_KEY);

// some checks start here
$payload = @file_get_contents('php://input');
$event = null;

if(!$payload){
	die('Empty input, exiting.' . PHP_EOL);
}

define('WEBHOOKS_LOG', './webhooks.log');
file_put_contents(WEBHOOKS_LOG, $payload . PHP_EOL, FILE_APPEND);

try {
	$event = \Stripe\Event::constructFrom( json_decode($payload, true) );
} catch(\UnexpectedValueException $e) {
	echo 'Webhook error while parsing basic request.';
	http_response_code(400);
	exit();
}

// if ($endpoint_secret) {
// 	$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
// 	try {
// 		$event = \Stripe\Webhook::constructEvent( $payload, $sig_header, $endpoint_secret );
// 	} catch(\Stripe\Exception\SignatureVerificationException $e) {
// 		echo 'Webhook error while validating signature.';
// 		http_response_code(400);
// 		exit();
// 	}
// }

// checks ended


require_once '/var/www/html/admin/vendor/autoload.php';

// handle the event

list($eventType, $eventStatus) = explode('.',$event->type);
file_put_contents(WEBHOOKS_LOG, "Event Type: {$event->type} @ " . microtime(true) . PHP_EOL, FILE_APPEND);
if( 'payment_intent' === $eventType ){
	file_put_contents(WEBHOOKS_LOG, "Payment Intent Conditional [{$event->type}] @ " . microtime(true) . PHP_EOL, FILE_APPEND);
	(new Dotenv())->load(ENV_FILE);
	$kernel = new Kernel('dev', true);
	$kernel->boot();

	// $paymentIntent = $event->data->object; // contains a \Stripe\PaymentIntent
	$paymentIntent = $stripe->paymentIntents->retrieve($event->data->object['id'], []);
		
	$entityManager = $kernel->getContainer()->get('doctrine.orm.entity_manager');

	//save or update transaction
	file_put_contents(WEBHOOKS_LOG, "Save or update transaction @ " . microtime(true) . PHP_EOL, FILE_APPEND);
	$t = $entityManager->getRepository(Transaction::class)->findOneByPaymentIntentId($paymentIntent['id']);
	if(!$t){
		file_put_contents(WEBHOOKS_LOG, "Create new Transaction() @ " . microtime(true) . PHP_EOL, FILE_APPEND);
		$t = new Transaction();
		$t->setCreated(new \DateTime());
	}
	$t->setPaymentIntentId($paymentIntent['id']);
	$t->setAmount($paymentIntent['amount']);
	$t->setLivemode($paymentIntent['livemode']);
	$t->setStatus($paymentIntent['status']);
	$entityManager->persist($t);
	$entityManager->flush();
	//save or update donation
	// Check both event type AND payment intent status to handle any payment_intent.* event that results in success
	if($event->type === 'payment_intent.succeeded' || $paymentIntent['status'] === 'succeeded'){
		file_put_contents(WEBHOOKS_LOG, "Payment Intent->succeeded Conditional (event={$event->type}, status={$paymentIntent['status']}) @ " . microtime(true) . PHP_EOL, FILE_APPEND);
		if(!$entityManager->getRepository(Donation::class)->paymentIntentIdExists($paymentIntent['id'])){
			file_put_contents(WEBHOOKS_LOG, "Create new Donation() @ " . microtime(true) . PHP_EOL, FILE_APPEND);
			$d = new Donation();
			$d->setCreated(new \DateTime());
			$d->setPaymentIntentId($paymentIntent['id']);
			
			// Set all metadata immediately to claim this payment intent
			$md = $paymentIntent['metadata'];
			$d->setFilingStatus($md['filingStatus']);
			$d->setTitle($md['title']);
			$d->setFirstName($md['firstName']);
			$d->setLastName($md['lastName']);
			$d->setTitle2($md['title2']);
			$d->setFirstName2($md['firstName2']);
			$d->setLastName2($md['lastName2']);
			$d->setPhoneNumber($md['phoneNumber']);
			$d->setAddressStreet1($md['addressStreet1']);
			$d->setAddressCity($md['addressCity']);
			$d->setAddressState($md['addressState']);
			$d->setAddressPostalCode($md['addressPostalCode']);
			$d->setAddressCountry($md['addressCountry']);
			$d->setEmail($md['email']);
			$d->setFilingYear((int)$md['filingYear']);
			$d->setQco($md['qco']);
			$d->setAmount($paymentIntent['amount']);
			$d->setSchoolDonationId((int)$md['schoolDonationId']);
			$d->setSchoolDonationName($md['schoolDonationName']);
			$d->setBillingAddressStreet1($md['billingAddressStreet1']);
			$d->setBillingAddressCity($md['billingAddressCity']);
			$d->setBillingAddressState($md['billingAddressState']);
			$d->setBillingAddressPostalCode($md['billingAddressPostalCode']);
			$d->setBillingAddressCountry($md['billingAddressCountry']);
			$d->setTaxProfessionalName($md['taxProfessionalName']);
			$d->setTaxProfessionalPhone($md['taxProfessionalPhone']);
			$d->setTaxProfessionalEmail($md['taxProfessionalEmail']);
			
			// Persist and flush immediately to prevent duplicate donations
			$entityManager->persist($d);
			$entityManager->flush();
			file_put_contents(WEBHOOKS_LOG, "Donation persisted @ " . microtime(true) . PHP_EOL, FILE_APPEND);
			
			// Now send receipt (this can take time, but donation is already in DB)
			sendReceipt($paymentIntent);
		}else{
			file_put_contents(WEBHOOKS_LOG, "Load existing Donation() @ " . microtime(true) . PHP_EOL, FILE_APPEND);
			$d = $entityManager->getRepository(Donation::class)->findOneByPaymentIntentId($paymentIntent['id']);
		}
		file_put_contents(WEBHOOKS_LOG, "Webhook End @ " . microtime(true) . PHP_EOL, FILE_APPEND);
	}
}


/*
if( 'payment_intent.created' === $event->type ) {
	(new Dotenv())->load(ENV_FILE);
	$kernel = new Kernel('dev', true);
	$kernel->boot();

	$paymentIntent = $event->data->object; // contains a \Stripe\PaymentIntent
		
	$t = new Transaction();
	$t->setPaymentIntentId($paymentIntent['id']);
	$t->setAmount($paymentIntent['amount']);
	$t->setCreated(new \DateTime());
	$t->setLivemode($paymentIntent['livemode']);
	// $t->setStatus($paymentIntent['status']);
	$t->setStatus('processed');

	$entityManager = $kernel->getContainer()->get('doctrine.orm.entity_manager');
	$entityManager->persist($t);
	$entityManager->flush();
}

if( 'payment_intent.payment_failed' === $event->type ) {
	(new Dotenv())->load(ENV_FILE);
	$kernel = new Kernel('dev', true);
	$kernel->boot();

	$paymentIntent = $event->data->object; // contains a \Stripe\PaymentIntent

	$entityManager = $kernel->getContainer()->get('doctrine.orm.entity_manager');
	$t = $entityManager->getRepository(Transaction::class)->findOneByPaymentIntentId($paymentIntent['id']);
	$t->setStatus($paymentIntent['status']);
	$entityManager->persist($t);
	$entityManager->flush();
}

if( 'payment_intent.succeeded' === $event->type) {
	(new Dotenv())->load(ENV_FILE);
	$kernel = new Kernel('dev', true);
	$kernel->boot();

	$paymentIntent = $event->data->object; // contains a \Stripe\PaymentIntent

	$entityManager = $kernel->getContainer()->get('doctrine.orm.entity_manager');

	// $t = $entityManager->getRepository(Transaction::class)->findOneByPaymentIntentId($paymentIntent['id']);
	// $t = new Transaction();
	// $t->setPaymentIntentId($paymentIntent['id']);
	// $t->setStatus($paymentIntent['status']);
	// $entityManager->persist($t);
	// $entityManager->flush();

	$d = new Donation();
	$md = $paymentIntent['metadata'];
	$d->setPaymentIntentId($paymentIntent['id']);
	$d->setFilingStatus($md['filingStatus']);
	$d->setTitle($md['title']);
	$d->setFirstName($md['firstName']);
	$d->setLastName($md['lastName']);
	$d->setTitle2($md['title2']);
	$d->setFirstName2($md['firstName2']);
	$d->setLastName2($md['lastName2']);
	$d->setPhoneNumber($md['phoneNumber']);
	$d->setAddressStreet1($md['addressStreet1']);
	$d->setAddressCity($md['addressCity']);
	$d->setAddressState($md['addressState']);
	$d->setAddressPostalCode($md['addressPostalCode']);
	$d->setAddressCountry($md['addressCountry']);
	$d->setEmail($md['email']);
	$d->setFilingYear((int)$md['filingYear']);
	$d->setQco($md['qco']);
	$d->setAmount($paymentIntent['amount']);
	$d->setSchoolDonationId((int)$md['schoolDonationId']);
	$d->setSchoolDonationName($md['schoolDonationName']);
	$d->setBillingAddressStreet1($md['billingAddressStreet1']);
	$d->setBillingAddressCity($md['billingAddressCity']);
	$d->setBillingAddressState($md['billingAddressState']);
	$d->setBillingAddressPostalCode($md['billingAddressPostalCode']);
	$d->setBillingAddressCountry($md['billingAddressCountry']);
	$d->setTaxProfessionalName($md['taxProfessionalName']);
	$d->setTaxProfessionalPhone($md['taxProfessionalPhone']);
	$d->setTaxProfessionalEmail($md['taxProfessionalEmail']);
	$d->setCreated(new \DateTime());

	$entityManager->persist($d);
	$entityManager->flush();

	sendReceipt($paymentIntent);
}
*/

http_response_code(200);

function sendReceipt($paymentIntent){

	$dollarFormatter = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
	$amountFormatted = $dollarFormatter->formatCurrency(($paymentIntent['amount'] / 100), 'USD');
	$currentTime = DateTime::createFromFormat( 'U', $paymentIntent['created'] );
	$donationDateFormatted = $currentTime->format( 'Y-m-d' );
	$filingYear = $paymentIntent['metadata']['filingYear'];
	$donorNames = implode(' ',[$paymentIntent['metadata']['title'],$paymentIntent['metadata']['firstName'],$paymentIntent['metadata']['lastName']]);
	if($paymentIntent['metadata']['firstName2'] || $paymentIntent['metadata']['lastName2']){
	  $donorNames  .= " & " . implode(' ',[$paymentIntent['metadata']['title2'],$paymentIntent['metadata']['firstName2'],$paymentIntent['metadata']['lastName2']]);
	}
	$donorAddress	= $paymentIntent['metadata']['addressStreet1'];
	$donorCityStateZip	= "{$paymentIntent['metadata']['addressCity']}, {$paymentIntent['metadata']['addressState']} {$paymentIntent['metadata']['addressPostalCode']}";
	
	$transport = (new Swift_SmtpTransport('email-smtp.us-west-2.amazonaws.com', 2587, 'tls'))
	->setUsername('AKIARDYN5ACARTU5IYAT')
	->setPassword('BKdkFi3+T6O5PQ3Yq44tsweKULwxf57nOCSmI3eNNcG9')
	->setStreamOptions(array('ssl' => array('allow_self_signed' => true, 'verify_peer' => false)));
 	;
  
	$pdf_body = file_get_contents('templates/pdf1.html');
	$pdf_body = str_replace('{{filingYear}}',$filingYear,$pdf_body);
	$pdf_body = str_replace('{{donationDate}}',$donationDateFormatted,$pdf_body);
	$pdf_body = str_replace('{{donationAmount}}',$amountFormatted,$pdf_body);
	$pdf_body = str_replace('{{donorNames}}',$donorNames,$pdf_body);
	$pdf_body = str_replace('{{donorAddress}}',$donorAddress,$pdf_body);
	$pdf_body = str_replace('{{donorCityStateZip}}',$donorCityStateZip,$pdf_body);
  
	$separator = md5(time());
	$eol = PHP_EOL;
	$filename = "{$filingYear}_IBEF_Donation_Receipt.pdf";
	$file_path =  tempnam('/tmp','IBE_PDF_');
	$mpdf = new \Mpdf\Mpdf();
	$mpdf->SetTitle("{$filingYear} IBEF Donation Receipt");
	$stylesheet = file_get_contents('templates/style.css');
	$mpdf->WriteHTML($stylesheet,\Mpdf\HTMLParserMode::HEADER_CSS);
	$mpdf->WriteHTML($pdf_body);
	$mpdf->Output($file_path,'F');
	$attachments = array($file_path);
	$body = str_replace('YEAR',$filingYear ,'YEAR Tax Credit Receipt');

	// Create the Mailer using your created Transport
	$mailer = new Swift_Mailer($transport);
	
	// Create a message
	$message = (new Swift_Message(str_replace('YEAR',$filingYear ,'YEAR Tax Credit Receipt')))
		->setFrom(['services@ibefoundation.org' => 'IBE Foundation'])
		->setTo([$paymentIntent['metadata']['email'] => implode(' ',[$paymentIntent['metadata']['firstName'], $paymentIntent['metadata']['lastName']])])
		->setBody($body)
		->attach(Swift_Attachment::fromPath($file_path)->setFileName($filename))
		;
	
	// Send the message
	$result = $mailer->send($message);
}
