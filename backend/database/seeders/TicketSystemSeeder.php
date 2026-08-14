<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Asset;
use App\Models\Ticket;
use App\Models\TicketWorklog;
use App\Models\MaintenanceSchedule;
use Illuminate\Database\Seeder;

class TicketSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create technician users
        $technician1 = User::firstOrCreate(
            ['email' => 'technician@kasir.app'],
            [
                'name' => 'Ahmad Teknisi',
                'password' => bcrypt('password'),
                'role' => 'staff',
                'is_technician' => true,
                'is_active' => true,
            ]
        );

        $technician2 = User::firstOrCreate(
            ['email' => 'maintenance@kasir.test'],
            [
                'name' => 'Budi Maintenance',
                'password' => bcrypt('password'),
                'role' => 'staff',
                'is_technician' => true,
                'is_active' => true,
            ]
        );

        // Get existing users
        $supervisor = User::where('role', 'supervisor')->first();
        $owner = User::where('role', 'owner')->first();

        // Get some assets
        $assets = Asset::with('location')->take(5)->get();

        if ($assets->isEmpty()) {
            $this->command->warn('No assets found. Please seed assets first.');
            return;
        }

        // Create incident tickets
        foreach ($assets->take(3) as $index => $asset) {
            $ticket = Ticket::create([
                'ticket_number' => sprintf('TKT-2026-%04d', $index + 1),
                'type' => 'INCIDENT',
                'asset_id' => $asset->id,
                'reported_by' => $owner->id ?? 1,
                'assigned_to' => $index % 2 === 0 ? $technician1->id : $technician2->id,
                'location_id' => $asset->location_id,
                'title' => match($index) {
                    0 => 'Komputer tidak bisa menyala',
                    1 => 'Printer error kertas macet',
                    2 => 'Monitor bergaris-garis',
                    default => 'Issue with asset',
                },
                'description' => match($index) {
                    0 => 'Komputer tiba-tiba mati dan tidak bisa dihidupkan kembali. Sudah dicoba tekan tombol power berkali-kali tapi tidak ada respon.',
                    1 => 'Printer selalu menunjukkan error "paper jam" padahal kertas sudah dikeluarkan. Tidak bisa print sama sekali.',
                    2 => 'Monitor menampilkan garis-garis vertikal di layar. Sudah coba restart tapi masih sama.',
                    default => 'There is an issue with this asset that needs attention.',
                },
                'priority' => $index === 0 ? 'HIGH' : 'NORMAL',
                'status' => match($index) {
                    0 => 'IN_PROGRESS',
                    1 => 'ASSIGNED',
                    2 => 'OPEN',
                    default => 'OPEN',
                },
                'category' => 'HARDWARE',
                'sla_due_date' => now()->addHours($index === 0 ? 24 : 72),
                'first_response_at' => $index !== 2 ? now()->subHours(2) : null,
                'created_at' => now()->subDays($index + 1),
            ]);

            // Add worklog for assigned tickets
            if ($index !== 2) {
                TicketWorklog::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $ticket->assigned_to,
                    'worklog_type' => 'STATUS_CHANGE',
                    'description' => 'Ticket assigned to ' . ($index % 2 === 0 ? 'Ahmad Teknisi' : 'Budi Maintenance'),
                    'is_internal' => false,
                ]);

                if ($index === 0) {
                    TicketWorklog::create([
                        'ticket_id' => $ticket->id,
                        'user_id' => $ticket->assigned_to,
                        'worklog_type' => 'WORK_DONE',
                        'description' => 'Sedang memeriksa power supply dan kabel power. Diduga masalah di power supply unit.',
                        'time_spent_minutes' => 30,
                        'is_internal' => false,
                        'created_at' => now()->subHours(1),
                    ]);
                }
            }
        }

        // Create maintenance tickets
        if ($assets->count() > 3) {
            $maintenanceAssets = $assets->slice(3, 2);
            
            foreach ($maintenanceAssets as $index => $asset) {
                $ticket = Ticket::create([
                    'ticket_number' => sprintf('TKT-2026-%04d', 4 + $index),
                    'type' => 'MAINTENANCE',
                    'asset_id' => $asset->id,
                    'reported_by' => $supervisor->id ?? $owner->id,
                    'assigned_to' => $technician1->id,
                    'location_id' => $asset->location_id,
                    'title' => 'Scheduled Preventive Maintenance',
                    'description' => 'Perform routine preventive maintenance check including cleaning, software updates, and hardware inspection.',
                    'priority' => 'NORMAL',
                    'status' => 'ASSIGNED',
                    'category' => 'HARDWARE',
                    'maintenance_type' => 'PREVENTIVE',
                    'scheduled_date' => now()->addDays(3 + $index),
                    'sla_due_date' => now()->addDays(3 + $index)->addHours(8),
                    'created_at' => now()->subDays(7),
                ]);

                TicketWorklog::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $supervisor->id ?? $owner->id,
                    'worklog_type' => 'COMMENT',
                    'description' => 'Maintenance scheduled as per quarterly maintenance plan.',
                    'is_internal' => false,
                ]);
            }
        }

        // Create maintenance schedules
        foreach ($assets as $index => $asset) {
            MaintenanceSchedule::create([
                'asset_id' => $asset->id,
                'maintenance_type' => $index % 2 === 0 ? 'PREVENTIVE' : 'INSPECTION',
                'frequency' => match($index % 4) {
                    0 => 'MONTHLY',
                    1 => 'QUARTERLY',
                    2 => 'SEMI_ANNUAL',
                    3 => 'ANNUAL',
                    default => 'QUARTERLY',
                },
                'last_maintenance_date' => now()->subMonths($index + 1),
                'next_maintenance_date' => now()->addDays(30 * ($index + 1)),
                'auto_create_ticket' => true,
                'is_active' => true,
                'notes' => 'Regular maintenance schedule for asset ' . $asset->asset_tag,
            ]);
        }

        $this->command->info('Ticket system seeded successfully!');
        $this->command->info('Created:');
        $this->command->info('- 2 Technician users');
        $this->command->info('- 5 Tickets (3 incidents + 2 maintenance)');
        $this->command->info('- ' . $assets->count() . ' Maintenance schedules');
    }
}
