<?php

use GuzzleHttp\Client;
use Illuminate\Database\Seeder;
use App\Category;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $client = new Client();
        $modelCatalogJson =  $client->request('GET', request()->root() . '/model_catalog.json')->getBody()->getContents();
        $modelCatalog = json_decode($modelCatalogJson);



        $regions = array(
            1 => json_decode('[{"id":115,"name":"\u0411\u0430\u0440\u0430\u043d\u043e\u0432\u0438\u0447\u0438"},{"id":795,"name":"\u0411\u0435\u043b\u043e\u043e\u0437\u0451\u0440\u0441\u043a"},{"id":116,"name":"\u0411\u0435\u0440\u0435\u0437\u0430"},{"id":7,"name":"\u0411\u0440\u0435\u0441\u0442"},{"id":796,"name":"\u0412\u044b\u0441\u043e\u043a\u043e\u0435"},{"id":117,"name":"\u0413\u0430\u043d\u0446\u0435\u0432\u0438\u0447\u0438"},{"id":789,"name":"\u0414\u0430\u0432\u0438\u0434-\u0413\u043e\u0440\u043e\u0434\u043e\u043a"},{"id":118,"name":"\u0414\u0440\u043e\u0433\u0438\u0447\u0438\u043d"},{"id":119,"name":"\u0416\u0430\u0431\u0438\u043d\u043a\u0430"},{"id":120,"name":"\u0418\u0432\u0430\u043d\u043e\u0432\u043e"},{"id":121,"name":"\u0418\u0432\u0430\u0446\u0435\u0432\u0438\u0447\u0438"},{"id":122,"name":"\u041a\u0430\u043c\u0435\u043d\u0435\u0446"},{"id":123,"name":"\u041a\u043e\u0431\u0440\u0438\u043d"},{"id":797,"name":"\u041a\u043e\u0441\u0441\u043e\u0432\u043e"},{"id":124,"name":"\u041b\u0443\u043d\u0438\u043d\u0435\u0446"},{"id":125,"name":"\u041b\u044f\u0445\u043e\u0432\u0438\u0447\u0438"},{"id":126,"name":"\u041c\u0430\u043b\u043e\u0440\u0438\u0442\u0430"},{"id":788,"name":"\u041c\u0438\u043a\u0430\u0448\u0435\u0432\u0438\u0447\u0438"},{"id":127,"name":"\u041f\u0438\u043d\u0441\u043a"},{"id":128,"name":"\u041f\u0440\u0443\u0436\u0430\u043d\u044b"},{"id":129,"name":"\u0421\u0442\u043e\u043b\u0438\u043d"}]'),
            2 => json_decode('[{"id":791,"name":"\u0411\u0430\u0440\u0430\u043d\u044c"},{"id":130,"name":"\u0411\u0435\u0448\u0435\u043d\u043a\u043e\u0432\u0438\u0447\u0438"},{"id":131,"name":"\u0411\u0440\u0430\u0441\u043b\u0430\u0432"},{"id":132,"name":"\u0412\u0435\u0440\u0445\u043d\u0435\u0434\u0432\u0438\u043d\u0441\u043a"},{"id":6,"name":"\u0412\u0438\u0442\u0435\u0431\u0441\u043a"},{"id":133,"name":"\u0413\u043b\u0443\u0431\u043e\u043a\u043e\u0435"},{"id":134,"name":"\u0413\u043e\u0440\u043e\u0434\u043e\u043a"},{"id":792,"name":"\u0414\u0438\u0441\u043d\u0430"},{"id":135,"name":"\u0414\u043e\u043a\u0448\u0438\u0446\u044b"},{"id":136,"name":"\u0414\u0443\u0431\u0440\u043e\u0432\u043d\u043e"},{"id":137,"name":"\u041b\u0435\u043f\u0435\u043b\u044c"},{"id":138,"name":"\u041b\u0438\u043e\u0437\u043d\u043e"},{"id":139,"name":"\u041c\u0438\u043e\u0440\u044b"},{"id":790,"name":"\u041d\u043e\u0432\u043e\u043b\u0443\u043a\u043e\u043c\u043b\u044c"},{"id":140,"name":"\u041d\u043e\u0432\u043e\u043f\u043e\u043b\u043e\u0446\u043a"},{"id":141,"name":"\u041e\u0440\u0448\u0430"},{"id":142,"name":"\u041f\u043e\u043b\u043e\u0446\u043a"},{"id":143,"name":"\u041f\u043e\u0441\u0442\u0430\u0432\u044b"},{"id":144,"name":"\u0420\u043e\u0441\u0441\u043e\u043d\u044b"},{"id":145,"name":"\u0421\u0435\u043d\u043d\u043e"},{"id":146,"name":"\u0422\u043e\u043b\u043e\u0447\u0438\u043d"},{"id":147,"name":"\u0423\u0448\u0430\u0447\u0438"},{"id":148,"name":"\u0427\u0430\u0448\u043d\u0438\u043a\u0438"},{"id":149,"name":"\u0428\u0430\u0440\u043a\u043e\u0432\u0449\u0438\u043d\u0430"},{"id":150,"name":"\u0428\u0443\u043c\u0438\u043b\u0438\u043d\u043e"}]'),
            3 => json_decode('[{"id":151,"name":"\u0411\u0440\u0430\u0433\u0438\u043d"},{"id":152,"name":"\u0411\u0443\u0434\u0430-\u041a\u043e\u0448\u0435\u043b\u0435\u0432\u043e"},{"id":793,"name":"\u0412\u0430\u0441\u0438\u043b\u0435\u0432\u0438\u0447\u0438"},{"id":153,"name":"\u0412\u0435\u0442\u043a\u0430"},{"id":8,"name":"\u0413\u043e\u043c\u0435\u043b\u044c"},{"id":154,"name":"\u0414\u043e\u0431\u0440\u0443\u0448"},{"id":155,"name":"\u0415\u043b\u044c\u0441\u043a"},{"id":156,"name":"\u0416\u0438\u0442\u043a\u043e\u0432\u0438\u0447\u0438"},{"id":157,"name":"\u0416\u043b\u043e\u0431\u0438\u043d"},{"id":158,"name":"\u041a\u0430\u043b\u0438\u043d\u043a\u043e\u0432\u0438\u0447\u0438"},{"id":160,"name":"\u041a\u043e\u0440\u043c\u0430"},{"id":161,"name":"\u041b\u0435\u043b\u044c\u0447\u0438\u0446\u044b"},{"id":162,"name":"\u041b\u043e\u0435\u0432"},{"id":163,"name":"\u041c\u043e\u0437\u044b\u0440\u044c"},{"id":164,"name":"\u041d\u0430\u0440\u043e\u0432\u043b\u044f"},{"id":165,"name":"\u041e\u043a\u0442\u044f\u0431\u0440\u044c\u0441\u043a\u0438\u0439"},{"id":166,"name":"\u041f\u0435\u0442\u0440\u0438\u043a\u043e\u0432"},{"id":167,"name":"\u0420\u0435\u0447\u0438\u0446\u0430"},{"id":168,"name":"\u0420\u043e\u0433\u0430\u0447\u0435\u0432"},{"id":169,"name":"\u0421\u0432\u0435\u0442\u043b\u043e\u0433\u043e\u0440\u0441\u043a"},{"id":802,"name":"\u0421\u043e\u0441\u043d\u043e\u0432\u044b\u0439 \u0411\u043e\u0440"},{"id":794,"name":"\u0422\u0443\u0440\u043e\u0432"},{"id":171,"name":"\u0425\u043e\u0439\u043d\u0438\u043a\u0438"},{"id":170,"name":"\u0427\u0435\u0447\u0435\u0440\u0441\u043a"}]'),
            4 => json_decode('[{"id":799,"name":"\u0411\u0435\u0440\u0451\u0437\u043e\u0432\u043a\u0430"},{"id":172,"name":"\u0411\u043e\u043b\u044c\u0448\u0430\u044f \u0411\u0435\u0440\u0435\u0441\u0442\u043e\u0432\u0438\u0446\u0430"},{"id":173,"name":"\u0412\u043e\u043b\u043a\u043e\u0432\u044b\u0441\u043a"},{"id":174,"name":"\u0412\u043e\u0440\u043e\u043d\u043e\u0432\u043e"},{"id":2,"name":"\u0413\u0440\u043e\u0434\u043d\u043e"},{"id":175,"name":"\u0414\u044f\u0442\u043b\u043e\u0432\u043e"},{"id":176,"name":"\u0417\u0435\u043b\u044c\u0432\u0430"},{"id":177,"name":"\u0418\u0432\u044c\u0435"},{"id":178,"name":"\u041a\u043e\u0440\u0435\u043b\u0438\u0447\u0438"},{"id":179,"name":"\u041b\u0438\u0434\u0430"},{"id":180,"name":"\u041c\u043e\u0441\u0442\u044b"},{"id":181,"name":"\u041d\u043e\u0432\u043e\u0433\u0440\u0443\u0434\u043e\u043a"},{"id":182,"name":"\u041e\u0441\u0442\u0440\u043e\u0432\u0435\u0446"},{"id":183,"name":"\u041e\u0448\u043c\u044f\u043d\u044b"},{"id":805,"name":"\u0420\u043e\u0441\u0441\u044c"},{"id":184,"name":"\u0421\u0432\u0438\u0441\u043b\u043e\u0447\u044c"},{"id":798,"name":"\u0421\u043a\u0438\u0434\u0435\u043b\u044c"},{"id":185,"name":"\u0421\u043b\u043e\u043d\u0438\u043c"},{"id":186,"name":"\u0421\u043c\u043e\u0440\u0433\u043e\u043d\u044c"},{"id":187,"name":"\u0429\u0443\u0447\u0438\u043d"}]'),
            5 => json_decode('[{"id":91,"name":"\u0411\u0435\u0440\u0435\u0437\u0438\u043d\u043e"},{"id":92,"name":"\u0411\u043e\u0440\u0438\u0441\u043e\u0432"},{"id":93,"name":"\u0412\u0438\u043b\u0435\u0439\u043a\u0430"},{"id":94,"name":"\u0412\u043e\u043b\u043e\u0436\u0438\u043d"},{"id":95,"name":"\u0414\u0437\u0435\u0440\u0436\u0438\u043d\u0441\u043a"},{"id":96,"name":"\u0416\u043e\u0434\u0438\u043d\u043e"},{"id":97,"name":"\u0417\u0430\u0441\u043b\u0430\u0432\u043b\u044c"},{"id":98,"name":"\u041a\u043b\u0435\u0446\u043a"},{"id":99,"name":"\u041a\u043e\u043f\u044b\u043b\u044c"},{"id":100,"name":"\u041a\u0440\u0443\u043f\u043a\u0438"},{"id":101,"name":"\u041b\u043e\u0433\u043e\u0439\u0441\u043a"},{"id":102,"name":"\u041b\u044e\u0431\u0430\u043d\u044c"},{"id":103,"name":"\u041c\u0430\u0440\u044c\u0438\u043d\u0430 \u0433\u043e\u0440\u043a\u0430"},{"id":1,"name":"\u041c\u0438\u043d\u0441\u043a"},{"id":803,"name":"\u041c\u0438\u0445\u0430\u043d\u043e\u0432\u0438\u0447\u0438"},{"id":104,"name":"\u041c\u043e\u043b\u043e\u0434\u0435\u0447\u043d\u043e"},{"id":105,"name":"\u041c\u044f\u0434\u0435\u043b\u044c"},{"id":106,"name":"\u041d\u0435\u0441\u0432\u0438\u0436"},{"id":804,"name":"\u041f\u0440\u0438\u043b\u0443\u043a\u0438"},{"id":107,"name":"\u041f\u0443\u0445\u043e\u0432\u0438\u0447\u0438"},{"id":787,"name":"\u0420\u0430\u043a\u043e\u0432"},{"id":247,"name":"\u0420\u0443\u0434\u0435\u043d\u0441\u043a"},{"id":108,"name":"\u0421\u043b\u0443\u0446\u043a"},{"id":109,"name":"\u0421\u043c\u043e\u043b\u0435\u0432\u0438\u0447\u0438"},{"id":110,"name":"\u0421\u043e\u043b\u0438\u0433\u043e\u0440\u0441\u043a"},{"id":111,"name":"\u0421\u0442\u0430\u0440\u044b\u0435 \u0434\u043e\u0440\u043e\u0433\u0438"},{"id":112,"name":"\u0421\u0442\u043e\u043b\u0431\u0446\u044b"},{"id":113,"name":"\u0423\u0437\u0434\u0430"},{"id":159,"name":"\u0424\u0430\u043d\u0438\u043f\u043e\u043b\u044c"},{"id":114,"name":"\u0427\u0435\u0440\u0432\u0435\u043d\u044c"}]'),
            6 => json_decode('[{"id":188,"name":"\u0411\u0435\u043b\u044b\u043d\u0438\u0447\u0438"},{"id":189,"name":"\u0411\u043e\u0431\u0440\u0443\u0439\u0441\u043a"},{"id":190,"name":"\u0411\u044b\u0445\u043e\u0432"},{"id":191,"name":"\u0413\u043b\u0443\u0441\u043a"},{"id":192,"name":"\u0413\u043e\u0440\u043a\u0438"},{"id":193,"name":"\u0414\u0440\u0438\u0431\u0438\u043d"},{"id":194,"name":"\u041a\u0438\u0440\u043e\u0432\u0441\u043a"},{"id":195,"name":"\u041a\u043b\u0438\u043c\u043e\u0432\u0438\u0447\u0438"},{"id":196,"name":"\u041a\u043b\u0438\u0447\u0435\u0432"},{"id":197,"name":"\u041a\u043e\u0441\u0442\u044e\u043a\u043e\u0432\u0438\u0447\u0438"},{"id":198,"name":"\u041a\u0440\u0430\u0441\u043d\u043e\u043f\u043e\u043b\u044c\u0435"},{"id":199,"name":"\u041a\u0440\u0438\u0447\u0435\u0432"},{"id":200,"name":"\u041a\u0440\u0443\u0433\u043b\u043e\u0435"},{"id":5,"name":"\u041c\u043e\u0433\u0438\u043b\u0435\u0432"},{"id":201,"name":"\u041c\u0441\u0442\u0438\u0441\u043b\u0430\u0432\u043b\u044c"},{"id":202,"name":"\u041e\u0441\u0438\u043f\u043e\u0432\u0438\u0447\u0438"},{"id":203,"name":"\u0421\u043b\u0430\u0432\u0433\u043e\u0440\u043e\u0434"},{"id":204,"name":"\u0425\u043e\u0442\u0438\u043c\u0441\u043a"},{"id":205,"name":"\u0427\u0430\u0443\u0441\u044b"},{"id":206,"name":"\u0427\u0435\u0440\u0438\u043a\u043e\u0432"},{"id":207,"name":"\u0428\u043a\u043b\u043e\u0432"}]'),
        );



        // $this->call(UserSeeder::class);
        DB::table('platforms')->insert([
            'name' => 'av.by',
            'url'  => 'https://av.by'
        ]);
        DB::table('platforms')->insert([
            'name' => 'kufar',
            'url'  => 'https://auto.kufar.by'
        ]);
        DB::table('countries')->insert([
            'name' => 'Беларусь',
        ]);


        DB::table('regions')->insert([
            [
                'country_id' => 1,
                'name'       => 'Брестская обл.',
            ],

            [
                'country_id' => 1,
                'name'       => 'Витебская обл.',
            ],

            [
                'country_id' => 1,
                'name'       => 'Гомельская обл.',
            ],

            [
                'country_id' => 1,
                'name'       => 'Гродненская обл.',
            ],

            [
                'country_id' => 1,
                'name'       => 'Минская обл.',
            ],

            [
                'country_id' => 1,
                'name'       => 'Могилевская обл.',
            ],
        ]);



        foreach ($regions as $regionId => $regionCities){
            foreach ($regionCities as $city){
                DB::table('cities')->insert([
                    'region_id' => $regionId,
                    'name'      => $city->name
                ]);
            }
        }



        foreach ($modelCatalog->brands as $brand){
            $category = new Category();
            $category->url = '';
            $category->name = $brand->name;
            $category->platform_id = 1;
            $category->url = $brand->url;
            $category->save();
            $id = $category->id;

            foreach ($brand->models as $model){
                $category = new Category();
                $category->url = $model->url;
                $category->name = $model->name;
                $category->platform_id = 1;
                $category->parent_id = $id;
                //dd($model->generations[0]->year_from);
                $category->release_start = isset($model->generations[0]) ? $model->generations[0]->year_from : null;
                $category->release_end = count($model->generations) > 1 ? (end($model->generations))->year_to : null;

                $category->save();
            }
        }
    }
}
