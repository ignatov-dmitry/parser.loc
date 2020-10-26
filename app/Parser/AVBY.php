<?php


namespace App\Parser;


use App\Category;
use App\City;
use App\Facades\TelegramBot;
use App\Filter;
use App\FilterVehicleModels;
use App\Region;
use App\TelegramUser;
use App\Vehicle;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class AVBY
{

    private $links = array(
        'pages' => array(),
        'category_pages'  => array()
    );


    private function doRequest(string $url, array $requestParameters = [], string $method = 'GET', array $clientParameters = []){
        $client = new Client($clientParameters);
        try {
            return $client->request($method, $url, $requestParameters)->getBody()->getContents();
        } catch (GuzzleException $e) {
            return $e;
        }
    }


    public function checkNewVehicle(){
        $json = $this->doRequest('https://api.av.by/offer-types/cars/filters/main/apply', [
            'headers' => [
                'Content-Type'     => 'application/json',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36 OPR/69.0.3686.95'
            ],
            'body' => '{"page":1,"properties":[{"name":"price_currency","value":2},{"name":"creation_date","value":10}],"sorting":4}'
        ],'POST');

        $pages = (json_decode($json))->pageCount;

        for($i = 1; $i < $pages; $i++){

            $json = $this->doRequest('https://api.av.by/offer-types/cars/filters/main/apply', [
                'headers' => [
                    'Content-Type'     => 'application/json',
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36 OPR/69.0.3686.95'
                ],
                'body' => '{"page":' . $i . ',"properties":[{"name":"price_currency","value":2},{"name":"creation_date","value":10}],"sorting":4}'
            ],'POST');


            $list = (json_decode($json))->adverts;
            $this->links['cars']['items'] = array();
            $dublicate = false;


            foreach ($list as $item){

                $url = $item->publicUrl;
                $urlToArray = explode('/', $url);
                $number = array_pop($urlToArray);

                if (Vehicle::latest()->firstWhere('url','=', $url) !== null){
                    $dublicate = true;
                    break;
                }


                $this->links['cars']['items'][] = array(
                    'url'         => $url,
                    'name'        => $item->metaInfo->h1 ,
                    'number'      => $number,
                    'category_id' => Category::where('url', 'LIKE', implode('/', $urlToArray) . '%')->first()->id,
                    'platform_id' => 1,
                    'year'        => $item->metadata->year,
                    'price'       => $item->price->usd->amount ,
                    'created_at'  => new \DateTime(),
                    'city_id'     => $city_id = $this->getCityIdFromName($item->shortLocationName),
                    'region_id'   => $region_id = City::where('id', '=', $city_id)->first()->region_id,
                    'country_id'  => Region::where('id', '=', $region_id)->first()->country_id
                );
            }



            $telegramUsers = TelegramUser::all();
            $cars = collect($this->links['cars']['items']);
            if ($this->links['cars']['items']){
                Vehicle::insert($this->links['cars']['items']);


                foreach ($telegramUsers as $telegramUser) {
                    $userCars = array();
                    $in = array();
                    $filteredCars = $cars;
                    $filters = Filter::where('chat_id' , '=', $telegramUser->chat_id)->get();


                    foreach ($filters as $filter){
                        $modles = FilterVehicleModels::where('filter_id', '=', $filter->id)->get();
                        $modles = $modles->toArray();
                        if ($filter->country_id != 0){
                            $filteredCars = $filteredCars->where('country_id', '=', $filter->country_id);
                        }

                        if ($filter->region_id != 0){
                            $filteredCars = $filteredCars->where('region_id', '=', $filter->region_id);
                        }

                        if ($filter->city_id != 0){
                            $filteredCars = $filteredCars->where('city_id', '=', $filter->city_id);
                        }


                        if ($modles){
                            foreach ($modles as $category){
                                $in[] = $category['category_id'];
                            }
                            $filteredCars = $filteredCars->whereIn('category_id', $in);
                        }
                        elseif ($filter->brand != 0){
                            $categories = Category::where('parent_id', '=', $filter->brand)->get('id');
                            foreach ($categories->toArray() as $category){
                                $in[] = $category['id'];
                            }
                            $filteredCars = $filteredCars->whereIn('category_id', $in);
                        }


                        $userCars = array_merge($userCars, $filteredCars->toArray());
                    }

                    if ($userCars){
                        $txt = array_map(function ($item){
                            return $item['url'];
                        }, $userCars);

                        TelegramBot::sendMessage($telegramUser->chat_id, "Новые автомобили:\n" . implode("\n ", $txt));
                    }

                }

            }

            if ($dublicate){
                break;
            }

        }

    }





    public function getCityIdFromName(string $name){
        return City::whereName($name)->first()->id;
    }

    public function getProperty(array $properties, string $propertyName){
        foreach ($properties as $property){
            if ($property->name === $propertyName){
                return $property->value;
            }
        }

        return false;
    }


    public function getLinkBrandCategory($mapping_id){
        $json = $this->doRequest('https://api.av.by/offer-types/cars/filters/main/apply', [
            'headers' => [
                'Content-Type'     => 'application/json',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36 OPR/69.0.3686.95'
            ],
            'body' => '{"properties":[{"modified":true,"name":"brands","property":5,"value":[[{"name":"brand","value":' . $mapping_id . ',"modified":true,"previousValue":null}]]},{"name":"price_currency","value":2}]}'
        ],'POST');

        $data = json_decode($json);

        return $data->seo->currentPage->url;
    }


    public function getLinkModelCategory($brandMappingId, $ModelMappingId){
        $json = $this->doRequest('https://api.av.by/offer-types/cars/filters/main/apply', [
            'headers' => [
                'Content-Type'     => 'application/json',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36 OPR/69.0.3686.95'
            ],
            'body' => '{"properties":[{"modified":true,"name":"brands","property":5,"value":[[{"name":"brand","value":' . $brandMappingId . '},{"name":"model","value":' . $ModelMappingId . ',"modified":true,"previousValue":null}]]},{"name":"price_currency","value":2}]}'
        ],'POST');

        $data = json_decode($json);

        return $data->seo->currentPage->url;
    }

}
