<?php
namespace App\Contracts;


use Symfony\Component\DomCrawler\Crawler;
interface IParser{
    function doRequest(string $url, array $requestParameters, string $method, array $clientParameters);
    function runCrawler(string $html);
    function loadCategories();
    function getSubcategories(string $url);
    function getCategoryPages(string $url, string $lastLink);
    function checkDuplicate(Crawler $crawler);
    function getCarList(int $category_id, string $url);
}
