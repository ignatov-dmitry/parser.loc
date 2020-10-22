<?php
namespace App\Console\Commands;

use App\Facades\AVBY;
use Illuminate\Console\Command;

class AVBYParser extends Command {

    /**
     * The name and signature of the console command
     *
     * @var string
     */
    protected $signature = 'avby:parse';


    public function handle(){
        AVBY::checkNewVehicle();
    }

}
