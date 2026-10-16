<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Diocese;
use App\Models\Forane;
use App\Models\Parish;
use App\Models\Rite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChurchDirectoryController extends Controller
{
        private const TYPES = [
        'rite'    => ['model' => Rite::class,    'table' => 'rites',     'parent' => null,       'fk' => null,          'ptable' => null],
        'diocese' => ['model' => Diocese::class, 'table' => 'dioceses',  'parent' => 'rite',     'fk' => 'rite_id',     'ptable' => 'rites'],
        'forane'  => ['model' => Forane::class,  'table' => 'foranes',   'parent' => 'diocese',  'fk' => 'diocese_id',  'ptable' => 'dioceses'],
        'parish'  => ['model' => Parish::class,  'table' => 'parishes',  'parent' => 'forane',   'fk' => 'forane_id',   'ptable' => 'foranes'],
    ];

    // letters (any language), numbers, space and  . , ' ’ & ( ) / + -
    private const NAME_REGEX = '/^[\p{L}\p{M}\p{N} .,\'’&()\/+\-]+$/u';

    private const PER_PAGE = 10;

    /* ------------------------------------------------------------------ page */

    public function index()
    {
        return view('admin.church.index', ['counts' => $this->counts()]);
    }

    /* ------------------------------------------------------------------ READ */

    public function list(Request $request, string $type): JsonResponse
    {
        $cfg = $this->cfg($type);
        $q = $cfg['model']::query()->orderBy('name');

        if ($s = trim((string) $request->query('q', ''))) {
            $q->where('name', 'like', '%' . addcslashes($s, '%_\\') . '%');
        }

        $rite = (int) $request->query('rite_id');
        $dio = (int) $request->query('diocese_id');
        $forane = (int) $request->query('forane_id');

        switch ($type) {
            case 'rite':
                $q->withCount('dioceses');
                break;

            case 'diocese':
                $q->with('rite:id,name')->withCount('foranes');
                if ($rite) {
                    $q->where('rite_id', $rite);
                }
                break;

            case 'forane':
                $q->with('diocese.rite')->withCount('parishes');
                if ($dio) {
                    $q->where('diocese_id', $dio);
                }
                if ($rite) {
                    $q->whereHas('diocese', fn ($d) => $d->where('rite_id', $rite));
                }
                break;

            case 'parish':
                $q->with('forane.diocese.rite')->withCount('users');
                if ($forane) {
                    $q->where('forane_id', $forane);
                }
                if ($dio) {
                    $q->whereHas('forane', fn ($f) => $f->where('diocese_id', $dio));
                }
                if ($rite) {
                    $q->whereHas('forane.diocese', fn ($d) => $d->where('rite_id', $rite));
                }
                break;
        }

        $page = $q->paginate(self::PER_PAGE);

        return response()->json([
            'items'  => $page->getCollection()->map(fn ($m) => $this->row($type, $m))->values(),
            'meta'   => [
                'current_page' => $page->currentPage(),
                'last_page'    => max(1, $page->lastPage()),
                'total'        => $page->total(),
                'per_page'     => $page->perPage(),
            ],
            'counts' => $this->counts(),
        ]);
    }

    /** Options for the cascading dropdowns: ?type=diocese&parent_id=3 */
    public function options(Request $request): JsonResponse
    {
        $type = (string) $request->query('type');
        $cfg = $this->cfg($type);

        $q = $cfg['model']::query()->orderBy('name');

        if ($cfg['fk']) {
            $parentId = (int) $request->query('parent_id');
            if (! $parentId) {
                return response()->json([]);
            }
            $q->where($cfg['fk'], $parentId);
        }

        return response()->json($q->get(['id', 'name']));
    }

    /* ---------------------------------------------------------------- CREATE */

    public function store(Request $request, string $type): JsonResponse
    {
        $cfg = $this->cfg($type);
        $data = $this->validated($request, $type);

        $model = $cfg['model']::create($data);

        return response()->json([
            'message' => ucfirst($type) . ' added.',
            'id'      => $model->id,
        ], 201);
    }

    /* ---------------------------------------------------------------- UPDATE */

    public function update(Request $request, string $type, int $id): JsonResponse
    {
        $cfg = $this->cfg($type);
        $model = $cfg['model']::findOrFail($id);

        $model->update($this->validated($request, $type, $id));

        return response()->json(['message' => ucfirst($type) . ' updated.']);
    }

    /* ---------------------------------------------------------------- DELETE */

    public function destroy(string $type, int $id): JsonResponse
    {
        $cfg = $this->cfg($type);
        $model = $cfg['model']::findOrFail($id);

        // The database cascades deletes, so block them here instead of wiping data by accident.
        [$count, $what] = match ($type) {
            'rite'    => [$model->dioceses()->count(), 'diocese(s)'],
            'diocese' => [$model->foranes()->count(), 'forane(s)'],
            'forane'  => [$model->parishes()->count(), 'parish(es)'],
            'parish'  => [$model->users()->count(), 'member(s)'],
        };

        if ($count > 0) {
            return response()->json([
                'message' => "Cannot delete: {$count} {$what} still linked to this {$type}. Remove or move them first.",
            ], 409);
        }

        $model->delete();

        return response()->json(['message' => ucfirst($type) . ' deleted.']);
    }

    /* --------------------------------------------------------------- helpers */

    private function cfg(string $type): array
    {
        abort_unless(isset(self::TYPES[$type]), 404);

        return self::TYPES[$type];
    }

    private function validated(Request $request, string $type, ?int $ignoreId = null): array
    {
        $cfg = $this->cfg($type);

        // tidy the name before validating: trim + collapse spaces
        $request->merge([
            'name' => preg_replace('/\s+/u', ' ', trim((string) $request->input('name'))),
        ]);

        // name must be unique inside its parent (or globally for rites)
        $unique = Rule::unique($cfg['table'], 'name')->ignore($ignoreId);
        if ($cfg['fk']) {
            $unique->where($cfg['fk'], (int) $request->input($cfg['fk']));
        }

        $rules = [
            'name' => ['required', 'string', 'min:2', 'max:100', 'regex:' . self::NAME_REGEX, $unique],
        ];
        if ($cfg['fk']) {
            $rules[$cfg['fk']] = ['required', 'integer', Rule::exists($cfg['ptable'], 'id')];
        }

        $where = $cfg['parent'] ? " under the selected {$cfg['parent']}" : '';

        return $request->validate($rules, [
            'name.required' => 'Name is required.',
            'name.min'      => 'Name must be at least 2 characters.',
            'name.max'      => 'Name must be at most 100 characters.',
            'name.regex'    => "Use letters, numbers and . , ' & ( ) / + - only.",
            'name.unique'   => "This {$type} already exists{$where}.",
            $cfg['fk'] . '.required' => 'Please select the ' . ($cfg['parent'] ?? '') . '.',
            $cfg['fk'] . '.exists'   => 'The selected ' . ($cfg['parent'] ?? '') . ' is invalid.',
        ]);
    }

    private function counts(): array
    {
        return [
            'rite'    => Rite::count(),
            'diocese' => Diocese::count(),
            'forane'  => Forane::count(),
            'parish'  => Parish::count(),
        ];
    }

    /** One table row, including the ids needed to pre-fill the edit form. */
    private function row(string $type, $m): array
    {
        return match ($type) {
            'rite' => [
                'id' => $m->id, 'name' => $m->name,
                'count' => $m->dioceses_count,
                'ids' => [],
            ],
            'diocese' => [
                'id' => $m->id, 'name' => $m->name,
                'rite' => $m->rite?->name,
                'count' => $m->foranes_count,
                'ids' => ['rite' => $m->rite_id],
            ],
            'forane' => [
                'id' => $m->id, 'name' => $m->name,
                'diocese' => $m->diocese?->name,
                'rite' => $m->diocese?->rite?->name,
                'count' => $m->parishes_count,
                'ids' => ['rite' => $m->diocese?->rite_id, 'diocese' => $m->diocese_id],
            ],
            'parish' => [
                'id' => $m->id, 'name' => $m->name,
                'forane' => $m->forane?->name,
                'diocese' => $m->forane?->diocese?->name,
                'rite' => $m->forane?->diocese?->rite?->name,
                'count' => $m->users_count,
                'ids' => [
                    'rite' => $m->forane?->diocese?->rite_id,
                    'diocese' => $m->forane?->diocese_id,
                    'forane' => $m->forane_id,
                ],
            ],
        };
    }
}
