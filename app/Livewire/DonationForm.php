<?php

namespace App\Livewire;

use App\Actions\Donation\ProcessDonation;
use App\Enums\FilingStatus;
use App\Enums\SchoolType;
use App\Livewire\Forms\DonationForm as DonationFormObject;
use App\Models\School;
use App\Services\FilingYearService;
use Exception;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;
use Spatie\Honeypot\Http\Livewire\Concerns\HoneypotData;
use Spatie\Honeypot\Http\Livewire\Concerns\UsesSpamProtection;

/**
 * @property-read float $maxCredit
 * @property-read array<int, string> $availableYears
 */
class DonationForm extends Component
{
    use UsesSpamProtection;

    public HoneypotData $extraFields;

    public DonationFormObject $form;

    public int $currentStep = 1;

    public bool $hasAttemptedSubmission = false;

    public ?School $selectedSchool = null;

    public string $schoolType = '';

    public bool $schoolRecommend = false;

    /** @var array<int, array{id: int, name: string}> */
    public array $schools = [];

    protected FilingYearService $filingYearService;

    public function boot(FilingYearService $filingYearService): void
    {
        $this->filingYearService = $filingYearService;
    }

    public function mount(): void
    {
        $this->extraFields = new HoneypotData;
        $this->step1Valid = $this->form->isStep1Valid();
        $this->step2Valid = $this->form->isStep2Valid();
        $this->step3Valid = $this->form->isStep3Valid();

        if (request()->has('schoolId')) {
            $this->form->schoolId = request()->get('schoolId');
            $this->selectedSchool = School::find($this->form->schoolId);

            if (! $this->selectedSchool) {
                $this->form->schoolId = '';
            } else {
                $this->schoolRecommend = true;
                if ($this->selectedSchool->type === SchoolType::Private) {
                    $this->schoolType = 'private';
                } else {
                    $this->schoolType = 'public';
                }
            }
        }
    }

    public function updatedSchoolRecommend(bool $value): void
    {
        if (! $value) {
            $this->schoolType = '';
            $this->form->schoolId = '';
            $this->selectedSchool = null;
            $this->form->customSchool = '';
        }
    }

    public function updatedSchoolType(string $value): void
    {
        if ($value === 'other') {
            $this->form->schoolId = '';
            $this->selectedSchool = null;
        } else {
            $this->form->customSchool = '';
        }
    }

    public function updatedFormFilingStatus(): void
    {
        $this->form->adjustDonorsForFilingStatus();
        $this->form->totalAmount = ''; // Reset amount on status change
    }

    public function updatedFormFilingYear(): void
    {
        $this->form->totalAmount = ''; // Reset amount on year change
    }

    public function addDonor(): void
    {
        if (count($this->form->donors) < 2) {
            $this->form->donors[] = [
                'title' => '',
                'first_name' => '',
                'last_name' => '',
            ];
        }
    }

    public function removeDonor(int $index): void
    {
        if (count($this->form->donors) > 1) {
            unset($this->form->donors[$index]);
            $this->form->donors = array_values($this->form->donors);
        }
    }

    /**
     * @return Collection<int, array{id: int, name: string}>
     */
    public function searchSchools(string $query): Collection
    {
        $q = School::where('name', 'like', '%'.$query.'%');

        if ($this->schoolType === 'public') {
            $q->whereIn('type', [SchoolType::Public, SchoolType::Charter]);
        } elseif ($this->schoolType === 'private') {
            $q->where('type', SchoolType::Private);
        }

        return $q->orderBy('name')
            ->limit(10)
            ->get()
            ->map(fn ($school) => ['id' => $school->id, 'name' => $school->name]);
    }

    public function selectSchool(int|string $schoolId): void
    {
        $this->form->schoolId = (string) $schoolId;
        $this->selectedSchool = School::find($schoolId);
    }

    /**
     * @return array<int, string>
     */
    public function getAvailableYearsProperty(): array
    {
        $years = $this->filingYearService->getAvailableFilingYears();

        return array_map(strval(...), $years);
    }

    public function getMaxCreditProperty(): float|int
    {
        $year = (int) $this->form->filingYear;
        $status = $this->form->filingStatus;

        if (! $year || ! $status) {
            return 0;
        }

        $limits = $this->filingYearService->getLimits($year);

        if (! $limits) {
            return 0;
        }

        $isMarried = $status == FilingStatus::MarriedFilingJointly->value;

        return $isMarried ? $limits['married'] : $limits['single'];
    }

    public function calculateMaxDonation(): float|int
    {
        $maxCredit = $this->maxCredit;
        $qcoAmount = floatval($this->form->qcoAmount);

        return max(0, $maxCredit - $qcoAmount);
    }

    public function useMaxDonation(): void
    {
        $this->form->totalAmount = (string) $this->calculateMaxDonation();
    }

    public function isStep1Valid(): bool
    {
        return $this->form->isStep1Valid();
    }

    public function isStep2Valid(): bool
    {
        return $this->form->isStep2Valid();
    }

    public function isStep3Valid(): bool
    {
        return $this->form->isStep3Valid();
    }

    public function submit(string $paymentMethodId, ProcessDonation $processDonation): mixed
    {
        $this->protectAgainstSpam();

        // Rate Limiting: 5 attempts per minute per IP
        $limiter = app(RateLimiter::class);
        $key = 'donation-submission:'.request()->ip();

        if ($limiter->tooManyAttempts($key, 5)) {
            $this->addError('payment', 'Too many attempts. Please try again later.');

            return null;
        }

        $limiter->hit($key, 60);

        // Idempotency: Prevent concurrent submissions
        $lock = Cache::lock('donation-lock:'.request()->ip(), 10);

        if (! $lock->get()) {
            $this->addError('payment', 'A transaction is already being processed. Please wait.');

            return null;
        }

        try {
            $this->form->paymentMethodId = $paymentMethodId;
            $this->validate();

            $donationData = $this->form->toDTO(
                $paymentMethodId,
                $this->selectedSchool ? $this->selectedSchool->name : null
            );

            $processDonation->execute($donationData);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            $this->addError('payment', 'Payment failed: '.$e->getMessage());

            return null;
        } finally {
            $lock->release();
        }

        return redirect()->route('donation.success');
    }

    public function checkValidation(): void
    {
        $this->hasAttemptedSubmission = true;
        $this->validate();
    }

    public bool $step1Valid = false;

    public bool $step2Valid = false;

    public bool $step3Valid = false;

    public function updated(string $property, mixed $value): void
    {
        if ($this->hasAttemptedSubmission) {
            $this->validate();
        }

        $this->step1Valid = $this->form->isStep1Valid();
        $this->step2Valid = $this->form->isStep2Valid();
        $this->step3Valid = $this->form->isStep3Valid();

        // Validate donors array fields on update
        if (str_starts_with($property, 'form.donors.')) {
            $this->validateOnly($property);
        }

        if ($property === 'form.email_confirmation' || $property === 'form.email') {
            if ($this->step1Valid) {
                $this->dispatch('step-1-completed');
            }
        }
    }

    public function validateDonorField(int $index, string $field): void
    {
        $this->validateOnly("form.donors.{$index}.{$field}");
    }

    public function validateField(string $field): void
    {
        $this->validateOnly($field);
    }

    public function render(): View
    {
        $title = 'IBE Foundation Donation';
        if ($this->selectedSchool) {
            $title .= ' - '.$this->selectedSchool->name;
        }

        return view('livewire.donation-form')
            ->layout('components.layouts.donation', ['title' => $title]);
    }

    /**
     * Prevent "Public method [toJSON] not found on component" error.
     * This can happen if the component is serialized by frontend tools.
     *
     * @return array<mixed>
     */
    public function toJSON(): array
    {
        return [];
    }
}
