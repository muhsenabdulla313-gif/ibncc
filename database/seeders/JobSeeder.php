<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Job;
use App\Models\JobCategory;
class JobSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Healthcare & Medical' => [
                'Doctor / Physician', 'Surgeon', 'Dentist', 'Nurse', 'Midwife', 'Pharmacist', 'Physiotherapist',
                'Radiologist', 'Lab Technician', 'Paramedic', 'Veterinarian', 'Psychologist', 'Psychiatrist',
                'Optometrist', 'Nutritionist / Dietitian', 'Ayurveda Practitioner', 'Homeopathy Practitioner',
                'Medical Representative', 'Healthcare Administrator', 'Caregiver',
            ],
            'Education & Training' => [
                'School Teacher', 'College Lecturer', 'Professor', 'Principal', 'Tutor', 'Trainer / Coach',
                'Librarian', 'Education Counselor', 'Special Educator', 'Early Childhood Educator',
                'Researcher', 'Education Administrator',
            ],
            'Information Technology' => [
                'Software Developer', 'Web Developer', 'Mobile App Developer', 'Full Stack Developer',
                'Frontend Developer', 'Backend Developer', 'Data Scientist', 'Data Analyst', 'AI / ML Engineer',
                'DevOps Engineer', 'Cloud Engineer', 'Cybersecurity Analyst', 'Database Administrator',
                'Network Engineer', 'System Administrator', 'QA / Test Engineer', 'UI/UX Designer',
                'Product Manager', 'Project Manager (IT)', 'IT Support', 'Game Developer', 'Blockchain Developer',
            ],
            'Engineering' => [
                'Civil Engineer', 'Mechanical Engineer', 'Electrical Engineer', 'Electronics Engineer',
                'Chemical Engineer', 'Aerospace Engineer', 'Automobile Engineer', 'Industrial Engineer',
                'Environmental Engineer', 'Biomedical Engineer', 'Structural Engineer', 'Petroleum Engineer',
                'Marine Engineer', 'Mining Engineer', 'Site Engineer', 'Quality Engineer',
            ],
            'Business & Management' => [
                'Business Owner', 'Entrepreneur', 'CEO / Managing Director', 'General Manager', 'Manager',
                'Operations Manager', 'Project Manager', 'HR Manager', 'Business Analyst', 'Management Consultant',
                'Supply Chain Manager', 'Procurement Officer', 'Administrative Assistant', 'Office Manager',
                'Executive Assistant', 'Receptionist', 'Data Entry Operator',
            ],
            'Finance & Accounting' => [
                'Accountant', 'Chartered Accountant', 'Auditor', 'Tax Consultant', 'Financial Analyst',
                'Financial Planner', 'Investment Banker', 'Banker', 'Bank Clerk', 'Loan Officer',
                'Insurance Agent', 'Actuary', 'Stock Broker', 'Cashier', 'Bookkeeper', 'Treasury Manager',
            ],
            'Sales & Marketing' => [
                'Sales Executive', 'Sales Manager', 'Marketing Manager', 'Digital Marketer', 'SEO Specialist',
                'Social Media Manager', 'Content Marketer', 'Brand Manager', 'Market Researcher',
                'Public Relations Officer', 'Advertising Executive', 'Business Development Executive',
                'Telemarketer', 'Retail Salesperson', 'Real Estate Agent', 'Customer Success Manager',
            ],
            'Legal & Law Enforcement' => [
                'Lawyer / Advocate', 'Judge', 'Legal Advisor', 'Paralegal', 'Notary', 'Company Secretary',
                'Police Officer', 'Detective', 'Security Guard', 'Security Officer', 'Prison Officer',
                'Customs Officer', 'Forensic Expert',
            ],
            'Government & Public Service' => [
                'Civil Servant', 'IAS / IPS / IFS Officer', 'Government Clerk', 'Revenue Officer',
                'Postal Worker', 'Municipal Worker', 'Panchayat Official', 'Diplomat', 'Politician',
                'Social Worker', 'NGO Worker', 'Public Health Inspector',
            ],
            'Defense & Armed Forces' => [
                'Army Personnel', 'Navy Personnel', 'Air Force Personnel', 'Paramilitary Personnel',
                'Coast Guard', 'Ex-Serviceman', 'Fire Service Personnel', 'Home Guard',
            ],
            'Religious & Spiritual Services' => [
                'Priest', 'Pastor', 'Bishop', 'Deacon', 'Nun / Sister', 'Brother / Religious',
                'Catechist', 'Church Administrator', 'Church Musician', 'Sacristan', 'Missionary',
                'Imam', 'Temple Priest', 'Chaplain', 'Seminarian',
            ],
            'Agriculture & Farming' => [
                'Farmer', 'Agricultural Officer', 'Agronomist', 'Horticulturist', 'Dairy Farmer',
                'Poultry Farmer', 'Fisherman', 'Aquaculture Farmer', 'Plantation Worker', 'Plantation Owner',
                'Beekeeper', 'Forest Officer', 'Agricultural Scientist', 'Farm Laborer',
            ],
            'Construction & Real Estate' => [
                'Contractor', 'Architect', 'Interior Designer', 'Quantity Surveyor', 'Land Surveyor',
                'Mason', 'Carpenter', 'Plumber', 'Electrician', 'Painter', 'Welder', 'Tiler',
                'Construction Worker', 'Property Manager', 'Real Estate Developer', 'Foreman',
            ],
            'Manufacturing & Industry' => [
                'Factory Worker', 'Machine Operator', 'Production Supervisor', 'Fitter', 'Turner / Machinist',
                'Quality Inspector', 'Industrial Technician', 'Plant Manager', 'Tailor', 'Garment Worker',
                'Textile Worker', 'Printing Operator', 'Packaging Worker',
            ],
            'Transport & Logistics' => [
                'Driver', 'Truck Driver', 'Taxi / Cab Driver', 'Bus Driver', 'Auto Rickshaw Driver',
                'Delivery Executive', 'Courier', 'Logistics Manager', 'Warehouse Manager', 'Warehouse Worker',
                'Pilot', 'Flight Attendant', 'Air Traffic Controller', 'Ship Captain', 'Seafarer',
                'Railway Employee', 'Freight Forwarder', 'Mechanic', 'Driving Instructor',
            ],
            'Hospitality & Tourism' => [
                'Hotel Manager', 'Chef', 'Cook', 'Baker', 'Waiter / Waitress', 'Bartender', 'Restaurant Owner',
                'Caterer', 'Travel Agent', 'Tour Guide', 'Front Desk Executive', 'Housekeeping Staff',
                'Event Planner', 'Resort Manager',
            ],
            'Retail & Trade' => [
                'Shop Owner', 'Shopkeeper', 'Store Manager', 'Wholesaler', 'Trader', 'Supermarket Staff',
                'Street Vendor', 'E-commerce Seller', 'Importer / Exporter', 'Jeweller', 'Textile Merchant',
                'Pharmacy Owner',
            ],
            'Media, Arts & Entertainment' => [
                'Journalist', 'Reporter', 'News Anchor', 'Editor', 'Writer / Author', 'Blogger',
                'Photographer', 'Videographer', 'Film Director', 'Actor / Actress', 'Producer',
                'Musician', 'Singer', 'Dancer', 'Painter / Artist', 'Graphic Designer', 'Animator',
                'Content Creator', 'YouTuber', 'Radio Jockey', 'Sound Engineer', 'Translator',
            ],
            'Sports & Fitness' => [
                'Athlete', 'Sports Coach', 'Fitness Trainer', 'Yoga Instructor', 'Referee',
                'Sports Manager', 'Physical Education Teacher', 'Swimming Instructor',
            ],
            'Science & Research' => [
                'Scientist', 'Research Scholar', 'Lab Researcher', 'Physicist', 'Chemist', 'Biologist',
                'Mathematician', 'Statistician', 'Geologist', 'Meteorologist', 'Astronomer',
                'Environmental Scientist', 'Biotechnologist',
            ],
            'Banking, Insurance & Investment' => [
                'Branch Manager', 'Relationship Manager', 'Credit Analyst', 'Insurance Underwriter',
                'Mutual Fund Distributor', 'Wealth Manager', 'Risk Analyst', 'Microfinance Officer',
            ],
            'Personal & Household Services' => [
                'Housekeeper', 'Domestic Helper', 'Nanny', 'Cleaner', 'Gardener', 'Cook (Home)',
                'Barber', 'Hairdresser', 'Beautician', 'Tailor (Home)', 'Laundry Worker', 'Pest Control Worker',
                'Home Nurse', 'Driver (Personal)', 'Watchman',
            ],
            'Technical Trades & Repair' => [
                'Auto Mechanic', 'Electrician (Technician)', 'AC Technician', 'Mobile Repair Technician',
                'Computer Technician', 'Appliance Repair Technician', 'CCTV Technician', 'Solar Technician',
                'Lineman', 'Plumber (Technician)', 'Welder (Technician)', 'Machinist', 'Goldsmith', 'Blacksmith',
            ],
            'Energy, Mining & Environment' => [
                'Power Plant Operator', 'Solar Installer', 'Wind Technician', 'Miner', 'Oil & Gas Worker',
                'Environmental Consultant', 'Waste Management Worker', 'Water Treatment Operator',
            ],
            'Telecom & Electronics' => [
                'Telecom Engineer', 'Network Technician', 'Call Center Agent', 'Customer Support Executive',
                'Electronics Technician', 'Tower Technician', 'Technical Support Engineer',
            ],
            'Freelance & Self-Employed' => [
                'Freelancer', 'Consultant', 'Self-Employed Professional', 'Commission Agent',
                'Online Tutor', 'Virtual Assistant', 'Independent Contractor',
            ],
            'Student & Not Working' => [
                'Student', 'Research Student', 'Job Seeker', 'Homemaker', 'Retired', 'Volunteer', 'Intern',
            ],
            'Other' => [],
        ];

        foreach ($data as $category => $jobs) {
            $cat = JobCategory::firstOrCreate(['name' => $category]);

            $jobs[] = 'Other';
            foreach (array_unique($jobs) as $job) {
                Job::firstOrCreate(['job_category_id' => $cat->id, 'name' => $job]);
            }
        }
    }
}
