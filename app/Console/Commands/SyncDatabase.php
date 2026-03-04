<?php

namespace App\Console\Commands;

use App\Enums\DonationStatus;
use App\Enums\FilingStatus;
use App\Enums\PaymentMethod;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\School;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SyncDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync data from legacy database to new database structure';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Starting database sync...');

        // Run Roles and Permissions Seeder
        $this->call('db:seed', [
            '--class' => 'RolesAndPermissionsSeeder',
            '--force' => true,
        ]);

        // Unguard models to allow setting IDs manually
        Model::unguard();

        $this->configureLegacyConnection();

        $this->syncSchools();
        $this->syncUsers();
        $this->syncDonations();
        $this->createUsersForDonors();
        $this->syncTransactions();
        $this->assignAdminRoles();

        // Reguard models (good practice, though script ends here)
        Model::reguard();

        $this->info('Database sync completed successfully.');
    }

    protected function configureLegacyConnection(): void
    {
        // @phpstan-ignore-next-line
        $databaseUrl = env('DATABASE_URL');

        if (! $databaseUrl) {
            $this->error('DATABASE_URL not found in .env');
            exit(1);
        }

        $parsedUrl = parse_url($databaseUrl);

        $config = [
            'driver' => 'mysql',
            'host' => $parsedUrl['host'] ?? '127.0.0.1',
            'port' => $parsedUrl['port'] ?? '3306',
            'database' => ltrim($parsedUrl['path'], '/'),
            'username' => $parsedUrl['user'] ?? 'root',
            'password' => $parsedUrl['pass'] ?? '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ];

        config(['database.connections.legacy' => $config]);
    }

    protected function syncSchools(): void
    {
        $this->info('Syncing schools...');

        $legacySchools = DB::connection('legacy')->table('school')->get();
        $bar = $this->output->createProgressBar($legacySchools->count());

        foreach ($legacySchools as $legacySchool) {
            School::withTrashed()->updateOrCreate(
                ['id' => $legacySchool->id],
                [
                    'ibe_id' => $legacySchool->ibe_id,
                    'name' => $legacySchool->name,
                    'type' => $legacySchool->type,
                ]
            );
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    protected function syncUsers(): void
    {
        $this->info('Syncing users...');

        $legacyUsers = DB::connection('legacy')->table('user')->get();
        $bar = $this->output->createProgressBar($legacyUsers->count());

        foreach ($legacyUsers as $legacyUser) {
            // Use DB facade to bypass 'hashed' cast on User model which fails verification on legacy hashes
            $exists = DB::table('users')->where('id', $legacyUser->id)->exists();

            $data = [
                'name' => $legacyUser->full_name,
                'email' => $legacyUser->email,
                'password' => $legacyUser->password,
                'updated_at' => now(),
            ];

            if (! $exists) {
                $data['id'] = $legacyUser->id;
                $data['created_at'] = now();
                DB::table('users')->insert($data);
            } else {
                DB::table('users')->where('id', $legacyUser->id)->update($data);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    protected function syncDonations(): void
    {
        $this->info('Syncing donations...');

        $legacyDonations = DB::connection('legacy')->table('donation')->get();
        $bar = $this->output->createProgressBar($legacyDonations->count());

        foreach ($legacyDonations as $legacyDonation) {
            // 1. Create or Update Donor
            $donor = Donor::withTrashed()->updateOrCreate(
                ['email' => $legacyDonation->email],
                [
                    'first_name' => $legacyDonation->first_name,
                    'last_name' => $legacyDonation->last_name,
                    'title' => $legacyDonation->title,
                    'spouse_title' => $legacyDonation->title2,
                    'spouse_first_name' => $legacyDonation->first_name2,
                    'spouse_last_name' => $legacyDonation->last_name2,
                    'phone' => Donor::formatPhone($legacyDonation->phone_number),
                ]
            );

            // 2. Create or Update Addresses for Donor
            if ($legacyDonation->address_street1) {
                $donor->addresses()->withTrashed()->updateOrCreate(
                    ['type' => 'mailing'],
                    [
                        'street' => $legacyDonation->address_street1,
                        'street_line_2' => $legacyDonation->address_street2 ?? null,
                        'city' => $legacyDonation->address_city,
                        'state' => $legacyDonation->address_state,
                        'postal_code' => $legacyDonation->address_postal_code,
                        'country' => $legacyDonation->address_country ?? 'US',
                    ]
                );
            }

            if ($legacyDonation->billing_address_street1) {
                $donor->addresses()->withTrashed()->updateOrCreate(
                    ['type' => 'billing'],
                    [
                        'street' => $legacyDonation->billing_address_street1,
                        'street_line_2' => $legacyDonation->billing_address_street2 ?? null,
                        'city' => $legacyDonation->billing_address_city,
                        'state' => $legacyDonation->billing_address_state,
                        'postal_code' => $legacyDonation->billing_address_postal_code,
                        'country' => $legacyDonation->billing_address_country ?? 'US',
                    ]
                );
            }

            // 3. Create Donation
            Donation::withTrashed()->updateOrCreate(
                ['id' => $legacyDonation->id],
                [
                    'school_id' => ($legacyDonation->school_donation_id && $legacyDonation->school_donation_id > 0) ? $legacyDonation->school_donation_id : null,
                    'donor_id' => $donor->id,
                    'payment_method' => PaymentMethod::Card,
                    'amount' => $legacyDonation->amount,
                    'status' => DonationStatus::Paid, // Defaulting to paid for legacy data
                    'filing_year' => $legacyDonation->filing_year,
                    'filing_status' => match ($legacyDonation->filing_status) {
                        1 => FilingStatus::MarriedFilingJointly,
                        2 => FilingStatus::MarriedFilingSeparately,
                        3 => FilingStatus::Single,
                        default => FilingStatus::Single,
                    },
                    'qco' => $legacyDonation->qco,
                    'school_name_snapshot' => $legacyDonation->school_donation_name,
                    'tax_professional_name' => $legacyDonation->tax_professional_name,
                    'tax_professional_phone' => $legacyDonation->tax_professional_phone,
                    'tax_professional_email' => $legacyDonation->tax_professional_email,
                    'created_at' => $legacyDonation->created,
                    'updated_at' => $legacyDonation->created,
                ]
            );
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    protected function syncTransactions(): void
    {
        $this->info('Syncing transactions...');

        $legacyTransactions = DB::connection('legacy')->table('transaction')->get();
        $bar = $this->output->createProgressBar($legacyTransactions->count());

        foreach ($legacyTransactions as $legacyTransaction) {
            // Find associated donation
            // Since we removed payment_intent_id from donations, we need to find the donation another way.
            // In the legacy system, transactions were linked to donations via payment_intent_id.
            // But we just synced donations using their legacy ID.
            // And legacy transactions don't seem to have a donation_id column based on the code above?
            // Wait, the legacy donation table has payment_intent_id.
            // So we can find the donation that has the same legacy ID as the donation that had this payment_intent_id?
            // Actually, simpler: We synced donations preserving their IDs.
            // If the legacy transaction has a way to link to the donation, we use that.
            // If the legacy transaction ONLY links via payment_intent_id, we have a problem if we don't store it on Donation.

            // Let's look at how we can link them.
            // We need to find the donation ID that corresponds to the payment_intent_id.
            // Since we don't store payment_intent_id on Donation anymore, we can't look it up directly.
            // However, we are in a migration script. We can look up the legacy donation that has this payment_intent_id,
            // get its ID, and then find the new Donation with that ID.

            $legacyDonation = DB::connection('legacy')->table('donation')
                ->where('payment_intent_id', $legacyTransaction->payment_intent_id)
                ->first();

            $donationId = $legacyDonation ? $legacyDonation->id : null;

            Transaction::withTrashed()->updateOrCreate(
                ['id' => $legacyTransaction->id],
                [
                    'donation_id' => $donationId,
                    'payment_intent_id' => $legacyTransaction->payment_intent_id,
                    'amount' => $legacyTransaction->amount,
                    'status' => $legacyTransaction->status,
                    'created_at' => $legacyTransaction->created,
                    'updated_at' => $legacyTransaction->created,
                ]
            );
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    protected function createUsersForDonors(): void
    {
        $this->info('Creating users for donors...');

        // We want to process all donors to ensure they have users and roles
        // But we can optimize by checking those without user_id first if we only cared about linking
        // However, the requirement implies we should ensure all donors have users and the role.
        // Let's iterate all donors to be safe, or maybe just those without user_id?
        // "create users ... for every email in the donor model"
        // "Assign the donor role to them"
        // "Make sure the existing database users are imported first"

        // If I only select whereNull('user_id'), I might miss donors that have a user_id but that user doesn't have the role?
        // But `syncDonations` creates donors. It doesn't set `user_id`.
        // So initially all donors created by `syncDonations` will have null `user_id`.
        // So `whereNull('user_id')` is a good optimization.

        $donors = Donor::whereNull('user_id')->get();
        $bar = $this->output->createProgressBar($donors->count());

        foreach ($donors as $donor) {
            // Check if user exists by email
            $user = User::where('email', $donor->email)->first();

            if (! $user) {
                $password = Str::random(16);
                $user = User::create([
                    'name' => "{$donor->first_name} {$donor->last_name}",
                    'email' => $donor->email,
                    'password' => Hash::make($password),
                ]);

                $user->assignRole('donor');
            } else {
                if (! $user->hasRole('donor')) {
                    $user->assignRole('donor');
                }
            }

            $donor->update(['user_id' => $user->id]);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    protected function assignAdminRoles(): void
    {
        $this->info('Assigning admin roles...');

        $emails = [
            'nathan@halabuda.io',
            'kim@ibescholarships.org',
            'drew@drewroberts.com',
        ];

        foreach ($emails as $email) {
            $user = User::where('email', $email)->first();

            if ($user) {
                $user->assignRole('admin');
                $this->info("Assigned admin role to {$email}");
            }
        }
        $this->newLine();
    }
}
