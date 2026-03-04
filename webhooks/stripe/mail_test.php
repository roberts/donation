<?php

ini_set('display_errors',true);

$to = 'halabuda@gmail.com';

require_once 'vendor/autoload.php';

$transport = (new Swift_SmtpTransport('email-smtp.us-west-2.amazonaws.com', 2587, 'tls'))
->setUsername('AKIARDYN5ACARTU5IYAT')
->setPassword('BKdkFi3+T6O5PQ3Yq44tsweKULwxf57nOCSmI3eNNcG9')
->setStreamOptions(array('ssl' => array('allow_self_signed' => true, 'verify_peer' => false)));
;

$filingYear = 2025;

$body = "Hello world!";
$pdf_body = file_get_contents('templates/pdf1.html');
$pdf_body = str_replace('{{filingYear}}',$filingYear,$pdf_body);
$pdf_body = str_replace('{{donationDate}}','2025-03-01',$pdf_body);
$pdf_body = str_replace('{{donationAmount}}','$123.45',$pdf_body);
$separator = md5(time());
$eol = PHP_EOL;
$filename = "{$filingYear}_IBEF_Donation_Receipt.pdf";
$file_path =  tempnam('/tmp','IBE_PDF_');
$mpdf = new \Mpdf\Mpdf(['debug' => true]);
$mpdf->SetTitle("{$filingYear} IBEF Donation Receipt");
$stylesheet = file_get_contents('templates/style.css');
$mpdf->WriteHTML($stylesheet,\Mpdf\HTMLParserMode::HEADER_CSS);
$mpdf->WriteHTML($pdf_body);
// $mpdf->Output($file_path,'F');
$mpdf->Output();die;
// $headers .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";
$attachments = array($file_path);

$mailer = new Swift_Mailer($transport);
$message = (new Swift_Message('Tax Credit Receipt'))
->setFrom(['services@ibefoundation.org' => 'services@ibefoundation.org'])
->setTo($to)
->setBody($body)
->attach(Swift_Attachment::fromPath($file_path)->setFileName($filename))
;
$result = $mailer->send($message);
