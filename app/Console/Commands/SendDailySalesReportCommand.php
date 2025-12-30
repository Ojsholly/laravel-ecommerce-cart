<?php

namespace App\Console\Commands;

use App\Jobs\SendDailySalesReport;
use Illuminate\Console\Command;

class SendDailySalesReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:daily-sales {date? : The date for the report (defaults to today)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily sales report email to admin';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $date = $this->argument('date') ?? today()->toDateString();

        $this->info("Dispatching daily sales report for {$date}...");

        SendDailySalesReport::dispatch($date);

        $this->info('Daily sales report job dispatched successfully!');

        return self::SUCCESS;
    }
}
