<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarketplaceCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'Electronics' => [
                'Mobiles' => ['Apple', 'Samsung', 'vivo', 'OPPO', 'realme', 'Xiaomi', 'Motorola', 'OnePlus', 'Nothing', 'iQOO', 'next'],
                'Smart Wearable Tech' => ['Smart Watches', 'Smart Bands', 'Smart Glasses'],
                'Health Care Appliances' => ['Bp Monitors', 'Weighing Scale', 'Massagers'],
                'Mobile Accessories' => ['Cases', 'Chargers', 'Power Banks', 'Screen Guards', 'Cables', 'Holders'],
                'Laptops' => ['Gaming Laptops', 'Thin & Light', '2-in-1 Laptops'],
                'Desktop PCs' => ['All-in-Ones', 'Mini PCs', 'Monitors'],
                'Computer Accessories' => ['Keyboards', 'Mouse', 'Pendrives', 'Hard Disks'],
                'Computer Peripherals' => ['Printers', 'Scanners', 'Webcams'],
                'Tablets' => ['Apple iPads', 'Android Tablets', 'Kids Tablets'],
                'Televisions' => ['Smart TVs', '4K Ultra HD', 'OLED TVs'],
                'Speakers' => ['Bluetooth Speakers', 'Soundbars', 'Home Theatre'],
                'Smart Home Automation' => ['Smart Lights', 'Smart Cameras', 'Smart Plugs'],
                'Camera' => ['DSLR & Mirrorless', 'Action Cameras', 'Instant Cameras'],
                'Camera Accessories' => ['Lenses', 'Tripods', 'Memory Cards'],
                'Network Components' => ['Routers', 'Range Extenders', 'Mesh WiFi'],
            ],
            'TVs & Appliances' => [
                'Television' => ['Smart & Ultra HD', '32 inch & below', '43–55 inch', '65 & above'],
                'Top Brands' => ['Samsung', 'LG', 'Sony', 'Mi', 'TCL'],
                'Washing Machine' => ['Front Load', 'Top Load', 'Semi Automatic'],
                'Air Conditioners' => ['Inverter AC', 'Split ACs', 'Window ACs'],
                'Refrigerators' => ['Single Door', 'Double Door', 'Side by Side', 'French Door'],
                'Kitchen Appliances' => ['Microwave', 'Chimney', 'Mixer Grinder', 'OTG'],
                'Small Appliances' => ['Irons', 'Fans', 'Geysers', 'Water Purifiers'],
                'Buying Guides' => ['TV Buying Guide', 'AC Buying Guide'],
            ],
            'Men' => [
                'Footwear' => ['Sports Shoes', 'Casual Shoes', 'Formal Shoes', 'Sandals', 'Sneakers'],
                'Men\'s Grooming' => ['Deodorants', 'Perfumes', 'Beard Care', 'Shavers'],
                'Top wear' => ['T-Shirts', 'Casual Shirts', 'Formal Shirts', 'Jackets'],
                'Bottom wear' => ['Jeans', 'Trousers', 'Shorts', 'Cargos'],
                'Ethnic wear' => ['Kurtas', 'Sherwanis', 'Ethnic Sets'],
                'Innerwear & Loungewear' => ['Briefs', 'Vests', 'Thermals'],
                'Accessories' => ['Watches', 'Wallets', 'Belts', 'Backpacks', 'Sunglasses'],
                'Featured Brands' => ['Nike', 'Adidas', 'Puma', 'Levi\'s'],
            ],
            'Women' => [
                'Western Wear' => ['Tops', 'Dresses', 'Jeans', 'Skirts', 'Jumpsuits'],
                'Lingerie & Sleepwear' => ['Bras', 'Panties', 'Nightwear'],
                'Ethnic Wear' => ['Sarees', 'Kurtis', 'Lehenga', 'Salwar Suits'],
                'Ethnic Bottoms' => ['Leggings', 'Palazzos', 'Shararas'],
                'Footwear' => ['Flats', 'Heels', 'Wedges', 'Sports Shoes'],
                'Watches' => ['Analog', 'Smart Watches'],
                'Beauty & Jewellery' => ['Make Up', 'Skin Care', 'Silver Jewellery', 'Handbags'],
                'Featured' => ['Biba', 'W', 'Lakme', 'Titan Raga'],
            ],
            'Baby & Kids' => [
                'Boys\' Clothing' => ['T-Shirts', 'Ethnic Wear', 'Shorts', 'Innerwear'],
                'Girls\' Clothing' => ['Dresses', 'Tops', 'Ethnic Wear'],
                'Kids\' Footwear' => ['Sandals', 'Sport Shoes', 'School Shoes'],
                'Winter Wear' => ['Sweatshirts', 'Jackets', 'Thermals'],
                'Toys' => ['Educational Toys', 'Soft Toys', 'Remote Control', 'Board Games'],
                'School Supplies' => ['School Bags', 'Lunch Boxes', 'Stationery'],
                'Baby Care' => ['Diapers', 'Wipes', 'Baby Food', 'Bath Care', 'Strollers'],
                'Featured Brands' => ['Barbie', 'Lego', 'Puma', 'Crocs'],
            ],
            'Home & Furniture' => [
                'Kitchen & Cookware' => ['Pans', 'Pressure Cookers', 'Gas Stoves', 'Dinner Sets'],
                'Kitchen Storage' => ['Bottles', 'Lunch Boxes', 'Containers'],
                'Bedroom Furniture' => ['Beds', 'Mattresses', 'Wardrobes'],
                'Living Room' => ['Sofas', 'TV Units', 'Coffee Tables'],
                'Furnishing' => ['Bedsheets', 'Curtains', 'Cushions', 'Towels'],
                'Home Decor' => ['Paintings', 'Clocks', 'Lamps', 'Showpieces'],
                'Smart Home' => ['Smart Lights', 'Door Locks', 'Cameras'],
                'Featured' => ['HomeTown', 'Prestige', 'Milton', 'Spaces'],
            ],
            'Sports, Books & More' => [
                'Sports' => ['Cricket', 'Badminton', 'Football', 'Cycling', 'Swimming'],
                'Exercise Fitness' => ['Dumbbells', 'Yoga Mat', 'Home Gyms', 'Cardio'],
                'Books' => ['Fiction', 'Self-Help', 'Academics', 'Children'],
                'Stationery' => ['Pens', 'Diaries', 'Organizers'],
                'Food Essentials' => ['Nuts', 'Tea & Coffee', 'Snacks', 'Breakfast'],
                'Health & Nutrition' => ['Protein', 'Vitamins', 'Health Drinks'],
                'Gaming & Music' => ['Consoles', 'Accessories', 'Instruments'],
                'Auto Accessories' => ['Helmets', 'Car Care', 'Audio'],
            ],
            'Farm & Garden' => [
                'Seeds & Saplings' => ['Vegetable Seeds', 'Flower Seeds', 'Herb Seeds', 'Fruit Saplings'],
                'Soil & Fertilizers' => ['Potting Mix', 'Organic Manure', 'Compost'],
                'Gardening Tools' => ['Hand Tools', 'Pruners', 'Watering Cans', 'Gloves'],
                'Irrigation' => ['Drip Kits', 'Sprinklers', 'Timers'],
                'Plants & Planters' => ['Indoor Plants', 'Succulents', 'Ceramic Pots', 'Grow Bags'],
                'Lawn Care' => ['Grass Seed', 'Weed Control'],
                'Pest & Plant Care' => ['Neem Oil', 'Organic Pesticides', 'Mulch'],
                'Featured' => ['Urban Farming Kits', 'Balcony Garden', 'Ugaoo'],
            ],
            'Food Items' => [
                'Fresh Produce' => ['Fruits', 'Vegetables', 'Leafy Greens', 'Organic Fresh'],
                'Dairy & Eggs' => ['Milk', 'Curd', 'Cheese', 'Butter', 'Eggs'],
                'Pantry Staples' => ['Rice & Grains', 'Atta', 'Dals', 'Oils', 'Spices'],
                'Breakfast & Spreads' => ['Cereals', 'Oats', 'Honey', 'Bread'],
                'Beverages' => ['Tea', 'Coffee', 'Juices', 'Health Drinks'],
                'Snacks & Sweets' => ['Namkeen', 'Biscuits', 'Chocolates', 'Dry Fruits'],
                'Cooking Essentials' => ['Sauces', 'Pickles', 'Ready-to-Cook', 'Pasta'],
                'Featured Stores' => ['Organic Pantry', 'Bulk Buy', 'Amul', 'Aashirvaad'],
            ],
        ];

        DB::transaction(function () use ($catalog) {
            $categoryIds = [];
            foreach ($catalog as $categoryName => $groups) {
                $category = DB::table('categories')->updateOrInsert(
                    ['slug' => Str::slug($categoryName)],
                    ['parent_id' => null, 'name' => $categoryName, 'depth' => 0, 'sort_order' => count($categoryIds), 'is_active' => true, 'updated_at' => now(), 'created_at' => now()]
                );
                $parent = DB::table('categories')->where('slug', Str::slug($categoryName))->first();
                $categoryIds[$categoryName] = $parent->id;
                foreach ($groups as $groupName => $items) {
                    DB::table('categories')->updateOrInsert(
                        ['slug' => Str::slug($categoryName.'-'.$groupName)],
                        ['parent_id' => $parent->id, 'name' => $groupName, 'depth' => 1, 'sort_order' => 0, 'is_active' => true, 'updated_at' => now(), 'created_at' => now()]
                    );
                    $group = DB::table('categories')->where('slug', Str::slug($categoryName.'-'.$groupName))->first();
                    foreach ($items as $index => $itemName) {
                        DB::table('categories')->updateOrInsert(
                            ['slug' => Str::slug($categoryName.'-'.$groupName.'-'.$itemName)],
                            ['parent_id' => $group->id, 'name' => $itemName, 'depth' => 2, 'sort_order' => $index, 'is_active' => true, 'updated_at' => now(), 'created_at' => now()]
                        );
                    }
                }
            }

            // Maintain materialized category paths (root/child/leaf).
            $allCategories = DB::table('categories')->orderBy('depth')->get();
            foreach ($allCategories as $row) {
                if ($row->parent_id) {
                    $parentPath = DB::table('categories')->where('id', $row->parent_id)->value('path');
                    $path = ($parentPath ?: '').$row->id.'/';
                } else {
                    $path = $row->id.'/';
                }
                DB::table('categories')->where('id', $row->id)->update(['path' => $path]);
            }

            // Global brand records gathered from the navbar's featured/top-brand sections.
            $brands = ['Aashirvaad', 'Adidas', 'Amul', 'Barbie', 'Biba', 'Crocs', 'HomeTown', 'LG', 'Lakme', 'Lego', 'Levi\'s', 'Mi', 'Milton', 'Nike', 'Prestige', 'Puma', 'Samsung', 'Sony', 'Spaces', 'TCL', 'Titan Raga', 'Ugaoo', 'W' ];
            foreach ($brands as $brandName) {
                DB::table('brands')->updateOrInsert(
                    ['slug' => Str::slug($brandName)],
                    ['name' => $brandName, 'is_active' => true, 'updated_at' => now(), 'created_at' => now()]
                );
            }

            // Base units and common commerce units.
            $unitRows = [
                ['Piece', 'pc', 'count', 1, false],
                ['Pair', 'pair', 'count', 1, false],
                ['Set', 'set', 'count', 1, false],
                ['Pack', 'pack', 'pack', 1, false],
                ['Kilogram', 'kg', 'weight', 1, true],
                ['Gram', 'g', 'weight', 0.001, true],
                ['Litre', 'L', 'volume', 1, true],
                ['Millilitre', 'ml', 'volume', 0.001, true],
                ['Metre', 'm', 'length', 1, true],
                ['Centimetre', 'cm', 'length', 0.01, true],
            ];
            foreach ($unitRows as [$name, $short, $type, $factor, $decimal]) {
                DB::table('units')->updateOrInsert(
                    ['name' => $name],
                    ['short_name' => $short, 'type' => $type, 'conversion_factor' => $factor, 'allow_decimal' => $decimal, 'updated_at' => now(), 'created_at' => now()]
                );
            }

            $attributeGroups = [
                ['General', 'general'],
                ['Dimensions', 'dimensions'],
                ['Product Details', 'product-details'],
                ['Compliance', 'compliance'],
            ];
            foreach ($attributeGroups as [$name, $slug]) {
                DB::table('attribute_groups')->updateOrInsert(['slug' => $slug], ['name' => $name, 'sort_order' => 0, 'updated_at' => now(), 'created_at' => now()]);
            }
            $groupIds = DB::table('attribute_groups')->pluck('id', 'slug');

            $attributes = [
                ['Color', 'color', 'select', 'general', 1, 1, 1, 1],
                ['Size', 'size', 'select', 'general', 1, 1, 1, 1],
                ['Material', 'material', 'select', 'product-details', 1, 1, 1, 0],
                ['Brand Model', 'brand-model', 'text', 'general', 0, 1, 1, 0],
                ['Warranty', 'warranty', 'select', 'product-details', 1, 1, 1, 0],
                ['Capacity', 'capacity', 'text', 'dimensions', 1, 1, 1, 0],
                ['Weight', 'weight', 'number', 'dimensions', 1, 1, 1, 0],
                ['Length', 'length', 'number', 'dimensions', 0, 1, 1, 0],
                ['Width', 'width', 'number', 'dimensions', 0, 1, 1, 0],
                ['Height', 'height', 'number', 'dimensions', 0, 1, 1, 0],
                ['Expiry Date', 'expiry-date', 'date', 'compliance', 0, 1, 0, 0],
                ['Country of Origin', 'country-of-origin', 'text', 'compliance', 0, 1, 1, 0],
            ];
            $attributeIds = [];
            foreach ($attributes as [$name, $slug, $inputType, $groupSlug, $filterable, $searchable, $comparable, $variantAxis]) {
                DB::table('attributes')->updateOrInsert(
                    ['slug' => $slug],
                    ['attribute_group_id' => $groupIds[$groupSlug], 'name' => $name, 'input_type' => $inputType, 'is_filterable' => (bool) $filterable, 'is_searchable' => (bool) $searchable, 'is_comparable' => (bool) $comparable, 'is_variant_axis' => (bool) $variantAxis, 'is_visible' => true, 'sort_order' => count($attributeIds), 'updated_at' => now(), 'created_at' => now()]
                );
                $attribute = DB::table('attributes')->where('slug', $slug)->first();
                $attributeIds[$slug] = $attribute->id;
            }
         foreach ([
    'Black', 'White', 'Blue', 'Red', 'Green',
    'Pink', 'Grey', 'Yellow', 'Brown', 'Multicolor'
]  as $option) {
                DB::table('attribute_options')->updateOrInsert(
                    ['attribute_id' => $attributeIds['color'], 'value' => Str::slug($option)],
                    ['label' => $option, 'sort_order' => 0, 'updated_at' => now(), 'created_at' => now()]
                );
            }
            foreach (['XS', 'S', 'M', 'L', 'XL', 'XXL', 'Free Size'] as $option) {
                DB::table('attribute_options')->updateOrInsert(
                    ['attribute_id' => $attributeIds['size'], 'value' => Str::slug($option)],
                    ['label' => $option, 'sort_order' => 0, 'updated_at' => now(), 'created_at' => now()]
                );
            }
            foreach (['Cotton', 'Polyester', 'Leather', 'Wood', 'Metal', 'Plastic', 'Glass', 'Stainless Steel', 'Silicone', 'Other'] as $option) {
                DB::table('attribute_options')->updateOrInsert(
                    ['attribute_id' => $attributeIds['material'], 'value' => Str::slug($option)],
                    ['label' => $option, 'sort_order' => 0, 'updated_at' => now(), 'created_at' => now()]
                );
            }
            foreach (['No Warranty', '6 Months', '1 Year', '2 Years', '3 Years', '5 Years'] as $option) {
                DB::table('attribute_options')->updateOrInsert(
                    ['attribute_id' => $attributeIds['warranty'], 'value' => Str::slug($option)],
                    ['label' => $option, 'sort_order' => 0, 'updated_at' => now(), 'created_at' => now()]
                );
            }

            // Attach a useful baseline set of attributes to every leaf category.
            $leafIds = DB::table('categories')->where('depth', 2)->pluck('id');
            $commonAttributeSlugs = ['color', 'size', 'material', 'brand-model', 'warranty', 'capacity', 'weight', 'length', 'width', 'height', 'country-of-origin'];
            foreach ($leafIds as $leafId) {
                foreach ($commonAttributeSlugs as $sort => $slug) {
                    DB::table('category_attributes')->updateOrInsert(
                        ['category_id' => $leafId, 'attribute_id' => $attributeIds[$slug]],
                        ['is_required' => false, 'is_filterable' => null, 'is_variant_axis' => null, 'applies_to' => 'both', 'sort_order' => $sort, 'updated_at' => now(), 'created_at' => now()]
                    );
                }
            }
        });
    }
}
