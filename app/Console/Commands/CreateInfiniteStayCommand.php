<?php

namespace App\Console\Commands;

use App\Services\Hoster\CloneHotel\CreateInifiteStay;
use Illuminate\Console\Command;

class CreateInfiniteStayCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:infinite-stay {hotel_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an infinite stay for presentation guest';

    protected $createInfiniteStay;

    public function __construct(CreateInifiteStay $createInfiniteStay)
    {
        parent::__construct();
        $this->createInfiniteStay = $createInfiniteStay;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hotelId = $this->argument('hotel_id');

        try {
            $stay = $this->createInfiniteStay->handle($hotelId);

            if ($stay) {
                $this->info('Infinite stay created/verified successfully');
            }

        } catch (\Exception $e) {
            $this->error('Error creating infinite stay: ' . $e->getMessage());
        }
    }
}
