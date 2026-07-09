<?php

namespace Database\Seeders;

use App\Models\Property;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    public function run(): void
    {
        $properties = [
            [
                'title' => 'Modern Family House',
                'location' => 'Canggu, Bali',
                'price' => 450000,
                'type' => 'House',
                'image' => 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800&q=80',
                'description' => 'Spacious modern family house with open living areas, a private pool, and a tropical garden close to the beach.',
            ],
            [
                'title' => 'Minimalist Urban Home',
                'location' => 'Seminyak, Bali',
                'price' => 380000,
                'type' => 'House',
                'image' => 'https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=800&q=80',
                'description' => 'Clean minimalist design with three bedrooms, smart home features, and a rooftop terrace in a prime neighborhood.',
            ],
            [
                'title' => 'Luxury Hillside Villa',
                'location' => 'Uluwatu, Bali',
                'price' => 1250000,
                'type' => 'Villa',
                'image' => 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800&q=80',
                'description' => 'Five-bedroom luxury villa perched on a cliff with panoramic ocean views, infinity pool, and private staff quarters.',
            ],
            [
                'title' => 'Contemporary Glass Residence',
                'location' => 'Ubud, Bali',
                'price' => 890000,
                'type' => 'Villa',
                'image' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=800&q=80',
                'description' => 'Architect-designed residence with floor-to-ceiling glass walls overlooking the rice terraces and a jungle ravine.',
            ],
            [
                'title' => 'Classic Suburban Home',
                'location' => 'Denpasar, Bali',
                'price' => 265000,
                'type' => 'House',
                'image' => 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=800&q=80',
                'description' => 'Well-maintained four-bedroom home with a large yard, double garage, and easy access to schools and shops.',
            ],
            [
                'title' => 'Riverside Retreat',
                'location' => 'Sanur, Bali',
                'price' => 540000,
                'type' => 'House',
                'image' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800&q=80',
                'description' => 'Peaceful riverside property with lush landscaping, outdoor pavilion, and a short walk to the beach promenade.',
            ],
            [
                'title' => 'City Center Apartment',
                'location' => 'Kuta, Bali',
                'price' => 195000,
                'type' => 'Apartment',
                'image' => 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&q=80',
                'description' => 'Two-bedroom apartment on the eighth floor with city views, gym access, and secure underground parking.',
            ],
            [
                'title' => 'Tropical Garden Estate',
                'location' => 'Tabanan, Bali',
                'price' => 720000,
                'type' => 'Villa',
                'image' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&q=80',
                'description' => 'Expansive estate with mature tropical gardens, guest house, koi pond, and mountain views on a quiet lane.',
            ],
            [
                'title' => 'Beachfront Development Land',
                'location' => 'Amed, Bali',
                'price' => 310000,
                'type' => 'Land',
                'image' => 'https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?w=800&q=80',
                'description' => 'Prime beachfront plot of 1,200 square meters with clear title, road access, and utilities at the boundary.',
            ],
            [
                'title' => 'Modern Office Space',
                'location' => 'Renon, Denpasar',
                'price' => 480000,
                'type' => 'Office',
                'image' => 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&q=80',
                'description' => 'Turnkey office building with open-plan floors, meeting rooms, fiber connectivity, and parking for twelve cars.',
            ],
            [
                'title' => 'Scandinavian Style Cottage',
                'location' => 'Bedugul, Bali',
                'price' => 340000,
                'type' => 'House',
                'image' => 'https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=800&q=80',
                'description' => 'Cozy highland cottage with warm wood interiors, fireplace, and cool mountain air near the botanical gardens.',
            ],
            [
                'title' => 'Waterfront Penthouse',
                'location' => 'Nusa Dua, Bali',
                'price' => 980000,
                'type' => 'Apartment',
                'image' => 'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?w=800&q=80',
                'description' => 'Top-floor penthouse with wraparound balcony, private elevator, and unobstructed views across the bay.',
            ],
        ];

        foreach ($properties as $property) {
            Property::query()->updateOrCreate(
                ['title' => $property['title']],
                $property
            );
        }
    }
}
