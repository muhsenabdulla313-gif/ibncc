<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $highlights = <<<'TXT'
Attending IBNCC Global Business Conclave Offers
High-value networking | Connect with industry leaders, potential partners and global investors in a trusted community.
Direct market insights | Gain first-hand knowledge of emerging market trends and international business landscapes.
Strategic growth opportunities | Discover new avenues for business expansion and collaborative ventures.

Networking and Partnerships
Connect with CEOs, founders and key decision-makers. | Engage directly with leaders who influence strategy, investment and growth.
Build lasting alliances, joint ventures and client leads. | Turn conversations into durable commercial and collaborative relationships.
Interact directly with prominent industry experts. | Ask questions, exchange ideas and learn from practitioners shaping the market.

Knowledge and Trends
Learn cutting-edge market trends and new technologies. | Stay ahead with insights that reshape how modern enterprises compete and grow.
Gain practical problem-solving strategies from peers. | Apply real-world approaches shared by fellow entrepreneurs and professionals.
Listen to expert keynote speakers and panel sessions. | Hear from voices who combine leadership experience with actionable perspective.

Growth and Visibility
Boost personal and corporate brand credibility. | Strengthen how your leadership and organisation are recognised in the community.
Showcase products or services to targeted audiences. | Present your offering to decision-makers and partners who matter.
Discover fresh regional or global market expansion channels. | Identify pathways to grow beyond your current geography and customer base.

IBNCC Global Business Conclave Includes
Time for Networking | Dedicated moments to meet peers, share ideas and build lasting business relationships.
Panel Discussions | Interactive sessions with industry leaders on trends, challenges and opportunities.
Speeches by Experts | AI in Business, Patent & Trademark, Future Business Opportunities, Emerging Technologies, Digital Marketing.
TXT;

        Event::updateOrCreate(['slug' => 'ibncc-global-business-conclave'], [
            'title'         => 'IBNCC Global Business Conclave',
            'tagline'       => 'Connect. Learn. Grow. Expand.',
            'event_date'    => '2026-09-13',
            'time_text'     => '9:00 AM – 8:00 PM',
            'venue'         => 'Monsoon Empress Hotel, NH Bypass, Palarivattom, Kochi',
            'city'          => 'Kochi, Kerala',
            'intro'         => 'Join a powerful gathering of Christian entrepreneurs and industry leaders to shape the future of faith-aligned business — locally and globally.',
            'highlights'    => $highlights,
            'cover_image'   => 'assets/images/connection.avif',
            'qr_image'      => 'assets/images/qr2.png',
            'contact_phone' => '9847030064',
            'contact_email' => 'ibncc26@gmail.com',
            'is_active'     => true,
        ]);

        $u = fn ($id, $w) => "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w={$w}&q=80";

        $previous = [
            ['Christian Leadership Summit',   '2025-10-18', 'Kochi, Kerala',       ['1515187029135-18ee286d815b', '1540575467063-178a50c2df87', '1544531586-fde5298cdd40', '1587825140708-dfaf72ae4b04']],
            ['Entrepreneurs Forum 2025',      '2025-06-22', 'Bengaluru, Karnataka', ['1475721027785-f74eccf877e2', '1552664730-d307ca884978', '1542744173-8e7e53415bb0', '1559136555-9303baea8ebd']],
            ['Women in Business Meet',        '2025-03-08', 'Chennai, Tamil Nadu',  ['1556761175-b413da4baf72', '1600880292203-757bb62b4baf', '1551836022-d5d88e9218df', '1573164713988-8665fc963095']],
            ['Faith & Finance Roundtable',    '2024-11-14', 'Mumbai, Maharashtra',  ['1591115765373-5207764f72e7', '1454165804606-c3d57bc86b40', '1554224155-6726b3ff858f', '1517048676732-d65bc937f952']],
            ['Startup Pitch Night',           '2024-09-05', 'Hyderabad, Telangana', ['1505373877841-8d25f7d46678', '1531482615713-2afd69097998', '1519389950473-47ba0277781c', '1522071820081-009f0129c71c']],
            ['Global Networking Evening',     '2024-04-20', 'Dubai, UAE',           ['1528605105345-5344ea20e269', '1464366400600-7168b8af9bc3', '1511795409834-ef04bbd61622', '1519167758481-83f550bb49b3']],
        ];

        foreach ($previous as [$title, $date, $city, $ids]) {
            $event = Event::updateOrCreate(['slug' => \Illuminate\Support\Str::slug($title)], [
                'title'       => $title,
                'event_date'  => $date,
                'city'        => $city,
                'cover_image' => $u($ids[0], 800),
                'is_active'   => true,
            ]);

            if ($event->images()->count() === 0) {
                foreach ($ids as $i => $id) {
                    $event->images()->create(['image' => $u($id, 1200), 'sort_order' => $i]);
                }
            }
        }
    }
}
