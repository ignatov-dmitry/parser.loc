<?php
namespace App\Console\Commands;
use App\Parser\AVBY;
use Illuminate\Console\Command;

class AVBYParser extends Command {

    /**
     * The name and signature of the console command
     *
     * @var string
     */
    protected $signature = 'avby:parse';


    public function handle(){
        $avby = new AVBY();
        $avby->loadSitemap();
    }

}
