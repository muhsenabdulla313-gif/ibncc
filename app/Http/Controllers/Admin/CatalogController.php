<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\AttributeOption;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * ONE controller for the whole catalog master data (single page + one modal):
 * categories (+ attribute assignment), attribute groups, attributes (+ options), units, brands.
 */
class CatalogController extends Controller
{
    private const MODELS = [
        'categories'       => Category::class,
        'attribute-groups' => AttributeGroup::class,
        'attributes'       => Attribute::class,
        'units'            => Unit::class,
        'brands'           => Brand::class,
    ];
    private const MAX_DEPTH = 2;   // 0 = category, 1 = group, 2 = item (mega-menu)

    public function index()
    {
        return view('admin.catalog.index');
    }

    private function model(string $entity): string
    {
        return self::MODELS[$entity] ?? abort(404);
    }

    private function fail(string $field, string $msg): never
    {
        throw ValidationException::withMessages([$field => [$msg]]);
    }

    /* ================= lookups used by dropdowns in the modal ================= */

    public function lookups()
    {
        return [
            'categories' => $this->categoryTree()->map(fn ($c) => ['id' => $c['id'], 'name' => $c['name'], 'depth' => $c['depth']])->values(),
            'attributes' => Attribute::orderBy('name')->get(['id', 'name', 'input_type']),
            'groups'     => AttributeGroup::orderBy('sort_order')->orderBy('name')->get(['id', 'name']),
            'units'      => Unit::orderBy('name')->get(['id', 'name']),
        ];
    }

    private function categoryTree()
    {
        $all = Category::orderBy('sort_order')->orderBy('name')->get()->groupBy(fn ($c) => $c->parent_id ?? 0);
        $out = collect();
        $walk = function ($pid, $depth) use (&$walk, $all, $out) {
            foreach ($all->get($pid, collect()) as $c) {
                $out->push(['id' => $c->id, 'name' => $c->name, 'slug' => $c->slug, 'depth' => $depth,
                            'parent_id' => $c->parent_id, 'is_active' => $c->is_active, 'sort_order' => $c->sort_order]);
                $walk($c->id, $depth + 1);
            }
        };
        $walk(0, 0);
        return $out;
    }

    /* ================= list ================= */

    public function list(string $entity)
    {
        $this->model($entity);

        switch ($entity) {
            case 'categories':
                $prod  = Product::withTrashed()->select('category_id', DB::raw('count(*) c'))->groupBy('category_id')->pluck('c', 'category_id');
                $attrs = DB::table('category_attributes')->select('category_id', DB::raw('count(*) c'))->groupBy('category_id')->pluck('c', 'category_id');
                $names = Category::pluck('name', 'id');
                return $this->categoryTree()->map(fn ($c) => $c + [
                    'parent' => $names[$c['parent_id']] ?? null,
                    'products_count' => $prod[$c['id']] ?? 0, 'attrs_count' => $attrs[$c['id']] ?? 0,
                ])->values();

            case 'attribute-groups':
                return AttributeGroup::withCount('attributes')->orderBy('sort_order')->orderBy('name')->get();

            case 'attributes':
                return Attribute::with(['group:id,name', 'unit:id,short_name'])->withCount(['options', 'categories'])
                    ->orderBy('name')->get()->map(fn ($a) => $a->toArray() + ['group_name' => $a->group?->name, 'unit_name' => $a->unit?->short_name]);

            case 'units':
                return Unit::with('baseUnit:id,name')->orderBy('type')->orderBy('name')->get()
                    ->map(fn ($u) => $u->toArray() + ['base_name' => $u->baseUnit?->name]);

            default: // brands
                return Brand::withCount('products')->orderBy('name')->get()->map(fn ($b) => $b->toArray() + ['logo_url' => $b->logo ? asset($b->logo) : null]);
        }
    }

    public function show(string $entity, int $id)
    {
        $row = $this->model($entity)::findOrFail($id);
        $data = $row->toArray();

        if ($entity === 'categories') {
            $data['image_url'] = $row->image ? asset($row->image) : null;
            $data['attrs'] = DB::table('category_attributes')->where('category_id', $id)->orderBy('sort_order')->get()
                ->map(fn ($r) => [
                    'attribute_id' => $r->attribute_id, 'is_required' => (bool) $r->is_required,
                    'is_filterable' => is_null($r->is_filterable) ? '' : (int) $r->is_filterable,
                    'is_variant_axis' => is_null($r->is_variant_axis) ? '' : (int) $r->is_variant_axis,
                    'applies_to' => $r->applies_to, 'sort_order' => $r->sort_order,
                ]);
        }
        if ($entity === 'attributes') {
            $data['options'] = $row->options()->get(['id', 'label', 'value', 'color_hex', 'sort_order']);
        }
        if ($entity === 'brands') {
            $data['logo_url'] = $row->logo ? asset($row->logo) : null;
        }
        return $data;
    }

    /* ================= create / update / delete ================= */

    public function store(Request $request, string $entity)
    {
        return $this->guard(fn () => $this->save($request, $entity, null));
    }

    public function update(Request $request, string $entity, int $id)
    {
        return $this->guard(fn () => $this->save($request, $entity, $this->model($entity)::findOrFail($id)));
    }

    public function destroy(string $entity, int $id)
    {
        $row = $this->model($entity)::findOrFail($id);

        return $this->guard(function () use ($entity, $row) {
            if ($entity === 'categories') {
                if (Category::where('parent_id', $row->id)->exists()) $this->fail('delete', 'Delete or move its sub-categories first.');
                if (Product::withTrashed()->where('category_id', $row->id)->exists()) $this->fail('delete', 'Products are listed in this category. Move them first, or mark the category inactive instead.');
            }
            if ($entity === 'attributes') {
                $used = DB::table('product_attribute_values')->where('attribute_id', $row->id)->distinct()->count('product_id')
                      + DB::table('variant_attribute_values')->where('attribute_id', $row->id)->count();
                if ($used) $this->fail('delete', "This attribute is used by existing products ($used values). Remove it from the categories instead.");
            }
            if ($entity === 'units' && Unit::where('base_unit_id', $row->id)->exists()) {
                $this->fail('delete', 'Other units are based on this unit.');
            }
            $img = $row->image ?? $row->logo ?? null;
            $row->delete();
            $this->dropFile($img);
            return response()->json(['message' => 'Deleted.']);
        });
    }

    /** turns FK violations into a readable 422 */
    private function guard(\Closure $fn)
    {
        try {
            return $fn();
        } catch (QueryException $e) {
            if (in_array($e->getCode(), ['23000', 23000])) {
                return response()->json(['message' => 'This record is in use elsewhere and cannot be changed/deleted.', 'errors' => ['db' => ['This record is in use elsewhere.']]], 422);
            }
            throw $e;
        }
    }

    private function save(Request $r, string $entity, $row)
    {
        $res = DB::transaction(fn () => match ($entity) {
            'categories'       => $this->saveCategory($r, $row),
            'attribute-groups' => $this->saveGroup($r, $row),
            'attributes'       => $this->saveAttribute($r, $row),
            'units'            => $this->saveUnit($r, $row),
            'brands'           => $this->saveBrand($r, $row),
        });
        return response()->json(['message' => $row ? 'Updated.' : 'Created.', 'id' => $res->id]);
    }

    private function slugFor(?string $slug, string $name): string
    {
        return Str::slug(filled($slug) ? $slug : $name) ?: Str::lower(Str::random(6));
    }

    /* ---------- images ---------- */
    private function pickImage(Request $r, string $field, ?string $old, string $dir, int $size): ?string
    {
        if ($r->boolean('remove_image') && !$r->hasFile($field)) { $this->dropFile($old); return null; }
        if (!$r->hasFile($field)) return $old;

        $abs = public_path("uploads/$dir");
        File::ensureDirectoryExists($abs);
        $png  = $r->file($field)->getMimeType() === 'image/png';
        $name = Str::lower(Str::random(14)) . ($png ? '.png' : '.jpg');
        $img  = (new ImageManager(new Driver()))->read($r->file($field)->getRealPath())->scaleDown(width: $size, height: $size);
        ($png ? $img->toPng() : $img->toJpeg(82))->save($abs . DIRECTORY_SEPARATOR . $name);
        $this->dropFile($old);
        return "uploads/$dir/$name";
    }

    private function dropFile(?string $path): void
    {
        if ($path && str_starts_with($path, 'uploads/')) File::delete(public_path($path));
    }

    /* ---------- categories ---------- */
    private function saveCategory(Request $r, ?Category $cat): Category
    {
        $id = $cat?->id;
        $d = $r->validate([
            'parent_id'        => 'nullable|integer|exists:categories,id',
            'name'             => 'required|string|max:255',
            'slug'             => ['nullable', 'string', 'max:255'],
            'description'      => 'nullable|string|max:5000',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'sort_order'       => 'nullable|integer|min:0',
            'is_active'        => 'nullable|boolean',
            'image'            => 'nullable|image|max:3072',
            'attrs_json'       => 'nullable|string',
        ]);
        $slug = $this->slugFor($d['slug'] ?? null, $d['name']);
        if (Category::where('slug', $slug)->where('id', '!=', $id ?? 0)->exists()) $this->fail('slug', 'This slug is already used.');

        $parent = !empty($d['parent_id']) ? Category::find($d['parent_id']) : null;
        if ($cat && $parent) {
            if ($parent->id === $cat->id || str_contains('/' . $parent->path, '/' . $cat->id . '/')) {
                $this->fail('parent_id', 'A category cannot be moved inside itself or its own sub-categories.');
            }
        }
        if ($parent && Product::withTrashed()->where('category_id', $parent->id)->exists() && $parent->id !== $cat?->parent_id) {
            $this->fail('parent_id', 'Products are already listed in the chosen parent. Sellers can only list in the last level.');
        }
        if (($parent ? $parent->depth + 1 : 0) + ($cat ? $this->height($cat) : 0) > self::MAX_DEPTH) {
            $this->fail('parent_id', 'Only 3 levels are supported (category → group → item).');
        }

        $fields = [
            'parent_id' => $parent?->id, 'name' => trim($d['name']), 'slug' => $slug,
            'description' => $d['description'] ?? null, 'meta_title' => $d['meta_title'] ?? null,
            'meta_description' => $d['meta_description'] ?? null,
            'sort_order' => $d['sort_order'] ?? 0, 'is_active' => $r->boolean('is_active', true),
            'image' => $this->pickImage($r, 'image', $cat?->image, 'categories', 900),
        ];
        $cat ? $cat->update($fields) : $cat = Category::create($fields);

        $this->setPath($cat, $parent);
        $this->syncCategoryAttributes($cat, $d['attrs_json'] ?? null);
        return $cat;
    }

    private function height(Category $c): int
    {
        $max = 0;
        foreach (Category::where('parent_id', $c->id)->get() as $ch) $max = max($max, 1 + $this->height($ch));
        return $max;
    }

    /** materialised path ("1/5/12/") + depth, cascading to descendants */
    private function setPath(Category $c, ?Category $parent): void
    {
        $c->path  = ($parent?->path ?? '') . $c->id . '/';
        $c->depth = $parent ? $parent->depth + 1 : 0;
        $c->save();
        foreach (Category::where('parent_id', $c->id)->get() as $ch) $this->setPath($ch, $c);
    }

    private function syncCategoryAttributes(Category $cat, ?string $json): void
    {
        if ($json === null) return;
        $rows = json_decode($json, true);
        if (!is_array($rows)) $this->fail('attrs_json', 'Invalid attribute list.');

        $v = Validator::make(['rows' => $rows], [
            'rows.*.attribute_id'    => 'required|integer|exists:attributes,id',
            'rows.*.is_required'     => 'nullable|boolean',
            'rows.*.is_filterable'   => 'nullable|in:0,1,',
            'rows.*.is_variant_axis' => 'nullable|in:0,1,',
            'rows.*.applies_to'      => 'required|in:retail,wholesale,both',
            'rows.*.sort_order'      => 'nullable|integer|min:0',
        ]);
        if ($v->fails()) throw new ValidationException($v);
        if (count(array_unique(array_column($rows, 'attribute_id'))) !== count($rows)) $this->fail('attrs_json', 'The same attribute is added twice.');

        $nullable = fn ($x) => ($x === '' || $x === null) ? null : (bool) (int) $x;
        DB::table('category_attributes')->where('category_id', $cat->id)->delete();
        foreach ($rows as $i => $x) {
            DB::table('category_attributes')->insert([
                'category_id' => $cat->id, 'attribute_id' => $x['attribute_id'],
                'is_required' => !empty($x['is_required']), 'is_filterable' => $nullable($x['is_filterable'] ?? null),
                'is_variant_axis' => $nullable($x['is_variant_axis'] ?? null), 'applies_to' => $x['applies_to'],
                'sort_order' => $x['sort_order'] ?? $i, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    /* ---------- attribute groups ---------- */
    private function saveGroup(Request $r, ?AttributeGroup $g): AttributeGroup
    {
        $d = $r->validate(['name' => 'required|string|max:255', 'slug' => 'nullable|string|max:255', 'sort_order' => 'nullable|integer|min:0']);
        $slug = $this->slugFor($d['slug'] ?? null, $d['name']);
        if (AttributeGroup::where('slug', $slug)->where('id', '!=', $g?->id ?? 0)->exists()) $this->fail('slug', 'This slug is already used.');
        $f = ['name' => trim($d['name']), 'slug' => $slug, 'sort_order' => $d['sort_order'] ?? 0];
        return $g ? tap($g)->update($f) : AttributeGroup::create($f);
    }

    /* ---------- attributes (+ options) ---------- */
    private function saveAttribute(Request $r, ?Attribute $a): Attribute
    {
        $d = $r->validate([
            'attribute_group_id' => 'nullable|integer|exists:attribute_groups,id',
            'unit_id'            => 'nullable|integer|exists:units,id',
            'name'               => 'required|string|max:255',
            'slug'               => 'nullable|string|max:255',
            'input_type'         => 'required|in:text,textarea,number,select,multiselect,boolean,color,date',
            'sort_order'         => 'nullable|integer|min:0',
            'options_json'       => 'nullable|string',
        ]);
        $slug = $this->slugFor($d['slug'] ?? null, $d['name']);
        if (Attribute::where('slug', $slug)->where('id', '!=', $a?->id ?? 0)->exists()) $this->fail('slug', 'This slug is already used.');

        $f = [
            'attribute_group_id' => $d['attribute_group_id'] ?? null, 'unit_id' => $d['unit_id'] ?? null,
            'name' => trim($d['name']), 'slug' => $slug, 'input_type' => $d['input_type'], 'sort_order' => $d['sort_order'] ?? 0,
            'is_filterable' => $r->boolean('is_filterable'), 'is_searchable' => $r->boolean('is_searchable'),
            'is_comparable' => $r->boolean('is_comparable'), 'is_variant_axis' => $r->boolean('is_variant_axis'),
            'is_visible' => $r->boolean('is_visible', true),
        ];
        $a ? $a->update($f) : $a = Attribute::create($f);

        $hasOptions = in_array($d['input_type'], ['select', 'multiselect', 'color']);
        $rows = $hasOptions ? json_decode($d['options_json'] ?? '[]', true) : [];
        if (!is_array($rows)) $this->fail('options_json', 'Invalid options.');
        if ($f['is_variant_axis'] && !in_array($d['input_type'], ['select', 'color'])) {
            $this->fail('input_type', 'A variant option (e.g. Size, Colour) must be a Select or Colour type.');
        }
        if ($hasOptions && !$rows && $f['is_variant_axis']) $this->fail('options_json', 'Add at least one option for a variant attribute.');

        $keep = []; $seen = [];
        foreach ($rows as $i => $o) {
            $label = trim($o['label'] ?? '');
            if ($label === '') continue;
            $value = Str::limit(trim($o['value'] ?? '') ?: Str::slug($label, '_'), 100, '');
            if (isset($seen[$value])) $this->fail('options_json', "Duplicate option value '$value'.");
            $seen[$value] = true;
            $hex = trim($o['color_hex'] ?? '');
            if ($hex !== '' && !preg_match('/^#[0-9a-fA-F]{3,8}$/', $hex)) $this->fail('options_json', "Invalid colour '$hex'.");

            $payload = ['label' => Str::limit($label, 255, ''), 'value' => $value, 'color_hex' => $hex ?: null, 'sort_order' => $o['sort_order'] ?? $i];
            $opt = !empty($o['id']) ? $a->options()->find($o['id']) : null;
            $opt ? $opt->update($payload) : ($opt = $a->options()->create($payload));
            $keep[] = $opt->id;
        }
        $a->options()->whereNotIn('id', $keep)->delete();   // FK restrict on variants -> friendly 422 via guard()
        return $a;
    }

    /* ---------- units ---------- */
    private function saveUnit(Request $r, ?Unit $u): Unit
    {
        $d = $r->validate([
            'name'              => ['required', 'string', 'max:255', Rule::unique('units', 'name')->ignore($u?->id)],
            'short_name'        => 'required|string|max:20',
            'type'              => 'required|in:count,weight,length,area,volume,pack',
            'base_unit_id'      => 'nullable|integer|exists:units,id',
            'conversion_factor' => 'nullable|numeric|min:0.000001',
        ]);
        if ($u && ($d['base_unit_id'] ?? null) == $u->id) $this->fail('base_unit_id', 'A unit cannot be its own base unit.');
        $f = ['name' => trim($d['name']), 'short_name' => $d['short_name'], 'type' => $d['type'],
              'base_unit_id' => $d['base_unit_id'] ?? null, 'conversion_factor' => $d['conversion_factor'] ?? 1,
              'allow_decimal' => $r->boolean('allow_decimal')];
        return $u ? tap($u)->update($f) : Unit::create($f);
    }

    /* ---------- brands ---------- */
    private function saveBrand(Request $r, ?Brand $b): Brand
    {
        $d = $r->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('brands', 'name')->ignore($b?->id)],
            'slug' => 'nullable|string|max:255', 'logo' => 'nullable|image|max:2048',
        ]);
        $slug = $this->slugFor($d['slug'] ?? null, $d['name']);
        if (Brand::where('slug', $slug)->where('id', '!=', $b?->id ?? 0)->exists()) $this->fail('slug', 'This slug is already used.');
        $f = ['name' => trim($d['name']), 'slug' => $slug, 'is_active' => $r->boolean('is_active', true),
              'logo' => $this->pickImage($r, 'logo', $b?->logo, 'brands', 500)];
        return $b ? tap($b)->update($f) : Brand::create($f);
    }
}
