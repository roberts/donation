<?php
// src/Service/DonationReceipt.php
namespace App\Service;
use App\Entity\Donation;
use Mpdf\Mpdf;
use Swiftmailer\Swiftmailer;

class DonationReceipt
{
    private $templateDir = '../../webhooks/stripe/templates';
    private $sesHost;
    private $sesPort;
    private $sesEncryption;
    private $sesUsername;
    private $sesPassword;

    public function __construct(
        string $sesHost,
        int $sesPort,
        string $sesEncryption,
        string $sesUsername,
        string $sesPassword
    ) {
        $this->sesHost = $sesHost;
        $this->sesPort = $sesPort;
        $this->sesEncryption = $sesEncryption;
        $this->sesUsername = $sesUsername;
        $this->sesPassword = $sesPassword;
    }

    private function prepare_receipt(Donation $donation){
        $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir()]);
        $dollarFormatter = new \NumberFormatter('en_US', \NumberFormatter::CURRENCY);
        $amountFormatted = $dollarFormatter->formatCurrency(($donation->getAmount() / 100), 'USD');
        $donationDateFormatted = $donation->getCreated()->format( 'Y-m-d' );
        $filingYear = $donation->getFilingYear();
        $donorNames = $donation->getFullName() . ($donation->getFullName2() ? ' & '.$donation->getFullName2() : null);
        $donorAddress = $donation->getAddressStreet1();
        $donorCityStateZip	= "{$donation->getAddressCity()}, {$donation->getAddressState()} {$donation->getAddressPostalCode()}";

        $pdf_body = file_get_contents("{$this->templateDir}/pdf1.html");
        $pdf_body = str_replace('{{filingYear}}',$filingYear,$pdf_body);
        $pdf_body = str_replace('{{donationDate}}',$donationDateFormatted,$pdf_body);
        $pdf_body = str_replace('{{donationAmount}}',$amountFormatted,$pdf_body);
        $pdf_body = str_replace('{{donorNames}}',$donorNames,$pdf_body);
        $pdf_body = str_replace('{{donorAddress}}',$donorAddress,$pdf_body);
        $pdf_body = str_replace('{{donorCityStateZip}}',$donorCityStateZip,$pdf_body);
        $separator = md5(time());
        $eol = PHP_EOL;
        $filename = "{$filingYear}_IBEF_Donation_Receipt.pdf";
        $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir()]);
        $mpdf->SetTitle("{$filingYear} IBEF Donation Receipt");
        $stylesheet = file_get_contents("{$this->templateDir}/style.css");
        $mpdf->WriteHTML($stylesheet,\Mpdf\HTMLParserMode::HEADER_CSS);
        $mpdf->WriteHTML($pdf_body);
        return $mpdf;
    }

    public function view_receipt_pdf(Donation $donation){
        $mpdf = $this->prepare_receipt($donation);
        $filename = "{$donation->getFilingYear()}_IBEF_Donation_Receipt.pdf";
        $mpdf->Output($filename, 'I');
    }
  

    public function send_receipt_pdf(Donation $donation){
        $transport = (new \Swift_SmtpTransport($this->sesHost, $this->sesPort, $this->sesEncryption))
        ->setUsername($this->sesUsername)
        ->setPassword($this->sesPassword)
        ->setStreamOptions(array('ssl' => array('allow_self_signed' => true, 'verify_peer' => false)))
        ;
        $mpdf = $this->prepare_receipt($donation);
        $filename = "{$donation->getFilingYear()}_IBEF_Donation_Receipt.pdf";
        $file_path =  tempnam('/tmp','IBE_PDF_');
        $mpdf->Output($file_path,'F');
        $attachments = array($file_path);
        // Create the Mailer using your created Transport
        $mailer = new \Swift_Mailer($transport);
        $body = str_replace('YEAR',$donation->getFilingYear() ,'YEAR Tax Credit Receipt');

        // Create a message
        $message = (new \Swift_Message(str_replace('YEAR',$donation->getFilingYear() ,'YEAR Tax Credit Receipt')))
            ->setFrom(['services@ibefoundation.org' => 'IBE Foundation'])
            ->setTo([$donation->getEmail() => $donation->getFullName()])
            // ->setTo(['halabuda@gmail.com' => 'nathan halabuda', "tucker.ted@gmail.com" => "Ted Tucker"])
            ->setBody($body)
            ->attach(\Swift_Attachment::fromPath($file_path)->setFileName($filename))
            ;
        
        // Send the message
        $result = $mailer->send($message);
    }

}