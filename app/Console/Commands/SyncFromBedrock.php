<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Event;
use App\Models\FaiMember;
use App\Models\Slot;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class SyncFromBedrock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dfn:sync {--fresh : Tronca le tabelle locali prima della sincronizzazione}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincronizza le tabelle wp_dfn_* da Bedrock MySQL al database Laravel';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('====================================================');
        $this->info('  DFN Bedrock -> Laravel Database Synchronization   ');
        $this->info('====================================================');

        // 1. Verifica connessione Bedrock
        try {
            DB::connection('bedrock')->getPdo();
            $this->info('✓ Connessione a Bedrock MySQL stabilita con successo.');
        } catch (\Throwable $e) {
            $this->error('✗ Impossibile connettersi al database Bedrock: '.$e->getMessage());

            return Command::FAILURE;
        }

        if ($this->option('fresh')) {
            $confirm = $this->input->isInteractive()
                ? $this->confirm('Sei sicuro di voler svuotare le tabelle locali events, slots, bookings, fai_members?')
                : true;

            if ($confirm) {
                $this->warn('Svuotamento tabelle locali...');
                Schema::disableForeignKeyConstraints();
                Booking::truncate();
                Slot::truncate();
                Event::truncate();
                FaiMember::truncate();
                Schema::enableForeignKeyConstraints();
                $this->info('✓ Tabelle locali svuotate.');
            }
        }

        $stats = [
            'events' => 0,
            'slots' => 0,
            'bookings' => 0,
            'fai_members' => 0,
        ];

        // 2. Sincronizzazione Events
        $stats['events'] = $this->syncEvents();

        // 3. Sincronizzazione Slots
        $stats['slots'] = $this->syncSlots();

        // 4. Sincronizzazione Bookings
        $stats['bookings'] = $this->syncBookings();

        // 5. Sincronizzazione FaiMembers
        $stats['fai_members'] = $this->syncFaiMembers();

        // 6. Verifica o creazione Admin predefinito Filament
        $this->ensureAdminUser();

        $this->newLine();
        $this->info('====================================================');
        $this->info('  Riepilogo Sincronizzazione Completata con Successo ');
        $this->info('====================================================');
        $this->table(
            ['Entità', 'Tabella Bedrock', 'Tabella Laravel', 'Record Sincronizzati'],
            [
                ['Event', 'wp_dfn_events', 'events', $stats['events']],
                ['Slot', 'wp_dfn_event_slots / wp_dfn_slots', 'slots', $stats['slots']],
                ['Booking', 'wp_dfn_bookings', 'bookings', $stats['bookings']],
                ['FaiMember', 'wp_dfn_fai_members', 'fai_members', $stats['fai_members']],
            ]
        );

        return Command::SUCCESS;
    }

    /**
     * Assicura la presenza dell'utente amministratore per il pannello Filament.
     */
    protected function ensureAdminUser(): void
    {
        $this->newLine();
        $this->line('<fg=cyan>→ Verifica utente amministratore Filament...</>');

        $adminEmail = 'alexvezz87@gmail.com';
        $user = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Alex Vezz',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $this->info("  ✓ Utente amministratore Filament pronto: {$user->email}");
    }

    /**
     * Risolve il nome effettivo della tabella nel connection Bedrock.
     */
    protected function resolveBedrockTableName(array $candidates): ?string
    {
        $schema = Schema::connection('bedrock');
        $prefix = DB::connection('bedrock')->getTablePrefix();

        foreach ($candidates as $name) {
            if ($schema->hasTable($name)) {
                return $name;
            }
            if ($prefix && str_starts_with($name, $prefix)) {
                $stripped = substr($name, strlen($prefix));
                if ($schema->hasTable($stripped)) {
                    return $stripped;
                }
            }
        }

        return null;
    }

    /**
     * Sincronizza gli eventi da wp_dfn_events
     */
    protected function syncEvents(): int
    {
        $this->newLine();
        $this->line('<fg=cyan>→ Sincronizzazione Events...</>');

        $tableName = $this->resolveBedrockTableName(['dfn_events', 'wp_dfn_events']);
        if (! $tableName) {
            $this->warn('  ! Tabella wp_dfn_events non trovata in Bedrock.');

            return 0;
        }

        $records = DB::connection('bedrock')->table($tableName)->get();
        $count = 0;

        $postsTable = $this->resolveBedrockTableName(['posts', 'wp_posts']);

        $progressBar = $this->output->createProgressBar($records->count());
        $progressBar->start();

        foreach ($records as $row) {
            $title = $row->title ?? null;
            if (empty($title) && ! empty($row->product_id) && $postsTable) {
                $post = DB::connection('bedrock')->table($postsTable)->where('ID', $row->product_id)->first();
                if ($post && ! empty($post->post_title)) {
                    $title = $post->post_title;
                }
            }

            Event::updateOrCreate(
                ['id' => $row->id],
                [
                    'product_id' => $row->product_id ?? null,
                    'title' => $title ?? "Evento #{$row->id}",
                    'access_type' => $row->access_type ?? 'slots',
                    'allocation_mode' => $row->allocation_mode ?? null,
                    'approval_workflow' => $row->approval_workflow ?? null,
                    'payment_mode' => $row->payment_mode ?? 'online',
                    'price_standard' => $row->price_standard ?? 0.00,
                    'price_fai' => $row->price_fai ?? 0.00,
                    'is_active' => (bool) ($row->is_active ?? ($row->status === 'published' ? 1 : 0)),
                    'event_date_start' => $row->event_date_start ?? null,
                    'event_date_end' => $row->event_date_end ?? null,
                    'event_time_start' => $row->event_time_start ?? null,
                    'event_time_end' => $row->event_time_end ?? null,
                    'location' => $row->location ?? null,
                    'city' => $row->city ?? null,
                    'description' => $row->description ?? null,
                    'slot_duration' => $row->slot_duration ?? 30,
                    'slot_capacity' => $row->slot_capacity ?? 0,
                    'slot_bonus' => $row->slot_bonus ?? 0,
                    'first_slot_time' => $row->first_slot_time ?? null,
                    'last_slot_time' => $row->last_slot_time ?? null,
                    'total_capacity' => $row->total_capacity ?? 0,
                    'staff_config' => $row->staff_config ?? null,
                    'status' => $row->status ?? 'draft',
                    'auto_cancel_hours' => $row->auto_cancel_hours ?? 24,
                    'detail_layout' => $row->detail_layout ?? 'auto',
                    'booking_opening_date' => $row->booking_opening_date ?? null,
                    'booking_status' => $row->booking_status ?? 'open',
                    'is_test_event' => (bool) ($row->is_test_event ?? 0),
                    'test_notification_email' => $row->test_notification_email ?? null,
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ]
            );
            $count++;
            $progressBar->advance();
        }

        // Recupera ed inserisce eventuali eventi orfani referenziati nelle prenotazioni
        $bookingsTable = $this->resolveBedrockTableName(['dfn_bookings', 'wp_dfn_bookings']);
        if ($bookingsTable) {
            $existingEventIds = $records->pluck('id')->toArray();
            $orphanEventIds = DB::connection('bedrock')->table($bookingsTable)
                ->whereNotIn('event_id', $existingEventIds)
                ->distinct()
                ->pluck('event_id');

            foreach ($orphanEventIds as $orphanId) {
                Event::updateOrCreate(
                    ['id' => $orphanId],
                    [
                        'title' => "Evento #{$orphanId} (Storico / Archiviato)",
                        'status' => 'archived',
                        'is_active' => false,
                        'access_type' => 'slots',
                        'description' => 'Evento storico o di test non presente nella tabella eventi attiva, ma con prenotazioni collegate.',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
                $count++;
            }
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("  ✓ {$count} eventi sincronizzati (inclusi storici).");

        return $count;
    }

    /**
     * Sincronizza gli slot da wp_dfn_event_slots / wp_dfn_slots
     */
    protected function syncSlots(): int
    {
        $this->newLine();
        $this->line('<fg=cyan>→ Sincronizzazione Slots...</>');

        $tableName = $this->resolveBedrockTableName(['dfn_event_slots', 'dfn_slots', 'wp_dfn_event_slots', 'wp_dfn_slots']);
        if (! $tableName) {
            $this->warn('  ! Tabella slot non trovata in Bedrock.');

            return 0;
        }

        $records = DB::connection('bedrock')->table($tableName)->get();
        $count = 0;

        $progressBar = $this->output->createProgressBar($records->count());
        $progressBar->start();

        foreach ($records as $row) {
            // Verifica che l'evento associato esista per rispettare il vincolo foreign key
            if (! Event::where('id', $row->event_id)->exists()) {
                continue;
            }

            $startTime = $row->start_time ?? $row->slot_time_start ?? '00:00:00';
            $endTime = $row->end_time ?? $row->slot_time_end ?? '00:00:00';
            $maxCapacity = $row->max_capacity ?? $row->capacity ?? 0;
            $overbookingMax = $row->overbooking_max ?? $row->bonus_capacity ?? 0;
            $status = $row->status ?? (! empty($row->is_locked) ? 'closed' : 'active');

            Slot::updateOrCreate(
                ['id' => $row->id],
                [
                    'event_id' => $row->event_id,
                    'slot_date' => $row->slot_date,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'max_capacity' => $maxCapacity,
                    'overbooking_max' => $overbookingMax,
                    'booked_count' => $row->booked_count ?? 0,
                    'is_locked' => (bool) ($row->is_locked ?? 0),
                    'status' => $status,
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? $row->created_at ?? now(),
                ]
            );
            $count++;
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("  ✓ {$count} slot sincronizzati.");

        return $count;
    }

    /**
     * Sincronizza le prenotazioni da wp_dfn_bookings
     */
    protected function syncBookings(): int
    {
        $this->newLine();
        $this->line('<fg=cyan>→ Sincronizzazione Bookings...</>');

        $tableName = $this->resolveBedrockTableName(['dfn_bookings', 'wp_dfn_bookings']);
        if (! $tableName) {
            $this->warn('  ! Tabella wp_dfn_bookings non trovata in Bedrock.');

            return 0;
        }

        $records = DB::connection('bedrock')->table($tableName)->get();
        $count = 0;

        $progressBar = $this->output->createProgressBar($records->count());
        $progressBar->start();

        foreach ($records as $row) {
            // Verifica che l'evento associato esista
            if (! Event::where('id', $row->event_id)->exists()) {
                continue;
            }

            $qrHash = $row->qr_code_hash ?? $row->qr_token ?? null;
            $checkinAt = $row->checkin_at ?? $row->checked_in_at ?? null;
            $checkinStatus = $row->checkin_status ?? (! empty($checkinAt) ? 'checked_in' : 'pending');

            Booking::updateOrCreate(
                ['id' => $row->id],
                [
                    'order_id' => $row->order_id ?? null,
                    'event_id' => $row->event_id,
                    'user_id' => $row->user_id ?? null,
                    'customer_name' => $row->customer_name ?? null,
                    'customer_email' => $row->customer_email ?? null,
                    'customer_phone' => $row->customer_phone ?? null,
                    'total_persons' => $row->total_persons ?? 1,
                    'persons_standard' => $row->persons_standard ?? 0,
                    'persons_fai' => $row->persons_fai ?? 0,
                    'status' => $row->status ?? 'confirmed',
                    'qr_code_hash' => $qrHash,
                    'checkin_status' => $checkinStatus,
                    'checkin_at' => $checkinAt,
                    'checked_in_by' => $row->checked_in_by ?? null,
                    'payment_method' => $row->payment_method ?? null,
                    'amount_due' => $row->amount_due ?? 0.00,
                    'amount_paid' => $row->amount_paid ?? 0.00,
                    'notes' => $row->notes ?? null,
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ]
            );
            $count++;
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("  ✓ {$count} prenotazioni sincronizzate.");

        return $count;
    }

    /**
     * Sincronizza i soci FAI da wp_dfn_fai_members
     */
    protected function syncFaiMembers(): int
    {
        $this->newLine();
        $this->line('<fg=cyan>→ Sincronizzazione FaiMembers...</>');

        $tableName = $this->resolveBedrockTableName(['dfn_fai_members', 'wp_dfn_fai_members']);
        if (! $tableName) {
            $this->warn('  ! Tabella wp_dfn_fai_members non trovata in Bedrock.');

            return 0;
        }

        $records = DB::connection('bedrock')->table($tableName)->get();
        $count = 0;

        $progressBar = $this->output->createProgressBar($records->count());
        $progressBar->start();

        foreach ($records as $row) {
            $expiryDate = $row->expiry_date ?? $row->card_expiry ?? null;
            $status = $row->status ?? (! empty($row->verified) ? 'active' : 'pending');

            FaiMember::updateOrCreate(
                ['id' => $row->id],
                [
                    'card_number' => $row->card_number,
                    'first_name' => $row->first_name ?? null,
                    'last_name' => $row->last_name ?? null,
                    'email' => $row->email ?? null,
                    'phone' => $row->phone ?? null,
                    'status' => $status,
                    'expiry_date' => $expiryDate,
                    'verified' => (bool) ($row->verified ?? false),
                    'verified_by' => $row->verified_by ?? null,
                    'verified_at' => $row->verified_at ?? null,
                    'user_id' => $row->user_id ?? null,
                    'card_type' => $row->card_type ?? 'INDIVIDUALE',
                    'is_volunteer' => (bool) ($row->is_volunteer ?? false),
                    'volunteer_status' => $row->volunteer_status ?? 'active',
                    'volunteer_notes' => $row->volunteer_notes ?? null,
                    'joined_date' => $row->joined_date ?? null,
                    'is_guide' => (bool) ($row->is_guide ?? false),
                    'has_safety_course' => (bool) ($row->has_safety_course ?? false),
                    'fai_registry_id' => $row->fai_registry_id ?? null,
                    'is_sivol_registered' => (bool) ($row->is_sivol_registered ?? false),
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ]
            );
            $count++;
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("  ✓ {$count} soci FAI sincronizzati.");

        return $count;
    }
}
