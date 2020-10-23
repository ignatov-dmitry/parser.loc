<?php

use Illuminate\Database\Seeder;
use GuzzleHttp\Client;
use App\Category;
class UpdateMigrationForCategoriesAndGenerationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $client = new Client();
        $modelCatalogJson =  $client->request('GET', request()->root() . '/model_catalog.json')->getBody()->getContents();
        $modelCatalog = json_decode($modelCatalogJson);
        $brands = $modelCatalog->brands;

        foreach ($brands as $brand){
            $categoryBrand = Category::where('url', '=', $brand->url)->first();
            $categoryBrand->mapping_id = $brand->id;

            foreach ($brand->models as $model){
                $categoryModle = Category::where('url', '=', $model->url)->first();
                $categoryModle->mapping_id = $model->id;

                foreach ($model->generations as $generation){
                    DB::table('generations')->insert([
                        'mapping_id'  => $generation->id,
                        'category_id' => $categoryModle->id,
                        'name'        => $generation->name,
                        'year_from'   => $generation->year_from,
                        'year_to'     => $generation->year_to
                    ]);
                }

                $categoryModle->save();
            }

            $categoryBrand->save();
        }



    }
}
