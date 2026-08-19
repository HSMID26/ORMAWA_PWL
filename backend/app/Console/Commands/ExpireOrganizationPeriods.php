<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OrganizationPeriod;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use Carbon\Carbon;

class ExpireOrganizationPeriods extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'organizations:expire-periods';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cek period organisasi dan set expire secara otomatis jika end_date telah lewat';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today()->toDateString();
        
        $expiredPeriods = OrganizationPeriod::with('organization.users')->where('status', 'active')
            ->whereDate('end_date', '<', $today)
            ->get();

        foreach ($expiredPeriods as $period) {
            $period->update(['status' => 'expired']);

            ActivityLogService::log(
                'expire', 
                'organization_periods', 
                'Periode organisasi otomatis kedaluwarsa: ' . $period->period_name, 
                clone $period
            );

            $adminUsers = $period->organization->users()->whereHas('roles', function($q) {
                $q->where('name', 'Admin Organisasi');
            })->get();

            foreach ($adminUsers as $admin) {
                NotificationService::send(
                    $admin,
                    'period_expired',
                    'Periode Telah Berakhir',
                    "Periode {$period->period_name} organisasi {$period->organization->nama} telah berakhir.",
                    '/organization/period'
                );
            }
        }

        // Notify for expiring soon (30 and 7 days)
        $expiring30 = OrganizationPeriod::with('organization.users')->where('status', 'active')
            ->whereDate('end_date', '=', Carbon::today()->addDays(30)->toDateString())
            ->get();
            
        foreach ($expiring30 as $period) {
             $adminUsers = $period->organization->users()->whereHas('roles', function($q) {
                $q->where('name', 'Admin Organisasi');
            })->get();

            foreach ($adminUsers as $admin) {
                NotificationService::send(
                    $admin,
                    'period_expiring_30',
                    'Periode Akan Berakhir',
                    "Periode {$period->period_name} organisasi {$period->organization->nama} akan berakhir dalam 30 hari.",
                    '/organization/period'
                );
            }
        }
        
        $expiring7 = OrganizationPeriod::with('organization.users')->where('status', 'active')
            ->whereDate('end_date', '=', Carbon::today()->addDays(7)->toDateString())
            ->get();
            
        foreach ($expiring7 as $period) {
             $adminUsers = $period->organization->users()->whereHas('roles', function($q) {
                $q->where('name', 'Admin Organisasi');
            })->get();

            foreach ($adminUsers as $admin) {
                NotificationService::send(
                    $admin,
                    'period_expiring_7',
                    'Periode Segera Berakhir',
                    "Periode {$period->period_name} organisasi {$period->organization->nama} akan berakhir dalam 7 hari.",
                    '/organization/period'
                );
            }
        }

        $this->info("Expired {$expiredPeriods->count()} periods.");
    }
}
