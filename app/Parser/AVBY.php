<?php


namespace App\Parser;


use App\Category;
use App\City;
use App\Contracts\IParser;
use App\Facades\TelegramBot;
use App\Filter;
use App\FilterVehicleModels;
use App\Region;
use App\TelegramUser;
use App\Vehicle;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Orchestra\Parser\Xml\Facade as XmlParser;
use Illuminate\Http\Request;

class AVBY implements IParser
{

    private $links = array(
        'pages' => array(),
        'category_pages'  => array()
    );
    private $modelCatalogJson = '';

    private $baseUrl = 'https://cars.av.by/';

    private $isDuplicate = false;

    function doRequest(string $url, array $requestParameters = [], string $method = 'GET', array $clientParameters = []){
        $client = new Client($clientParameters);
        try {
            return $client->request($method, $url, $requestParameters)->getBody()->getContents();
        } catch (GuzzleException $e) {
            return $e;
        }
    }


    function runCrawler(string $html){
        return new Crawler($html);
    }


//    public function loadAllCategories(){
//        $this->modelCatalogJson = $this->doRequest(request()->root() . '/model_catalog.json');
//        $modelCatalog = json_decode($this->modelCatalogJson);





        //get categories from site
//        for ($i = 0; $i < count($modelCatalog->brands); $i++){
//            $brands = $this->doRequest('https://av.by/ajax/parameters.php',
//                ['form_params' =>
//                    [
//                        'event' => 'Number_PreSearch',
//                        'brand_id[0]' => $modelCatalog->brands[$i]->id
//                    ]
//                ], 'POST');
//            $modelCatalog->brands[$i]->url = (json_decode($brands))->search_url;
//            for ($j = 0; $j < count($modelCatalog->brands[$i]->models); $j++){
//                $models = $this->doRequest('https://av.by/ajax/parameters.php',
//                    ['form_params' =>
//                        [
//                            'event'       => 'Number_PreSearch',
//                            'brand_id[0]' => $modelCatalog->brands[$i]->id,
//                            'model_id[0]' => $modelCatalog->brands[$i]->models[$j]->id
//                        ]
//                    ], 'POST');
//                $modelCatalog->brands[$i]->models[$j]->url = (json_decode($models))->search_url;
//            }
//        }
//        echo json_encode($modelCatalog);
//        die();




//        foreach ($modelCatalog->brands as $brand){
//            $category = new Category();
//            $category->url = '';
//            $category->name = $brand->name;
//            $category->platform_id = 1;
//            $category->url = $brand->url;
//            $category->save();
//            $id = $category->id;
//
//            foreach ($brand->models as $model){
//                $category = new Category();
//                $category->url = $model->url;
//                $category->name = $model->name;
//                $category->platform_id = 1;
//                $category->parent_id = $id;
//                //dd($model->generations[0]->year_from);
//                $category->release_start = isset($model->generations[0]) ? $model->generations[0]->year_from : null;
//                $category->release_end = count($model->generations) > 1 ? (end($model->generations))->year_to : null;
//
//                $category->save();
//            }
//        }
//    }



    public function importCategory(Request $request){
        if ($cat = Category::where('url', '=', $request->url)->first()){
            $id = $cat->id;
        }
        else{
            $category = new Category($request->all());
            $category->platform_id = 1;
            $category->save();
            $id = $category->id;
        }

        $subCategories = $request->sub_categories;
        foreach ($subCategories as $subCategory){
            if ($subCat = Category::whereUrl($subCategory['url'])->first()){
                continue;
            }
            else{
                $category = new Category($subCategory);
                $category->platform_id = 1;
                $category->parent_id = $id;
                $category->save();
            }

        }
    }





// Получить категории
    function loadCategories(){
        //$baseUrl = "https://cars.av.by";
        $categoryPageHtml = $this->doRequest($this->baseUrl, [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36 OPR/69.0.3686.95'
            ]
        ]);

        $crawler = $this->runCrawler($categoryPageHtml);
        $crawler->filter('.brandslist li a')->each(function (Crawler $node, $i) {

            if ($node->text() !== '...'){
                $this->links['categories'][] = array(
                    'name' => $node->filter('span')->text(),
                    'url'  => $node->attr('href'),
                    'models' => $this->getSubcategories($node->attr('href'))
                );
            }

        });
        return response()->json($this->links);
    }



//Получение подкатегорий
    function getSubcategories(string $url){
        $models = array();
        $subCategoryPageHtml = $this->doRequest($url,[
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36 OPR/69.0.3686.95'
            ]
        ]);
        $subCrawler = $this->runCrawler($subCategoryPageHtml);
        $models = $subCrawler->filter('.brandslist li a')->each(function (Crawler $node, $i) {
            return array(
                'name' => $node->filter('span')->text(),
                'url'  => $node->attr('href')
            );
        });


//        $subCrawler->filter('.brandslist li a')->each(function (Crawler $node, $i) {
//
//            $list = $this->getCategoryPages($node->attr('href'));
//            $this->clearArray($this->links['pages']);
//            foreach ($list as $page){
//                $this->links['test'][] =  array(
//                    'name' => $node->filter('span')->text(),
//                    'url'  => $page,
//                );
//            }
//
//        });


        //$models = $this->links['category_pages'];
        //$this->clearArray($this->links['category_pages']);
        return $models;
    }





// Получение страниц категории
    function getCategoryPages(string $url, string $lastLink = ''){
        $baseUrl = $url;
        $categoryPageHtml = $this->doRequest($baseUrl,[
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36 OPR/69.0.3686.95'
            ]
        ]);
        $crawler = $this->runCrawler($categoryPageHtml);

        $test = $crawler->filter('.pages .pages-numbers a');
        foreach ($test as $item)
        {

            if ($this->checkDuplicate($crawler)){
                break;
            }

            $this->links['pages'][] = $item->getAttribute('href');
        }


        if (end($this->links['pages']) != $lastLink && !$this->isDuplicate){
            $this->getCategoryPages(end($this->links['pages']), end($this->links['pages']));
        }
        $result =  array_values(array_unique($this->links['pages'])) ? array_values(array_unique($this->links['pages'])) : array($url);
        return $result;
    }


    function checkDuplicate(Crawler $crawler){
        $carsList = $crawler->filter('.listing .listing-item .listing-item-title > h4 > a');
        foreach ($carsList as $item) {
            if (Vehicle::latest()->firstWhere('url','=',$item->getAttribute('href')) !== null){
                $this->isDuplicate = true;
                break;
            }
        }

        return $this->isDuplicate;
    }







//Получение объявлений машин
    function getCarList(int $category_id, string $url){
        $this->links['cars']['items'] = array();

        //$pages = $this->getCategoryPages($url);


        //foreach ($pages as $page){
            $categoryPageHtml = $this->doRequest($url, [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36 OPR/69.0.3686.95'
                ]
            ]);
            $crawler = $this->runCrawler($categoryPageHtml);

            $list = $crawler->filter('.listing .listing-item .listing-item-title > h4 > a');

            foreach ($list as $item){
                if (Vehicle::latest()->firstWhere('url','=', $item->getAttribute('href')) !== null){
                    break;
                }
                $number =  explode('/', $item->getAttribute('href'));
                $this->links['cars']['items'][] = array(
                    'url' => $item->getAttribute('href'),
                    'name' => trim($item->nodeValue) ,
                    'number' => end($number),
                    'category_id' => $category_id,
                    'platform_id' => 1,
                    'created_at' => new \DateTime()
                );
            }
        //}


        return $this->links['cars'];
    }



    public function loadSitemap(){
        $start = microtime(true);
//        $categoryPageHtml[] = $this->doRequest('https://cars.av.by/search/?search_time=1&sort=date&order=desc', [
//                'headers' => [
//                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36 OPR/69.0.3686.95'
//                ]
//            ]
//        );

        for($i = 1; $i < 200; $i++){
            if ($i === 1){
                $substr = '';
            }
            else{
                $substr = 'page/' . $i;
            }


            $categoryPageHtml= $this->doRequest('https://cars.av.by/search/' . $substr . '?search_time=1&sort=date&order=desc', [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36 OPR/69.0.3686.95'
                ]
            ]);


            if ($this->parse($categoryPageHtml)){
                break;
            }
        }





//        $telegramUsers = TelegramUser::all();
//
//        if ($this->links['cars']['items']){
//            Vehicle::insert($this->links['cars']['items']);
//                $txt = array_map(function ($item){
//                    return $item['url'];
//                }, $this->links['cars']['items']);
//                foreach ($telegramUsers as $telegramUser) {
//                    TelegramBot::sendMessage($telegramUser->chat_id, "Новые автомобили:\n" . implode("\n ", $txt));
//                }
//        }

        dd(round(microtime(true) - $start, 2));
    }

    public function parse($page){
        $crawler = $this->runCrawler($page);
        $list = $crawler->filter('.listing .listing-item');
        $this->links['cars']['items'] = array();
        $dublicate = false;

        foreach ($list as $item){
            $url = (new Crawler($item))->filter('.listing-item-title > h4 > a');
            //dd((new Crawler($item))->filter('.listing-item-price .listing-item-location')->text());
            $urlToArray = explode('/', $url->attr('href'));
            $number = array_pop($urlToArray);


            if (Vehicle::latest()->firstWhere('url','=', $url->attr('href')) !== null){
                $dublicate = true;
                break;
            }


            $this->links['cars']['items'][] = array(
                'url'         => $url->attr('href'),
                'name'        => trim($url->text()) ,
                'number'      => $number,
                'category_id' => Category::where('url', 'LIKE', implode('/', $urlToArray) . '%')->first()->id,
                'platform_id' => 1,
                'year'        => trim((new Crawler($item))->filter('.listing-item-price > span')->text()),
                'price'       => (int)str_replace(' ', '', trim((new Crawler($item))->filter('.listing-item-price > small')->text())) ,
                'created_at'  => new \DateTime(),
                'city_id'     => $city_id = $this->getCityIdFromName(trim((new Crawler($item))->filter('.listing-item-price .listing-item-location')->text())),
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
                $filters = Filter::where('chat_id' , '=', $telegramUser->chat_id)->get(); //TODO 518575553 replace to $telegramUser->chat_id


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

                //dd($cars);
                //dd($filteredCars);

                if ($userCars){
                    $txt = array_map(function ($item){
                        return $item['url'];
                    }, $userCars);

                    TelegramBot::sendMessage($telegramUser->chat_id, "Новые автомобили:\n" . implode("\n ", $txt));
                }

            }

        }


        return $dublicate;
    }


    public function utfToWin($str){
        $symbols = array(
            "Р°"=>"а",
            "Р±"=>"б",
            "РІ"=>"в",
            "Рі"=>"г",
            "Рґ"=>"д",
            "Рµ"=>"е",
            "С‘"=>"ё",
            "Р¶"=>"ж",
            "Р·"=>"з",
            "Рё"=>"и",
            "Р№"=>"й",
            "Рє"=>"к",
            "Р»"=>"л",
            "Рј"=>"м",
            "РЅ"=>"н",
            "Рѕ"=>"о",
            "Рї"=>"п",
            "СЂ"=>"р",
            "СЃ"=>"с",
            "С‚"=>"т",
            "Сѓ"=>"у",
            "С„"=>"ф",
            "С…"=>"х",
            "С†"=>"ц",
            "С‡"=>"ч",
            "С?"=>"ш",
            "С‰"=>"щ",
            "СЉ"=>"ъ",
            "С‹"=>"ы",
            "СЊ"=>"ь",
            "СЌ"=>"э",
            "СЋ"=>"ю",
            "СЏ"=>"я",
            "Рђ"=>"А",
            "Р‘"=>"Б",
            "Р’"=>"В",
            "Р“"=>"Г",
            "Р”"=>"Д",
            "Р•"=>"Е",
            "РЃ"=>"Ё",
            "Р–"=>"Ж",
            "Р—"=>"З",
            "Р˜"=>"И",
            "Р™"=>"Й",
            "Рљ"=>"К",
            "Р›"=>"Л",
            "Рњ"=>"М",
            "Рќ"=>"Н",
            "Рћ"=>"О",
            "Рџ"=>"П",
            "Р "=>"Р",
            "РЎ"=>"С",
            "Рў"=>"Т",
            "РЈ"=>"У",
            "Р¤"=>"Ф",
            "РҐ"=>"Х",
            "Р¦"=>"Ц",
            "Р§"=>"Ч",
            "РЁ"=>"Ш",
            "Р©"=>"Щ",
            "РЄ"=>"Ъ",
            "Р«"=>"Ы",
            "Р¬"=>"Ь",
            "Р­"=>"Э",
            "Р®"=>"Ю",
            "РЇ"=>"Я"
        );

        $str = strtr($str, $symbols);
        return $str;
    }

    private function clearArray(array $array){
        $array = array();
    }


    public function getCityIdFromName(string $name){
        return City::whereName($name)->first()->id;
    }

}
