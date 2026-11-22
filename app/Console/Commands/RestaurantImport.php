<?php

namespace App\Console\Commands;

use Igniter\Cart\Models\Category;
use Igniter\Cart\Models\Menu;
use Igniter\Local\Models\Location;
use Igniter\System\Models\Settings;
use Illuminate\Console\Command;

/**
 * Loads the restaurant from content/menu.json (name, categories, dishes, photos).
 * Run it again after editing menu.json: the menu is rebuilt from the file.
 */
class RestaurantImport extends Command
{
    protected $signature = 'restaurant:import {file=content/menu.json}';

    protected $description = 'Load the restaurant name, menu and photos from content/menu.json';

    public function handle(): int
    {
        $file = base_path($this->argument('file'));
        $data = json_decode((string) @file_get_contents($file), true);
        if (!is_array($data)) {
            $this->error("Cannot read $file (is the JSON valid?)");
            return self::FAILURE;
        }
        $dir = dirname($file);
        $r = $data['restaurant'] ?? [];

        Settings::set(['site_name' => $r['name'] ?? 'My Restaurant', 'sender_name' => $r['name'] ?? 'My Restaurant']);

        $location = Location::query()->first();
        if ($location) {
            $location->location_name = $r['name'] ?? $location->location_name;
            if (!empty($r['description'])) $location->description = $r['description'];
            $location->save();
        }

        // Rebuild the menu from the file
        Menu::query()->get()->each(fn ($m) => $m->delete());
        Category::query()->get()->each(fn ($c) => $c->delete());

        $count = 0;
        foreach ($data['categories'] ?? [] as $ci => $cat) {
            $category = Category::create([
                'name' => $cat['name'],
                'description' => $cat['description'] ?? null,
                'priority' => $ci,
                'status' => 1,
            ]);
            if ($location) $category->locations()->sync([$location->getKey()]);

            foreach ($cat['items'] ?? [] as $mi => $item) {
                $menu = Menu::create([
                    'menu_name' => $item['name'],
                    'menu_description' => $item['description'] ?? '',
                    'menu_price' => (float) ($item['price'] ?? 0),
                    'minimum_qty' => 1,
                    'menu_status' => 1,
                    'menu_priority' => $mi,
                ]);
                $menu->categories()->sync([$category->getKey()]);
                if ($location) $menu->locations()->sync([$location->getKey()]);

                $img = isset($item['image']) ? $dir.'/'.$item['image'] : null;
                if ($img && is_file($img)) {
                    $media = $menu->newMediaInstance();
                    $media->addFromFile($img, 'thumb');
                    if (!empty($item['alt'])) $media->setCustomProperty('alt', $item['alt']);
                    $media->save();
                }
                $count++;
            }
        }

        $this->info("Loaded {$count} dishes for ".($r['name'] ?? 'the restaurant').'.');
        return self::SUCCESS;
    }
}
