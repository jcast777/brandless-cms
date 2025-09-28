<?php

namespace Database\Seeders;

use App\Models\ApiToken;
use App\Models\User;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $superAdminRole = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $editorRole = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $viewerRole = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);

        // Create permissions if they don't exist
        $permissions = [
            'view any',
            'view',
            'create',
            'update',
            'delete',
            'restore',
            'force delete',
            'manage users',
            'manage roles',
            'manage permissions',
            'manage settings',
            'publish posts',
            'unpublish posts',
            'manage media'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // Assign all permissions to superadmin
        $superAdminRole->givePermissionTo(Permission::all());

        // Create superadmin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('superadmin123'),
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->assignRole($superAdminRole);

        // Create admin user if not exists
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('admin123'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole($adminRole);

        // Create editor user
        $editor = User::firstOrCreate(
            ['email' => 'editor@example.com'],
            [
                'name' => 'Editor User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $editor->assignRole($editorRole);

        // Generate a secure random token if not set in config
        $token = config('app.sample_api_token');

        ApiToken::create(
            [
                'name' => 'guitar-catalogue',
                'token' => $token,
                'abilities' => ['read'],
                'is_active' => true
            ]
        );

        $this->command->info('Sample API Token: ' . $token);

        // Create guitar-related categories
        $categories = [
            'Electric Guitars',
            'Acoustic Guitars',
            'Bass Guitars',
            'Classical Guitars',
            'Accessories',
            'Amps & Effects'
        ];

        $categoryIds = [];
        foreach ($categories as $category) {
            $cat = Category::firstOrCreate([
                'name' => $category,
                'slug' => Str::slug($category),
            ]);
            $categoryIds[] = $cat->id;
        }

        // Create guitar-related tags
        $tags = [
            'Stratocaster',
            'Les Paul',
            'Telecaster',
            'SG',
            'Jazzmaster',
            'Precision Bass',
            'Jazz Bass',
            'Mahogany',
            'Maple',
            'Rosewood',
            'Ash',
            'Alder',
            'Spruce',
            'Mahogany Top',
            'Maple Top',
            'Humbucker',
            'Single Coil',
            'P90',
            'Active',
            'Passive',
            'Vintage',
            'Modern',
            'Left-handed',
            'Superstrat',
            'Floyd Rose',
            'Thin Neck',
            'Fast Neck',
            'Hollow Body',
            'Semi-Hollow',
            'RG',
            'S',
            'AZ',
            'Prestige',
            'Premium',
            'Iron Label',
            'Signature',
            '7-String',
            '8-String',
            'Baritone',
            'Flame Maple',
            'Quilted Maple',
            'Basswood',
            'Bolt-On',
            'Neck-Through',
            'Floyd Rose',
            'Hardtail',
            'Tremolo',
            'Locking Nut',
            'Jumbo Frets'
        ];

        $tagIds = [];
        foreach ($tags as $tag) {
            $t = Tag::firstOrCreate([
                'name' => $tag,
                'slug' => Str::slug($tag),
            ]);
            $tagIds[] = $t->id;
        }

        try {
            // Get the first user as author
            $author = User::first();

            if (!$author) {
                // Create a default author if none exists
                $author = User::create([
                    'name' => 'Admin User',
                    'email' => 'admin@example.com',
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ]);
            }

            // Sample guitar posts
            $guitars = [
                // Ibanez Guitars
                [
                    'title' => 'Ibanez RG550 Genesis Collection',
                    'excerpt' => 'The iconic RG550 returns with original specs including the original Wizard neck profile and Edge tremolo.',
                    'content' => 'The Ibanez RG550 Genesis Collection brings back the legendary RG550 with all its original specifications. Featuring the original Wizard neck profile with a slim, fast-playing feel, the RG550 is perfect for shredders and technical players. The Edge tremolo bridge provides excellent tuning stability, while the V7 and V8 pickups deliver classic RG tone with plenty of output and clarity. The RG550 is built with a basswood body, maple neck, and rosewood fingerboard with 24 jumbo frets.',
                    'category_id' => $categoryIds[0], // Electric Guitars
                    'is_featured' => true,
                    'meta' => [
                        'brand' => 'Ibanez',
                        'model' => 'RG550',
                        'type' => 'Superstrat',
                        'year' => 2023,
                        'price' => 1199.99,
                        'body_wood' => 'Basswood',
                        'fretboard_wood' => 'Rosewood',
                        'neck_wood' => 'Maple',
                        'color' => 'Road Flare Red',
                        'pickups' => 'V7 (Neck), S1 (Middle), V8 (Bridge)',
                        'scale_length' => '25.5"',
                        'frets' => 24,
                        'bridge' => 'Edge Tremolo',
                        'fingerboard_radius' => '17"',
                        'nut_width' => '43mm'
                    ]
                ],
                [
                    'title' => 'Ibanez AZ2402 Prestige',
                    'excerpt' => 'The AZ Prestige series represents the pinnacle of Ibanez craftsmanship with premium features and versatile tone.',
                    'content' => 'The Ibanez AZ2402 Prestige is a high-performance instrument designed for the modern player. Featuring a roasted maple neck with an ultra-smooth finish, the AZ2402 offers exceptional playability and stability. The Seymour Duncan Hyperion pickups provide a wide range of tones, from vintage warmth to modern high-gain. The Dyna-MIX9 switching system offers 11 different pickup combinations, making this one of the most versatile guitars in the Ibanez lineup. The Gotoh T1800 tremolo ensures excellent tuning stability and smooth operation.',
                    'category_id' => $categoryIds[0], // Electric Guitars
                    'is_featured' => true,
                    'meta' => [
                        'brand' => 'Ibanez',
                        'model' => 'AZ2402',
                        'type' => 'Superstrat',
                        'year' => 2023,
                        'price' => 2299.99,
                        'body_wood' => 'Alder',
                        'top_wood' => 'Flame Maple',
                        'fretboard_wood' => 'Roasted Maple',
                        'neck_wood' => 'Roasted Maple',
                        'color' => 'Black Gold Burst',
                        'pickups' => 'Seymour Duncan Hyperion',
                        'scale_length' => '25.5"',
                        'frets' => 22,
                        'bridge' => 'Gotoh T1800 Tremolo',
                        'fingerboard_radius' => '12"-16" Compound Radius',
                        'nut_width' => '42mm'
                    ]
                ],
                [
                    'title' => 'Ibanez JEMJR Steve Vai Signature',
                    'excerpt' => 'The JEMJR brings the iconic JEM design to players at an affordable price point without sacrificing playability.',
                    'content' => 'The Ibanez JEMJR Steve Vai Signature guitar offers players the chance to own a piece of guitar history at an accessible price. Featuring the legendary JEM body shape with the iconic "monkey grip" handle, the JEMJR is designed for maximum playability. The INF3 pickups deliver a powerful, articulate tone perfect for everything from clean jazz to high-gain rock. The comfortable JEM neck profile and jumbo frets make fast playing effortless, while the Edge III tremolo provides stable tuning and smooth vibrato effects.',
                    'category_id' => $categoryIds[0], // Electric Guitars
                    'is_featured' => true,
                    'meta' => [
                        'brand' => 'Ibanez',
                        'model' => 'JEMJR',
                        'type' => 'Superstrat',
                        'year' => 2023,
                        'price' => 899.99,
                        'body_wood' => 'Basswood',
                        'fretboard_wood' => 'Rosewood',
                        'neck_wood' => 'Maple',
                        'color' => 'White',
                        'pickups' => 'INF3 (Neck), INF3 (Middle), INF3 (Bridge)',
                        'scale_length' => '25.5"',
                        'frets' => 24,
                        'bridge' => 'Edge III Tremolo',
                        'fingerboard_radius' => '15.75"',
                        'nut_width' => '43mm',
                        'inlay' => 'Tree of Life (Vine)'
                    ]
                ],
                [
                    'title' => 'Fender American Professional II Stratocaster',
                    'excerpt' => 'The American Professional II Stratocaster builds on the classic design with modern playability and tone.',
                    'content' => 'The Fender American Professional II Stratocaster delivers the classic Stratocaster sound with modern refinements. Featuring V-Mod II pickups for enhanced clarity and dynamics, this guitar offers exceptional tonal versatility. The deep C-shaped neck profile and narrow-tall frets provide a comfortable playing experience, while the 2-point tremolo system ensures stable tuning and smooth vibrato action. The treble-bleed circuit maintains high-end clarity when rolling back the volume control.',
                    'category_id' => $categoryIds[0], // Electric Guitars
                    'is_featured' => true,
                    'meta' => [
                        'brand' => 'Fender',
                        'model' => 'American Professional II Stratocaster',
                        'type' => 'Stratocaster',
                        'year' => 2023,
                        'price' => 1699.99,
                        'body_wood' => 'Alder',
                        'fretboard_wood' => 'Maple',
                        'neck_wood' => 'Maple',
                        'color' => '3-Color Sunburst',
                        'pickups' => 'V-Mod II Single-Coil',
                        'scale_length' => '25.5"',
                        'frets' => 22,
                        'bridge' => '2-Point Tremolo with Bent Steel Saddles',
                        'fingerboard_radius' => '9.5"',
                        'nut_width' => '42.8mm',
                        'inlay' => 'Dot'
                    ]
                ],
                [
                    'title' => 'Taylor 814ce Builder\'s Edition',
                    'excerpt' => 'The 814ce Builder\'s Edition combines premium tonewoods with innovative design for exceptional acoustic performance.',
                    'content' => 'The Taylor 814ce Builder\'s Edition is a masterpiece of acoustic guitar craftsmanship. Featuring a solid Sitka spruce top with torrefied adirondack bracing, this guitar delivers a rich, resonant tone with excellent projection. The back and sides are made of premium Indian rosewood, providing deep bass response and sparkling highs. The V-Class bracing enhances sustain and note articulation, while the armrest and beveled edges ensure supreme playing comfort. The Expression System 2 electronics faithfully reproduce the guitar\'s natural acoustic tone when amplified.',
                    'category_id' => $categoryIds[1], // Acoustic Guitars
                    'is_featured' => true,
                    'meta' => [
                        'brand' => 'Taylor',
                        'model' => '814ce Builder\'s Edition',
                        'type' => 'Grand Auditorium',
                        'year' => 2023,
                        'price' => 4999.99,
                        'top_wood' => 'Solid Sitka Spruce',
                        'back_sides_wood' => 'Indian Rosewood',
                        'neck_wood' => 'Tropical Mahogany',
                        'fretboard_wood' => 'Ebony',
                        'color' => 'Natural',
                        'electronics' => 'Taylor Expression System 2',
                        'scale_length' => '25.5"',
                        'frets' => 20,
                        'nut_width' => '44.5mm',
                        'body_length' => '20"',
                        'body_width' => '16"',
                        'body_depth' => '4.625"'
                    ]
                ],
                [
                    'title' => 'Gibson Les Paul Standard 60s',
                    'excerpt' => 'The Les Paul Standard 60s captures the essence of the golden era of electric guitars with modern reliability.',
                    'content' => 'The Gibson Les Paul Standard 60s is a faithful recreation of the iconic 1960s Les Pauls that defined rock music. Featuring a solid mahogany body with a maple top, this guitar delivers the classic Les Paul sustain and warmth. The slim taper 60s neck profile offers fast, comfortable playability, while the Burstbucker 61R and 61T pickups provide authentic PAF-style tone with enhanced clarity and articulation. The ABR-1 Tune-o-matic bridge and stopbar tailpiece ensure excellent sustain and tuning stability.',
                    'category_id' => $categoryIds[0], // Electric Guitars
                    'is_featured' => true,
                    'meta' => [
                        'brand' => 'Gibson',
                        'model' => 'Les Paul Standard 60s',
                        'type' => 'Solid Body',
                        'year' => 2023,
                        'price' => 2799.99,
                        'body_wood' => 'Mahogany',
                        'top_wood' => 'Maple',
                        'fretboard_wood' => 'Rosewood',
                        'neck_wood' => 'Mahogany',
                        'color' => 'Bourbon Burst',
                        'pickups' => 'Burstbucker 61R (Neck), Burstbucker 61T (Bridge)',
                        'scale_length' => '24.75"',
                        'frets' => 22,
                        'bridge' => 'ABR-1 Tune-o-matic',
                        'fingerboard_radius' => '12"',
                        'nut_width' => '43mm',
                        'inlay' => 'Acrylic Trapezoid'
                    ]
                ],
                [
                    'title' => 'Music Man StingRay Special 5-String Bass',
                    'excerpt' => 'The StingRay Special 5-String Bass delivers powerful, articulate tone with exceptional playability and modern features.',
                    'content' => 'The Music Man StingRay Special 5-String Bass combines classic StingRay tone with modern refinements. The roasted maple neck provides enhanced stability and a smooth playing feel, while the high-mass bridge ensures optimal sustain and string vibration transfer. The Music Man humbucking pickup delivers the signature StingRay punch and growl, with a 3-band active EQ for extensive tonal shaping. The contoured body and sculpted neck heel offer exceptional comfort and easy access to the upper frets.',
                    'category_id' => $categoryIds[2], // Bass Guitars
                    'is_featured' => true,
                    'meta' => [
                        'brand' => 'Music Man',
                        'model' => 'StingRay Special 5-String',
                        'type' => 'Solid Body Bass',
                        'year' => 2023,
                        'price' => 2249.99,
                        'body_wood' => 'Ash',
                        'fretboard_wood' => 'Maple',
                        'neck_wood' => 'Roasted Maple',
                        'color' => 'Jet Black',
                        'pickups' => 'Music Man Humbucker',
                        'electronics' => '3-Band Active EQ',
                        'scale_length' => '34"',
                        'frets' => 21,
                        'bridge' => 'High-Mass Bass Bridge',
                        'fingerboard_radius' => '11"',
                        'nut_width' => '45mm',
                        'string_spacing' => '18mm'
                    ]
                ],
                [
                    'title' => 'Ibanez RGD71ALMS Iron Label',
                    'excerpt' => 'The RGD71ALMS is a 7-string metal machine with a multi-scale design and Fishman Fluence pickups.',
                    'content' => 'The Ibanez RGD71ALMS Iron Label is built for modern metal players who demand the best in playability and tone. The multi-scale fanned fret design (25.5"-27") provides optimal string tension and intonation across all strings, while the roasted maple neck offers stability and speed. Fishman Fluence Modern pickups deliver crushing high-gain tones with exceptional clarity and note definition. The Gibraltar Standard II-7 bridge ensures excellent tuning stability, even with heavy tremolo use. The open-pore finish provides a natural feel that enhances playability.',
                    'category_id' => $categoryIds[0], // Electric Guitars
                    'meta' => [
                        'brand' => 'Ibanez',
                        'model' => 'RGD71ALMS',
                        'type' => '7-String Multi-Scale',
                        'year' => 2023,
                        'price' => 1499.99,
                        'body_wood' => 'Ash',
                        'fretboard_wood' => 'Ebony',
                        'neck_wood' => 'Roasted Maple',
                        'color' => 'Black Flat',
                        'pickups' => 'Fishman Fluence Modern',
                        'scale_length' => '25.5"-27" Multi-Scale',
                        'frets' => 24,
                        'bridge' => 'Gibraltar Standard II-7',
                        'fingerboard_radius' => '15.75"',
                        'nut_width' => '48mm',
                        'strings' => '7'
                    ]
                ],
                [
                    'title' => 'Ibanez SR500E Bass Guitar',
                    'excerpt' => 'The SR500E offers professional features and premium tonewoods at an affordable price point.',
                    'content' => 'The Ibanez SR500E bass guitar combines premium tonewoods with high-quality electronics for a versatile instrument that excels in any musical situation. The mahogany body provides warm, resonant tone, while the 5-piece maple/walnut neck offers stability and sustain. The Bartolini MK-1 pickups and Ibanez Custom Electronics 3-band EQ deliver a wide range of tones, from deep, punchy lows to crisp, articulate highs. The SR500E features a comfortable, fast-playing neck profile and a 34" scale length for familiar feel and playability.',
                    'category_id' => $categoryIds[2], // Bass Guitars
                    'meta' => [
                        'brand' => 'Ibanez',
                        'model' => 'SR500E',
                        'type' => '4-String Bass',
                        'year' => 2023,
                        'price' => 799.99,
                        'body_wood' => 'Mahogany',
                        'fretboard_wood' => 'Rosewood',
                        'neck_wood' => '5-Piece Maple/Walnut',
                        'color' => 'Brown Mahogany',
                        'pickups' => 'Bartolini MK-1',
                        'scale_length' => '34"',
                        'frets' => 24,
                        'bridge' => 'Ibanez MR4S',
                        'fingerboard_radius' => '12"',
                        'nut_width' => '45mm',
                        'electronics' => 'Ibanez Custom Electronics 3-Band EQ'
                    ]
                ],
            ];

            // Featured image mappings from existing database
            // TODO: Update these mappings based on actual posts.featured_image data
            $featuredImageMappings = [
                'Ibanez RG550 Genesis Collection' => 'sample_01K67TSK2P8A9RTXX889H93C3M.jpg',
                'Ibanez AZ2402 Prestige' => 'sample_01K67TW5H9QJZT0VH0A5XX7MNE.jpg',
                'Ibanez JEMJR Steve Vai Signature' => 'sample_01K67TYSXJHG99KY299N3PP90G.jpg',
                'Fender American Professional II Stratocaster' => 'sample_01K67V1RT8AEGYFD7DC5T4XHBV.avif',
                'Taylor 814ce Builder\'s Edition' => 'sample_01K667WFQWFYKPSBWTE2BAKY14.png',
                'Gibson Les Paul Standard 60s' => 'sample_01K67VCEG2B5FFWG0SAWH7VRQW.png',
                'Music Man StingRay Special 5-String Bass' => 'sample_01K67VFVSMNFZ612D83ZN4Q2FR.png',
                'Ibanez RGD71ALMS Iron Label' => 'sample_01K67VHRST8PBMGVZ6DC47DMZK.jpg',
                'Ibanez SR500E Bass Guitar' => 'sample_01K67VKP6V535BHHR0XR8FXKC5.jpg',
            ];

            // Create sample guitar posts
            foreach ($guitars as $guitarData) {
                // Get featured image from mapping or use default
                $featuredImage = $featuredImageMappings[$guitarData['title']] ?? 'default.jpg';

                $post = Post::create([
                    'title' => $guitarData['title'],
                    'slug' => Str::slug($guitarData['title']),
                    'excerpt' => $guitarData['excerpt'],
                    'content' => $guitarData['content'],
                    'featured_image' => $featuredImage,
                    'author_id' => $author->id,
                    'category_id' => $guitarData['category_id'],
                    'status' => 'published',
                    'published_at' => now(),
                    'meta' => $guitarData['meta'],
                    'is_featured' => in_array($guitarData['title'], [
                        'Ibanez RG550 Genesis Collection',
                        'Fender American Professional II Stratocaster',
                        'Gibson Les Paul Standard 60s',
                        'Music Man StingRay Special 5-String Bass'
                    ]),
                    'allow_comments' => true,
                    'seo' => [
                        'title' => $guitarData['title'] . ' | Premium Guitar',
                        'description' => $guitarData['excerpt'],
                        'keywords' => implode(', ', array_merge(
                            explode(' ', $guitarData['title']),
                            array_filter([
                                $guitarData['meta']['body_wood'] ?? null,
                                $guitarData['meta']['color'] ?? null,
                                $guitarData['meta']['brand'] ?? null,
                                $guitarData['meta']['model'] ?? null
                            ])
                        ))
                    ],
                    'views_count' => rand(0, 1000),
                    'created_at' => now()->subDays(rand(1, 30)),
                    'updated_at' => now()
                ]);

                // Attach relevant tags based on guitar model
                $relevantTags = [];

                // Add brand-specific tags
                if (stripos($post->title, 'ibanez') !== false) {
                    $relevantTags = array_merge($relevantTags, array_filter($tagIds, function ($tagId) use ($tags) {
                        $tagName = strtolower(Tag::find($tagId)->name);
                        return in_array($tagName, [
                            'rg',
                            's',
                            'az',
                            'prestige',
                            'premium',
                            'iron label',
                            'signature',
                            '7-string',
                            '8-string',
                            'superstrat',
                            'floyd rose',
                            'fast neck',
                            'thin neck'
                        ]);
                    }));
                }

                // Add model-specific tags
                if (stripos($post->title, 'rg550') !== false) {
                    $relevantTags = array_merge($relevantTags, array_filter($tagIds, function ($tagId) use ($tags) {
                        $tagName = strtolower(Tag::find($tagId)->name);
                        return in_array($tagName, ['superstrat', 'floyd rose', 'fast neck', 'humbucker']);
                    }));
                } elseif (stripos($post->title, 'az2402') !== false) {
                    $relevantTags = array_merge($relevantTags, array_filter($tagIds, function ($tagId) use ($tags) {
                        $tagName = strtolower(Tag::find($tagId)->name);
                        return in_array($tagName, ['prestige', 'modern', 'versatile', 'humbucker', 'single coil']);
                    }));
                } elseif (stripos($post->title, 'jem') !== false) {
                    $relevantTags = array_merge($relevantTags, array_filter($tagIds, function ($tagId) use ($tags) {
                        $tagName = strtolower(Tag::find($tagId)->name);
                        return in_array($tagName, ['signature', 'floyd rose', 'fast neck', 'humbucker']);
                    }));
                } elseif (stripos($post->title, 'rgd71alms') !== false) {
                    $relevantTags = array_merge($relevantTags, array_filter($tagIds, function ($tagId) use ($tags) {
                        $tagName = strtolower(Tag::find($tagId)->name);
                        return in_array($tagName, ['7-string', 'multi-scale', 'modern', 'metal', 'active']);
                    }));
                } elseif (stripos($post->title, 'sr500e') !== false) {
                    $relevantTags = array_merge($relevantTags, array_filter($tagIds, function ($tagId) use ($tags) {
                        $tagName = strtolower(Tag::find($tagId)->name);
                        return in_array($tagName, ['bass', 'active', 'modern', 'versatile']);
                    }));
                }

                // Make sure we have at least 3 tags, but no more than 5
                $numTags = min(5, max(3, count($relevantTags)));
                shuffle($relevantTags);
                $selectedTags = array_slice($relevantTags, 0, $numTags);

                // If we don't have enough relevant tags, add some random ones
                if (count($selectedTags) < 3) {
                    $remaining = 3 - count($selectedTags);
                    $availableTags = array_diff($tagIds, $selectedTags);
                    shuffle($availableTags);
                    $additionalTags = array_slice($availableTags, 0, $remaining);
                    $selectedTags = array_merge($selectedTags, $additionalTags);
                }

                // Ensure all tag IDs are unique before attaching
                $uniqueTags = array_unique($selectedTags);
                $post->tags()->syncWithoutDetaching($uniqueTags);
            }

            $this->command->info('Sample guitar catalog data created successfully!');
        } catch (\Exception $e) {
            $this->command->error('Error creating sample data: ' . $e->getMessage());
            $this->command->error($e->getTraceAsString());
        }
    }
}
