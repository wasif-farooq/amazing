<?php

use Illuminate\Database\Seeder;

/**
 * Class IconTableSeeder
 */
class IconTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $version = $this->getVersion();
        $variationTypes = $this->getVariationTypes();
        $categories = $this->getCategories();
        $data = $this->getData();

        foreach ($data as $row) {
            $icon = $this->get($row->slug, $variationTypes['icon']->id);
            $icon = $icon ?? new App\Icon();

            $icon->name = $row->name;
            $icon->slug = $row->slug;
            $icon->classes = $row->classes;
            $icon->version()->associate($version);
            $icon->variation()->associate($variationTypes['icon']);

            $icon->save();


            // handling categories
            $this->handleCategories($row, $icon, $categories);


            foreach ($row->variations as $children) {
                $variation = $this->get($icon->slug, $variationTypes[$children->type]->id);
                $variation = $variation ?? new App\Icon();
                $variation->name = $row->name;
                $variation->slug = $row->slug;
                $variation->classes = $row->classes;
                $variation->version()->associate($version);
                $variation->variation()->associate($variationTypes[$children->type]);
                $variation->parent()->associate($icon);
                $variation->price = $children->price;
                $variation->paid = (bool) $children->paid;

                $variation->save();

                // handling categories
                $this->handleCategories($row, $variation, $categories);
            }
        }
    }

    /**
     * @return array|mixed|object
     */
    private function getData()
    {
        $this->command->info("Getting json file.");

        // getting josn file data
        $data = File::get(database_path('data/amazing-neo.json'));

        $this->command->info("decoding json data.");
        // decoding the json

        return json_decode($data, false);
    }

    /**
     * @param string $slug
     * @param int $variation
     * @return \App\Icon|null
     */
    private function get(string $slug, int $variation): ?App\Icon
    {
        return App\Icon::where(array(
            'slug' => $slug,
            'variation_id' => $variation
        ))->get()->first();
    }

    /**
     * @return \App\Version
     */
    private function getVersion(): App\Version
    {
        return App\Version::orderBy('created_at', 'desc')->first();
    }

    /**
     * @return array
     */
    private function getVariationTypes(): array
    {
        $variationTypes = [];
        $results = App\VariationType::all();
        foreach ($results as $r) {
            $variationTypes[$r->slug] = $r;
        }
        return $variationTypes;
    }

    /**
     * @return array
     */
    private function getCategories(): array
    {
        $categories = [];
        $results = App\Category::all();
        foreach ($results as $r) {
            $categories[$r->slug] = $r;
        }
        return $categories;
    }

    /**
     * @param StdClass $row
     * @param \App\Icon $icon
     * @param array $categories
     */
    private function handleCategories(\StdClass $row, App\Icon $icon, array $categories): void
    {
        $ids = [];
        foreach ($row->categories as $category) {
            $ids[] = $categories[$category]->id;
        }
        $icon->categories()->sync($ids);
    }
}