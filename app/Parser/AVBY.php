<?php


namespace App\Parser;


use App\Category;
use App\Contracts\IParser;
use App\Vehicle;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Cache;
class AVBY implements IParser
{

    private $links = array(
        'pages' => array()
    );

    private $vehiclesCache = null;

    public function __construct()
    {
        //$this->vehiclesCache = Cache::put('vehicles', Vehicle::all());
        //Cache::put('vehicles', Vehicle::all());
        //Cache::flush();
//        $value = Cache::rememberForever('vehicles', function () {
//            return DB::table('vehicles')->get();
//        });
        //$this->vehiclesCache = Cache::get('vehicles');

        //dd($this->vehiclesCache);
    }

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



// Получить категории
    function loadCategories(){
        $baseUrl = "https://av.by";
        $categoryPageHtml = $this->doRequest($baseUrl, [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36 OPR/69.0.3686.95'
            ]
        ]);

        $crawler = $this->runCrawler(iconv("windows-1251", "utf-8", $categoryPageHtml));

        $crawler->filter('.brandslist li a')->each(function (Crawler $node, $i) {

            $this->links['categories'][] = array(
                'name' => $node->filter('span')->html(),
                'url'  => $node->attr('href'),
                'models' => $this->getSubcategories($node->attr('href'))
            );


        });
        //dd($this->links['categories']);
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
        //$baseUrl = $url;
        $this->links['cars']['items'] = array();

        $pages = $this->getCategoryPages($url);
        foreach ($pages as $page){
            $categoryPageHtml = $this->doRequest($page, [
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
        }


        return $this->links['cars'];
    }


}
