<?php

namespace Code16\LaravelTiteliveClient\Database\Factories;

use Code16\LaravelTiteliveClient\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    protected $model = Book::class;

    private static int $VISUAL_INDEX = 0;

    private static array $VISUALS = [
        '229/9782070585229',
        '904/9782070624904',
        '073/9782070643073',
        '670/9782070572670',
        '644/9782070577644',
        '413/9782070612413',
        '214/9782070619214',
        '628/9782070584628',
        '522/9782070624522',
        '693/9782070649693',
        '028/9782070643028',
        '532/9787020144532',
        '425/9782070518425',
        '093/9782070696093',
        '270/9782070541270',
        '369/9782070612369',
        '177/9782070619177',
        '462/9782840116462',
        '212/9782070585212',
        '560/9782070624560',
        '066/9782070643066',
        '854/9782070556854',
        '577/9782070525577',
        '861/9782070556861',
        '406/9782070612406',
        '205/9782070585205',
        '384/9782075090384',
        '553/9782070624553',
        '059/9782070643059',
        '588/9782070543588',
        '519/9782070543519',
        '207/9782070619207',
        '390/9782070612390',
        '236/9782070585236',
        '911/9782070624911',
        '080/9782070643080',
        '360/9782070615360',
        '033/9782070618033',
        '377/9782070615377',
        '642/9782070584642',
        '539/9782070624539',
        '035/9782070643035',
        '184/9782070619184',
        '556/9782070524556',
        '998/9782840116998',
        '294/9782070541294',
        '376/9782070612376',
        '925/9782070584925',
        '546/9782070624546',
        '042/9782070643042',
        '300/9782070541300',
        '189/9782070528189',
        '191/9782070619191',
        '383/9782070612383',
        '382/9782075094382',
        '209/9782075074209',
        '635/9782877068635',
        '114/9791032102114',
        '161/9782877068161',
        '708/9782846667708',
        '613/9782075150613',
    ];

    public function definition()
    {
        return [
            'id' => $this->faker->ean13,
            'title' => $this->faker->sentence,
            'authors' => [$this->faker->name, $this->faker->name],
            'description' => $this->faker->paragraph(5),
            'translator' => $this->faker->boolean ? $this->faker->name : null,
            'weight' => $this->faker->numberBetween(100, 1000),
            'page_count' => $this->faker->numberBetween(150, 1000),
            'category_codes' => $this->faker->randomElements(
                [$this->faker->numerify('#########'), $this->faker->numerify('#########'), $this->faker->numerify('#########'), $this->faker->numerify('#########')],
                $this->faker->numberBetween(1, 4)
            ),
            'readership' => $this->faker->boolean(25)
                ? sprintf('À partir de %s ans', $this->faker->numberBetween(5, 16))
                : null,
            'editor' => $this->faker->company,
            'price' => $this->faker->numberBetween(500, 2500),
            'published_date' => now()->subMonths(rand(1, 240))->setTime(0, 0),
            'support' => $this->faker->randomElement(['P', 'T']),
            'visuals' => [
                'large' => $this->getVisual('L'),
                'thumbnail' => $this->getVisual('S'),
                'medium' => $this->getVisual('M'),
            ],
            'availability' => $this->faker->numberBetween(1, 8),
            'stock' => $this->faker->numberBetween(0, 5),
            'editions' => $this->faker->randomElements(
                [$this->faker->ean13, $this->faker->ean13, $this->faker->ean13, $this->faker->ean13],
                $this->faker->numberBetween(0, 4)
            ),
            'refreshed_at' => now()->subDays($this->faker->numberBetween(0, 30))->toISOString(),
        ];
    }

    private function getVisual(string $size = 'L'): string
    {
        $visual = sprintf(
            'https://images.epagine.fr/%s_1_%s.jpg',
            self::$VISUALS[self::$VISUAL_INDEX],
            ['S' => 'v', 'M' => 'm', 'L' => '75'][$size] ?? '75'
        );

        if (++self::$VISUAL_INDEX >= count(self::$VISUALS)) {
            self::$VISUAL_INDEX = 0;
        }

        return $visual;
    }
}
