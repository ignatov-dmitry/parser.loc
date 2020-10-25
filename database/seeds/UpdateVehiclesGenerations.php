<?php

use Illuminate\Database\Seeder;
use GuzzleHttp\Client;
use App\Vehicle;
use App\Category;
use App\Generation;
use App\Facades\AVBY;

class UpdateVehiclesGenerations extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $client = new Client();
        $vehicles = Vehicle::all();
        foreach ($vehicles as $vehicle){
            try {
                $json = $client->request('GET', 'https://api.av.by/offers/' . $vehicle->number);
            }
            catch (\GuzzleHttp\Exception\BadResponseException $exception){
                var_dump($exception->getCode());
                continue;
            }
            //$json = $client->request('GET', 'https://api.av.by/offers/' . $vehicle->number);
            $json = $client->request('GET', 'https://api.av.by/offers/20198866');
            $carInfo = json_decode($json->getBody()->getContents());

            if ($vehicle->url === $carInfo->publicUrl){
                $category = Category::whereUrl((array_reverse($carInfo->breadcrumbs))[1]->url)->first();

                $str =  str_replace(' · ', '% %', AVBY::getProperty($carInfo->properties, 'generation'));
                if ($str !== ""){
                    var_dump('https://api.av.by/offers/' . $vehicle->number);
                    var_dump('1 ' . $str);
                    $generation = Generation::where('name', 'LIKE', '%' . $str . '%')
                                            ->where('category_id', '=', $category->id)->first();
                }
                elseif($strYears = AVBY::getProperty($carInfo->properties, 'generation_with_years')){
                    var_dump('https://api.av.by/offers/' . $vehicle->number);
                    var_dump('2 ' . $strYears);
//                    $generation = Generation::where('name', 'LIKE', $str)
//                                            ->where('year_from', '=',)->first();
                }


                $vehicle->generation_id = $generation->id;
                //$vehicle->save();
            }

        }
    }
}
