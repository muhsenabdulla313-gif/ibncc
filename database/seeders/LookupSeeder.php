<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\District;
use App\Models\Job;
use App\Models\JobCategory;
use App\Models\State;
use Illuminate\Database\Seeder;

class LookupSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['India', 'United Arab Emirates', 'Saudi Arabia', 'Qatar', 'Kuwait', 'Oman', 'Bahrain',
                  'United States', 'United Kingdom', 'Canada', 'Australia', 'Germany', 'Singapore'] as $name) {
            Country::firstOrCreate(['name' => $name]);
        }

        $india = Country::where('name', 'India')->first();
        foreach (['Kerala', 'Tamil Nadu', 'Karnataka', 'Maharashtra', 'Goa', 'Delhi'] as $name) {
            State::firstOrCreate(['country_id' => $india->id, 'name' => $name]);
        }

        $kerala = State::where('name', 'Kerala')->first();
        foreach (['Thiruvananthapuram', 'Kollam', 'Pathanamthitta', 'Alappuzha', 'Kottayam', 'Idukki', 'Ernakulam',
                  'Thrissur', 'Palakkad', 'Malappuram', 'Kozhikode', 'Wayanad', 'Kannur', 'Kasaragod'] as $name) {
            District::firstOrCreate(['state_id' => $kerala->id, 'name' => $name]);
        }

        $jobs = [
            'Engineering'           => ['Civil Engineer', 'Mechanical Engineer', 'Electrical Engineer', 'Software Engineer'],
            'Healthcare'            => ['Physician', 'Nurse', 'Pharmacist', 'Dentist'],
            'Education'             => ['Teacher', 'Professor', 'Trainer'],
            'Business'              => ['Entrepreneur', 'Business Consultant', 'Trader', 'Marketing Strategist'],
            'Legal & Finance'       => ['Lawyer', 'Chartered Accountant', 'Financial Advisor'],
            'Architecture & Design' => ['Architect', 'Interior Designer'],
        ];
        foreach ($jobs as $category => $names) {
            $cat = JobCategory::firstOrCreate(['name' => $category]);
            foreach ($names as $name) {
                Job::firstOrCreate(['job_category_id' => $cat->id, 'name' => $name]);
            }
        }
    }
}
