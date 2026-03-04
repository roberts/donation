<?php

namespace App\Entity;

use App\Repository\DonationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DonationRepository::class)]
class Donation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $paymentIntentId = null;

    #[ORM\Column]
    private ?int $amount = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $firstName2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastName2 = null;
        
    #[ORM\Column(length: 255)]
    private ?string $filingStatus = null;

    #[ORM\Column]
    private ?int $filingYear = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $addressStreet1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $addressCity = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $addressState = null;

    #[ORM\Column(nullable: true)]
    private ?int $addressPostalCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $addressCountry = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $qco = null;

    #[ORM\Column(nullable: true)]
    private ?int $schoolDonationId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $schoolDonationName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $taxProfessionalName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $taxProfessionalPhone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $taxProfessionalEmail = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $billingAddressStreet1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $billingAddressCity = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $billingAddressState = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $billingAddressPostalCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $billingAddressCountry = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPaymentIntentId(): ?string
    {
        return $this->paymentIntentId;
    }

    public function setPaymentIntentId(string $paymentIntentId): static
    {
        $this->paymentIntentId = $paymentIntentId;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFilingStatus(): ?string
    {
        return $this->filingStatus;
    }

    public function setFilingStatus(string $filingStatus): static
    {
        $this->filingStatus = $filingStatus;

        return $this;
    }

    public function getFilingYear(): ?int
    {
        return $this->filingYear;
    }

    public function setFilingYear(int $filingYear): static
    {
        $this->filingYear = $filingYear;

        return $this;
    }

    public function getAddressStreet1(): ?string
    {
        return $this->addressStreet1;
    }

    public function setAddressStreet1(?string $addressStreet1): static
    {
        $this->addressStreet1 = $addressStreet1;

        return $this;
    }

    public function getAddressCity(): ?string
    {
        return $this->addressCity;
    }

    public function setAddressCity(?string $addressCity): static
    {
        $this->addressCity = $addressCity;

        return $this;
    }

    public function getAddressState(): ?string
    {
        return $this->addressState;
    }

    public function setAddressState(?string $addressState): static
    {
        $this->addressState = $addressState;

        return $this;
    }

    public function getAddressPostalCode(): ?int
    {
        return $this->addressPostalCode;
    }

    public function setAddressPostalCode(?int $addressPostalCode): static
    {
        $this->addressPostalCode = $addressPostalCode;

        return $this;
    }

    public function getAddressCountry(): ?string
    {
        return $this->addressCountry;
    }

    public function setAddressCountry(?string $addressCountry): static
    {
        $this->addressCountry = $addressCountry;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getQco(): ?string
    {
        return $this->qco;
    }

    public function setQco(?string $qco): static
    {
        $this->qco = $qco;

        return $this;
    }

    public function getSchoolDonationName(): ?string
    {
        return $this->schoolDonationName;
    }

    public function setSchoolDonationName(?string $schoolDonationName): static
    {
        $this->schoolDonationName = $schoolDonationName;

        return $this;
    }

    public function getTitle2(): ?string
    {
        return $this->title2;
    }

    public function setTitle2(?string $title2): static
    {
        $this->title2 = $title2;

        return $this;
    }

    public function getFirstName2(): ?string
    {
        return $this->firstName2;
    }

    public function setFirstName2(?string $firstName2): static
    {
        $this->firstName2 = $firstName2;

        return $this;
    }

    public function getLastName2(): ?string
    {
        return $this->lastName2;
    }

    public function setLastName2(?string $lastName2): static
    {
        $this->lastName2 = $lastName2;

        return $this;
    }

    public function getSchoolDonationId(): ?int
    {
        return $this->schoolDonationId;
    }

    public function setSchoolDonationId(int $schoolDonationId): static
    {
        $this->schoolDonationId = $schoolDonationId;

        return $this;
    }

    public function getTaxProfessionalName(): ?string
    {
        return $this->taxProfessionalName;
    }

    public function setTaxProfessionalName(?string $taxProfessionalName): static
    {
        $this->taxProfessionalName = $taxProfessionalName;

        return $this;
    }

    public function getTaxProfessionalPhone(): ?string
    {
        return $this->taxProfessionalPhone;
    }

    public function setTaxProfessionalPhone(?string $taxProfessionalPhone): static
    {
        $this->taxProfessionalPhone = $taxProfessionalPhone;

        return $this;
    }

    public function getTaxProfessionalEmail(): ?string
    {
        return $this->taxProfessionalEmail;
    }

    public function setTaxProfessionalEmail(?string $taxProfessionalEmail): static
    {
        $this->taxProfessionalEmail = $taxProfessionalEmail;

        return $this;
    }

    public function getBillingAddressStreet1(): ?string
    {
        return $this->billingAddressStreet1;
    }

    public function setBillingAddressStreet1(?string $billingAddressStreet1): static
    {
        $this->billingAddressStreet1 = $billingAddressStreet1;

        return $this;
    }

    public function getBillingAddressCity(): ?string
    {
        return $this->billingAddressCity;
    }

    public function setBillingAddressCity(?string $billingAddressCity): static
    {
        $this->billingAddressCity = $billingAddressCity;

        return $this;
    }

    public function getBillingAddressState(): ?string
    {
        return $this->billingAddressState;
    }

    public function setBillingAddressState(?string $billingAddressState): static
    {
        $this->billingAddressState = $billingAddressState;

        return $this;
    }

    public function getBillingAddressPostalCode(): ?string
    {
        return $this->billingAddressPostalCode;
    }

    public function setBillingAddressPostalCode(?string $billingAddressPostalCode): static
    {
        $this->billingAddressPostalCode = $billingAddressPostalCode;

        return $this;
    }

    public function getBillingAddressCountry(): ?string
    {
        return $this->billingAddressCountry;
    }

    public function setBillingAddressCountry(?string $billingAddressCountry): static
    {
        $this->billingAddressCountry = $billingAddressCountry;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): static
    {
        $this->created = $created;

        return $this;
    }

    public function getFullName()
    {
        return trim(implode(' ', [$this->getTitle(),$this->getFirstName(),$this->getLastName()]));
    }

    public function getFullName2()
    {
        return trim(implode(' ', [$this->getTitle2(),$this->getFirstName2(),$this->getLastName2()]));
    }

}
