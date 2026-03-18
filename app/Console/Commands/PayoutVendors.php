<?php

namespace App\Console\Commands;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\Payout;
use App\Models\Vendor;
use App\Services\StripeConnectService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PayoutVendors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payout:vendors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perdorm Vendors Payout';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Monthley Payout process for vendors...');

        $vendors = Vendor::eligibleForPayout()->get();
        foreach ($vendors as $vendor) {
            $this->processPayout($vendor);
        }

        $this->info('Monthley Payout process completed.');

        return Command::SUCCESS;
    }

    /**
     * Calculate and transfer the monthly payout for a single vendor.
     */
    protected function processPayout(Vendor $vendor)
    {
        $this->info("processing payout for vendor [ID=$vendor->user_id] - '$vendor->store_name'");

        try {
            DB::beginTransaction();
            $startingFrom = Payout::where('vendor_id', $vendor->user_id)
                ->orderBy('until', 'desc')
                ->value('until');

            $startingFrom = $startingFrom ?? Carbon::make('1970-01-01');

            $until = Carbon::now()->subMonthNoOverflow()->endOfMonth();

            $vendorSubtotal = Order::query()
                ->where('vendor_user_id', $vendor->user_id)
                ->where('status', OrderStatusEnum::Paid->value)
                ->whereBetween('created_at', [$startingFrom, $until])
                ->sum('vendor_subtotal');

            if ($vendorSubtotal) {
                $this->info("Payout made with amount: $vendorSubtotal");

                Payout::create([
                    'vendor_id' => $vendor->user_id,
                    'amount' => $vendorSubtotal,
                    'starting_from' => $startingFrom,
                    'until' => $until,
                ]);

                $stripeConnect = app(StripeConnectService::class);
                $stripeConnect->transfer($vendor->user, (int) ($vendorSubtotal * 100));
            } else {
                $this->info('Nothing to process');
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error($e->getMessage());
        }
    }
}
