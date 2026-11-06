<?php

namespace Database\Seeders;

use App\Models\ContactMessage;
use Illuminate\Database\Seeder;

/**
 * 50 realistic sample messages so the admin inbox has something to show.
 * Development only, and safe to re-run (it won't add a second batch).
 */
class ContactMessageSeeder extends Seeder
{
    private const COUNT = 50;
    private const SAMPLE_DOMAIN = 'example.test';

    public function run(): void
    {
        if (app()->isProduction()) {
            return;
        }

        if (ContactMessage::where('email', 'like', '%@'.self::SAMPLE_DOMAIN)->exists()) {
            return;
        }

        $names = [
            'Maria Santos', 'Juan dela Cruz', 'Angela Reyes', 'Paolo Garcia', 'Bea Villanueva',
            'Miguel Tan', 'Carla Mendoza', 'Jerome Aquino', 'Kristine Lim', 'Rafael Cruz',
            'Sofia Ramos', 'Daniel Bautista', 'Hannah Flores', 'Nathan Uy', 'Isabel Navarro',
            'Gabriel Torres', 'Camille Ong', 'Lucas Castillo', 'Patricia Domingo', 'Kenji Sato',
            'Elise Park', 'Marco Rossi', 'Aisha Khan', 'Noah Williams', 'Mei Lin',
        ];

        $templates = [
            ['Do you take reservations?', 'Hi! Do you take reservations for groups? We would like a table for six this Saturday afternoon.'],
            ['Catering for an office event', "We're planning an office event for about 30 people next month. Do you offer catering or bulk matcha orders?"],
            ['Do you have oat milk?', "Quick question: can I swap in oat or almond milk for the Chai Matcha? I'm lactose intolerant."],
            ['Wholesale matcha inquiry', 'I run a small bakery in Mandaue and we are interested in buying your matcha wholesale. Could you share pricing?'],
            ['Holiday opening hours', "Are you open on holidays like Christmas Eve and New Year's Day?"],
            ['Feedback on the Strawberry Matcha', 'I loved the Strawberry Matcha! The strawberry puree tasted fresh and was not too sweet. Please keep it on the menu.'],
            ['Lost umbrella', 'I think I left a black umbrella at your shop on Sunday afternoon. Has anyone turned one in?'],
            ['Photo collaboration', "I'm a local photographer and would love to shoot a photo series featuring your drinks. Can we set up a call?"],
            ['Are you hiring baristas?', 'I have two years of cafe experience and I am available for weekend shifts. Who should I send my resume to?'],
            ['Birthday pre-order', "I'd like to pre-order 12 iced matcha drinks for a birthday on the 20th. How far ahead should I order?"],
            ['Matcha whisking workshop?', 'Do you hold matcha whisking workshops? A few friends and I would love to learn how to do it properly.'],
            ['Parking near the shop', "Is there parking near the shop in Canjulao? We'll be coming by car with my parents."],
            ['Nut allergy question', 'Do any of your drinks contain nuts? My daughter has a tree-nut allergy and I want to be careful.'],
            ['Gift sets', "Do you sell matcha powder or gift sets? I'd like to send one to a friend abroad."],
            ['Do you deliver?', 'Do you deliver to Mactan, or is it pick-up only? I am looking for a morning delivery for a meeting.'],
            ['About the Roasted Banana Matcha', 'How sweet is the Roasted Banana Matcha? I am trying to cut down on sugar but really want to try it.'],
            ['Thank you to your staff', 'Your staff were so kind to my grandmother last week and made her feel welcome. Thank you for that.'],
            ['Student discount?', 'Are there student discounts? I study nearby and would be a regular if the prices work out.'],
            ['Pet-friendly seating?', "Is your outdoor area pet-friendly? I'd like to bring my small, very quiet dog."],
            ['Menu page was slow', 'The menu page loaded slowly on my phone this morning. Just letting you know in case it helps.'],
            ['Is the matcha really from Uji?', "I'd love to hear more about the farm you source from. Is it really from Uji, Kyoto?"],
            ['Loyalty card', 'Do you have a loyalty or stamp card? I come by almost every week and would love to collect stamps.'],
            ['Renting the shop for an event', 'Could we rent the shop for a small private event on a Monday evening, when you are normally closed?'],
            ['Vegan options', 'Are your drinks vegan-friendly? I want to know if the Chai Matcha is made with honey.'],
            ['Menu suggestion', 'Have you thought about a hojicha latte or a matcha affogato? I would order both!'],
        ];

        $closings = ['Thank you!', 'Looking forward to hearing from you.', 'Salamat!', 'Hope to visit soon.', 'Have a great day!'];

        $rows = [];

        for ($i = 0; $i < self::COUNT; $i++) {
            [$subject, $body] = $templates[$i % count($templates)];
            $name = $names[($i * 7 + 3) % count($names)];
            $first = strtolower(explode(' ', $name)[0]);

            // Oldest first: spread the 50 messages over roughly the last 60 days.
            $daysAgo = (int) floor((self::COUNT - 1 - $i) * 60 / (self::COUNT - 1));
            $createdAt = now()->subDays($daysAgo)->subHours(($i * 5) % 11)->subMinutes(($i * 13) % 60);

            // The 15 newest are unread, plus every 7th older one.
            $isRead = $i < 35 && $i % 7 !== 0;

            $rows[] = [
                'email' => $first.($i + 1).'@'.self::SAMPLE_DOMAIN,
                'subject' => $subject,
                'message' => $body.' '.$closings[($i * 3) % count($closings)]."\n\n".$name,
                'read_at' => $isRead ? $createdAt->copy()->addDay()->toDateTimeString() : null,
                'created_at' => $createdAt->toDateTimeString(),
                'updated_at' => $createdAt->toDateTimeString(),
            ];
        }

        ContactMessage::insert($rows);
    }
}
