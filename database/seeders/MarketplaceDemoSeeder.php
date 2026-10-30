<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class MarketplaceDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('users')) {
            throw new RuntimeException('Run the users migration first.');
        }

        $columns = ['id'];
        foreach (['name', 'phone', 'email'] as $column) {
            if (Schema::hasColumn('users', $column)) $columns[] = $column;
        }
        $users = DB::table('users')->select($columns)->orderBy('id')->limit(2)->get();
        if ($users->isEmpty()) {
            throw new RuntimeException('Register at least one user before running this seeder. Existing users are reused as seller/buyer.');
        }

        $seller = $users[0];
        $buyer = $users[1] ?? $users[0];
        $sellerName = $seller->name ?? 'Demo Seller';
        $sellerPhone = $seller->phone ?? null;
        $buyerName = $buyer->name ?? 'Demo Buyer';
        $buyerPhone = $buyer->phone ?? $sellerPhone ?? '0000000000';
        $buyerEmail = $buyer->email ?? null;
        $now = now();

        DB::transaction(function () use ($seller, $buyer, $sellerName, $sellerPhone, $buyerName, $buyerPhone, $buyerEmail, $now) {
            // CATEGORIES
            $furniture = $this->id('categories', ['slug'=>'furniture'], [
                'parent_id'=>null, 'name'=>'Furniture', 'path'=>null, 'depth'=>0,
                'description'=>'Furniture for home and office.', 'image'=>'demo/categories/furniture.jpg',
                'meta_title'=>'Furniture', 'meta_description'=>'Retail and wholesale furniture.',
                'sort_order'=>1, 'is_active'=>true, 'created_at'=>$now, 'updated_at'=>$now,
            ]);
            $sofas = $this->id('categories', ['slug'=>'sofas'], [
                'parent_id'=>$furniture, 'name'=>'Sofas', 'path'=>$furniture.'/', 'depth'=>1,
                'description'=>'Sofas and seating.', 'image'=>'demo/categories/sofas.jpg',
                'meta_title'=>'Sofas', 'meta_description'=>'Shop sofas in retail or bulk.',
                'sort_order'=>1, 'is_active'=>true, 'created_at'=>$now, 'updated_at'=>$now,
            ]);
            DB::table('categories')->where('id',$sofas)->update(['path'=>$furniture.'/'.$sofas.'/','updated_at'=>$now]);
            $clothing = $this->id('categories', ['slug'=>'clothing'], [
                'parent_id'=>null, 'name'=>'Clothing', 'path'=>null, 'depth'=>0,
                'description'=>'Clothing and apparel.', 'image'=>'demo/categories/clothing.jpg',
                'meta_title'=>'Clothing', 'meta_description'=>'Retail and wholesale clothing.',
                'sort_order'=>2, 'is_active'=>true, 'created_at'=>$now, 'updated_at'=>$now,
            ]);
            $electronics = $this->id('categories', ['slug'=>'electronics'], [
                'parent_id'=>null, 'name'=>'Electronics', 'path'=>null, 'depth'=>0,
                'description'=>'Electronic products.', 'image'=>'demo/categories/electronics.jpg',
                'meta_title'=>'Electronics', 'meta_description'=>'Browse electronic products.',
                'sort_order'=>3, 'is_active'=>true, 'created_at'=>$now, 'updated_at'=>$now,
            ]);

            // BRANDS
            $homeBrand = $this->id('brands',['slug'=>'demo-home'],[
                'name'=>'Demo Home','logo'=>'demo/brands/demo-home.png','is_active'=>true,
                'created_at'=>$now,'updated_at'=>$now,
            ]);
            $wearBrand = $this->id('brands',['slug'=>'everyday-wear'],[
                'name'=>'Everyday Wear','logo'=>'demo/brands/everyday-wear.png','is_active'=>true,
                'created_at'=>$now,'updated_at'=>$now,
            ]);
            $techBrand = $this->id('brands',['slug'=>'nova-electronics'],[
                'name'=>'Nova Electronics','logo'=>'demo/brands/nova-electronics.png','is_active'=>true,
                'created_at'=>$now,'updated_at'=>$now,
            ]);

            // UNITS
            $piece = $this->id('units',['name'=>'Piece'],[
                'short_name'=>'pc','type'=>'count','base_unit_id'=>null,'conversion_factor'=>1,
                'allow_decimal'=>false,'created_at'=>$now,'updated_at'=>$now,
            ]);
            $kg = $this->id('units',['name'=>'Kilogram'],[
                'short_name'=>'kg','type'=>'weight','base_unit_id'=>null,'conversion_factor'=>1,
                'allow_decimal'=>true,'created_at'=>$now,'updated_at'=>$now,
            ]);
            $this->id('units',['name'=>'Gram'],[
                'short_name'=>'g','type'=>'weight','base_unit_id'=>$kg,'conversion_factor'=>0.001,
                'allow_decimal'=>true,'created_at'=>$now,'updated_at'=>$now,
            ]);
            $this->id('units',['name'=>'Dozen'],[
                'short_name'=>'doz','type'=>'count','base_unit_id'=>$piece,'conversion_factor'=>12,
                'allow_decimal'=>false,'created_at'=>$now,'updated_at'=>$now,
            ]);
            $metre = $this->id('units',['name'=>'Metre'],[
                'short_name'=>'m','type'=>'length','base_unit_id'=>null,'conversion_factor'=>1,
                'allow_decimal'=>true,'created_at'=>$now,'updated_at'=>$now,
            ]);
            $this->id('units',['name'=>'Pack'],[
                'short_name'=>'pack','type'=>'pack','base_unit_id'=>null,'conversion_factor'=>1,
                'allow_decimal'=>false,'created_at'=>$now,'updated_at'=>$now,
            ]);

            // ATTRIBUTE GROUPS, ATTRIBUTES, OPTIONS
            $general = $this->id('attribute_groups',['slug'=>'general'],[
                'name'=>'General','sort_order'=>1,'created_at'=>$now,'updated_at'=>$now,
            ]);
            $dimensions = $this->id('attribute_groups',['slug'=>'dimensions'],[
                'name'=>'Dimensions','sort_order'=>2,'created_at'=>$now,'updated_at'=>$now,
            ]);
            $color = $this->id('attributes',['slug'=>'color'],[
                'attribute_group_id'=>$general,'unit_id'=>null,'name'=>'Color','input_type'=>'color',
                'is_filterable'=>true,'is_searchable'=>true,'is_comparable'=>true,'is_variant_axis'=>true,
                'is_visible'=>true,'sort_order'=>1,'created_at'=>$now,'updated_at'=>$now,
            ]);
            $size = $this->id('attributes',['slug'=>'size'],[
                'attribute_group_id'=>$general,'unit_id'=>null,'name'=>'Size','input_type'=>'select',
                'is_filterable'=>true,'is_searchable'=>true,'is_comparable'=>true,'is_variant_axis'=>true,
                'is_visible'=>true,'sort_order'=>2,'created_at'=>$now,'updated_at'=>$now,
            ]);
            $material = $this->id('attributes',['slug'=>'material'],[
                'attribute_group_id'=>$general,'unit_id'=>null,'name'=>'Material','input_type'=>'select',
                'is_filterable'=>true,'is_searchable'=>false,'is_comparable'=>true,'is_variant_axis'=>false,
                'is_visible'=>true,'sort_order'=>3,'created_at'=>$now,'updated_at'=>$now,
            ]);
            $length = $this->id('attributes',['slug'=>'length'],[
                'attribute_group_id'=>$dimensions,'unit_id'=>$metre,'name'=>'Length','input_type'=>'number',
                'is_filterable'=>true,'is_searchable'=>false,'is_comparable'=>true,'is_variant_axis'=>false,
                'is_visible'=>true,'sort_order'=>1,'created_at'=>$now,'updated_at'=>$now,
            ]);
            $opts = [];
            foreach ([
                [$color,'Blue','blue','#0000FF'],[$color,'Black','black','#000000'],
                [$size,'Medium','M',null],[$size,'Large','L',null],
                [$material,'Wood','wood',null],[$material,'Cotton','cotton',null],
            ] as [$attributeId,$label,$value,$hex]) {
                $opts[$attributeId.':'.$value] = $this->id('attribute_options',[
                    'attribute_id'=>$attributeId,'value'=>$value
                ],[
                    'label'=>$label,'color_hex'=>$hex,'image'=>null,'sort_order'=>count($opts)+1,
                    'created_at'=>$now,'updated_at'=>$now,
                ]);
            }

            // CATEGORY ATTRIBUTES PIVOT
            foreach ([
                [$sofas,$color,false,true,true,'both',1],[$sofas,$material,true,true,false,'both',2],
                [$sofas,$length,false,true,false,'both',3],[$clothing,$color,true,true,true,'both',1],
                [$clothing,$size,true,true,true,'both',2],[$clothing,$material,false,true,false,'both',3],
                [$electronics,$color,false,true,true,'both',1],
            ] as [$cat,$attr,$required,$filterable,$axis,$applies,$sort]) {
                DB::table('category_attributes')->updateOrInsert(
                    ['category_id'=>$cat,'attribute_id'=>$attr],
                    ['is_required'=>$required,'is_filterable'=>$filterable,'is_variant_axis'=>$axis,
                     'applies_to'=>$applies,'sort_order'=>$sort,'created_at'=>$now,'updated_at'=>$now]
                );
            }

            // PRODUCTS
            $sofa = $this->id('products',['slug'=>'demo-modern-sofa'],[
                'uuid'=>(string)Str::uuid(),'user_id'=>$seller->id,'category_id'=>$sofas,'brand_id'=>$homeBrand,'unit_id'=>$piece,
                'title'=>'Demo Modern Sofa','model_no'=>'SOFA-DEMO-01',
                'short_description'=>'Modern three-seater sofa.','description'=>'Demo sofa for retail and bulk orders.',
                'sale_type'=>'both','pack_qty'=>1,'min_order_qty'=>1,'max_order_qty'=>50,'track_stock'=>true,
                'hsn_code'=>'94016100','tax_rate'=>18,'price_includes_tax'=>true,'offer_text'=>'Bulk enquiries welcome',
                'is_featured'=>true,'is_assured'=>false,'rating_avg'=>5,'rating_count'=>1,'status'=>'active',
                'rejection_reason'=>null,'published_at'=>$now,'meta_title'=>'Demo Modern Sofa',
                'meta_description'=>'Modern sofa demo product.','created_at'=>$now,'updated_at'=>$now,'deleted_at'=>null,
            ]);
            $shirt = $this->id('products',['slug'=>'demo-cotton-t-shirt'],[
                'uuid'=>(string)Str::uuid(),'user_id'=>$seller->id,'category_id'=>$clothing,'brand_id'=>$wearBrand,'unit_id'=>$piece,
                'title'=>'Demo Cotton T-Shirt','model_no'=>'TSHIRT-DEMO-01',
                'short_description'=>'Cotton T-shirt with color and size options.','description'=>'Demo clothing for retail and wholesale.',
                'sale_type'=>'both','pack_qty'=>1,'min_order_qty'=>10,'max_order_qty'=>1000,'track_stock'=>true,
                'hsn_code'=>'61091000','tax_rate'=>5,'price_includes_tax'=>true,'offer_text'=>'Wholesale price from 10 pieces',
                'is_featured'=>true,'is_assured'=>false,'rating_avg'=>0,'rating_count'=>0,'status'=>'active',
                'rejection_reason'=>null,'published_at'=>$now,'meta_title'=>'Demo Cotton T-Shirt',
                'meta_description'=>'Cotton T-shirt demo product.','created_at'=>$now,'updated_at'=>$now,'deleted_at'=>null,
            ]);
            $tv = $this->id('products',['slug'=>'demo-led-tv'],[
                'uuid'=>(string)Str::uuid(),'user_id'=>$seller->id,'category_id'=>$electronics,'brand_id'=>$techBrand,'unit_id'=>$piece,
                'title'=>'Demo 43-inch LED TV','model_no'=>'TV-DEMO-43',
                'short_description'=>'43-inch LED television.','description'=>'Demo electronics listing.',
                'sale_type'=>'retail','pack_qty'=>1,'min_order_qty'=>1,'max_order_qty'=>null,'track_stock'=>true,
                'hsn_code'=>'852872','tax_rate'=>18,'price_includes_tax'=>true,'offer_text'=>null,
                'is_featured'=>false,'is_assured'=>false,'rating_avg'=>0,'rating_count'=>0,'status'=>'active',
                'rejection_reason'=>null,'published_at'=>$now,'meta_title'=>'Demo LED TV',
                'meta_description'=>'LED TV demo product.','created_at'=>$now,'updated_at'=>$now,'deleted_at'=>null,
            ]);

            // PRODUCT VARIANTS
            $sofaV = $this->id('product_variants',['sku'=>'DEMO-SOFA-BLUE'],[
                'product_id'=>$sofa,'barcode'=>'8900000000011','options'=>json_encode(['color'=>'Blue']),
                'mrp'=>32000,'price'=>28000,'cost_price'=>22000,'stock'=>12,'reserved_stock'=>0,'low_stock_alert'=>2,
                'weight'=>45,'length'=>2.1,'width'=>0.9,'height'=>0.85,'is_default'=>true,'is_active'=>true,
                'created_at'=>$now,'updated_at'=>$now,
            ]);
            $shirtBlue = $this->id('product_variants',['sku'=>'DEMO-TSHIRT-BLUE-M'],[
                'product_id'=>$shirt,'barcode'=>'8900000000028','options'=>json_encode(['color'=>'Blue','size'=>'Medium']),
                'mrp'=>599,'price'=>499,'cost_price'=>260,'stock'=>100,'reserved_stock'=>0,'low_stock_alert'=>10,
                'weight'=>0.2,'length'=>null,'width'=>null,'height'=>null,'is_default'=>true,'is_active'=>true,
                'created_at'=>$now,'updated_at'=>$now,
            ]);
            $shirtBlack = $this->id('product_variants',['sku'=>'DEMO-TSHIRT-BLACK-L'],[
                'product_id'=>$shirt,'barcode'=>'8900000000035','options'=>json_encode(['color'=>'Black','size'=>'Large']),
                'mrp'=>599,'price'=>499,'cost_price'=>260,'stock'=>80,'reserved_stock'=>0,'low_stock_alert'=>10,
                'weight'=>0.2,'length'=>null,'width'=>null,'height'=>null,'is_default'=>false,'is_active'=>true,
                'created_at'=>$now,'updated_at'=>$now,
            ]);
            $tvV = $this->id('product_variants',['sku'=>'DEMO-TV-43'],[
                'product_id'=>$tv,'barcode'=>'8900000000042','options'=>json_encode(['size'=>'43 inch']),
                'mrp'=>29999,'price'=>25999,'cost_price'=>22000,'stock'=>8,'reserved_stock'=>0,'low_stock_alert'=>2,
                'weight'=>7.5,'length'=>0.96,'width'=>0.12,'height'=>0.56,'is_default'=>true,'is_active'=>true,
                'created_at'=>$now,'updated_at'=>$now,
            ]);

            // PRODUCT ATTRIBUTE VALUES
            foreach ([
                [$sofa,$material,$opts[$material.':wood'],'Wood',null],
                [$sofa,$length,null,null,2.1],
                [$shirt,$material,$opts[$material.':cotton'],'Cotton',null],
            ] as [$productId,$attrId,$optionId,$textValue,$numberValue]) {
                DB::table('product_attribute_values')->updateOrInsert(
                    ['product_id'=>$productId,'attribute_id'=>$attrId],
                    ['attribute_option_id'=>$optionId,'value_text'=>$textValue,'value_number'=>$numberValue,
                     'value_bool'=>null,'value_date'=>null,'created_at'=>$now,'updated_at'=>$now]
                );
            }

            // VARIANT ATTRIBUTE VALUES
            foreach ([
                [$sofaV,$color,$opts[$color.':blue']],
                [$shirtBlue,$color,$opts[$color.':blue']],[$shirtBlue,$size,$opts[$size.':M']],
                [$shirtBlack,$color,$opts[$color.':black']],[$shirtBlack,$size,$opts[$size.':L']],
            ] as [$variantId,$attrId,$optionId]) {
                DB::table('variant_attribute_values')->updateOrInsert(
                    ['variant_id'=>$variantId,'attribute_id'=>$attrId],
                    ['attribute_option_id'=>$optionId,'created_at'=>$now,'updated_at'=>$now]
                );
            }

            // QUANTITY-BASED PRICES
            foreach ([
                [$sofa,$sofaV,'retail',1,null,28000],[$sofa,$sofaV,'wholesale',5,9,25500],
                [$sofa,$sofaV,'wholesale',10,null,24000],[$shirt,$shirtBlue,'retail',1,9,499],
                [$shirt,$shirtBlue,'wholesale',10,49,399],[$shirt,$shirtBlue,'wholesale',50,null,349],
                [$tv,$tvV,'retail',1,null,25999],
            ] as [$productId,$variantId,$type,$min,$max,$price]) {
                DB::table('product_price_tiers')->updateOrInsert(
                    ['product_id'=>$productId,'variant_id'=>$variantId,'price_type'=>$type,'min_qty'=>$min],
                    ['max_qty'=>$max,'price'=>$price,'created_at'=>$now,'updated_at'=>$now]
                );
            }

            // IMAGES, VIDEO, DOCUMENT (paths are demo placeholders)
            foreach ([
                [$sofa,$sofaV,'demo/products/sofa-front.jpg','Sofa front view',1,true],
                [$sofa,$sofaV,'demo/products/sofa-side.jpg','Sofa side view',2,false],
                [$shirt,$shirtBlue,'demo/products/tshirt-blue.jpg','Blue T-shirt',1,true],
                [$shirt,$shirtBlack,'demo/products/tshirt-black.jpg','Black T-shirt',2,false],
                [$tv,$tvV,'demo/products/led-tv.jpg','43-inch LED TV',1,true],
            ] as [$productId,$variantId,$path,$alt,$sort,$primary]) {
                DB::table('product_images')->updateOrInsert(
                    ['product_id'=>$productId,'path'=>$path],
                    ['variant_id'=>$variantId,'alt'=>$alt,'sort_order'=>$sort,'is_primary'=>$primary,
                     'created_at'=>$now,'updated_at'=>$now]
                );
            }
            DB::table('product_videos')->updateOrInsert(
                ['product_id'=>$sofa,'url'=>'https://www.youtube.com/watch?v=demo-sofa'],
                ['provider'=>'youtube','sort_order'=>1,'created_at'=>$now,'updated_at'=>$now]
            );
            DB::table('product_documents')->updateOrInsert(
                ['product_id'=>$sofa,'title'=>'Sofa product specification'],
                ['path'=>'demo/documents/sofa-specification.pdf','sort_order'=>1,'created_at'=>$now,'updated_at'=>$now]
            );

            // TAGS AND PIVOT
            $best = $this->id('tags',['slug'=>'best-seller'],['name'=>'Best Seller','created_at'=>$now,'updated_at'=>$now]);
            $bulk = $this->id('tags',['slug'=>'wholesale-available'],['name'=>'Wholesale Available','created_at'=>$now,'updated_at'=>$now]);
            $eco = $this->id('tags',['slug'=>'eco-friendly'],['name'=>'Eco Friendly','created_at'=>$now,'updated_at'=>$now]);
            foreach ([[$sofa,$best],[$sofa,$bulk],[$shirt,$bulk],[$shirt,$eco]] as [$productId,$tagId]) {
                DB::table('product_tag')->updateOrInsert(['product_id'=>$productId,'tag_id'=>$tagId],[]);
            }

            // STOCK MOVEMENTS
            foreach ([[$sofaV,12],[$shirtBlue,100]] as [$variantId,$qty]) {
                DB::table('stock_movements')->updateOrInsert(
                    ['variant_id'=>$variantId,'type'=>'initial','note'=>'Demo opening stock'],
                    ['user_id'=>$seller->id,'qty_change'=>$qty,'qty_after'=>$qty,'reference_type'=>null,
                     'reference_id'=>null,'created_at'=>$now,'updated_at'=>$now]
                );
            }

            // REVIEW, WISHLIST, CART
            DB::table('product_reviews')->updateOrInsert(
                ['product_id'=>$sofa,'user_id'=>$buyer->id],
                ['rating'=>5,'title'=>'Comfortable sofa','comment'=>'Demo review for testing.',
                 'is_verified_purchase'=>false,'status'=>'approved','created_at'=>$now,'updated_at'=>$now]
            );
            DB::table('wishlists')->updateOrInsert(
                ['user_id'=>$buyer->id,'product_id'=>$shirt],['created_at'=>$now,'updated_at'=>$now]
            );
            DB::table('cart_items')->updateOrInsert(
                ['user_id'=>$buyer->id,'variant_id'=>$shirtBlue],
                ['product_id'=>$shirt,'qty'=>2,'created_at'=>$now,'updated_at'=>$now]
            );

            // ORDER + ORDER ITEM (no payment gateway)
            $order = $this->id('orders',['order_no'=>'DEMO-ORDER-0001'],[
                'buyer_id'=>$buyer->id,'buyer_name'=>$buyerName,'buyer_phone'=>$buyerPhone,
                'buyer_email'=>$buyerEmail,
                'shipping_address'=>json_encode([
                    'name'=>$buyerName,'phone'=>$buyerPhone,
                    'address_line_1'=>'Demo address - replace this',
                    'city'=>'Kanhangad','state'=>'Kerala','pincode'=>'671315','country'=>'India',
                ]),
                'buyer_note'=>'Demo order only. Seller should contact buyer to confirm.',
                'tax_total'=>0,'total'=>998,'status'=>'placed','created_at'=>$now,'updated_at'=>$now,
            ]);
            DB::table('order_items')->updateOrInsert(
                ['order_id'=>$order,'product_id'=>$shirt,'variant_id'=>$shirtBlue],
                ['seller_id'=>$seller->id,'seller_name'=>$sellerName,'seller_phone'=>$sellerPhone,
                 'title'=>'Demo Cotton T-Shirt','sku'=>'DEMO-TSHIRT-BLUE-M',
                 'options'=>json_encode(['color'=>'Blue','size'=>'Medium']),'unit_name'=>'Piece',
                 'hsn_code'=>'61091000','price_type'=>'retail','qty'=>2,'unit_price'=>499,
                 'tax_rate'=>0,'tax_amount'=>0,'line_total'=>998,'status'=>'placed',
                 'seller_note'=>null,'contacted_at'=>null,'created_at'=>$now,'updated_at'=>$now]
            );
        });

        $this->command?->info('Marketplace demo data seeded successfully.');
        $this->command?->warn('Image/PDF paths and the video URL are demo placeholders. Replace them before production.');
    }

    private function id(string $table, array $key, array $values): int
    {
        DB::table($table)->updateOrInsert($key, $values);
        $id = DB::table($table)->where($key)->value('id');
        if (!$id) throw new RuntimeException("Could not find seeded row in {$table}.");
        return (int) $id;
    }
}
