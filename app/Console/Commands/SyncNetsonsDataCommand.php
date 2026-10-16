<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Event;
use App\Models\FaiMember;
use App\Models\Slot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SyncNetsonsDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dfn:sync-netsons {--file=storage/sync/netsons_prod_data.json}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa i dati reali esportati da WordPress/Bedrock Netsons in TiDB Cloud / Database Laravel';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = base_path($this->option('file'));

        if (! File::exists($filePath)) {
            $this->error("File non trovato in: {$filePath}");

            return self::FAILURE;
        }

        $json = File::get($filePath);
        $data = json_decode($json, true);

        if (! is_array($data)) {
            $this->error('File JSON non valido.');

            return self::FAILURE;
        }

        $events = $data['events'] ?? [];
        $slots = $data['slots'] ?? [];
        $bookings = $data['bookings'] ?? [];
        $faiMembers = $data['fai_members'] ?? [];
        $productsMap = $data['products_map'] ?? [];

        $this->info('Inizio sincronizzazione dati reali da Netsons...');

        // Disabilita temporaneamente i vincoli di foreign key
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Booking::truncate();
        Slot::truncate();
        Event::truncate();
        FaiMember::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Inserimento Eventi
        $this->info('Importazione di '.count($events).' eventi...');
        $insertedEventIds = [];
        foreach ($events as $e) {
            $prodId = (string) ($e['product_id'] ?? '');
            $title = $productsMap[$prodId] ?? ($e['title'] ?? 'Evento #'.$e['id']);
            $insertedEventIds[] = (int) $e['id'];

            Event::create([
                'id' => (int) $e['id'],
                'product_id' => ! empty($e['product_id']) ? (int) $e['product_id'] : null,
                'title' => $title,
                'access_type' => $e['access_type'] ?? 'free_flow',
                'allocation_mode' => $e['allocation_mode'] ?? 'automatic',
                'approval_workflow' => $e['approval_workflow'] ?? 'auto',
                'payment_mode' => $e['payment_mode'] ?? 'online',
                'price_standard' => ! empty($e['price_standard']) ? (float) $e['price_standard'] : 0.00,
                'price_fai' => ! empty($e['price_fai']) ? (float) $e['price_fai'] : 0.00,
                'is_active' => ($e['status'] ?? '') === 'published',
                'event_date_start' => ! empty($e['event_date_start']) ? $e['event_date_start'] : null,
                'event_date_end' => ! empty($e['event_date_end']) ? $e['event_date_end'] : null,
                'event_time_start' => ! empty($e['event_time_start']) ? $e['event_time_start'] : null,
                'event_time_end' => ! empty($e['event_time_end']) ? $e['event_time_end'] : null,
                'location' => $e['location'] ?? null,
                'city' => $e['city'] ?? null,
                'description' => $e['description'] ?? null,
                'slot_duration' => ! empty($e['slot_duration']) ? (int) $e['slot_duration'] : 30,
                'slot_capacity' => ! empty($e['slot_capacity']) ? (int) $e['slot_capacity'] : 0,
                'slot_bonus' => ! empty($e['slot_bonus']) ? (int) $e['slot_bonus'] : 0,
                'first_slot_time' => ! empty($e['first_slot_time']) ? $e['first_slot_time'] : null,
                'last_slot_time' => ! empty($e['last_slot_time']) ? $e['last_slot_time'] : null,
                'total_capacity' => ! empty($e['total_capacity']) ? (int) $e['total_capacity'] : 0,
                'staff_config' => $e['staff_config'] ?? null,
                'status' => $e['status'] ?? 'published',
                'auto_cancel_hours' => ! empty($e['auto_cancel_hours']) ? (int) $e['auto_cancel_hours'] : 24,
                'detail_layout' => $e['detail_layout'] ?? 'layout2',
                'booking_opening_date' => ! empty($e['booking_opening_date']) ? $e['booking_opening_date'] : null,
                'booking_status' => $e['booking_status'] ?? 'open',
                'is_test_event' => (bool) ($e['is_test_event'] ?? false),
                'test_notification_email' => $e['test_notification_email'] ?? null,
                'created_at' => $e['created_at'] ?? now(),
                'updated_at' => $e['updated_at'] ?? now(),
            ]);
        }

        // Se ci sono prenotazioni associate a un ID evento eliminato (es. ID 2 di test pregressi), crea un evento archiviato placeholder
        $allBookingEventIds = array_unique(array_map(fn ($b) => (int) $b['event_id'], $bookings));
        foreach ($allBookingEventIds as $bEvtId) {
            if (! in_array($bEvtId, $insertedEventIds, true)) {
                Event::create([
                    'id' => $bEvtId,
                    'title' => "Evento Storico #{$bEvtId} (Archiviato)",
                    'access_type' => 'free_flow',
                    'status' => 'archived',
                    'is_active' => false,
                    'event_date_start' => '2026-08-01',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 2. Inserimento Slot
        if (! empty($slots)) {
            $this->info('Importazione di '.count($slots).' slot...');
            foreach ($slots as $s) {
                Slot::create([
                    'id' => (int) $s['id'],
                    'event_id' => (int) $s['event_id'],
                    'slot_date' => $s['slot_date'],
                    'start_time' => $s['slot_time_start'] ?? ($s['start_time'] ?? '10:00:00'),
                    'end_time' => $s['slot_time_end'] ?? ($s['end_time'] ?? '11:00:00'),
                    'max_capacity' => (int) ($s['capacity'] ?? ($s['max_capacity'] ?? 0)),
                    'overbooking_max' => (int) ($s['bonus'] ?? ($s['overbooking_max'] ?? 0)),
                    'booked_count' => (int) ($s['booked_count'] ?? 0),
                    'is_locked' => (bool) ($s['is_locked'] ?? false),
                    'status' => $s['status'] ?? 'active',
                    'created_at' => $s['created_at'] ?? now(),
                    'updated_at' => $s['updated_at'] ?? now(),
                ]);
            }
        }

        // 3. Inserimento Prenotazioni
        $this->info('Importazione di '.count($bookings).' prenotazioni...');
        foreach ($bookings as $b) {
            Booking::create([
                'id' => (int) $b['id'],
                'order_id' => ! empty($b['order_id']) ? (int) $b['order_id'] : null,
                'event_id' => (int) $b['event_id'],
                'user_id' => ! empty($b['user_id']) ? (int) $b['user_id'] : null,
                'customer_name' => $b['customer_name'] ?? null,
                'customer_email' => $b['customer_email'] ?? null,
                'customer_phone' => $b['customer_phone'] ?? null,
                'total_persons' => (int) ($b['total_persons'] ?? 1),
                'persons_standard' => (int) ($b['persons_standard'] ?? 0),
                'persons_fai' => (int) ($b['persons_fai'] ?? 0),
                'status' => $b['status'] ?? 'confirmed',
                'qr_code_hash' => $b['qr_token'] ?? ($b['qr_code_hash'] ?? null),
                'checkin_status' => ! empty($b['checked_in_at']) ? 'completed' : 'pending',
                'checkin_at' => $b['checked_in_at'] ?? null,
                'checked_in_by' => ! empty($b['checked_in_by']) ? (int) $b['checked_in_by'] : null,
                'payment_method' => $b['payment_method'] ?? 'stripe',
                'amount_due' => ! empty($b['amount_due']) ? (float) $b['amount_due'] : 0.00,
                'amount_paid' => ! empty($b['amount_paid']) ? (float) $b['amount_paid'] : 0.00,
                'notes' => $b['notes'] ?? null,
                'created_at' => $b['created_at'] ?? now(),
                'updated_at' => $b['updated_at'] ?? now(),
            ]);
        }

        // 4. Inserimento Membri FAI
        if (! empty($faiMembers)) {
            $this->info('Importazione di '.count($faiMembers).' membri FAI...');
            foreach ($faiMembers as $m) {
                FaiMember::create([
                    'id' => (int) $m['id'],
                    'first_name' => $m['first_name'] ?? null,
                    'last_name' => $m['last_name'] ?? null,
                    'email' => $m['email'] ?? null,
                    'phone' => $m['phone'] ?? null,
                    'card_number' => $m['card_number'] ?? (string) $m['id'],
                    'expiry_date' => ! empty($m['expiry_date']) ? $m['expiry_date'] : null,
                    'status' => (! empty($m['is_valid']) || ($m['status'] ?? '') === 'active') ? 'active' : 'inactive',
                    'created_at' => $m['created_at'] ?? now(),
                    'updated_at' => $m['updated_at'] ?? now(),
                ]);
            }
        }

        $this->info('Sincronizzazione completata con successo!');
        $this->info('Eventi nel DB: '.Event::count());
        $this->info('Prenotazioni nel DB: '.Booking::count());
        $this->info('Soci FAI nel DB: '.FaiMember::count());

        return self::SUCCESS;
    }
}
