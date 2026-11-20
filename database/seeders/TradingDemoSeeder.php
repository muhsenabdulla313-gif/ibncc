<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use App\Models\Banner;
use App\Models\User;
use Illuminate\Support\Str;

class TradingDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $adminId = $admin ? $admin->id : 1;

        /* ==================== 1. HERO BANNERS ==================== */
        $bannersData = [
            [
                'image' => 'assets/images/gift2.png',
                'eyebrow' => 'IBNCC Presents',
                'title' => 'Floral Bloom',
                'subtitle' => 'Timeless pieces for moments that matter',
                'cta_text' => 'Shop Now',
                'link' => '/products',
                'text_position' => 'right',
                'sort_order' => 1,
            ],
            [
                'image' => 'assets/images/harvest.png',
                'eyebrow' => 'Farm & Garden',
                'title' => 'Harvest Fresh',
                'subtitle' => 'Quality produce from trusted local growers',
                'cta_text' => 'Explore Deals',
                'link' => '/products?cat=farm-garden',
                'text_position' => 'left',
                'sort_order' => 2,
            ],
            [
                'image' => 'assets/images/network1.png',
                'eyebrow' => 'Networking',
                'title' => 'Connect & Grow',
                'subtitle' => 'Build trusted partnerships across the community',
                'cta_text' => 'Join Network',
                'link' => '/members',
                'text_position' => 'left',
                'sort_order' => 3,
            ],
            [
                'image' => 'assets/images/sld3.png',
                'eyebrow' => 'Food Items',
                'title' => 'Market Essentials',
                'subtitle' => 'Everyday staples delivered with care',
                'cta_text' => 'Shop Food',
                'link' => '/products?cat=food-items',
                'text_position' => 'right',
                'sort_order' => 4,
            ],
            [
                'image' => 'assets/images/wholesale.png',
                'eyebrow' => 'Trading Hub',
                'title' => 'Bulk. Better. Together.',
                'subtitle' => 'Commodities and wholesale deals you can trust',
                'cta_text' => 'Start Trading',
                'link' => '/trading/products',
                'text_position' => 'left',
                'sort_order' => 5,
            ],
        ];

        foreach ($bannersData as $b) {
            Banner::firstOrCreate(
                ['image' => $b['image']],
                [
                    'eyebrow' => $b['eyebrow'],
                    'title' => $b['title'],
                    'subtitle' => $b['subtitle'],
                    'cta_text' => $b['cta_text'],
                    'link' => $b['link'],
                    'text_position' => $b['text_position'],
                    'sort_order' => $b['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        /* ==================== 2. TRADING CATEGORIES ==================== */
        $tradingCats = [
            [
                'name' => 'Food Grains',
                'slug' => 'food-grains',
                'description' => 'Quality grains in bulk, delivered with trust',
                'image' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=600&q=80',
                'icon' => 'fa-wheat-awn',
                'sort_order' => 1,
            ],
            [
                'name' => 'Agro Products',
                'slug' => 'agro-products',
                'description' => 'Fresh produce for growing businesses',
                'image' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=600&q=80',
                'icon' => 'fa-seedling',
                'sort_order' => 2,
            ],
            [
                'name' => 'Electronics Wholesale',
                'slug' => 'electronics-wholesale',
                'description' => 'Top brands. Best prices. For your business',
                'image' => 'https://images.unsplash.com/photo-1498049794561-7780e7231661?auto=format&fit=crop&w=600&q=80',
                'icon' => 'fa-desktop',
                'sort_order' => 3,
            ],
            [
                'name' => 'Home & Furniture',
                'slug' => 'home-furniture-trading',
                'description' => 'Premium quality furniture for every space',
                'image' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=600&q=80',
                'icon' => 'fa-couch',
                'sort_order' => 4,
            ],
            [
                'name' => 'Spices & Oils',
                'slug' => 'spices-oils',
                'description' => 'Authentic flavours in wholesale quantities',
                'image' => 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=600&q=80',
                'icon' => 'fa-mortar-pestle',
                'sort_order' => 5,
            ],
            [
                'name' => 'Packaging Supplies',
                'slug' => 'packaging-supplies',
                'description' => 'Reliable packing solutions for every shipment',
                'image' => 'https://images.unsplash.com/photo-1566576721346-d4a3b4eaeb55?auto=format&fit=crop&w=600&q=80',
                'icon' => 'fa-box',
                'sort_order' => 6,
            ],
        ];

        foreach ($tradingCats as $tc) {
            $cat = Category::firstOrNew(['slug' => $tc['slug']]);
            $cat->name = $tc['name'];
            $cat->description = $tc['description'];
            $cat->image = $tc['image'];
            $cat->icon = $tc['icon'];
            $cat->sort_order = $tc['sort_order'];
            $cat->is_active = true;
            $cat->is_trading = true;
            $cat->save();
        }

        /* ==================== 3. BESTSELLERS & MARKETPLACE PRODUCTS ==================== */
        $productsData = [
            // Bestsellers
            [
                'title' => 'boAt Airdopes 141',
                'slug' => 'boat-airdopes-141',
                'short_description' => 'Wireless Earbuds',
                'description' => 'boAt Airdopes 141 Bluetooth Truly Wireless in Ear Earbuds with 42H Playtime, Beast Mode, ENx Tech, ASAP Charge, IWP, IPX4 Water Resistance.',
                'sale_type' => 'retail',
                'price' => 1299,
                'mrp' => 2999,
                'rating_avg' => 4.5,
                'rating_count' => 12400,
                'is_featured' => true,
                'offer_text' => '57% OFF',
                'image' => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=400&q=80',
                'category_slug' => 'electronics',
            ],
            [
                'title' => 'Noise ColorFit Pro 4',
                'slug' => 'noise-colorfit-pro-4',
                'short_description' => 'Smartwatch',
                'description' => 'Noise ColorFit Pro 4 Alpha 1.78" AMOLED Display, Bluetooth Calling Smart Watch, Functional Crown, 7-Day Battery, Gesture Control.',
                'sale_type' => 'retail',
                'price' => 2499,
                'mrp' => 5999,
                'rating_avg' => 4.4,
                'rating_count' => 8100,
                'is_featured' => true,
                'offer_text' => '58% OFF',
                'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=400&q=80',
                'category_slug' => 'electronics',
            ],
            [
                'title' => 'Sony WH-CH520',
                'slug' => 'sony-wh-ch520',
                'short_description' => 'Bluetooth Headphones',
                'description' => 'Sony WH-CH520 Wireless Bluetooth On-Ear Headphones with Mic, up to 50 Hours Playtime, DSEE, Multipoint Connection.',
                'sale_type' => 'retail',
                'price' => 4490,
                'mrp' => 5990,
                'rating_avg' => 4.6,
                'rating_count' => 5200,
                'is_featured' => true,
                'offer_text' => '25% OFF',
                'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=400&q=80',
                'category_slug' => 'electronics',
            ],
            [
                'title' => 'Minimalist SPF 50',
                'slug' => 'minimalist-spf-50',
                'short_description' => 'Sunscreen',
                'description' => 'Minimalist Sunscreen SPF 50 PA++++ With Niacinamide, Vitamin B5 & F for Broad Spectrum Protection, Lightweight & Non-Greasy.',
                'sale_type' => 'retail',
                'price' => 399,
                'mrp' => 499,
                'rating_avg' => 4.7,
                'rating_count' => 21000,
                'is_featured' => true,
                'offer_text' => '20% OFF',
                'image' => 'https://images.unsplash.com/photo-1585386959984-a4155224a1ad?auto=format&fit=crop&w=400&q=80',
                'category_slug' => 'women',
            ],
            [
                'title' => 'Nike Revolution 6',
                'slug' => 'nike-revolution-6',
                'short_description' => "Men's Running Shoes",
                'description' => 'Nike Revolution 6 Next Nature Mens Road Running Shoes with plush cushioning and flexible traction.',
                'sale_type' => 'retail',
                'price' => 3695,
                'mrp' => 4495,
                'rating_avg' => 4.3,
                'rating_count' => 3800,
                'is_featured' => true,
                'offer_text' => '18% OFF',
                'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=400&q=80',
                'category_slug' => 'men',
            ],
            [
                'title' => 'Prestige Mixer',
                'slug' => 'prestige-mixer',
                'short_description' => 'Kitchen Appliance',
                'description' => 'Prestige Iris Plus 750 W Mixer Grinder with 4 Jars, Stainless Steel Blades, Sturdy Handles.',
                'sale_type' => 'retail',
                'price' => 2999,
                'mrp' => 5495,
                'rating_avg' => 4.2,
                'rating_count' => 9600,
                'is_featured' => true,
                'offer_text' => '45% OFF',
                'image' => 'https://images.unsplash.com/photo-1585659722983-3a675dabf23d?auto=format&fit=crop&w=400&q=80',
                'category_slug' => 'home-furniture',
            ],

            // Marketplace Carousel Items
            [
                'title' => 'Luggage Collection',
                'slug' => 'luggage-collection',
                'short_description' => 'Travel in style with top brands',
                'description' => 'Durable hard-shell and soft-shell luggage sets built for journeys near and far.',
                'sale_type' => 'retail',
                'price' => 4999,
                'mrp' => 9999,
                'rating_avg' => 4.6,
                'rating_count' => 3200,
                'is_featured' => true,
                'offer_text' => 'Up to 50% Off',
                'image' => 'https://images.unsplash.com/photo-1565022449183-c4a2d0b2a0a0?auto=format&fit=crop&w=500&q=80',
                'category_slug' => 'men',
            ],
            [
                'title' => 'Watches & More',
                'slug' => 'watches-and-more',
                'short_description' => 'Timeless style. Smart choices.',
                'description' => 'Classic wristwatches and modern smart fitness bands from international watchmakers.',
                'sale_type' => 'retail',
                'price' => 1999,
                'mrp' => 4999,
                'rating_avg' => 4.5,
                'rating_count' => 4800,
                'is_featured' => true,
                'offer_text' => 'Up to 60% Off',
                'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=500&q=80',
                'category_slug' => 'electronics',
            ],
            [
                'title' => 'Jewellery Collection',
                'slug' => 'jewellery-collection',
                'short_description' => 'Shine with elegance',
                'description' => 'Handcrafted necklaces, earrings, bangles and rings in gold, silver and fine stones.',
                'sale_type' => 'retail',
                'price' => 2499,
                'mrp' => 6250,
                'rating_avg' => 4.8,
                'rating_count' => 2900,
                'is_featured' => true,
                'offer_text' => 'Up to 60% Off',
                'image' => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?auto=format&fit=crop&w=500&q=80',
                'category_slug' => 'women',
            ],
            [
                'title' => 'Kindle Paperwhite',
                'slug' => 'kindle-paperwhite',
                'short_description' => 'Your next great read awaits',
                'description' => 'Kindle Paperwhite (16 GB) with 6.8" display, adjustable warm light, up to 10 weeks battery life.',
                'sale_type' => 'retail',
                'price' => 16999,
                'mrp' => 21999,
                'rating_avg' => 4.7,
                'rating_count' => 6400,
                'is_featured' => true,
                'offer_text' => '',
                'image' => 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&w=500&q=80',
                'category_slug' => 'electronics',
            ],
            [
                'title' => 'Fashion Essentials',
                'slug' => 'fashion-essentials',
                'short_description' => 'Everyday style for the family',
                'description' => 'Comfortable cotton shirts, trousers, dresses and casual wear crafted for every day.',
                'sale_type' => 'retail',
                'price' => 899,
                'mrp' => 1499,
                'rating_avg' => 4.3,
                'rating_count' => 5100,
                'is_featured' => true,
                'offer_text' => 'Up to 40% Off',
                'image' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=500&q=80',
                'category_slug' => 'men',
            ],
            [
                'title' => 'Beauty & Care',
                'slug' => 'beauty-and-care',
                'short_description' => 'Glow with trusted brands',
                'description' => 'Premium skincare, haircare, and personal beauty essentials for healthy daily routines.',
                'sale_type' => 'retail',
                'price' => 649,
                'mrp' => 999,
                'rating_avg' => 4.6,
                'rating_count' => 7300,
                'is_featured' => true,
                'offer_text' => 'Up to 35% Off',
                'image' => 'assets/images/bt.jpg',
                'category_slug' => 'women',
            ],

            // Similar Searches Products:
            // 1) Luggage & Travel (3 items)
            [
                'title' => 'Hard-shell Suitcase',
                'slug' => 'hard-shell-suitcase',
                'short_description' => 'Cabin Luggage',
                'description' => 'Impact-resistant polycarbonate hard-shell travel suitcase with 360 spinner wheels.',
                'sale_type' => 'retail',
                'price' => 3499,
                'mrp' => 6999,
                'rating_avg' => 4.5,
                'rating_count' => 1500,
                'image' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=500&q=80',
                'category_slug' => 'men',
            ],
            [
                'title' => 'Travel Duffel Bag',
                'slug' => 'travel-duffel-bag',
                'short_description' => 'Weekend Duffel',
                'description' => 'Spacious and waterproof travel duffel bag with shoe compartment.',
                'sale_type' => 'retail',
                'price' => 1499,
                'mrp' => 2999,
                'rating_avg' => 4.4,
                'rating_count' => 890,
                'image' => 'https://images.unsplash.com/photo-1565026057447-bc90a3dceb87?auto=format&fit=crop&w=500&q=80',
                'category_slug' => 'men',
            ],
            [
                'title' => 'Cabin Luggage Set',
                'slug' => 'cabin-luggage-set',
                'short_description' => 'Set of 2 Trolleys',
                'description' => 'Matching cabin and check-in luggage combo with TSA lock.',
                'sale_type' => 'retail',
                'price' => 5999,
                'mrp' => 11999,
                'rating_avg' => 4.6,
                'rating_count' => 1200,
                'image' => 'assets/images/lag2.jpg',
                'category_slug' => 'men',
            ],

            // 2) Smartwatches (3 items)
            [
                'title' => 'Amoled Black Smartwatch',
                'slug' => 'amoled-black-smartwatch',
                'short_description' => 'Fitness Smartwatch',
                'description' => 'Full touch high resolution AMOLED smartwatch with heart rate & SpO2 tracking.',
                'sale_type' => 'retail',
                'price' => 2999,
                'mrp' => 6999,
                'rating_avg' => 4.5,
                'rating_count' => 2400,
                'image' => 'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?auto=format&fit=crop&w=500&q=80',
                'category_slug' => 'electronics',
            ],
            [
                'title' => 'Sport Smartwatch',
                'slug' => 'sport-smartwatch',
                'short_description' => 'GPS Multisport Watch',
                'description' => 'Rugged waterproof sports watch with 100+ workout modes and built-in GPS.',
                'sale_type' => 'retail',
                'price' => 3999,
                'mrp' => 7999,
                'rating_avg' => 4.3,
                'rating_count' => 1100,
                'image' => 'https://images.unsplash.com/photo-1434493789847-2f02dc6ca35d?auto=format&fit=crop&w=500&q=80',
                'category_slug' => 'electronics',
            ],
            [
                'title' => 'Classic Leather Smartwatch',
                'slug' => 'classic-leather-smartwatch',
                'short_description' => 'Hybrid Watch',
                'description' => 'Elegant stainless steel case with genuine leather strap and smart health notifications.',
                'sale_type' => 'retail',
                'price' => 4999,
                'mrp' => 9999,
                'rating_avg' => 4.7,
                'rating_count' => 3100,
                'image' => 'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?auto=format&fit=crop&w=500&q=80',
                'category_slug' => 'electronics',
            ],

            // 3) Home Essentials (3 items)
            [
                'title' => 'Modern Bedroom Bed',
                'slug' => 'modern-bedroom-bed',
                'short_description' => 'King Size Bed',
                'description' => 'Solid teak wood king-size bed frame with ergonomic headboard.',
                'sale_type' => 'retail',
                'price' => 24999,
                'mrp' => 35000,
                'rating_avg' => 4.6,
                'rating_count' => 540,
                'image' => 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=500&q=80',
                'category_slug' => 'home-furniture',
            ],
            [
                'title' => 'Nordic Dining Set',
                'slug' => 'nordic-dining-set',
                'short_description' => '6-Seater Dining Table',
                'description' => 'Contemporary solid wood dining table with 6 cushioned chairs.',
                'sale_type' => 'retail',
                'price' => 18999,
                'mrp' => 28000,
                'rating_avg' => 4.5,
                'rating_count' => 430,
                'image' => 'https://images.unsplash.com/photo-1617806118233-18e1de247200?auto=format&fit=crop&w=500&q=80',
                'category_slug' => 'home-furniture',
            ],
            [
                'title' => 'Plush Living Room Sofa',
                'slug' => 'plush-living-room-sofa',
                'short_description' => '3-Seater Fabric Sofa',
                'description' => 'Ultra-comfortable 3-seater luxury couch with high-density foam padding.',
                'sale_type' => 'retail',
                'price' => 21999,
                'mrp' => 32000,
                'rating_avg' => 4.7,
                'rating_count' => 780,
                'image' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=500&q=80',
                'category_slug' => 'home-furniture',
            ],

            // 4) Kitchen Appliances (3 items)
            [
                'title' => 'Digital Microwave Oven',
                'slug' => 'digital-microwave-oven',
                'short_description' => 'Convection Microwave',
                'description' => '28L convection microwave oven with auto cook menus and defrost features.',
                'sale_type' => 'retail',
                'price' => 9999,
                'mrp' => 14990,
                'rating_avg' => 4.4,
                'rating_count' => 2200,
                'image' => 'https://images.unsplash.com/photo-1585659722983-3a675dabf23d?auto=format&fit=crop&w=500&q=80',
                'category_slug' => 'home-furniture',
            ],
            [
                'title' => 'Rapid Air Fryer',
                'slug' => 'rapid-air-fryer',
                'short_description' => '4.2L Digital Air Fryer',
                'description' => 'Oil-free rapid crisp air fryer with touch screen controls and timer.',
                'sale_type' => 'retail',
                'price' => 4999,
                'mrp' => 8990,
                'rating_avg' => 4.6,
                'rating_count' => 3400,
                'image' => 'https://images.unsplash.com/photo-1585515320310-259814833e62?auto=format&fit=crop&w=500&q=80',
                'category_slug' => 'home-furniture',
            ],
            [
                'title' => 'High-Speed Smoothie Blender',
                'slug' => 'high-speed-smoothie-blender',
                'short_description' => 'Nutri-Blender 1000W',
                'description' => '1000W high-speed motor extractor with bullet cups for fresh juices and smoothies.',
                'sale_type' => 'retail',
                'price' => 2499,
                'mrp' => 4990,
                'rating_avg' => 4.5,
                'rating_count' => 1900,
                'image' => 'https://images.unsplash.com/photo-1574269909862-7e1d70bb8078?auto=format&fit=crop&w=500&q=80',
                'category_slug' => 'home-furniture',
            ],
        ];

        foreach ($productsData as $pd) {
            $cat = Category::where('slug', $pd['category_slug'])->first();
            $catId = $cat ? $cat->id : null;

            $product = Product::firstOrNew(['slug' => $pd['slug']]);
            $isNew = !$product->exists;

            $product->user_id = $adminId;
            $product->category_id = $catId;
            $product->unit_id = 1;
            $product->title = $pd['title'];
            $product->short_description = $pd['short_description'];
            $product->description = $pd['description'];
            $product->sale_type = $pd['sale_type'];
            $product->status = 'active';
            $product->published_at = now();
            $product->rating_avg = $pd['rating_avg'];
            $product->rating_count = $pd['rating_count'];
            $product->offer_text = $pd['offer_text'] ?? null;
            $product->is_featured = $pd['is_featured'] ?? false;
            $product->save();

            // Default variant
            $variant = ProductVariant::firstOrNew([
                'product_id' => $product->id,
                'is_default' => true,
            ]);
            $variant->sku = 'SKU-' . strtoupper(Str::slug($pd['title']));
            $variant->price = $pd['price'];
            $variant->mrp = $pd['mrp'];
            $variant->stock = 100;
            $variant->is_active = true;
            $variant->save();

            // Product image
            ProductImage::firstOrCreate([
                'product_id' => $product->id,
                'path' => $pd['image'],
            ], [
                'sort_order' => 1,
            ]);
        }
    }
}
