<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductPriceTier;
use App\Models\ProductImage;
use App\Models\Brand;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Str;

class TradingProductsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $adminId = $admin ? $admin->id : 1;

        $pieceUnit = Unit::where('name', 'like', '%piece%')->first()?->id ?? 1;
        $kgUnit = Unit::where('name', 'like', '%kilogram%')->first()?->id ?? 2;
        $packUnit = Unit::where('name', 'like', '%pack%')->first()?->id ?? 6;
        $setUnit = Unit::where('name', 'like', '%set%')->first()?->id ?? 8;

        /* =========================================================================
         * 1. CREATE SUBCATEGORIES FOR TRADING CATEGORIES
         * ========================================================================= */
        $tradingTree = [
            'food-grains' => [
                'Rice & Paddy' => [
                    'slug' => 'rice-paddy',
                    'icon' => 'fa-wheat-awn',
                    'items' => [
                        ['name' => 'Basmati Rice', 'slug' => 'basmati-rice'],
                        ['name' => 'Sona Masoori', 'slug' => 'sona-masoori'],
                        ['name' => 'Parboiled Rice', 'slug' => 'parboiled-rice'],
                        ['name' => 'Jeerakasala Rice', 'slug' => 'jeerakasala-rice'],
                    ],
                ],
                'Wheat & Flour' => [
                    'slug' => 'wheat-flour',
                    'icon' => 'fa-bowl-rice',
                    'items' => [
                        ['name' => 'Sharbati Wheat', 'slug' => 'sharbati-wheat'],
                        ['name' => 'Chakki Fresh Atta', 'slug' => 'chakki-fresh-atta'],
                        ['name' => 'Maida & Semolina', 'slug' => 'maida-semolina'],
                    ],
                ],
                'Pulses & Dal' => [
                    'slug' => 'pulses-dal',
                    'icon' => 'fa-seedling',
                    'items' => [
                        ['name' => 'Toor Dal Premium', 'slug' => 'toor-dal-premium'],
                        ['name' => 'Moong Dal Yellow', 'slug' => 'moong-dal-yellow'],
                        ['name' => 'Chana Dal Bold', 'slug' => 'chana-dal-bold'],
                        ['name' => 'Urad Dal Whole', 'slug' => 'urad-dal-whole'],
                    ],
                ],
            ],
            'agro-products' => [
                'Bulk Vegetables' => [
                    'slug' => 'bulk-vegetables',
                    'icon' => 'fa-carrot',
                    'items' => [
                        ['name' => 'Potatoes Grade A', 'slug' => 'potatoes-grade-a'],
                        ['name' => 'Red Onions Nasik', 'slug' => 'red-onions-nasik'],
                        ['name' => 'Hybrid Tomatoes', 'slug' => 'hybrid-tomatoes'],
                        ['name' => 'Green Chillies Bulk', 'slug' => 'green-chillies-bulk'],
                    ],
                ],
                'Plantation Crops' => [
                    'slug' => 'plantation-crops',
                    'icon' => 'fa-tree',
                    'items' => [
                        ['name' => 'Fresh Coconuts', 'slug' => 'fresh-coconuts'],
                        ['name' => 'Natural Rubber Sheets', 'slug' => 'natural-rubber-sheets'],
                        ['name' => 'Raw Cashew Nuts', 'slug' => 'raw-cashew-nuts'],
                        ['name' => 'Areca Nut Supreme', 'slug' => 'areca-nut-supreme'],
                    ],
                ],
                'Organic Fertilizers' => [
                    'slug' => 'organic-fertilizers',
                    'icon' => 'fa-flask',
                    'items' => [
                        ['name' => 'Vermicompost 50kg', 'slug' => 'vermicompost-50kg'],
                        ['name' => 'Neem Cake Fertilizer', 'slug' => 'neem-cake-fertilizer'],
                        ['name' => 'Bio Humic Granules', 'slug' => 'bio-humic-granules'],
                    ],
                ],
            ],
            'electronics-wholesale' => [
                'Mobile Accessories' => [
                    'slug' => 'mobile-accessories-bulk',
                    'icon' => 'fa-headphones',
                    'items' => [
                        ['name' => 'Fast Charging Cables', 'slug' => 'fast-charging-cables'],
                        ['name' => '20W PD Wall Adapters', 'slug' => '20w-pd-wall-adapters'],
                        ['name' => 'Tempered Glass Bulk', 'slug' => 'tempered-glass-bulk'],
                        ['name' => 'Magnetic Car Mounts', 'slug' => 'magnetic-car-mounts'],
                    ],
                ],
                'Commercial Displays' => [
                    'slug' => 'commercial-displays',
                    'icon' => 'fa-tv',
                    'items' => [
                        ['name' => 'Commercial LED Signage', 'slug' => 'commercial-led-signage'],
                        ['name' => 'Hospitality Smart TVs', 'slug' => 'hospitality-smart-tvs'],
                        ['name' => 'Interactive Flat Panels', 'slug' => 'interactive-flat-panels'],
                    ],
                ],
                'Audio & Peripherals' => [
                    'slug' => 'audio-peripherals-wholesale',
                    'icon' => 'fa-volume-high',
                    'items' => [
                        ['name' => 'Wireless Neckbands Bulk', 'slug' => 'wireless-neckbands-bulk'],
                        ['name' => 'USB Office Keyboards', 'slug' => 'usb-office-keyboards'],
                        ['name' => 'Optical Mice Cartons', 'slug' => 'optical-mice-cartons'],
                    ],
                ],
            ],
            'spices-oils' => [
                'Whole Spices Bulk' => [
                    'slug' => 'whole-spices-bulk',
                    'icon' => 'fa-pepper-hot',
                    'items' => [
                        ['name' => 'Malabar Black Pepper', 'slug' => 'malabar-black-pepper'],
                        ['name' => 'Green Cardamom 8mm', 'slug' => 'green-cardamom-8mm'],
                        ['name' => 'Cloves Handpicked', 'slug' => 'cloves-handpicked'],
                        ['name' => 'Cinnamon Quills', 'slug' => 'cinnamon-quills'],
                    ],
                ],
                'Ground Spices' => [
                    'slug' => 'ground-spices-wholesale',
                    'icon' => 'fa-mortar-pestle',
                    'items' => [
                        ['name' => 'Turmeric Powder 5% Curcumin', 'slug' => 'turmeric-powder-curcumin'],
                        ['name' => 'Kashmiri Chilli Powder', 'slug' => 'kashmiri-chilli-powder'],
                        ['name' => 'Coriander Powder Bold', 'slug' => 'coriander-powder-bold'],
                    ],
                ],
                'Commercial Oils' => [
                    'slug' => 'commercial-oils',
                    'icon' => 'fa-bottle-droplet',
                    'items' => [
                        ['name' => 'Cold Pressed Coconut Oil 15L', 'slug' => 'cold-pressed-coconut-oil-15l'],
                        ['name' => 'Mustard Oil Tin 15L', 'slug' => 'mustard-oil-tin-15l'],
                        ['name' => 'Refined Sunflower Oil 15L', 'slug' => 'refined-sunflower-oil-15l'],
                    ],
                ],
            ],
            'packaging-supplies' => [
                'Corrugated Boxes' => [
                    'slug' => 'corrugated-boxes',
                    'icon' => 'fa-box',
                    'items' => [
                        ['name' => '3-Ply Shipping Boxes', 'slug' => '3-ply-shipping-boxes'],
                        ['name' => '5-Ply Heavy Master Cartons', 'slug' => '5-ply-heavy-master-cartons'],
                        ['name' => 'Die-Cut Pizza Boxes', 'slug' => 'die-cut-pizza-boxes'],
                    ],
                ],
                'Sacks & Bags' => [
                    'slug' => 'sacks-and-bags',
                    'icon' => 'fa-bag-shopping',
                    'items' => [
                        ['name' => 'HDPE Woven Sacks 50kg', 'slug' => 'hdpe-woven-sacks-50kg'],
                        ['name' => 'Jute Gunny Bags 50kg', 'slug' => 'jute-gunny-bags-50kg'],
                        ['name' => 'Non-Woven D-Cut Bags', 'slug' => 'non-woven-d-cut-bags'],
                    ],
                ],
                'Protective & Tape' => [
                    'slug' => 'protective-and-tape',
                    'icon' => 'fa-tape',
                    'items' => [
                        ['name' => 'Bubble Wrap Rolls 100m', 'slug' => 'bubble-wrap-rolls-100m'],
                        ['name' => 'BOPP Transparent Tape Ctn', 'slug' => 'bopp-transparent-tape-ctn'],
                        ['name' => 'Stretch Film Rolls', 'slug' => 'stretch-film-rolls'],
                    ],
                ],
            ],
            'home-furniture-trading' => [
                'Office & Commercial' => [
                    'slug' => 'office-commercial-furniture',
                    'icon' => 'fa-chair',
                    'items' => [
                        ['name' => 'Ergonomic Mesh Chairs Lot', 'slug' => 'ergonomic-mesh-chairs-lot'],
                        ['name' => 'Modular Workstations 4-Seater', 'slug' => 'modular-workstations-4-seater'],
                        ['name' => 'Steel Filing Cabinets', 'slug' => 'steel-filing-cabinets'],
                    ],
                ],
                'Hospitality & Dining' => [
                    'slug' => 'hospitality-dining-furniture',
                    'icon' => 'fa-utensils',
                    'items' => [
                        ['name' => 'Banquet Stackable Chairs', 'slug' => 'banquet-stackable-chairs'],
                        ['name' => 'Restaurant Dining Sets Bulk', 'slug' => 'restaurant-dining-sets-bulk'],
                        ['name' => 'Bar Stools Commercial', 'slug' => 'bar-stools-commercial'],
                    ],
                ],
            ],
        ];

        $subcatMap = []; // slug => Category model

        foreach ($tradingTree as $parentSlug => $groups) {
            $parent = Category::where('slug', $parentSlug)->first();
            if (!$parent) {
                continue;
            }

            $gOrder = 1;
            foreach ($groups as $groupName => $groupInfo) {
                $group = Category::firstOrNew(['slug' => $groupInfo['slug']]);
                $group->name = $groupName;
                $group->parent_id = $parent->id;
                $group->icon = $groupInfo['icon'];
                $group->sort_order = $gOrder++;
                $group->is_active = true;
                $group->is_trading = true;
                $group->save();
                $subcatMap[$groupInfo['slug']] = $group;

                $iOrder = 1;
                foreach ($groupInfo['items'] as $itemInfo) {
                    $item = Category::firstOrNew(['slug' => $itemInfo['slug']]);
                    $item->name = $itemInfo['name'];
                    $item->parent_id = $group->id;
                    $item->sort_order = $iOrder++;
                    $item->is_active = true;
                    $item->is_trading = true;
                    $item->save();
                    $subcatMap[$itemInfo['slug']] = $item;
                }
            }
        }

        /* =========================================================================
         * 2. SEED REALISTIC WHOLESALE DEMO PRODUCTS
         * ========================================================================= */
        $wholesaleProducts = [
            // 1. Food Grains - Basmati Rice
            [
                'title' => 'Premium 1121 Steam Basmati Rice (50kg Bag)',
                'slug' => 'premium-1121-steam-basmati-rice-50kg',
                'short_description' => 'Extra long grain, aged 2 years, ideal for hotels, caterers and exporters.',
                'description' => 'Premium Quality 1121 Steam Basmati Rice packaged in 50kg moisture-resistant HDPE bags. Aged naturally for 24 months for optimum elongation, non-sticky texture, and exquisite aroma. Perfect for commercial kitchens, biryani houses, catering services, and bulk food retail.',
                'category_slug' => 'basmati-rice',
                'unit_id' => $packUnit,
                'min_order_qty' => 10,
                'max_order_qty' => 1000,
                'pack_qty' => 50,
                'mrp' => 4500,
                'base_price' => 3850,
                'tiers' => [
                    ['min' => 10, 'max' => 49, 'price' => 3850],
                    ['min' => 50, 'max' => 99, 'price' => 3600],
                    ['min' => 100, 'max' => null, 'price' => 3400],
                ],
                'offer_text' => 'Save up to ₹450/bag on 100+ bags',
                'image' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=600&q=80',
            ],
            // 2. Food Grains - Sharbati Wheat
            [
                'title' => 'MP Sharbati Premium Whole Wheat Grain (50kg Bag)',
                'slug' => 'mp-sharbati-premium-whole-wheat-grain-50kg',
                'short_description' => 'Golden polished Sharbati wheat, high protein content for mills and bakeries.',
                'description' => 'Direct from Sehore farms, Madhya Pradesh. Golden polished Sharbati whole wheat grains with high protein, superior dough elasticity and sweet natural taste. Double-cleaned and destoned, ready for commercial milling and large flour distribution networks.',
                'category_slug' => 'sharbati-wheat',
                'unit_id' => $packUnit,
                'min_order_qty' => 20,
                'max_order_qty' => 2000,
                'pack_qty' => 50,
                'mrp' => 2200,
                'base_price' => 1850,
                'tiers' => [
                    ['min' => 20, 'max' => 49, 'price' => 1850],
                    ['min' => 50, 'max' => 99, 'price' => 1720],
                    ['min' => 100, 'max' => null, 'price' => 1600],
                ],
                'offer_text' => 'Bulk Mill Rate: ₹1,600/bag on 100+',
                'image' => 'https://images.unsplash.com/photo-1574323347407-f5e1ad6d020b?auto=format&fit=crop&w=600&q=80',
            ],
            // 3. Food Grains - Toor Dal
            [
                'title' => 'Desi Unpolished Toor Dal Fatka (30kg Bag)',
                'slug' => 'desi-unpolished-toor-dal-fatka-30kg',
                'short_description' => 'Latur special unpolished toor dal, 100% natural, quick cooking.',
                'description' => 'Famous Latur quality unpolished Toor Dal processed naturally without artificial water or oil polish. Rich in dietary protein and natural enzymes. Highly preferred by premium hotels, restaurants, institutions, and supermarket chains for consistent taste and cooking yield.',
                'category_slug' => 'toor-dal-premium',
                'unit_id' => $packUnit,
                'min_order_qty' => 15,
                'max_order_qty' => 500,
                'pack_qty' => 30,
                'mrp' => 4800,
                'base_price' => 4200,
                'tiers' => [
                    ['min' => 15, 'max' => 39, 'price' => 4200],
                    ['min' => 40, 'max' => 79, 'price' => 3950],
                    ['min' => 80, 'max' => null, 'price' => 3750],
                ],
                'offer_text' => 'Wholesale Tier Available',
                'image' => 'https://images.unsplash.com/photo-1585994192701-f1a505c817ea?auto=format&fit=crop&w=600&q=80',
            ],
            // 4. Agro Products - Potatoes
            [
                'title' => 'Fresh Agra Jyoti Table Potatoes (Bulk 50kg Mesh Bag)',
                'slug' => 'fresh-agra-jyoti-table-potatoes-bulk-50kg',
                'short_description' => 'Uniform 45mm+ size table potatoes, minimal soil, cold storage conditioned.',
                'description' => 'Freshly harvested Agra Jyoti variety table potatoes. Uniform size grading (45mm+), firm skin, zero greening and ready for wholesale distributors, chip manufacturers, fast food chains, and vegetable vendors.',
                'category_slug' => 'potatoes-grade-a',
                'unit_id' => $packUnit,
                'min_order_qty' => 15,
                'max_order_qty' => 500,
                'pack_qty' => 50,
                'mrp' => 1400,
                'base_price' => 1100,
                'tiers' => [
                    ['min' => 15, 'max' => 49, 'price' => 1100],
                    ['min' => 50, 'max' => 99, 'price' => 980],
                    ['min' => 100, 'max' => null, 'price' => 880],
                ],
                'offer_text' => 'Direct Farm Dispatch',
                'image' => 'https://images.unsplash.com/photo-1518977676601-b53f82aba655?auto=format&fit=crop&w=600&q=80',
            ],
            // 5. Agro Products - Red Onions
            [
                'title' => 'Nasik Medium Red Onions (Bulk 40kg Mesh Bags)',
                'slug' => 'nasik-medium-red-onions-bulk-40kg',
                'short_description' => 'Dry red onions 50mm+ diameter, long shelf-life for traders & exporters.',
                'description' => 'Grade A red onions sourced directly from Lasalgaon, Nasik. Cured properly under sun, dry paper-thin outer skin, robust pungency and excellent shelf life of up to 45 days. Best for wholesale mandis, retail chains, and food processing units.',
                'category_slug' => 'red-onions-nasik',
                'unit_id' => $packUnit,
                'min_order_qty' => 20,
                'max_order_qty' => 600,
                'pack_qty' => 40,
                'mrp' => 1700,
                'base_price' => 1350,
                'tiers' => [
                    ['min' => 20, 'max' => 49, 'price' => 1350],
                    ['min' => 50, 'max' => 99, 'price' => 1220],
                    ['min' => 100, 'max' => null, 'price' => 1100],
                ],
                'offer_text' => 'Mandi Wholesale Price',
                'image' => 'https://images.unsplash.com/photo-1618512496248-a07fe83aa8cb?auto=format&fit=crop&w=600&q=80',
            ],
            // 6. Agro Products - Coconuts
            [
                'title' => 'Pollachi De-Husked Mature Coconuts (Bag of 50 Pcs)',
                'slug' => 'pollachi-de-husked-mature-coconuts-bag-50-pcs',
                'short_description' => 'Heavy grade 550g+ mature coconuts, rich copra & water, for oil & retail.',
                'description' => 'Selected mature brown coconuts from Pollachi, Tamil Nadu. Average weight 550g to 600g per nut, thick kernel, high oil recovery and rich sweet water. Sacked in ventilated gunny bags with negligible transit breakage.',
                'category_slug' => 'fresh-coconuts',
                'unit_id' => $packUnit,
                'min_order_qty' => 10,
                'max_order_qty' => 300,
                'pack_qty' => 50,
                'mrp' => 1800,
                'base_price' => 1450,
                'tiers' => [
                    ['min' => 10, 'max' => 24, 'price' => 1450],
                    ['min' => 25, 'max' => 49, 'price' => 1320],
                    ['min' => 50, 'max' => null, 'price' => 1200],
                ],
                'offer_text' => 'Only ₹24/nut at 50+ bags',
                'image' => 'https://images.unsplash.com/photo-1544476915-ed1370594142?auto=format&fit=crop&w=600&q=80',
            ],
            // 7. Electronics Wholesale - Fast Type-C Cables
            [
                'title' => 'Braided Fast Charging Type-C Cables (Carton of 100 Pcs)',
                'slug' => 'braided-fast-charging-type-c-cables-carton-100-pcs',
                'short_description' => '65W Fast Charge, 1.2m nylon braided, retail barcode hang-card packaging.',
                'description' => 'Commercial wholesale lot of 100 pieces heavy-duty Type-C charging and sync cables. Supports 65W PD fast charging, reinforced aluminum connectors, tangle-free nylon braiding, and individual retail blister packaging ready for store shelves.',
                'category_slug' => 'fast-charging-cables',
                'unit_id' => $packUnit,
                'min_order_qty' => 5,
                'max_order_qty' => 200,
                'pack_qty' => 100,
                'mrp' => 5900,
                'base_price' => 3500,
                'tiers' => [
                    ['min' => 5, 'max' => 19, 'price' => 3500],
                    ['min' => 20, 'max' => 49, 'price' => 3000],
                    ['min' => 50, 'max' => null, 'price' => 2500],
                ],
                'offer_text' => '₹25/pc on 50+ cartons (100pcs/ctn)',
                'image' => 'https://images.unsplash.com/photo-1541658016709-82535e94bc69?auto=format&fit=crop&w=600&q=80',
            ],
            // 8. Electronics Wholesale - 20W PD Adapters
            [
                'title' => '20W PD USB-C Fast Wall Chargers (Carton of 50 Pcs)',
                'slug' => '20w-pd-usb-c-fast-wall-chargers-carton-50-pcs',
                'short_description' => 'BIS Approved, smart IC safety, dual pin Indian plug for retail shops.',
                'description' => 'Bulk pack of 50 units BIS certified 20W USB Power Delivery wall adapters. Built-in over-voltage and short circuit protection. Compatible with all modern iOS and Android smartphones. Attractive retail packaging with hologram seal.',
                'category_slug' => '20w-pd-wall-adapters',
                'unit_id' => $packUnit,
                'min_order_qty' => 2,
                'max_order_qty' => 100,
                'pack_qty' => 50,
                'mrp' => 9950,
                'base_price' => 6500,
                'tiers' => [
                    ['min' => 2, 'max' => 9, 'price' => 6500],
                    ['min' => 10, 'max' => 24, 'price' => 5800],
                    ['min' => 25, 'max' => null, 'price' => 5100],
                ],
                'offer_text' => 'Margin > 60% for retailers',
                'image' => 'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?auto=format&fit=crop&w=600&q=80',
            ],
            // 9. Spices & Oils - Malabar Black Pepper
            [
                'title' => 'Wayanad Malabar Black Pepper TGSEB (25kg Sack)',
                'slug' => 'wayanad-malabar-black-pepper-tgseb-25kg',
                'short_description' => 'Tellicherry Garbled Special Extra Bold, 580 G/L density, high piperine.',
                'description' => 'Supreme export quality TGSEB Malabar Black Pepper sourced directly from high-range estates in Wayanad, Kerala. Naturally dried, bold spherical peppercorns with rich pungent aroma and guaranteed minimum 5.5% piperine content. Packaged in vacuum-sealed food-grade sacks.',
                'category_slug' => 'malabar-black-pepper',
                'unit_id' => $packUnit,
                'min_order_qty' => 2,
                'max_order_qty' => 80,
                'pack_qty' => 25,
                'mrp' => 19500,
                'base_price' => 16250,
                'tiers' => [
                    ['min' => 2, 'max' => 9, 'price' => 16250],
                    ['min' => 10, 'max' => 24, 'price' => 15500],
                    ['min' => 25, 'max' => null, 'price' => 14750],
                ],
                'offer_text' => 'Export Grade TGSEB Spec',
                'image' => 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=600&q=80',
            ],
            // 10. Spices & Oils - Green Cardamom
            [
                'title' => 'Idukki Green Cardamom 8mm Bold Grade (10kg Master Carton)',
                'slug' => 'idukki-green-cardamom-8mm-bold-grade-10kg',
                'short_description' => '8mm+ whole green pods, intense aroma, moisture < 10%, vacuum packed.',
                'description' => 'Top-tier 8mm+ Green Cardamom harvested from the misty hills of Idukki. Vibrant parrot green color, full plump pods packed with dark brown seeds, and rich aromatic volatile oils. Ideal for luxury sweet manufacturers, spice blenders, and export repackagers.',
                'category_slug' => 'green-cardamom-8mm',
                'unit_id' => $packUnit,
                'min_order_qty' => 2,
                'max_order_qty' => 50,
                'pack_qty' => 10,
                'mrp' => 28000,
                'base_price' => 24000,
                'tiers' => [
                    ['min' => 2, 'max' => 4, 'price' => 24000],
                    ['min' => 5, 'max' => 9, 'price' => 22500],
                    ['min' => 10, 'max' => null, 'price' => 21000],
                ],
                'offer_text' => 'Special Auction Lot Price',
                'image' => 'https://images.unsplash.com/photo-1599909634599-2a912bbbc5ea?auto=format&fit=crop&w=600&q=80',
            ],
            // 11. Packaging Supplies - 5-Ply Cartons
            [
                'title' => '5-Ply Heavy Duty Master Shipping Cartons (Bundle of 50 Pcs)',
                'slug' => '5-ply-heavy-duty-master-shipping-cartons-bundle-50',
                'short_description' => 'Virgin kraft paper, 180 GSM, burst strength 14 kg/cm², flat shipped.',
                'description' => 'Heavy duty 5-ply corrugated carton boxes sized 18x14x12 inches. Manufactured with high burst factor 180 GSM virgin kraft liner. Capable of supporting stacking loads up to 35 kg. Perfect for e-commerce warehouses, export shipments, and wholesale logistics.',
                'category_slug' => '5-ply-heavy-master-cartons',
                'unit_id' => $packUnit,
                'min_order_qty' => 5,
                'max_order_qty' => 300,
                'pack_qty' => 50,
                'mrp' => 2200,
                'base_price' => 1750,
                'tiers' => [
                    ['min' => 5, 'max' => 19, 'price' => 1750],
                    ['min' => 20, 'max' => 49, 'price' => 1500],
                    ['min' => 50, 'max' => null, 'price' => 1300],
                ],
                'offer_text' => 'Only ₹26/box at 50+ bundles',
                'image' => 'https://images.unsplash.com/photo-1566576721346-d4a3b4eaeb55?auto=format&fit=crop&w=600&q=80',
            ],
            // 12. Packaging Supplies - Bubble Wrap Rolls
            [
                'title' => 'Industrial Protective Air Bubble Wrap Rolls (Bundle of 5 Rolls)',
                'slug' => 'industrial-protective-air-bubble-wrap-rolls-bundle-5',
                'short_description' => '10mm bubble diameter, 100m length x 1m width, 45 GSM high cushioning.',
                'description' => 'Commercial pack of 5 rolls premium two-layer air bubble film. Each roll measures 1 meter wide by 100 meters long with resilient 10mm air bubbles. Maximum shock absorption for fragile goods, glass, electronics, and parcel packaging.',
                'category_slug' => 'bubble-wrap-rolls-100m',
                'unit_id' => $packUnit,
                'min_order_qty' => 2,
                'max_order_qty' => 60,
                'pack_qty' => 5,
                'mrp' => 3200,
                'base_price' => 2400,
                'tiers' => [
                    ['min' => 2, 'max' => 9, 'price' => 2400],
                    ['min' => 10, 'max' => 24, 'price' => 2100],
                    ['min' => 25, 'max' => null, 'price' => 1850],
                ],
                'offer_text' => 'Direct Factory Pricing',
                'image' => 'https://images.unsplash.com/photo-1589939705384-5185137a7f0f?auto=format&fit=crop&w=600&q=80',
            ],
            // 13. Home & Furniture (Trading) - Ergonomic Chairs
            [
                'title' => 'High-Back Ergonomic Breathable Mesh Chairs (Lot of 10 Units)',
                'slug' => 'high-back-ergonomic-breathable-mesh-chairs-lot-10',
                'short_description' => 'BIFMA certified class 4 gas lift, 2D lumbar support, nylon star base.',
                'description' => 'Commercial grade executive ergonomic office chairs supplied in lots of 10 units. Features breathable Korean mesh back, high-density molded foam seat, 3D adjustable armrests, synchronized tilt lock mechanism, and BIFMA level 3 durability certification. Ideal for corporate fitouts, co-working spaces, and IT infrastructure.',
                'category_slug' => 'ergonomic-mesh-chairs-lot',
                'unit_id' => $setUnit,
                'min_order_qty' => 1,
                'max_order_qty' => 50,
                'pack_qty' => 10,
                'mrp' => 49990,
                'base_price' => 38000,
                'tiers' => [
                    ['min' => 1, 'max' => 4, 'price' => 38000],
                    ['min' => 5, 'max' => 9, 'price' => 34500],
                    ['min' => 10, 'max' => null, 'price' => 31000],
                ],
                'offer_text' => '₹3,100/chair at 10+ lots (100 chairs)',
                'image' => 'https://images.unsplash.com/photo-1580481077195-c3a821a58875?auto=format&fit=crop&w=600&q=80',
            ],
            // 14. Home & Furniture (Trading) - Banquet Chairs
            [
                'title' => 'Heavy Duty Steel Stackable Banquet Chairs (Lot of 25 Chairs)',
                'slug' => 'heavy-duty-steel-stackable-banquet-chairs-lot-25',
                'short_description' => 'Powder coated steel frame, 2.5 inch fire-retardant foam, stackable up to 12.',
                'description' => 'Wholesale set of 25 commercial banquet chairs built for wedding halls, hotels, churches, and convention centers. Made from heavy 16-gauge square steel tubing with double support braces, commercial fabric upholstery, and protective nylon bumper guards for vertical stacking.',
                'category_slug' => 'banquet-stackable-chairs',
                'unit_id' => $setUnit,
                'min_order_qty' => 2,
                'max_order_qty' => 40,
                'pack_qty' => 25,
                'mrp' => 42500,
                'base_price' => 32500,
                'tiers' => [
                    ['min' => 2, 'max' => 4, 'price' => 32500],
                    ['min' => 5, 'max' => 9, 'price' => 29000],
                    ['min' => 10, 'max' => null, 'price' => 26500],
                ],
                'offer_text' => '₹1,060/chair on 10+ lots',
                'image' => 'https://images.unsplash.com/photo-1503602642458-232111445657?auto=format&fit=crop&w=600&q=80',
            ],
        ];

        foreach ($wholesaleProducts as $pData) {
            $cat = Category::where('slug', $pData['category_slug'])->first();
            $catId = $cat ? $cat->id : null;

            // Upsert product by slug
            $product = Product::firstOrNew(['slug' => $pData['slug']]);
            $product->user_id = $adminId;
            $product->category_id = $catId;
            $product->unit_id = $pData['unit_id'];
            $product->title = $pData['title'];
            $product->slug = $pData['slug'];
            $product->model_no = 'TRD-' . strtoupper(Str::random(6));
            $product->short_description = $pData['short_description'];
            $product->description = $pData['description'];
            $product->sale_type = 'wholesale';
            $product->min_order_qty = $pData['min_order_qty'];
            $product->max_order_qty = $pData['max_order_qty'];
            $product->pack_qty = $pData['pack_qty'];
            $product->track_stock = true;
            $product->offer_text = $pData['offer_text'];
            $product->is_featured = true;
            $product->is_assured = true;
            $product->rating_avg = 4.8;
            $product->rating_count = rand(60, 350);
            $product->status = 'active';
            $product->published_at = now();
            $product->save();

            // Default variant
            $sku = 'SKU-TRD-' . strtoupper(Str::slug(substr($pData['title'], 0, 15))) . '-' . $product->id;
            $variant = ProductVariant::firstOrNew(['product_id' => $product->id, 'is_default' => true]);
            $variant->sku = $sku;
            $variant->price = $pData['base_price'];
            $variant->mrp = $pData['mrp'];
            $variant->cost_price = round($pData['base_price'] * 0.85);
            $variant->stock = rand(500, 5000);
            $variant->is_default = true;
            $variant->is_active = true;
            $variant->save();

            // Product image
            ProductImage::firstOrCreate(
                ['product_id' => $product->id, 'path' => $pData['image']],
                ['sort_order' => 1, 'is_primary' => true]
            );

            // Wholesale Price Tiers
            // Clear existing wholesale tiers for idempotence
            ProductPriceTier::where('product_id', $product->id)
                ->where('price_type', 'wholesale')
                ->delete();

            foreach ($pData['tiers'] as $tier) {
                ProductPriceTier::create([
                    'product_id' => $product->id,
                    'variant_id' => $variant->id,
                    'price_type' => 'wholesale',
                    'min_qty' => $tier['min'],
                    'max_qty' => $tier['max'],
                    'price' => $tier['price'],
                ]);
            }
        }
    }
}
