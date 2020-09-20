<?php


namespace App\Parser;


use App\Category;
use App\Contracts\IParser;
use App\Facades\TelegramBot;
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


        for($i = 2; $i < 200; $i++){
            $categoryPageHtml[] = $this->doRequest('https://cars.av.by/search/page/' . $i . '?search_time=1', [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36 OPR/69.0.3686.95'
                ]
            ]
            );
        }

        foreach ($categoryPageHtml as $page){
            $crawler = $this->runCrawler($page);
            $list = $crawler->filter('.listing .listing-item .listing-item-title > h4 > a');

            foreach ($list as $item){
                if (Vehicle::latest()->firstWhere('url','=', $item->getAttribute('href')) !== null){
                    continue;
                }

                $urlToArray = explode('/', $item->getAttribute('href'));
                $number = array_pop($urlToArray);

                $this->links['cars']['items'][] = array(
                    'url' => $item->getAttribute('href'),
                    'name' => trim($item->nodeValue) ,
                    'number' => $number,
                    'category_id' => Category::where('url', 'LIKE', implode('/', $urlToArray) . '%')->first() ? Category::where('url', 'LIKE', implode('/', $urlToArray) . '%')->first()->id : 0,
                    'platform_id' => 1,
                    'created_at' => new \DateTime()
                );
            }

        }


        $telegramUsers = TelegramUser::all();

        if ($this->links['cars']['items']){
            Vehicle::insert($this->links['cars']['items']);
                $txt = array_map(function ($item){
                    return $item['url'];
                }, $this->links['cars']['items']);
                foreach ($telegramUsers as $telegramUser) {
                    TelegramBot::sendMessage($telegramUser->chat_id, "Новые автомобили:\n" . implode("\n ", $txt));
                }
        }

        dd(round(microtime(true) - $start, 2));


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

            foreach (glob("tmp/sitemap-cars-cars_public_*.xml.gz") as $item){
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
            foreach (glob("xmls/sitemap-cars-cars_public_*.xml") as $item){
                $xmlCars = XmlParser::load($item);
                $sitemapCars = $xmlCars->parse([
                    'cars' => ['uses' => 'url[loc,lastmod]']
                ]);


//                $cars = array_map(function ($tag){
//                    $urlToArray = explode('/', $tag['loc']);
//                    $number = array_pop($urlToArray);
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
        dd(round(microtime(true) - $start, 2));
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
}
