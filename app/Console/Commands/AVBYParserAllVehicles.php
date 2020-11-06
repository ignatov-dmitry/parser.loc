<?php
namespace App\Console\Commands;

use App\Facades\AVBY;
use Illuminate\Console\Command;

class AVBYParserAllVehicles extends Command {

    /**
     * The name and signature of the console command
     *
     * @var string
     */
    protected $signature = 'avby:parse_all';


    public function handle(){
        AVBY::checkAllVehicles();
    }

}
