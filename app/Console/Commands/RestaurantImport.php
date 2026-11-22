<?php

namespace App\Console\Commands;

use Igniter\Cart\Models\Category;
use Igniter\Cart\Models\Menu;
use Igniter\Cart\Models\MenuOption;
use Igniter\Coupons\Models\Coupon;
use Igniter\Frontend\Models\Slider;
use Igniter\Local\Models\Location;
use Igniter\Main\Classes\PageBuilder;
use Igniter\Main\Classes\ThemeManager;
use Igniter\Main\Models\Theme;
use Igniter\Main\Models\ThemePage;
use Igniter\System\Models\Country;
use Igniter\System\Models\Currency;
use Igniter\System\Models\Settings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Loads the restaurant from content/menu.json (name, contact, categories, dishes, photos).
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
        $name = $r['name'] ?? 'My Restaurant';

        Settings::set([
            'site_name' => $name,
            'sender_name' => $name,
            'site_email' => $r['email'] ?? 'hello@example.test',
            'sender_email' => $r['email'] ?? 'hello@example.test',
        ]);

        if (!empty($r['currency']) && ($currency = Currency::query()->where('currency_code', $r['currency'])->first())) {
            $currency->currency_status = 1;
            $currency->save();
            $currency->makeDefault();
        }
        $country = null;
        if (!empty($r['address']['country'])) {
            $country = Country::query()->where('iso_code_2', $r['address']['country'])->first();
            $country?->makeDefault();
        }

        $location = Location::query()->first();
        if ($location) {
            $a = $r['address'] ?? [];
            $location->location_name = $name;
            $location->description = $r['description'] ?? $location->description;
            $location->location_email = $r['email'] ?? $location->location_email;
            $location->location_telephone = $r['telephone'] ?? $location->location_telephone;
            $location->location_address_1 = $a['street'] ?? $location->location_address_1;
            $location->location_city = $a['city'] ?? $location->location_city;
            $location->location_postcode = $a['postcode'] ?? $location->location_postcode;
            if ($country) $location->location_country_id = $country->getKey();
            $location->saveQuietly();
        }

        // Photo on the restaurant card: the hero photo
        $hero = isset($r['hero_image']) ? $dir.'/'.$r['hero_image'] : null;
        if ($location && $hero && is_file($hero)) {
            $location->getMedia('thumb')->each->delete();
            $location->newMediaInstance()->addFromFile($hero, 'thumb');
        }

        // The restaurant's own theme (themes/lotus-lane) with its main colour
        Theme::syncAll();
        if ($theme = Theme::query()->where('code', 'lotus-lane')->first()) {
            Theme::activateTheme('lotus-lane');
            // Theme settings: the Orange defaults, then this restaurant's name as logo and its main colour
            $themeData = [];
            foreach ($theme->getFieldsConfig() as $key => $field) {
                if (array_key_exists('default', $field)) $themeData[$key] = $field['default'];
            }
            $themeData = array_merge($themeData, (array) $theme->data, ['logo_image' => null, 'logo_text' => $name]);
            // Demo banner on every customer page while menu.json says "demo": true (themes/lotus-lane/includes/header.blade.php)
            $themeData['demo'] = !empty($r['demo']);
            if (!empty($r['primary_color'])) {
                // The Orange theme's CSS is pre-built with orange (#ff4900) baked in, so we add
                // a small CSS override that repaints every orange shade in the main colour.
                $themeData['color-primary'] = $r['primary_color'];
                $themeData['custom_css'] = $this->brandCss($r['primary_color']);
            }
            $theme->data = $themeData;
            $theme->save();
            Artisan::call('cache:clear');
            // Menu page: show each dish's photo
            $themeObj = resolve(ThemeManager::class)->findTheme('lotus-lane');
            $sections = resolve(PageBuilder::class)->defaultSections($themeObj, 'local.menus');
            foreach ($sections as &$section) {
                foreach ($section['columns'] as &$column) {
                    foreach ($column['blocks'] as &$block) {
                        if (str_ends_with($block['code'], 'menu-item-list')) {
                            $block['settings'] = array_merge($block['settings'], [
                                'showThumb' => true, 'menuThumbWidth' => 110, 'menuThumbHeight' => 90,
                            ]);
                        }
                    }
                }
            }
            unset($section, $column, $block);
            ThemePage::query()->updateOrCreate(
                ['theme_code' => 'lotus-lane', 'page_code' => 'local.menus'],
                ['sections' => $sections],
            );
        }

        // Home page slider: the restaurant's hero photo instead of the demo slide
        if ($hero && is_file($hero)) {
            $slider = Slider::query()->firstOrCreate(['code' => 'home-slider'], ['name' => 'Homepage slider']);
            $slider->getMedia('images')->each->delete();
            $slider->newMediaInstance()->addFromFile($hero, 'images');
        }

        // Remove the TastyIgniter demo extras that belong to the old menu
        MenuOption::query()->get()->each(fn ($o) => $o->delete());
        Coupon::query()->get()->each(fn ($c) => $c->delete());

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
                    if (!empty($item['alt'])) {
                        $media->setCustomProperty('alt', $item['alt']);
                        $media->save();
                    }
                } elseif ($img) {
                    $this->warn("Photo not found for {$item['name']}: {$item['image']}");
                }
                $count++;
            }
        }

        $this->info("Loaded {$count} dishes for {$name}.");
        return self::SUCCESS;
    }

    /** CSS rules of the Orange theme that use an orange shade, repainted in $hex (same light/dark steps). */
    protected function brandCss(string $hex): string
    {
        $css = (string) @file_get_contents(public_path('vendor/igniter-orange/css/app.css'));
        if ($css === '' || !preg_match('/^#?([0-9a-f]{6})$/i', $hex, $m)) return '';
        [$th, $ts, $tl] = $this->hsl(array_map('hexdec', str_split($m[1], 2)));
        [, , $bl] = $this->hsl([255, 73, 0]);
        $repaint = function (array $rgb) use ($th, $ts, $tl, $bl): ?array {
            [$h, $s, $l] = $this->hsl($rgb);
            if ($s < 0.6 || $h < 10 || $h > 25) return null; // not an orange shade
            return $this->rgb($th, $ts, max(0, min(1, $tl + ($l - $bl))));
        };
        $out = '';
        preg_match_all('/([^{}]+)\{([^{}]*)\}/', $css, $rules, PREG_SET_ORDER);
        foreach ($rules as [, $selector, $body]) {
            $keep = [];
            foreach (explode(';', $body) as $decl) {
                $changed = false;
                $decl = preg_replace_callback('/#([0-9a-f]{6})\b/i', function ($c) use ($repaint, &$changed) {
                    $new = $repaint(array_map('hexdec', str_split($c[1], 2)));
                    if (!$new) return $c[0];
                    $changed = true;
                    return vsprintf('#%02x%02x%02x', $new);
                }, $decl);
                $decl = preg_replace_callback('/\b(\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})\b/', function ($c) use ($repaint, &$changed) {
                    $new = $repaint([(int) $c[1], (int) $c[2], (int) $c[3]]);
                    if (!$new) return $c[0];
                    $changed = true;
                    return implode(',', $new);
                }, $decl);
                if ($changed) $keep[] = trim($decl).' !important';
            }
            // the theme prints this CSS HTML-escaped: avoid > and quotes in selectors
            $selector = preg_replace('/^@charset[^;]*;/', '', trim($selector));
            $selector = preg_replace('/=(["\'])([\w-]+)\1/', '=$2', str_replace('>', ' ', $selector));
            if ($keep && !preg_match('/["\'<&]/', $selector)) $out .= trim($selector).'{'.implode(';', $keep)."}\n";
        }
        return $out;
    }

    protected function hsl(array $rgb): array
    {
        [$r, $g, $b] = array_map(fn ($v) => $v / 255, $rgb);
        $max = max($r, $g, $b); $min = min($r, $g, $b); $l = ($max + $min) / 2; $d = $max - $min;
        if ($d == 0) return [0, 0, $l];
        $s = $d / (1 - abs(2 * $l - 1));
        $h = match ($max) { $r => fmod(($g - $b) / $d, 6), $g => ($b - $r) / $d + 2, default => ($r - $g) / $d + 4 } * 60;
        return [$h < 0 ? $h + 360 : $h, $s, $l];
    }

    protected function rgb(float $h, float $s, float $l): array
    {
        $c = (1 - abs(2 * $l - 1)) * $s; $x = $c * (1 - abs(fmod($h / 60, 2) - 1)); $m = $l - $c / 2;
        [$r, $g, $b] = match (intdiv((int) $h, 60) % 6) { 0 => [$c, $x, 0], 1 => [$x, $c, 0], 2 => [0, $c, $x], 3 => [0, $x, $c], 4 => [$x, 0, $c], default => [$c, 0, $x] };
        return array_map(fn ($v) => (int) round(($v + $m) * 255), [$r, $g, $b]);
    }
}
