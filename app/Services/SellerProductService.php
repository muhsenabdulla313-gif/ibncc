<?php

namespace App\Services;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\StockMovement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SellerProductService
{
    /* ------------------------------------------------------------------
     |  Category attributes (inherits from parent categories, deeper wins)
     * ----------------------------------------------------------------*/
    public function attributesFor(Category $category, ?string $saleType = null): Collection
    {
        $chain = [];
        for ($n = $category; $n; $n = $n->parent) {
            array_unshift($chain, $n->id);
        }

        $rows   = DB::table('category_attributes')->whereIn('category_id', $chain)->get();
        $merged = [];
        foreach ($chain as $cid) {
            foreach ($rows->where('category_id', $cid) as $r) {
                $merged[$r->attribute_id] = $r;
            }
        }

        $attrs = Attribute::with(['options', 'unit'])->whereIn('id', array_keys($merged))->get()->keyBy('id');

        return collect($merged)
            ->filter(function ($r) use ($saleType) {
                return !$saleType || $saleType === 'both' || $r->applies_to === 'both' || $r->applies_to === $saleType;
            })
            ->map(function ($r) use ($attrs) {
                $a = $attrs->get($r->attribute_id);
                if (!$a) return null;
                return [
                    'id'              => $a->id,
                    'name'            => $a->name,
                    'slug'            => $a->slug,
                    'input_type'      => $a->input_type,
                    'unit'            => $a->unit?->short_name,
                    'is_required'     => (bool) $r->is_required,
                    'is_variant_axis' => (bool) ($r->is_variant_axis ?? $a->is_variant_axis),
                    'sort'            => ((int) $r->sort_order) * 1000 + (int) $a->sort_order,
                    'options'         => $a->options->map(fn ($o) => ['id' => $o->id, 'label' => $o->label, 'color_hex' => $o->color_hex])->values()->all(),
                ];
            })
            ->filter()->sortBy('sort')->values();
    }

    /* ------------------------------------------------------------------
     |  Full JSON payload for the seller page (every panel reads from this)
     * ----------------------------------------------------------------*/
    public function payload(Product $p): array
    {
        $p->load(['category.parent.parent', 'brand', 'unit', 'images', 'variants.attributeValues', 'priceTiers',
                  'attributeValues.attribute', 'attributeValues.option']);

        $defs = $this->attributesFor($p->category, $p->sale_type);

        $path = [];
        for ($n = $p->category; $n; $n = $n->parent) {
            array_unshift($path, ['id' => $n->id, 'name' => $n->name]);
        }

        $default = $p->variants->firstWhere('is_default', true) ?? $p->variants->first();

        $movements = StockMovement::whereIn('variant_id', $p->variants->pluck('id'))
            ->with('variant:id,sku')->latest()->limit(10)->get()
            ->map(fn ($m) => [
                'sku' => $m->variant?->sku, 'type' => $m->type, 'change' => (float) $m->qty_change,
                'after' => (float) $m->qty_after, 'note' => $m->note, 'at' => $m->created_at?->format('d M Y, H:i'),
            ]);

        return [
            'uuid' => $p->uuid, 'id' => $p->id,
            'title' => $p->title, 'slug' => $p->slug,
            'category_id' => $p->category_id, 'category_path' => $path,
            'brand_name' => $p->brand?->name,
            'unit_id' => $p->unit_id, 'unit_name' => $p->unit?->name, 'unit_allow_decimal' => (bool) $p->unit?->allow_decimal,
            'sale_type' => $p->sale_type, 'status' => $p->status, 'rejection_reason' => $p->rejection_reason,
            'model_no' => $p->model_no, 'sku' => $default?->sku,
            'short_description' => $p->short_description, 'description' => $p->description,
            'offer_text' => $p->offer_text,
            'min_order_qty' => (float) $p->min_order_qty, 'max_order_qty' => $p->max_order_qty !== null ? (float) $p->max_order_qty : null,
            'track_stock' => (bool) $p->track_stock,
            'hsn_code' => $p->hsn_code, 'tax_rate' => (float) $p->tax_rate, 'price_includes_tax' => (bool) $p->price_includes_tax,
            'missing' => $this->missing($p),
            'images' => $p->images->map(fn ($i) => ['id' => $i->id, 'url' => $i->url, 'is_primary' => (bool) $i->is_primary, 'option_id' => $i->attribute_option_id])->values(),
            'image_axis' => $this->imageAxis($p),
            'axes' => $defs->where('is_variant_axis', true)->values(),
            'details' => $this->detailRows($p, $defs->where('is_variant_axis', false)),
            'variants' => $p->variants->sortByDesc('is_default')->values()->map(fn ($v) => [
                'id' => $v->id, 'sku' => $v->sku, 'is_default' => (bool) $v->is_default, 'is_active' => (bool) $v->is_active,
                'options' => (object) $v->attributeValues->pluck('attribute_option_id', 'attribute_id')->all(),
                'label' => $v->options ? implode(' / ', array_values($v->options)) : 'Default',
                'mrp' => $v->mrp !== null ? (float) $v->mrp : null,
                'price' => $v->price !== null ? (float) $v->price : null,
                'stock' => (float) $v->stock, 'reserved' => (float) $v->reserved_stock,
                'low_stock_alert' => $v->low_stock_alert !== null ? (float) $v->low_stock_alert : null,
            ]),
            'tiers' => $p->priceTiers->where('price_type', 'wholesale')->whereNull('variant_id')->values()->map(fn ($t) => [
                'min_qty' => (float) $t->min_qty, 'max_qty' => $t->max_qty !== null ? (float) $t->max_qty : null, 'price' => (float) $t->price,
            ]),
            'movements' => $movements,
        ];
    }

    /** the variant option (colour first, else the first axis) that images can be assigned to, limited to values the product really uses */
    public function imageAxis(Product $p): ?array
    {
        $p->loadMissing(['variants.attributeValues', 'category.parent.parent']);
        $axes = $this->attributesFor($p->category, $p->sale_type)->where('is_variant_axis', true)->values();
        if ($axes->isEmpty()) return null;

        $axis = $axes->firstWhere('input_type', 'color') ?? $axes->first();
        $used = $p->variants->flatMap(fn ($v) => $v->attributeValues->where('attribute_id', $axis['id'])->pluck('attribute_option_id'))->unique()->all();
        $opts = collect($axis['options'])->filter(fn ($o) => in_array($o['id'], $used))->values();

        return $opts->isEmpty() ? null : ['id' => $axis['id'], 'name' => $axis['name'], 'options' => $opts->all()];
    }

    private function detailRows(Product $p, Collection $defs): array
    {
        $vals = $p->attributeValues->groupBy('attribute_id');
        $rows = [];

        foreach ($defs as $d) {
            $rows[] = $d + ['custom' => false, 'value' => $this->valueFor($d['input_type'], $vals->get($d['id'], collect()))];
        }
        foreach ($vals as $attrId => $group) {
            if ($defs->contains('id', $attrId)) continue;
            $a = $group->first()->attribute;
            if (!$a) continue;
            $rows[] = ['id' => null, 'name' => $a->name, 'input_type' => 'text', 'options' => [], 'is_required' => false,
                       'custom' => true, 'value' => (string) $group->map->display_value->filter()->implode(', ')];
        }
        return $rows;
    }

    private function valueFor(string $type, Collection $g)
    {
        if ($type === 'multiselect') return $g->pluck('attribute_option_id')->filter()->values()->all();
        $v = $g->first();
        if (!$v) return '';
        return match ($type) {
            'select', 'color' => (string) ($v->attribute_option_id ?? ''),
            'boolean'         => is_null($v->value_bool) ? '' : ($v->value_bool ? '1' : '0'),
            'number'          => is_null($v->value_number) ? '' : rtrim(rtrim((string) $v->value_number, '0'), '.'),
            'date'            => $v->value_date?->format('Y-m-d') ?? '',
            default           => (string) ($v->value_text ?? ''),
        };
    }

    /* ------------------------------------------------------------------
     |  Save "Product details" rows into product_attribute_values
     * ----------------------------------------------------------------*/
    public function saveDetails(Product $p, array $rows): void
    {
        $defs   = $this->attributesFor($p->category, $p->sale_type)->where('is_variant_axis', false)->keyBy('id');
        $errors = [];

        DB::transaction(function () use ($p, $rows, $defs, &$errors) {
            $p->attributeValues()->delete();
            $seen = [];

            foreach ($rows as $row) {
                $attrId = $row['attribute_id'] ?? null;
                $value  = $row['value'] ?? null;

                if ($attrId && $defs->has((int) $attrId)) {
                    $d = $defs[(int) $attrId];
                    $empty = is_array($value) ? count(array_filter($value, 'strlen')) === 0 : trim((string) $value) === '';
                    if ($empty) {
                        if ($d['is_required']) $errors[] = $d['name'] . ' is required.';
                        continue;
                    }
                    $seen[$d['id']] = true;
                    $this->storeTyped($p, $d, $value, $errors);
                    continue;
                }

                // free-text property typed by the seller
                $name = trim((string) ($row['name'] ?? ''));
                $text = is_array($value) ? '' : trim((string) $value);
                if ($name === '' || $text === '') continue;

                $slug = Str::slug($name) ?: 'prop-' . substr(md5($name), 0, 8);
                $attr = Attribute::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => Str::limit($name, 100, ''), 'input_type' => 'text', 'is_visible' => true]
                );
                if (isset($seen[$attr->id]) || ($defs->has($attr->id))) continue;
                $seen[$attr->id] = true;
                ProductAttributeValue::create(['product_id' => $p->id, 'attribute_id' => $attr->id, 'value_text' => Str::limit($text, 500, '')]);
            }

            foreach ($defs as $d) {
                if ($d['is_required'] && !isset($seen[$d['id']]) && !in_array($d['name'] . ' is required.', $errors)) {
                    $errors[] = $d['name'] . ' is required.';
                }
            }
            if ($errors) {
                throw ValidationException::withMessages(['details' => array_values(array_unique($errors))]);
            }
        });
    }

    private function storeTyped(Product $p, array $d, $value, array &$errors): void
    {
        $base = ['product_id' => $p->id, 'attribute_id' => $d['id']];
        $optIds = collect($d['options'])->pluck('id')->all();

        switch ($d['input_type']) {
            case 'select': case 'color':
                if (!in_array((int) $value, $optIds)) { $errors[] = $d['name'] . ': invalid choice.'; return; }
                ProductAttributeValue::create($base + ['attribute_option_id' => (int) $value]);
                break;
            case 'multiselect':
                foreach ((array) $value as $v) {
                    if (!in_array((int) $v, $optIds)) { $errors[] = $d['name'] . ': invalid choice.'; return; }
                    ProductAttributeValue::create($base + ['attribute_option_id' => (int) $v]);
                }
                break;
            case 'number':
                if (!is_numeric($value)) { $errors[] = $d['name'] . ' must be a number.'; return; }
                ProductAttributeValue::create($base + ['value_number' => $value]);
                break;
            case 'boolean':
                ProductAttributeValue::create($base + ['value_bool' => (string) $value === '1']);
                break;
            case 'date':
                if (!strtotime((string) $value)) { $errors[] = $d['name'] . ': invalid date.'; return; }
                ProductAttributeValue::create($base + ['value_date' => date('Y-m-d', strtotime($value))]);
                break;
            default:
                ProductAttributeValue::create($base + ['value_text' => Str::limit(trim((string) $value), 500, '')]);
        }
    }

    /* ------------------------------------------------------------------
     |  What is still missing before the product can be submitted
     * ----------------------------------------------------------------*/
    public function missing(Product $p): array
    {
        $p->loadMissing(['images', 'variants', 'priceTiers', 'attributeValues', 'category.parent.parent']);
        $m = [];

        if ($p->images->isEmpty()) $m[] = 'Add at least one product image';

        if (in_array($p->sale_type, ['retail', 'both'])
            && $p->variants->where('is_active', true)->filter(fn ($v) => (float) $v->price > 0)->isEmpty()) {
            $m[] = 'Set a retail price (Pricing)';
        }
        if (in_array($p->sale_type, ['wholesale', 'both'])
            && $p->priceTiers->where('price_type', 'wholesale')->isEmpty()) {
            $m[] = 'Add wholesale pricing (Price tiers / Bulk rule)';
        }
        if ($p->track_stock && $p->variants->where('is_active', true)->sum(fn ($v) => (float) $v->stock) <= 0) {
            $m[] = 'Add stock (Stock)';
        }
        if ($p->category) {
            $filled = $p->attributeValues->pluck('attribute_id')->all();
            foreach ($this->attributesFor($p->category, $p->sale_type) as $d) {
                if ($d['is_required'] && !$d['is_variant_axis'] && !in_array($d['id'], $filled)) {
                    $m[] = 'Fill required detail: ' . $d['name'];
                }
            }
        }
        return $m;
    }

    /* ------------------------------------------------------------------
     |  Stock adjustment with audit trail
     * ----------------------------------------------------------------*/
    public function adjustStock($variant, float $newQty, ?int $userId, string $note = 'Updated by seller'): void
    {
        $old = (float) $variant->stock;
        if (abs($old - $newQty) < 0.0005) return;

        $variant->update(['stock' => $newQty]);
        StockMovement::create([
            'variant_id' => $variant->id, 'user_id' => $userId, 'type' => 'adjustment',
            'qty_change' => $newQty - $old, 'qty_after' => $newQty, 'note' => $note,
        ]);
    }

    /* ------------------------------------------------------------------
     |  Keep the denormalised listing columns in sync (only if the
     |  000012 listing migration has been run - safe to call either way)
     * ----------------------------------------------------------------*/
    public function syncListing(Product $p): void
    {
        if (!Schema::hasColumn('products', 'min_price')) return;

        $p->load(['variants', 'priceTiers']);
        $prices = collect();
        if (in_array($p->sale_type, ['retail', 'both'])) {
            $prices = $prices->merge($p->variants->where('is_active', true)->pluck('price')->filter());
        }
        if (in_array($p->sale_type, ['wholesale', 'both'])) {
            $prices = $prices->merge($p->priceTiers->where('price_type', 'wholesale')->pluck('price'));
        }
        $discount = $p->variants->where('is_active', true)->map(fn ($v) => $v->discount_percent)->max() ?? 0;
        $inStock  = !$p->track_stock || $p->variants->where('is_active', true)->sum(fn ($v) => $v->available_stock) > 0;

        $data = ['min_price' => $prices->min(), 'max_price' => $prices->max(), 'in_stock' => $inStock];
        if (Schema::hasColumn('products', 'discount_label')) {
            $data['discount_label'] = $discount > 0 ? $discount . '% off' : null;
        }
        DB::table('products')->where('id', $p->id)->update($data);
    }
}
