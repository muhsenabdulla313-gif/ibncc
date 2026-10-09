<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Diocese;
use App\Models\Forane;
use App\Models\Parish;
use App\Models\Rite;
class KeralachurchSeeder extends Seeder
{
   private const PLACEHOLDER_FORANE = 'General';
 
    public function run(): void
    {
        $data = [
 
            'Syro-Malabar' => [
                'Ernakulam-Angamaly' => [
                    "St. Mary's Cathedral Basilica, Ernakulam",
                    'St. George Forane Church, Edappally',
                    'Holy Family Church, Kakkanad',
                ],
                'Thrissur' => [
                    'Our Lady of Dolours Basilica, Thrissur',
                    'St. Thomas Church, Palayur',
                    "St. Antony's Forane Church, Ollur",
                ],
                'Palakkad' => [
                    "St. Raphael's Cathedral, Palakkad",
                    'Sacred Heart Church, Chittur',
                    "St. Mary's Church, Mannarkkad",
                ],
                'Thamarassery' => [
                    'Mary Matha Cathedral, Thamarassery',
                    'St. George Forane Church, Kodenchery',
                    'Holy Cross Church, Thiruvambady',
                ],
                'Thalassery' => [
                    "St. Joseph's Cathedral, Thalassery",
                    "St. Sebastian's Church, Iritty",
                    'Little Flower Church, Alakode',
                ],
            ],
 
            'Syro-Malankara' => [
                'Trivandrum' => [
                    "St. Mary's Cathedral, Pattom",
                    'St. George Church, Nalanchira',
                    'Mar Baselios Church, Kattakkada',
                ],
                'Mavelikara' => [
                    "St. Mary's Cathedral, Mavelikara",
                    'St. Thomas Church, Chengannur',
                    'St. George Church, Haripad',
                ],
                'Pathanamthitta' => [
                    "St. Peter's Cathedral, Pathanamthitta",
                    "St. Mary's Church, Adoor",
                    'St. Thomas Church, Ranni',
                ],
                'Bathery' => [
                    'St. Thomas Cathedral, Sulthan Bathery',
                    "St. Mary's Church, Kalpetta",
                    'Holy Cross Church, Meenangadi',
                ],
            ],
 
            'Latin' => [
                'Kozhikode' => [
                    'Mother of God Cathedral, Kozhikode',
                    "St. Joseph's Church, Kozhikode Beach",
                    'Holy Redeemer Church, Vadakara',
                ],
                'Kannur' => [
                    'Holy Trinity Cathedral, Burnassery',
                    "St. Antony's Church, Thalap",
                    'Our Lady of Lourdes Church, Payyambalam',
                ],
                'Verapoly' => [
                    'St. Francis of Assisi Cathedral, Ernakulam',
                    'Our Lady of Mount Carmel Church, Varapuzha',
                    "St. Sebastian's Church, Kadavanthra",
                ],
                'Kollam' => [
                    'Infant Jesus Cathedral, Kollam',
                    "St. Antony's Church, Thangassery",
                    'Our Lady of Velankanni Church, Kollam',
                ],
            ],
        ];
 
        foreach ($data as $riteName => $dioceses) {
            $rite = Rite::firstOrCreate(['name' => $riteName]);
 
            foreach ($dioceses as $dioceseName => $parishes) {
                $diocese = Diocese::firstOrCreate(['rite_id' => $rite->id, 'name' => $dioceseName]);
                $forane  = Forane::firstOrCreate(['diocese_id' => $diocese->id, 'name' => self::PLACEHOLDER_FORANE]);
 
                foreach ($parishes as $parishName) {
                    Parish::firstOrCreate(['forane_id' => $forane->id, 'name' => $parishName]);
                }
            }
        }
    }
}
