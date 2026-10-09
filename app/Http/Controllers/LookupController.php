<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Diocese;
use App\Models\Forane;
use App\Models\JobCategory;
use App\Models\Rite;
use App\Models\State;

class LookupController extends Controller
{
    public function dioceses(Rite $rite)           { return $this->list($rite->dioceses()); }
    public function foranes(Diocese $diocese)      { return $this->list($diocese->foranes()); }
    public function parishes(Forane $forane)       { return $this->list($forane->parishes()); }
    public function states(Country $country)       { return $this->list($country->states()); }
    public function districts(State $state)        { return $this->list($state->districts()); }
    public function jobs(JobCategory $category)    { return $this->list($category->jobs()); }

    private function list($relation)
    {
        return response()->json($relation->orderBy('name')->get(['id', 'name']));
    }
}
