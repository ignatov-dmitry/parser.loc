<?php


namespace App\Parser;


use App\Category;
use App\City;
use App\Country;
use App\Facades\TelegramBot;
use App\Filter;
use App\FilterVehicleModels;
use App\Generation;
use App\Region;
use App\TelegramUser;
use App\Vehicle;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Pool;
use XmlParser;

class AVBY
{

    private $links = array(
        'pages' => array(),
        'category_pages'  => array()
    );
    private $cacheKeys = array();
    private $cars = array();

    private $counter;
    private $iterator;
    private $responses = array();

    private function doRequest(string $url, array $requestParameters = [], string $method = 'GET', array $clientParameters = []){
        $client = new Client($clientParameters);
        try {
            return $client->request($method, $url, $requestParameters)->getBody()->getContents();
        } catch (GuzzleException $e) {
            return $e;
        }
    }


    public function loadSitemap(){
        $client = new Client();
        $xml = XmlParser::load('https://cars.av.by/sitemap.xml');

        $sitemap = $xml->parse([
            'sitemap'     => ['uses' => 'sitemap[loc,lastmod]']
        ]);

        foreach ($sitemap['sitemap'] as $item){
            $client->get($item['loc'],[
                'sink' => 'tmp/' . basename($item['loc']),
                'allow_redirects' => false
            ]);
        }


        if (is_dir('tmp/')){
            $files = scandir('tmp/');

            foreach (glob("tmp/sitemap*.xml.gz") as $item){
                $path_parts = pathinfo($item);

                $gq = gzopen($item, 'rb');
                $fp = fopen('xmls/'. $path_parts['filename'], 'w+');
                while ($string = gzread($gq, 4096)) {
                    fwrite($fp, $string, strlen($string));
                }
                gzclose($gq);
                fclose($fp);
            }
        }




        if (is_dir('xmls/')){
            $cars = null;
            foreach (glob("xmls/sitemap*.xml") as $item){
                $xmlCars = XmlParser::load($item);
                $sitemapCars = $xmlCars->parse([
                    'cars' => ['uses' => 'url[loc,lastmod]']
                ]);


//                $cars = array_map(function ($tag){
//                    $urlToArray = explode('/', $tag['loc']);
//                    $number = array_pop($urlToArray);
//
//                    $vehiclesArray[] = [
//                        'url'           => $tag['loc'],
//                        'name'          => $item['name'] ,
//                        'number'        => $number,
//                        'category_id'   => $category_id,
//                        'platform_id'   => 1,
//                        'year'          => $item['year'],
//                        'price'         => $item['price'],
//                        'created_at'    => new \DateTime(),
//                        'generation_id' => $generation_id,
//                        'city_id'       => $city_id = $this->getCityIdFromName($item['shortLocationName']),
//                        'region_id'     => $region_id = $cities->where('id', '=', $city_id)->first()->region_id,
//                        'country_id'    => $regions->where('id', '=', $region_id)->first()->country_id
//                    ];
//
//                    return array(
//                        'url'         => $tag['loc'],
//                        'upped'       => $tag['lastmod'],
//                        'name'        => '' ,
//                        'number'      => $number,
//                        'category_id' => Category::where('url', 'LIKE', implode('/', $urlToArray) . '%')->first() ? Category::where('url', 'LIKE', implode('/', $urlToArray) . '%')->first()->id : 0,
//                        'platform_id' => 1,
//                        'created_at'  => new \DateTime()
//                    );
//
//
//
//                }, $sitemapCars['cars']);
//
//                if ($cars){
//                    Vehicle::insertOrIgnore(array_filter($cars));
//                }


            }
        }
    }

    public function testAsync(){
        $start = microtime(true);
        echo round(microtime(true) - $start);
        echo '<br>';
        $this->iterator = 0;
        $this->counter = 0;
        $vehicles = Vehicle::all();
        $categories = Category::where('parent_id', '!=', 0)->get();
        $regions = Region::all();
        $cities = City::all();
        $client = new Client();

        $requests = function () use ($categories){
             foreach ($categories as $category){
                 //if ($this->iterator >= 100) break;
                 $brandId = Category::whereId($category->parent_id)->first()->mapping_id;
                 $json = $this->doRequest('https://api.av.by/offer-types/cars/filters/main/apply', [
                     'headers' => [
                         'Content-Type'     => 'application/json',
                         'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36 OPR/69.0.3686.95'
                     ],
                     'body' => '{"page":1,"properties":[{"name":"brands","property":5,"value":[[{"name":"brand","value":' . $brandId . '},{"name":"model","value":' . $category->mapping_id . '}]]},{"name":"price_currency","value":2}],"sorting":1}'
                 ],'POST');

                 $pages = (json_decode($json))->pageCount;

                 for($i = 1; $i <= $pages; $i++){
                     yield new Request('POST', 'https://api.av.by/offer-types/cars/filters/main/apply', [
                         'Content-Type'     => 'application/json',
                         'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36 OPR/69.0.3686.95'
                     ], '{"page":' . $i . ',"properties":[{"name":"brands","property":5,"value":[[{"name":"brand","value":' . $brandId . '},{"name":"model","value":' . $category->mapping_id . '}]]},{"name":"price_currency","value":2}],"sorting":1}');
                 }

                 //$this->iterator++;
            }
        };



        $pool = new Pool($client, $requests(),[
            'concurrency' => 5,
            'fulfilled' => function (Response $response, $index) {
                $json = $response->getBody()->getContents();
                $arrayResponse = array();
                foreach ((json_decode($json))->adverts as $item){

                    $this->cars[] = array(
                        'name'                  => $item->metaInfo->h1,
                        'year'                  => $item->metadata->year,
                        'price'                 => $item->price->usd->amount,
                        'url'                   => $item->publicUrl,
                        'shortLocationName'          => $item->shortLocationName,
                        'properties'            =>
                            array(
                                'generation'            => $this->getProperty($item->properties, 'generation') ? $this->getProperty($item->properties, 'generation') : '',
                                'generation_with_years' => $this->getProperty($item->properties, 'generation_with_years') ? $this->getProperty($item->properties, 'generation_with_years') : ''
                            )
                    );
                }

                //Cache::forever(str_replace(' ', '_', (json_decode($json))->seo->breadcrumbs[2]->label) . $this->counter, $arrayResponse );
                $this->cacheKeys[] = str_replace(' ', '_', (json_decode($json))->seo->breadcrumbs[2]->label) . $this->counter;
                $this->counter++;
            },
            'rejected' => function (RequestException $reason, $index) {
            },
        ]);


        $promise = $pool->promise();
        $promise->wait();

        echo round(microtime(true) - $start);
        echo '<br>';


        $categories = Category::all();
        //foreach ($this->cacheKeys as $key){

            //$items = Cache::get($key);

        while (count($this->cars) != 0){
            $vehiclesArray = array();
            for ($i = 0; $i < (count($this->cars) > 1000 ? 1000 : count($this->cars)) ; $i++){
                $item = array_shift($this->cars);
                $url = $item['url'];
                $urlToArray = explode('/', $url);
                $number = array_pop($urlToArray);

                $category_id = $categories->where('url', 'LIKE', implode('/', $urlToArray))->first()->id;

                $str =  str_replace(' · ', '% %', $item['properties']['generation']);
                if ($str !== ""){
                    $generation = Generation::where('name', 'LIKE', '%' . $str . '%')
                                            ->where('category_id', '=', $category_id)->first();
                }
                elseif($strYears = $item['properties']['generation_with_years']){
                    $strYears = preg_replace('/[^0-9 ,]/', '', $strYears);
                    $strYearsArray = explode(' ', $strYears);
                    $generation = Generation::where('year_from', '=', $strYearsArray[0])
                                            ->where('category_id', '=', $category_id)->first();
                }

                if (isset($generation->id)){
                    $generation_id = $generation->id;
                }
                else{
                    $generation_id = null;
                }

                $vehiclesArray[] = [
                    'url'           => $url,
                    'name'          => $item['name'] ,
                    'number'        => $number,
                    'category_id'   => $category_id,
                    'platform_id'   => 1,
                    'year'          => $item['year'],
                    'price'         => $item['price'],
                    'created_at'    => new \DateTime(),
                    'generation_id' => $generation_id,
                    'city_id'       => $city_id = $this->getCityIdFromName($item['shortLocationName']),
                    'region_id'     => $region_id = $cities->where('id', '=', $city_id)->first()->region_id,
                    'country_id'    => $regions->where('id', '=', $region_id)->first()->country_id
                ];
            }
            Vehicle::insert($vehiclesArray);
        }


        echo round(microtime(true) - $start);
        echo '<br>';
        die();
    }








    public function checkNewVehicles(){
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


    public function checkAllVehicles(){
        $categories = Category::where('parent_id', '!=', 0)->get();

        foreach ($categories as $category){
            $brandId = Category::whereId($category->parent_id)->first()->mapping_id;
            $json = $this->doRequest('https://api.av.by/offer-types/cars/filters/main/apply', [
                'headers' => [
                    'Content-Type'     => 'application/json',
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36 OPR/69.0.3686.95'
                ],
                'body' => '{"page":1,"properties":[{"name":"brands","property":5,"value":[[{"name":"brand","value":' . $brandId . '},{"name":"model","value":' . $category->mapping_id . '}]]},{"name":"price_currency","value":2}],"sorting":1}'
            ],'POST');



            $pages = (json_decode($json))->pageCount;

            for($i = 1; $i <= $pages; $i++){
                $json = $this->doRequest('https://api.av.by/offer-types/cars/filters/main/apply', [
                    'headers' => [
                        'Content-Type'     => 'application/json',
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36 OPR/69.0.3686.95'
                    ],
                    'body' => '{"page":' . $i . ',"properties":[{"name":"brands","property":5,"value":[[{"name":"brand","value":' . $brandId . '},{"name":"model","value":' . $category->mapping_id . '}]]},{"name":"price_currency","value":2}],"sorting":1}'
                ],'POST');

                //Log::debug('{"page":' . $i . ',"properties":[{"name":"brands","property":5,"value":[[{"name":"brand","value":' . $brandId . '},{"name":"model","value":' . $category->mapping_id . '}]]},{"name":"price_currency","value":2}],"sorting":1}');

                try {
                    $list = (json_decode($json))->adverts;
                }
                catch (\Exception $exception){
                    $list = array();
                    //Log::alert('ERROR:');
                    //Log::debug('{"page":' . $i . ',"properties":[{"name":"brands","property":5,"value":[[{"name":"brand","value":' . $brandId . '},{"name":"model","value":' . $category->mapping_id . '}]]},{"name":"price_currency","value":2}],"sorting":1}');
                }
                if (count($list) == 0){
                    break;
                }
                $this->links['cars']['items'] = array();


                foreach ($list as $item){

                    $url = $item->publicUrl;
                    $urlToArray = explode('/', $url);
                    $number = array_pop($urlToArray);

                    $str =  str_replace(' · ', '% %', $this->getProperty($item->properties, 'generation'));
                    if ($str !== ""){
                        $generation = Generation::where('name', 'LIKE', '%' . $str . '%')
                                                ->where('category_id', '=', $category->id)->first();
                    }
                    elseif($strYears = AVBY::getProperty($item->properties, 'generation_with_years')){
                        $strYears = preg_replace('/[^0-9 ,]/', '', $strYears);
                        $strYearsArray = explode(' ', $strYears);
                        $generation = Generation::where('year_from', '=', $strYearsArray[0])
                                                ->where('category_id', '=', $category->id)->first();
                    }

                    if (isset($generation->id)){
                        $generation_id = $generation->id;
                    }
                    else{
                        $generation_id = null;
                    }


                    $vehicle = Vehicle::firstOrCreate([
                        'url'         => $url,
                    ], [
                        'name'          => $item->metaInfo->h1 ,
                        'number'        => $number,
                        'category_id'   => $category->id,
                        'platform_id'   => 1,
                        'year'          => $item->metadata->year,
                        'price'         => $item->price->usd->amount ,
                        'created_at'    => new \DateTime(),
                        'generation_id' => $generation_id,
                        'city_id'       => $city_id = $this->getCityIdFromName($item->shortLocationName),
                        'region_id'     => $region_id = City::where('id', '=', $city_id)->first()->region_id,
                        'country_id'    => Region::where('id', '=', $region_id)->first()->country_id
                    ]);
                }

                if ($pages == 0){
                    continue;
                }
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
