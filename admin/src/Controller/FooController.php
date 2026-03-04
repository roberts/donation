<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class FooController extends AbstractController
{
    #[Route(path: '/foo', name: 'app_foo')]
    public function app_foo(): Response
    {
      require_once '/var/www/html/webhooks/stripe/vendor/autoload.php';
      $event = \Stripe\Event::constructFrom( json_decode(file_get_contents('/var/www/html/admin/webhook.json'), true) );
      $this->sendReceipt($event);
      die('foo!');
    }

public function sendReceipt($event){
  // Get Stripe credentials from environment
  $stripeEnv = $_ENV['STRIPE_ENV'] ?? 'live';
  $stripeKeyVar = $stripeEnv === 'test' ? 'STRIPE_SECRET_KEY_TEST' : 'STRIPE_SECRET_KEY_LIVE';
  define('STRIPE_SECRET_KEY', $_ENV[$stripeKeyVar]);
  
  $stripe = new \Stripe\StripeClient(STRIPE_SECRET_KEY);
	$paymentIntent = $stripe->paymentIntents->retrieve($event->data->object['id'], []);

$dollarFormatter = new \NumberFormatter('en_US', \NumberFormatter::CURRENCY);
$amountFormatted = $dollarFormatter->formatCurrency(($paymentIntent['amount'] / 100), 'USD');
$currentTime = \DateTime::createFromFormat( 'U', $paymentIntent['created'] );
$donationDateFormatted = $currentTime->format( 'Y-m-d' );
$filingYear = $paymentIntent['metadata']['filingYear'];
$donorNames = implode(' ',[$paymentIntent['metadata']['title'],$paymentIntent['metadata']['firstName'],$paymentIntent['metadata']['lastName']]);
if($paymentIntent['metadata']['firstName2'] || $paymentIntent['metadata']['lastName2']){
  $donorNames  .= " & " . implode(' ',[$paymentIntent['metadata']['title2'],$paymentIntent['metadata']['firstName2'],$paymentIntent['metadata']['lastName2']]);
}
$donorAddress	= $paymentIntent['metadata']['addressStreet1'];
$donorCityStateZip	= "{$paymentIntent['metadata']['addressCity']}, {$paymentIntent['metadata']['addressState']} {$paymentIntent['metadata']['addressPostalCode']}";
  $pdf_body = file_get_contents('/var/www/html/webhooks/stripe/templates/pdf1.html');
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
  $stylesheet = file_get_contents('/var/www/html/webhooks/stripe/templates/style.css');
  $mpdf->WriteHTML($stylesheet,\Mpdf\HTMLParserMode::HEADER_CSS);
  $mpdf->WriteHTML($pdf_body);
  $mpdf->Output();

}

  
  
}
