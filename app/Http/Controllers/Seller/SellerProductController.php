<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Unit;
use App\Models\VariantAttributeValue;
use App\Services\ProductImageService;
use App\Services\SellerProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SellerProductController extends Controller
{
    public function __construct(private SellerProductService $svc, private ProductImageService $images) {}

    private function mine(string $uuid): Product
    {
        return Product::where('uuid', $uuid)->where('user_id', auth()->id())->firstOrFail();
    }

    private function fail(string $field, string $msg): never
    {
        throw ValidationException::withMessages([$field => [$msg]]);
    }

    private function ok(Product $p, string $message)
    {
        $this->svc->syncListing($p);
        return response()->json(['message' => $message, 'product' => $this->svc->payload($p->fresh())]);
    }

    /* ================= list ================= */

    public function index(Request $request)
    {
        $q = Product::where('user_id', auth()->id())
            ->with(['category:id,name', 'unit:id,name', 'variants', 'images', 'priceTiers'])
            ->latest('updated_at');

        $request->query('mode') === 'trading' ? $q->trading() : $q->retail();

        return $q->get()->map(function (Product $p) use ($request) {
            $wholesale = $request->query('mode') === 'trading';
            $price = $wholesale
                ? $p->priceTiers->where('price_type', 'wholesale')->min('price')
                : $p->variants->where('is_active', true)->min('price');
            $img = $p->images->firstWhere('is_primary', true) ?? $p->images->first();

            return [
                'uuid' => $p->uuid, 'title' => $p->title, 'sku' => ($p->variants->firstWhere('is_default', true) ?? $p->variants->first())?->sku,
                'category' => $p->category?->name, 'unit' => $p->unit?->name, 'sale_type' => $p->sale_type,
                'status' => $p->status, 'image' => $img?->url, 'price' => $price !== null ? (float) $price : null,
                'stock' => $p->variants->sum(fn ($v) => (float) $v->stock), 'variants' => $p->variants->count(),
            ];
        })->values();
    }

    public function show(string $uuid)
    {
        return $this->svc->payload($this->mine($uuid));
    }

    /* ================= add / edit ================= */

    public function store(Request $request)
    {
        $p = $this->persist($request, null);
        return $this->ok($p, 'Product created. Now set pricing and stock.');
    }

    public function update(Request $request, string $uuid)
    {
        $p = $this->persist($request, $this->mine($uuid));
        return $this->ok($p, 'Product saved.');
    }

    private function persist(Request $r, ?Product $p): Product
    {
        $data = $r->validate([
            'title'             => 'required|string|max:300',
            'category_id'       => 'required|integer|exists:categories,id',
            'brand_name'        => 'nullable|string|max:120',
            'sale_type'         => 'required|in:retail,wholesale,both',
            'model_no'          => 'nullable|string|max:120',
            'sku'               => 'nullable|string|max:100',
            'short_description' => 'nullable|string|max:500',
            'description'       => 'nullable|string|max:20000',
            'action'            => 'nullable|in:draft,submit',
            'images'            => 'nullable|array|max:8',
            'images.*'          => 'image|max:4096',
            'remove_images'     => 'nullable|array',
            'remove_images.*'   => 'integer',
            'primary_id'        => 'nullable|integer',
            'primary_new'       => 'nullable|integer|min:0',
        ]);

        $cat = Category::active()->find($data['category_id']);
        if (!$cat) $this->fail('category_id', 'Selected category is not available.');
        if (Category::active()->where('parent_id', $cat->id)->exists()) {
            $this->fail('category_id', 'Please choose the most specific category (sub-subcategory).');
        }

        $unitId = $p?->unit_id ?? Unit::where('name', 'Piece')->value('id') ?? Unit::orderBy('id')->value('id');
        if (!$unitId) $this->fail('unit_id', 'No units are configured yet. Please ask the admin to add units.');

        // images: total after save must be <= 8
        $removeIds = collect($data['remove_images'] ?? [])->map(fn ($i) => (int) $i);
        $existing  = $p ? $p->images()->whereNotIn('id', $removeIds)->count() : 0;
        $newFiles  = $r->file('images', []);
        if ($existing + count($newFiles) > 8) $this->fail('images', 'You can keep a maximum of 8 images per product.');

        $brandId = null;
        if (filled($data['brand_name'] ?? null)) {
            $name    = trim($data['brand_name']);
            $brandId = Brand::firstOrCreate(['slug' => Str::slug($name) ?: md5($name)], ['name' => $name])->id;
        }

        $submit = ($data['action'] ?? 'submit') === 'submit';

        return DB::transaction(function () use ($data, $p, $cat, $unitId, $brandId, $submit, $removeIds, $newFiles, $r) {
            $fields = [
                'category_id' => $cat->id, 'brand_id' => $brandId, 'title' => trim($data['title']),
                'sale_type' => $data['sale_type'], 'model_no' => $data['model_no'] ?? null,
                'short_description' => $data['short_description'] ?? null, 'description' => $data['description'] ?? null,
            ];

            if (!$p) {
                $p = Product::create($fields + [
                    'user_id' => auth()->id(), 'unit_id' => $unitId, 'slug' => $this->uniqueSlug($data['title']),
                    'status' => $submit ? 'pending' : 'draft',
                    'min_order_qty' => 1,
                ]);
                $p->variants()->create([
                    'sku' => $this->uniqueSku($data['sku'] ?? null), 'is_default' => true, 'is_active' => true,
                    'stock' => 0, 'options' => null,
                ]);
            } else {
                $p->update($fields);
                // re-submitting a draft / rejected product
                if ($submit && in_array($p->status, ['draft', 'rejected'])) {
                    $p->update(['status' => 'pending', 'rejection_reason' => null]);
                } elseif (!$submit && in_array($p->status, ['pending', 'rejected'])) {
                    $p->update(['status' => 'draft']);
                }
                if (filled($data['sku'] ?? null)) {
                    $v = $p->variants()->where('is_default', true)->first() ?? $p->variants()->first();
                    if ($v && $v->sku !== trim($data['sku'])) {
                        if (ProductVariant::where('sku', trim($data['sku']))->where('id', '!=', $v->id)->exists()) {
                            $this->fail('sku', 'This product code is already in use.');
                        }
                        $v->update(['sku' => trim($data['sku'])]);
                    }
                }
            }

            // images
            foreach ($p->images()->whereIn('id', $removeIds)->get() as $img) $this->images->delete($img);

            $sort = (int) $p->images()->max('sort_order');
            $created = [];
            foreach ($newFiles as $file) $created[] = $this->images->store($p, $file, ++$sort);

            $primary = null;
            if (!empty($data['primary_id'])) $primary = $p->images()->find($data['primary_id']);
            if (!$primary && isset($data['primary_new'])) $primary = $created[(int) $data['primary_new']] ?? null;
            if (!$primary) $primary = $p->images()->where('is_primary', true)->first() ?? $p->images()->orderBy('sort_order')->first();
            if ($primary) {
                $p->images()->update(['is_primary' => false]);
                $primary->update(['is_primary' => true]);
            }

            return $p;
        });
    }

    private function uniqueSlug(string $title): string
    {
        $slug = Str::slug($title) ?: 'product';
        while (Product::withTrashed()->where('slug', $slug)->exists()) {
            $slug = Str::slug($title) . '-' . Str::lower(Str::random(5));
        }
        return $slug;
    }

    private function uniqueSku(?string $wanted): string
    {
        $wanted = trim((string) $wanted);
        if ($wanted !== '') {
            if (ProductVariant::where('sku', $wanted)->exists()) $this->fail('sku', 'This product code is already in use.');
            return $wanted;
        }
        do { $sku = 'SKU-' . strtoupper(Str::random(8)); } while (ProductVariant::where('sku', $sku)->exists());
        return $sku;
    }

    public function destroy(string $uuid)
    {
        $this->mine($uuid)->delete();   // soft delete: past orders keep their snapshot
        return response()->json(['message' => 'Product deleted.']);
    }

    /* ================= status workflow ================= */

    public function status(Request $request, string $uuid)
    {
        $p = $this->mine($uuid);
        $action = $request->validate(['action' => 'required|in:submit,draft,deactivate,activate'])['action'];

        switch ($action) {
            case 'submit':
                if (!in_array($p->status, ['draft', 'rejected'])) $this->fail('status', 'Only draft or rejected products can be submitted.');
                if ($missing = $this->svc->missing($p)) {
                    throw ValidationException::withMessages(['status' => array_merge(['Complete these before submitting:'], $missing)]);
                }
                $p->update(['status' => 'pending', 'rejection_reason' => null]);
                $msg = 'Submitted for review.';
                break;
            case 'draft':
                if ($p->status !== 'pending') $this->fail('status', 'Only pending products can go back to draft.');
                $p->update(['status' => 'draft']);
                $msg = 'Moved back to draft.';
                break;
            case 'deactivate':
                if ($p->status !== 'active') $this->fail('status', 'Only live products can be deactivated.');
                $p->update(['status' => 'inactive']);
                $msg = 'Product hidden from the store.';
                break;
            default: // activate
                if ($p->status !== 'inactive') $this->fail('status', 'Only deactivated products can be re-activated.');
                $p->update(['status' => $p->published_at ? 'active' : 'pending']);
                $msg = $p->published_at ? 'Product is live again.' : 'Sent for review.';
        }
        return $this->ok($p, $msg);
    }

    /* ================= details ================= */

    public function details(Request $request, string $uuid)
    {
        $p = $this->mine($uuid);
        $rows = $request->validate([
            'rows'               => 'nullable|array|max:60',
            'rows.*.attribute_id' => 'nullable|integer',
            'rows.*.name'        => 'nullable|string|max:100',
            'rows.*.value'       => 'nullable',
        ])['rows'] ?? [];

        $this->svc->saveDetails($p, $rows);
        return $this->ok($p, 'Product details saved.');
    }

    /* ================= variants ================= */

    public function variants(Request $request, string $uuid)
    {
        $p = $this->mine($uuid);
        $in = $request->validate([
            'variants'               => 'required|array|min:1|max:100',
            'variants.*.id'          => 'nullable|integer',
            'variants.*.sku'         => 'required|string|max:100',
            'variants.*.is_active'   => 'nullable|boolean',
            'variants.*.options'     => 'nullable|array',
        ])['variants'];

        $axes  = $this->svc->attributesFor($p->category, $p->sale_type)->where('is_variant_axis', true)->keyBy('id');
        $multi = count($in) > 1;
        $seenSku = []; $seenCombo = [];

        foreach ($in as $i => $row) {
            $sku = trim($row['sku']);
            if (isset($seenSku[$sku])) $this->fail('variants', "Duplicate code '$sku'.");
            $seenSku[$sku] = true;
            if (ProductVariant::where('sku', $sku)->where('id', '!=', $row['id'] ?? 0)->exists()) {
                $this->fail('variants', "Code '$sku' is already used by another product.");
            }
            $combo = [];
            foreach ($axes as $axId => $ax) {
                $opt = (int) ($row['options'][$axId] ?? 0);
                if ($opt && !collect($ax['options'])->contains('id', $opt)) $this->fail('variants', "Invalid {$ax['name']} choice.");
                if ($multi && !$opt) $this->fail('variants', "Choose {$ax['name']} for every variant.");
                $combo[$axId] = $opt;
            }
            if ($multi && $axes->isNotEmpty()) {
                $key = json_encode($combo);
                if (isset($seenCombo[$key])) $this->fail('variants', 'Two variants have the same option combination.');
                $seenCombo[$key] = true;
            }
        }

        DB::transaction(function () use ($p, $in, $axes) {
            $keep = [];
            $base = $p->variants()->where('is_default', true)->first() ?? $p->variants()->first();

            foreach ($in as $row) {
                $variant = !empty($row['id']) ? $p->variants()->find($row['id']) : null;

                $labels = [];
                foreach ($axes as $axId => $ax) {
                    $opt = collect($ax['options'])->firstWhere('id', (int) ($row['options'][$axId] ?? 0));
                    if ($opt) $labels[$ax['slug']] = $opt['label'];
                }

                $attrs = ['sku' => trim($row['sku']), 'is_active' => (bool) ($row['is_active'] ?? true), 'options' => $labels ?: null];
                if ($variant) {
                    $variant->update($attrs);
                } else {
                    $variant = $p->variants()->create($attrs + [
                        'is_default' => false, 'stock' => 0,
                        'mrp' => $base?->mrp, 'price' => $base?->price,   // new variant starts with the base price
                    ]);
                }
                $keep[] = $variant->id;

                $variant->attributeValues()->delete();
                foreach ($axes as $axId => $ax) {
                    $opt = (int) ($row['options'][$axId] ?? 0);
                    if ($opt) VariantAttributeValue::create(['variant_id' => $variant->id, 'attribute_id' => $axId, 'attribute_option_id' => $opt]);
                }
            }

            $p->variants()->whereNotIn('id', $keep)->delete();

            // exactly one default
            if (!$p->variants()->where('is_default', true)->exists()) {
                $p->variants()->orderBy('id')->first()?->update(['is_default' => true]);
            }
        });

        return $this->ok($p, 'Variants saved.');
    }

    /* ================= pricing (retail) ================= */

    public function pricing(Request $request, string $uuid)
    {
        $p = $this->mine($uuid);
        $in = $request->validate([
            'unit_id'            => 'required|integer|exists:units,id',
            'hsn_code'           => 'nullable|string|max:20',
            'tax_rate'           => 'nullable|numeric|min:0|max:100',
            'price_includes_tax' => 'nullable|boolean',
            'variants'           => 'required|array|min:1',
            'variants.*.id'      => 'required|integer',
            'variants.*.mrp'     => 'required|numeric|min:0',
            'variants.*.price'   => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($p, $in) {
            foreach ($in['variants'] as $row) {
                $v = $p->variants()->find($row['id']);
                if (!$v) continue;
                $price = $row['price'] ?? null;
                $price = ($price === null || $price === '') ? $row['mrp'] : $price;
                if ((float) $price > (float) $row['mrp']) $this->fail('variants', "Current price cannot exceed MRP ({$v->sku}).");
                $v->update(['mrp' => $row['mrp'], 'price' => $price]);
            }
            $p->update([
                'unit_id' => $in['unit_id'], 'hsn_code' => $in['hsn_code'] ?? null,
                'tax_rate' => $in['tax_rate'] ?? 0, 'price_includes_tax' => (bool) ($in['price_includes_tax'] ?? true),
            ]);
        });

        return $this->ok($p, 'Pricing saved.');
    }

    /* ================= bulk rule (marketplace -> wholesale) ================= */

    public function bulkRule(Request $request, string $uuid)
    {
        $p = $this->mine($uuid);
        $in = $request->validate([
            'min_order_qty' => 'required|numeric|min:1',
            'bulk_price'    => 'nullable|numeric|min:0.0001',
            'offer_text'    => 'nullable|string|max:255',
        ]);
        $this->assertQty($p, (float) $in['min_order_qty'], 'min_order_qty');

        DB::transaction(function () use ($p, $in) {
            $moq = (float) $in['min_order_qty'];
            $p->update([
                'min_order_qty' => $moq, 'offer_text' => $in['offer_text'] ?? null,
                'sale_type' => $p->sale_type === 'retail' ? 'both' : $p->sale_type,
            ]);

            if (!empty($in['bulk_price'])) {
                $p->priceTiers()->where('price_type', 'wholesale')->whereNull('variant_id')->where('min_qty', '<', $moq)->delete();
                $p->priceTiers()->updateOrCreate(
                    ['price_type' => 'wholesale', 'variant_id' => null, 'min_qty' => $moq],
                    ['price' => $in['bulk_price']]
                );
            }
        });

        return $this->ok($p, 'Bulk rule saved.');
    }

    public function disableBulk(string $uuid)
    {
        $p = $this->mine($uuid);
        if ($p->sale_type === 'wholesale') $this->fail('sale_type', 'This is a wholesale-only product. Change its sale type first.');

        DB::transaction(function () use ($p) {
            $p->priceTiers()->where('price_type', 'wholesale')->delete();
            $p->update(['sale_type' => 'retail', 'min_order_qty' => 1, 'offer_text' => null]);
        });
        return $this->ok($p, 'Bulk selling turned off.');
    }

    /* ================= price tiers (trading) ================= */

    public function tiers(Request $request, string $uuid)
    {
        $p = $this->mine($uuid);
        $in = $request->validate([
            'unit_id'          => 'required|integer|exists:units,id',
            'tiers'            => 'required|array|min:1|max:15',
            'tiers.*.min_qty'  => 'required|numeric|min:0.001',
            'tiers.*.max_qty'  => 'nullable|numeric|min:0.001',
            'tiers.*.price'    => 'required|numeric|min:0.0001',
        ]);

        $tiers = collect($in['tiers'])->map(fn ($t) => [
            'min_qty' => (float) $t['min_qty'],
            'max_qty' => ($t['max_qty'] ?? '') === '' || $t['max_qty'] === null ? null : (float) $t['max_qty'],
            'price'   => (float) $t['price'],
        ])->sortBy('min_qty')->values();

        foreach ($tiers as $i => $t) {
            if ($t['max_qty'] !== null && $t['max_qty'] < $t['min_qty']) $this->fail('tiers', 'Tier ' . ($i + 1) . ': "To" must be greater than "From".');
            $next = $tiers[$i + 1] ?? null;
            if ($next) {
                if ($next['min_qty'] == $t['min_qty']) $this->fail('tiers', 'Two tiers start at the same quantity.');
                if ($t['max_qty'] === null || $t['max_qty'] >= $next['min_qty']) $this->fail('tiers', 'Tier ' . ($i + 1) . ' overlaps the next tier.');
            }
        }
        $this->assertQty($p, $tiers->first()['min_qty'], 'tiers');

        DB::transaction(function () use ($p, $in, $tiers) {
            $p->priceTiers()->where('price_type', 'wholesale')->whereNull('variant_id')->delete();
            foreach ($tiers as $t) {
                $p->priceTiers()->create($t + ['price_type' => 'wholesale', 'variant_id' => null]);
            }
            $p->update([
                'unit_id' => $in['unit_id'],
                'min_order_qty' => $tiers->first()['min_qty'],          // MOQ = first tier start
                'sale_type' => $p->sale_type === 'retail' ? 'both' : $p->sale_type,
            ]);
        });

        return $this->ok($p, 'Price tiers saved.');
    }

    private function assertQty(Product $p, float $qty, string $field): void
    {
        if (!$p->unit?->allow_decimal && floor($qty) != $qty) {
            $this->fail($field, "Unit '{$p->unit?->name}' does not allow fractional quantities.");
        }
    }

    /* ================= stock ================= */

    public function stock(Request $request, string $uuid)
    {
        $p = $this->mine($uuid);
        $in = $request->validate([
            'track_stock'               => 'nullable|boolean',
            'variants'                  => 'required|array|min:1',
            'variants.*.id'             => 'required|integer',
            'variants.*.stock'          => 'required|numeric|min:0',
            'variants.*.low_stock_alert' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($p, $in, $request) {
            $p->update(['track_stock' => (bool) ($in['track_stock'] ?? true)]);

            foreach ($in['variants'] as $row) {
                $v = $p->variants()->find($row['id']);
                if (!$v) continue;
                $this->assertQty($p, (float) $row['stock'], 'variants');
                if ((float) $row['stock'] < (float) $v->reserved_stock) {
                    $this->fail('variants', "{$v->sku}: stock cannot be lower than reserved quantity ({$v->reserved_stock}).");
                }
                $this->svc->adjustStock($v, (float) $row['stock'], $request->user()->id);
                $v->update(['low_stock_alert' => ($row['low_stock_alert'] ?? '') === '' ? null : $row['low_stock_alert']]);
            }
        });

        return $this->ok($p, 'Stock updated.');
    }
}
