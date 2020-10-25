<?php

use Illuminate\Database\Seeder;
use GuzzleHttp\Client;
use App\Category;
use App\Facades\AVBY;
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


        $modelCatalogNewJson =  $client->request('GET', request()->root() . '/model_catalog_new.json')->getBody()->getContents();
        $modelCatalogNew = json_decode($modelCatalogNewJson);
        $brandsNew = $modelCatalogNew->brands;


        foreach ($brands as $brand){
            $categoryBrand = Category::where('url', '=', $brand->url)->first();
            $categoryBrand->mapping_id = $brand->id;

            foreach ($brand->models as $model){
                $categoryModel = Category::where('url', '=', $model->url)->first();
                $categoryModel->mapping_id = $model->id;
                $categoryModel->save();
            }

            $categoryBrand->save();
        }


        foreach ($brandsNew as $brand){
            $category = Category::where('mapping_id', '=', $brand->id)->firstOrCreate([
                'mapping_id' => $brand->id
            ],[
                'name' => $brand->name,
                'platform_id' => 1,
                'url' => 'undefined',
                'parent_id' => 0
            ]);
            foreach ($brand->models as $model){
                $categoryModel = Category::where('mapping_id', '=', $model->id)->firstOrCreate([
                    'mapping_id' => $model->id

                ],[
                    'name' => $model->name,
                    'platform_id' => 1,
                    'url' => AVBY::getLinkCategory($model->id),
                    'parent_id' => $category->id
                ]);

                foreach ($model->generations as $generation){
                    DB::table('generations')->insert([
                        'mapping_id'  => $generation->id,
                        'category_id' => $categoryModel->id,
                        'name'        => $generation->name,
                        'year_from'   => $generation->year_from,
                        'year_to'     => $generation->year_to
                    ]);
                }

                $categoryModel->save();
            }

            $categoryBrand->save();
        }
    }
}
