<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Symfony\Component\DomCrawler\Crawler;

class TestController extends Controller
{
    private $links = array();
    protected $parser;


    public function parsing(){

        $baseUrl = 'https://av.by';
        $categoryPageHtml = $this->doRequest($baseUrl);
        $crawler = $this->runCrawler($categoryPageHtml);

        $crawler->filter('.brandslist li a')->each(function (Crawler $node, $i) {
            $this->links['categories'][] = array(
                'name' => $node->filter('span')->text(),
                'url'  => $node->attr('href'),
                'models' => $this->getSubcategories($node->attr('href'))
            );
            //var_dump($node->attr('href') . '<br>');
        });

//        foreach ($this->links['category'] as $link){
//            $subCategoryPageHtml = $this->doRequest($link);
//        }

        foreach ($this->links as $link){

        }
        dd($this->links);

    }

    public function doRequest($url, $requestParameters = [], $method = 'GET', $clientParameters = [])
    {
        $client = new Client($clientParameters);
        try {
            return $client->request($method, $url, $requestParameters)->getBody()->getContents();
        } catch (GuzzleException $e) {
            return $e;
        }
    }


    public function runCrawler($html)
    {
        return new Crawler($html);
    }


    public function getSubcategories ($url){
        $models = array();
        $subCategoryPageHtml = $this->doRequest($url);
        $subCrawler = $this->runCrawler($subCategoryPageHtml);
        $models = $subCrawler->filter('.brandslist li a')->each(function (Crawler $node, $i) {
            return array(
                'name' => $node->filter('span')->text(),
                'url'  => $node->attr('href')
            );
        });
        return $models;
    }
}
