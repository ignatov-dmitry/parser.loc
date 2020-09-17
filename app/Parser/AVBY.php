<?php


namespace App\Parser;


use App\Contracts\IParser;
use App\Vehicle;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
class AVBY implements IParser
{

    private $links = array(
        'pages' => array()
    );


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
        $baseUrl = "https://cars.av.by";
        $categoryPageHtml = $this->doRequest($baseUrl, [
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

}
