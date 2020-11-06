<?php
namespace App\Console\Commands;

use App\Facades\AVBY;
use Illuminate\Console\Command;

class AVBYParserNewVehicles extends Command {

    /**
     * The name and signature of the console command
     *
     * @var string
     */
    protected $signature = 'avby:parse_new';


    public function handle(){
        AVBY::checkNewVehicles();
    }

}
